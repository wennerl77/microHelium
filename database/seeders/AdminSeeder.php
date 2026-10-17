<?php

namespace Database\Seeders;

use Helium\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Provisions the default administrator account for production or fresh environments.
     */
    public function run(): void
    {
        $username = config('auth.admin.username') ?: env('DEFAULT_ADMIN_USERNAME', 'admin');
        $email = config('auth.admin.email') ?: env('DEFAULT_ADMIN_EMAIL', 'admin@microhelium.local');
        $fullname = config('auth.admin.fullname') ?: env('DEFAULT_ADMIN_NAME', 'Administrator');
        $password = config('auth.admin.password') ?: env('DEFAULT_ADMIN_PASSWORD', 'admin123');

        if (empty($password)) {
            $password = 'admin123';
        }

        if (app()->environment('production') && $password === 'admin123') {
            $this->command?->warn('⚠️  AVISO DE SEGURANÇA: Administrador criado com a senha padrão (admin123) em produção. Altere a senha imediatamente após o login!');
        }

        $existing = User::where('username', $username)->first();

        if ($existing) {
            $this->command?->info("Usuário administrador '{$username}' já existe. Nenhuma alteração foi realizada.");

            return;
        }

        User::create([
            'username' => $username,
            'fullname' => $fullname,
            'email' => $email,
            'password' => Hash::make($password),
            'user_type' => User::TYPE_ADMIN,
            'is_enabled' => true,
            'contest_id' => null,
            'site_id' => null,
        ]);

        $this->command?->info("Usuário administrador '{$username}' criado com sucesso.");
    }
}
