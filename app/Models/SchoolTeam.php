<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Time permanente de uma escola; a conta `users` e criada por prova. */
class SchoolTeam extends Model
{
    protected $fillable = ['school_id', 'name', 'level'];

    protected $casts = ['level' => 'integer'];

    /** @return BelongsTo<School, $this> */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /** @return HasMany<ExamParticipation, $this> */
    public function participations(): HasMany
    {
        return $this->hasMany(ExamParticipation::class);
    }

    /** @return HasMany<SchoolTeamMember, $this> */
    public function members(): HasMany
    {
        return $this->hasMany(SchoolTeamMember::class, 'school_team_id');
    }
}
