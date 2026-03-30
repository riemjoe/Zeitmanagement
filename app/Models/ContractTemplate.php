<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContractTemplate extends Model
{
    protected $fillable = ['name', 'description', 'type', 'content'];

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    /** Typ-Label auf Deutsch */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'privacy'     => 'Datenschutz',
            'handover'    => 'Übergabe',
            'maintenance' => 'Wartung',
            default       => 'Sonstige',
        };
    }

    /**
     * Platzhalter im Template mit echten Werten befüllen.
     *
     * Verfügbare Platzhalter:
     *   {{customer_name}}, {{customer_street}}, {{customer_zip}}, {{customer_city}},
     *   {{customer_email}}, {{company_name}}, {{company_street}}, {{company_zip}},
     *   {{company_city}}, {{company_email}}, {{company_phone}}, {{date}}
     */
    public function render(Customer $customer, array $settings = [], ?string $date = null): string
    {
        $date     = $date ?? now()->format('d.m.Y');
        $address  = trim(($customer->street ?? '') . "\n" . trim(($customer->zip ?? '') . ' ' . ($customer->city ?? '')));

        $replacements = [
            '{{customer_name}}'    => $customer->name,
            '{{customer_street}}'  => $customer->street ?? '',
            '{{customer_zip}}'     => $customer->zip    ?? '',
            '{{customer_city}}'    => $customer->city   ?? '',
            '{{customer_email}}'   => $customer->email  ?? '',
            '{{company_name}}'     => $settings['company_name']   ?? '',
            '{{company_street}}'   => $settings['company_street'] ?? '',
            '{{company_zip}}'      => $settings['company_zip']    ?? '',
            '{{company_city}}'     => $settings['company_city']   ?? '',
            '{{company_email}}'    => $settings['company_email']  ?? '',
            '{{company_phone}}'    => $settings['company_phone']  ?? '',
            '{{date}}'             => $date,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $this->content);
    }
}
