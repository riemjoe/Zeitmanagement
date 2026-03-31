@extends('layouts.app')
@section('title', 'Team')

@section('header-actions')
<a href="{{ route('team.create') }}"
   class="flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-3 py-1.5 rounded-lg transition-colors">
    <i class="ph-bold ph-plus text-sm"></i>
    <span class="hidden sm:inline">Mitglied hinzufügen</span>
</a>
@endsection

@section('content')
<div class="max-w-3xl">

    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition
         class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 flex items-center justify-between mb-4 gap-3">
        <span class="flex items-center gap-2 text-sm">
            <i class="ph-fill ph-check-circle text-green-500 text-lg shrink-0"></i>
            {{ session('success') }}
        </span>
        <button @click="show = false" class="text-green-600 hover:text-green-900 shrink-0">
            <i class="ph-bold ph-x text-base"></i>
        </button>
    </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">E-Mail</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Rolle</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($members as $member)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2.5">
                            <span class="flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold
                                         {{ $member->isAdmin() ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ strtoupper(mb_substr($member->name, 0, 1)) }}
                            </span>
                            <div>
                                <p class="font-medium text-gray-800">
                                    {{ $member->name }}
                                    @if($member->id === auth()->id())
                                    <span class="ml-1 text-xs text-gray-400">(ich)</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-gray-600">{{ $member->email }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                     {{ $member->isAdmin()
                                          ? 'bg-indigo-100 text-indigo-700'
                                          : 'bg-gray-100 text-gray-600' }}">
                            {{ $member->roleName() }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        @if($member->is_active)
                        <span class="inline-flex items-center gap-1 text-xs text-green-700">
                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> Aktiv
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 text-xs text-gray-400">
                            <span class="w-1.5 h-1.5 bg-gray-300 rounded-full"></span> Inaktiv
                        </span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('team.edit', $member) }}"
                           class="text-gray-400 hover:text-indigo-600 text-xs font-medium transition-colors">
                            Bearbeiten
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-gray-400 text-sm">
                        Keine Teammitglieder vorhanden.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
