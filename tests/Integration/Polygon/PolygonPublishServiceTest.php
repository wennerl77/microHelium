<?php

namespace Tests\Integration\Polygon;

use App\Http\Controllers\Backend\ContestWizardController;
use App\Models\Contest;
use App\Models\ContestLog;
use App\Models\Language;
use App\Models\PolygonExercise;
use App\Models\Problem;
use App\Models\ProblemBank;
use App\Models\ProblemLanguageLimit;
use App\Models\TestCase as ProblemTestCase;
use App\Services\Polygon\LanguageLimitDerivationService;
use App\Services\Polygon\PolygonPublishService;
use Helium\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class PolygonPublishServiceTest extends TestCase
{
    use RefreshDatabase;

    private PolygonPublishService $publishService;

    private array $createdExerciseIds = [];

    private array $createdContestIds = [];

    private array $createdBankCodes = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->publishService = new PolygonPublishService(
            new LanguageLimitDerivationService()
        );
    }

    protected function tearDown(): void
    {
        // Clean up temporary workspace directories
        foreach ($this->createdExerciseIds as $id) {
            $tempDir = storage_path("app/temp/polygon_{$id}");
            if (is_dir($tempDir)) {
                File::deleteDirectory($tempDir);
            }
        }

        // Clean up contest storage directories
        foreach ($this->createdContestIds as $contestId) {
            $contestDir = storage_path("app/problems/{$contestId}");
            if (is_dir($contestDir)) {
                File::deleteDirectory($contestDir);
            }
        }

        // Clean up problem bank storage directories
        foreach ($this->createdBankCodes as $code) {
            $bankDir = storage_path("app/problem_bank/{$code}");
            if (is_dir($bankDir)) {
                File::deleteDirectory($bankDir);
            }
        }

        parent::tearDown();
    }

    public function test_publish_scenario_a_contest_creates_problem_test_cases_storage_and_limits(): void
    {
        $user = User::factory()->create();
        $contest = Contest::factory()->create();
        $this->createdContestIds[] = $contest->id;

        // Create languages for contest to verify derived language limits
        Language::factory()->create([
            'contest_id' => $contest->id,
            'name' => 'C++ (G++ 15)',
            'extension' => 'cpp_gpp13',
            'is_active' => true,
        ]);
        Language::factory()->create([
            'contest_id' => $contest->id,
            'name' => 'Python 3',
            'extension' => 'py3',
            'is_active' => true,
        ]);
        Language::factory()->create([
            'contest_id' => $contest->id,
            'name' => 'Java 21',
            'extension' => 'java21',
            'is_active' => true,
        ]);

        $exercise = PolygonExercise::factory()->create([
            'contest_id' => $contest->id,
            'created_by' => $user->user_id,
            'name' => 'Soma dos Elementos',
            'short_name' => 'A',
            'basename' => 'soma-elementos',
            'description' => 'Calcule a soma de dois números inteiros.',
            'input_description' => 'Dois inteiros A e B.',
            'output_description' => 'A soma A + B.',
            'notes' => '1 <= A, B <= 10^9',
            'time_limit' => 2,
            'memory_limit' => 512,
            'output_limit' => 2048,
            'color_name' => 'Verde',
            'color_hex' => '#22C55E',
            'status' => PolygonExercise::STATUS_CALIBRATED,
            'calibration_results' => [
                'summary' => [
                    'total_tests' => 3,
                    'accept_passed' => true,
                ],
                'cases' => [
                    ['number' => 1, 'is_sample' => true],
                    ['number' => 2, 'is_sample' => false],
                    ['number' => 3, 'is_sample' => false],
                ],
            ],
            'manual_tests' => [
                ['number' => 1, 'input' => "2 3\n", 'output' => "5\n", 'is_sample' => true],
            ],
        ]);
        $this->createdExerciseIds[] = $exercise->id;

        // Setup test files in exercise temp directory
        $testsDir = $exercise->getTempDirectory() . '/tests';
        File::makeDirectory($testsDir, 0755, true, true);

        file_put_contents($testsDir . '/001.in', "2 3\n");
        file_put_contents($testsDir . '/001.out', "5\n");
        file_put_contents($testsDir . '/002.in', "10 20\n");
        file_put_contents($testsDir . '/002.out', "30\n");
        file_put_contents($testsDir . '/003.in', "100 200\n");
        file_put_contents($testsDir . '/003.out', "300\n");

        // Act
        $result = $this->publishService->publish($exercise);

        // Assert return payload
        $this->assertTrue($result['success']);
        $this->assertSame('contest', $result['target']);
        $this->assertNotNull($result['problem_id']);
        $this->assertInstanceOf(Problem::class, $result['problem']);

        $problem = $result['problem'];
        $this->assertSame($contest->id, $problem->contest_id);
        $this->assertSame('A', $problem->short_name);
        $this->assertSame('Soma dos Elementos', $problem->name);
        $this->assertSame('soma-elementos', $problem->basename);
        $this->assertSame(2, $problem->time_limit);
        $this->assertSame(512, $problem->memory_limit);
        $this->assertSame(2048, $problem->output_limit);
        $this->assertSame('Verde', $problem->color_name);
        $this->assertSame('#22C55E', $problem->color_hex);
        $this->assertSame(0, $problem->sort_order);
        $this->assertTrue($problem->auto_judge);
        $this->assertFalse($problem->is_fake);

        // Verify consolidated description
        $this->assertStringContainsString('Calcule a soma de dois números inteiros.', $problem->description);
        $this->assertStringContainsString("## Entrada\nDois inteiros A e B.", $problem->description);
        $this->assertStringContainsString("## Saída\nA soma A + B.", $problem->description);
        $this->assertStringContainsString("## Notas\n1 <= A, B <= 10^9", $problem->description);

        // Assert test_cases records
        $testCases = ProblemTestCase::where('problem_id', $problem->id)->orderBy('number')->get();
        $this->assertCount(3, $testCases);

        $this->assertSame(1, $testCases[0]->number);
        $this->assertTrue($testCases[0]->is_sample);
        $this->assertSame('problems/' . $contest->id . '/soma-elementos/input/1', $testCases[0]->input_file);
        $this->assertSame('problems/' . $contest->id . '/soma-elementos/output/1', $testCases[0]->output_file);
        $this->assertSame(hash('sha256', "2 3\n"), $testCases[0]->input_hash);
        $this->assertSame(hash('sha256', "5\n"), $testCases[0]->output_hash);

        $this->assertSame(2, $testCases[1]->number);
        $this->assertFalse($testCases[1]->is_sample);

        $this->assertSame(3, $testCases[2]->number);
        $this->assertFalse($testCases[2]->is_sample);

        // Assert physical files in Storage disk('local')
        $this->assertTrue(Storage::disk('local')->exists("problems/{$contest->id}/soma-elementos/input/1"));
        $this->assertTrue(Storage::disk('local')->exists("problems/{$contest->id}/soma-elementos/output/1"));
        $this->assertSame("2 3\n", Storage::disk('local')->get("problems/{$contest->id}/soma-elementos/input/1"));
        $this->assertSame("5\n", Storage::disk('local')->get("problems/{$contest->id}/soma-elementos/output/1"));

        $this->assertTrue(Storage::disk('local')->exists("problems/{$contest->id}/soma-elementos/input/2"));
        $this->assertTrue(Storage::disk('local')->exists("problems/{$contest->id}/soma-elementos/output/2"));
        $this->assertSame("10 20\n", Storage::disk('local')->get("problems/{$contest->id}/soma-elementos/input/2"));
        $this->assertSame("30\n", Storage::disk('local')->get("problems/{$contest->id}/soma-elementos/output/2"));

        $this->assertTrue(Storage::disk('local')->exists("problems/{$contest->id}/soma-elementos/input/3"));
        $this->assertTrue(Storage::disk('local')->exists("problems/{$contest->id}/soma-elementos/output/3"));

        // Assert derived language limits
        $limits = ProblemLanguageLimit::where('problem_id', $problem->id)->get();
        $this->assertGreaterThanOrEqual(1, $limits->count());

        // Python limit should be 3x base time = 6s
        $pyLimit = $limits->first(fn ($l) => $l->language->extension === 'py3');
        if ($pyLimit) {
            $this->assertSame(6, $pyLimit->time_limit);
            $this->assertSame(512, $pyLimit->memory_limit);
        }

        // Assert ContestLog created
        $log = ContestLog::where('contest_id', $contest->id)->latest()->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('Soma dos Elementos', $log->message);
        $this->assertSame($user->user_id, $log->user_id);

        // Assert exercise status updated
        $freshExercise = $exercise->fresh();
        $this->assertSame(PolygonExercise::STATUS_PUBLISHED, $freshExercise->status);
        $this->assertSame($problem->id, $freshExercise->published_problem_id);
    }

    public function test_publish_scenario_b_bank_creates_problem_bank_and_persists_full_package(): void
    {
        $user = User::factory()->create();

        $exercise = PolygonExercise::factory()->create([
            'contest_id' => null,
            'created_by' => $user->user_id,
            'name' => 'Multiplicação de Matrizes',
            'short_name' => 'MATMUL',
            'basename' => 'mat-mul',
            'description' => 'Multiplique duas matrizes N x N.',
            'input_description' => 'A dimensão N e os elementos das matrizes.',
            'output_description' => 'A matriz resultante.',
            'notes' => '1 <= N <= 100',
            'time_limit' => 3,
            'memory_limit' => 512,
            'status' => PolygonExercise::STATUS_CALIBRATED,
            'calibration_results' => [
                'summary' => [
                    'total_tests' => 2,
                    'accept_passed' => true,
                ],
            ],
            'manual_tests' => [
                ['number' => 1, 'input' => "2\n1 2\n3 4\n", 'output' => "7 10\n15 22\n", 'is_sample' => true],
            ],
        ]);
        $this->createdExerciseIds[] = $exercise->id;
        $this->createdBankCodes[] = 'mat-mul';

        // Setup test files in workspace
        $testsDir = $exercise->getTempDirectory() . '/tests';
        File::makeDirectory($testsDir, 0755, true, true);

        file_put_contents($testsDir . '/001.in', "2\n1 2\n3 4\n");
        file_put_contents($testsDir . '/001.out', "7 10\n15 22\n");
        file_put_contents($testsDir . '/002.in', "1\n5\n");
        file_put_contents($testsDir . '/002.out', "25\n");

        // Act
        $result = $this->publishService->publish($exercise);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertSame('bank', $result['target']);
        $this->assertSame('mat-mul', $result['bank_code']);
        $this->assertInstanceOf(ProblemBank::class, $result['problem_bank']);

        $bankProblem = $result['problem_bank'];
        $this->assertSame('mat-mul', $bankProblem->code);
        $this->assertSame('Multiplicação de Matrizes', $bankProblem->name);
        $this->assertSame('Multiplique duas matrizes N x N.', $bankProblem->description);
        $this->assertSame('A dimensão N e os elementos das matrizes.', $bankProblem->input_description);
        $this->assertSame('A matriz resultante.', $bankProblem->output_description);
        $this->assertSame('1 <= N <= 100', $bankProblem->notes);
        $this->assertSame(3, $bankProblem->time_limit);
        $this->assertSame(512, $bankProblem->memory_limit);
        $this->assertSame("2\n1 2\n3 4\n", $bankProblem->sample_input);
        $this->assertSame("7 10\n15 22\n", $bankProblem->sample_output);
        $this->assertTrue($bankProblem->is_active);

        // Assert test cases package saved in storage/app/problem_bank/{code}/
        $this->assertTrue(Storage::disk('local')->exists('problem_bank/mat-mul/input/1'));
        $this->assertTrue(Storage::disk('local')->exists('problem_bank/mat-mul/output/1'));
        $this->assertSame("2\n1 2\n3 4\n", Storage::disk('local')->get('problem_bank/mat-mul/input/1'));
        $this->assertSame("7 10\n15 22\n", Storage::disk('local')->get('problem_bank/mat-mul/output/1'));

        $this->assertTrue(Storage::disk('local')->exists('problem_bank/mat-mul/input/2'));
        $this->assertTrue(Storage::disk('local')->exists('problem_bank/mat-mul/output/2'));
        $this->assertSame("1\n5\n", Storage::disk('local')->get('problem_bank/mat-mul/input/2'));
        $this->assertSame("25\n", Storage::disk('local')->get('problem_bank/mat-mul/output/2'));

        // Assert exercise status updated
        $fresh = $exercise->fresh();
        $this->assertSame(PolygonExercise::STATUS_PUBLISHED, $fresh->status);
        $this->assertSame('mat-mul', $fresh->published_bank_code);
    }

    public function test_contest_wizard_imports_full_package_when_bank_storage_exists(): void
    {
        $code = 'fatorial-simples';
        $this->createdBankCodes[] = $code;

        $bankProblem = ProblemBank::create([
            'code' => $code,
            'name' => 'Fatorial Simples',
            'description' => 'Calcule o fatorial de N.',
            'input_description' => 'Um inteiro N.',
            'output_description' => 'O valor N!.',
            'notes' => '0 <= N <= 12',
            'time_limit' => 1,
            'memory_limit' => 256,
            'sample_input' => "3\n",
            'sample_output' => "6\n",
            'is_active' => true,
        ]);

        // Place 3 full test cases in storage/app/problem_bank/{code}/
        Storage::disk('local')->put("problem_bank/{$code}/input/1", "3\n");
        Storage::disk('local')->put("problem_bank/{$code}/output/1", "6\n");
        Storage::disk('local')->put("problem_bank/{$code}/input/2", "4\n");
        Storage::disk('local')->put("problem_bank/{$code}/output/2", "24\n");
        Storage::disk('local')->put("problem_bank/{$code}/input/3", "5\n");
        Storage::disk('local')->put("problem_bank/{$code}/output/3", "120\n");

        $contest = Contest::factory()->create();
        $this->createdContestIds[] = $contest->id;

        // Act: import from bank via ContestWizardController
        $count = ContestWizardController::addProblemsFromBank($contest->id, [$bankProblem->id]);
        $this->assertSame(1, $count);

        // Verify Problem created
        $problem = Problem::where('contest_id', $contest->id)->where('basename', $code)->first();
        $this->assertNotNull($problem);

        // Verify all 3 test cases were imported (not just the 1 sample!)
        $testCases = ProblemTestCase::where('problem_id', $problem->id)->orderBy('number')->get();
        $this->assertCount(3, $testCases);

        $this->assertSame(1, $testCases[0]->number);
        $this->assertTrue($testCases[0]->is_sample);
        $this->assertSame(hash('sha256', "3\n"), $testCases[0]->input_hash);
        $this->assertSame(hash('sha256', "6\n"), $testCases[0]->output_hash);

        $this->assertSame(2, $testCases[1]->number);
        $this->assertFalse($testCases[1]->is_sample);

        $this->assertSame(3, $testCases[2]->number);
        $this->assertFalse($testCases[2]->is_sample);

        // Verify files were placed in contest storage
        $this->assertTrue(Storage::disk('local')->exists("problems/{$contest->id}/{$code}/input/1"));
        $this->assertTrue(Storage::disk('local')->exists("problems/{$contest->id}/{$code}/input/2"));
        $this->assertTrue(Storage::disk('local')->exists("problems/{$contest->id}/{$code}/input/3"));
        $this->assertSame("120\n", Storage::disk('local')->get("problems/{$contest->id}/{$code}/output/3"));
    }

    public function test_publish_throws_invalid_argument_exception_if_not_calibrated(): void
    {
        $exercise = PolygonExercise::factory()->create([
            'status' => PolygonExercise::STATUS_DRAFT,
            'calibration_results' => null,
        ]);
        $this->createdExerciseIds[] = $exercise->id;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('precisa estar calibrado');

        $this->publishService->publish($exercise);
    }

    public function test_publish_throws_runtime_exception_if_tests_directory_is_missing(): void
    {
        $exercise = PolygonExercise::factory()->create([
            'status' => PolygonExercise::STATUS_CALIBRATED,
            'calibration_results' => [
                'summary' => ['accept_passed' => true],
            ],
        ]);
        $this->createdExerciseIds[] = $exercise->id;

        $tempDir = $exercise->getTempDirectory();
        if (is_dir($tempDir)) {
            File::deleteDirectory($tempDir);
        }

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('não existe no workspace');

        $this->publishService->publish($exercise);
    }

    public function test_publish_throws_runtime_exception_if_matching_out_file_is_missing(): void
    {
        $exercise = PolygonExercise::factory()->create([
            'status' => PolygonExercise::STATUS_CALIBRATED,
            'calibration_results' => [
                'summary' => ['accept_passed' => true],
            ],
        ]);
        $this->createdExerciseIds[] = $exercise->id;

        $testsDir = $exercise->getTempDirectory() . '/tests';
        File::makeDirectory($testsDir, 0755, true, true);

        file_put_contents($testsDir . '/001.in', "input\n");
        // Intentionally omit 001.out

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Arquivo de saída oficial (.out) correspondente não encontrado');

        $this->publishService->publish($exercise);
    }
}
