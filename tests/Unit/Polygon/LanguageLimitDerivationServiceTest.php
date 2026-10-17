<?php

namespace Tests\Unit\Polygon;

use App\Models\Contest;
use App\Models\Language;
use App\Models\PolygonExercise;
use App\Models\Problem;
use App\Models\ProblemLanguageLimit;
use App\Services\Polygon\LanguageLimitDerivationService;
use Helium\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageLimitDerivationServiceTest extends TestCase
{
    use RefreshDatabase;

    private LanguageLimitDerivationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LanguageLimitDerivationService();
    }

    public function test_derives_cpp_limits_at_1x_and_base_memory(): void
    {
        $cppExtensions = ['c', 'cpp', 'c_gcc13', 'c_clang17', 'c99_gcc', 'cpp_gpp13', 'cpp_clang'];

        foreach ($cppExtensions as $ext) {
            $result = $this->service->deriveForLanguage(
                ['extension' => $ext, 'name' => strtoupper($ext)],
                baseTimeLimit: 1,
                baseMemoryLimit: 256
            );

            $this->assertSame(1, $result['time_limit'], "Time limit should be 1.0x for {$ext}");
            $this->assertSame(256, $result['memory_limit'], "Memory limit should be base memory for {$ext}");
            $this->assertContains($result['source'], [
                LanguageLimitDerivationService::SOURCE_BASE,
                LanguageLimitDerivationService::SOURCE_MEASURED,
            ]);
        }

        // Test with higher base limits (2s, 512MB)
        $resultHigher = $this->service->deriveForLanguage(
            ['extension' => 'cpp', 'name' => 'C++'],
            baseTimeLimit: 2,
            baseMemoryLimit: 512
        );
        $this->assertSame(2, $resultHigher['time_limit']);
        $this->assertSame(512, $resultHigher['memory_limit']);

        // Test with custom measured source
        $resultMeasured = $this->service->deriveForLanguage(
            ['extension' => 'cpp', 'name' => 'C++'],
            baseTimeLimit: 1,
            baseMemoryLimit: 256,
            baseSource: LanguageLimitDerivationService::SOURCE_MEASURED
        );
        $this->assertSame(LanguageLimitDerivationService::SOURCE_MEASURED, $resultMeasured['source']);
    }

    public function test_derives_python_limits_at_3x_with_minimum_1s_startup(): void
    {
        $pythonExtensions = ['py', 'py3', 'python', 'pypy', 'pypy3'];

        foreach ($pythonExtensions as $ext) {
            $result = $this->service->deriveForLanguage(
                ['extension' => $ext, 'name' => 'Python'],
                baseTimeLimit: 1,
                baseMemoryLimit: 256
            );

            // ceil(max(1, 1 * 3.0)) = 3
            $this->assertSame(3, $result['time_limit'], "Time limit should be 3s for {$ext}");
            $this->assertSame(256, $result['memory_limit'], "Memory limit should remain base memory for {$ext}");
            $this->assertSame(LanguageLimitDerivationService::SOURCE_DERIVED_3X, $result['source']);
            $this->assertSame('python', $result['language']);
        }

        // Test baseTime = 2s -> 6s
        $result2s = $this->service->deriveForLanguage(
            ['extension' => 'py3', 'name' => 'Python 3'],
            baseTimeLimit: 2,
            baseMemoryLimit: 512
        );
        $this->assertSame(6, $result2s['time_limit']);
        $this->assertSame(512, $result2s['memory_limit']);

        // Test minimum startup: baseTime = 0.2s -> ceil(max(1, 0.2 * 3.0)) = ceil(max(1, 0.6)) = 1s
        $resultMin = $this->service->deriveForLanguage(
            ['extension' => 'py3', 'name' => 'Python 3'],
            baseTimeLimit: 0.2,
            baseMemoryLimit: 256
        );
        $this->assertSame(1, $resultMin['time_limit'], 'Should enforce minimum 1s startup for Python');

        // Test float ceiling rounding: baseTime = 1.4s -> 1.4 * 3.0 = 4.2 -> ceil = 5s
        $resultFloat = $this->service->deriveForLanguage(
            ['extension' => 'py3', 'name' => 'Python 3'],
            baseTimeLimit: 1.4,
            baseMemoryLimit: 256
        );
        $this->assertSame(5, $resultFloat['time_limit'], 'Should round up with ceil()');
    }

    public function test_derives_jvm_limits_at_2x_plus_1s_startup_and_plus_128mb_memory(): void
    {
        $jvmExtensions = ['java', 'java25', 'java21', 'java17', 'kt', 'scala'];

        foreach ($jvmExtensions as $ext) {
            $result = $this->service->deriveForLanguage(
                ['extension' => $ext, 'name' => strtoupper($ext)],
                baseTimeLimit: 1,
                baseMemoryLimit: 256
            );

            // ceil(1 * 2.0 + 1.0) = 3
            $this->assertSame(3, $result['time_limit'], "Time limit should be 2.0x + 1.0s for {$ext}");
            // 256 + 128 = 384 MB
            $this->assertSame(384, $result['memory_limit'], "Memory limit should be base + 128MB for {$ext}");
            $this->assertSame(LanguageLimitDerivationService::SOURCE_DERIVED_JVM, $result['source']);
        }

        // Test baseTime = 2s, baseMemory = 512MB
        // ceil(2 * 2.0 + 1.0) = 5s
        // 512 + 128 = 640MB
        $result2s = $this->service->deriveForLanguage(
            ['extension' => 'java25', 'name' => 'Java (OpenJDK 25 LTS)'],
            baseTimeLimit: 2,
            baseMemoryLimit: 512
        );
        $this->assertSame(5, $result2s['time_limit']);
        $this->assertSame(640, $result2s['memory_limit']);

        // Test float baseTime = 1.2s -> ceil(1.2 * 2.0 + 1.0) = ceil(3.4) = 4s
        $resultFloat = $this->service->deriveForLanguage(
            ['extension' => 'kt', 'name' => 'Kotlin'],
            baseTimeLimit: 1.2,
            baseMemoryLimit: 256
        );
        $this->assertSame(4, $resultFloat['time_limit']);
        $this->assertSame(384, $resultFloat['memory_limit']);
        $this->assertSame('kotlin', $resultFloat['language']);
    }

    public function test_derives_node_and_javascript_limits_at_2x_and_base_memory(): void
    {
        $nodeExtensions = ['js', 'ts', 'node', 'js_node24', 'js_node22'];

        foreach ($nodeExtensions as $ext) {
            $result = $this->service->deriveForLanguage(
                ['extension' => $ext, 'name' => strtoupper($ext)],
                baseTimeLimit: 1,
                baseMemoryLimit: 256
            );

            // ceil(1 * 2.0) = 2
            $this->assertSame(2, $result['time_limit'], "Time limit should be 2.0x for {$ext}");
            $this->assertSame(256, $result['memory_limit'], "Memory limit should be base memory for {$ext}");
            $this->assertSame(LanguageLimitDerivationService::SOURCE_DERIVED_2X, $result['source']);
        }

        // Test baseTime = 2s, baseMemory = 512MB -> 4s, 512MB
        $result2s = $this->service->deriveForLanguage(
            ['extension' => 'js_node24', 'name' => 'JavaScript (Node 24 LTS)'],
            baseTimeLimit: 2,
            baseMemoryLimit: 512
        );
        $this->assertSame(4, $result2s['time_limit']);
        $this->assertSame(512, $result2s['memory_limit']);

        // TypeScript family check
        $resultTs = $this->service->deriveForLanguage(
            ['extension' => 'ts', 'name' => 'TypeScript (Node 24)'],
            baseTimeLimit: 1,
            baseMemoryLimit: 256
        );
        $this->assertSame('typescript', $resultTs['language']);
        $this->assertSame(2, $resultTs['time_limit']);
    }

    public function test_derives_default_limits_for_other_or_unknown_languages(): void
    {
        $otherLanguages = ['scratch', 'portugol_studio', 'ruby', 'pascal', 'unknown_lang'];

        foreach ($otherLanguages as $ext) {
            $result = $this->service->deriveForLanguage(
                ['extension' => $ext, 'name' => ucfirst($ext)],
                baseTimeLimit: 1,
                baseMemoryLimit: 256
            );

            // Safe default: ceil(1 * 2.0) = 2
            $this->assertSame(2, $result['time_limit'], "Default time limit should be 2.0x for {$ext}");
            $this->assertSame(256, $result['memory_limit'], "Default memory limit should be base memory for {$ext}");
            $this->assertSame(LanguageLimitDerivationService::SOURCE_DERIVED_DEFAULT, $result['source']);
        }
    }

    public function test_derive_for_exercise_uses_exercise_limits(): void
    {
        $user = User::factory()->create();
        $contest = Contest::factory()->create();

        $exercise = PolygonExercise::factory()->create([
            'contest_id' => $contest->id,
            'created_by' => $user->user_id,
            'time_limit' => 2,
            'memory_limit' => 512,
        ]);

        $customLanguages = [
            ['id' => 101, 'extension' => 'cpp_gpp13', 'name' => 'C++ (G++ 15)'],
            ['id' => 102, 'extension' => 'py3', 'name' => 'Python 3.14'],
            ['id' => 103, 'extension' => 'java25', 'name' => 'Java (OpenJDK 25)'],
            ['id' => 104, 'extension' => 'js_node24', 'name' => 'JavaScript (Node 24)'],
            ['id' => 105, 'extension' => 'ruby', 'name' => 'Ruby 3'],
        ];

        $derived = $this->service->deriveLimits(
            $exercise->time_limit,
            $exercise->memory_limit,
            $customLanguages
        );

        $byExtension = collect($derived)->keyBy('extension');

        // C++: 1.0x -> 2s, 512MB
        $this->assertSame(2, $byExtension['cpp_gpp13']['time_limit']);
        $this->assertSame(512, $byExtension['cpp_gpp13']['memory_limit']);

        // Python: 3.0x -> 6s, 512MB
        $this->assertSame(6, $byExtension['py3']['time_limit']);
        $this->assertSame(512, $byExtension['py3']['memory_limit']);
        $this->assertSame(LanguageLimitDerivationService::SOURCE_DERIVED_3X, $byExtension['py3']['source']);

        // Java: 2.0x + 1s -> 5s, 512 + 128 = 640MB
        $this->assertSame(5, $byExtension['java25']['time_limit']);
        $this->assertSame(640, $byExtension['java25']['memory_limit']);
        $this->assertSame(LanguageLimitDerivationService::SOURCE_DERIVED_JVM, $byExtension['java25']['source']);

        // Node: 2.0x -> 4s, 512MB
        $this->assertSame(4, $byExtension['js_node24']['time_limit']);
        $this->assertSame(512, $byExtension['js_node24']['memory_limit']);
        $this->assertSame(LanguageLimitDerivationService::SOURCE_DERIVED_2X, $byExtension['js_node24']['source']);

        // Ruby: default 2.0x -> 4s, 512MB
        $this->assertSame(4, $byExtension['ruby']['time_limit']);
        $this->assertSame(512, $byExtension['ruby']['memory_limit']);
        $this->assertSame(LanguageLimitDerivationService::SOURCE_DERIVED_DEFAULT, $byExtension['ruby']['source']);

        // Also test directly via deriveForExercise
        $exerciseDerived = $this->service->deriveForExercise($exercise);
        $this->assertIsArray($exerciseDerived);
        $this->assertNotEmpty($exerciseDerived);
    }

    public function test_apply_limits_to_problem_persists_to_database(): void
    {
        $contest = Contest::factory()->create();
        $problem = Problem::factory()->create([
            'contest_id' => $contest->id,
            'time_limit' => 1,
            'memory_limit' => 256,
        ]);

        $langCpp = Language::factory()->create([
            'contest_id' => $contest->id,
            'name' => 'C++ (G++ 15)',
            'extension' => 'cpp_gpp13',
            'is_active' => true,
        ]);

        $langPy = Language::factory()->create([
            'contest_id' => $contest->id,
            'name' => 'Python 3.14',
            'extension' => 'py3',
            'is_active' => true,
        ]);

        $langJava = Language::factory()->create([
            'contest_id' => $contest->id,
            'name' => 'Java 25',
            'extension' => 'java25',
            'is_active' => true,
        ]);

        $langNode = Language::factory()->create([
            'contest_id' => $contest->id,
            'name' => 'JavaScript (Node 24)',
            'extension' => 'js_node24',
            'is_active' => true,
        ]);

        $derivedLimits = $this->service->deriveLimits(
            baseTimeLimit: $problem->time_limit,
            baseMemoryLimit: $problem->memory_limit,
            activeLanguages: [$langCpp, $langPy, $langJava, $langNode]
        );

        $this->service->applyLimitsToProblem($problem, $derivedLimits);

        // Verify database records in problem_language_limits
        $this->assertDatabaseHas('problem_language_limits', [
            'problem_id' => $problem->id,
            'language_id' => $langCpp->id,
            'time_limit' => 1,
            'memory_limit' => 256,
        ]);

        $this->assertDatabaseHas('problem_language_limits', [
            'problem_id' => $problem->id,
            'language_id' => $langPy->id,
            'time_limit' => 3,
            'memory_limit' => 256,
        ]);

        $this->assertDatabaseHas('problem_language_limits', [
            'problem_id' => $problem->id,
            'language_id' => $langJava->id,
            'time_limit' => 3,
            'memory_limit' => 384, // 256 + 128
        ]);

        $this->assertDatabaseHas('problem_language_limits', [
            'problem_id' => $problem->id,
            'language_id' => $langNode->id,
            'time_limit' => 2,
            'memory_limit' => 256,
        ]);

        // Verify Problem helper accessors reflect the saved overrides
        $problem->refresh();
        $this->assertSame(1, $problem->getTimeLimitFor($langCpp));
        $this->assertSame(256, $problem->getMemoryLimitFor($langCpp));

        $this->assertSame(3, $problem->getTimeLimitFor($langPy));
        $this->assertSame(256, $problem->getMemoryLimitFor($langPy));

        $this->assertSame(3, $problem->getTimeLimitFor($langJava));
        $this->assertSame(384, $problem->getMemoryLimitFor($langJava));

        $this->assertSame(2, $problem->getTimeLimitFor($langNode));
        $this->assertSame(256, $problem->getMemoryLimitFor($langNode));
    }

    public function test_apply_limits_to_problem_is_idempotent_and_updates_existing_records(): void
    {
        $contest = Contest::factory()->create();
        $problem = Problem::factory()->create(['contest_id' => $contest->id]);
        $lang = Language::factory()->create(['contest_id' => $contest->id, 'extension' => 'py3']);

        // First application: 3s, 256MB
        $initialLimits = [
            [
                'language_id' => $lang->id,
                'extension' => 'py3',
                'language' => 'python',
                'name' => 'Python 3',
                'time_limit' => 3,
                'memory_limit' => 256,
                'source' => LanguageLimitDerivationService::SOURCE_DERIVED_3X,
            ],
        ];

        $this->service->applyLimitsToProblem($problem, $initialLimits);

        $this->assertSame(1, ProblemLanguageLimit::where('problem_id', $problem->id)->where('language_id', $lang->id)->count());
        $this->assertSame(3, ProblemLanguageLimit::where('problem_id', $problem->id)->where('language_id', $lang->id)->first()->time_limit);

        // Re-application with updated limits: 6s, 512MB
        $updatedLimits = [
            [
                'language_id' => $lang->id,
                'extension' => 'py3',
                'language' => 'python',
                'name' => 'Python 3',
                'time_limit' => 6,
                'memory_limit' => 512,
                'source' => LanguageLimitDerivationService::SOURCE_DERIVED_3X,
            ],
        ];

        $this->service->applyLimitsToProblem($problem, $updatedLimits);

        // Still only 1 record (no duplicate error, updated values)
        $this->assertSame(1, ProblemLanguageLimit::where('problem_id', $problem->id)->where('language_id', $lang->id)->count());
        $this->assertSame(6, ProblemLanguageLimit::where('problem_id', $problem->id)->where('language_id', $lang->id)->first()->time_limit);
        $this->assertSame(512, ProblemLanguageLimit::where('problem_id', $problem->id)->where('language_id', $lang->id)->first()->memory_limit);
    }

    public function test_apply_limits_resolves_language_by_extension_when_language_id_is_null(): void
    {
        $contest = Contest::factory()->create();
        $problem = Problem::factory()->create(['contest_id' => $contest->id]);
        $lang = Language::factory()->create([
            'contest_id' => $contest->id,
            'extension' => 'kt',
            'name' => 'Kotlin',
        ]);

        $limitsWithoutId = [
            [
                'language_id' => null,
                'extension' => 'kt',
                'language' => 'kotlin',
                'name' => 'Kotlin',
                'time_limit' => 3,
                'memory_limit' => 384,
                'source' => LanguageLimitDerivationService::SOURCE_DERIVED_JVM,
            ],
        ];

        $this->service->applyLimitsToProblem($problem, $limitsWithoutId);

        $this->assertDatabaseHas('problem_language_limits', [
            'problem_id' => $problem->id,
            'language_id' => $lang->id,
            'time_limit' => 3,
            'memory_limit' => 384,
        ]);
    }
}
