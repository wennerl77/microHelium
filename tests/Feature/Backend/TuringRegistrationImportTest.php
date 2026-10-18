<?php

namespace Tests\Feature\Backend;

use App\Models\School;
use App\Models\SchoolTeam;
use App\Models\SchoolTeamMember;
use App\Services\TuringRegistrationImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TuringRegistrationImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_importer_creates_schools_teams_and_members_with_correct_levels(): void
    {
        $csvPath = base_path('database/data/turing_registrations.csv');
        $this->assertFileExists($csvPath);

        $importer = app(TuringRegistrationImporter::class);
        $result = $importer->import($csvPath);

        $this->assertSame(10, $result['schools']);
        $this->assertSame(46, $result['teams']);
        $this->assertSame(129, $result['members']);
        $this->assertSame(10, $result['levels'][1]);
        $this->assertSame(23, $result['levels'][2]);
        $this->assertSame(13, $result['levels'][3]);

        // Verifica escolas
        $betim = School::where('city', 'Betim')->firstOrFail();
        $this->assertSame('IFMG - Campus Betim', $betim->name);
        $this->assertSame(9, $betim->teams()->count());

        // Verifica time N1 e seus integrantes
        $bugadosBetim = SchoolTeam::where('school_id', $betim->id)->where('name', 'Bugados')->firstOrFail();
        $this->assertSame(1, $bugadosBetim->level);
        $this->assertSame(3, $bugadosBetim->members()->count());
        $this->assertTrue($bugadosBetim->members()->where('name', 'like', 'Bernardo%')->exists());

        // Verifica time com mesmo nome em outra escola (Ouro Preto)
        $ouroPreto = School::where('city', 'Ouro Preto')->firstOrFail();
        $bugadosOP = SchoolTeam::where('school_id', $ouroPreto->id)->where('name', 'Bugados')->firstOrFail();
        $this->assertSame(1, $bugadosOP->level);
        $this->assertNotEquals($bugadosBetim->id, $bugadosOP->id);

        // Verifica time misto (Web Divas em Ouro Branco tem 1º e 2º ano -> Nível 2)
        $ouroBranco = School::where('city', 'Ouro Branco')->firstOrFail();
        $webDivas = SchoolTeam::where('school_id', $ouroBranco->id)->where('name', 'Web Divas')->firstOrFail();
        $this->assertSame(2, $webDivas->level);
    }

    public function test_importer_is_idempotent(): void
    {
        $csvPath = base_path('database/data/turing_registrations.csv');
        $importer = app(TuringRegistrationImporter::class);

        $first = $importer->import($csvPath);
        $second = $importer->import($csvPath);

        $this->assertSame($first['schools'], $second['schools']);
        $this->assertSame($first['teams'], $second['teams']);
        $this->assertSame($first['members'], $second['members']);
        $this->assertSame(10, School::count());
        $this->assertSame(46, SchoolTeam::count());
        $this->assertSame(129, SchoolTeamMember::count());
    }

    public function test_artisan_command_imports_registrations(): void
    {
        $this->artisan('turing:import-registrations')
            ->expectsOutputToContain('MARATONA TURING: INSCRIÇÕES IMPORTADAS COM SUCESSO')
            ->assertSuccessful();

        $this->assertSame(10, School::count());
        $this->assertSame(46, SchoolTeam::count());
    }

    public function test_admin_can_add_and_remove_team_member(): void
    {
        $admin = $this->createAdminUser();
        $school = School::create(['name' => 'Campus Teste', 'city' => 'Teste']);
        $team = $school->teams()->create(['name' => 'Time Teste', 'level' => 1]);

        // Adiciona participante
        $response = $this->actingAs($admin)->post(route('backend.schools.members.store', $team), [
            'name' => 'Aluno Teste',
            'academic_registry' => 'RA12345',
            'email' => 'aluno@teste.com',
            'education_level' => '1º ano do Ensino Médio',
        ]);
        $response->assertRedirect()->assertSessionHas('success');

        $member = $team->members()->firstOrFail();
        $this->assertSame('Aluno Teste', $member->name);
        $this->assertSame('RA12345', $member->academic_registry);

        // Remove participante
        $delResponse = $this->actingAs($admin)->delete(route('backend.schools.members.destroy', $member));
        $delResponse->assertRedirect()->assertSessionHas('success');
        $this->assertSame(0, $team->members()->count());
    }
}
