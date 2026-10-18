<?php

use App\Http\Controllers\Backend\ConfigurationController;
use App\Http\Controllers\Backend\ContestLogController;
use App\Http\Controllers\Backend\ContestWizardController;
use App\Http\Controllers\Backend\ExamScheduleController;
use App\Http\Controllers\Backend\ExamTemplateController;
use App\Http\Controllers\Backend\SchoolController;
use App\Http\Controllers\Backend\LanguageController;
use App\Http\Controllers\Backend\ProblemBankController;
use App\Http\Controllers\Backend\ProblemManagementController;
use App\Http\Controllers\Backend\ProblemPackageImportController;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\ClarificationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JudgeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PrintRequestController;
use App\Http\Controllers\ProblemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\RejudgingScreenController;
use App\Http\Controllers\ScoreboardController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SosController;
use App\Http\Controllers\SosQueueController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\SubmitController;
use App\Models\Contest;
use App\Models\ContestLog;
use App\Models\ProblemBank;
use App\Services\BocaImporterService;
use App\Services\ContestLifecycle;
use Helium\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Home with statistics
Route::get('/', [HomeController::class, 'index']);

Route::get('/home', [HomeController::class, 'index'])->middleware('auth')->name('home');

// Profile (own account, any authenticated role)
Route::get('/profile', [ProfileController::class, 'edit'])->middleware('auth')->name('profile.edit');
Route::put('/profile', [ProfileController::class, 'update'])->middleware('auth')->name('profile.update');
// Issue #395 -- o titular baixa os proprios dados (LGPD art. 18).
Route::get('/profile/export', [ProfileController::class, 'export'])->middleware('auth')->name('profile.export');

// Problems (real BOCA-schema Problem model, scoped to the user's contest)
Route::get('/exercises', [ProblemController::class, 'index'])->name('exercises');

Route::get('/exercise/{problem}', [ProblemController::class, 'show'])->name('exercise.show');

// Issue #137: the statement file a BOCA package brings in description/ --
// imported, stored, and until now never linked from the team's screen, so a
// problem imported the normal way showed the team "o enunciado ainda nao foi
// disponibilizado". No auth middleware on purpose: the rule is the contest
// visibility rule (#135) plus the contest clock, both applied in the
// controller, so a public contest's archived statements stay readable.
Route::get('/exercise/{problem}/enunciado', [ProblemController::class, 'statement'])->name('exercise.statement');

// Scoreboard
Route::get('/scoreboard', [ScoreboardController::class, 'index'])->name('scoreboard');

// Scoreboard Export (CSV)
Route::get('/scoreboard/export', [ScoreboardController::class, 'export'])->name('scoreboard.export');

// Clarifications
// Issue #135: a clarification answer is problem information -- "n can be
// zero", "the input is sorted" -- written by the jury for the teams in the
// contest. The POST already required auth; the GET did not, so anyone on the
// internet could read the jury's answers live without an account. Asking
// required a login, reading did not.
Route::get('/clarifications', [ClarificationController::class, 'index'])
    ->middleware('auth')->name('clarifications');

Route::post('/clarifications', [ClarificationController::class, 'store'])->middleware('auth');

// Submissions
Route::get('/submissions', [SubmissionController::class, 'index'])->middleware('auth')->name('submissions');

Route::get('/submission/{run}', [SubmissionController::class, 'show'])->middleware('auth')->name('submission.show');
// Issue #268 -- baixar o fonte. E o unico jeito util quando ele e binario:
// um `.sb3` e um ZIP, e renderiza-lo num `<pre>` despeja centenas de KB de
// U+FFFD no HTML.
Route::get('/submission/{run}/source', [SubmissionController::class, 'source'])->middleware('auth')->name('submission.source');

// Submit solution -- creates a real Run and dispatches it to the auto-judge
// queue (see App\Http\Controllers\SubmitController). Previously this was a
// stub that wrote a 'pending' row to the legacy exercise_team table and
// never actually judged anything.
Route::get('/submit/{problem}', [SubmitController::class, 'create'])->name('exercise.submit');

