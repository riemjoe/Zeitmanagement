<?php

namespace App\Http\Controllers;

use App\Services\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TwoFactorController extends Controller
{
    /** Setup-Seite: neues Secret generieren und QR-Code zeigen. */
    public function setup()
    {
        $user   = auth()->user();
        $secret = TotpService::generateSecret();
        session(['2fa_setup_secret' => $secret]);

        $qrUrl  = TotpService::getQrCodeUrl($secret, $user->email);
        $otpUrl = TotpService::getOtpAuthUrl($secret, $user->email);

        return view('auth.2fa-setup', compact('secret', 'qrUrl', 'otpUrl', 'user'));
    }

    /** Setup bestätigen: Code prüfen, 2FA aktivieren. */
    public function confirmSetup(Request $request)
    {
        $request->validate(['code' => 'required|string|max:6']);

        $secret = session('2fa_setup_secret');
        if (! $secret) {
            return back()->withErrors(['code' => 'Setup-Session abgelaufen. Bitte neu starten.']);
        }

        if (! TotpService::verify($secret, $request->code)) {
            return back()->withErrors(['code' => 'Der Code ist ungültig oder abgelaufen.']);
        }

        $backupCodes = TotpService::generateBackupCodes();
        $user = auth()->user();
        $user->forceFill([
            'two_factor_secret'       => $secret,
            'two_factor_enabled'      => true,
            'two_factor_backup_codes' => $backupCodes,
        ])->save();

        session()->forget('2fa_setup_secret');

        return redirect()->route('settings.edit')
            ->with('success', '2FA wurde aktiviert.')
            ->with('backup_codes', $backupCodes);
    }

    /** 2FA deaktivieren (Passwort-Bestätigung erforderlich). */
    public function disable(Request $request)
    {
        $request->validate(['password' => 'required|string']);

        $user = auth()->user();
        if (! Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Passwort ist falsch.']);
        }

        $user->forceFill([
            'two_factor_secret'       => null,
            'two_factor_enabled'      => false,
            'two_factor_backup_codes' => null,
        ])->save();

        return redirect()->route('settings.edit')->with('success', '2FA wurde deaktiviert.');
    }

    /** Neue Backup-Codes generieren. */
    public function regenerateBackupCodes(Request $request)
    {
        $request->validate(['password' => 'required|string']);

        $user = auth()->user();
        if (! Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Passwort ist falsch.']);
        }

        $codes = TotpService::generateBackupCodes();
        $user->forceFill(['two_factor_backup_codes' => $codes])->save();

        return redirect()->route('settings.edit')
            ->with('success', 'Neue Backup-Codes wurden generiert.')
            ->with('backup_codes', $codes);
    }

    /** 2FA-Verify-Seite anzeigen (nach dem Login). */
    public function showVerify()
    {
        if (! session('2fa_user_id')) {
            return redirect()->route('login');
        }
        return view('auth.2fa-verify');
    }

    /** 2FA-Code beim Login prüfen. */
    public function verify(Request $request)
    {
        $userId = session('2fa_user_id');
        if (! $userId) {
            return redirect()->route('login');
        }

        $request->validate(['code' => 'required|string']);

        $user   = \App\Models\User::findOrFail($userId);
        $code   = preg_replace('/\s/', '', $request->code);

        // Prüfen: TOTP
        if (TotpService::verify($user->two_factor_secret, $code)) {
            session()->forget('2fa_user_id');
            \Illuminate\Support\Facades\Auth::login($user, session()->pull('2fa_remember', false));
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        // Prüfen: Backup-Code
        $backupCodes = $user->two_factor_backup_codes ?? [];
        foreach ($backupCodes as $i => $bc) {
            if (hash_equals(strtoupper($bc), strtoupper($code))) {
                // Backup-Code einmal verwenden → löschen
                unset($backupCodes[$i]);
                $user->forceFill(['two_factor_backup_codes' => array_values($backupCodes)])->save();

                session()->forget('2fa_user_id');
                \Illuminate\Support\Facades\Auth::login($user, session()->pull('2fa_remember', false));
                $request->session()->regenerate();
                return redirect()->intended(route('dashboard'))
                    ->with('warning', 'Backup-Code verwendet. Noch ' . count($backupCodes) . ' Backup-Codes verfügbar.');
            }
        }

        return back()->withErrors(['code' => 'Ungültiger Code. Bitte versuche es erneut.']);
    }
}
