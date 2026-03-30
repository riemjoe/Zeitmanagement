@extends('layouts.app')
@section('title', 'Export')

@section('content')
<div class="max-w-xl">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center gap-3 mb-4">
            <i class="ph-bold ph-export text-indigo-500 text-2xl"></i>
            <div>
                <h3 class="font-semibold text-gray-800 text-lg">Daten exportieren</h3>
                <p class="text-sm text-gray-500">Alle Daten als JSON-Datei herunterladen</p>
            </div>
        </div>

        <p class="text-sm text-gray-600 mb-6">
            Der Export enthält alle Kunden, Projekte, Arbeitskategorien, Zeiteinträge,
            Ausgaben und Rechnungen. Die Datei kann jederzeit wieder importiert werden.
        </p>

        <a href="{{ route('export-import.download') }}"
           class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition-colors">
            <i class="ph-bold ph-download-simple"></i> Export herunterladen
        </a>
    </div>

    <div class="mt-4 bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-700">
        <strong>Tipp:</strong> Erstelle regelmäßige Exporte als Datensicherung,
        bevor du größere Änderungen vornimmst.
    </div>
</div>
@endsection
