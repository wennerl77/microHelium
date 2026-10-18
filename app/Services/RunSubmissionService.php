<?php

namespace App\Services;

use App\Jobs\JudgeRunJob;
use App\Models\Contest;
use App\Models\ContestLog;
use App\Models\Language;
use App\Models\Problem;
use App\Models\Run;
use App\Services\Clics\ContestEventRecorder;
use Helium\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

use App\Support\SourceFilename;

/**
 * Creating a Run and handing it to the judge, shared by the competition
 * submit path and issue #43's practice path.
 *
 * docs/specs/43-practice.md asks for exactly this split: "não reutilizar
 * diretamente SubmitController sem adaptar suas regras de horário e escopo
 * [...] reusar avaliação, validação de linguagem/fonte e isolamento do juiz
 * por serviço compartilhado com contexto explícito". So what differs between
 * the two callers -- whether a contest clock applies, whether resubmitting
 * identical source is refused -- is passed in rather than inferred here, and
 * what must not differ -- run numbering, storage layout, dispatch -- lives
 * here once.
 */
class RunSubmissionService
{
    /**
     * @param  bool  $rejectDuplicateSource  Competition submissions refuse an
     *                                       identical resubmission (it is almost always a double-click and it
     *                                       pollutes the scoreboard). Practice allows it: retrying the same code
     *                                       after an infrastructure failure is legitimate there, and #43's
     *                                       accidental-double-submit protection is the Idempotency-Key instead.
     *
     * @throws DuplicateSubmissionException
     */
    public function submit(
        Contest $contest,
        int $siteId,
        User $user,
        Problem $problem,
        Language $language,
        string $filename,
        string $source,
        bool $rejectDuplicateSource = true,
    ): Run {
        $sourceHash = hash('sha256', $source);

        try {
            $run = DB::transaction(function () use (
                $contest, $siteId, $user, $problem, $language,
                $filename, $source, $sourceHash, $rejectDuplicateSource
            ) {
                if ($rejectDuplicateSource) {
                    // Locked for the duration of the transaction so a
                    // double-submit race cannot have both requests pass the
                    // check before either commits.
                    $duplicate = Run::where('contest_id', $contest->id)
                        ->where('user_id', $user->user_id)
                        ->where('problem_id', $problem->id)
                        ->where('source_hash', $sourceHash)
                        ->lockForUpdate()
                        ->first();

                    if ($duplicate) {
                        throw new RuntimeException("DUPLICATE:{$duplicate->run_number}");
                    }
                }

                // Lock the site's existing runs so two concurrent submissions
                // can't compute the same MAX(run_number)+1 and collide on the
                // unique (contest_id, site_id, run_number) constraint.
                Run::where('contest_id', $contest->id)->where('site_id', $siteId)->lockForUpdate()->get();
                $runNumber = Run::getNextRunNumber($contest->id, $siteId);

                $path = "runs/{$contest->id}/{$user->user_id}/".uniqid('run_', true).'_'.$filename;
                Storage::disk('local')->put($path, $source);

                return Run::create([
                    'contest_id' => $contest->id,
                    'site_id' => $siteId,
                    'user_id' => $user->user_id,
                    'problem_id' => $problem->id,
                    'language_id' => $language->id,
                    'run_number' => $runNumber,
                    'filename' => $filename,
                    'source_file' => $path,
                    'source_hash' => $sourceHash,
                    // 0 for practice: the practice contest has no start_time,
                    // so getContestTime() is 0 by construction rather than by
                    // a special case here.
                    'contest_time' => $contest->getContestTime(),
                    'status' => 'pending',
                ]);
            });
        } catch (RuntimeException $e) {
            if (str_starts_with($e->getMessage(), 'DUPLICATE:')) {
                throw new DuplicateSubmissionException(substr($e->getMessage(), strlen('DUPLICATE:')));
            }

            throw $e;
        }

        // Issue #219 -- o event feed.
        //
        // Fora da transacao, de proposito: o evento conta que o envio
        // EXISTE, e um evento gravado dentro de uma transacao que depois
        // desfaz contaria sobre um envio que nao chegou a existir. Aqui a
        // transacao ja fechou.
        app(ContestEventRecorder::class)->submissionCreated($run);

        ContestLog::info($contest->id, "Run #{$run->run_number} submitted", [
            'user_id' => $user->user_id,
            'problem_id' => $problem->id,
            'language_id' => $language->id,
        ]);

        if ($problem->auto_judge) {
            JudgeRunJob::dispatch($run);
        }

        return $run;
    }

