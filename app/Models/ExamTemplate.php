<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Receita fixa de prova (N1/N2/N3). Nao e um contest: o agendamento
 * (App\Services\ExamScheduler) copia suas questoes para um contest novo.
 */
class ExamTemplate extends Model
{
    protected $fillable = ['code', 'name', 'level', 'duration', 'penalty', 'freeze_minutes'];

    /** @return BelongsToMany<ProblemBank, $this> */
    public function problems(): BelongsToMany
    {
        return $this->belongsToMany(ProblemBank::class, 'exam_template_problems', 'exam_template_id', 'problem_bank_id')
            ->withPivot('position')
            ->orderByPivot('position');
    }

    /** @return HasMany<Contest, $this> */
    public function contests(): HasMany
    {
        return $this->hasMany(Contest::class);
    }
}
