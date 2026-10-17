<?php

namespace Tests\E2E;

use App\Http\Controllers\Backend\ContestWizardController;
use App\Models\Answer;
use App\Models\Contest;
use App\Models\ContestLog;
use App\Models\Language;
use App\Models\PolygonExercise;
use App\Models\Problem;
use App\Models\ProblemBank;
use App\Models\ProblemLanguageLimit;
use App\Models\Run;
use App\Models\Site;
use App\Models\TestCase as ProblemTestCase;
use App\Services\AutoJudgeService;
use App\Services\Polygon\LanguageLimitDerivationService;
use App\Services\Polygon\PolygonCalibrationService;
use App\Services\Polygon\PolygonGeneratorService;
use App\Services\Polygon\PolygonPublishService;
use App\Services\RunSubmissionService;
use Helium\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\RequiresJudgeSandbox;
use Tests\TestCase;

/**
 * Teste Ponta a Ponta (E2E) e Homologação Completa do Polygon:
 *
 * Valida o ciclo de vida integral da autoria de problemas com o Polygon:
 * 1. Criação de rascunho de PolygonExercise com gerador testlib.h, scripts e soluções.
 * 2. Compilação do gerador C++.
 * 3. Geração de N casos de teste (manuais + script).
 * 4. Calibração completa de soluções (ACCEPT modelo gerando .out oficiais, TLE e WRONG).
 * 5. Publicação em Conteste (Problema, TestCases, ProblemLanguageLimits, ContestLog).
 * 6. Julgamento real com AutoJudgeService (AC, WA e TLE).
 * 7. Publicação no Banco Geral de Problemas (ProblemBank com pacote de N testes).
 * 8. Importação do Banco para Contest via ContestWizardController::addProblemsFromBank.
 * 9. Julgamento de solução de equipe sobre o problema importado (AC).
 * 10. Limpeza completa dos workspaces e diretórios no tearDown.
 */
class PolygonFullLifecycleTest extends TestCase
{
    use RefreshDatabase;
    use RequiresJudgeSandbox;

    private PolygonGeneratorService $generatorService;

    private PolygonCalibrationService $calibrationService;

    private PolygonPublishService $publishService;

    private RunSubmissionService $submissionService;

    private AutoJudgeService $autoJudge;

    /** @var int[] */
    private array $createdExerciseIds = [];

    /** @var int[] */
    private array $createdContestIds = [];

    /** @var string[] */
    private array $createdBankCodes = [];

    protected function setUp(): void
    {
        $this->ensureGppAvailable();
        $this->skipUnlessJudgeSandboxAvailable();

        parent::setUp();

        $this->enableJudgeSandbox();

        $this->generatorService = new PolygonGeneratorService();
        $this->calibrationService = new PolygonCalibrationService(
            new LanguageLimitDerivationService()
        );
        $this->publishService = new PolygonPublishService(
            new LanguageLimitDerivationService()
        );
        $this->submissionService = app(RunSubmissionService::class);
        $this->autoJudge = new AutoJudgeService();
    }

    protected function tearDown(): void
    {
        // 1. Limpa diretórios temporários dos exercícios Polygon
        foreach ($this->createdExerciseIds as $id) {
            $tempDir = storage_path("app/temp/polygon_{$id}");
            if (is_dir($tempDir)) {
                File::deleteDirectory($tempDir);
            }
        }

        // 2. Limpa diretórios permanentes dos contests e runs
        foreach ($this->createdContestIds as $contestId) {
            $problemsDir = storage_path("app/problems/{$contestId}");
            if (is_dir($problemsDir)) {
                File::deleteDirectory($problemsDir);
            }

            $runsDir = storage_path("app/runs/{$contestId}");
            if (is_dir($runsDir)) {
                File::deleteDirectory($runsDir);
            }
        }

        // 3. Limpa diretórios do Banco Geral de Problemas
        foreach ($this->createdBankCodes as $code) {
            $bankDir = storage_path("app/problem_bank/{$code}");
            if (is_dir($bankDir)) {
                File::deleteDirectory($bankDir);
            }
        }

        parent::tearDown();
    }

