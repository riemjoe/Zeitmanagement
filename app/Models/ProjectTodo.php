<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTodo extends Model
{
    protected $fillable = [
        'project_id', 'title', 'description',
        'completed', 'completed_at', 'sort_order',
    ];

    protected $casts = [
        'completed'    => 'boolean',
        'completed_at' => 'datetime',
        'sort_order'   => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
