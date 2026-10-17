<?php

namespace App\Models;

use Database\Factories\PolygonExerciseFactory;
use Helium\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolygonExercise extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_GENERATED = 'generated';
    public const STATUS_CALIBRATED = 'calibrated';
    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'contest_id',
        'name',
        'short_name',
        'basename',
        'time_limit',
        'memory_limit',
        'output_limit',
        'description',
        'input_description',
        'output_description',
        'notes',
        'color_name',
        'color_hex',
        'generator_source',
        'generator_script',
        'manual_tests',
        'solution_accept',
        'solution_tle',
        'solution_wrong',
        'additional_solutions',
        'status',
        'calibration_results',
        'published_problem_id',
        'published_bank_code',
        'created_by',
    ];

    protected $casts = [
        'manual_tests' => 'array',
        'additional_solutions' => 'array',
        'calibration_results' => 'array',
        'time_limit' => 'integer',
        'memory_limit' => 'integer',
        'output_limit' => 'integer',
    ];

    /**
     * Create a new factory instance for the model.
     *
     * @return Factory
     */
    protected static function newFactory()
    {
        return PolygonExerciseFactory::new();
    }

    /**
     * The contest this exercise is targeted to, or null if it targets the global ProblemBank.
     *
     * @return BelongsTo<Contest, $this>
     */
    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    /**
     * The published problem in a contest if finalized and published.
     *
     * @return BelongsTo<Problem, $this>
     */
    public function publishedProblem(): BelongsTo
    {
        return $this->belongsTo(Problem::class, 'published_problem_id');
    }

    /**
     * The administrator user who created this polygon exercise.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    /**
     * Alias for creator relation.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->creator();
    }

    /**
     * Returns the dedicated temporary workspace directory for compiling and executing this exercise.
     */
    public function getTempDirectory(): string
    {
        return storage_path("app/temp/polygon_{$this->id}");
    }
}
