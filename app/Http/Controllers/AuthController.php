<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Login-Seite anzeigen.
     * Falls noch kein Nutzer existiert → Ersteinrichtung anzeigen.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $hasUsers = User::exists();
        return view('auth.login', compact('hasUsers'));
    }

    /**
     * Login / Ersteinrichtung verarbeiten.
     */
    public function login(Request $request)
    {
        // ── Ersteinrichtung: noch kein Nutzer vorhanden ──────────────────
        if (! User::exists()) {
            $data = $request->validate([
                'name'                  => 'required|string|max:255',
                'email'                 => 'required|email|max:255',
                'password'              => 'required|min:8|confirmed',
            ], [
                'name.required'             => 'Bitte deinen Namen eingeben.',
                'email.required'            => 'Bitte eine E-Mail-Adresse eingeben.',
                'email.email'               => 'Bitte eine gültige E-Mail-Adresse eingeben.',
                'password.required'         => 'Bitte ein Passwort eingeben.',
                'password.min'              => 'Das Passwort muss mindestens 8 Zeichen lang sein.',
                'password.confirmed'        => 'Die Passwörter stimmen nicht überein.',
            ]);

            $admin = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'role'     => 'admin',
                'is_active'=> true,
            ]);

            Auth::login($admin, remember: true);
            $request->session()->regenerate();

            return redirect()->route('dashboard')
                ->with('success', 'Willkommen! Admin-Konto wurde eingerichtet.');
        }

        // ── Normaler Login ───────────────────────────────────────────────
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'email.required'    => 'Bitte eine E-Mail-Adresse eingeben.',
            'email.email'       => 'Bitte eine gültige E-Mail-Adresse eingeben.',
            'password.required' => 'Bitte das Passwort eingeben.',
        ]);

        // Konto aktiv?
        $user = User::where('email', $credentials['email'])->first();
        if ($user && ! $user->is_active) {
            return back()->withErrors([
                'email' => 'Dieses Konto wurde deaktiviert.',
            ])->onlyInput('email');
        }

        if (! Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'E-Mail oder Passwort ist falsch.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();
        return redirect()->intended(route('dashboard'));
    }

    /**
     * Abmelden.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