Route::post('/submit/{problem}', [SubmitController::class, 'store']);

// Help page
Route::get('/ajuda', function () {
    return view('more-info');
})->name('ajuda');

Route::get('/wizard', function () {
    return view('wizard');
})->name('wizard');

// Issue #397 -- o seletor de idioma. Sem `auth`: a tela de entrada tambem
// oferece o seletor, e quem ainda nao entrou e quem mais precisa ler.
Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

// Health check
Route::get('/up', function () {
    return response('OK', 200);
});

// Auth routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function () {
    $loginInput = trim((string) request()->input('email', request()->input('login')));
    $password = (string) request()->input('password');

    $clientIp = request()->ip();

    $candidate = User::where('email', $loginInput)->first()
        ?? User::whereRaw('LOWER(TRIM(username)) = ?', [mb_strtolower($loginInput)])->first();

    $remember = request()->filled('remember');
    if ($remember && $candidate?->site?->ip_address) {
        $remember = false;
    }

    if (! $candidate || ! Hash::check($password, $candidate->password)) {
        return back()->withErrors([
            'email' => __('Credenciais invalidas.'),
        ])->withInput(request()->only('email', 'login'));
    }

    // Se a conta estiver desabilitada, checar se pertence a um contest cujo horário já iniciou
    if (! $candidate->is_enabled) {
        $contest = $candidate->contest;
        $autoAllowed = $contest && $contest->start_time && now()->gte($contest->start_time);

        if ($autoAllowed) {
            $candidate->forceFill(['is_enabled' => true])->save();
        } else {
            $logContestId = $candidate->contest_id ?? $candidate->site?->contest_id;

            if ($logContestId !== null) {
                ContestLog::warning(
                    (int) $logContestId,
                    'Login bloqueado: conta desabilitada',
                    ['user_id' => $candidate->user_id]
                );
            }

            $errorMessage = ($contest && $contest->start_time && now()->lt($contest->start_time))
                ? __('Acesso bloqueado: sua maratona iniciará em :time. Aguarde o início ou a liberação da organização.', ['time' => \Carbon\Carbon::parse($contest->start_time)->format('H:i \d\e d/m/Y')])
                : __('Esta conta esta desabilitada. Procure a organizacao do evento.');

            return back()->withErrors([
                'email' => $errorMessage,
            ])->withInput(request()->only('email', 'login'));
        }
    }

    // Issue #50: Site::ip_address
    if ($candidate->site && ! $candidate->site->isIpAllowed($clientIp)) {
        ContestLog::warning(
            $candidate->contest_id ?? $candidate->site->contest_id,
            "Login bloqueado: IP fora da rede configurada para o site \"{$candidate->site->name}\"",
            ['user_id' => $candidate->user_id, 'site_id' => $candidate->site_id, 'ip' => $clientIp]
        );

        return back()->withErrors([
            'email' => __('Acesso bloqueado: fora da rede autorizada para o seu site.'),
        ])->withInput(request()->only('email', 'login'));
    }

    auth()->login($candidate, $remember);
    request()->session()->regenerate();

    return redirect()->intended('/home');
})->middleware('throttle:5,1');

// Issue #275 -- o auto-cadastro deixou de entregar sessao.
//
// Era um par de closures que criava a conta com `DB::table()`, gravava
// `is_enabled => true` e chamava `auth()->loginUsingId()` -- sem throttle,
// enquanto o `/login` logo acima tem `throttle:5,1`. Uma rota publica sem
// limite de tentativas que cria conta habilitada e ja loga e um caminho para
// obter sessao autenticada sem que ninguem da organizacao tenha decidido
// isso, e o SRS nao descrevia a rota em requisito nenhum.
//
// O throttle e o MESMO do `/login`, de proposito: as duas portas para a
// mesma tabela de usuarios nao podem ter limites diferentes, senao a mais
// frouxa e a que vale.
Route::get('/register', [RegistrationController::class, 'create'])->name('register');
Route::post('/register', [RegistrationController::class, 'store'])
    ->middleware('throttle:register');

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
})->name('logout');

