<?php

namespace App\Services\Polygon;

use App\Exceptions\JudgeWallClockTimeoutException;
use App\Models\PolygonExercise;
use App\Services\AutoJudgeService;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Process\Exceptions\ProcessTimedOutException as LaravelProcessTimedOutException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Process\Exception\ProcessSignaledException;
use Symfony\Component\Process\Exception\ProcessTimedOutException as SymfonyProcessTimedOutException;

class PolygonCalibrationService
{
    /**
     * Timeout in seconds for compiling C++ solutions.
     */
    protected const COMPILE_TIMEOUT_SECONDS = 30;

    protected LanguageLimitDerivationService $limitDerivationService;

    protected AutoJudgeService $autoJudge;

    public function __construct(
        LanguageLimitDerivationService $limitDerivationService,
        ?AutoJudgeService $autoJudge = null
    ) {
        $this->limitDerivationService = $limitDerivationService;
        $this->autoJudge = $autoJudge ?? app(AutoJudgeService::class);
    }

    /**
     * Calibrates the exercise by compiling solutions (ACCEPT, TLE, WRONG),
     * generating official .out files from ACCEPT, evaluating TLE and WRONG,
     * and deriving limits for all supported languages.
     *
     * @param  PolygonExercise  $exercise
     * @return array{
     *     status: string,
     *     summary: array{
     *         total_tests: int,
     *         accept_passed: bool,
     *         max_time_seconds: float,
     *         max_memory_mb: float,
     *         tle_calibrated: ?bool,
     *         wrong_calibrated: ?bool,
     *         warnings: array<int, string>
     *     },
     *     cases: array<int, array{
     *         number: int,
     *         is_sample: bool,
     *         input_bytes: int,
     *         output_bytes: int,
     *         accept: array{verdict: string, time: float, memory: float},
     *         tle: ?array{verdict: string, time: float},
     *         wrong: ?array{verdict: string, match: bool}
     *     }>,
     *     language_limits: array<int, mixed>,
     *     message?: string
     * }
     */
    public function calibrate(PolygonExercise $exercise): array
    {
        $tempDir = $exercise->getTempDirectory();
        $testsDir = $tempDir . '/tests';

        // 1. Validate test cases directory and input files
        if (! is_dir($testsDir)) {
            return $this->buildErrorResult(
                'Nenhum caso de teste encontrado no workspace. Execute a geração de testes primeiro.',
                0
            );
        }

        $inFiles = glob($testsDir . '/*.in') ?: [];
        natsort($inFiles);
        $inFiles = array_values($inFiles);

        if (empty($inFiles)) {
            return $this->buildErrorResult(
                'Nenhum arquivo de entrada (.in) encontrado no workspace. Execute a geração de testes primeiro.',
                0
            );
        }

        // 2. Validate mandatory ACCEPT solution
        $acceptSource = trim($exercise->solution_accept ?? '');
        if ($acceptSource === '') {
            return $this->buildErrorResult(
                'A solução modelo ACCEPT (C++) é obrigatória para calibração.',
                count($inFiles)
            );
        }

        // 3. Prepare solutions workspace directory
        $solutionsDir = $tempDir . '/solutions';
        if (! is_dir($solutionsDir)) {
            mkdir($solutionsDir, 0755, true);
        }

        // 4. Compile ACCEPT solution
        $acceptSourceFile = $solutionsDir . '/accept.cpp';
        $acceptBinaryFile = $solutionsDir . '/accept.bin';
        file_put_contents($acceptSourceFile, $acceptSource);

        $acceptCompilation = $this->compileCpp($acceptSourceFile, $acceptBinaryFile, $solutionsDir);
        if (! $acceptCompilation['success']) {
            return $this->buildErrorResult(
                'Erro de compilação na solução ACCEPT: ' . $acceptCompilation['output'],
                count($inFiles),
                ['Erro de compilação na solução ACCEPT (CE): ' . $acceptCompilation['output']]
            );
        }

        // 5. Compile TLE solution (optional)
        $hasTle = ! empty(trim($exercise->solution_tle ?? ''));
        $tleBinaryFile = null;
        $warnings = [];

        if ($hasTle) {
            $tleSourceFile = $solutionsDir . '/tle.cpp';
            $tleBinaryFile = $solutionsDir . '/tle.bin';
            file_put_contents($tleSourceFile, (string) $exercise->solution_tle);

            $tleCompilation = $this->compileCpp($tleSourceFile, $tleBinaryFile, $solutionsDir);
            if (! $tleCompilation['success']) {
                $warnings[] = 'Aviso: Solução TLE não compilou: ' . $tleCompilation['output'];
                $tleBinaryFile = null;
            }
        }

        // 6. Compile WRONG solution (optional)
        $hasWrong = ! empty(trim($exercise->solution_wrong ?? ''));
        $wrongBinaryFile = null;

        if ($hasWrong) {
            $wrongSourceFile = $solutionsDir . '/wrong.cpp';
            $wrongBinaryFile = $solutionsDir . '/wrong.bin';
            file_put_contents($wrongSourceFile, (string) $exercise->solution_wrong);

            $wrongCompilation = $this->compileCpp($wrongSourceFile, $wrongBinaryFile, $solutionsDir);
            if (! $wrongCompilation['success']) {
                $warnings[] = 'Aviso: Solução WRONG não compilou: ' . $wrongCompilation['output'];
                $wrongBinaryFile = null;
            }
        }

        // 7. Parse manual tests to detect is_sample flag for each test case
        $manualTests = $exercise->manual_tests;
        if (is_string($manualTests)) {
            $manualTests = json_decode($manualTests, true) ?: [];
        }
        $validManualTests = is_array($manualTests)
            ? array_values(array_filter($manualTests, fn($m) => isset($m['input']) && trim((string) $m['input']) !== ''))
            : [];

        $timeLimit = max(1, (int) ($exercise->time_limit ?? 1));
        $memoryLimit = max(16, (int) ($exercise->memory_limit ?? 256));
        $outputLimitKb = max(1, (int) ($exercise->output_limit ?? 1024));

        $cases = [];
        $acceptPassed = true;
        $maxTime = 0.0;
        $maxMemory = 0.0;
        $anyTleOccurred = false;
        $anyWrongOccurred = false;

        // 8. Execute test cases
        foreach ($inFiles as $index => $inFile) {
            $caseNumber = $index + 1;
            $baseName = sprintf('%03d', $caseNumber);
            $officialOutFile = $testsDir . '/' . $baseName . '.out';
            $isSample = isset($validManualTests[$index]) ? (bool) ($validManualTests[$index]['is_sample'] ?? false) : false;

            // --- 8a. Run ACCEPT solution and generate official .out ---
            $acceptStderr = $solutionsDir . '/accept_' . $baseName . '.stderr';
            $acceptRss = $solutionsDir . '/.mhrss_acc_' . $baseName;

            $acceptResult = $this->executeSolution(
                $acceptBinaryFile,
                $inFile,
                $officialOutFile,
                $acceptStderr,
                $acceptRss,
                $timeLimit,
                $memoryLimit,
                $outputLimitKb,
                $tempDir,
                $exercise->id,
                'acc',
                $caseNumber
            );

            if ($acceptResult['verdict'] !== 'AC') {
                $acceptPassed = false;
            }

            $maxTime = max($maxTime, $acceptResult['time']);
            $maxMemory = max($maxMemory, $acceptResult['memory']);

            // --- 8b. Run TLE solution (if available) ---
            $tleCase = null;
            if ($tleBinaryFile !== null) {
                $tleOutFile = $solutionsDir . '/tle_' . $baseName . '.out';
                $tleStderr = $solutionsDir . '/tle_' . $baseName . '.stderr';
                $tleRss = $solutionsDir . '/.mhrss_tle_' . $baseName;

                $tleResult = $this->executeSolution(
                    $tleBinaryFile,
                    $inFile,
                    $tleOutFile,
                    $tleStderr,
                    $tleRss,
                    $timeLimit,
                    $memoryLimit,
                    $outputLimitKb,
                    $tempDir,
                    $exercise->id,
                    'tle',
                    $caseNumber
                );

                if ($tleResult['verdict'] === 'TLE') {
                    $anyTleOccurred = true;
                }

                $tleCase = [
                    'verdict' => $tleResult['verdict'],
                    'time' => $tleResult['time'],
                ];
            }

            // --- 8c. Run WRONG solution (if available) ---
            $wrongCase = null;
            if ($wrongBinaryFile !== null) {
                $wrongOutFile = $solutionsDir . '/wrong_' . $baseName . '.out';
                $wrongStderr = $solutionsDir . '/wrong_' . $baseName . '.stderr';
                $wrongRss = $solutionsDir . '/.mhrss_wrong_' . $baseName;

                $wrongExecResult = $this->executeSolution(
                    $wrongBinaryFile,
                    $inFile,
                    $wrongOutFile,
                    $wrongStderr,
                    $wrongRss,
                    $timeLimit,
                    $memoryLimit,
                    $outputLimitKb,
                    $tempDir,
                    $exercise->id,
                    'wrong',
                    $caseNumber
                );

                if ($wrongExecResult['verdict'] !== 'AC') {
                    $wrongVerdict = $wrongExecResult['verdict'];
                    $wrongMatch = false;
                    $anyWrongOccurred = true;
                } else {
                    $expectedText = file_exists($officialOutFile) ? (string) file_get_contents($officialOutFile) : '';
                    $actualText = file_exists($wrongOutFile) ? (string) file_get_contents($wrongOutFile) : '';

                    $comparison = $this->autoJudge->compareOutput($expectedText, $actualText);
                    if ($comparison['verdict'] === 'AC') {
                        $wrongVerdict = 'AC';
                        $wrongMatch = true;
                    } else {
                        $wrongVerdict = 'WA';
                        $wrongMatch = false;
                        $anyWrongOccurred = true;
                    }
                }

                $wrongCase = [
                    'verdict' => $wrongVerdict,
                    'match' => $wrongMatch,
                ];
            }

            $inContent = file_exists($inFile) ? (string) file_get_contents($inFile) : '';
            $outContent = file_exists($officialOutFile) ? (string) file_get_contents($officialOutFile) : '';

            $cases[] = [
                'number' => $caseNumber,
                'is_sample' => $isSample,
                'input_bytes' => (int) @filesize($inFile),
                'output_bytes' => (int) (file_exists($officialOutFile) ? @filesize($officialOutFile) : 0),
                'input' => $inContent,
                'output' => $outContent,
                'accept' => [
                    'verdict' => $acceptResult['verdict'],
                    'time' => $acceptResult['time'],
                    'memory' => $acceptResult['memory'],
                ],
                'tle' => $tleCase,
                'wrong' => $wrongCase,
            ];
        }

        // 9. Calibrate TLE and WRONG diagnostic checks
        $tleCalibrated = null;
        if ($hasTle) {
            $tleCalibrated = $anyTleOccurred;
            if (! $tleCalibrated) {
                $warnings[] = 'Atenção: a solução TLE passou em todos os testes. Recomenda-se aumentar os limites dos casos de teste ou reduzir o time_limit';
            }
        }

        $wrongCalibrated = null;
        if ($hasWrong) {
            $wrongCalibrated = $anyWrongOccurred;
            if (! $wrongCalibrated) {
                $warnings[] = 'Atenção: a solução WRONG passou em todos os testes. Adicione casos de teste específicos para cobrir este caso de borda';
            }
        }

        // 10. Derive language limits
        $languageLimits = $this->limitDerivationService->deriveForExercise(
            $exercise,
            LanguageLimitDerivationService::SOURCE_MEASURED
        );

        // 11. Determine final status
        $status = $acceptPassed ? PolygonExercise::STATUS_CALIBRATED : 'failed';

        $payload = [
            'status' => $status,
            'summary' => [
                'total_tests' => count($inFiles),
                'accept_passed' => $acceptPassed,
                'max_time_seconds' => round($maxTime, 3),
                'max_memory_mb' => round($maxMemory, 1),
                'tle_calibrated' => $tleCalibrated,
                'wrong_calibrated' => $wrongCalibrated,
                'warnings' => $warnings,
            ],
            'cases' => $cases,
            'language_limits' => $languageLimits,
        ];

        // 12. Persist state and calibration results
        if ($acceptPassed) {
            $exercise->status = PolygonExercise::STATUS_CALIBRATED;
        }
        $exercise->calibration_results = $payload;
        $exercise->save();

        return $payload;
    }