    /**
     * Valida o ciclo completo no Cenário A: Contest -> Rascunho -> Compilar -> Gerar -> Calibrar -> Publicar -> AutoJudge (AC, WA, TLE).
     */
    public function test_polygon_full_lifecycle_contest_creation_calibration_publishing_and_autojudge(): void
    {
        // 1. Criação do Contest com Site, Línguas e Respostas Padrão
        [$contest, $site, $languageCpp] = $this->createContestFixture('Maratona Polygon 2026');

        $author = User::factory()->create([
            'user_type' => 'admin',
            'is_enabled' => true,
        ]);

        // 2. Criação do Rascunho do Exercício vinculado ao Contest
        $exercise = PolygonExercise::factory()->create([
            'contest_id' => $contest->id,
            'created_by' => $author->user_id,
            'name' => 'Paridade e Resto',
            'short_name' => 'A',
            'basename' => 'paridade-resto',
            'description' => 'Dado dois inteiros N e M, determine se N é PAR ou IMPAR e calcule N mod M.',
            'input_description' => 'Uma linha contendo dois inteiros N e M.',
            'output_description' => 'A primeira linha deve conter PAR ou IMPAR. A segunda linha deve conter o resto N mod M.',
            'notes' => '1 <= N, M <= 10^9',
            'time_limit' => 1,
            'memory_limit' => 256,
            'output_limit' => 1024,
            'color_name' => 'Azul',
            'color_hex' => '#3B82F6',
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
        // Trava em loop infinito intencional
        if (n >= 1) {
            volatile long long counter = 0;
            while (true) {
                counter++;
            }
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
        cout << "RESPOSTA_INCORRETA" << endl;
        cout << (n + m + 999) << endl;
    }
    return 0;
}
CPP,
        ]);
        $this->createdExerciseIds[] = $exercise->id;

        // 3. Compilação do Gerador C++
        $compileResult = $this->generatorService->compileGenerator($exercise);
        $this->assertTrue($compileResult['success'], 'Compilação do gerador falhou: ' . ($compileResult['output'] ?? ''));
        $this->assertNotNull($compileResult['executable']);
        $this->assertFileExists($compileResult['executable']);
        $this->assertTrue(is_executable($compileResult['executable']));

        // 4. Geração dos Casos de Teste (1 manual de exemplo + 2 do script = 3 testes)
        $genTestsResult = $this->generatorService->generateTests($exercise);
        $this->assertTrue($genTestsResult['success'], 'Geração de testes falhou: ' . ($genTestsResult['error'] ?? ''));
        $this->assertSame(3, $genTestsResult['total_tests']);
        $this->assertCount(3, $genTestsResult['cases']);
        $this->assertSame(PolygonExercise::STATUS_GENERATED, $exercise->fresh()->status);

        $tempTestsDir = $exercise->getTempDirectory() . '/tests';
        $this->assertFileExists($tempTestsDir . '/001.in');
        $this->assertFileExists($tempTestsDir . '/002.in');
        $this->assertFileExists($tempTestsDir . '/003.in');
        $this->assertSame("4 3\n", file_get_contents($tempTestsDir . '/001.in'));

        // 5. Execução da Calibração
        $calibResult = $this->calibrationService->calibrate($exercise);
        $this->assertSame(PolygonExercise::STATUS_CALIBRATED, $calibResult['status']);
        $this->assertTrue($calibResult['summary']['accept_passed'], 'ACCEPT deveria passar em 100% dos testes.');
        $this->assertTrue($calibResult['summary']['tle_calibrated'], 'Solução TLE deveria disparar TLE.');
        $this->assertTrue($calibResult['summary']['wrong_calibrated'], 'Solução WRONG deveria falhar com WA.');
        $this->assertNotEmpty($calibResult['language_limits'], 'Limites derivados devem ser calculados.');

        // Assegura geração dos arquivos .out oficiais para todos os casos
        $this->assertFileExists($tempTestsDir . '/001.out');
        $this->assertFileExists($tempTestsDir . '/002.out');
        $this->assertFileExists($tempTestsDir . '/003.out');
        $this->assertSame("PAR\n1\n", file_get_contents($tempTestsDir . '/001.out'));

        $freshExercise = $exercise->fresh();
        $this->assertSame(PolygonExercise::STATUS_CALIBRATED, $freshExercise->status);
        $this->assertNotNull($freshExercise->calibration_results);

        // 6. Publicação no Contest
        $pubResult = $this->publishService->publish($exercise);
        $this->assertTrue($pubResult['success']);
        $this->assertSame('contest', $pubResult['target']);
        $this->assertNotNull($pubResult['problem_id']);

