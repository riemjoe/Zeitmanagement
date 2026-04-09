<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;

class PublicSurveyController extends Controller
{
    /** Öffentliche Umfrage anzeigen */
    public function show(string $token)
    {
        $survey = Survey::where('token', $token)
            ->with(['template.sections.questions.options'])
            ->firstOrFail();

        if (!$survey->isAcceptingResponses()) {
            return view('surveys.public.closed', compact('survey'));
        }

        return view('surveys.public.show', compact('survey'));
    }

    /** Öffentliche Umfrage absenden */
    public function submit(Request $request, string $token)
    {
        $survey = Survey::where('token', $token)
            ->with(['template.sections.questions.options'])
            ->firstOrFail();

        if (!$survey->isAcceptingResponses()) {
            return back()->with('error', 'Diese Umfrage ist leider nicht mehr verfügbar.');
        }

        // Pflichtfelder validieren
        $rules = [
            'respondent_name'  => 'nullable|string|max:255',
            'respondent_email' => 'nullable|email|max:255',
        ];

        $allQuestions = $survey->template->sections->flatMap->questions;

        foreach ($allQuestions as $question) {
            $key = 'answers.' . $question->id;
            if ($question->is_required) {
                $rules[$key] = 'required';
            } else {
                $rules[$key] = 'nullable';
            }
        }

        $validated = $request->validate($rules);

        // Response anlegen
        $response = SurveyResponse::create([
            'survey_id'        => $survey->id,
            'respondent_name'  => $validated['respondent_name'] ?? null,
            'respondent_email' => $validated['respondent_email'] ?? null,
            'submitted_at'     => now(),
        ]);

        // Antworten speichern
        $answersData = $validated['answers'] ?? [];

        foreach ($allQuestions as $question) {
            $raw = $answersData[$question->id] ?? null;
            if ($raw === null || $raw === '') continue;

            $valueText   = null;
            $valueNumber = null;
            $score       = null;

            if ($question->type === 'text') {
                $valueText = (string) $raw;
                $score     = null;
            } elseif ($question->type === 'select') {
                // $raw ist die Option-ID
                $valueNumber = (float) $raw;
                $score       = $question->calculateScore($raw);
            } else {
                // range / number
                $valueNumber = (float) $raw;
                $score       = $question->calculateScore($valueNumber);
            }

            SurveyAnswer::create([
                'survey_response_id' => $response->id,
                'survey_question_id' => $question->id,
                'value_text'         => $valueText,
                'value_number'       => $valueNumber,
                'score'              => $score,
            ]);
        }

        // Score berechnen (braucht geladene answers + question-relations)
        $response->load(['answers.question']);
        $response->computeAndSaveScore();

        return view('surveys.public.thanks', compact('survey', 'response'));
    }
}
