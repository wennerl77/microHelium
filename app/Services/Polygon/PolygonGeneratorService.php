<?php

namespace App\Services\Polygon;

use App\Models\PolygonExercise;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class PolygonGeneratorService
{
    /**
     * Timeout in seconds for compiling the C++ generator.
     */
    protected const COMPILE_TIMEOUT_SECONDS = 30;

    /**
     * Timeout in seconds for running the generator on each test case.
     */
    protected const GENERATOR_RUN_TIMEOUT_SECONDS = 5;

    /**
     * Compiles the exercise generator source code (gen.cpp) into a native binary.
     *
     * @param PolygonExercise $exercise
     * @return array{success: bool, output: string, executable: ?string}
     */
    public function compileGenerator(PolygonExercise $exercise): array
    {
        $tempDir = $exercise->getTempDirectory();
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $sourceCode = $exercise->generator_source;
        if (empty(trim($sourceCode ?? ''))) {
            return [
                'success' => false,
                'output' => 'Generator source code is empty.',
                'executable' => null,
            ];
        }

        $sourceFile = $tempDir . '/gen.cpp';
        $executableFile = $tempDir . '/gen';

        file_put_contents($sourceFile, $sourceCode);

        // Attempt compilation with -static first
        $result = $this->runGppCompilation($sourceFile, $executableFile, true);
        if ($result->successful()) {
            @chmod($executableFile, 0755);
            return [
                'success' => true,
                'output' => $result->output() ?: 'Compilation successful with -static.',
                'executable' => $executableFile,
            ];
        }

        // If -static failed, fallback to compilation without -static
        $fallback = $this->runGppCompilation($sourceFile, $executableFile, false);
        if ($fallback->successful()) {
            @chmod($executableFile, 0755);
            return [
                'success' => true,
                'output' => $fallback->output() ?: 'Compilation successful (without -static).',
                'executable' => $executableFile,
            ];
        }

        // Both attempts failed; return compiler error output
        $errorOutput = trim($fallback->errorOutput() ?: $fallback->output() ?: $result->errorOutput() ?: $result->output());

        return [
            'success' => false,
            'output' => $errorOutput ?: 'Compilation failed.',
            'executable' => null,
        ];
    }

    /**
     * Parses the test generator script into argument lists for each test case.
     *
     * Ignores empty lines and comments starting with '#' or '//'.
     * Strips redirection tokens (e.g., '> $') and generator commands (e.g., 'gen', './gen').
     *
     * @param string|null $script
     * @return array<int, array<int, string>>
     */
    public function parseScript(?string $script): array
    {
        if ($script === null || trim($script) === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $script);
        $parsed = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '' || str_starts_with($trimmed, '#') || str_starts_with($trimmed, '//')) {
                continue;
            }

            // Strip redirection (e.g. > $, > 1, >$)
            $cleaned = preg_replace('/>\s*\$?.*$/', '', $trimmed);

            // Strip generator prefix (e.g. ./gen, gen, gen.exe)
            $cleaned = preg_replace('/^(\.\/)?gen(\.exe)?\b\s*/i', '', trim((string) $cleaned));
            $cleaned = trim((string) $cleaned);

            $args = $this->extractArguments($cleaned);
            $parsed[] = $args;
        }

        return $parsed;
    }

    /**
     * Generates input test cases for the exercise by executing the compiled generator
     * for each script line and processing any manual tests.
     *
     * Saves inputs in $exercise->getTempDirectory() . '/tests/{number}.in'
     * and updates the exercise status to 'generated'.
     *
     * @param PolygonExercise $exercise
     * @return array{
     *     success: bool,
     *     total_tests: int,
     *     cases: array<int, array{
     *         number: int,
     *         origin: string,
     *         input_file: string,
     *         size_bytes: int,
     *         is_sample: bool,
     *         preview: string
     *     }>,
     *     error?: string
     * }
     */
    public function generateTests(PolygonExercise $exercise): array
    {
        $tempDir = $exercise->getTempDirectory();
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $executable = $tempDir . '/gen';
        $sourceFile = $tempDir . '/gen.cpp';
        $parsedScript = $this->parseScript($exercise->generator_script);

        // Ensure generator is compiled if there is a script or generator source provided
        if (!empty($parsedScript) || !empty(trim($exercise->generator_source ?? ''))) {
            $needsCompile = !file_exists($executable)
                || !is_executable($executable)
                || !file_exists($sourceFile)
                || (!empty($exercise->generator_source) && @file_get_contents($sourceFile) !== $exercise->generator_source);

            if ($needsCompile) {
                $compilation = $this->compileGenerator($exercise);
                if (!$compilation['success']) {
                    return [
                        'success' => false,
                        'total_tests' => 0,
                        'cases' => [],
                        'error' => 'Generator compilation failed: ' . ($compilation['output'] ?? 'Unknown error'),
                    ];
                }
                $executable = $compilation['executable'];
            }
        }

        // Prepare tests directory and clean old .in files
        $testsDir = $tempDir . '/tests';
        if (!is_dir($testsDir)) {
            mkdir($testsDir, 0755, true);
        }

        $oldFiles = array_merge(
            glob($testsDir . '/*.in') ?: [],
            glob($testsDir . '/*.out') ?: []
        );
        foreach ($oldFiles as $oldFile) {
            @unlink($oldFile);
        }

        $cases = [];
        $caseNumber = 1;

        // 1. Process manual tests (skip empty inputs)
        $manualTests = $exercise->manual_tests;
        if (is_string($manualTests)) {
            $manualTests = json_decode($manualTests, true);
        }

        if (is_array($manualTests)) {
            foreach ($manualTests as $manual) {
                $rawInput = $manual['input'] ?? null;
                if ($rawInput === null || trim((string) $rawInput) === '') {
                    continue;
                }
                $input = (string) $rawInput;
                if (! str_ends_with($input, "\n")) {
                    $input .= "\n";
                }
                $isSample = (bool) ($manual['is_sample'] ?? false);
                $fileName = sprintf('%03d.in', $caseNumber);
                $filePath = $testsDir . '/' . $fileName;

                file_put_contents($filePath, $input);

                $cases[] = [
                    'number' => $caseNumber,
                    'origin' => 'manual',
                    'input_file' => $filePath,
                    'size_bytes' => (int) @filesize($filePath),
                    'is_sample' => $isSample,
                    'preview' => mb_substr($input, 0, 256),
                ];

                $caseNumber++;
            }
        }

        // 2. Process script tests
        foreach ($parsedScript as $args) {
            // If no arguments/seed were provided (e.g. bare "gen > $"), supply caseNumber as seed
            if (empty($args)) {
                $args = [(string) $caseNumber];
            }

            try {
                $cmd = array_merge([$executable], $args);
                $result = Process::path($tempDir)
                    ->timeout(self::GENERATOR_RUN_TIMEOUT_SECONDS)
                    ->run($cmd);
            } catch (\Throwable $e) {
                return [
                    'success' => false,
                    'total_tests' => count($cases),
                    'cases' => $cases,
                    'error' => "Generator failed on case {$caseNumber}: " . $e->getMessage(),
                ];
            }

            if (!$result->successful()) {
                $error = trim($result->errorOutput() ?: $result->output());
                return [
                    'success' => false,
                    'total_tests' => count($cases),
                    'cases' => $cases,
                    'error' => "Generator process exited with error on case {$caseNumber}: " . ($error ?: "Exit code {$result->exitCode()}"),
                ];
            }

            $output = $result->output();
            $fileName = sprintf('%03d.in', $caseNumber);
            $filePath = $testsDir . '/' . $fileName;

            file_put_contents($filePath, $output);

            $cases[] = [
                'number' => $caseNumber,
                'origin' => 'script',
                'input_file' => $filePath,
                'size_bytes' => (int) @filesize($filePath),
                'is_sample' => false,
                'preview' => mb_substr($output, 0, 256),
            ];

            $caseNumber++;
        }

        if (empty($cases)) {
            return [
                'success' => false,
                'total_tests' => 0,
                'cases' => [],
                'error' => 'No test cases generated. Script and manual tests are empty.',
            ];
        }

        // Update exercise status to 'generated' and persist
        $exercise->status = PolygonExercise::STATUS_GENERATED;
        $exercise->save();

        return [
            'success' => true,
            'total_tests' => count($cases),
            'cases' => $cases,
        ];
    }

    /**
     * Cleans up the temporary workspace directory for the given exercise.
     */
    public function cleanTempDirectory(PolygonExercise $exercise): void
    {
        $tempDir = $exercise->getTempDirectory();
        if (is_dir($tempDir)) {
            File::deleteDirectory($tempDir);
        }
    }

    /**
     * Executes the g++ compilation process.
     */
    protected function runGppCompilation(string $sourcePath, string $executablePath, bool $static): ProcessResult
    {
        $includePath = base_path('resources/judge-runtime/include');

        $cmd = ['g++', '-O2', '-std=c++20'];
        if ($static) {
            $cmd[] = '-static';
        }
        $cmd[] = '-I';
        $cmd[] = $includePath;
        $cmd[] = $sourcePath;
        $cmd[] = '-o';
        $cmd[] = $executablePath;

        return Process::timeout(self::COMPILE_TIMEOUT_SECONDS)->run($cmd);
    }

    /**
     * Splits a command line string into separate arguments, respecting quoted strings.
     *
     * @param string $commandLine
     * @return array<int, string>
     */
    protected function extractArguments(string $commandLine): array
    {
        if ($commandLine === '') {
            return [];
        }

        preg_match_all('/"([^"]*)"|\'([^\']*)\'|(\S+)/', $commandLine, $matches, PREG_SET_ORDER);

        $args = [];
        foreach ($matches as $m) {
            if (isset($m[1]) && $m[1] !== '') {
                $args[] = $m[1];
            } elseif (isset($m[2]) && $m[2] !== '') {
                $args[] = $m[2];
            } elseif (isset($m[3]) && $m[3] !== '') {
                $args[] = $m[3];
            }
        }

        return $args;
    }
}
