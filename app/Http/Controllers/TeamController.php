<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    /** Liste aller Teammitglieder */
    public function index()
    {
        $members = User::orderBy('name')->get();
        return view('team.index', compact('members'));
    }

    /** Formular: neues Teammitglied */
    public function create()
    {
        return view('team.create');
    }

    /** Neues Teammitglied speichern */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role'     => 'required|in:admin,member',
        ], [
            'name.required'      => 'Bitte einen Namen eingeben.',
            'email.required'     => 'Bitte eine E-Mail-Adresse eingeben.',
            'email.unique'       => 'Diese E-Mail-Adresse wird bereits verwendet.',
            'password.required'  => 'Bitte ein Passwort eingeben.',
            'password.min'       => 'Das Passwort muss mindestens 8 Zeichen lang sein.',
            'password.confirmed' => 'Die Passwörter stimmen nicht überein.',
            'role.required'      => 'Bitte eine Rolle auswählen.',
            'role.in'            => 'Ungültige Rolle.',
        ]);

        User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role'      => $data['role'],
            'is_active' => true,
        ]);

        return redirect()->route('team.index')
            ->with('success', 'Teammitglied "' . $data['name'] . '" wurde angelegt.');
    }

    /** Formular: Teammitglied bearbeiten */
    public function edit(User $team)
    {
        return view('team.edit', ['member' => $team]);
    }

    /** Änderungen speichern */
    public function update(Request $request, User $team)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', 'max:255', Rule::unique('users')->ignore($team->id)],
            'role'     => 'required|in:admin,member',
            'is_active'=> 'boolean',
            'password' => 'nullable|min:8|confirmed',
        ], [
            'name.required'  => 'Bitte einen Namen eingeben.',
            'email.required' => 'Bitte eine E-Mail-Adresse eingeben.',
            'email.unique'   => 'Diese E-Mail-Adresse wird bereits verwendet.',
            'role.required'  => 'Bitte eine Rolle auswählen.',
            'password.min'   => 'Das neue Passwort muss mindestens 8 Zeichen lang sein.',
            'password.confirmed' => 'Die Passwörter stimmen nicht überein.',
        ]);

        // Letzten Admin schützen: kann nicht degradiert werden
        if ($team->isAdmin() && $data['role'] !== 'admin') {
            $adminCount = User::where('role', 'admin')->where('is_active', true)->count();
            if ($adminCount <= 1) {
                return back()->withErrors(['role' => 'Es muss mindestens ein aktiver Administrator vorhanden sein.']);
            }
        }

        $update = [
            'name'      => $data['name'],
            'email'     => $data['email'],
            'role'      => $data['role'],
            'is_active' => $request->boolean('is_active'),
        ];

        if (! empty($data['password'])) {
            $update['password'] = Hash::make($data['password']);
        }

        $team->update($update);

        // Wurde der eigene Account deaktiviert? Dann ausloggen.
        if (Auth::id() === $team->id && ! $team->is_active) {
            Auth::logout();
            return redirect()->route('login')->with('success', 'Konto wurde deaktiviert.');
        }

        return redirect()->route('team.index')
            ->with('success', 'Teammitglied "' . $team->name . '" wurde aktualisiert.');
    }
}
