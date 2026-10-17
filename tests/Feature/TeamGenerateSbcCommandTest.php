<?php

namespace Tests\Feature;

use App\Models\Contest;
use App\Models\Site;
use Helium\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamGenerateSbcCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_generates_teams_and_exports_csv(): void
    {
        $contest = Contest::factory()->create();
        $site = Site::factory()->create(['contest_id' => $contest->id]);

        $exportFile = tempnam(sys_get_temp_dir(), 'sbc_teams_');

        $this->artisan('teams:generate-sbc', [
            '--contest' => $contest->id,
            '--site' => $site->id,
            '--count' => 4,
            '--prefix' => 'time',
            '--export' => $exportFile,
        ])->assertExitCode(0);

        $this->assertDatabaseCount('users', 4);
        $this->assertFileExists($exportFile);

        $content = file_get_contents($exportFile);
        $this->assertStringContainsString('equipe,usuario,senha,site_id,contest_id', $content);
        $this->assertStringContainsString('time-', $content);

        @unlink($exportFile);
    }
}
