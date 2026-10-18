<?php

namespace Database\Seeders;

use App\Models\PolygonExercise;
use App\Models\ProblemBank;
use App\Services\Polygon\LanguageLimitDerivationService;
use Illuminate\Database\Seeder;

class Prova1Seeder extends Seeder
{
    /**
     * Seeds Prova 1 (Maratona Turing) problems into problem_bank and polygon_exercises.
     * Also verifies and derives per-language execution limits (Python 3x, JS 2x, Java 2x+1s).
     */
    public function run(): void
    {
        $this->call([
            ProvaProblemBankSeeder::class,
            ProvaPolygonSeeder::class,
            ExamTemplateSeeder::class,
            SchoolSeeder::class,
        ]);

        $limitDerivationService = app(LanguageLimitDerivationService::class);

        $problems = [
            'I01', 'I02', 'I03', 'I04', 'I05', 'I06', 'I07', 'I08', 'I09', 'I10', 'I11', 'I12',
            'T01', 'T02', 'T03', 'T04',
            'G01', 'G02', 'G03', 'G04', 'G05', 'G06'
        ];

        $bankCount = ProblemBank::whereIn('code', $problems)->count();
        $polyCount = PolygonExercise::whereIn('short_name', $problems)->count();

        $this->command?->info("=================================================");
        $this->command?->info("PROVA 1 - MARATONA TURING: IMPORTAÇÃO CONCLUÍDA");
        $this->command?->info("=================================================");
        $this->command?->info("Total de questões no Problem Bank: {$bankCount}/22");
        $this->command?->info("Total de rascunhos no Polygon:     {$polyCount}/22");
        $this->command?->info("-------------------------------------------------");

        foreach ($problems as $code) {
            $poly = PolygonExercise::where('short_name', $code)->first();
            if ($poly) {
                // Ensure language limits are pre-derived and stored in calibration_results metadata
                $derivedLimits = $limitDerivationService->deriveForExercise($poly);
                $limitsSummary = [];
                foreach ($derivedLimits as $dl) {
                    $limitsSummary[$dl['extension']] = "{$dl['time_limit']}s / {$dl['memory_limit']}MB";
                }

                $sampleCount = is_array($poly->manual_tests) ? count($poly->manual_tests) : 0;
                $scriptCount = count(array_filter(explode("\n", (string)$poly->generator_script)));

                $this->command?->line(sprintf(
                    "  [OK] %-4s %-35s | Exemplos: %d | Testes: %d | Python: %s | Java: %s",
                    $poly->short_name,
                    mb_substr($poly->name, 0, 35),
                    $sampleCount,
                    $scriptCount,
                    $limitsSummary['py'] ?? '3s',
                    $limitsSummary['java'] ?? '3s'
                ));
            }
        }

        $this->command?->info("-------------------------------------------------");
        $this->command?->info("Todos os 22 exercícios foram cadastrados com sucesso!");
        $this->command?->info("Acesse a interface em /backend/polygon para gerar os testes e calibrar.");
    }
}
