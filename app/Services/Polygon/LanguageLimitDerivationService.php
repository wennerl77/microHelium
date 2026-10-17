<?php

namespace App\Services\Polygon;

use App\Models\Language;
use App\Models\PolygonExercise;
use App\Models\Problem;
use App\Models\ProblemLanguageLimit;

class LanguageLimitDerivationService
{
    public const SOURCE_BASE = 'base';
    public const SOURCE_MEASURED = 'measured';
    public const SOURCE_DERIVED_3X = 'derived_3x';
    public const SOURCE_DERIVED_JVM = 'derived_jvm';
    public const SOURCE_DERIVED_2X = 'derived_2x';
    public const SOURCE_DERIVED_DEFAULT = 'derived_default';

    public const CATEGORY_CPP = 'cpp';
    public const CATEGORY_PYTHON = 'python';
    public const CATEGORY_JVM = 'jvm';
    public const CATEGORY_NODE = 'node';
    public const CATEGORY_DEFAULT = 'default';

    /**
     * Derives per-language execution limits (time and memory) from base parameters.
     *
     * @param  int|float  $baseTimeLimit   Base time limit in seconds
     * @param  int  $baseMemoryLimit Base memory limit in MB
     * @param  iterable|null  $activeLanguages Optional collection/array of Language instances or arrays
     * @param  string  $baseSource      Source tag for C/C++ ('base' or 'measured')
     * @return array<int, array{
     *     language_id: int|null,
     *     extension: string,
     *     language: string,
     *     name: string,
     *     time_limit: int,
     *     memory_limit: int,
     *     source: string
     * }>
     */
    public function deriveLimits(
        int|float $baseTimeLimit,
        int $baseMemoryLimit,
        ?iterable $activeLanguages = null,
        string $baseSource = self::SOURCE_BASE
    ): array {
        $languages = $this->resolveActiveLanguages($activeLanguages);
        $results = [];

        foreach ($languages as $lang) {
            $results[] = $this->deriveForLanguage($lang, $baseTimeLimit, $baseMemoryLimit, $baseSource);
        }

        return $results;
    }

    /**
     * Derives limits for an existing PolygonExercise instance.
     *
     * @param  PolygonExercise  $exercise
     * @param  string  $baseSource
     * @return array
     */
    public function deriveForExercise(PolygonExercise $exercise, string $baseSource = self::SOURCE_BASE): array
    {
        $activeLanguages = null;

        if ($exercise->contest_id) {
            try {
                $contestLangs = Language::where('contest_id', $exercise->contest_id)
                    ->where('is_active', true)
                    ->get();

                if ($contestLangs->isNotEmpty()) {
                    $activeLanguages = $contestLangs;
                }
            } catch (\Throwable) {
                // Ignore DB errors in unmigrated or isolated environments
            }
        }

        $baseTime = $exercise->time_limit ?? 1;
        $baseMemory = $exercise->memory_limit ?? 256;

        return $this->deriveLimits($baseTime, $baseMemory, $activeLanguages, $baseSource);
    }

