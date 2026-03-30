<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session('authenticated')) {
            return redirect()->route('dashboard');
        }

        $hasPassword = !empty(Setting::get('password_hash'));
        return view('auth.login', compact('hasPassword'));
    }

    public function login(Request $request)
    {
        $passwordHash = Setting::get('password_hash');

        // Noch kein Passwort gesetzt → erstes Einrichten
        if (empty($passwordHash)) {
            $request->validate([
                'password'              => 'required|min:6',
                'password_confirmation' => 'required|same:password',
            ], [
                'password.required'              => 'Bitte ein Passwort eingeben.',
                'password.min'                   => 'Das Passwort muss mindestens 6 Zeichen lang sein.',
                'password_confirmation.required' => 'Bitte das Passwort bestätigen.',
                'password_confirmation.same'     => 'Die Passwörter stimmen nicht überein.',
            ]);

            Setting::set('password_hash', Hash::make($request->password));
            session(['authenticated' => true]);
            return redirect()->route('dashboard')->with('success', 'Passwort wurde eingerichtet. Willkommen!');
        }

        // Normaler Login
        $request->validate([
            'password' => 'required',
        ], [
            'password.required' => 'Bitte das Passwort eingeben.',
        ]);

        if (!Hash::check($request->password, $passwordHash)) {
            return back()->withErrors(['password' => 'Das Passwort ist falsch.']);
        }

        session(['authenticated' => true]);
        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        $request->session()->forget('authenticated');
        return redirect()->route('login');
    }
}