Route::get('/password/reset', function () {
    return view('auth.passwords.email');
})->name('password.request');

Route::post('/password/email', function () {
    // This is a placeholder to make the route exist.
    // A full implementation would send a password reset link.
    return back()->with('status', 'Password reset link sent!');
})->name('password.email');

/*
|--------------------------------------------------------------------------
| Judge Routes
|--------------------------------------------------------------------------
*/

Route::get('/judge/runs', [JudgeController::class, 'index'])->name('judge.runs');
Route::post('/judge/runs/{run}', [JudgeController::class, 'judge'])->name('judge.runs.judge');
// Issue #138 -- releasing a verdict to the teams, and taking it back. Gated
// by JudgeController's own constructor (['auth', 'role:judge,admin']), like
// every other action on this controller; these routes carry no middleware of
// their own, matching the two above.
Route::post('/judge/runs/{run}/verify', [JudgeController::class, 'verify'])->name('judge.runs.verify');
Route::post('/judge/runs/{run}/unverify', [JudgeController::class, 'unverify'])->name('judge.runs.unverify');
// Issue #193 -- segurar o julgamento de um problema com defeito, sem tirar
// o enunciado das equipes. Mesmo portao dos demais: o construtor do
// JudgeController exige ['auth', 'role:judge,admin'].
Route::post('/judge/problems/{problem}/pause', [JudgeController::class, 'pauseJudging'])->name('judge.problems.pause');
Route::post('/judge/problems/{problem}/resume', [JudgeController::class, 'resumeJudging'])->name('judge.problems.resume');

// Issue #192/#245 -- rejulgamento EM LOTE pela tela. O #192 entregou o
// servico inteiro e nenhuma porta: ate aqui, corrigir um caso de teste
// errado no meio da prova era montar a chamada a API a mao. Mesmo portao das
// rotas acima -- o construtor do RejudgingScreenController exige
// ['auth', 'role:judge,admin'], como a API faz pela mesma razao.
Route::get('/judge/rejudgings', [RejudgingScreenController::class, 'index'])->name('judge.rejudgings');
Route::post('/judge/rejudgings/{contest}/preview', [RejudgingScreenController::class, 'preview'])->name('judge.rejudgings.preview');
Route::post('/judge/rejudgings/{contest}', [RejudgingScreenController::class, 'store'])->name('judge.rejudgings.store');
Route::get('/judge/rejudgings/set/{rejudging}', [RejudgingScreenController::class, 'show'])->name('judge.rejudgings.show');
Route::post('/judge/rejudgings/set/{rejudging}/apply', [RejudgingScreenController::class, 'apply'])->name('judge.rejudgings.apply');
Route::post('/judge/rejudgings/set/{rejudging}/cancel', [RejudgingScreenController::class, 'cancel'])->name('judge.rejudgings.cancel');

/*
|--------------------------------------------------------------------------
| Staff Routes
|--------------------------------------------------------------------------
*/

Route::get('/staff/tasks', [StaffController::class, 'tasks'])->name('staff.tasks');
Route::post('/staff/tasks/{task}/complete', [StaffController::class, 'complete'])->name('staff.tasks.complete');
Route::get('/staff/tasks/{task}/file', [StaffController::class, 'downloadFile'])
    ->middleware(['auth', 'role:staff,admin'])
    ->name('staff.tasks.file');

// Issue #139 -- the S.O.S. queue: this site's teams asking for a person,
// not for a verdict. Separate from the task queue above because it is a
// different queue, with a state a staff member moves rather than a single
// "done". Site coordinators are included: at a real site they are the local
// organisation too. Judges are not -- an S.O.S. never goes to the jury.
Route::get('/staff/sos', [SosQueueController::class, 'index'])->name('staff.sos');
Route::post('/staff/sos/{sosCall}/acknowledge', [SosQueueController::class, 'acknowledge'])
    ->name('staff.sos.acknowledge');