    /**
     * Executes a compiled solution under the judge sandbox / resource limits,
     * measuring execution time and peak RSS memory.
     *
     * @return array{verdict: string, time: float, memory: float, exit_code: int}
     */
    protected function executeSolution(
        string $binaryPath,
        string $inFile,
        string $outFile,
        string $stderrFile,
        string $rssPath,
        int $timeLimit,
        int $memoryLimit,
        int $outputLimitKb,
        string $workDir,
        int|string $exerciseId,
        string $phase,
        int $caseNumber
    ): array {
        $rawCommand = escapeshellarg($binaryPath) . ' < ' . escapeshellarg($inFile) . ' > ' . escapeshellarg($outFile) . ' 2> ' . escapeshellarg($stderrFile);
        $commandWithRss = $this->autoJudge->withPeakRssMeasurement($rawCommand, $rssPath);

        $options = [
            'allow_net' => false,
            'ro_binds' => array_filter([$inFile, $this->autoJudge->judgeRuntimePath()]),
            'cpu_seconds' => $timeLimit,
            'file_size_kb' => $outputLimitKb,
            'max_processes' => (int) config('autojudge.run_max_processes', 256),
            'memory_kb' => ($memoryLimit + 64) * 1024,
        ];

        if ($this->autoJudge->isUsingBwrap()) {
            $sandboxedCmd = $this->autoJudge->wrapWithBwrap($commandWithRss, $workDir, $options);
        } else {
            $sandboxedCmd = 'bash -c ' . escapeshellarg("ulimit -t {$timeLimit}; " . $commandWithRss);
        }

        $cgroupName = 'poly_' . $exerciseId . '_' . $phase . '_' . $caseNumber . '_' . getmypid();
        $sandboxedCmd = $this->autoJudge->cgroup()->confine($sandboxedCmd, $cgroupName, $memoryLimit);

        $wallSeconds = $this->autoJudge->wallBackstopSeconds($timeLimit);
        $isTimeout = false;
        $signaled = null;
        $t0 = microtime(true);
        $processResult = null;

        try {
            $processResult = $this->autoJudge->runWithWallBackstop(
                Process::timeout($wallSeconds)
                    ->path($workDir)
                    ->env(['HOME' => $workDir, 'TMPDIR' => $workDir]),
                $sandboxedCmd,
                'execucao',
                $wallSeconds
            );
            $this->autoJudge->assertSandboxStarted($processResult);
        } catch (JudgeWallClockTimeoutException|LaravelProcessTimedOutException|SymfonyProcessTimedOutException) {
            $isTimeout = true;
        } catch (ProcessSignaledException $e) {
            $signaled = $e->getSignal();
        } finally {
            $cgroupUsage = $this->autoJudge->cgroup()->release($cgroupName);
        }
        $t1 = microtime(true);

        $measured = $this->autoJudge->measurement($rssPath);
        $wallSec = $measured['wall_seconds'] ?? max(0.001, $t1 - $t0);
        $cpuSec = $measured['cpu_seconds'] ?? $wallSec;
        $timeCase = round($cpuSec, 3);
        $peakRssKb = $measured['peak_rss_kb'];
        $memMb = $peakRssKb !== null ? round($peakRssKb / 1024, 1) : 12.0;

        $exitCode = $processResult !== null ? $processResult->exitCode() : ($isTimeout ? 137 : 1);

        // Resolve verdict following AutoJudgeService semantics
        if ($isTimeout || in_array($signaled, [9, 24], true) || in_array($exitCode, [137, 9, 152, 24], true)) {
            $verdict = 'TLE';
        } elseif ($this->autoJudge->cgroup()->exceeded($cgroupUsage, $memoryLimit, $exitCode === 0)
            || ($peakRssKb !== null && $peakRssKb > $memoryLimit * 1024)) {
            $verdict = 'MLE';
        } elseif ($exitCode === 153 || $exitCode === 25) {
            $verdict = 'RE';
        } elseif ($exitCode === 139 || $exitCode === 11) {
            $verdict = 'RE';
        } elseif ($exitCode !== 0) {
            $verdict = 'RTE';
        } else {
            $verdict = 'AC';
        }

        return [
            'verdict' => $verdict,
            'time' => $timeCase,
            'memory' => $memMb,
            'exit_code' => $exitCode,
        ];
    }

