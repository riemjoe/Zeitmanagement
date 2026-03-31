@extends('layouts.app')
@section('title', 'Teammitglied bearbeiten')

@section('content')
<div class="max-w-lg">

    <div class="mb-5">
        <a href="{{ route('team.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 transition-colors">
            <i class="ph-bold ph-arrow-left text-sm"></i> Zurück zum Team
        </a>
    </div>

    @if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">
        <ul class="space-y-1">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
        <div>
            <h2 class="text-base font-semibold text-gray-800">{{ $member->name }}</h2>
            <p class="text-xs text-gray-400">Mitglied seit {{ $member->created_at->format('d.m.Y') }}</p>
        </div>

        <form method="POST" action="{{ route('team.update', $member) }}" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $member->name) }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">E-Mail <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email', $member->email) }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Rolle <span class="text-red-500">*</span></label>
                <select name="role" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="member" {{ old('role', $member->role) === 'member' ? 'selected' : '' }}>
                        Mitglied
                    </option>
                    <option value="admin" {{ old('role', $member->role) === 'admin' ? 'selected' : '' }}>
                        Admin
                    </option>
                </select>
            </div>

            <div x-data="{ show: false }">
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-sm font-medium text-gray-700">Neues Passwort</label>
                    <button type="button" @click="show = !show"
                            class="text-xs text-indigo-600 hover:text-indigo-700">
                        <span x-text="show ? 'Ausblenden' : 'Passwort ändern'"></span>
                    </button>
                </div>
                <div x-show="show" class="space-y-3">
                    <input type="password" name="password"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="Mindestens 8 Zeichen">
                    <input type="password" name="password_confirmation"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="Passwort bestätigen">
                </div>
                <p class="text-xs text-gray-400 mt-1">Leer lassen, um das Passwort nicht zu ändern.</p>
            </div>

            {{-- Aktiv-Status --}}
            <div class="pt-2 border-t border-gray-100">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $member->is_active) ? 'checked' : '' }}
                           class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <span class="text-sm font-medium text-gray-700">Konto aktiv</span>
                        <p class="text-xs text-gray-400">Deaktivierte Konten können sich nicht mehr einloggen.</p>
                    </div>
                </label>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 rounded-lg text-sm transition-colors">
                    Änderungen speichern
                </button>
                <a href="{{ route('team.index') }}"
                   class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                    Abbrechen
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