Route::post('/staff/sos/{sosCall}/resolve', [SosQueueController::class, 'resolve'])
    ->name('staff.sos.resolve');

/*
|--------------------------------------------------------------------------
| Site Coordinator Routes (issue #18 -- multi-site coordination)
|--------------------------------------------------------------------------
*/

Route::get('/site/dashboard', [SiteController::class, 'dashboard'])->name('site.dashboard');
Route::get('/site/tasks', [SiteController::class, 'tasks'])->name('site.tasks');
Route::post('/site/tasks/{task}/complete', [SiteController::class, 'completeTask'])->name('site.tasks.complete');
Route::get('/site/teams', [SiteController::class, 'teams'])->name('site.teams');
Route::post('/site/teams', [SiteController::class, 'storeTeam'])->name('site.teams.store');
Route::get('/site/clarifications', [SiteController::class, 'clarifications'])->name('site.clarifications');
Route::post('/site/clarifications/{clarification}/answer', [SiteController::class, 'answerClarification'])->name('site.clarifications.answer');

/*
|--------------------------------------------------------------------------
| Backend Routes (Admin)
|--------------------------------------------------------------------------
*/

// Issue #94 -- a team sends a file to the staff print queue. Team-only and
// rate limited: a five-hour contest with two hundred teams is a queue
// anyone could flood.
Route::get('/print', [PrintRequestController::class, 'create'])->name('print.create');
Route::post('/print', [PrintRequestController::class, 'store'])
    ->middleware('throttle:10,60')
    ->name('print.store');