    /**
     * The stored filename for a submission. The compile/run commands come
     * from the chosen language, not from sniffing the file, so the extension
     * has to be the one that language actually compiles -- see the long note
     * in SubmitController on why $language->extension itself is not it.
     *
     * For Java, the filename must match the public class (or the class declaring
     * the main entry point if none is public) because javac and java enforce this.
     */
    public function filenameFor(Language $language, ?string $preferredBasename = null, ?string $source = null): string
    {
        $ext = $this->sanitize($language->getFileExtension());
        $preferredSanitized = $this->sanitize((string) $preferredBasename);
        $basename = pathinfo($preferredSanitized, PATHINFO_FILENAME);

        if ($ext === 'java') {
            $detectedClass = $source !== null ? $this->detectJavaMainClass($source) : null;
            if ($detectedClass !== null) {
                $basename = $this->sanitize($detectedClass);
            } elseif ($basename === '' || strtolower($basename) === 'main') {
                $basename = 'Main';
            }
        } elseif ($basename === '') {
            $basename = 'main';
        }

        return $basename . '.' . $ext;
    }

    /**
     * Detects the primary entry class name in a Java source file.
     *
     * 1. Strips comments and strings to avoid false matches.
     * 2. Checks for a public class/record (required by JLS to match filename).
     * 3. If none is public, finds the top-level class enclosing the main method.
     */
    public function detectJavaMainClass(string $source): ?string
    {
        // 1. Strip comments and string literals to prevent false positives from headers, templates, or logs.
        $cleaned = preg_replace('!/\*.*?\*/!s', ' ', $source);
        if ($cleaned !== null) {
            $cleaned = preg_replace('!//[^\r\n]*!m', ' ', $cleaned);
        }
        if ($cleaned !== null) {
            $cleaned = preg_replace('/"""[\s\S]*?"""|"(?:\\\\.|[^"\\\\])*"/s', '""', $cleaned);
        }
        if ($cleaned !== null) {
            $cleaned = preg_replace("/'(?:\\\\.|[^'\\\\])'/", "''", $cleaned);
        }
        $cleaned = $cleaned ?? $source;

        // 2. Search for public class or record (JLS requires filename to match public class)
        if (preg_match('/\b(?:(?:public|final|sealed|abstract)\s+)*public\s+(?:(?:public|final|sealed|abstract)\s+)*(?:class|record)\s+([A-Za-z0-9_]+)\b/', $cleaned, $matches)) {
            return $matches[1];
        }

        // 3. If no public class exists, locate the top-level class containing the main method
        $pattern = '/(?<open>\{)|(?<close>\})|(?<decl>\b(?:class|record)\s+([A-Za-z0-9_]+)\b)|(?<main>\bvoid\s+main\b)/';
        if (preg_match_all($pattern, $cleaned, $tokens, PREG_SET_ORDER)) {
            $depth = 0;
            $currentTopLevelClass = null;
            $fallbackClass = null;

            foreach ($tokens as $token) {
                if (!empty($token['decl'])) {
                    $className = $token[4];
                    if ($depth === 0) {
                        $currentTopLevelClass = $className;
                        $fallbackClass ??= $className;
                    }
                } elseif (!empty($token['open'])) {
                    $depth++;
                } elseif (!empty($token['close'])) {
                    $depth = max(0, $depth - 1);
                    if ($depth === 0) {
                        $currentTopLevelClass = null;
                    }
                } elseif (!empty($token['main'])) {
                    if ($currentTopLevelClass !== null) {
                        return $currentTopLevelClass;
                    }
                }
            }

            if ($fallbackClass !== null) {
                return $fallbackClass;
            }
        }

        return null;
    }

    /**
     * Client-supplied names are untrusted input that ends up in a storage
     * path: strip any directory component and keep a conservative charset.
     */
    public function sanitize(string $name): string
    {
        return SourceFilename::sanitize($name);
    }
}
