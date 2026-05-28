<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerItilSlaSetting extends Model
{
    protected $table = 'customer_itil_sla_settings';

    protected $fillable = [
        'customer_id',
        'priority',
        'response_hours',
        'resolve_hours',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