// Issue #139 -- BOCA's S.O.S. button (src/team/task.php). Team-only, and
// throttled only against raw hammering: what actually bounds the staff queue
// is that a team may hold one unresolved call at a time (see the unique
// index in the sos_calls migration), not a cooldown. A cooldown would mean
// telling a team with a dead machine to wait, which is the one answer this
// button must never give.
Route::get('/sos', [SosController::class, 'create'])->name('sos.create');
Route::post('/sos', [SosController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('sos.store');

// Issue #88 -- the contest audit log. Outside the admin-only backend group
// on purpose: a judge or staff member auditing a disputed verdict needs it
// too, scoped to their own contest and without the IP column.
Route::get('/backend/logs', [ContestLogController::class, 'index'])
    ->middleware(['auth', 'role:admin,judge,staff'])
    ->name('backend.logs');

Route::prefix('backend')->middleware(['auth', 'admin'])->group(function () {

    // Problem Management (real Problem/TestCase rows -- see issue #34;
    // replaces the old "Exercises Management" that targeted the legacy,
    // disconnected `exercises` table)
    // Issue #200/#233 -- importar um pacote da ICPC/Kattis PARA UMA
    // COMPETICAO. Separado de `backend.import-boca`, que traz um contest
    // inteiro do BOCA para o BANCO: contrato e destino diferentes.
    Route::get('/import-package', [ProblemPackageImportController::class, 'show'])->name('backend.import-package');
    Route::post('/import-package', [ProblemPackageImportController::class, 'store'])->name('backend.import-package.store');

    Route::get('/exercises', [ProblemManagementController::class, 'index'])->name('backend.exercises');
    Route::post('/exercises', [ProblemManagementController::class, 'store']);

    // Issue #401: Polygon - Autoria e Calibracao de Problemas
    Route::get('/polygon', function () {
        return view('backend.polygon.index');
    })->name('backend.polygon');

    Route::get('/polygon/{exerciseId}', function ($exerciseId) {
        return view('backend.polygon.workspace', compact('exerciseId'));
    })->whereNumber('exerciseId')->name('backend.polygon.workspace');

    // Users Management
    Route::get('/users', [UserController::class, 'index'])->name('backend.users');
    Route::post('/users', [UserController::class, 'store']);
    // Issue #100 -- editing an account was not possible at all; the only
    // way to fix a typo was to delete it, and deleting cascades to its
    // runs, scores, tasks and logs.
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('backend.users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('backend.users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('backend.users.destroy');
    // Issue #183 -- um link de uso unico para a pessoa escolher a propria
    // senha, em vez de alguem digitar uma por ela e passar a saber qual e.
    Route::post('/users/{user}/reset-link', [UserController::class, 'resetLink'])->name('backend.users.reset-link');
    Route::post('/users/generate-sbc', [UserController::class, 'generateSbcBatch'])->name('backend.users.generate-sbc');
    Route::post('/users/toggle-contest-access', [UserController::class, 'toggleContestAccess'])->name('backend.users.toggle-contest-access');

    // Site Management (multi-site coordination -- see issue #18)
    Route::get('/sites', [App\Http\Controllers\Backend\SiteController::class, 'index'])->name('backend.sites');
    Route::post('/sites', [App\Http\Controllers\Backend\SiteController::class, 'store'])->name('backend.sites.store');
    Route::put('/sites/{site}', [App\Http\Controllers\Backend\SiteController::class, 'update'])->name('backend.sites.update');
    Route::delete('/sites/{site}', [App\Http\Controllers\Backend\SiteController::class, 'destroy'])->name('backend.sites.destroy');

    // Language Management (issue #140) -- BOCA's src/admin/language.php.
    // compile_command/run_command are the shell command lines the judge
    // executes, so this screen stays inside this admin-only group: it is
    // command entry by an administrator, audited in ContestLog (#88) and
    // contained by the sandbox (#49) at execution time.
    Route::get('/languages', [LanguageController::class, 'index'])->name('backend.languages');
    Route::post('/languages', [LanguageController::class, 'store'])->name('backend.languages.store');
    Route::put('/languages/{language}', [LanguageController::class, 'update'])->name('backend.languages.update');
    Route::delete('/languages/{language}', [LanguageController::class, 'destroy'])->name('backend.languages.destroy');

    // Gestao de competicoes (#190: tudo em `contests`)
    Route::get('/configurations', [ConfigurationController::class, 'index'])->name('backend.configurations');
    Route::post('/configurations', [ConfigurationController::class, 'store']);

    // Contest Wizard
    Route::get('/contest-wizard', [ContestWizardController::class, 'index'])->name('backend.contest-wizard');
    Route::post('/contest-wizard', [ContestWizardController::class, 'store']);

    // Templates de prova N1/N2/N3 (fixos: sem create/destroy), escolas e
    // agendamento de provas a partir dos templates.
    Route::get('/exam-templates', [ExamTemplateController::class, 'index'])->name('backend.exam-templates');
    Route::get('/exam-templates/{template}/edit', [ExamTemplateController::class, 'edit'])->name('backend.exam-templates.edit');
    Route::put('/exam-templates/{template}', [ExamTemplateController::class, 'update'])->name('backend.exam-templates.update');

    Route::get('/exams', [ExamScheduleController::class, 'index'])->name('backend.exams');
    Route::get('/exams/create', [ExamScheduleController::class, 'create'])->name('backend.exams.create');
    Route::post('/exams', [ExamScheduleController::class, 'store'])->name('backend.exams.store');

    Route::get('/schools', [SchoolController::class, 'index'])->name('backend.schools');
    Route::post('/schools', [SchoolController::class, 'store'])->name('backend.schools.store');
    Route::put('/schools/{school}', [SchoolController::class, 'update'])->name('backend.schools.update');
    Route::delete('/schools/{school}', [SchoolController::class, 'destroy'])->name('backend.schools.destroy');
    Route::post('/schools/{school}/teams', [SchoolController::class, 'storeTeam'])->name('backend.schools.teams.store');
    Route::put('/school-teams/{team}', [SchoolController::class, 'updateTeam'])->name('backend.schools.teams.update');
    Route::delete('/school-teams/{team}', [SchoolController::class, 'destroyTeam'])->name('backend.schools.teams.destroy');
    Route::post('/school-teams/{team}/members', [SchoolController::class, 'storeMember'])->name('backend.schools.members.store');
    Route::delete('/school-team-members/{member}', [SchoolController::class, 'destroyMember'])->name('backend.schools.members.destroy');

    // Clarifications Management (Admin)
    Route::get('/clarifications', function () {
        $clarifications = DB::table('clarifications')
            ->leftJoin('users', 'clarifications.user_id', '=', 'users.user_id')
            ->select('clarifications.*', 'users.fullname as team_name')
            ->orderBy('clarifications.status', 'asc')
            ->orderBy('clarifications.created_at', 'desc')
            ->get()
            ->map(function ($item) {
                $item->answered = $item->status === 'answered';
                $item->problem = $item->problem_id ? 'Problema #'.$item->problem_id : null;

                return $item;
            });

        return view('backend.clarifications', compact('clarifications'));
    })->name('backend.clarifications');

    Route::post('/clarifications/{id}/answer', function ($id) {
        DB::table('clarifications')->where('id', $id)->update([
            'answer' => request('answer'),
            'status' => 'answered',
            'answered_time' => 0,
            'updated_at' => now(),
        ]);

        return redirect()->route('backend.clarifications')->with('success', 'Resposta enviada!');
    });

    // Submissions Management (Admin)
    Route::get('/submissions', function () {
        $submissions = collect([]);

        if (Schema::hasTable('runs')) {
            $submissions = DB::table('runs')
                ->leftJoin('users', 'runs.user_id', '=', 'users.user_id')
                ->leftJoin('problems', 'runs.problem_id', '=', 'problems.id')
                ->leftJoin('answers', 'runs.answer_id', '=', 'answers.id')
                ->leftJoin('languages', 'runs.language_id', '=', 'languages.id')
                ->select('runs.*', 'users.fullname as team_name', 'problems.name as problem_name',
                    'answers.short_name as result', 'languages.name as language')
                ->orderBy('runs.created_at', 'desc')
                ->get();
        }

        return view('backend.submissions', compact('submissions'));
    })->name('backend.submissions');

    // Contest Management Actions
    Route::post('/contest/{id}/activate', function ($id) {
        // Issue #43: global activation must never be able to turn the
        // technical practice contest into the running event.
        if (DB::table('contests')->where('id', $id)->value('is_practice')) {
            return redirect()->route('backend.configurations')
                ->with('error', 'O contest tecnico do Treino Livre nao pode ser ativado como competicao.');
        }

        // Deactivate all contests first
        DB::table('contests')->update(['is_active' => false]);
        // Activate the selected one
        DB::table('contests')->where('id', $id)->update(['is_active' => true]);

        return redirect()->route('backend.configurations')->with('success', 'Maratona ativada com sucesso!');
    });

    // Issue #190 -- procura em `contests`. Procurava em `hackathons` por
    // `hackathon_id`, entao competicao criada por qualquer outro caminho
    // respondia "Maratona nao encontrada" mesmo existindo.
    Route::get('/contest/{id}/edit', function ($id) {
        $contest = DB::table('contests')->where('id', $id)->first();

        if (! $contest) {
            return redirect()->route('backend.configurations')->with('error', 'Maratona nao encontrada');
        }

        return view('backend.contest-edit', compact('contest'));
    })->name('backend.contest.edit');

    Route::put('/contest/{id}/update', function ($id, Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'duration' => 'required|integer|min:30|max:10080',
            'penalty' => 'integer|min:0|max:120',
            'freeze_time' => 'integer|min:0',
            'max_file_size' => 'integer|min:1|max:10240',
            // Issue #271 -- identidade do evento para um agregado nacional.
            // Uma regional e uma final nacional nao sao o mesmo tipo de
            // evento no ranking, e o nome da prova sozinho nao carrega essa
            // distincao de forma legivel por maquina.
            'edition' => 'nullable|string|max:50',
            'phase' => 'nullable|string|max:50',
            'unlock_key' => 'nullable|string|max:100',
        ]);

        $startTime = new DateTime($validated['start_time']);
        $endTime = clone $startTime;
        $endTime->modify('+'.$validated['duration'].' minutes');

        // Issue #190 -- unicidade contra `contests`. A checagem antiga
        // olhava `hackathons`, que nao tem linha para competicao criada
        // pela API ou pelo importador: dois contests podiam ficar com o
        // mesmo nome sem ninguem ser avisado.
        $existing = DB::table('contests')
            ->where('name', $validated['name'])
            ->where('id', '!=', $id)
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Ja existe uma maratona com este nome');
        }

        // Update contest
        DB::table('contests')->where('id', $id)->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'start_time' => $startTime->format('Y-m-d H:i:s'),
            'duration' => $validated['duration'],
            'freeze_time' => $validated['freeze_time'] ?? 60,
            'penalty' => $validated['penalty'] ?? 20,
            'max_file_size' => $validated['max_file_size'] ?? 100,
            // Campo em branco vira NULL, e nao string vazia: "" no
            // manifesto do pacote de resultados seria pior que ausente,
            // porque o consumidor teria que tratar dois jeitos de dizer "nao
            // informado".
            //
            // Quem faz isso e o `ConvertEmptyStringsToNull` do grupo `web`,
            // que roda ANTES da validacao -- medido, nao suposto. Escrevi um
            // `?: null` aqui antes de medir, e uma mutacao mostrou que ele
            // nao guardava nada. O `??` cobre o caso real que sobra: a chave
            // ausente, quando o corpo nao traz o campo.
            'edition' => $validated['edition'] ?? null,
            'phase' => $validated['phase'] ?? null,
            'is_active' => $request->has('is_active'),
            'is_public' => $request->has('is_public'),
            // `unlock_key` e nullable na validacao, e regra nullable que
            // nao vem no corpo simplesmente NAO APARECE em $validated --
            // ler a chave direto era "Undefined array key" numa requisicao
            // sem o campo. O formulario sempre manda, entao isso so
            // aparecia fora do navegador; um teste foi o primeiro a mandar
            // sem.
            'unlock_key' => $validated['unlock_key'] ?? null,
            'updated_at' => now(),
        ]);

        return redirect()->route('backend.configurations')->with('success', 'Maratona atualizada com sucesso!');
    })->name('backend.contest.update');

    Route::delete('/contest/{id}/delete', function ($id) {
        // Issue #190 -- nada de `hackathons` aqui: apagar a linha legada
        // dava a impressao de ter apagado a competicao mesmo quando ela
        // nunca teve uma.
        DB::table('contests')->where('id', $id)->delete();
        DB::table('languages')->where('contest_id', $id)->delete();
        DB::table('answers')->where('contest_id', $id)->delete();
        DB::table('sites')->where('contest_id', $id)->delete();

        return redirect()->route('backend.configurations')->with('success', 'Maratona excluida com sucesso!');
    });

    // Issue #225 -- os dois atalhos faziam o OPOSTO do que prometiam.
    //
    // Escreviam direto na tabela com DB::table, sem passar pelo modelo, e
    // por isso ninguem tinha notado: `freeze_time = 0` significa "sem
    // congelamento" desde o #189, entao o botao "Placar congelado!"
    // descongelava; e `is_active = false` fazia isFrozen() devolver falso,
    // entao o botao de encerrar publicava a classificacao.
    //
    // Agora chamam o mesmo servico que a API chama. A competicao vem pelo
    // scope competition(), e nao pelo primeiro is_active da tabela: o
    // contest de treino do #43 nunca e "o evento".
    Route::post('/contest/freeze', function (Request $request, ContestLifecycle $lifecycle) {
        $contest = Contest::query()->competition()->where('is_active', true)->first();

        if (! $contest) {
            return redirect()->route('backend.configurations')->with('error', 'Nenhuma maratona ativa');
        }

        $lifecycle->freezeNow($contest, $request->user());

        return redirect()->route('backend.configurations')->with('success', 'Placar congelado a partir de agora.');
    });

    Route::post('/contest/end', function (Request $request, ContestLifecycle $lifecycle) {
        $contest = Contest::query()->competition()->where('is_active', true)->first();

        if (! $contest) {
            return redirect()->route('backend.configurations')->with('error', 'Nenhuma maratona ativa');
        }

        $lifecycle->endEarly($contest, $request->user());

        return redirect()->route('backend.configurations')
            ->with('success', 'Competicao encerrada. O placar continua congelado ate alguem revelar.');
    });

    // Problem Bank Management
    Route::get('/problem-bank', [ProblemBankController::class, 'index'])->name('backend.problem-bank');
    Route::delete('/problem-bank/{id}', [ProblemBankController::class, 'destroy'])->name('backend.problem-bank.destroy');
    Route::post('/problem-bank/{id}/toggle', [ProblemBankController::class, 'toggle'])->name('backend.problem-bank.toggle');

    // BOCA Import Routes
    Route::get('/import-boca', function () {
        return view('backend.import-boca');
    })->name('backend.import-boca');

    Route::post('/import-boca/upload', function () {
        // Issue #46 (docs/specs/46-bank-ownership.md) calls out this route
        // by name: it creates new problem_bank rows, so it must reapply the
        // same ownership policy as the new bank-governance screen. This
        // route also sits behind the `admin` middleware today, so the
        // check below is currently unreachable for non-admins -- it's
        // defense-in-depth so import stays admin-only even if this route's
        // gate is ever loosened.
        abort_unless(auth()->user()->can('create', ProblemBank::class), 403);

        $file = request()->file('boca_zip');

        if (! $file || ! $file->isValid()) {
            return redirect()->route('backend.import-boca')->with('error', 'Arquivo invalido');
        }

        // Store temporarily
        $path = $file->store('temp/boca');
        $fullPath = storage_path('app/'.$path);

        // Import
        $importer = new BocaImporterService;
        $result = $importer->importFromZip($fullPath);

        // Cleanup
        @unlink($fullPath);

        if ($result['success']) {
            return redirect()->route('backend.problem-bank')
                ->with('success', 'Importados '.$result['count'].' problema(s) do BOCA!');
        }

        return redirect()->route('backend.import-boca')
            ->with('error', implode(', ', $result['errors']));
    });

    Route::post('/import-boca/github', function () {
        // Issue #46: same reasoning as /import-boca/upload above (also
        // currently unreachable for non-admins, kept as defense-in-depth).
        abort_unless(auth()->user()->can('create', ProblemBank::class), 403);

        $importer = new BocaImporterService;
        $result = $importer->importFromBocaGitHub();

        if ($result['success'] || $result['count'] > 0) {
            $message = 'Importados '.$result['count'].' problema(s) do BOCA GitHub!';
            if (! empty($result['errors'])) {
                $message .= ' (Alguns erros: '.implode(', ', array_slice($result['errors'], 0, 3)).')';
            }

            return redirect()->route('backend.problem-bank')->with('success', $message);
        }

        return redirect()->route('backend.import-boca')
            ->with('error', implode(', ', $result['errors']));
    });
});

require __DIR__.'/frontend.php';
require __DIR__.'/frontend_api_webcast.php';
require __DIR__.'/frontend_api_similarity.php';
require __DIR__.'/frontend_api_bank_governance.php';
// Issue #188 -- a gestao de organizacoes, que e a fase que o #46 deixou
// para depois. Sem ela o seletor de proprietario do #46 abre vazio em
// qualquer instalacao real.
require __DIR__.'/frontend_api_organizations.php';
require __DIR__.'/frontend_api_managed_accounts.php';
require __DIR__.'/frontend_api_practice.php';
require __DIR__.'/frontend_api_icpc.php';
require __DIR__.'/frontend_api_reports.php';
require __DIR__.'/frontend_api_judgehosts.php';
require __DIR__.'/frontend_api_judging_health.php';
require __DIR__.'/backend_api_polygon.php';
