<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerSlaSetting extends Model
{
    protected $fillable = ['customer_id', 'support_category_id', 'sla_hours'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function supportCategory(): BelongsTo
    {
        return $this->belongsTo(SupportCategory::class);
    }
}
