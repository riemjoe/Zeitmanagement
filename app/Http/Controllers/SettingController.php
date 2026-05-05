<?php

namespace App\Http\Controllers;

use App\Mail\MaintenanceReminder;
use App\Models\EmailLog;
use App\Models\MaintenanceEvent;
use App\Models\Project;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    /** Einstellungsseite anzeigen (alle Nutzer). */
    public function edit()
    {
        $settings  = Setting::getAll();
        $emailLogs = EmailLog::with('ticket')
            ->latest()
            ->limit(200)
            ->get();

        return view('settings.edit', compact('settings', 'emailLogs'));
    }

    /**
     * Unternehmenseinstellungen speichern (nur Admin, via Route-Middleware).
     */
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
            'payment_days'        => 'required|integer|min:0',
            'bank_name'           => 'nullable|string|max:255',
            'bank_iban'           => 'nullable|string|max:50',
            'bank_bic'            => 'nullable|string|max:20',
            'dark_mode'           => 'required|in:off,on,auto',
            'dark_mode_from'      => 'nullable|string|max:10',
            'dark_mode_to'        => 'nullable|string|max:10',
            // Helpdesk-Branding
            'helpdesk_name'       => 'nullable|string|max:255',
            'helpdesk_subtitle'   => 'nullable|string|max:500',
            'helpdesk_logo_url'   => 'nullable|url|max:500',
            'helpdesk_accent'     => 'nullable|string|max:20',
            'privacy_url'         => 'nullable|url|max:500',
            'imprint_url'         => 'nullable|url|max:500',
        ]);

        $data['kleinunternehmer'] = $request->boolean('kleinunternehmer') ? '1' : '0';
        $data['dark_mode']        = $data['dark_mode'] ?? 'off';
        $data['dark_mode_from']   = $data['dark_mode_from'] ?? '21:00';
        $data['dark_mode_to']     = $data['dark_mode_to']   ?? '06:00';

        if ($data['kleinunternehmer'] === '1') {
            $data['tax_rate'] = '0';
        }

        foreach ($data as $key => $value) {
            Setting::set($key, $value ?? '');
        }

        return redirect()->route('settings.edit')->with('success', 'Einstellungen wurden gespeichert.');
    }

    /**
     * Eigenes Profil (Name, E-Mail) aktualisieren – für alle Nutzer.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ], [
            'name.required'  => 'Bitte einen Namen eingeben.',
            'email.required' => 'Bitte eine E-Mail-Adresse eingeben.',
            'email.unique'   => 'Diese E-Mail-Adresse wird bereits verwendet.',
        ]);

        $user->update(['name' => $data['name'], 'email' => $data['email']]);

        return redirect()->route('settings.edit')->with('success', 'Profil wurde aktualisiert.');
    }

    /**
     * Eigenes Passwort ändern – für alle Nutzer.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:8|confirmed',
        ], [
            'current_password.required' => 'Bitte das aktuelle Passwort eingeben.',
            'new_password.required'     => 'Bitte ein neues Passwort eingeben.',
            'new_password.min'          => 'Das neue Passwort muss mindestens 8 Zeichen lang sein.',
            'new_password.confirmed'    => 'Die Passwörter stimmen nicht überein.',
        ]);

        if (! Hash::check($request->current_password, $user->password)) {
            return redirect()->route('settings.edit')
                ->withErrors(['Das aktuelle Passwort ist falsch.'], 'password');
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return redirect()->route('settings.edit')->with('success', 'Passwort wurde geändert.');
    }

    /**
     * Kundennachricht-Template speichern.
     */
    public function updateCustomerMessageTemplate(Request $request)
    {
        $data = $request->validate([
            'customer_message_subject'  => 'nullable|string|max:255',
            'customer_message_template' => 'nullable|string',
        ]);

        Setting::set('customer_message_subject',  $data['customer_message_subject']  ?? 'Nachricht von uns');
        Setting::set('customer_message_template', $data['customer_message_template'] ?? '');

        return redirect()->route('settings.edit')
            ->with('success', 'Kundennachricht-Template wurde gespeichert.');
    }

    /**
     * Mahnwesen-Einstellungen speichern (nur Admin).
     */
    public function updateDunning(Request $request)
    {
        $data = $request->validate([
            'dunning_reminder_days' => 'required|integer|min:1|max:90',
            'dunning_notice_days'   => 'required|integer|min:1|max:90',
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->route('settings.edit')
            ->with('success', 'Mahnungseinstellungen wurden gespeichert.')
            ->with('_tab', 'mahnungen');
    }

    /**
     * Sendet sofort eine Test-Wartungserinnerung an den eingeloggten Admin.
     */
    public function testMail(Request $request)
    {
        $project = Project::first();

        if (!$project) {
            return redirect()->route('settings.edit')
                ->with('error', 'Kein Projekt vorhanden. Bitte zuerst ein Projekt anlegen.');
        }

        $event = new MaintenanceEvent([
            'project_id'     => $project->id,
            'title'          => '🔧 Test-Wartungserinnerung',
            'description'    => 'Dies ist eine automatisch generierte Test-Nachricht, um den E-Mail-Versand zu prüfen.',
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => now()->addMinute()->format('H:i:s'),
            'priority'       => 'medium',
            'notify'         => true,
        ]);
        $event->setRelation('project', $project);
        $event->setRelation('assignedUser', null);

        $subject = '🔧 Test-Wartungserinnerung';

        try {
            Mail::to(Auth::user()->email)
                ->send(new MaintenanceReminder($event));

            EmailLog::record('test_mail', Auth::user()->email, $subject, 'sent');

            return redirect()->route('settings.edit')
                ->with('success', 'Test-E-Mail wurde erfolgreich an ' . Auth::user()->email . ' gesendet.');
        } catch (\Throwable $e) {
            Log::error('Test-Mail fehlgeschlagen: ' . $e->getMessage());
            EmailLog::record('test_mail', Auth::user()->email, $subject, 'failed', $e->getMessage());

            return redirect()->route('settings.edit')
                ->with('error', 'E-Mail-Versand fehlgeschlagen: ' . $e->getMessage());
        }
    }
}
