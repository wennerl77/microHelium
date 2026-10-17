<?php

namespace Tests\Unit;

use App\Models\Contest;
use App\Models\Site;
use App\Services\TeamBatchProvisioner;
use Helium\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeamBatchProvisionerTest extends TestCase
{
    use RefreshDatabase;

    private TeamBatchProvisioner $provisioner;
    private Contest $contest;
    private Site $site;

    protected function setUp(): void
    {
        parent::setUp();
        $this->provisioner = new TeamBatchProvisioner();
        $this->contest = Contest::factory()->create();
        $this->site = Site::factory()->create(['contest_id' => $this->contest->id]);
    }

    public function test_generate_creates_specified_number_of_teams(): void
    {
        $result = $this->provisioner->generate(
            contestId: $this->contest->id,
            siteId: $this->site->id,
            count: 5,
            prefix: 'time',
            managedBy: null,
            enabled: false
        );

        $this->assertCount(5, $result['created']);
        $this->assertDatabaseCount('users', 5);

        foreach ($result['created'] as $item) {
            $this->assertStringStartsWith('time-', $item['username']);
            $this->assertNotEmpty($item['password_plain']);
            $this->assertEquals(8, strlen($item['password_plain']));

            $user = User::find($item['user']->user_id);
            $this->assertNotNull($user);
            $this->assertNull($user->email);
            $this->assertFalse($user->is_enabled);
            $this->assertEquals('team', $user->user_type);
            $this->assertEquals('private', $user->profile_visibility);
            $this->assertTrue(Hash::check($item['password_plain'], $user->password));
        }

        // CSV must have header and 5 rows
        $csvLines = array_filter(explode("\n", trim($result['csv'])));
        $this->assertCount(6, $csvLines);
        $this->assertStringContainsString('equipe,usuario,senha,site_id,contest_id', $csvLines[0]);
    }

    public function test_toggle_contest_teams_access(): void
    {
        $this->provisioner->generate(
            contestId: $this->contest->id,
            siteId: $this->site->id,
            count: 3,
            prefix: 'sbc',
            enabled: false
        );

        $this->assertEquals(0, User::where('contest_id', $this->contest->id)->where('is_enabled', true)->count());

        $updated = $this->provisioner->toggleContestTeamsAccess($this->contest->id, true);
        $this->assertEquals(3, $updated);
        $this->assertEquals(3, User::where('contest_id', $this->contest->id)->where('is_enabled', true)->count());

        $this->provisioner->toggleContestTeamsAccess($this->contest->id, false);
        $this->assertEquals(0, User::where('contest_id', $this->contest->id)->where('is_enabled', true)->count());
    }
}
