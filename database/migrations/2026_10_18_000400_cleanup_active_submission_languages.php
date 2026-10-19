<?php

use App\Models\Language;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Limpa e padroniza as linguagens ativas de todos os contests existentes no banco.
 * Apenas C, C++, Java (21), Python 3 e JavaScript permanecem ativas na tela de
 * submissão dos alunos, com nomes limpos e sem ambiguidades de versão.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('languages')) {
            return;
        }

        $allowedExtensions = Language::DEFAULT_CONTEST_LANGUAGES;
        $friendlyNames = Language::FRIENDLY_NAMES;

        // Atualiza as 5 linguagens homologadas para ativas e com nome simplificado
        foreach ($friendlyNames as $ext => $name) {
            DB::table('languages')
                ->where('extension', $ext)
                ->update([
                    'is_active' => true,
                    'name' => $name,
                    'updated_at' => now(),
                ]);
        }

        // Desativa todas as outras linguagens em todos os contests
        DB::table('languages')
            ->whereNotIn('extension', array_keys($friendlyNames))
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Reversão não é necessária para padronização de flags de interface
    }
};
