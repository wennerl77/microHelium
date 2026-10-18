<?php

namespace App\Services\Polygon;

use App\Models\Contest;
use App\Models\ContestLog;
use App\Models\PolygonExercise;
use App\Models\Problem;
use App\Models\ProblemBank;
use App\Models\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class PolygonPublishService
{
    public function __construct(
        protected LanguageLimitDerivationService $limitDerivationService
    ) {}

    /**
     * Publishes a calibrated Polygon exercise either into a contest or into the general problem bank.
     *
     * @param  PolygonExercise  $exercise
     * @param  string|null  $forceTarget  Optional override: 'contest' or 'bank'
     * @return array{
     *     success: bool,
     *     target: string,
     *     problem_id?: int,
     *     problem?: Problem,
     *     bank_code?: string,
     *     problem_bank?: ProblemBank
     * }
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function publish(PolygonExercise $exercise, ?string $forceTarget = null): array
    {
        // 1. Strict validation: Exercise must be calibrated with ACCEPT passed
        $isCalibrated = ($exercise->status === PolygonExercise::STATUS_CALIBRATED)
            || (! empty($exercise->calibration_results['summary']['accept_passed']));

        if (! $isCalibrated) {
            throw new InvalidArgumentException(
                "O exercício [{$exercise->id}] precisa estar calibrado com uma solução ACCEPT válida antes de ser publicado."
            );
        }

        // 2. Strict validation: Tests directory must exist and contain matching .in and .out files
        $tempDir = $exercise->getTempDirectory();
        $testsDir = $tempDir . '/tests';

        if (! is_dir($testsDir)) {
            throw new RuntimeException(
                "O diretório de testes [{$testsDir}] não existe no workspace do exercício."
            );
        }

        $inFiles = glob($testsDir . '/*.in') ?: [];
        if (empty($inFiles)) {
            throw new RuntimeException(
                "Nenhum caso de teste (.in) foi encontrado no diretório [{$testsDir}]."
            );
        }

        natsort($inFiles);
        $inFiles = array_values($inFiles);

        $testCasesData = [];
        foreach ($inFiles as $inFile) {
            $outFile = preg_replace('/\.in$/', '.out', $inFile);
            if (! file_exists($outFile)) {
                throw new RuntimeException(
                    "Arquivo de saída oficial (.out) correspondente não encontrado para o teste: [{$inFile}]."
                );
            }

            $testCasesData[] = [
                'in_file' => $inFile,
                'out_file' => $outFile,
            ];
        }

        // 3. Resolve sample flags
        $explicitSamples = [];

        // Parse manual_tests if present
        $manualTests = $exercise->manual_tests;
        if (is_string($manualTests)) {
            $manualTests = json_decode($manualTests, true) ?: [];
        }
        $manualTests = is_array($manualTests) ? array_values($manualTests) : [];

        foreach ($manualTests as $idx => $m) {
            if (! empty($m['is_sample'])) {
                $explicitSamples[$idx + 1] = true;
            }
        }

        // Check calibration_results cases if present
        if (! empty($exercise->calibration_results['cases']) && is_array($exercise->calibration_results['cases'])) {
            foreach ($exercise->calibration_results['cases'] as $case) {
                if (! empty($case['is_sample']) && ! empty($case['number'])) {
                    $explicitSamples[(int) $case['number']] = true;
                }
            }
        }

        $hasAnySampleFlag = ! empty($explicitSamples);

        // Determine destination target
        $target = $forceTarget ?? ($exercise->contest_id ? 'contest' : 'bank');

        return DB::transaction(function () use ($exercise, $testCasesData, $hasAnySampleFlag, $explicitSamples, $target) {
            if ($target === 'contest' && $exercise->contest_id) {
                return $this->publishToContest($exercise, $testCasesData, $hasAnySampleFlag, $explicitSamples);
            }

            return $this->publishToBank($exercise, $testCasesData, $hasAnySampleFlag, $explicitSamples);
        });
    }

    /**
     * Scenario A: Publish exercise into an existing Contest.
     */
    protected function publishToContest(
        PolygonExercise $exercise,
        array $testCasesData,
        bool $hasAnySampleFlag,
        array $explicitSamples
    ): array {
        $contest = Contest::findOrFail($exercise->contest_id);

        $sortOrder = Problem::where('contest_id', $exercise->contest_id)->count();

        // Build consolidated markdown description
        $description = (string) ($exercise->description ?? '');
        if (filled($exercise->input_description)) {
            $description .= (filled($description) ? "\n\n" : '') . "## Entrada\n" . $exercise->input_description;
        }
        if (filled($exercise->output_description)) {
            $description .= (filled($description) ? "\n\n" : '') . "## Saída\n" . $exercise->output_description;
        }
        if (filled($exercise->notes)) {
            $description .= (filled($description) ? "\n\n" : '') . "## Notas\n" . $exercise->notes;
        }

        // Create Problem record
        $problem = Problem::create([
            'contest_id' => $exercise->contest_id,
            'short_name' => $exercise->short_name,
            'name' => $exercise->name,
            'basename' => $exercise->basename,
            'description' => $description,
            'time_limit' => $exercise->time_limit ?? 1,
            'memory_limit' => $exercise->memory_limit ?? 256,
            'output_limit' => $exercise->output_limit ?? 1024,
            'color_name' => $exercise->color_name ?? 'Azul',
            'color_hex' => $exercise->color_hex ?? '#3B82F6',
            'auto_judge' => true,
            'is_fake' => false,
            'sort_order' => $sortOrder,
        ]);

        // Copy test cases to permanent problem storage and record in test_cases table
        $contestId = $exercise->contest_id;
        $basename = $exercise->basename;

        $inputDirRel = "problems/{$contestId}/{$basename}/input";
        $outputDirRel = "problems/{$contestId}/{$basename}/output";

        Storage::disk('local')->makeDirectory($inputDirRel);
        Storage::disk('local')->makeDirectory($outputDirRel);

        foreach ($testCasesData as $index => $tc) {
            $i = $index + 1;
            $inputRel = "{$inputDirRel}/{$i}";
            $outputRel = "{$outputDirRel}/{$i}";

            $destIn = Storage::disk('local')->path($inputRel);
            $destOut = Storage::disk('local')->path($outputRel);

            copy($tc['in_file'], $destIn);
            copy($tc['out_file'], $destOut);

            $isSample = $hasAnySampleFlag ? ! empty($explicitSamples[$i]) : ($i === 1);

            TestCase::create([
                'problem_id' => $problem->id,
                'number' => $i,
                'input_file' => $inputRel,
                'output_file' => $outputRel,
                'input_hash' => hash_file('sha256', $tc['in_file']),
                'output_hash' => hash_file('sha256', $tc['out_file']),
                'is_sample' => $isSample,
            ]);
        }

        // Apply derived language limits
        $derived = $this->limitDerivationService->deriveForExercise($exercise);
        $this->limitDerivationService->applyLimitsToProblem($problem, $derived);

        // Record in ContestLog
        $userId = null;
        if ($exercise->created_by && DB::table('users')->where('user_id', $exercise->created_by)->exists()) {
            $userId = $exercise->created_by;
        } elseif (auth()->check()) {
            $userId = auth()->id();
        }

        ContestLog::create([
            'contest_id' => $contest->id,
            'site_id' => null,
            'user_id' => $userId,
            'ip_address' => request()?->ip() ?? '127.0.0.1',
            'type' => 'info',
            'message' => "Problema {$problem->short_name} ({$problem->name}) publicado via Polygon",
            'context' => [
                'exercise_id' => $exercise->id,
                'problem_id' => $problem->id,
                'basename' => $problem->basename,
            ],
        ]);

        // Update PolygonExercise
        $exercise->status = PolygonExercise::STATUS_PUBLISHED;
        $exercise->published_problem_id = $problem->id;
        $exercise->save();

        return [
            'success' => true,
            'target' => 'contest',
            'problem_id' => $problem->id,
            'problem' => $problem,
        ];
    }

    /**
     * Scenario B: Publish exercise into the global ProblemBank.
     */
    protected function publishToBank(
        PolygonExercise $exercise,
        array $testCasesData,
        bool $hasAnySampleFlag,
        array $explicitSamples
    ): array {
        // Resolve sample input and output from first sample test (or first test)
        $sampleInFile = null;
        $sampleOutFile = null;
        foreach ($testCasesData as $index => $tc) {
            $caseNum = $index + 1;
            $isSample = $hasAnySampleFlag ? ! empty($explicitSamples[$caseNum]) : ($caseNum === 1);
            if ($isSample) {
                $sampleInFile = $tc['in_file'];
                $sampleOutFile = $tc['out_file'];
                break;
            }
        }

        if (! $sampleInFile) {
            $sampleInFile = $testCasesData[0]['in_file'];
            $sampleOutFile = $testCasesData[0]['out_file'];
        }

        $sampleInput = (string) file_get_contents($sampleInFile);
        $sampleOutput = (string) file_get_contents($sampleOutFile);

        $code = ! empty($exercise->basename) ? $exercise->basename : Str::slug($exercise->short_name);
        $code = substr($code, 0, 50);

        // Create or update ProblemBank entry
        $bankProblem = ProblemBank::updateOrCreate(
            ['code' => $code],
            [
                'name' => $exercise->name,
                'description' => (string) ($exercise->description ?? ''),
                'input_description' => (string) ($exercise->input_description ?? ''),
                'output_description' => (string) ($exercise->output_description ?? ''),
                'notes' => $exercise->notes,
                'time_limit' => $exercise->time_limit ?? 1,
                'memory_limit' => $exercise->memory_limit ?? 256,
                'sample_input' => $sampleInput,
                'sample_output' => $sampleOutput,
                'source' => 'Polygon',
                'is_active' => true,
            ]
        );

        // Copy full package of N test cases to storage/app/problem_bank/{code}/input/ and output/
        $bankInputDirRel = "problem_bank/{$bankProblem->code}/input";
        $bankOutputDirRel = "problem_bank/{$bankProblem->code}/output";

        Storage::disk('local')->makeDirectory($bankInputDirRel);
        Storage::disk('local')->makeDirectory($bankOutputDirRel);

        foreach ($testCasesData as $index => $tc) {
            $i = $index + 1;
            $inputRel = "{$bankInputDirRel}/{$i}";
            $outputRel = "{$bankOutputDirRel}/{$i}";

            copy($tc['in_file'], Storage::disk('local')->path($inputRel));
            copy($tc['out_file'], Storage::disk('local')->path($outputRel));
        }

        // Update PolygonExercise
        $exercise->status = PolygonExercise::STATUS_PUBLISHED;
        $exercise->published_bank_code = $bankProblem->code;
        $exercise->save();

        return [
            'success' => true,
            'target' => 'bank',
            'bank_code' => $bankProblem->code,
            'problem_bank' => $bankProblem,
        ];
    }
}