    /**
     * Derives execution limits for a single language item.
     *
     * @param  mixed  $language Model, array, or extension string
     * @param  int|float  $baseTimeLimit
     * @param  int  $baseMemoryLimit
     * @param  string  $baseSource
     * @return array{
     *     language_id: int|null,
     *     extension: string,
     *     language: string,
     *     name: string,
     *     time_limit: int,
     *     memory_limit: int,
     *     source: string
     * }
     */
    public function deriveForLanguage(
        mixed $language,
        int|float $baseTimeLimit,
        int $baseMemoryLimit,
        string $baseSource = self::SOURCE_BASE
    ): array {
        $languageId = is_object($language)
            ? ($language->id ?? null)
            : ($language['id'] ?? null);

        $extension = is_object($language)
            ? ($language->extension ?? 'cpp')
            : (is_array($language) ? ($language['extension'] ?? 'cpp') : (string) $language);

        $name = is_object($language)
            ? ($language->name ?? $extension)
            : (is_array($language) ? ($language['name'] ?? $extension) : $extension);

        $fileExt = is_object($language)
            ? ($language->file_ext ?? null)
            : (is_array($language) ? ($language['file_ext'] ?? null) : null);

        $category = $this->determineCategory($extension, $name, $fileExt);
        $normalizedFamily = $this->normalizeFamily($extension, $name, $fileExt);

        switch ($category) {
            case self::CATEGORY_CPP:
                // C / C++: 1.0x (base), tempo e memória iguais à base
                $derivedTime = (int) max(1, (int) ceil($baseTimeLimit * 1.0));
                $derivedMemory = (int) $baseMemoryLimit;
                $source = $baseSource;
                break;

            case self::CATEGORY_PYTHON:
                // Python: 3.0x do tempo base (mínimo de 1s para startup, teto arredondado para cima)
                $derivedTime = (int) ceil(max(1, $baseTimeLimit * 3.0));
                $derivedMemory = (int) $baseMemoryLimit;
                $source = self::SOURCE_DERIVED_3X;
                break;

            case self::CATEGORY_JVM:
                // Java / Kotlin / Scala: 2.0x do tempo base + 1.0s de startup de JVM, memória base + 128MB
                $derivedTime = (int) ceil($baseTimeLimit * 2.0 + 1.0);
                $derivedMemory = (int) ($baseMemoryLimit + 128);
                $source = self::SOURCE_DERIVED_JVM;
                break;

            case self::CATEGORY_NODE:
                // JavaScript / TypeScript / Node: 2.0x do tempo base, memória base
                $derivedTime = (int) max(1, (int) ceil($baseTimeLimit * 2.0));
                $derivedMemory = (int) $baseMemoryLimit;
                $source = self::SOURCE_DERIVED_2X;
                break;

            case self::CATEGORY_DEFAULT:
            default:
                // Outras linguagens ativas / desconhecidas: default seguro 2.0x do tempo base, memória base
                $derivedTime = (int) max(1, (int) ceil($baseTimeLimit * 2.0));
                $derivedMemory = (int) $baseMemoryLimit;
                $source = self::SOURCE_DERIVED_DEFAULT;
                break;
        }

        return [
            'language_id' => $languageId,
            'extension' => $extension,
            'language' => $normalizedFamily,
            'name' => $name,
            'time_limit' => $derivedTime,
            'memory_limit' => $derivedMemory,
            'source' => $source,
        ];
    }

    /**
     * Determines the limit derivation category from language metadata.
     *
     * @param  string  $extension
     * @param  string  $name
     * @param  string|null  $fileExt
     * @return string
     */
    public function determineCategory(string $extension, string $name = '', ?string $fileExt = null): string
    {
        $ext = strtolower(trim($extension));
        $n = strtolower(trim($name));
        $fExt = strtolower(trim($fileExt ?? ''));

        // 1. Python / PyPy (py, py3, python, pypy, etc.)
        if (
            in_array($ext, ['py', 'py2', 'py3', 'pypy', 'pypy3', 'python'], true)
            || in_array($fExt, ['py'], true)
            || preg_match('/^(py|pypy|python)(_|\d|$)/i', $ext)
            || str_contains($n, 'python')
            || str_contains($n, 'pypy')
        ) {
            return self::CATEGORY_PYTHON;
        }

        // 2. JavaScript / TypeScript / Node (js, ts, node, etc.)
        if (
            in_array($ext, ['js', 'ts', 'node', 'nodejs'], true)
            || in_array($fExt, ['js', 'ts'], true)
            || preg_match('/^(js|ts|node)(_|\d|$)/i', $ext)
            || str_contains($n, 'javascript')
            || str_contains($n, 'typescript')
            || str_contains($n, 'node')
        ) {
            return self::CATEGORY_NODE;
        }

        // 3. JVM Languages: Java / Kotlin / Scala / Groovy / Clojure (java, kt, scala, etc.)
        if (
            in_array($ext, ['java', 'kt', 'kotlin', 'scala', 'groovy', 'clj', 'clojure'], true)
            || in_array($fExt, ['java', 'kt', 'scala', 'groovy'], true)
            || preg_match('/^(java\d*|kt|scala|groovy|clj)(_|$)/i', $ext)
            || (str_contains($n, 'java') && ! str_contains($n, 'javascript'))
            || str_contains($n, 'kotlin')
            || str_contains($n, 'scala')
            || str_contains($n, 'groovy')
            || str_contains($n, 'clojure')
        ) {
            return self::CATEGORY_JVM;
        }

        // 4. C / C++ (c, cpp, cc, cxx, c99, c_gcc13, cpp_gpp13, etc.)
        if (
            in_array($ext, ['c', 'cpp', 'cc', 'cxx'], true)
            || in_array($fExt, ['c', 'cpp'], true)
            || preg_match('/^(c|cpp|c99|c11|c17|c23)(_|$)/i', $ext)
            || str_contains($n, 'c++')
            || str_contains($n, 'g++')
            || str_contains($n, 'clang++')
            || (preg_match('/\b(c|c99|c11|c17|c23)\b/i', $n) && ! str_contains($n, 'c#') && ! str_contains($n, 'csharp'))
        ) {
            return self::CATEGORY_CPP;
        }

        // 5. Default fallback for other/unknown languages
        return self::CATEGORY_DEFAULT;
    }

