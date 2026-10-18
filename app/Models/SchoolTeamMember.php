<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Participante / aluno de um time escolar.
 */
class SchoolTeamMember extends Model
{
    protected $fillable = [
        'school_team_id',
        'name',
        'academic_registry',
        'email',
        'phone',
        'education_level',
        'college_period',
    ];

    /** @return BelongsTo<SchoolTeam, $this> */
    public function team(): BelongsTo
    {
        return $this->belongsTo(SchoolTeam::class, 'school_team_id');
    }
}
