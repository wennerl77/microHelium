<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProvaPolygonSeeder extends Seeder
{
    /**
     * Carrega os 22 rascunhos da Prova 1 em polygon_exercises.
     * Como polygon_exercises.created_by é obrigatório, usa o primeiro usuário existente como dono.
     * Executado somente via `make prova-1`.
     */
    public function run(): void
    {
        $sqlPath = database_path('sql/prova_1_polygon.sql');

        if (!File::exists($sqlPath)) {
            $this->command?->error("Arquivo SQL não encontrado em: {$sqlPath}");
            return;
        }

        $ownerId = DB::table('users')->min('user_id');
        if ($ownerId === null) {
            $this->command?->error('Nenhum usuário encontrado para ser dono dos rascunhos do Polygon. Rode `make seed-admin` e depois `make prova-1`.');
            return;
        }

        $this->command?->info('Carregando Prova 1 no Polygon (dono: user_id ' . $ownerId . ')...');
        DB::unprepared('SET @prova_owner_id = ' . (int) $ownerId . ';' . "\n" . File::get($sqlPath));
    }
}