    /**
     * Returns a normalized canonical family string for display and grouping.
     *
     * @param  string  $extension
     * @param  string  $name
     * @param  string|null  $fileExt
     * @return string
     */
    public function normalizeFamily(string $extension, string $name = '', ?string $fileExt = null): string
    {
        $category = $this->determineCategory($extension, $name, $fileExt);
        $ext = strtolower(trim($extension));
        $n = strtolower(trim($name));

        switch ($category) {
            case self::CATEGORY_CPP:
                if ($ext === 'c' || (preg_match('/^c(_|\d|$)/i', $ext) && ! preg_match('/^cpp/i', $ext))) {
                    return 'c';
                }
                return 'cpp';

            case self::CATEGORY_PYTHON:
                return 'python';

            case self::CATEGORY_JVM:
                if ($ext === 'kt' || str_contains($n, 'kotlin')) {
                    return 'kotlin';
                }
                if ($ext === 'scala' || str_contains($n, 'scala')) {
                    return 'scala';
                }
                return 'java';

            case self::CATEGORY_NODE:
                if ($ext === 'ts' || str_contains($n, 'typescript')) {
                    return 'typescript';
                }
                return 'javascript';

            case self::CATEGORY_DEFAULT:
            default:
                return ! empty($ext) ? preg_replace('/[^a-z0-9_-]/', '', $ext) : 'default';
        }
    }

    /**
     * Persists or updates derived limits into problem_language_limits for the given Problem.
     *
     * @param  Problem  $problem
     * @param  array  $derivedLimits
     * @return void
     */
    public function applyLimitsToProblem(Problem $problem, array $derivedLimits): void
    {
        foreach ($derivedLimits as $limit) {
            $languageId = $limit['language_id'] ?? null;

            if (! $languageId && ! empty($limit['extension'])) {
                try {
                    $lang = Language::where('contest_id', $problem->contest_id)
                        ->where('extension', $limit['extension'])
                        ->first()
                        ?? Language::where('extension', $limit['extension'])->first();

                    $languageId = $lang?->id;
                } catch (\Throwable) {
                    $languageId = null;
                }
            }

            if (! $languageId) {
                continue;
            }

            ProblemLanguageLimit::updateOrCreate(
                [
                    'problem_id' => $problem->id,
                    'language_id' => $languageId,
                ],
                [
                    'time_limit' => (int) $limit['time_limit'],
                    'memory_limit' => (int) $limit['memory_limit'],
                ]
            );
        }

        if ($problem->relationLoaded('languageLimits')) {
            $problem->load('languageLimits');
        }
    }

    /**
     * Resolves the list of active languages to use for derivation.
     *
     * @param  iterable|null  $activeLanguages
     * @return iterable
     */
    private function resolveActiveLanguages(?iterable $activeLanguages): iterable
    {
        if ($activeLanguages !== null) {
            return $activeLanguages;
        }

        try {
            $dbLanguages = Language::where('is_active', true)->get();
            if ($dbLanguages->isNotEmpty()) {
                return $dbLanguages;
            }
        } catch (\Throwable) {
            // DB table might not exist in isolated test scenarios
        }

        $defaultLanguages = Language::getDefaultLanguages();

        return array_values(array_filter($defaultLanguages, fn ($l) => ! empty($l['is_active'])));
    }
}
