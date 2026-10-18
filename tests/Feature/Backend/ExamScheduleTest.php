<?php

namespace Tests\Feature\Backend;

use App\Models\Contest;
use App\Models\ExamTemplate;
use App\Models\ProblemBank;
use App\Models\School;
use Database\Seeders\ExamTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamScheduleTest extends TestCase
{
    use RefreshDatabase;

    private function seedBank(): void
    {
        $codes = collect(ExamTemplateSeeder::TEMPLATES)->pluck('problems')->flatten()->unique();
        foreach ($codes as $code) {
            ProblemBank::create([
                'code' => $code, 'name' => "Q $code", 'difficulty' => 'easy', 'description' => 'd',
                'input_description' => 'i', 'output_description' => 'o', 'time_limit' => 1,
                'memory_limit' => 128, 'is_active' => true,
            ]);
        }
        $this->seed(ExamTemplateSeeder::class);
    }

    public function test_seeder_creates_three_templates_in_documented_order_and_is_idempotent(): void
    {
        $this->seedBank();
        $this->seed(ExamTemplateSeeder::class);

        $this->assertSame(3, ExamTemplate::count());
        $n2 = ExamTemplate::where('code', 'N2')->first();
        $this->assertSame(
            ['I03', 'T01', 'T02', 'I04', 'T03', 'T04', 'I07', 'I09', 'G01', 'G04', 'G05', 'G06'],
            $n2->problems->pluck('code')->all()
        );
        $this->assertSame(180, $n2->duration);
    }

    public function test_seeder_does_not_overwrite_ui_edits(): void
    {
        $this->seedBank();
        ExamTemplate::where('code', 'N1')->update(['duration' => 90]);
        $this->seed(ExamTemplateSeeder::class);

        $this->assertSame(90, ExamTemplate::where('code', 'N1')->value('duration'));
    }

    public function test_seeder_fails_when_bank_is_missing_problems(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->seed(ExamTemplateSeeder::class);
    }

    public function test_schedule_creates_contest_with_ordered_problems_and_only_matching_level_teams(): void
    {
        $this->seedBank();
        $admin = $this->createAdminUser();
        $school = School::create(['name' => 'Escola X']);
        $teamA = $school->teams()->create(['name' => 'A', 'level' => 2]);
        $teamA->members()->create(['name' => 'Carlos Silva', 'position' => 1]);
        $teamA->members()->create(['name' => 'Ana Souza', 'position' => 2]);
        $teamB = $school->teams()->create(['name' => 'B', 'level' => 2]);
        $school->teams()->create(['name' => 'C', 'level' => 1]);
        $template = ExamTemplate::where('code', 'N2')->first();

        $response = $this->actingAs($admin)->post(route('backend.exams.store'), [
            'template_id' => $template->id,
            'start_time' => now()->addDay()->format('Y-m-d H:i:s'),
            'school_ids' => [$school->id],
        ]);

        $response->assertOk()->assertViewIs('backend.exams.scheduled');
        $credentials = $response->viewData('credentials');
        $this->assertCount(2, $credentials);

        $credA = collect($credentials)->firstWhere('team', 'A');
        $this->assertNotNull($credA);
        $this->assertSame('Carlos Silva', $credA['leader']);
        $this->assertSame('Escola X', $credA['school']);
        $this->assertMatchesRegularExpression('/^team-[a-z0-9]{4}$/', $credA['username']);
        $this->assertSame(8, strlen($credA['password']));

        $userA = \Helium\User::where('username', $credA['username'])->first();
        $this->assertNotNull($userA);
        $this->assertSame('A', $userA->fullname);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check($credA['password'], $userA->password));
        $this->assertTrue((bool) $userA->is_enabled);

        $contest = Contest::where('exam_template_id', $template->id)->firstOrFail();
        $this->assertSame(180, (int) $contest->duration);
        $this->assertTrue($contest->is_active);
        $this->assertSame(
            ['I03', 'T01', 'T02', 'I04', 'T03', 'T04', 'I07', 'I09', 'G01', 'G04', 'G05', 'G06'],
            $contest->problems->pluck('basename')->map('strtoupper')->all()
        );
        $this->assertSame(['A', 'B', 'C'], array_slice($contest->problems->pluck('short_name')->all(), 0, 3));
        $this->assertSame(2, $contest->users()->count());
        $this->assertDatabaseCount('exam_participations', 2);
    }

    public function test_team_cannot_enter_a_second_exam_in_same_phase(): void
    {
        $this->seedBank();
        $admin = $this->createAdminUser();
        $school = School::create(['name' => 'Escola X']);
        $school->teams()->create(['name' => 'A', 'level' => 1]);
        $template = ExamTemplate::where('code', 'N1')->first();
        $payload = ['template_id' => $template->id, 'start_time' => now()->addDay()->format('Y-m-d H:i:s'), 'school_ids' => [$school->id]];

        $this->actingAs($admin)->post(route('backend.exams.store'), $payload)->assertOk();
        $this->actingAs($admin)->post(route('backend.exams.store'), $payload)->assertSessionHasErrors('school_ids');

        $this->assertSame(1, Contest::where('exam_template_id', $template->id)->count());
    }

    public function test_editing_template_does_not_change_existing_contest(): void
    {
        $this->seedBank();
        $admin = $this->createAdminUser();
        $school = School::create(['name' => 'Escola X']);
        $school->teams()->create(['name' => 'A', 'level' => 1]);
        $template = ExamTemplate::where('code', 'N1')->first();
        $this->actingAs($admin)->post(route('backend.exams.store'), [
            'template_id' => $template->id, 'start_time' => now()->addDay()->format('Y-m-d H:i:s'), 'school_ids' => [$school->id],
        ])->assertOk();

        $this->actingAs($admin)->put(route('backend.exam-templates.update', $template), [
            'name' => $template->name, 'duration' => 60, 'penalty' => 20, 'freeze_minutes' => 10,
            'problems' => [$template->problems->first()->id],
        ])->assertRedirect();

        $contest = Contest::where('exam_template_id', $template->id)->first();
        $this->assertSame(180, (int) $contest->duration);
        $this->assertSame(12, $contest->problems()->count());
        $this->assertSame(1, $template->fresh()->problems()->count());
    }

    public function test_templates_cannot_be_created_or_deleted_and_non_admin_is_blocked(): void
    {
        $this->seedBank();
        $admin = $this->createAdminUser();
        $template = ExamTemplate::first();

        $this->actingAs($admin)->post('/backend/exam-templates', [])->assertStatus(405);
        $this->actingAs($admin)->delete("/backend/exam-templates/{$template->id}")->assertStatus(405);
        $this->actingAs($this->createTestUser())->get(route('backend.exam-templates'))->assertStatus(403);
    }

    public function test_admin_pages_render(): void
    {
        $this->seedBank();
        $admin = $this->createAdminUser();
        School::create(['name' => 'E'])->teams()->create(['name' => 'T', 'level' => 1]);

        foreach (['backend.exam-templates', 'backend.exams', 'backend.schools'] as $r) {
            $this->actingAs($admin)->get(route($r))->assertOk();
        }
        $this->actingAs($admin)->get(route('backend.exam-templates.edit', ExamTemplate::first()))->assertOk();
        $this->actingAs($admin)->get(route('backend.exams.create', ['template_id' => ExamTemplate::first()->id]))->assertOk();
    }

    public function test_exam_template_selection_ui_renders_continue_button_and_schedule_flow(): void
    {
        $this->seedBank();
        $admin = $this->createAdminUser();
        $school = School::create(['name' => 'Instituto Alfa', 'city' => 'Belo Horizonte']);
        $school->teams()->create(['name' => 'Team 1', 'level' => 1]);
        $template = ExamTemplate::where('code', 'N1')->first();

        // 1. Templates listing has the "Agendar Prova" button pointing to create with template_id
        $templatesRes = $this->actingAs($admin)->get(route('backend.exam-templates'));
        $templatesRes->assertOk();
        $templatesRes->assertSee('Agendar Prova');
        $templatesRes->assertSee(route('backend.exams.create', ['template_id' => $template->id]));

        // 2. Exam create screen without template_id renders template select and visible "Continuar" button
        $createRes = $this->actingAs($admin)->get(route('backend.exams.create'));
        $createRes->assertOk();
        $createRes->assertSee('Template de prova');
        $createRes->assertSee('Continuar');
        $createRes->assertDontSee('Agendar Prova');

        // 3. Exam create screen with template_id renders template info and scheduling form
        $createWithTemplateRes = $this->actingAs($admin)->get(route('backend.exams.create', ['template_id' => $template->id]));
        $createWithTemplateRes->assertOk();
        $createWithTemplateRes->assertSee($template->name);
        $createWithTemplateRes->assertSee('Data e Horário de Início');
        $createWithTemplateRes->assertSee('name="start_date"', false);
        $createWithTemplateRes->assertSee('type="date"', false);
        $createWithTemplateRes->assertSee('name="start_time"', false);
        $createWithTemplateRes->assertSee('type="time"', false);
        $createWithTemplateRes->assertSee('Horário do servidor:');
        $createWithTemplateRes->assertSee('Instituto Alfa');
        $createWithTemplateRes->assertSee('1 time(s) elegíveis N1');
        $createWithTemplateRes->assertSee('Agendar Prova');
    }

    public function test_schedule_creates_contest_with_separated_date_and_time_inputs(): void
    {
        $this->seedBank();
        $admin = $this->createAdminUser();
        $school = School::create(['name' => 'Escola Y']);
        $school->teams()->create(['name' => 'Team N1', 'level' => 1]);
        $template = ExamTemplate::where('code', 'N1')->first();

        $targetDate = now()->addDays(2)->format('Y-m-d');
        $targetTime = '14:30';

        $response = $this->actingAs($admin)->post(route('backend.exams.store'), [
            'template_id' => $template->id,
            'start_date' => $targetDate,
            'start_time' => $targetTime,
            'school_ids' => [$school->id],
        ]);

        $response->assertOk()->assertViewIs('backend.exams.scheduled');
        $contest = Contest::where('exam_template_id', $template->id)->firstOrFail();
        $this->assertSame("$targetDate $targetTime:00", $contest->start_time->format('Y-m-d H:i:s'));
    }

    public function test_schedule_rejects_past_start_time_with_friendly_validation_error(): void
    {
        $this->seedBank();
        $admin = $this->createAdminUser();
        $school = School::create(['name' => 'Escola Z']);
        $school->teams()->create(['name' => 'Team N1', 'level' => 1]);
        $template = ExamTemplate::where('code', 'N1')->first();

        $pastDate = now()->subDay()->format('Y-m-d');
        $pastTime = '10:00';

        $response = $this->actingAs($admin)->from(route('backend.exams.create', ['template_id' => $template->id]))
            ->post(route('backend.exams.store'), [
                'template_id' => $template->id,
                'start_date' => $pastDate,
                'start_time' => $pastTime,
                'school_ids' => [$school->id],
            ]);

        $response->assertRedirect(route('backend.exams.create', ['template_id' => $template->id]));
        $response->assertSessionHasErrors('combined_start');
        $this->assertStringContainsString('posterior ao horário atual do servidor', session('errors')->first('combined_start'));
    }
}

