<?php

namespace App\Services;

use App\Http\Controllers\Backend\ContestWizardController;
use App\Models\Contest;
use App\Models\ContestLog;
use App\Models\ExamParticipation;
use App\Models\ExamTemplate;
use App\Models\SchoolTeam;
use Carbon\CarbonInterface;
use Helium\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Agenda uma prova a partir de um template: cria um Contest real, copia as
 * questões do template e cria as contas de acesso seguras para os times elegíveis.
 *
 * Cada time recebe:
 *  - Nome de exibição (fullname): Nome original do time (ex: "Bugados")
 *  - Líder da equipe: Participante 1 cadastrado
 *  - Login de acesso aleatório (username): Formato imprevisível team-<abc>
 *  - Senha aleatória direta: 8 caracteres alfanuméricos legíveis já habilitada
 */
class ExamScheduler
{
    public function __construct(
        private readonly ManagedAccountProvisioner $provisioner,
        private readonly TeamBatchProvisioner $teamBatchProvisioner
    ) {
    }

    /**
     * Teams that would enter an exam of $template for these schools.
     *
     * @param  array<int, int>  $schoolIds
     * @return \Illuminate\Support\Collection<int, SchoolTeam>
     */
    public function eligibleTeams(ExamTemplate $template, array $schoolIds, int $phase = 1)
    {
        return SchoolTeam::query()
            ->with(['school', 'members'])
            ->whereIn('school_id', $schoolIds)
            ->where('level', $template->level)
            ->whereDoesntHave('participations', fn ($q) => $q->where('phase', $phase))
            ->orderBy('school_id')
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array<int, int>  $schoolIds
     * @return array{contest: Contest, credentials: array<int, array{team: string, school: string, leader: string, username: string, password: string, members: string}>}
     *
     * @throws \InvalidArgumentException when the template has no problems or no team is eligible
     */
    public function schedule(ExamTemplate $template, CarbonInterface $start, array $schoolIds, ?int $adminId, int $phase = 1, ?string $name = null): array
    {
        $bankIds = $template->problems()->pluck('problem_bank.id')->all();
        if (! $bankIds) {
            throw new \InvalidArgumentException("O template {$template->code} não tem questões.");
        }

        return DB::transaction(function () use ($template, $start, $schoolIds, $adminId, $phase, $name, $bankIds) {
            $teams = $this->eligibleTeams($template, $schoolIds, $phase);
            if ($teams->isEmpty()) {
                throw new \InvalidArgumentException("Nenhum time {$template->code} elegível nas escolas selecionadas.");
            }

            $contest = Contest::create([
                'name' => $this->uniqueName($name ?: $template->name . ' — ' . $start->format('d/m/Y H:i')),
                'description' => null,
                'start_time' => $start,
                'duration' => $template->duration,
                'freeze_time' => $template->freeze_minutes,
                'penalty' => $template->penalty,
                'max_file_size' => 100,
                'is_active' => true,
                'is_public' => false,
                'exam_template_id' => $template->id,
            ]);

            $activeLanguages = \App\Models\Language::DEFAULT_CONTEST_LANGUAGES;
            ContestWizardController::provisionContestScaffolding($contest->id, $activeLanguages);
            ContestWizardController::addProblemsFromBank($contest->id, $bankIds, true);

            $siteId = (int) DB::table('sites')->where('contest_id', $contest->id)->value('id');

            $credentials = [];
            foreach ($teams as $team) {
                $username = $this->generateRandomTeamUsername('team');
                $plainPassword = $this->teamBatchProvisioner->generateRandomPassword(8);

                $leader = $team->members->first();
                $leaderName = $leader ? $leader->name : 'Não informado';
                $otherMembers = $team->members->slice(1)->pluck('name')->implode(', ');
                $teamDescription = $team->school->name . " | Líder: {$leaderName}" . (filled($otherMembers) ? " | Integrantes: {$otherMembers}" : '');

                $user = User::create([
                    'fullname' => $team->name,
                    'username' => $username,
                    'password' => Hash::make($plainPassword),
                    'user_type' => User::TYPE_TEAM,
                    'contest_id' => $contest->id,
                    'site_id' => $siteId,
                    'description' => $teamDescription,
                    'is_enabled' => true,
                    'profile_visibility' => 'private',
                    'managed_by' => $adminId,
                    'managed_at' => now(),
                ]);

                ExamParticipation::create([
                    'school_team_id' => $team->id,
                    'contest_id' => $contest->id,
                    'user_id' => $user->user_id,
                    'phase' => $phase,
                ]);

                $credentials[] = [
                    'team' => $team->name,
                    'school' => $team->school->name,
                    'leader' => $leaderName,
                    'username' => $username,
                    'password' => $plainPassword,
                    'members' => $team->members->pluck('name')->implode(', '),
                ];
            }

            ContestLog::log($contest->id, 'info', "Prova agendada a partir do template {$template->code} com {$teams->count()} times.", $siteId, $adminId);

            return [
                'contest' => $contest,
                'credentials' => $credentials,
                'links' => $credentials, // retrocompatibilidade
            ];
        });
    }

    /**
     * Gera um username aleatório único no formato team-<abc> (ex: team-k7p2).
     */
    public function generateRandomTeamUsername(string $prefix = 'team'): string
    {
        do {
            $suffix = $this->teamBatchProvisioner->generateRandomSuffix(4);
            $username = "{$prefix}-{$suffix}";
        } while (User::where('username', $username)->exists());

        return $username;
    }

    private function uniqueName(string $base): string
    {
        $name = $base;
        for ($i = 2; Contest::withTrashed()->where('name', $name)->exists(); $i++) {
            $name = "{$base} ({$i})";
        }

        return $name;
    }
}
