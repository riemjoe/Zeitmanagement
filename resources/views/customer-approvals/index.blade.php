@extends('layouts.app')
@section('title', 'Kundenfreigaben')

@section('header-actions')
    <a href="{{ route('customer-approvals.create') }}"
       class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        + Freigabeanfrage
    </a>
@endsection

@section('content')

{{-- ── Status-Filter ────────────────────────────────────────────────────── --}}
@php
    $tabs = [
        ''         => 'Alle (' . $counts['all'] . ')',
        'pending'  => 'Ausstehend (' . $counts['pending'] . ')',
        'approved' => 'Erlaubt (' . $counts['approved'] . ')',
        'rejected' => 'Abgelehnt (' . $counts['rejected'] . ')',
    ];
@endphp
<div class="flex items-center gap-1.5 mb-5 flex-wrap">
    @foreach($tabs as $value => $label)
    <a href="{{ route('customer-approvals.index', $value ? ['status' => $value] : []) }}"
       class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors
              {{ ($status ?? '') === $value ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
        {{ $label }}
    </a>
    @endforeach
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-xs text-gray-500 uppercase tracking-wide">
                <th class="px-5 py-3 text-left">Titel</th>
                <th class="px-5 py-3 text-left">Kunde</th>
                <th class="px-5 py-3 text-left">Projekt</th>
                <th class="px-5 py-3 text-left">Angefragt am</th>
                <th class="px-5 py-3 text-left">Gültig bis</th>
                <th class="px-5 py-3 text-center">Status</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($approvals as $approval)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3">
                    <a href="{{ route('customer-approvals.show', $approval) }}" class="font-medium text-gray-800 hover:text-indigo-600">
                        {{ $approval->title }}
                    </a>
                </td>
                <td class="px-5 py-3 text-gray-600">{{ $approval->customer->name }}</td>
                <td class="px-5 py-3 text-gray-500">{{ $approval->project->name ?? '–' }}</td>
                <td class="px-5 py-3 text-gray-500">{{ $approval->created_at->format('d.m.Y') }}</td>
                <td class="px-5 py-3 text-gray-500">
                    {{ $approval->expires_at ? $approval->expires_at->format('d.m.Y') : 'unbegrenzt' }}
                </td>
                <td class="px-5 py-3 text-center">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $approval->statusColorClasses() }}">
                        {{ $approval->statusLabelGerman() }}
                    </span>
                </td>
                <td class="px-5 py-3 text-right">
                    <a href="{{ route('customer-approvals.show', $approval) }}" class="text-gray-400 hover:text-indigo-600 text-xs">Ansehen</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                    Noch keine Kundenfreigaben vorhanden.
                    <a href="{{ route('customer-approvals.create') }}" class="text-indigo-600 hover:underline ml-1">Erste Freigabeanfrage erstellen →</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $approvals->links() }}
</div>

@endsection
