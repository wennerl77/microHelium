<?php

namespace Database\Seeders;

use App\Models\School;
use App\Services\TuringRegistrationImporter;
use Illuminate\Database\Seeder;

/**
 * Seeder de Escolas, Times e Integrantes da Maratona Turing.
 * Se o CSV de inscrições existir em database/data/turing_registrations.csv,
 * importa todas as inscrições reais. Caso contrário, gera dados de exemplo para dev/test.
 */
class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = base_path('database/data/turing_registrations.csv');

        if (file_exists($csvPath)) {
            $importer = app(TuringRegistrationImporter::class);
            $result = $importer->import($csvPath);
            $this->command?->info("Inscrições importadas: {$result['teams']} times, {$result['members']} participantes em {$result['schools']} escolas.");
            return;
        }

        $data = [
            'Escola Exemplo Ouro Branco' => ['Ouro Branco', [['Alpha', 1], ['Beta', 1], ['Gama', 2]]],
            'Escola Exemplo Lafaiete' => ['Conselheiro Lafaiete', [['Delta', 2], ['Épsilon', 3]]],
        ];

        foreach ($data as $name => [$city, $teams]) {
            $school = School::firstOrCreate(['name' => $name], ['city' => $city]);
            foreach ($teams as [$teamName, $level]) {
                $school->teams()->firstOrCreate(['name' => $teamName], ['level' => $level]);
            }
        }
    }
}
