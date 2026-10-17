<?php

namespace Tests\Feature;

use App\Models\Contest;
use App\Models\Site;
use Helium\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeamBatchToggleCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_enables_and_disables_teams(): void
    {
        $contest = Contest::factory()->create();
        $site = Site::factory()->create(['contest_id' => $contest->id]);

        User::create([
            'fullname' => 'Equipe 1',
            'username' => 'team-1',
            'password' => Hash::make('password123'),
            'user_type' => User::TYPE_TEAM,
            'contest_id' => $contest->id,
            'site_id' => $site->id,
            'is_enabled' => false,
        ]);

        User::create([
            'fullname' => 'Equipe 2',
            'username' => 'team-2',
            'password' => Hash::make('password123'),
            'user_type' => User::TYPE_TEAM,
            'contest_id' => $contest->id,
            'site_id' => $site->id,
            'is_enabled' => false,
        ]);

        $this->artisan('teams:toggle-access', [
            '--contest' => $contest->id,
            '--enable' => true,
        ])->assertExitCode(0);

        $this->assertEquals(2, User::where('contest_id', $contest->id)->where('is_enabled', true)->count());

        $this->artisan('teams:toggle-access', [
            '--contest' => $contest->id,
            '--disable' => true,
        ])->assertExitCode(0);

        $this->assertEquals(0, User::where('contest_id', $contest->id)->where('is_enabled', true)->count());
    }
}
