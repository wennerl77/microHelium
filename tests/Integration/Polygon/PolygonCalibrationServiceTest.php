<?php

namespace Tests\Integration\Polygon;

use App\Models\PolygonExercise;
use App\Services\Polygon\LanguageLimitDerivationService;
use App\Services\Polygon\PolygonCalibrationService;
use App\Services\Polygon\PolygonGeneratorService;
use Helium\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolygonCalibrationServiceTest extends TestCase
{
    use RefreshDatabase;

    private PolygonGeneratorService $generatorService;

    private PolygonCalibrationService $calibrationService;

    private ?PolygonExercise $exercise = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ensureGppAvailable();

        $this->generatorService = new PolygonGeneratorService();
        $this->calibrationService = new PolygonCalibrationService(
            new LanguageLimitDerivationService()
        );
    }

    protected function tearDown(): void
    {
        if ($this->exercise instanceof PolygonExercise) {
            $this->calibrationService->cleanTempDirectory($this->exercise);
        }

        parent::tearDown();
    }

    public function test_calibrate_exercise_with_accept_tle_and_wrong_successfully(): void
    {
        $user = User::factory()->create();

        $this->exercise = PolygonExercise::factory()->create([
            'created_by' => $user->user_id,
            'time_limit' => 1,
            'memory_limit' => 256,
            'generator_source' => <<<'CPP'
#include "testlib.h"
#include <iostream>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    long long n = atoll(argv[1]);
    long long m = atoll(argv[2]);
    cout << rnd.next(1LL, n) << " " << rnd.next(1LL, m) << endl;
    return 0;
}
CPP,
            'generator_script' => "gen 10 20 > $\ngen 100 200 > $\n",
            'manual_tests' => [
                [
                    'number' => 1,
                    'input' => "4 3\n",
                    'output' => "PAR\n1\n",
                    'is_sample' => true,
                ],
            ],
            'solution_accept' => <<<'CPP'
#include <iostream>

using namespace std;

int main() {
    long long n, m;
    if (cin >> n >> m) {
        cout << (n % 2 == 0 ? "PAR" : "IMPAR") << endl;
        cout << (n % m) << endl;
    }
    return 0;
}
CPP,
            'solution_tle' => <<<'CPP'
#include <iostream>

using namespace std;

int main() {
    long long n, m;
    if (cin >> n >> m) {
        // Pass small inputs, loop infinitely on larger ones
        if (n > 5) {
            while (true) {}
        }
        cout << (n % 2 == 0 ? "PAR" : "IMPAR") << endl;
        cout << (n % m) << endl;
    }
    return 0;
}
CPP,
            'solution_wrong' => <<<'CPP'
#include <iostream>

using namespace std;

int main() {
    long long n, m;
    if (cin >> n >> m) {
        // Intentionally wrong logic
        cout << "ERRADO" << endl;
        cout << (n + m) << endl;
    }
    return 0;
}
CPP,
        ]);

        // 1. Generate tests (1 manual + 2 script = 3 tests)
        $genResult = $this->generatorService->generateTests($this->exercise);
        $this->assertTrue($genResult['success'], 'Falha na geração de testes: ' . ($genResult['error'] ?? ''));
        $this->assertSame(3, $genResult['total_tests']);

        // 2. Calibrate exercise
        $result = $this->calibrationService->calibrate($this->exercise);

        // 3. Verify general status and summary
        $this->assertSame(PolygonExercise::STATUS_CALIBRATED, $result['status']);
        $this->assertSame(3, $result['summary']['total_tests']);
        $this->assertTrue($result['summary']['accept_passed'], 'ACCEPT deveria passar em todos os testes.');
        $this->assertTrue($result['summary']['tle_calibrated'], 'TLE deveria ser calibrado.');
        $this->assertTrue($result['summary']['wrong_calibrated'], 'WRONG deveria ser calibrado.');
        $this->assertGreaterThan(0.0, $result['summary']['max_memory_mb']);

        // 4. Verify test cases detail
        $this->assertCount(3, $result['cases']);

        $testsDir = $this->exercise->getTempDirectory() . '/tests';

        foreach ($result['cases'] as $case) {
            $num = $case['number'];
            $baseName = sprintf('%03d', $num);
            $expectedOut = $testsDir . '/' . $baseName . '.out';

            // Verify .out file was created and is non-empty
            $this->assertFileExists($expectedOut, "Arquivo .out do caso {$num} deve existir.");
            $this->assertGreaterThan(0, $case['output_bytes']);
            $this->assertGreaterThan(0, (int) filesize($expectedOut));

            // Verify ACCEPT verdict
            $this->assertSame('AC', $case['accept']['verdict'], "Caso {$num} deve ter AC no ACCEPT.");
            $this->assertNotNull($case['accept']['time']);
            $this->assertNotNull($case['accept']['memory']);

            // Verify TLE detail
            $this->assertNotNull($case['tle']);
            $this->assertContains($case['tle']['verdict'], ['AC', 'TLE']);

            // Verify WRONG detail
            $this->assertNotNull($case['wrong']);
            $this->assertSame('WA', $case['wrong']['verdict']);
            $this->assertFalse($case['wrong']['match']);
        }

        // Case 1 is manual test -> is_sample must be true
        $this->assertTrue($result['cases'][0]['is_sample']);
        // Case 2 and 3 are script tests -> is_sample must be false
        $this->assertFalse($result['cases'][1]['is_sample']);
        $this->assertFalse($result['cases'][2]['is_sample']);

        // Verify that at least one case in TLE had TLE verdict
        $tleVerdicts = array_column(array_column($result['cases'], 'tle'), 'verdict');
        $this->assertContains('TLE', $tleVerdicts, 'Ao menos um caso deve ter estourado o tempo na solução TLE.');

        // 5. Verify language limits derivation
        $this->assertNotEmpty($result['language_limits']);
        $languages = array_column($result['language_limits'], 'language');
        $this->assertContains('cpp', $languages);
        $this->assertContains('python', $languages);

        // 6. Verify persistence in DB
        $this->exercise->refresh();
        $this->assertSame(PolygonExercise::STATUS_CALIBRATED, $this->exercise->status);
        $this->assertIsArray($this->exercise->calibration_results);
        $this->assertSame(PolygonExercise::STATUS_CALIBRATED, $this->exercise->calibration_results['status']);
        $this->assertTrue($this->exercise->calibration_results['summary']['accept_passed']);
    }

    public function test_calibrate_returns_error_when_no_tests_exist(): void
    {
        $user = User::factory()->create();

        $this->exercise = PolygonExercise::factory()->create([
            'created_by' => $user->user_id,
            'solution_accept' => 'int main() { return 0; }',
        ]);

        $result = $this->calibrationService->calibrate($this->exercise);

        $this->assertSame('error', $result['status']);
        $this->assertFalse($result['summary']['accept_passed']);
        $this->assertSame(0, $result['summary']['total_tests']);
        $this->assertStringContainsString('Nenhum', $result['message']);
    }

    public function test_calibrate_returns_error_when_solution_accept_is_missing(): void
    {
        $user = User::factory()->create();

        $this->exercise = PolygonExercise::factory()->create([
            'created_by' => $user->user_id,
            'solution_accept' => '',
            'manual_tests' => [
                ['number' => 1, 'input' => "10 20\n", 'output' => "30\n"],
            ],
        ]);

        $this->generatorService->generateTests($this->exercise);

        $result = $this->calibrationService->calibrate($this->exercise);

        $this->assertSame('error', $result['status']);
        $this->assertFalse($result['summary']['accept_passed']);
        $this->assertStringContainsString('obrigatória', $result['message']);
    }

    public function test_calibrate_returns_error_when_accept_fails_compilation(): void
    {
        $user = User::factory()->create();

        $this->exercise = PolygonExercise::factory()->create([
            'created_by' => $user->user_id,
            'solution_accept' => 'this is totally invalid c++ code {{{',
            'manual_tests' => [
                ['number' => 1, 'input' => "10 20\n", 'output' => "30\n"],
            ],
        ]);

        $this->generatorService->generateTests($this->exercise);

        $result = $this->calibrationService->calibrate($this->exercise);

        $this->assertSame('error', $result['status']);
        $this->assertFalse($result['summary']['accept_passed']);
        $this->assertStringContainsString('compilação', $result['message']);
        $this->assertNotEmpty($result['summary']['warnings']);
    }

    public function test_calibrate_warns_when_tle_solution_passes_all_tests(): void
    {
        $user = User::factory()->create();

        $this->exercise = PolygonExercise::factory()->create([
            'created_by' => $user->user_id,
            'solution_accept' => <<<'CPP'
#include <iostream>
using namespace std;
int main() {
    long long n, m;
    if (cin >> n >> m) cout << (n + m) << endl;
    return 0;
}
CPP,
            'solution_tle' => <<<'CPP'
#include <iostream>
using namespace std;
int main() {
    long long n, m;
    // Fast code that passes without TLE
    if (cin >> n >> m) cout << (n + m) << endl;
    return 0;
}
CPP,
            'manual_tests' => [
                ['number' => 1, 'input' => "10 20\n", 'output' => "30\n"],
            ],
        ]);

        $this->generatorService->generateTests($this->exercise);

        $result = $this->calibrationService->calibrate($this->exercise);

        $this->assertSame(PolygonExercise::STATUS_CALIBRATED, $result['status']);
        $this->assertTrue($result['summary']['accept_passed']);
        $this->assertFalse($result['summary']['tle_calibrated'], 'TLE calibrado deve ser falso pois passou.');

        $warningFound = false;
        foreach ($result['summary']['warnings'] as $warning) {
            if (str_contains($warning, 'solução TLE passou em todos os testes')) {
                $warningFound = true;
                break;
            }
        }
        $this->assertTrue($warningFound, 'Deve incluir aviso sobre a solução TLE passar em todos os testes.');
    }

    public function test_calibrate_warns_when_wrong_solution_matches_all_tests(): void
    {
        $user = User::factory()->create();

        $this->exercise = PolygonExercise::factory()->create([
            'created_by' => $user->user_id,
            'solution_accept' => <<<'CPP'
#include <iostream>
using namespace std;
int main() {
    long long n, m;
    if (cin >> n >> m) cout << (n + m) << endl;
    return 0;
}
CPP,
            'solution_wrong' => <<<'CPP'
#include <iostream>
using namespace std;
int main() {
    long long n, m;
    // Produces same output as accept
    if (cin >> n >> m) cout << (n + m) << endl;
    return 0;
}
CPP,
            'manual_tests' => [
                ['number' => 1, 'input' => "10 20\n", 'output' => "30\n"],
            ],
        ]);

        $this->generatorService->generateTests($this->exercise);

        $result = $this->calibrationService->calibrate($this->exercise);

        $this->assertSame(PolygonExercise::STATUS_CALIBRATED, $result['status']);
        $this->assertTrue($result['summary']['accept_passed']);
        $this->assertFalse($result['summary']['wrong_calibrated'], 'WRONG calibrado deve ser falso pois coincidiu.');

        $warningFound = false;
        foreach ($result['summary']['warnings'] as $warning) {
            if (str_contains($warning, 'solução WRONG passou em todos os testes')) {
                $warningFound = true;
                break;
            }
        }
        $this->assertTrue($warningFound, 'Deve incluir aviso sobre a solução WRONG passar em todos os testes.');
    }

    private function ensureGppAvailable(): void
    {
        exec('which g++ 2>/dev/null', $output, $code);
        if ($code !== 0) {
            $this->markTestSkipped('g++ não está instalado no ambiente de teste.');
        }
    }
}
