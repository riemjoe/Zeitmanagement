<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    protected $fillable = [
        'customer_id', 'contract_template_id', 'title', 'content',
        'status', 'date', 'valid_until', 'signed_pdf_path', 'notes',
    ];

    protected $casts = [
        'date'        => 'date',
        'valid_until' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ContractTemplate::class, 'contract_template_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'      => 'Entwurf',
            'sent'       => 'Versendet',
            'signed'     => 'Unterzeichnet',
            'terminated' => 'Beendet',
            default      => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft'      => 'bg-gray-100 text-gray-600',
            'sent'       => 'bg-blue-100 text-blue-700',
            'signed'     => 'bg-green-100 text-green-700',
            'terminated' => 'bg-red-100 text-red-600',
            default      => 'bg-gray-100 text-gray-600',
        };
    }

    /** Geschützte Download-URL für die signierte PDF (nur eingeloggte Nutzer). */
    public function getSignedPdfUrlAttribute(): ?string
    {
        if (!$this->signed_pdf_path) return null;
        return route('contracts.download-pdf', $this->id);
    }
}
