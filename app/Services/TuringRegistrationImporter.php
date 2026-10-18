<?php

namespace App\Services;

use App\Models\School;
use App\Models\SchoolTeam;
use App\Models\SchoolTeamMember;
use Illuminate\Support\Facades\DB;

/**
 * Importa e mapeia os inscritos da Maratona Turing a partir do CSV.
 * Mapeia os 10 campi do IFMG para escolas, determina o nível de cada time
 * conforme as regras da Maratona (1º ano -> N1, 2º/3º ano -> N2, Superior -> N3)
 * e cadastra todos os participantes.
 */
class TuringRegistrationImporter
{
    public const CAMPUS_MAP = [
        'Ouro Branco' => ['name' => 'IFMG - Campus Ouro Branco', 'city' => 'Ouro Branco'],
        'Betim' => ['name' => 'IFMG - Campus Betim', 'city' => 'Betim'],
        'Ponte Nova' => ['name' => 'IFMG - Campus Ponte Nova', 'city' => 'Ponte Nova'],
        'Ipatinga' => ['name' => 'IFMG - Campus Ipatinga', 'city' => 'Ipatinga'],
        'Bambuí' => ['name' => 'IFMG - Campus Bambuí', 'city' => 'Bambuí'],
        'Ibirité' => ['name' => 'IFMG - Campus Ibirité', 'city' => 'Ibirité'],
        'Ouro Preto' => ['name' => 'IFMG - Campus Ouro Preto', 'city' => 'Ouro Preto'],
        'São João Envangelista' => ['name' => 'IFMG - Campus São João Evangelista', 'city' => 'São João Evangelista'],
        'São João Evangelista' => ['name' => 'IFMG - Campus São João Evangelista', 'city' => 'São João Evangelista'],
        'Ribeirão das Neves' => ['name' => 'IFMG - Campus Ribeirão das Neves', 'city' => 'Ribeirão das Neves'],
        'Itabirito' => ['name' => 'IFMG - Campus Itabirito', 'city' => 'Itabirito'],
    ];

    /**
     * @param  string  $csvPath
     * @return array{schools: int, teams: int, members: int, levels: array<int, int>}
     */
    public function import(string $csvPath): array
    {
        if (! file_exists($csvPath) || ! is_readable($csvPath)) {
            throw new \InvalidArgumentException("Arquivo CSV não encontrado ou inacessível: {$csvPath}");
        }

        $handle = fopen($csvPath, 'r');
        if (! $handle) {
            throw new \RuntimeException("Falha ao abrir o arquivo: {$csvPath}");
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            throw new \RuntimeException("O arquivo CSV está vazio.");
        }

        $rows = [];
        while (($data = fgetcsv($handle)) !== false) {
            if (empty(array_filter($data))) {
                continue;
            }
            $rows[] = $data;
        }
        fclose($handle);

        return DB::transaction(function () use ($rows) {
            $schoolsCreated = 0;
            $teamsCount = 0;
            $membersCount = 0;
            $levelsCount = [1 => 0, 2 => 0, 3 => 0];

            foreach ($rows as $row) {
                $teamName = trim($row[2] ?? '');
                $rawCampus = trim($row[3] ?? '');

                if (empty($teamName) || empty($rawCampus)) {
                    continue;
                }

                $campusInfo = self::CAMPUS_MAP[$rawCampus] ?? [
                    'name' => "IFMG - Campus {$rawCampus}",
                    'city' => $rawCampus,
                ];

                $school = School::firstOrCreate(
                    ['name' => $campusInfo['name']],
                    ['city' => $campusInfo['city']]
                );

                // Integrantes
                $candidates = [
                    // P1
                    [
                        'name' => trim($row[4] ?? ''),
                        'academic_registry' => trim($row[5] ?? ''),
                        'email' => trim($row[6] ?? ''),
                        'phone' => trim($row[7] ?? ''),
                        'education_level' => trim($row[8] ?? ''),
                        'college_period' => trim($row[9] ?? ''),
                    ],
                    // P2
                    [
                        'name' => trim($row[10] ?? ''),
                        'academic_registry' => trim($row[11] ?? ''),
                        'email' => trim($row[12] ?? ''),
                        'phone' => trim($row[13] ?? ''),
                        'education_level' => trim($row[14] ?? ''),
                        'college_period' => trim($row[15] ?? ''),
                    ],
                    // P3
                    [
                        'name' => trim($row[17] ?? ''),
                        'academic_registry' => trim($row[18] ?? ''),
                        'email' => trim($row[19] ?? ''),
                        'phone' => trim($row[20] ?? ''),
                        'education_level' => trim($row[21] ?? ''),
                        'college_period' => trim($row[22] ?? ''),
                    ],
                ];

                $membersData = [];
                $seenKeys = [];

                foreach ($candidates as $candidate) {
                    if (empty($candidate['name'])) {
                        continue;
                    }

                    // Evita duplicação do mesmo aluno na mesma linha
                    $key = mb_strtolower($candidate['name']) . '|' . mb_strtolower($candidate['academic_registry']);
                    if (isset($seenKeys[$key])) {
                        continue;
                    }
                    $seenKeys[$key] = true;

                    $candidate['level'] = $this->determineMemberLevel($candidate['education_level']);
                    $membersData[] = $candidate;
                }

                if (empty($membersData)) {
                    continue;
                }

                // O nível do time é dado pelo integrante de maior escolaridade
                $memberLevels = array_column($membersData, 'level');
                $teamLevel = ! empty($memberLevels) ? max($memberLevels) : 2;

                $team = SchoolTeam::updateOrCreate(
                    ['school_id' => $school->id, 'name' => $teamName],
                    ['level' => $teamLevel]
                );

                // Sincroniza os membros do time
                $team->members()->delete();
                foreach ($membersData as $member) {
                    $team->members()->create([
                        'name' => $member['name'],
                        'academic_registry' => filled($member['academic_registry']) ? $member['academic_registry'] : null,
                        'email' => filled($member['email']) ? $member['email'] : null,
                        'phone' => filled($member['phone']) ? $member['phone'] : null,
                        'education_level' => filled($member['education_level']) ? $member['education_level'] : null,
                        'college_period' => (filled($member['college_period']) && mb_strtolower($member['college_period']) !== 'nenhum') ? $member['college_period'] : null,
                    ]);
                    $membersCount++;
                }

                $teamsCount++;
                $levelsCount[$teamLevel] = ($levelsCount[$teamLevel] ?? 0) + 1;
            }

            return [
                'schools' => School::count(),
                'teams' => $teamsCount,
                'members' => $membersCount,
                'levels' => $levelsCount,
            ];
        });
    }

    /**
     * Mapeia a escolaridade para o nível (1, 2 ou 3).
     * 1º ano -> Nível 1
     * 2º/3º ano -> Nível 2
     * Superior/Graduação -> Nível 3
     */
    public function determineMemberLevel(?string $educationLevel): int
    {
        if (empty($educationLevel)) {
            return 2;
        }

        $norm = mb_strtolower(trim($educationLevel));

        if (str_contains($norm, 'gradua') || str_contains($norm, 'superior')) {
            return 3;
        }

        if (str_contains($norm, '1º') || (str_contains($norm, '1') && str_contains($norm, 'médio'))) {
            return 1;
        }

        // 2º ano, 3º ano ou outros do ensino médio
        return 2;
    }
}
