<?php

namespace Tests\Integration\Polygon;

use App\Models\PolygonExercise;
use App\Services\Polygon\PolygonGeneratorService;
use Helium\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolygonGeneratorServiceTest extends TestCase
{
    use RefreshDatabase;

    private PolygonGeneratorService $service;
    private ?PolygonExercise $exercise = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ensureGppAvailable();
        $this->service = new PolygonGeneratorService();
    }

    protected function tearDown(): void
    {
        if ($this->exercise instanceof PolygonExercise) {
            $this->service->cleanTempDirectory($this->exercise);
        }

        parent::tearDown();
    }

    public function test_compile_generator_successfully(): void
    {
        $user = User::factory()->create();

        $this->exercise = PolygonExercise::factory()->create([
            'created_by' => $user->user_id,
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
        ]);

        $result = $this->service->compileGenerator($this->exercise);

        $this->assertTrue($result['success'], 'Falha na compilação do gerador: ' . $result['output']);
        $this->assertNotNull($result['executable']);
        $this->assertFileExists($result['executable']);
        $this->assertTrue(is_executable($result['executable']));
    }

    public function test_compile_generator_returns_error_on_invalid_cpp_source(): void
    {
        $user = User::factory()->create();

        $this->exercise = PolygonExercise::factory()->create([
            'created_by' => $user->user_id,
            'generator_source' => 'this is not valid c++ code {{{',
        ]);

        $result = $this->service->compileGenerator($this->exercise);

        $this->assertFalse($result['success']);
        $this->assertNull($result['executable']);
        $this->assertNotEmpty($result['output']);
        $this->assertStringContainsString('error', strtolower($result['output']));
    }

    public function test_parse_script_handles_various_formats_and_ignores_comments(): void
    {
        $script = <<<SCRIPT
# Linha de comentário estilo hash
// Linha de comentário estilo C++

gen 5 10 > $
./gen 10 50 > $
1000 5000
   gen 200 "foo bar" > $   

# Outro comentário final
SCRIPT;

        $parsed = $this->service->parseScript($script);

        $this->assertCount(4, $parsed);
        $this->assertSame(['5', '10'], $parsed[0]);
        $this->assertSame(['10', '50'], $parsed[1]);
        $this->assertSame(['1000', '5000'], $parsed[2]);
        $this->assertSame(['200', 'foo bar'], $parsed[3]);
    }

    public function test_parse_script_returns_empty_array_for_empty_input(): void
    {
        $this->assertSame([], $this->service->parseScript(null));
        $this->assertSame([], $this->service->parseScript(''));
        $this->assertSame([], $this->service->parseScript("   \n\n  \t  \n"));
    }

    public function test_generate_tests_creates_inputs_for_script_and_manual_tests(): void
    {
        $user = User::factory()->create();

        $this->exercise = PolygonExercise::factory()->create([
            'created_by' => $user->user_id,
            'status' => PolygonExercise::STATUS_DRAFT,
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
            'generator_script' => "gen 5 10 > $\ngen 10 50 > $\ngen 100 500 > $\n",
            'manual_tests' => [
                [
                    'number' => 1,
                    'input' => "4 3\n",
                    'output' => "PAR\n1\n",
                    'is_sample' => true,
                ],
            ],
        ]);

        // 1. Testa compilação explícita primeiro
        $compileResult = $this->service->compileGenerator($this->exercise);
        $this->assertTrue($compileResult['success'], 'A compilação do gerador deve ter sucesso.');

        // 2. Executa geração de testes
        $result = $this->service->generateTests($this->exercise);

        $this->assertTrue($result['success'], 'Falha na geração de testes: ' . ($result['error'] ?? ''));
        $this->assertSame(4, $result['total_tests'], 'Devem ser gerados 4 testes (1 manual + 3 do script).');
        $this->assertCount(4, $result['cases']);

        // Valida teste manual (Caso 1)
        $case1 = $result['cases'][0];
        $this->assertSame(1, $case1['number']);
        $this->assertSame('manual', $case1['origin']);
        $this->assertTrue($case1['is_sample']);
        $this->assertFileExists($case1['input_file']);
        $this->assertGreaterThan(0, $case1['size_bytes']);
        $this->assertSame("4 3\n", file_get_contents($case1['input_file']));
        $this->assertSame("4 3\n", $case1['preview']);

        // Valida testes gerados pelo script (Casos 2, 3 e 4)
        for ($i = 1; $i < 4; $i++) {
            $case = $result['cases'][$i];
            $expectedNumber = $i + 1;

            $this->assertSame($expectedNumber, $case['number']);
            $this->assertSame('script', $case['origin']);
            $this->assertFalse($case['is_sample']);
            $this->assertFileExists($case['input_file']);
            $this->assertGreaterThan(0, $case['size_bytes']);

            $content = file_get_contents($case['input_file']);
            $this->assertNotEmpty(trim($content));
            $this->assertSame(mb_substr($content, 0, 256), $case['preview']);
        }

        // Valida atualização do status no banco de dados
        $this->exercise->refresh();
        $this->assertSame(PolygonExercise::STATUS_GENERATED, $this->exercise->status);
    }

    public function test_generate_tests_cleans_old_in_files_on_subsequent_runs(): void
    {
        $user = User::factory()->create();

        $this->exercise = PolygonExercise::factory()->create([
            'created_by' => $user->user_id,
            'generator_script' => "gen 5 10 > $\ngen 10 50 > $\n",
            'manual_tests' => [],
        ]);

        // Primeira rodada: 2 testes
        $result1 = $this->service->generateTests($this->exercise);
        $this->assertTrue($result1['success']);
        $this->assertSame(2, $result1['total_tests']);

        $testsDir = $this->exercise->getTempDirectory() . '/tests';
        $inFilesRound1 = glob($testsDir . '/*.in');
        $this->assertCount(2, $inFilesRound1);

        // Atualiza para apenas 1 teste
        $this->exercise->generator_script = "gen 5 10 > $\n";
        $this->exercise->save();

        $result2 = $this->service->generateTests($this->exercise);
        $this->assertTrue($result2['success']);
        $this->assertSame(1, $result2['total_tests']);

        $inFilesRound2 = glob($testsDir . '/*.in');
        $this->assertCount(1, $inFilesRound2, 'Arquivos .in antigos devem ter sido limpos.');
    }

    public function test_generate_tests_ignores_empty_manual_tests(): void
    {
        $user = User::factory()->create();

        $this->exercise = PolygonExercise::factory()->create([
            'created_by' => $user->user_id,
            'generator_script' => "gen 5 10 > $\n",
            'manual_tests' => [
                [
                    'number' => 1,
                    'input' => null,
                    'output' => null,
                    'is_sample' => true,
                ],
                [
                    'number' => 2,
                    'input' => "   \n\t  ",
                    'output' => '',
                    'is_sample' => true,
                ],
            ],
        ]);

        $result = $this->service->generateTests($this->exercise);

        $this->assertTrue($result['success']);
        $this->assertSame(1, $result['total_tests'], 'Testes manuais vazios devem ser ignorados.');
        $this->assertSame('script', $result['cases'][0]['origin']);
        $this->assertGreaterThan(0, $result['cases'][0]['size_bytes']);
    }

    public function test_generate_tests_auto_seeds_empty_script_lines_producing_different_outputs(): void
    {
        $user = User::factory()->create();

        $this->exercise = PolygonExercise::factory()->create([
            'created_by' => $user->user_id,
            'generator_source' => <<<'CPP'
#include "testlib.h"
#include <iostream>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    int n = opt<int>("n", rnd.next(1, 1000000));
    cout << n << "\n";
    return 0;
}
CPP,
            'generator_script' => "gen > $\ngen > $\n",
            'manual_tests' => [],
        ]);

        $result = $this->service->generateTests($this->exercise);
        $this->assertTrue($result['success']);
        $this->assertSame(2, $result['total_tests']);

        $testsDir = $this->exercise->getTempDirectory() . '/tests';
        $test1 = trim(file_get_contents($testsDir . '/001.in'));
        $test2 = trim(file_get_contents($testsDir . '/002.in'));

        $this->assertNotSame($test1, $test2, 'Duas chamadas de "gen > $" devem receber sementes automáticas distintas.');
    }

    private function ensureGppAvailable(): void
    {
        exec('which g++ 2>/dev/null', $output, $code);
        if ($code !== 0) {
            $this->markTestSkipped('g++ não está instalado no ambiente de teste.');
        }
    }
}
