<?php

namespace Tests\Unit\Polygon;

use App\Models\Contest;
use App\Models\PolygonExercise;
use App\Models\Problem;
use Helium\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PolygonExerciseModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_and_persist_polygon_exercise(): void
    {
        $user = User::factory()->create();
        $contest = Contest::factory()->create();

        $exercise = PolygonExercise::factory()->create([
            'contest_id' => $contest->id,
            'created_by' => $user->user_id,
            'name' => 'Resto da Divisao',
            'short_name' => 'A',
            'basename' => 'resto-divisao',
            'time_limit' => 2,
            'memory_limit' => 512,
            'output_limit' => 2048,
            'description' => '# Resto da Divisao',
            'status' => PolygonExercise::STATUS_DRAFT,
        ]);

        $this->assertDatabaseHas('polygon_exercises', [
            'id' => $exercise->id,
            'name' => 'Resto da Divisao',
            'short_name' => 'A',
            'basename' => 'resto-divisao',
            'time_limit' => 2,
            'memory_limit' => 512,
            'output_limit' => 2048,
            'status' => 'draft',
            'created_by' => $user->user_id,
            'contest_id' => $contest->id,
        ]);
    }

    public function test_casts_json_and_integer_attributes_correctly(): void
    {
        $manualTests = [
            [
                'number' => 1,
                'input' => "10 20\n",
                'output' => "30\n",
                'is_sample' => true,
            ],
            [
                'number' => 2,
                'input' => "100 200\n",
                'output' => "300\n",
                'is_sample' => false,
            ],
        ];

        $additionalSolutions = [
            [
                'lang' => 'py3',
                'type' => 'accept',
                'source' => "a, b = map(int, input().split())\nprint(a + b)\n",
            ],
        ];

        $calibrationResults = [
            'summary' => [
                'total_tests' => 2,
                'accept_passed' => true,
                'max_time_seconds' => 0.03,
                'max_memory_mb' => 14.2,
            ],
        ];

        $exercise = PolygonExercise::factory()->create([
            'manual_tests' => $manualTests,
            'additional_solutions' => $additionalSolutions,
            'calibration_results' => $calibrationResults,
            'time_limit' => '3',
            'memory_limit' => '512',
            'output_limit' => '4096',
        ]);

        // Re-read from database
        $fresh = PolygonExercise::findOrFail($exercise->id);

        $this->assertIsArray($fresh->manual_tests);
        $this->assertCount(2, $fresh->manual_tests);
        $this->assertSame(1, $fresh->manual_tests[0]['number']);
        $this->assertTrue($fresh->manual_tests[0]['is_sample']);

        $this->assertIsArray($fresh->additional_solutions);
        $this->assertSame('py3', $fresh->additional_solutions[0]['lang']);

        $this->assertIsArray($fresh->calibration_results);
        $this->assertTrue($fresh->calibration_results['summary']['accept_passed']);

        $this->assertIsInt($fresh->time_limit);
        $this->assertSame(3, $fresh->time_limit);

        $this->assertIsInt($fresh->memory_limit);
        $this->assertSame(512, $fresh->memory_limit);

        $this->assertIsInt($fresh->output_limit);
        $this->assertSame(4096, $fresh->output_limit);
    }

    public function test_relationships_resolve_contest_problem_and_user(): void
    {
        $user = User::factory()->create();
        $contest = Contest::factory()->create();
        $problem = Problem::factory()->create(['contest_id' => $contest->id]);

        $exercise = PolygonExercise::factory()->create([
            'contest_id' => $contest->id,
            'created_by' => $user->user_id,
            'published_problem_id' => $problem->id,
        ]);

        $this->assertInstanceOf(Contest::class, $exercise->contest);
        $this->assertSame($contest->id, $exercise->contest->id);

        $this->assertInstanceOf(Problem::class, $exercise->publishedProblem);
        $this->assertSame($problem->id, $exercise->publishedProblem->id);

        $this->assertInstanceOf(User::class, $exercise->creator);
        $this->assertSame($user->user_id, $exercise->creator->user_id);

        $this->assertInstanceOf(User::class, $exercise->user);
        $this->assertSame($user->user_id, $exercise->user->user_id);
    }

    public function test_get_temp_directory_returns_expected_path(): void
    {
        $exercise = PolygonExercise::factory()->create();

        $expectedPath = storage_path("app/temp/polygon_{$exercise->id}");
        $this->assertSame($expectedPath, $exercise->getTempDirectory());
    }

    public function test_status_states_and_factory_helpers(): void
    {
        $draft = PolygonExercise::factory()->create();
        $this->assertSame(PolygonExercise::STATUS_DRAFT, $draft->status);

        $generated = PolygonExercise::factory()->generated()->create();
        $this->assertSame(PolygonExercise::STATUS_GENERATED, $generated->status);

        $calibrated = PolygonExercise::factory()->calibrated()->create();
        $this->assertSame(PolygonExercise::STATUS_CALIBRATED, $calibrated->status);
        $this->assertNotNull($calibrated->calibration_results);

        $published = PolygonExercise::factory()->published()->create();
        $this->assertSame(PolygonExercise::STATUS_PUBLISHED, $published->status);
        $this->assertSame('POLY-001', $published->published_bank_code);
    }

    public function test_foreign_key_contest_and_problem_null_on_delete(): void
    {
        $contest = Contest::factory()->create();
        $problem = Problem::factory()->create(['contest_id' => $contest->id]);

        $exercise = PolygonExercise::factory()->create([
            'contest_id' => $contest->id,
            'published_problem_id' => $problem->id,
        ]);

        $this->assertNotNull($exercise->contest_id);
        $this->assertNotNull($exercise->published_problem_id);

        // Delete problem (force delete because Problem has SoftDeletes)
        $problem->forceDelete();
        $exercise->refresh();
        $this->assertNull($exercise->published_problem_id);

        // Delete contest (force delete because Contest has SoftDeletes)
        $contest->forceDelete();
        $exercise->refresh();
        $this->assertNull($exercise->contest_id);
    }

    public function test_cascade_delete_when_creator_user_is_deleted(): void
    {
        $user = User::factory()->create();
        $exercise = PolygonExercise::factory()->create([
            'created_by' => $user->user_id,
        ]);

        $exerciseId = $exercise->id;
        $this->assertDatabaseHas('polygon_exercises', ['id' => $exerciseId]);

        // Delete creator user
        $user->forceDelete();

        $this->assertDatabaseMissing('polygon_exercises', ['id' => $exerciseId]);
    }

    public function test_migration_up_and_down_rollback_successfully(): void
    {
        $migration = require database_path('migrations/2026_10_15_000001_create_polygon_exercises_table.php');

        $this->assertTrue(Schema::hasTable('polygon_exercises'));

        $migration->down();
        $this->assertFalse(Schema::hasTable('polygon_exercises'));

        $migration->up();
        $this->assertTrue(Schema::hasTable('polygon_exercises'));

        // Verify key columns exist after re-migration
        $this->assertTrue(Schema::hasColumn('polygon_exercises', 'id'));
        $this->assertTrue(Schema::hasColumn('polygon_exercises', 'contest_id'));
        $this->assertTrue(Schema::hasColumn('polygon_exercises', 'name'));
        $this->assertTrue(Schema::hasColumn('polygon_exercises', 'short_name'));
        $this->assertTrue(Schema::hasColumn('polygon_exercises', 'basename'));
        $this->assertTrue(Schema::hasColumn('polygon_exercises', 'time_limit'));
        $this->assertTrue(Schema::hasColumn('polygon_exercises', 'memory_limit'));
        $this->assertTrue(Schema::hasColumn('polygon_exercises', 'output_limit'));
        $this->assertTrue(Schema::hasColumn('polygon_exercises', 'description'));
        $this->assertTrue(Schema::hasColumn('polygon_exercises', 'input_description'));
        $this->assertTrue(Schema::hasColumn('polygon_exercises', 'output_description'));
        $this->assertTrue(Schema::hasColumn('polygon_exercises', 'notes'));
        $this->assertTrue(Schema::hasColumn('polygon_exercises', 'color_name'));
        $this->assertTrue(Schema::hasColumn('polygon_exercises', 'color_hex'));
        $this->assertTrue(Schema::hasColumn('polygon_exercises', 'generator_source'));
        $this->assertTrue(Schema::hasColumn('polygon_exercises', 'generator_script'));
        $this->assertTrue(Schema::hasColumn('polygon_exercises', 'manual_tests'));
        $this->assertTrue(Schema::hasColumn('polygon_exercises', 'solution_accept'));
        $this->assertTrue(Schema::hasColumn('polygon_exercises', 'solution_tle'));
        $this->assertTrue(Schema::hasColumn('polygon_exercises', 'solution_wrong'));
        $this->assertTrue(Schema::hasColumn('polygon_exercises', 'additional_solutions'));
        $this->assertTrue(Schema::hasColumn('polygon_exercises', 'status'));
        $this->assertTrue(Schema::hasColumn('polygon_exercises', 'calibration_results'));
        $this->assertTrue(Schema::hasColumn('polygon_exercises', 'published_problem_id'));
        $this->assertTrue(Schema::hasColumn('polygon_exercises', 'published_bank_code'));
        $this->assertTrue(Schema::hasColumn('polygon_exercises', 'created_by'));
        $this->assertTrue(Schema::hasColumn('polygon_exercises', 'created_at'));
        $this->assertTrue(Schema::hasColumn('polygon_exercises', 'updated_at'));
    }
}
