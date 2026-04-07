@extends('layouts.app')
@section('title', 'Kunden')

@section('header-actions')
    <a href="{{ route('customers.create') }}"
       class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        + Neuer Kunde
    </a>
@endsection

@section('content')

{{-- Flash-Messages --}}
@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700 flex items-center gap-2">
    <i class="ph-bold ph-check-circle shrink-0"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 flex items-center gap-2">
    <i class="ph-bold ph-warning-circle shrink-0"></i> {{ session('error') }}
</div>
@endif

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden" x-data="{ msgModal: false, msgCustomer: null, msgCustomerId: null }">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Kundennr.</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Name</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Ansprechpartner:in</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Kontakt</th>
                <th class="text-center px-5 py-3 font-semibold text-gray-600">Projekte</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($customers as $customer)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3 text-gray-400 font-mono text-xs">
                    {{ $customer->customer_number ?? '–' }}
                </td>
                <td class="px-5 py-3 font-medium">
                    <a href="{{ route('customers.show', $customer) }}"
                       class="text-indigo-600 hover:underline">{{ $customer->name }}</a>
                </td>
                <td class="px-5 py-3 text-gray-500">{{ $customer->contact_person ?: '–' }}</td>
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
                <td class="px-5 py-3 text-right space-x-2 whitespace-nowrap">
                    @if($customer->email)
                    <button
                        @click="msgModal = true; msgCustomer = '{{ addslashes($customer->name) }}'; msgCustomerId = {{ $customer->id }}"
                        class="inline-flex items-center gap-1 text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-600 px-2 py-1 rounded-lg transition-colors">
                        <i class="ph-bold ph-envelope text-xs"></i> Nachricht schreiben
                    </button>
                    @endif
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
                <td colspan="6" class="px-5 py-10 text-center text-gray-400">
                    Noch keine Kunden vorhanden. <a href="{{ route('customers.create') }}" class="text-indigo-600 hover:underline">Ersten Kunden anlegen →</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ── Nachricht-Modal ─────────────────────────────────────────────── --}}
    <template x-teleport="body">
        <div x-show="msgModal" x-cloak
             @keydown.escape.window="msgModal = false"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
             style="background:rgba(0,0,0,.5)">

            <div @click.stop
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-y-auto max-h-[90vh]">

                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                        <i class="ph-bold ph-envelope text-indigo-500"></i>
                        Nachricht an <span class="text-indigo-600 ml-1" x-text="msgCustomer"></span>
                    </h2>
                    <button @click="msgModal = false" class="text-gray-400 hover:text-gray-700 transition-colors p-1 rounded">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>

                <form method="POST"
                      :action="'/admin/customers/' + msgCustomerId + '/send-message'"
                      class="px-6 py-5 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Betreff <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="subject" required
                            placeholder="Betreff…"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Ticketnummer
                        </label>
                        <input type="text" name="ticket_number"
                            placeholder="Ticketnummer…"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nachricht <span class="text-red-500">*</span>
                            <span class="font-normal text-gray-400">(wird in das Template als <code class="bg-gray-100 px-1 rounded text-xs">@{{client_message}}</code> eingefügt)</span>
                        </label>
                        <textarea name="client_message" rows="6" required
                                  placeholder="Ihre Nachricht an den Kunden…"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" @click="msgModal = false"
                                class="px-4 py-2 text-sm text-gray-500 hover:text-gray-800 transition-colors">
                            Abbrechen
                        </button>
                        <button type="submit"
                                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-1.5">
                            <i class="ph-bold ph-paper-plane-tilt"></i> Senden
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
@endsection
