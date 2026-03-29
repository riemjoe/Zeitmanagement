@extends('layouts.app')
@section('title', 'Kunden')

@section('header-actions')
    <a href="{{ route('customers.create') }}"
       class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        + Neuer Kunde
    </a>
@endsection

@section('content')
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Name</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Adresse</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Kontakt</th>
                <th class="text-center px-5 py-3 font-semibold text-gray-600">Projekte</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($customers as $customer)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3 font-medium">
                    <a href="{{ route('customers.show', $customer) }}"
                       class="text-indigo-600 hover:underline">{{ $customer->name }}</a>
                </td>
                <td class="px-5 py-3 text-gray-500">{{ $customer->address ?: '–' }}</td>
                <td class="px-5 py-3 text-gray-500">
                    {{ $customer->email ?: '' }}
                    @if($customer->email && $customer->phone) · @endif
                    {{ $customer->phone ?: '' }}
                    @if(!$customer->email && !$customer->phone) – @endif
                </td>
                <td class="px-5 py-3 text-center">
                    <span class="bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full text-xs font-medium">
                        {{ $customer->projects_count }}
                    </span>
                </td>
                <td class="px-5 py-3 text-right space-x-2">
                    <a href="{{ route('customers.edit', $customer) }}"
                       class="text-gray-400 hover:text-indigo-600 text-xs">Bearbeiten</a>
                    <form method="POST" action="{{ route('customers.destroy', $customer) }}" class="inline"
                          onsubmit="return confirm('Kunden wirklich löschen?')">
                        @csrf @method('DELETE')
                        <button class="text-gray-400 hover:text-red-600 text-xs">Löschen</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-5 py-10 text-center text-gray-400">
                    Noch keine Kunden vorhanden. <a href="{{ route('customers.create') }}" class="text-indigo-600 hover:underline">Ersten Kunden anlegen →</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
