<?php

namespace Tests\Feature;

use App\Models\Contest;
use App\Models\ExamTemplate;
use App\Models\Problem;
use App\Models\ProblemBank;
use App\Models\School;
use App\Services\ExamScheduler;
use Database\Seeders\ExamTemplateSeeder;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ExamPreStartVisibilityTest extends TestCase
{
    private function seedTemplatesAndBank(): void
    {
        $codes = collect(ExamTemplateSeeder::TEMPLATES)->pluck('problems')->flatten()->unique();
        foreach ($codes as $code) {
            ProblemBank::create([
                'code' => $code,
                'name' => "Questão $code",
                'difficulty' => 'easy',
                'description' => "Enunciado completo da questão $code",
                'input_description' => 'Entrada',
                'output_description' => 'Saída',
                'time_limit' => 1,
                'memory_limit' => 128,
                'is_active' => true,
            ]);
        }
        $this->seed(ExamTemplateSeeder::class);
    }

    public function test_scheduled_exam_is_created_with_is_active_true(): void
    {
        $this->seedTemplatesAndBank();
        $admin = $this->createAdminUser();
        $school = School::create(['name' => 'Escola Teste']);
        $school->teams()->create(['name' => 'Time Alfa', 'level' => 1]);

        $template = ExamTemplate::where('code', 'N1')->firstOrFail();
        $startTime = now()->addHour();

        $scheduler = app(ExamScheduler::class);
        $result = $scheduler->schedule($template, $startTime, [$school->id], $admin->user_id);

        $contest = $result['contest'];
        $this->assertTrue((bool) $contest->is_active);
    }

    public function test_api_contest_current_prioritizes_logged_in_user_contest(): void
    {
        $this->seedTemplatesAndBank();
        $admin = $this->createAdminUser();
        $school = School::create(['name' => 'Escola Teste']);
        $school->teams()->create(['name' => 'Time Alfa', 'level' => 1]);

        $template = ExamTemplate::where('code', 'N1')->firstOrFail();
        $startTime = now()->addHour();

        $scheduler = app(ExamScheduler::class);
        $result = $scheduler->schedule($template, $startTime, [$school->id], $admin->user_id);

        $cred = $result['credentials'][0];
        $student = \Helium\User::where('username', $cred['username'])->firstOrFail();

        // Another unrelated contest that is active
        Contest::factory()->running(30)->create(['name' => 'Outra Competicao', 'is_public' => true]);

        $response = $this->actingAs($student)->getJson('/api/contest/current');
        $response->assertOk();
        $this->assertSame($result['contest']->id, $response->json('id'));
        $this->assertSame($result['contest']->name, $response->json('name'));
    }

    public function test_student_cannot_see_problems_before_contest_starts(): void
    {
        $this->seedTemplatesAndBank();
        $admin = $this->createAdminUser();
        $school = School::create(['name' => 'Escola Teste']);
        $school->teams()->create(['name' => 'Time Alfa', 'level' => 1]);

        $template = ExamTemplate::where('code', 'N1')->firstOrFail();
        $startTime = now()->addHour();

        $scheduler = app(ExamScheduler::class);
        $result = $scheduler->schedule($template, $startTime, [$school->id], $admin->user_id);

        $cred = $result['credentials'][0];
        $student = \Helium\User::where('username', $cred['username'])->firstOrFail();
        $problem = $result['contest']->problems()->firstOrFail();

        // 1. /exercises list does not leak problems and informs user
        $response = $this->actingAs($student)->get(route('exercises'));
        $response->assertOk();
        $response->assertViewHas('notStartedYet', true);
        $this->assertCount(0, $response->viewData('problems'));
        $response->assertSee('A prova ainda não começou');
        $response->assertDontSee($problem->name);

        // 2. /exercise/{problem} redirects back to exercises with message
        $responseShow = $this->actingAs($student)->get(route('exercise.show', $problem));
        $responseShow->assertRedirect(route('exercises'));
        $responseShow->assertSessionHas('error');

        // 3. /exercise/{problem}/submit redirects back to exercises
        $responseSubmit = $this->actingAs($student)->get(route('exercise.submit', $problem));
        $responseSubmit->assertRedirect(route('exercises'));

        // 4. API /api/problems does not list the problems for student
        $apiList = $this->actingAs($student)->getJson('/api/problems');
        $apiList->assertOk();
        $this->assertCount(0, $apiList->json('data'));

        // 5. API /api/problems/{problem} returns 404
        $apiDetail = $this->actingAs($student)->getJson("/api/problems/{$problem->id}");
        $apiDetail->assertStatus(404);
    }

    public function test_staff_can_view_problems_before_contest_starts(): void
    {
        $this->seedTemplatesAndBank();
        $admin = $this->createAdminUser();
        $school = School::create(['name' => 'Escola Teste']);
        $school->teams()->create(['name' => 'Time Alfa', 'level' => 1]);

        $template = ExamTemplate::where('code', 'N1')->firstOrFail();
        $startTime = now()->addHour();

        $scheduler = app(ExamScheduler::class);
        $result = $scheduler->schedule($template, $startTime, [$school->id], $admin->user_id);
        $problem = $result['contest']->problems()->firstOrFail();

        $responseShow = $this->actingAs($admin)->get(route('exercise.show', $problem));
        $responseShow->assertOk();
        $responseShow->assertSee($problem->name);

        $responseSubmit = $this->actingAs($admin)->get(route('exercise.submit', $problem));
        $responseSubmit->assertOk();

        $apiDetail = $this->actingAs($admin)->getJson("/api/problems/{$problem->id}");
        $apiDetail->assertOk();
        $this->assertSame($problem->id, $apiDetail->json('id'));
    }

    public function test_student_can_view_and_submit_problems_once_contest_starts(): void
    {
        $this->seedTemplatesAndBank();
        $admin = $this->createAdminUser();
        $school = School::create(['name' => 'Escola Teste']);
        $school->teams()->create(['name' => 'Time Alfa', 'level' => 1]);

        $template = ExamTemplate::where('code', 'N1')->firstOrFail();
        $startTime = now()->addHour();

        $scheduler = app(ExamScheduler::class);
        $result = $scheduler->schedule($template, $startTime, [$school->id], $admin->user_id);

        $cred = $result['credentials'][0];
        $student = \Helium\User::where('username', $cred['username'])->firstOrFail();
        $problem = $result['contest']->problems()->firstOrFail();

        // Fast-forward time to 5 minutes after start_time
        Carbon::setTestNow($startTime->copy()->addMinutes(5));

        // 1. /exercises now shows the problem list
        $response = $this->actingAs($student)->get(route('exercises'));
        $response->assertOk();
        $response->assertViewHas('notStartedYet', false);
        $this->assertGreaterThan(0, $response->viewData('problems')->count());
        $response->assertSee($problem->name);

        // 2. /exercise/{problem} renders the problem
        $responseShow = $this->actingAs($student)->get(route('exercise.show', $problem));
        $responseShow->assertOk();
        $responseShow->assertSee($problem->name);

        // 3. /exercise/{problem}/submit renders the submission page
        $responseSubmit = $this->actingAs($student)->get(route('exercise.submit', $problem));
        $responseSubmit->assertOk();

        // 4. API list shows problems
        $apiList = $this->actingAs($student)->getJson('/api/problems');
        $apiList->assertOk();
        $this->assertGreaterThan(0, count($apiList->json('data')));

        // 5. API detail shows problem
        $apiDetail = $this->actingAs($student)->getJson("/api/problems/{$problem->id}");
        $apiDetail->assertOk();
        $this->assertSame($problem->id, $apiDetail->json('id'));

        Carbon::setTestNow();
    }
}
