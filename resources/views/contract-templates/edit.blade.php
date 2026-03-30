@extends('layouts.app')
@section('title', 'Vorlage bearbeiten · ' . $contractTemplate->name)

@section('content')
<form method="POST" action="{{ route('contract-templates.update', $contractTemplate) }}" class="space-y-6">
@csrf @method('PUT')

<div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
    <h3 class="font-semibold text-gray-700 border-b pb-2">Vorlage bearbeiten</h3>

    <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $contractTemplate->name) }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Typ</label>
            <select name="type" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @foreach(['privacy'=>'Datenschutz','handover'=>'Übergabe','maintenance'=>'Wartung','custom'=>'Sonstige'] as $v => $l)
                <option value="{{ $v }}" {{ old('type', $contractTemplate->type) === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kurzbeschreibung</label>
            <input type="text" name="description" value="{{ old('description', $contractTemplate->description) }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Inhalt (Markdown) <span class="text-red-500">*</span>
        </label>
        <textarea name="content" rows="24" required
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('content', $contractTemplate->content) }}</textarea>
    </div>
</div>

<div class="flex gap-3">
    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-2 rounded-lg text-sm">
        Änderungen speichern
    </button>
    <a href="{{ route('contract-templates.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2 rounded-lg text-sm">
        Abbrechen
    </a>
</div>
</form>
@endsection
