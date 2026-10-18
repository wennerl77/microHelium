<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamParticipation extends Model
{
    protected $fillable = ['school_team_id', 'contest_id', 'user_id', 'phase'];

    /** @return BelongsTo<SchoolTeam, $this> */
    public function schoolTeam(): BelongsTo
    {
        return $this->belongsTo(SchoolTeam::class);
    }

    /** @return BelongsTo<Contest, $this> */
    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    /** @return BelongsTo<\Helium\User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\Helium\User::class, 'user_id', 'user_id');
    }
}
