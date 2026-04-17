<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Setting;
use App\Mail\CustomerPortalInvitation;
use App\Services\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CustomerPortalController extends Controller
{
    // ── Auth ────────────────────────────────────────────────────────────────

    public function showLogin()
    {
        if (session('portal_customer_id')) {
            return redirect()->route('portal.dashboard');
        }
        return view('portal.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $customer = Customer::where('email', $data['email'])
            ->where('portal_enabled', true)
            ->first();

        if (! $customer || ! Hash::check($data['password'], $customer->portal_password)) {
            return back()->withErrors(['email' => 'E-Mail-Adresse oder Passwort ist falsch.'])->withInput();
        }

        session(['portal_customer_id' => $customer->id]);
        session()->forget('portal_2fa_verified');

        // Passwortänderung erzwingen
        if ($customer->portal_must_change_password) {
            return redirect()->route('portal.change-password');
        }

        // 2FA-Verifizierung
        if ($customer->portal_2fa_enabled) {
            return redirect()->route('portal.2fa.verify');
        }

        return redirect()->route('portal.dashboard');
    }

    public function logout()
    {
        session()->forget(['portal_customer_id', 'portal_2fa_verified']);
        return redirect()->route('portal.login')->with('success', 'Sie wurden erfolgreich abgemeldet.');
    }

    // ── Einladungslink ───────────────────────────────────────────────────────

    public function acceptInvitation(Request $request, string $token)
    {
        $customer = Customer::where('portal_invitation_token', $token)
            ->where('portal_enabled', true)
            ->first();

        if (! $customer) {
            return redirect()->route('portal.login')->with('error', 'Der Einladungslink ist ungültig.');
        }

        if ($customer->portal_invitation_expires_at && $customer->portal_invitation_expires_at->isPast()) {
            return redirect()->route('portal.login')->with('error', 'Der Einladungslink ist abgelaufen. Bitte wenden Sie sich an Ihren Ansprechpartner.');
        }

        // Token löschen, Sitzung starten
        $customer->update([
            'portal_invitation_token'    => null,
            'portal_invitation_expires_at' => null,
            'portal_must_change_password' => true,
        ]);

        session(['portal_customer_id' => $customer->id]);
        session()->forget('portal_2fa_verified');

        return redirect()->route('portal.change-password')
            ->with('info', 'Willkommen! Bitte legen Sie ein Passwort für Ihren Zugang fest.');
    }

    // ── Passwort ändern ──────────────────────────────────────────────────────

    public function showChangePassword()
    {
        return view('portal.change-password');
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
        ]);

        $customer = Customer::findOrFail(session('portal_customer_id'));
        $customer->update([
            'portal_password'             => Hash::make($data['password']),
            'portal_must_change_password' => false,
        ]);

        // Nach Passwortänderung: 2FA-Info-Seite zeigen (wenn noch nicht aktiviert)
        if (! $customer->portal_2fa_enabled) {
            return redirect()->route('portal.2fa.prompt');
        }

        return redirect()->route('portal.dashboard')
            ->with('success', 'Passwort wurde erfolgreich geändert.');
    }

    // ── 2FA Prompt (Info-Seite nach erstem Login) ───────────────────────────

    public function show2faPrompt()
    {
        return view('portal.2fa-prompt');
    }

    // ── 2FA Setup ───────────────────────────────────────────────────────────

    public function show2faSetup()
    {
        $customer = Customer::findOrFail(session('portal_customer_id'));

        if (! $customer->portal_2fa_secret) {
            $customer->update(['portal_2fa_secret' => TotpService::generateSecret()]);
            $customer->refresh();
        }

        $otpUrl   = TotpService::getOtpAuthUrl($customer->portal_2fa_secret, $customer->email, Setting::get('company_name', 'ZeitManager'));
        $qrUrl    = TotpService::getQrCodeUrl($otpUrl);
        $secret   = $customer->portal_2fa_secret;

        return view('portal.2fa-setup', compact('qrUrl', 'secret'));
    }

    public function confirm2faSetup(Request $request)
    {
        $data = $request->validate(['code' => 'required|string|size:6']);

        $customer = Customer::findOrFail(session('portal_customer_id'));

        if (! TotpService::verify($customer->portal_2fa_secret, $data['code'])) {
            return back()->withErrors(['code' => 'Der Code ist ungültig. Bitte versuchen Sie es erneut.']);
        }

        $backupCodes = TotpService::generateBackupCodes();
        $customer->update([
            'portal_2fa_enabled'      => true,
            'portal_2fa_backup_codes' => array_map('md5', $backupCodes), // Hash backup codes
        ]);

        session(['portal_2fa_verified' => true]);

        return redirect()->route('portal.2fa.backup-codes')
            ->with('backup_codes', $backupCodes);
    }

    public function showBackupCodes(Request $request)
    {
        $codes = session('backup_codes') ?? $request->session()->get('backup_codes');
        return view('portal.2fa-backup-codes', compact('codes'));
    }

    // ── 2FA Verifikation (beim Login) ────────────────────────────────────────

    public function show2faVerify()
    {
        return view('portal.2fa-verify');
    }

    public function verify2fa(Request $request)
    {
        $data = $request->validate(['code' => 'required|string']);

        $customer = Customer::findOrFail(session('portal_customer_id'));
        $code     = preg_replace('/\s/', '', $data['code']);

        // TOTP-Code prüfen
        if (TotpService::verify($customer->portal_2fa_secret, $code)) {
            session(['portal_2fa_verified' => true]);
            return redirect()->route('portal.dashboard');
        }

        // Backup-Code prüfen
        $backupCodes = $customer->portal_2fa_backup_codes ?? [];
        $hashedCode  = md5($code);

        if (in_array($hashedCode, $backupCodes, true)) {
            // Backup-Code einmalig verbrauchen
            $remaining = array_values(array_filter($backupCodes, fn($c) => $c !== $hashedCode));
            $customer->update(['portal_2fa_backup_codes' => $remaining]);

            session(['portal_2fa_verified' => true]);
            return redirect()->route('portal.dashboard')
                ->with('warning', 'Sie haben einen Backup-Code verwendet. Bitte generieren Sie neue Backup-Codes in Ihren Sicherheitseinstellungen.');
        }

        return back()->withErrors(['code' => 'Der Code ist ungültig.']);
    }

    // ── Portal-Seiten ────────────────────────────────────────────────────────

    public function dashboard()
    {
        $customer = Customer::findOrFail(session('portal_customer_id'));
        $customer->load(['projects', 'tickets' => fn($q) => $q->latest()->limit(5), 'invoices' => fn($q) => $q->latest()->limit(5)]);

        $openTickets   = $customer->tickets()->whereNotIn('status', ['closed', 'resolved'])->count();
        $openInvoices  = $customer->invoices()->where('status', 'sent')->count();
        $activeProjects = $customer->projects()->where('status', 'active')->count();

        return view('portal.dashboard', compact('customer', 'openTickets', 'openInvoices', 'activeProjects'));
    }

    public function tickets()
    {
        $customer = Customer::findOrFail(session('portal_customer_id'));
        $tickets  = $customer->tickets()->with('supportCategory')->latest()->paginate(20);
        return view('portal.tickets', compact('customer', 'tickets'));
    }

    public function ticket(\App\Models\Ticket $ticket)
    {
        $customer = Customer::findOrFail(session('portal_customer_id'));

        if ($ticket->customer_id !== $customer->id) {
            abort(403);
        }

        $ticket->load(['messages', 'supportCategory']);
        return view('portal.ticket-detail', compact('customer', 'ticket'));
    }

    public function projects()
    {
        $customer = Customer::findOrFail(session('portal_customer_id'));
        $projects = $customer->projects()->with(['timeEntries'])->get();
        return view('portal.projects', compact('customer', 'projects'));
    }

    public function invoices()
    {
        $customer = Customer::findOrFail(session('portal_customer_id'));
        $invoices = $customer->invoices()->latest()->paginate(20);
        return view('portal.invoices', compact('customer', 'invoices'));
    }

    // ── Admin: Portal-Zugang verwalten ──────────────────────────────────────

    public function adminEnablePortal(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'mode'     => 'required|in:password,invitation',
            'password' => 'required_if:mode,password|nullable|string|min:8',
        ]);

        if ($data['mode'] === 'password') {
            $customer->update([
                'portal_enabled'              => true,
                'portal_password'             => Hash::make($data['password']),
                'portal_must_change_password' => true,
                'portal_invitation_token'     => null,
                'portal_invitation_expires_at' => null,
            ]);
            return back()->with('success', 'Portal-Zugang wurde aktiviert. Der Kunde kann sich nun mit dem angegebenen Passwort anmelden.');
        }

        // Einladungslink generieren
        if (! $customer->email) {
            return back()->with('error', 'Der Kunde hat keine E-Mail-Adresse hinterlegt.');
        }

        $token   = Str::random(48);
        $expires = now()->addDays(7);

        $customer->update([
            'portal_enabled'               => true,
            'portal_invitation_token'      => $token,
            'portal_invitation_expires_at' => $expires,
            'portal_must_change_password'  => true,
        ]);

        $url = route('portal.invitation', $token);

        try {
            Mail::to($customer->email, $customer->name)->send(new CustomerPortalInvitation($customer, $url));
            return back()->with('success', 'Einladungslink wurde an ' . $customer->email . ' gesendet (gültig bis ' . $expires->format('d.m.Y H:i') . ').');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Portal-Einladung fehlgeschlagen: ' . $e->getMessage());
            return back()->with('error', 'E-Mail-Versand fehlgeschlagen. Einladungslink: ' . $url);
        }
    }

    public function adminDisablePortal(Customer $customer)
    {
        $customer->update(['portal_enabled' => false]);
        return back()->with('success', 'Portal-Zugang wurde deaktiviert.');
    }

    public function adminResetPortalPassword(Request $request, Customer $customer)
    {
        $data = $request->validate(['password' => 'required|string|min:8']);

        $customer->update([
            'portal_password'             => Hash::make($data['password']),
            'portal_must_change_password' => true,
        ]);

        return back()->with('success', 'Portal-Passwort wurde zurückgesetzt. Der Kunde muss sich ein neues Passwort setzen.');
    }

    public function adminResendInvitation(Customer $customer)
    {
        if (! $customer->email) {
            return back()->with('error', 'Der Kunde hat keine E-Mail-Adresse hinterlegt.');
        }

        $token   = Str::random(48);
        $expires = now()->addDays(7);

        $customer->update([
            'portal_invitation_token'      => $token,
            'portal_invitation_expires_at' => $expires,
            'portal_must_change_password'  => true,
        ]);

        $url = route('portal.invitation', $token);

        try {
            Mail::to($customer->email, $customer->name)->send(new CustomerPortalInvitation($customer, $url));
            return back()->with('success', 'Neuer Einladungslink wurde versendet (gültig bis ' . $expires->format('d.m.Y H:i') . ').');
        } catch (\Throwable $e) {
            return back()->with('error', 'E-Mail-Versand fehlgeschlagen. Link: ' . $url);
        }
    }
}
