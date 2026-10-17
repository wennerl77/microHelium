<?php

namespace App\Console\Commands;

use App\Models\Contest;
use App\Models\Site;
use App\Services\TeamBatchProvisioner;
use Helium\User;
use Illuminate\Console\Command;

class TeamGenerateSbcCommand extends Command
{
    protected $signature = 'teams:generate-sbc
                            {--contest= : Id do contest que recebe as equipes}
                            {--site= : Id do site/sede onde as equipes competem}
                            {--count=10 : Quantidade de equipes para gerar}
                            {--prefix=team : Prefixo do nome de usuario}
                            {--as= : Usuario admin responsavel pelas contas}
                            {--export= : Grava os logins e senhas gerados neste arquivo CSV}
                            {--enabled : Cria as contas ja habilitadas para acesso}';

    protected $description = 'Gera equipes em lote com credenciais aleatorias estilo SBC/BOCA';

    public function handle(TeamBatchProvisioner $provisioner): int
    {
        $contestId = $this->option('contest');
        if ($contestId === null) {
            $this->error('Informe --contest=<id>. E necessario indicar qual maratona recebe as equipes.');
            return self::FAILURE;
        }

        $contest = Contest::find((int) $contestId);
        if (! $contest) {
            $this->error("Contest {$contestId} nao encontrado.");
            return self::FAILURE;
        }

        $siteId = $this->option('site');
        if ($siteId === null) {
            $sites = Site::query()->where('contest_id', $contest->id)->pluck('name', 'id');
            if ($sites->count() === 1) {
                $siteId = (int) $sites->keys()->first();
            } else {
                $this->error('Informe --site=<id>. Sites disponíveis: ' . ($sites->isEmpty() ? '(nenhum)' : $sites->map(fn ($n, $i) => "{$i}={$n}")->implode(', ')));
                return self::FAILURE;
            }
        } else {
            $siteId = (int) $siteId;
            if (! Site::query()->where('contest_id', $contest->id)->where('id', $siteId)->exists()) {
                $this->error("Site {$siteId} nao pertence ao contest {$contest->id} ({$contest->name}).");
                return self::FAILURE;
            }
        }

        $count = (int) $this->option('count');
        if ($count < 1 || $count > 500) {
            $this->error('--count deve ser um numero entre 1 e 500.');
            return self::FAILURE;
        }

        $prefix = (string) $this->option('prefix');

        $adminId = null;
        if ($asUsername = $this->option('as')) {
            $admin = User::query()->whereRaw('LOWER(TRIM(username)) = ?', [mb_strtolower(trim((string) $asUsername))])->first();
            if (! $admin) {
                $this->error("Usuario admin '{$asUsername}' nao encontrado.");
                return self::FAILURE;
            }
            $adminId = $admin->user_id;
        }

        $enabled = (bool) $this->option('enabled');

        $result = $provisioner->generate(
            contestId: $contest->id,
            siteId: $siteId,
            count: $count,
            prefix: $prefix,
            managedBy: $adminId,
            enabled: $enabled
        );

        $created = $result['created'];

        $this->info(count($created) . " equipe(s) criada(s) com sucesso no contest \"{$contest->name}\".");
        $this->table(
            ['Equipe', 'Usuário', 'Senha Gerada', 'Site', 'Status'],
            array_map(fn (array $item) => [
                $item['fullname'],
                $item['username'],
                $item['password_plain'],
                $item['site_id'],
                $enabled ? 'Habilitado' : 'Desabilitado (bloqueado ate liberacao)',
            ], $created)
        );

        if ($exportPath = $this->option('export')) {
            file_put_contents($exportPath, $result['csv']);
            $this->newLine();
            $this->info("Credenciais gravadas com sucesso no arquivo CSV: {$exportPath}");
            $this->comment('Imprima as filipetas para as equipes e proteja este arquivo com segurança.');
        }

        return self::SUCCESS;
    }
}
