<?php

namespace App\Services;

use App\Models\Contest;
use App\Models\Site;
use Helium\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeamBatchProvisioner
{
    /**
     * Caracteres seguros e legíveis para senhas (evita 0, O, 1, I, l).
     */
    private const PASSWORD_CHARS = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';

    /**
     * Caracteres para o sufixo aleatório do username.
     */
    private const USERNAME_CHARS = '23456789abcdefghjkmnpqrstuvwxyz';

    /**
     * Gera uma senha aleatória legível.
     */
    public function generateRandomPassword(int $length = 8): string
    {
        $charsLength = strlen(self::PASSWORD_CHARS);
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= self::PASSWORD_CHARS[random_int(0, $charsLength - 1)];
        }

        return $password;
    }

    /**
     * Gera um sufixo alfanumérico aleatório para o login.
     */
    public function generateRandomSuffix(int $length = 4): string
    {
        $charsLength = strlen(self::USERNAME_CHARS);
        $suffix = '';
        for ($i = 0; $i < $length; $i++) {
            $suffix .= self::USERNAME_CHARS[random_int(0, $charsLength - 1)];
        }

        return $suffix;
    }

    /**
     * Gera um username único com prefixo e sufixo aleatório.
     */
    public function generateUniqueUsername(string $prefix = 'team'): string
    {
        $cleanPrefix = trim($prefix);
        if ($cleanPrefix === '') {
            $cleanPrefix = 'team';
        }

        do {
            $candidate = $cleanPrefix . '-' . $this->generateRandomSuffix(4);
        } while (User::query()->whereRaw('LOWER(TRIM(username)) = ?', [mb_strtolower($candidate)])->exists());

        return $candidate;
    }

    /**
     * Cria N equipes em lote para um contest e site.
     *
     * @return array{
     *     created: list<array{user: User, username: string, password_plain: string, fullname: string, contest_id: int, site_id: int}>,
     *     csv: string
     * }
     */
    public function generate(
        int $contestId,
        int $siteId,
        int $count,
        string $prefix = 'team',
        ?int $managedBy = null,
        bool $enabled = false
    ): array {
        $created = [];

        DB::transaction(function () use ($contestId, $siteId, $count, $prefix, $managedBy, $enabled, &$created) {
            for ($i = 1; $i <= $count; $i++) {
                $username = $this->generateUniqueUsername($prefix);
                $passwordPlain = $this->generateRandomPassword(8);
                $fullname = 'Equipe ' . strtoupper(explode('-', $username)[1] ?? (string) $i);

                $user = User::create([
                    'fullname' => $fullname,
                    'username' => $username,
                    'email' => null,
                    'password' => Hash::make($passwordPlain),
                    'user_type' => User::TYPE_TEAM,
                    'contest_id' => $contestId,
                    'site_id' => $siteId,
                    'is_enabled' => $enabled,
                    'managed_by' => $managedBy,
                    'managed_at' => now(),
                    'profile_visibility' => 'private',
                ]);

                $created[] = [
                    'user' => $user,
                    'username' => $username,
                    'password_plain' => $passwordPlain,
                    'fullname' => $fullname,
                    'contest_id' => $contestId,
                    'site_id' => $siteId,
                ];
            }
        });

        $csv = $this->buildCsv($created);

        return [
            'created' => $created,
            'csv' => $csv,
        ];
    }

    /**
     * Constrói o conteúdo CSV a partir dos dados gerados.
     *
     * @param list<array{user: User, username: string, password_plain: string, fullname: string, contest_id: int, site_id: int}> $items
     */
    public function buildCsv(array $items): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['equipe', 'usuario', 'senha', 'site_id', 'contest_id']);

        foreach ($items as $item) {
            fputcsv($handle, [
                $item['fullname'],
                $item['username'],
                $item['password_plain'],
                $item['site_id'],
                $item['contest_id'],
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv !== false ? $csv : '';
    }

    /**
     * Habilita ou desabilita em lote todas as equipes de um determinado contest.
     */
    public function toggleContestTeamsAccess(int $contestId, bool $enable, ?int $siteId = null): int
    {
        $query = User::query()
            ->where('contest_id', $contestId)
            ->where('user_type', User::TYPE_TEAM);

        if ($siteId !== null) {
            $query->where('site_id', $siteId);
        }

        return $query->update([
            'is_enabled' => $enable,
            'updated_at' => now(),
        ]);
    }
}
