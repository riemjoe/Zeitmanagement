@extends('layouts.app')
@section('title', 'Arbeitskategorien')

@section('content')
<div class="grid grid-cols-2 gap-6">

    {{-- Liste --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Farbe</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Name</th>
                    <th class="text-center px-5 py-3 font-semibold text-gray-600">Einträge</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100" x-data="editCategory()">
                @forelse($categories as $cat)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <span class="inline-block w-5 h-5 rounded-full border border-gray-200"
                              style="background-color: {{ $cat->color }}"></span>
                    </td>
                    <td class="px-5 py-3 font-medium">{{ $cat->name }}</td>
                    <td class="px-5 py-3 text-center text-gray-500">{{ $cat->time_entries_count }}</td>
                    <td class="px-5 py-3 text-right space-x-2">
                        <button @click="load({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ $cat->color }}')"
                                class="text-gray-400 hover:text-indigo-600 text-xs">Bearbeiten</button>
                        <form method="POST" action="{{ route('work-categories.destroy', $cat) }}" class="inline"
                              onsubmit="return confirm('Kategorie löschen?')">
                            @csrf @method('DELETE')
                            <button class="text-gray-400 hover:text-red-600 text-xs">Löschen</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-5 py-8 text-center text-gray-400">Noch keine Kategorien.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Formular (Anlegen / Bearbeiten) --}}
    <div x-data="editCategory()" class="space-y-4">

        {{-- Neue Kategorie --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6" x-show="!editId" x-cloak>
            <h3 class="font-semibold text-gray-700 mb-4">Neue Kategorie</h3>
            <form method="POST" action="{{ route('work-categories.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Farbe</label>
                    <input type="color" name="color" value="#6366f1"
                           class="h-10 w-full rounded-lg border border-gray-300 cursor-pointer">
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 rounded-lg text-sm">
                    Kategorie anlegen
                </button>
            </form>
        </div>

        {{-- Kategorie bearbeiten --}}
        <template x-if="editId">
            <div class="bg-white rounded-xl border border-indigo-300 p-6">
                <h3 class="font-semibold text-gray-700 mb-4">Kategorie bearbeiten</h3>
                <form :action="`/work-categories/${editId}`" method="POST" class="space-y-3">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" x-model="editName" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Farbe</label>
                        <input type="color" name="color" x-model="editColor"
                               class="h-10 w-full rounded-lg border border-gray-300 cursor-pointer">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 rounded-lg text-sm">
                            Speichern
                        </button>
                        <button type="button" @click="reset()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 rounded-lg text-sm">
                            Abbrechen
                        </button>
                    </div>
                </form>
            </div>
        </template>
    </div>
</div>

@push('scripts')
<script>
function editCategory() {
    return {
        editId: null,
        editName: '',
        editColor: '#6366f1',
        load(id, name, color) { this.editId = id; this.editName = name; this.editColor = color; },
        reset() { this.editId = null; this.editName = ''; this.editColor = '#6366f1'; }
    }
}
</script>
@endpush
@endsection
