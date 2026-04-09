<?php

namespace App\Http\Controllers;

use App\Models\SurveyTemplate;
use App\Models\SurveySection;
use App\Models\SurveyQuestion;
use App\Models\SurveyOption;
use Illuminate\Http\Request;

class SurveyTemplateController extends Controller
{
    public function index()
    {
        $templates = SurveyTemplate::withCount('surveys')->orderBy('name')->get();
        return view('surveys.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('surveys.templates.form', ['template' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'good_threshold' => 'required|integer|min:1|max:100',
            'bad_threshold'  => 'required|integer|min:0|max:99',
            'sections'       => 'required|array|min:1',
            'sections.*.title'       => 'required|string|max:255',
            'sections.*.description' => 'nullable|string',
            'sections.*.questions'   => 'nullable|array',
            'sections.*.questions.*.title'       => 'required|string|max:255',
            'sections.*.questions.*.description' => 'nullable|string',
            'sections.*.questions.*.type'        => 'required|in:range,number,text,select',
            'sections.*.questions.*.is_required' => 'nullable|boolean',
            'sections.*.questions.*.weight'      => 'required|integer|min:1|max:5',
            'sections.*.questions.*.settings'    => 'nullable|array',
            'sections.*.questions.*.options'     => 'nullable|array',
            'sections.*.questions.*.options.*.label' => 'required|string|max:255',
            'sections.*.questions.*.options.*.score' => 'required|integer|min:0|max:100',
        ]);

        $template = SurveyTemplate::create([
            'name'           => $data['name'],
            'description'    => $data['description'] ?? null,
            'good_threshold' => $data['good_threshold'],
            'bad_threshold'  => $data['bad_threshold'],
        ]);

        $this->saveSections($template, $data['sections']);

        return redirect()->route('survey-templates.index')
            ->with('success', 'Fragebogen wurde erstellt.');
    }

    public function edit(SurveyTemplate $surveyTemplate)
    {
        $surveyTemplate->load(['sections.questions.options']);
        return view('surveys.templates.form', ['template' => $surveyTemplate]);
    }

    public function update(Request $request, SurveyTemplate $surveyTemplate)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'good_threshold' => 'required|integer|min:1|max:100',
            'bad_threshold'  => 'required|integer|min:0|max:99',
            'sections'       => 'required|array|min:1',
            'sections.*.title'       => 'required|string|max:255',
            'sections.*.description' => 'nullable|string',
            'sections.*.questions'   => 'nullable|array',
            'sections.*.questions.*.title'       => 'required|string|max:255',
            'sections.*.questions.*.description' => 'nullable|string',
            'sections.*.questions.*.type'        => 'required|in:range,number,text,select',
            'sections.*.questions.*.is_required' => 'nullable|boolean',
            'sections.*.questions.*.weight'      => 'required|integer|min:1|max:5',
            'sections.*.questions.*.settings'    => 'nullable|array',
            'sections.*.questions.*.options'     => 'nullable|array',
            'sections.*.questions.*.options.*.label' => 'required|string|max:255',
            'sections.*.questions.*.options.*.score' => 'required|integer|min:0|max:100',
        ]);

        $surveyTemplate->update([
            'name'           => $data['name'],
            'description'    => $data['description'] ?? null,
            'good_threshold' => $data['good_threshold'],
            'bad_threshold'  => $data['bad_threshold'],
        ]);

        // Alle alten Sektionen/Fragen löschen und neu anlegen
        $surveyTemplate->sections()->delete();
        $this->saveSections($surveyTemplate, $data['sections']);

        return redirect()->route('survey-templates.index')
            ->with('success', 'Fragebogen wurde aktualisiert.');
    }

    public function destroy(SurveyTemplate $surveyTemplate)
    {
        if ($surveyTemplate->surveys()->exists()) {
            return back()->with('error', 'Fragebogen kann nicht gelöscht werden – es gibt zugehörige Umfragen.');
        }
        $surveyTemplate->delete();
        return redirect()->route('survey-templates.index')
            ->with('success', 'Fragebogen wurde gelöscht.');
    }

    // ── Privat ───────────────────────────────────────────────────────────────

    private function saveSections(SurveyTemplate $template, array $sections): void
    {
        foreach ($sections as $sPos => $sData) {
            $section = SurveySection::create([
                'survey_template_id' => $template->id,
                'title'              => $sData['title'],
                'description'        => $sData['description'] ?? null,
                'position'           => $sPos,
            ]);

            foreach ($sData['questions'] ?? [] as $qPos => $qData) {
                $settings = $qData['settings'] ?? [];

                // Einstellungen normalisieren
                if (in_array($qData['type'], ['range', 'number'])) {
                    $settings = [
                        'min'       => (float) ($settings['min']       ?? 0),
                        'max'       => (float) ($settings['max']       ?? 10),
                        'step'      => (float) ($settings['step']      ?? 1),
                        'good_from' => (float) ($settings['good_from'] ?? $settings['max'] ?? 10),
                        'bad_to'    => (float) ($settings['bad_to']    ?? $settings['min'] ?? 0),
                    ];
                } elseif ($qData['type'] === 'text') {
                    $settings = ['placeholder' => $settings['placeholder'] ?? ''];
                } else {
                    $settings = [];
                }

                $question = SurveyQuestion::create([
                    'survey_section_id'  => $section->id,
                    'survey_template_id' => $template->id,
                    'title'              => $qData['title'],
                    'description'        => $qData['description'] ?? null,
                    'type'               => $qData['type'],
                    'is_required'        => !empty($qData['is_required']),
                    'weight'             => (int) ($qData['weight'] ?? 1),
                    'position'           => $qPos,
                    'settings'           => $settings ?: null,
                ]);

                foreach ($qData['options'] ?? [] as $oPos => $oData) {
                    SurveyOption::create([
                        'survey_question_id' => $question->id,
                        'label'              => $oData['label'],
                        'score'              => (int) ($oData['score'] ?? 50),
                        'position'           => $oPos,
                    ]);
                }
            }
        }
    }
}
