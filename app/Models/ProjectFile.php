<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectFile extends Model
{
    protected $fillable = [
        'project_id', 'original_name', 'path', 'size', 'mime_type',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Lesbare Dateigröße (z.B. "2,4 MB").
     */
    public function getReadableSizeAttribute(): string
    {
        $bytes = $this->size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0, ',', '.') . ' KB';
        }
        return $bytes . ' B';
    }

    /**
     * Icon-Klasse passend zum MIME-Typ.
     */
    public function getIconClassAttribute(): string
    {
        $mime = $this->mime_type ?? '';
        if (str_contains($mime, 'pdf'))   return 'ph-file-pdf text-red-500';
        if (str_contains($mime, 'image')) return 'ph-file-image text-blue-500';
        if (str_contains($mime, 'word') || str_contains($mime, 'document')) return 'ph-file-doc text-blue-700';
        if (str_contains($mime, 'sheet') || str_contains($mime, 'excel')) return 'ph-file-xls text-green-600';
        if (str_contains($mime, 'zip') || str_contains($mime, 'rar')) return 'ph-file-zip text-amber-600';
        return 'ph-file text-gray-500';
    }
}
