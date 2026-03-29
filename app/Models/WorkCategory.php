<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkCategory extends Model
{
    protected $fillable = ['name', 'color'];

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }
}
