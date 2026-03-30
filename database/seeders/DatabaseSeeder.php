<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;
use App\Models\WorkCategory;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Standard-Einstellungen
        $defaults = [
            'company_name'        => 'Mein Unternehmen',
            'company_street'      => 'Musterstraße 1',
            'company_zip'         => '12345',
            'company_city'        => 'Musterstadt',
            'company_country'     => 'Deutschland',
            'company_email'       => 'info@example.com',
            'company_phone'       => '',
            'company_tax_number'  => '',   // Steuernummer (z.B. 123/456/78901)
            'company_vat_id'      => '',   // USt-IdNr. (z.B. DE123456789)
            'hourly_rate'         => '80.00',
            'tax_rate'            => '19',
            // Rechnungsnummern werden automatisch generiert (Format: R-Mär26-01)
            'kleinunternehmer'    => '0',
            'payment_days'        => '14',
            'bank_name'           => '',
            'bank_iban'           => '',
            'bank_bic'            => '',
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        // Standard-Arbeitskategorien
        $categories = [
            ['name' => 'Entwicklung',       'color' => '#6366f1'],
            ['name' => 'Support',           'color' => '#f59e0b'],
            ['name' => 'Beratung',          'color' => '#10b981'],
            ['name' => 'Design',            'color' => '#ec4899'],
            ['name' => 'Projektmanagement', 'color' => '#3b82f6'],
        ];

        foreach ($categories as $cat) {
            WorkCategory::firstOrCreate(['name' => $cat['name']], ['color' => $cat['color']]);
        }
    }
}
