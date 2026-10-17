<?php

namespace Tests\Feature;

use Database\Seeders\AdminSeeder;
use Helium\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_default_admin_user()
    {
        $this->assertDatabaseMissing('users', [
            'username' => 'admin',
        ]);

        $this->seed(AdminSeeder::class);

        $this->assertDatabaseHas('users', [
            'username' => 'admin',
            'email' => 'admin@microhelium.local',
            'user_type' => User::TYPE_ADMIN,
            'is_enabled' => true,
        ]);

        $user = User::where('username', 'admin')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('admin123', $user->password));
        $this->assertNull($user->contest_id);
        $this->assertNull($user->site_id);
    }

    public function test_it_respects_custom_configured_credentials()
    {
        Config::set('auth.admin.username', 'superadmin');
        Config::set('auth.admin.email', 'super@example.com');
        Config::set('auth.admin.fullname', 'Super Admin');
        Config::set('auth.admin.password', 'supersecret456');

        $this->seed(AdminSeeder::class);

        $this->assertDatabaseHas('users', [
            'username' => 'superadmin',
            'email' => 'super@example.com',
            'fullname' => 'Super Admin',
            'user_type' => User::TYPE_ADMIN,
        ]);

        $user = User::where('username', 'superadmin')->first();
        $this->assertTrue(Hash::check('supersecret456', $user->password));
    }

    public function test_it_is_idempotent_and_does_not_overwrite_existing_admin()
    {
        // First run creates admin
        $this->seed(AdminSeeder::class);

        $user = User::where('username', 'admin')->first();
        $this->assertNotNull($user);

        // User changes their password
        $user->password = Hash::make('new_changed_password_999');
        $user->save();

        // Second run must not overwrite the modified password or fail with duplicate key
        $this->seed(AdminSeeder::class);

        $user->refresh();
        $this->assertTrue(Hash::check('new_changed_password_999', $user->password));
        $this->assertFalse(Hash::check('admin123', $user->password));
    }

    public function test_it_runs_in_production_environment()
    {
        $this->app->detectEnvironment(fn () => 'production');

        $this->seed(AdminSeeder::class);

        $this->assertDatabaseHas('users', [
            'username' => 'admin',
            'user_type' => User::TYPE_ADMIN,
        ]);
    }
}
