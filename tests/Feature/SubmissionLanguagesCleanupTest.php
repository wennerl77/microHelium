<?php

namespace Tests\Feature;

use App\Http\Controllers\Backend\ContestWizardController;
use App\Models\Contest;
use App\Models\Language;
use App\Models\Problem;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmissionLanguagesCleanupTest extends TestCase
{
    use RefreshDatabase;

    private function createContestWithScaffolding(): array
    {
        $contest = Contest::create([
            'name' => 'Maratona Turing Test',
            'start_time' => now()->subHour(),
            'duration' => 300,
            'freeze_time' => 60,
            'penalty' => 20,
            'max_file_size' => 1024,
            'is_active' => true,
            'is_public' => true,
        ]);

        ContestWizardController::provisionContestScaffolding($contest->id, []);

        $site = Site::where('contest_id', $contest->id)->first();
        $site->update(['auto_judge' => true, 'is_active' => true, 'permit_logins' => true]);

        $problem = Problem::create([
            'contest_id' => $contest->id,
            'short_name' => 'A',
            'name' => 'Soma Simples',
            'basename' => 'soma',
            'description' => 'Calcule A + B',
            'time_limit' => 1,
            'memory_limit' => 256,
            'is_active' => true,
        ]);

        $user = $this->createTestUser([
            'contest_id' => $contest->id,
            'site_id' => $site->id,
        ]);

        return [$contest, $problem, $user];
    }

    public function test_provisioning_activates_only_the_5_standard_languages_with_friendly_names(): void
    {
        [$contest] = $this->createContestWithScaffolding();

        $activeLanguages = Language::where('contest_id', $contest->id)
            ->where('is_active', true)
            ->get();

        $this->assertCount(5, $activeLanguages);

        $expected = [
            'c_gcc13' => 'C',
            'cpp_gpp13' => 'C++',
            'java21' => 'Java',
            'py3' => 'Python 3',
            'js_node24' => 'JavaScript',
        ];

        foreach ($expected as $extension => $expectedName) {
            $lang = $activeLanguages->firstWhere('extension', $extension);
            $this->assertNotNull($lang, "Extensão {$extension} deveria estar ativa.");
            $this->assertSame($expectedName, $lang->name);
        }

        // Verifica que linguagens confusas foram desativadas
        $inactiveExtensions = ['java17', 'java25', 'c_clang17', 'c99_gcc', 'cpp14_gpp', 'cpp17_gpp', 'rs', 'go', 'sh'];
        foreach ($inactiveExtensions as $extension) {
            $lang = Language::where('contest_id', $contest->id)->where('extension', $extension)->first();
            if ($lang) {
                $this->assertFalse((bool) $lang->is_active, "Extensão {$extension} deveria estar inativa.");
            }
        }
    }

    public function test_submit_screen_shows_only_5_clean_languages_and_no_confusing_versions(): void
    {
        [$contest, $problem, $user] = $this->createContestWithScaffolding();

        $response = $this->actingAs($user)->get(route('exercise.submit', $problem));

        $response->assertOk();
        $response->assertSee('Selecione a linguagem...');

        // Deve exibir as 5 limpas
        $response->assertSee('<option value="', false);
        $response->assertSee('>C<', false);
        $response->assertSee('>C++<', false);
        $response->assertSee('>Java<', false);
        $response->assertSee('>Python 3<', false);
        $response->assertSee('>JavaScript<', false);

        // NÃO deve conter versões confusas de Java ou compiladores redundantes
        $response->assertDontSee('Java (OpenJDK 17 LTS)');
        $response->assertDontSee('Java (OpenJDK 25 LTS)');
        $response->assertDontSee('C (Clang 22)');
        $response->assertDontSee('C99 (GCC 15)');
        $response->assertDontSee('C++14 (G++ 15)');
        $response->assertDontSee('C++17 (G++ 15)');
    }

    public function test_submitting_with_inactive_language_is_rejected(): void
    {
        [$contest, $problem, $user] = $this->createContestWithScaffolding();

        $java17 = Language::where('contest_id', $contest->id)->where('extension', 'java17')->firstOrFail();

        $response = $this->actingAs($user)->post(route('exercise.submit', $problem), [
            'language_id' => $java17->id,
            'code_text' => 'public class Main { public static void main(String[] args) {} }',
        ]);

        $response->assertSessionHasErrors('language_id');
    }

    public function test_submitting_with_clean_java_succeeds(): void
    {
        [$contest, $problem, $user] = $this->createContestWithScaffolding();

        $java = Language::where('contest_id', $contest->id)->where('extension', 'java21')->firstOrFail();

        $response = $this->actingAs($user)->post(route('exercise.submit', $problem), [
            'language_id' => $java->id,
            'code_text' => "import java.util.Scanner;\npublic class Main {\n    public static void main(String[] args) {\n        System.out.println(\"Hello\");\n    }\n}",
        ]);

        $response->assertRedirect(route('submissions'));
        $this->assertDatabaseHas('runs', [
            'contest_id' => $contest->id,
            'problem_id' => $problem->id,
            'language_id' => $java->id,
        ]);
    }
}
