<?php

namespace Database\Seeders;

use App\Models\ExamTemplate;
use App\Models\ProblemBank;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Templates fixos da Maratona Turing, conforme as pastas nivel-1/2/3 do
 * repositorio de questoes (ordem = prefixo numerico dos arquivos).
 *
 * Idempotente e nao destrutivo: um template que ja existe NAO e tocado, para
 * nunca reverter edicoes feitas pela UI. Exige que o banco ja tenha as
 * questoes (Prova1Seeder).
 */
class ExamTemplateSeeder extends Seeder
{
    public const TEMPLATES = [
        'N1' => [
            'name' => 'Maratona Turing N1 (Iniciante)',
            'level' => 1,
            'problems' => ['I01', 'I02', 'I03', 'I04', 'I05', 'I06', 'I07', 'I08', 'I09', 'I10', 'I11', 'I12'],
        ],
        'N2' => [
            'name' => 'Maratona Turing N2 (Técnico)',
            'level' => 2,
            'problems' => ['I03', 'T01', 'T02', 'I04', 'T03', 'T04', 'I07', 'I09', 'G01', 'G04', 'G05', 'G06'],
        ],
        'N3' => [
            'name' => 'Maratona Turing N3 (Graduação)',
            'level' => 3,
            'problems' => ['I02', 'T01', 'T02', 'I05', 'I06', 'T04', 'G01', 'G02', 'G03', 'G04', 'G05', 'G06'],
        ],
    ];

    public function run(): void
    {
        $codes = collect(self::TEMPLATES)->pluck('problems')->flatten()->unique()->all();
        $ids = ProblemBank::whereIn('code', $codes)->pluck('id', 'code');

        $missing = array_values(array_diff($codes, $ids->keys()->all()));
        if ($missing) {
            throw new \RuntimeException('Questões ausentes no problem_bank (rode Prova1Seeder antes): ' . implode(', ', $missing));
        }

        DB::transaction(function () use ($ids) {
            foreach (self::TEMPLATES as $code => $def) {
                if (ExamTemplate::where('code', $code)->exists()) {
                    continue;
                }

                $template = ExamTemplate::create([
                    'code' => $code,
                    'name' => $def['name'],
                    'level' => $def['level'],
                    'duration' => 180,
                    'penalty' => 20,
                    'freeze_minutes' => 60,
                ]);

                foreach ($def['problems'] as $i => $problemCode) {
                    $template->problems()->attach($ids[$problemCode], ['position' => $i + 1]);
                }
            }
        });

        $this->command?->info('Templates N1/N2/N3 prontos.');
    }
}
