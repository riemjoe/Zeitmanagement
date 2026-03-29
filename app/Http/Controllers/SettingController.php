<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        $settings = Setting::getAll();
        return view('settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'company_name'        => 'required|string|max:255',
            'company_street'      => 'nullable|string|max:255',
            'company_zip'         => 'nullable|string|max:20',
            'company_city'        => 'nullable|string|max:255',
            'company_country'     => 'nullable|string|max:100',
            'company_email'       => 'nullable|email|max:255',
            'company_phone'       => 'nullable|string|max:50',
            'company_tax_id'      => 'nullable|string|max:100',
            'hourly_rate'         => 'required|numeric|min:0',
            'tax_rate'            => 'required|numeric|min:0|max:100',
            // invoice_prefix entfällt – Format ist automatisch R-Mär26-01
            'payment_days'        => 'required|integer|min:0',
            'bank_name'           => 'nullable|string|max:255',
            'bank_iban'           => 'nullable|string|max:50',
            'bank_bic'            => 'nullable|string|max:20',
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->route('settings.edit')->with('success', 'Einstellungen wurden gespeichert.');
    }
}
