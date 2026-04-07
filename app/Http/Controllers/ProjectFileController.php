<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectFileController extends Controller
{
    /** Datei hochladen */
    public function store(Request $request, Project $project)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // max 50 MB
        ]);

        $uploaded = $request->file('file');

        $path = $uploaded->store('project-files/' . $project->id, 'local');

        ProjectFile::create([
            'project_id'    => $project->id,
            'original_name' => $uploaded->getClientOriginalName(),
            'path'          => $path,
            'size'          => $uploaded->getSize(),
            'mime_type'     => $uploaded->getMimeType(),
        ]);

        return back()->with('success', 'Datei wurde hochgeladen.');
    }

    /** Datei herunterladen */
    public function download(ProjectFile $file)
    {
        if (! Storage::disk('local')->exists($file->path)) {
            abort(404, 'Datei nicht gefunden.');
        }

        return Storage::disk('local')->download($file->path, $file->original_name);
    }

    /** Datei löschen */
    public function destroy(ProjectFile $file)
    {
        Storage::disk('local')->delete($file->path);
        $file->delete();

        return back()->with('success', 'Datei wurde gelöscht.');
    }
}
