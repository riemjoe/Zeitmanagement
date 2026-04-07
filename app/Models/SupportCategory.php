<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportCategory extends Model
{
    protected $fillable = ['name', 'priority', 'work_category_id'];

    public function workCategory(): BelongsTo
    {
        return $this->belongsTo(WorkCategory::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function slaSettings(): HasMany
    {
        return $this->hasMany(CustomerSlaSetting::class);
    }

    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            'low'      => 'Niedrig',
            'medium'   => 'Mittel',
            'high'     => 'Hoch',
            'critical' => 'Kritisch',
            default    => ucfirst($this->priority),
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'low'      => 'green',
            'medium'   => 'blue',
            'high'     => 'orange',
            'critical' => 'red',
            default    => 'gray',
        };
    }
}
