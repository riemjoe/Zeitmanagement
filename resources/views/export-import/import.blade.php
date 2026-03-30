@extends('layouts.app')
@section('title', 'Import')

@section('content')
<div class="max-w-xl">

    @if(session('success'))
    <div class="mb-4 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-sm text-emerald-700 flex items-center gap-2">
        <i class="ph-fill ph-check-circle text-emerald-500 text-lg shrink-0"></i>
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
        <p class="font-medium mb-1">Fehler beim Import:</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center gap-3 mb-4">
            <i class="ph-bold ph-download-simple text-indigo-500 text-2xl"></i>
            <div>
                <h3 class="font-semibold text-gray-800 text-lg">Daten importieren</h3>
                <p class="text-sm text-gray-500">JSON-Exportdatei einlesen</p>
            </div>
        </div>

        <form method="POST" action="{{ route('export-import.import.post') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Export-Datei (JSON)</label>
                <input type="file" name="file" accept=".json"
                       class="block w-full text-sm text-gray-700 border border-gray-300 rounded-lg px-3 py-2
                              file:mr-3 file:py-1 file:px-3 file:border-0 file:text-sm file:font-medium
                              file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer"
                       required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Import-Modus</label>
                <div class="space-y-2">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="radio" name="mode" value="merge" checked class="mt-0.5 text-indigo-600">
                        <div>
                            <span class="text-sm font-medium text-gray-800">Zusammenführen</span>
                            <p class="text-xs text-gray-500">Bestehende Datensätze bleiben erhalten. Neue Einträge werden hinzugefügt. Doppelte werden übersprungen.</p>
                        </div>
                    </label>
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="radio" name="mode" value="replace" class="mt-0.5 text-indigo-600">
                        <div>
                            <span class="text-sm font-medium text-gray-800">Ersetzen</span>
                            <p class="text-xs text-gray-500 flex items-center gap-1">
                                <i class="ph-bold ph-warning text-red-500"></i>
                                Alle bestehenden Daten werden gelöscht und durch die Import-Datei ersetzt.
                            </p>
                        </div>
                    </label>
                </div>
            </div>

            <button type="submit"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition-colors"
                    onclick="return confirm('Import wirklich starten? Bei Modus \"Ersetzen\" gehen alle bestehenden Daten verloren.')">
                <i class="ph-bold ph-upload-simple"></i> Import starten
            </button>
        </form>
    </div>

    <div class="mt-4 bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-700 flex items-start gap-2">
        <i class="ph-bold ph-warning text-amber-500 text-lg shrink-0 mt-0.5"></i>
        <span><strong>Hinweis:</strong> Erstelle vor einem Import mit dem Modus "Ersetzen"
        unbedingt vorher einen Export als Sicherungskopie.</span>
    </div>
</div>
@endsection
