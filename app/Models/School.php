<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    protected $fillable = ['name', 'city', 'archived_at'];

    protected $casts = ['archived_at' => 'datetime'];

    /** @return HasMany<SchoolTeam, $this> */
    public function teams(): HasMany
    {
        return $this->hasMany(SchoolTeam::class)->orderBy('level')->orderBy('name');
    }
}
