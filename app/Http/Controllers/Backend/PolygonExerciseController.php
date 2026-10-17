<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Contest;
use App\Models\PolygonExercise;
use App\Services\Polygon\LanguageLimitDerivationService;
use App\Services\Polygon\PolygonCalibrationService;
use App\Services\Polygon\PolygonGeneratorService;
use App\Services\Polygon\PolygonPublishService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use RuntimeException;

class PolygonExerciseController extends Controller
{
    public function __construct(
        protected PolygonGeneratorService $generatorService,
        protected PolygonCalibrationService $calibrationService,
        protected PolygonPublishService $publishService,
        protected LanguageLimitDerivationService $limitDerivationService
    ) {}

    /**
     * Lists all polygon exercise drafts with associated contest, filters, counts, and available contests.
     */
    public function index(Request $request): JsonResponse
    {
        $query = PolygonExercise::with('contest');

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->has('contest_id')) {
            $contestId = $request->query('contest_id');
            if ($contestId === 'none' || $contestId === 'null' || $contestId === '0') {
                $query->whereNull('contest_id');
            } elseif (is_numeric($contestId) && (int) $contestId > 0) {
                $query->where('contest_id', (int) $contestId);
            }
        }

        if ($request->filled('search')) {
            $search = '%' . $request->query('search') . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('short_name', 'like', $search)
                  ->orWhere('basename', 'like', $search);
            });
        }

        $exercises = $query->latest()->get();
        $drafts = $exercises->where('status', '!==', 'published')->values();
        $published = $exercises->where('status', '===', 'published')->values();
        $contests = Contest::select('id', 'name')->orderBy('name')->get();

        $counts = [
            'total' => PolygonExercise::count(),
            'draft' => PolygonExercise::where('status', 'draft')->count(),
            'generated' => PolygonExercise::where('status', 'generated')->count(),
            'calibrated' => PolygonExercise::where('status', 'calibrated')->count(),
            'published' => PolygonExercise::where('status', 'published')->count(),
        ];

        return response()->json([
            'data' => $exercises,
            'exercises' => $exercises,
            'drafts' => $drafts,
            'published' => $published,
            'counts' => $counts,
            'contests' => $contests,
        ], 200);
    }

    /**
     * Creates a new polygon exercise draft.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'short_name' => 'required|string|max:10',
            'basename' => 'required|string|max:100',
            'time_limit' => 'nullable|integer|min:1',
            'memory_limit' => 'nullable|integer|min:16',
            'output_limit' => 'nullable|integer|min:1',
            'contest_id' => 'nullable|exists:contests,id',
            'description' => 'nullable|string',
            'input_description' => 'nullable|string',
            'output_description' => 'nullable|string',
            'notes' => 'nullable|string',
            'color_name' => 'nullable|string|max:50',
            'color_hex' => 'nullable|string|max:7',
            'generator_source' => 'nullable|string',
            'generator_script' => 'nullable|string',
            'manual_tests' => 'nullable',
            'solution_accept' => 'nullable|string',
            'solution_tle' => 'nullable|string',
            'solution_wrong' => 'nullable|string',
            'additional_solutions' => 'nullable',
        ]);

        $validated['time_limit'] = $validated['time_limit'] ?? 1;
        $validated['memory_limit'] = $validated['memory_limit'] ?? 256;
        $validated['output_limit'] = $validated['output_limit'] ?? 1024;
        $validated['status'] = PolygonExercise::STATUS_DRAFT;
        $validated['created_by'] = Auth::id() ?? 1;

        $exercise = PolygonExercise::create($validated);

        return response()->json([
            'data' => $exercise,
            'exercise' => $exercise,
            ...$exercise->toArray(),
        ], 201);
    }

    /**
     * Shows a polygon exercise draft with its generated test cases and available contests.
     */
    public function show(string|int $id): JsonResponse
    {
        $exercise = PolygonExercise::with('contest')->findOrFail($id);
        $testsDir = $exercise->getTempDirectory() . '/tests';

        $cases = [];
        if (is_dir($testsDir)) {
            $inFiles = glob($testsDir . '/*.in') ?: [];
            natsort($inFiles);
            foreach (array_values($inFiles) as $idx => $inFile) {
                $number = $idx + 1;
                $outFile = preg_replace('/\.in$/', '.out', $inFile);
                $hasOut = file_exists($outFile);
                $inContent = @file_get_contents($inFile);
                $outContent = $hasOut ? @file_get_contents($outFile) : null;

                $isSample = false;
                if (!empty($exercise->manual_tests) && is_array($exercise->manual_tests)) {
                    if (isset($exercise->manual_tests[$idx]['is_sample'])) {
                        $isSample = (bool) $exercise->manual_tests[$idx]['is_sample'];
                    }
                }
                if (!empty($exercise->calibration_results['cases']) && is_array($exercise->calibration_results['cases'])) {
                    foreach ($exercise->calibration_results['cases'] as $c) {
                        if (($c['number'] ?? null) == $number && !empty($c['is_sample'])) {
                            $isSample = true;
                            break;
                        }
                    }
                }

                $cases[] = [
                    'number' => $number,
                    'input_file' => $inFile,
                    'output_file' => $hasOut ? $outFile : null,
                    'size_bytes' => (int) @filesize($inFile),
                    'is_sample' => $isSample,
                    'input' => $inContent !== false ? $inContent : '',
                    'output' => $outContent !== false ? $outContent : null,
                    'preview' => mb_substr($inContent !== false ? $inContent : '', 0, 256),
                ];
            }
        }

        $contests = Contest::select('id', 'name')->orderBy('name')->get();

        $data = $exercise->toArray();
        $data['cases'] = $cases;

        return response()->json([
            'data' => $data,
            'exercise' => $exercise,
            'cases' => $cases,
            'contests' => $contests,
            ...$data,
        ], 200);
    }

    /**
     * Updates a polygon exercise draft and manages state transitions.
     */
    public function update(Request $request, string|int $id): JsonResponse
    {
        $exercise = PolygonExercise::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:200',
            'short_name' => 'sometimes|required|string|max:10',
            'basename' => 'sometimes|required|string|max:100',
            'time_limit' => 'nullable|integer|min:1',
            'memory_limit' => 'nullable|integer|min:16',
            'output_limit' => 'nullable|integer|min:1',
            'contest_id' => 'nullable|exists:contests,id',
            'description' => 'nullable|string',
            'input_description' => 'nullable|string',
            'output_description' => 'nullable|string',
            'notes' => 'nullable|string',
            'color_name' => 'nullable|string|max:50',
            'color_hex' => 'nullable|string|max:7',
            'generator_source' => 'nullable|string',
            'generator_script' => 'nullable|string',
            'manual_tests' => 'nullable',
            'solution_accept' => 'nullable|string',
            'solution_tle' => 'nullable|string',
            'solution_wrong' => 'nullable|string',
            'additional_solutions' => 'nullable',
        ]);

        // State machine rule: if calibrated and generator or structural limits changed, revert to draft
        if ($exercise->status === PolygonExercise::STATUS_CALIBRATED) {
            $generatorChanged = ($request->has('generator_source') && $request->input('generator_source') !== $exercise->generator_source)
                || ($request->has('generator_script') && $request->input('generator_script') !== $exercise->generator_script)
                || ($request->has('manual_tests') && $request->input('manual_tests') != $exercise->manual_tests);

            $limitsChanged = ($request->has('time_limit') && (int) $request->input('time_limit') !== (int) $exercise->time_limit)
                || ($request->has('memory_limit') && (int) $request->input('memory_limit') !== (int) $exercise->memory_limit);

            if ($generatorChanged || $limitsChanged) {
                $exercise->status = PolygonExercise::STATUS_DRAFT;
            }
        }

        $exercise->fill($validated);
        $exercise->save();

        return response()->json([
            'data' => $exercise,
            'exercise' => $exercise,
            'message' => 'Rascunho atualizado com sucesso.',
            ...$exercise->toArray(),
        ], 200);
    }

    /**
     * Deletes a polygon exercise draft and purges its workspace directory.
     */
    public function destroy(string|int $id): Response
    {
        $exercise = PolygonExercise::findOrFail($id);
        $this->generatorService->cleanTempDirectory($exercise);
        $exercise->delete();

        return response()->noContent();
    }

    /**
     * Compiles the exercise generator source code (gen.cpp).
     */
    public function compileGenerator(Request $request, string|int $id): JsonResponse
    {
        $exercise = PolygonExercise::findOrFail($id);
        $result = $this->generatorService->compileGenerator($exercise);

        return response()->json([
            'success' => (bool) ($result['success'] ?? false),
            'output' => (string) ($result['output'] ?? ''),
            ...$result,
        ], 200);
    }

    /**
     * Executes the test generator script and produces .in files.
     */
    public function generateTests(Request $request, string|int $id): JsonResponse
    {
        $exercise = PolygonExercise::findOrFail($id);
        $result = $this->generatorService->generateTests($exercise);

        return response()->json([
            'success' => (bool) ($result['success'] ?? false),
            'total_tests' => (int) ($result['total_tests'] ?? 0),
            'cases' => $result['cases'] ?? [],
            ...$result,
        ], 200);
    }

    /**
     * Runs calibration on all test cases with ACCEPT, TLE, and WRONG solutions.
     */
    public function calibrate(Request $request, string|int $id): JsonResponse
    {
        $exercise = PolygonExercise::findOrFail($id);

        try {
            $result = $this->calibrationService->calibrate($exercise);

            return response()->json([
                'data' => $result,
                ...$result,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Erro ao calibrar soluções: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Publishes a calibrated polygon exercise into a contest or global problem bank.
     */
    public function publish(Request $request, string|int $id): JsonResponse
    {
        $exercise = PolygonExercise::findOrFail($id);

        try {
            $forceTarget = $request->input('target');
            $result = $this->publishService->publish($exercise, $forceTarget);

            return response()->json([
                'success' => true,
                'target' => $result['target'],
                'problem_id' => $result['problem_id'] ?? null,
                'bank_code' => $result['bank_code'] ?? null,
                'data' => $result,
                ...$result,
            ], 200);
        } catch (InvalidArgumentException|RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