        $problem = Problem::findOrFail($pubResult['problem_id']);
        $this->assertSame($contest->id, $problem->contest_id);
        $this->assertSame('paridade-resto', $problem->basename);
        $this->assertSame('A', $problem->short_name);
        $this->assertSame('Paridade e Resto', $problem->name);
        $this->assertTrue($problem->auto_judge);

        // Assegura que os registros de test_cases foram criados e arquivos copiados para o storage permanente
        $testCases = ProblemTestCase::where('problem_id', $problem->id)->orderBy('number')->get();
        $this->assertCount(3, $testCases);

        $this->assertTrue($testCases[0]->is_sample);
        $this->assertSame(1, $testCases[0]->number);
        $this->assertFileExists($testCases[0]->getInputPath());
        $this->assertFileExists($testCases[0]->getOutputPath());
        $this->assertSame("4 3\n", file_get_contents($testCases[0]->getInputPath()));
        $this->assertSame("PAR\n1\n", file_get_contents($testCases[0]->getOutputPath()));
        $this->assertSame(hash('sha256', "4 3\n"), $testCases[0]->input_hash);
        $this->assertSame(hash('sha256', "PAR\n1\n"), $testCases[0]->output_hash);

        $this->assertFalse($testCases[1]->is_sample);
        $this->assertFileExists($testCases[1]->getInputPath());
        $this->assertFileExists($testCases[1]->getOutputPath());

        $this->assertFalse($testCases[2]->is_sample);
        $this->assertFileExists($testCases[2]->getInputPath());
        $this->assertFileExists($testCases[2]->getOutputPath());

        // Assegura que problem_language_limits foi populado
        $this->assertDatabaseHas('problem_language_limits', [
            'problem_id' => $problem->id,
            'language_id' => $languageCpp->id,
        ]);

        // Assegura registro no ContestLog
        $this->assertDatabaseHas('contest_logs', [
            'contest_id' => $contest->id,
            'type' => 'info',
        ]);

        // Assegura status final do exercício Polygon
        $this->assertSame(PolygonExercise::STATUS_PUBLISHED, $exercise->fresh()->status);
        $this->assertSame($problem->id, $exercise->fresh()->published_problem_id);

        // 7. Validação com AutoJudgeService
        // Criação da Equipe Competidora
        $team = User::factory()->create([
            'fullname' => 'Equipe Alpha',
            'username' => 'equipe_alpha_' . uniqid(),
            'email' => 'alpha_' . uniqid() . '@example.com',
            'password' => bcrypt('secret'),
            'user_type' => 'team',
            'is_enabled' => true,
            'contest_id' => $contest->id,
            'site_id' => $site->id,
        ]);

        // 7a. Submissão da Solução Correta (ACCEPT)
        $runAC = $this->submissionService->submit(
            $contest,
            $site->id,
            $team,
            $problem,
            $languageCpp,
            'solution_ac.cpp',
            $exercise->solution_accept
        );

        $this->assertSame('pending', $runAC->status);
        $this->autoJudge->judge($runAC);

        $runAC->refresh();
        $this->assertSame('judged', $runAC->status);
        $this->assertSame('AC', $runAC->verdict);
        $this->assertSame('AC', $runAC->answer?->short_name);
        $this->assertTrue($runAC->isAccepted());

        // 7b. Submissão da Solução Incorreta (WRONG -> WA)
        $runWA = $this->submissionService->submit(
            $contest,
            $site->id,
            $team,
            $problem,
            $languageCpp,
            'solution_wa.cpp',
            $exercise->solution_wrong
        );

        $this->autoJudge->judge($runWA);

        $runWA->refresh();
        $this->assertSame('judged', $runWA->status);
        $this->assertSame('WA', $runWA->verdict);
        $this->assertSame('WA', $runWA->answer?->short_name);
        $this->assertFalse($runWA->isAccepted());

        // 7c. Submissão da Solução Lenta (TLE)
        $runTLE = $this->submissionService->submit(
            $contest,
            $site->id,
            $team,
            $problem,
            $languageCpp,
            'solution_tle.cpp',
            $exercise->solution_tle
        );

        $this->autoJudge->judge($runTLE);

