<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
            'company_tax_number'  => 'nullable|string|max:100',
            'company_vat_id'      => 'nullable|string|max:100',
            'hourly_rate'         => 'required|numeric|min:0',
            'tax_rate'            => 'required|numeric|min:0|max:100',
            // invoice_prefix entfällt – Format ist automatisch R-Mär26-01
            'payment_days'        => 'required|integer|min:0',
            'bank_name'           => 'nullable|string|max:255',
            'bank_iban'           => 'nullable|string|max:50',
            'bank_bic'            => 'nullable|string|max:20',
        ]);

        // Checkbox-Werte sind im POST nicht enthalten wenn nicht angehakt → explizit auf 0 setzen
        $data['kleinunternehmer'] = $request->boolean('kleinunternehmer') ? '1' : '0';

        // MwSt. auf 0 zwingen wenn Kleinunternehmer aktiv
        if ($data['kleinunternehmer'] === '1') {
            $data['tax_rate'] = '0';
        }

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->route('settings.edit')->with('success', 'Einstellungen wurden gespeichert.');
    }

    public function updatePassword(Request $request)
    {
        $currentHash = Setting::get('password_hash');

        $request->validate([
            'current_password'      => 'required',
            'new_password'          => 'required|min:6',
            'new_password_confirmation' => 'required|same:new_password',
        ], [
            'current_password.required'          => 'Bitte das aktuelle Passwort eingeben.',
            'new_password.required'              => 'Bitte ein neues Passwort eingeben.',
            'new_password.min'                   => 'Das neue Passwort muss mindestens 6 Zeichen lang sein.',
            'new_password_confirmation.required' => 'Bitte das neue Passwort bestätigen.',
            'new_password_confirmation.same'     => 'Die Passwörter stimmen nicht überein.',
        ]);

        if (!Hash::check($request->current_password, $currentHash)) {
            return redirect()->route('settings.edit')
                ->withErrors(['Das aktuelle Passwort ist falsch.'], 'password');
        }

        Setting::set('password_hash', Hash::make($request->new_password));

        return redirect()->route('settings.edit')->with('success', 'Passwort wurde geändert.');
    }
}
