<?php

namespace Tests\Feature;

use App\Models\Contest;
use Helium\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginWithUsernameTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_username_when_email_is_null(): void
    {
        $contest = Contest::factory()->create([
            'is_active' => true,
            'start_time' => now()->subHour(),
        ]);

        $user = $this->createTestUser([
            'username' => 'team-abc1',
            'email' => null,
            'password' => Hash::make('secret123'),
            'contest_id' => $contest->id,
            'is_enabled' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'team-abc1',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_still_login_with_email(): void
    {
        $user = $this->createTestUser([
            'username' => 'admin.user',
            'email' => 'admin@example.com',
            'password' => Hash::make('adminpass123'),
            'is_enabled' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'adminpass123',
        ]);

        $response->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_is_blocked_before_contest_start_time_when_disabled(): void
    {
        $futureTime = now()->addDays(1)->setHour(15)->setMinute(0)->setSecond(0);

        $contest = Contest::factory()->create([
            'is_active' => true,
            'start_time' => $futureTime,
        ]);

        $this->createTestUser([
            'username' => 'team-future',
            'email' => null,
            'password' => Hash::make('secret123'),
            'contest_id' => $contest->id,
            'is_enabled' => false,
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'team-future',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/login');
        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_user_is_automatically_enabled_and_logged_in_when_contest_has_started(): void
    {
        $pastTime = now()->subMinutes(10);

        $contest = Contest::factory()->create([
            'is_active' => true,
            'start_time' => $pastTime,
        ]);

        $user = $this->createTestUser([
            'username' => 'team-now',
            'email' => null,
            'password' => Hash::make('secret123'),
            'contest_id' => $contest->id,
            'is_enabled' => false, // Nasceu desabilitado!
        ]);

        $response = $this->post('/login', [
            'email' => 'team-now',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);

        // Deve ter auto-habilitado no banco
        $this->assertTrue($user->fresh()->is_enabled);
    }

    public function test_multi_contest_isolation_user_a_enters_user_b_is_blocked(): void
    {
        // Contest A começou hoje às 14:00
        $contestA = Contest::factory()->create([
            'name' => 'Maratona A',
            'is_active' => true,
            'start_time' => now()->subMinutes(30),
        ]);

        // Contest B é amanhã às 15:00
        $contestB = Contest::factory()->create([
            'name' => 'Maratona B',
            'is_active' => true,
            'start_time' => now()->addDay(),
        ]);

        $userA = $this->createTestUser([
            'username' => 'team-contest-a',
            'email' => null,
            'password' => Hash::make('pass-a'),
            'contest_id' => $contestA->id,
            'is_enabled' => false,
        ]);

        $userB = $this->createTestUser([
            'username' => 'team-contest-b',
            'email' => null,
            'password' => Hash::make('pass-b'),
            'contest_id' => $contestB->id,
            'is_enabled' => false,
        ]);

        // User A tenta logar: deve conseguir porque Contest A já iniciou
        $responseA = $this->post('/login', [
            'email' => 'team-contest-a',
            'password' => 'pass-a',
        ]);
        $responseA->assertRedirect('/home');
        $this->assertAuthenticatedAs($userA);

        // Logout
        $this->post('/logout');
        $this->assertGuest();

        // User B tenta logar: deve ser bloqueado porque Contest B só inicia amanhã
        $responseB = $this->from('/login')->post('/login', [
            'email' => 'team-contest-b',
            'password' => 'pass-b',
        ]);
        $responseB->assertRedirect('/login');
        $this->assertGuest();
        $responseB->assertSessionHasErrors('email');
    }
}
