<?php

namespace Tests\Feature\Polygon;

use App\Models\Contest;
use App\Models\PolygonExercise;
use App\Models\Problem;
use App\Models\ProblemBank;
use App\Models\TestCase as ProblemTestCase;
use App\Services\Polygon\PolygonGeneratorService;
use Helium\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PolygonApiControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private PolygonGeneratorService $generatorService;
    private array $createdExerciseIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->ensureGppAvailable();

        $this->admin = $this->createAdminUser();
        $this->generatorService = new PolygonGeneratorService();
    }

    protected function tearDown(): void
    {
        foreach ($this->createdExerciseIds as $id) {
            $exercise = PolygonExercise::find($id);
            if ($exercise) {
                $this->generatorService->cleanTempDirectory($exercise);
            }
        }

        parent::tearDown();
    }

    /**
     * Complete lifecycle test through all REST API endpoints.
     */
    public function test_complete_polygon_exercise_lifecycle_via_rest_api(): void
    {
        // 1. POST /api/backend/polygon (criação de rascunho) -> 201
        $createResponse = $this->actingAs($this->admin)->postJson('/api/backend/polygon', [
            'name' => 'Soma Simples',
            'short_name' => 'A',
            'basename' => 'soma-simples',
            'time_limit' => 1,
            'memory_limit' => 256,
            'description' => '# Soma Simples\n\nDado A e B, calcule a soma.',
            'input_description' => 'Dois números inteiros A e B.',
            'output_description' => 'A soma A + B.',
            'notes' => '1 <= A, B <= 10^9',
            'color_name' => 'Azul',
            'color_hex' => '#3B82F6',
        ]);

        $createResponse->assertStatus(201);
        $exerciseId = $createResponse->json('data.id') ?? $createResponse->json('id');
        $this->assertNotNull($exerciseId);
        $this->createdExerciseIds[] = $exerciseId;

        $this->assertDatabaseHas('polygon_exercises', [
            'id' => $exerciseId,
            'basename' => 'soma-simples',
            'status' => 'draft',
        ]);

        // 2. GET /api/backend/polygon/{id} -> 200
        $showResponse = $this->actingAs($this->admin)->getJson("/api/backend/polygon/{$exerciseId}");
        $showResponse->assertStatus(200);
        $this->assertSame('Soma Simples', $showResponse->json('data.name') ?? $showResponse->json('name'));
        $this->assertArrayHasKey('contests', $showResponse->json());
        $this->assertArrayHasKey('cases', $showResponse->json());

        // 3. PUT /api/backend/polygon/{id} (salva código C++, script e soluções) -> 200
        $generatorSource = <<<'CPP'
#include <iostream>
using namespace std;
int main(int argc, char* argv[]) {
    long long n = argc > 1 ? atoll(argv[1]) : 10;
    long long m = argc > 2 ? atoll(argv[2]) : 20;
    cout << n << " " << m << endl;
    return 0;
}
CPP;

        $acceptSource = <<<'CPP'
#include <iostream>
using namespace std;
int main() {
    long long a, b;
    if (cin >> a >> b) {
        cout << (a + b) << endl;
    }
    return 0;
}
CPP;

        $tleSource = <<<'CPP'
#include <iostream>
using namespace std;
int main() {
    long long a, b;
    if (cin >> a >> b) {
        if (a > 50) {
            while (true) {}
        }
        cout << (a + b) << endl;
    }
    return 0;
}
CPP;

        $wrongSource = <<<'CPP'
#include <iostream>
using namespace std;
int main() {
    long long a, b;
    if (cin >> a >> b) {
        cout << (a * b) << endl;
    }
    return 0;
}
CPP;

        $updatePayload = [
            'generator_source' => $generatorSource,
            'generator_script' => "gen 10 20 > $\ngen 100 200 > $\n",
            'manual_tests' => [
                ['number' => 1, 'input' => "2 3\n", 'output' => "5\n", 'is_sample' => true],
            ],
            'solution_accept' => $acceptSource,
            'solution_tle' => $tleSource,
            'solution_wrong' => $wrongSource,
        ];

        $updateResponse = $this->actingAs($this->admin)->putJson("/api/backend/polygon/{$exerciseId}", $updatePayload);
        $updateResponse->assertStatus(200);
        $this->assertDatabaseHas('polygon_exercises', [
            'id' => $exerciseId,
            'generator_script' => "gen 10 20 > $\ngen 100 200 > $",
        ]);

        // 4. POST /api/backend/polygon/{id}/compile-generator -> 200
        $compileResponse = $this->actingAs($this->admin)->postJson("/api/backend/polygon/{$exerciseId}/compile-generator");
        $compileResponse->assertStatus(200);
        $this->assertTrue($compileResponse->json('success'), 'Compilação do gerador falhou: ' . $compileResponse->json('output'));

        // 5. POST /api/backend/polygon/{id}/generate-tests -> 200
        $generateResponse = $this->actingAs($this->admin)->postJson("/api/backend/polygon/{$exerciseId}/generate-tests");
        $generateResponse->assertStatus(200);
        $this->assertTrue($generateResponse->json('success'));
        $this->assertSame(3, $generateResponse->json('total_tests')); // 1 manual + 2 script
        $this->assertCount(3, $generateResponse->json('cases'));

        // 6. POST /api/backend/polygon/{id}/calibrate -> 200 com resumo e casos
        $calibrateResponse = $this->actingAs($this->admin)->postJson("/api/backend/polygon/{$exerciseId}/calibrate");
        $calibrateResponse->assertStatus(200);

        $summary = $calibrateResponse->json('data.summary') ?? $calibrateResponse->json('summary');
        $this->assertTrue($summary['accept_passed'], 'A solução ACCEPT deveria ter passado.');
        $this->assertTrue($summary['tle_calibrated'], 'A solução TLE deveria ter sido calibrada.');
        $this->assertTrue($summary['wrong_calibrated'], 'A solução WRONG deveria ter sido calibrada.');

        $cases = $calibrateResponse->json('data.cases') ?? $calibrateResponse->json('cases');
        $this->assertCount(3, $cases);

        // 7. POST /api/backend/polygon/{id}/publish -> 200 com problema publicado no Banco Geral
        $publishResponse = $this->actingAs($this->admin)->postJson("/api/backend/polygon/{$exerciseId}/publish");
        $publishResponse->assertStatus(200);
        $this->assertTrue($publishResponse->json('success'));
        $this->assertSame('bank', $publishResponse->json('target'));
        $bankCode = $publishResponse->json('bank_code');
        $this->assertNotNull($bankCode);

        $this->assertDatabaseHas('problem_bank', ['code' => $bankCode]);
        $this->assertDatabaseHas('polygon_exercises', [
            'id' => $exerciseId,
            'status' => 'published',
            'published_bank_code' => $bankCode,
        ]);

        // 8. DELETE /api/backend/polygon/{id} -> 204
        $deleteResponse = $this->actingAs($this->admin)->deleteJson("/api/backend/polygon/{$exerciseId}");
        $deleteResponse->assertStatus(204);
        $this->assertDatabaseMissing('polygon_exercises', ['id' => $exerciseId]);
    }

    /**
     * Tests publication to an existing contest.
     */
    public function test_publish_to_contest_successfully(): void
    {
        $contest = Contest::factory()->create([
            'is_active' => true,
            'start_time' => now()->addHour(),
        ]);

        $exercise = PolygonExercise::factory()->create([
            'contest_id' => $contest->id,
            'created_by' => $this->admin->user_id,
            'name' => 'Problema na Prova',
            'short_name' => 'B',
            'basename' => 'problema-prova',
            'solution_accept' => <<<'CPP'
#include <iostream>
using namespace std;
int main() {
    int a;
    if (cin >> a) cout << a << endl;
    return 0;
}
CPP,
            'manual_tests' => [
                ['number' => 1, 'input' => "42\n", 'output' => "42\n", 'is_sample' => true],
            ],
        ]);
        $this->createdExerciseIds[] = $exercise->id;

        // Generate and calibrate
        $this->actingAs($this->admin)->postJson("/api/backend/polygon/{$exercise->id}/generate-tests")->assertStatus(200);
        $this->actingAs($this->admin)->postJson("/api/backend/polygon/{$exercise->id}/calibrate")->assertStatus(200);

        // Publish to contest
        $publishResponse = $this->actingAs($this->admin)->postJson("/api/backend/polygon/{$exercise->id}/publish");
        $publishResponse->assertStatus(200);
        $this->assertTrue($publishResponse->json('success'));
        $this->assertSame('contest', $publishResponse->json('target'));
        $problemId = $publishResponse->json('problem_id');
        $this->assertNotNull($problemId);

        $this->assertDatabaseHas('problems', [
            'id' => $problemId,
            'contest_id' => $contest->id,
            'short_name' => 'B',
            'basename' => 'problema-prova',
        ]);

        $this->assertDatabaseHas('test_cases', [
            'problem_id' => $problemId,
            'number' => 1,
            'is_sample' => true,
        ]);
    }

    /**
     * Validates that missing required fields return 422 Unprocessable Entity.
     */
    public function test_missing_required_fields_return_422(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/backend/polygon', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'short_name', 'basename']);
    }

    /**
     * Validates that non-existent IDs return 404 Not Found.
     */
    public function test_non_existent_id_returns_404(): void
    {
        $nonExistentId = 999999;

        $this->actingAs($this->admin)->getJson("/api/backend/polygon/{$nonExistentId}")->assertStatus(404);
        $this->actingAs($this->admin)->putJson("/api/backend/polygon/{$nonExistentId}", ['name' => 'New'])->assertStatus(404);
        $this->actingAs($this->admin)->deleteJson("/api/backend/polygon/{$nonExistentId}")->assertStatus(404);
        $this->actingAs($this->admin)->postJson("/api/backend/polygon/{$nonExistentId}/compile-generator")->assertStatus(404);
        $this->actingAs($this->admin)->postJson("/api/backend/polygon/{$nonExistentId}/generate-tests")->assertStatus(404);
        $this->actingAs($this->admin)->postJson("/api/backend/polygon/{$nonExistentId}/calibrate")->assertStatus(404);
        $this->actingAs($this->admin)->postJson("/api/backend/polygon/{$nonExistentId}/publish")->assertStatus(404);
    }

    /**
     * Validates that attempting to publish an uncalibrated exercise returns 422.
     */
    public function test_publish_uncalibrated_exercise_returns_422(): void
    {
        $exercise = PolygonExercise::factory()->create([
            'created_by' => $this->admin->user_id,
            'status' => PolygonExercise::STATUS_DRAFT,
            'calibration_results' => null,
        ]);
        $this->createdExerciseIds[] = $exercise->id;

        $response = $this->actingAs($this->admin)->postJson("/api/backend/polygon/{$exercise->id}/publish");

        $response->assertStatus(422);
        $this->assertFalse($response->json('success'));
        $this->assertStringContainsString('calibrado', strtolower($response->json('message')));
    }

    /**
     * Validates state machine: modifying generator or structural limits on a calibrated exercise reverts status to draft.
     */
    public function test_modifying_generator_or_limits_reverts_calibrated_status_to_draft(): void
    {
        $exercise = PolygonExercise::factory()->create([
            'created_by' => $this->admin->user_id,
            'status' => PolygonExercise::STATUS_CALIBRATED,
            'time_limit' => 1,
            'generator_source' => '// original code',
        ]);
        $this->createdExerciseIds[] = $exercise->id;

        // Modifying time limit reverts status to draft
        $response = $this->actingAs($this->admin)->putJson("/api/backend/polygon/{$exercise->id}", [
            'time_limit' => 2,
        ]);

        $response->assertStatus(200);
        $this->assertSame(PolygonExercise::STATUS_DRAFT, $response->json('data.status') ?? $response->json('status'));
        $this->assertDatabaseHas('polygon_exercises', [
            'id' => $exercise->id,
            'status' => 'draft',
            'time_limit' => 2,
        ]);
    }

    /**
     * Validates that GET /api/backend/polygon returns counts summary and respects status and search query filters.
     */
    public function test_index_returns_counts_and_filters_by_status_and_search(): void
    {
        PolygonExercise::factory()->create([
            'name' => 'Problema Alfa',
            'short_name' => 'A',
            'basename' => 'problema-alfa',
            'status' => 'draft',
            'created_by' => $this->admin->user_id,
        ]);
        PolygonExercise::factory()->create([
            'name' => 'Problema Beta',
            'short_name' => 'B',
            'basename' => 'problema-beta',
            'status' => 'calibrated',
            'created_by' => $this->admin->user_id,
        ]);

        // 1. Unfiltered request returns all, drafts, published and counts summary
        $response = $this->actingAs($this->admin)->getJson('/api/backend/polygon');
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        $this->assertCount(2, $response->json('drafts')); // Alfa (draft) and Beta (calibrated) are both drafts
        $this->assertCount(0, $response->json('published'));
        $this->assertEquals(2, $response->json('counts.total'));
        $this->assertEquals(1, $response->json('counts.draft'));
        $this->assertEquals(1, $response->json('counts.calibrated'));

        // 2. Filter by status=draft
        $draftResponse = $this->actingAs($this->admin)->getJson('/api/backend/polygon?status=draft');
        $draftResponse->assertStatus(200);
        $this->assertCount(1, $draftResponse->json('data'));
        $this->assertEquals('Problema Alfa', $draftResponse->json('data.0.name'));

        // 3. Filter by search=beta
        $searchResponse = $this->actingAs($this->admin)->getJson('/api/backend/polygon?search=beta');
        $searchResponse->assertStatus(200);
        $this->assertCount(1, $searchResponse->json('data'));
        $this->assertEquals('Problema Beta', $searchResponse->json('data.0.name'));
    }

    private function ensureGppAvailable(): void
    {
        exec('which g++ 2>/dev/null', $output, $code);
        if ($code !== 0) {
            $this->markTestSkipped('g++ não está instalado no ambiente de teste.');
        }
    }
}
