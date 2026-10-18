<?php

namespace App\Console\Commands;

use App\Services\TuringRegistrationImporter;
use Illuminate\Console\Command;

class TuringImportRegistrationsCommand extends Command
{
    protected $signature = 'turing:import-registrations
        {file? : Caminho do arquivo CSV de inscrições (opcional, padrão: database/data/turing_registrations.csv)}';

    protected $description = 'Importa as inscrições da Maratona Turing a partir do CSV (escolas, times por nível e integrantes)';

    public function handle(TuringRegistrationImporter $importer): int
    {
        $path = $this->argument('file');

        if (! $path) {
            $defaultData = base_path('database/data/turing_registrations.csv');
            $docData = '/home/viier/Documents/questoes-turing/Inscrição para a Maratona Turing 👨_💻👾.csv';

            if (file_exists($defaultData)) {
                $path = $defaultData;
            } elseif (file_exists($docData)) {
                $path = $docData;
            } else {
                $this->error("Arquivo de inscrições não encontrado no caminho padrão: {$defaultData}");
                return self::FAILURE;
            }
        } elseif (! is_file($path) && is_file(base_path($path))) {
            $path = base_path($path);
        }

        $this->info("Importando inscrições de: {$path}");

        try {
            $result = $importer->import($path);
        } catch (\Exception $e) {
            $this->error("Erro durante a importação: {$e->getMessage()}");
            return self::FAILURE;
        }

        $this->info("=================================================");
        $this->info("MARATONA TURING: INSCRIÇÕES IMPORTADAS COM SUCESSO");
        $this->info("=================================================");
        $this->line("Total de Escolas cadastradas: {$result['schools']}");
        $this->line("Total de Equipes importadas:  {$result['teams']}");
        $this->line("Total de Alunos cadastrados:  {$result['members']}");
        $this->info("-------------------------------------------------");
        $this->line("Distribuição de Equipes por Nível:");
        $this->line("  - Nível 1 (1º ano Médio):            {$result['levels'][1]} equipe(s)");
        $this->line("  - Nível 2 (2º e 3º ano Médio):       {$result['levels'][2]} equipe(s)");
        $this->line("  - Nível 3 (Superior / Graduação):    {$result['levels'][3]} equipe(s)");
        $this->info("=================================================");

        return self::SUCCESS;
    }
}
