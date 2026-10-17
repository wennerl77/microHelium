<?php

namespace App\Console\Commands;

use App\Models\Contest;
use App\Services\TeamBatchProvisioner;
use Illuminate\Console\Command;

class TeamBatchToggleCommand extends Command
{
    protected $signature = 'teams:toggle-access
                            {--contest= : Id do contest}
                            {--site= : Id opcional do site}
                            {--enable : Habilita o acesso das equipes}
                            {--disable : Bloqueia o acesso das equipes}';

    protected $description = 'Habilita ou bloqueia em lote o acesso das equipes de uma maratona';

    public function handle(TeamBatchProvisioner $provisioner): int
    {
        $contestId = $this->option('contest');
        if ($contestId === null) {
            $this->error('Informe --contest=<id>.');
            return self::FAILURE;
        }

        $contest = Contest::find((int) $contestId);
        if (! $contest) {
            $this->error("Contest {$contestId} nao encontrado.");
            return self::FAILURE;
        }

        $enable = (bool) $this->option('enable');
        $disable = (bool) $this->option('disable');

        if ($enable === $disable) {
            $this->error('Informe exatamente uma das opcoes: --enable ou --disable.');
            return self::FAILURE;
        }

        $siteId = $this->option('site') !== null ? (int) $this->option('site') : null;

        $updated = $provisioner->toggleContestTeamsAccess((int) $contestId, $enable, $siteId);

        $statusStr = $enable ? 'habilitada(s)' : 'bloqueada(s)';
        $this->info("{$updated} conta(s) de equipe {$statusStr} com sucesso no contest \"{$contest->name}\".");

        return self::SUCCESS;
    }
}