    /**
     * Compiles a C++ source file using g++ with -static and fallback without -static.
     *
     * @return array{success: bool, output: string}
     */
    protected function compileCpp(string $sourceFile, string $binaryFile, string $workDir): array
    {
        $includePath = base_path('resources/judge-runtime/include');

        // 1. First attempt: with -static
        $cmdStatic = [
            'g++',
            '-O2',
            '-std=c++20',
            '-static',
            '-I',
            $includePath,
            $sourceFile,
            '-o',
            $binaryFile,
        ];

        try {
            $result = Process::path($workDir)
                ->timeout(self::COMPILE_TIMEOUT_SECONDS)
                ->run($cmdStatic);

            if ($result->successful() && file_exists($binaryFile)) {
                @chmod($binaryFile, 0755);

                return [
                    'success' => true,
                    'output' => $result->output() ?: 'Compilation successful with -static.',
                ];
            }
        } catch (\Throwable) {
            // Fallback to non-static
        }

        // 2. Fallback attempt: without -static
        $cmdNoStatic = [
            'g++',
            '-O2',
            '-std=c++20',
            '-I',
            $includePath,
            $sourceFile,
            '-o',
            $binaryFile,
        ];

        try {
            $fallback = Process::path($workDir)
                ->timeout(self::COMPILE_TIMEOUT_SECONDS)
                ->run($cmdNoStatic);

            if ($fallback->successful() && file_exists($binaryFile)) {
                @chmod($binaryFile, 0755);

                return [
                    'success' => true,
                    'output' => $fallback->output() ?: 'Compilation successful without -static.',
                ];
            }

            $error = trim($fallback->errorOutput() ?: $fallback->output() ?: ($result->errorOutput() ?? ''));

            return [
                'success' => false,
                'output' => $error ?: 'Compilation failed.',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'output' => $e->getMessage(),
            ];
        }
    }

    /**
     * Constructs a standard error response array when calibration cannot proceed.
     *
     * @param  string  $message
     * @param  int  $totalTests
     * @param  array<int, string>  $warnings
     * @return array<string, mixed>
     */
    protected function buildErrorResult(string $message, int $totalTests = 0, array $warnings = []): array
    {
        if (empty($warnings)) {
            $warnings = [$message];
        }

        return [
            'status' => 'error',
            'message' => $message,
            'summary' => [
                'total_tests' => $totalTests,
                'accept_passed' => false,
                'max_time_seconds' => 0.0,
                'max_memory_mb' => 0.0,
                'tle_calibrated' => null,
                'wrong_calibrated' => null,
                'warnings' => $warnings,
            ],
            'cases' => [],
            'language_limits' => [],
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
}
