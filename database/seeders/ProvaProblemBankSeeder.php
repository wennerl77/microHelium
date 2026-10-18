<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProvaProblemBankSeeder extends Seeder
{
    /**
     * Carrega as 22 questões da Prova 1 em problem_bank (idempotente, sem dependência de usuário).
     * Executado somente via `make prova-1`.
     */
    public function run(): void
    {
        $sqlPath = database_path('sql/prova_1_problem_bank.sql');

        if (!File::exists($sqlPath)) {
            $this->command?->error("Arquivo SQL não encontrado em: {$sqlPath}");
            return;
        }

        $this->command?->info('Carregando Prova 1 no Problem Bank...');
        DB::unprepared(File::get($sqlPath));
    }
}