        $runTLE->refresh();
        $this->assertSame('judged', $runTLE->status);
        $this->assertSame('TLE', $runTLE->verdict);
        $this->assertSame('TLE', $runTLE->answer?->short_name);
        $this->assertFalse($runTLE->isAccepted());
    }

    /**
     * Valida o ciclo no Cenário B: Criação sem contest -> Publicação no Banco Geral -> Importação para Contest -> AutoJudge (AC).
     */
    public function test_polygon_full_lifecycle_bank_publishing_import_to_contest_and_autojudge(): void
    {
        $author = User::factory()->create([
            'user_type' => 'admin',
            'is_enabled' => true,
        ]);

        // 1. Criação do Exercício sem Contest (destino Banco Geral)
        $bankExercise = PolygonExercise::factory()->create([
            'contest_id' => null,
            'created_by' => $author->user_id,
            'name' => 'Multiplicação de Fatores',
            'short_name' => 'MULT',
            'basename' => 'multiplicacao-fatores',
            'description' => 'Calcule o produto de dois inteiros A e B.',
            'input_description' => 'Dois inteiros A e B na mesma linha.',
            'output_description' => 'Um inteiro representando A * B.',
            'notes' => '1 <= A, B <= 10^4',
            'time_limit' => 1,
            'memory_limit' => 256,
            'output_limit' => 1024,
            'status' => PolygonExercise::STATUS_DRAFT,
            'generator_source' => <<<'CPP'
#include "testlib.h"
#include <iostream>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    long long a = atoll(argv[1]);
    long long b = atoll(argv[2]);
    cout << rnd.next(1LL, a) << " " << rnd.next(1LL, b) << endl;
    return 0;
}
CPP,
            'generator_script' => "gen 20 30 > $\ngen 50 100 > $\n",
            'manual_tests' => [
                [
                    'number' => 1,
                    'input' => "3 5\n",
                    'output' => "15\n",
                    'is_sample' => true,
                ],
            ],
            'solution_accept' => <<<'CPP'
#include <iostream>

using namespace std;

int main() {
    long long a, b;
    if (cin >> a >> b) {
        cout << (a * b) << endl;
    }
    return 0;
}
CPP,
            'solution_wrong' => <<<'CPP'
#include <iostream>

using namespace std;

int main() {
    long long a, b;
    if (cin >> a >> b) {
        cout << (a + b) << endl;
    }
    return 0;
}
CPP,
        ]);
        $this->createdExerciseIds[] = $bankExercise->id;

        // 2. Compilar gerador e gerar testes
        $compResult = $this->generatorService->compileGenerator($bankExercise);
        $this->assertTrue($compResult['success']);

        $genResult = $this->generatorService->generateTests($bankExercise);
        $this->assertTrue($genResult['success']);
        $this->assertSame(3, $genResult['total_tests']);

        // 3. Calibrar
        $calibResult = $this->calibrationService->calibrate($bankExercise);
        $this->assertSame(PolygonExercise::STATUS_CALIBRATED, $calibResult['status']);
        $this->assertTrue($calibResult['summary']['accept_passed']);

        // 4. Publicar no Banco Geral (ProblemBank)
        $pubResult = $this->publishService->publish($bankExercise);
        $this->assertTrue($pubResult['success']);
        $this->assertSame('bank', $pubResult['target']);
        $this->assertSame('multiplicacao-fatores', $pubResult['bank_code']);
        $this->createdBankCodes[] = $pubResult['bank_code'];

        $bankProblem = ProblemBank::where('code', 'multiplicacao-fatores')->firstOrFail();
        $this->assertSame('Multiplicação de Fatores', $bankProblem->name);
        $this->assertSame("3 5\n", $bankProblem->sample_input);
        $this->assertSame("15\n", $bankProblem->sample_output);
        $this->assertSame('Polygon', $bankProblem->source);
        $this->assertTrue($bankProblem->is_active);

        // Verifica que todos os 3 testes foram salvos no storage permanente do problem_bank
        $bankInput1 = storage_path("app/problem_bank/{$bankProblem->code}/input/1");
        $bankOutput1 = storage_path("app/problem_bank/{$bankProblem->code}/output/1");
        $bankInput2 = storage_path("app/problem_bank/{$bankProblem->code}/input/2");
        $bankOutput2 = storage_path("app/problem_bank/{$bankProblem->code}/output/2");
        $bankInput3 = storage_path("app/problem_bank/{$bankProblem->code}/input/3");
        $bankOutput3 = storage_path("app/problem_bank/{$bankProblem->code}/output/3");

        $this->assertFileExists($bankInput1);
        $this->assertFileExists($bankOutput1);
        $this->assertFileExists($bankInput2);
        $this->assertFileExists($bankOutput2);
        $this->assertFileExists($bankInput3);
        $this->assertFileExists($bankOutput3);

        $this->assertSame("3 5\n", file_get_contents($bankInput1));
        $this->assertSame("15\n", file_get_contents($bankOutput1));

        // 5. Criação de um Contest de Destino para Importação
        [$targetContest, $targetSite, $languageCpp] = $this->createContestFixture('Contest Alvo de Importacao');

        // 6. Importação do Problema do Banco Geral para o Contest via ContestWizardController::addProblemsFromBank
        $addedCount = ContestWizardController::addProblemsFromBank($targetContest->id, [$bankProblem->id]);
        $this->assertSame(1, $addedCount, 'Exatamente 1 problema deveria ter sido importado.');

        // 7. Verificação de que o Problema e TODOS os N casos de teste foram importados no contest
        $importedProblem = Problem::where('contest_id', $targetContest->id)
            ->where('basename', $bankProblem->code)
            ->firstOrFail();

        $this->assertSame('Multiplicação de Fatores', $importedProblem->name);

        $importedCases = ProblemTestCase::where('problem_id', $importedProblem->id)->orderBy('number')->get();
        $this->assertCount(3, $importedCases, 'Todos os 3 casos do pacote do banco devem ser importados no contest.');

        // Verifica caso de exemplo 1
        $this->assertTrue($importedCases[0]->is_sample);
        $this->assertSame(1, $importedCases[0]->number);
        $this->assertFileExists($importedCases[0]->getInputPath());
        $this->assertFileExists($importedCases[0]->getOutputPath());
        $this->assertSame("3 5\n", file_get_contents($importedCases[0]->getInputPath()));
        $this->assertSame("15\n", file_get_contents($importedCases[0]->getOutputPath()));

        // Verifica casos 2 e 3
        $this->assertFalse($importedCases[1]->is_sample);
        $this->assertFileExists($importedCases[1]->getInputPath());
        $this->assertFileExists($importedCases[1]->getOutputPath());

        $this->assertFalse($importedCases[2]->is_sample);
        $this->assertFileExists($importedCases[2]->getInputPath());
        $this->assertFileExists($importedCases[2]->getOutputPath());

        // 8. Submissão de Equipe contra o Problema Importado e Validação pelo AutoJudgeService
        $team = User::factory()->create([
            'fullname' => 'Equipe Beta',
            'username' => 'equipe_beta_' . uniqid(),
            'email' => 'beta_' . uniqid() . '@example.com',
            'password' => bcrypt('secret'),
            'user_type' => 'team',
            'is_enabled' => true,
            'contest_id' => $targetContest->id,
            'site_id' => $targetSite->id,
        ]);

        $runImported = $this->submissionService->submit(
            $targetContest,
            $targetSite->id,
            $team,
            $importedProblem,
            $languageCpp,
            'solution_imported.cpp',
            $bankExercise->solution_accept
        );

        $this->autoJudge->judge($runImported);

        $runImported->refresh();
        $this->assertSame('judged', $runImported->status);
        $this->assertSame('AC', $runImported->verdict);
        $this->assertSame('AC', $runImported->answer?->short_name);
        $this->assertTrue($runImported->isAccepted());
    }

    /**
     * Auxiliar para criar Contest com Site, Línguas e Respostas Padrão.
     *
     * @return array{0: Contest, 1: Site, 2: Language}
     */
    private function createContestFixture(string $contestName): array
    {
        $contest = Contest::factory()->create([
            'name' => $contestName,
            'is_active' => true,
            'start_time' => now()->subMinutes(10),
            'duration' => 300,
        ]);
        $this->createdContestIds[] = $contest->id;

        $site = Site::factory()->create([
            'contest_id' => $contest->id,
            'name' => 'Sede Principal',
        ]);

        foreach (Answer::getDefaultAnswers() as $ans) {
            Answer::create(array_merge($ans, ['contest_id' => $contest->id]));
        }

        foreach (Language::getDefaultLanguages() as $lang) {
            Language::create(array_merge($lang, ['contest_id' => $contest->id]));
        }

        $languageCpp = Language::where('contest_id', $contest->id)
            ->where('extension', 'cpp_gpp13')
            ->firstOrFail();

        return [$contest, $site, $languageCpp];
    }

    private function ensureGppAvailable(): void
    {
        exec('which g++ 2>/dev/null', $output, $code);
        if ($code !== 0) {
            $this->markTestSkipped('g++ não está instalado no ambiente de teste.');
        }
    }
}
