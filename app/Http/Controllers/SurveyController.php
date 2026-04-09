<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\SurveyTemplate;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function index()
    {
        $surveys = Survey::with(['template', 'customer'])
            ->withCount('responses')
            ->orderByDesc('created_at')
            ->get();
        return view('surveys.index', compact('surveys'));
    }

    public function create()
    {
        $templates = SurveyTemplate::orderBy('name')->get();
        $customers = Customer::orderBy('name')->get();
        return view('surveys.create', compact('templates', 'customers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'survey_template_id' => 'required|exists:survey_templates,id',
            'customer_id'        => 'nullable|exists:customers,id',
            'title'              => 'required|string|max:255',
            'max_responses'      => 'nullable|integer|min:1',
            'expires_at'         => 'nullable|date|after:now',
            'is_active'          => 'nullable|boolean',
        ]);

        Survey::create([
            'survey_template_id' => $data['survey_template_id'],
            'customer_id'        => $data['customer_id'] ?? null,
            'title'              => $data['title'],
            'token'              => Survey::generateToken(),
            'max_responses'      => $data['max_responses'] ?? null,
            'expires_at'         => $data['expires_at'] ?? null,
            'is_active'          => $request->boolean('is_active', true),
        ]);

        return redirect()->route('surveys.index')
            ->with('success', 'Umfrage wurde erstellt.');
    }

    public function show(Survey $survey)
    {
        $survey->load([
            'template.sections.questions.options',
            'responses.answers.question',
            'customer',
        ]);

        // Globale Statistiken für diesen Survey
        $responses    = $survey->responses;
        $totalCount   = $responses->count();
        $scoredCount  = $responses->whereNotNull('total_score')->count();
        $avgScore     = $scoredCount ? round($responses->whereNotNull('total_score')->avg('total_score'), 1) : null;
        $verdictCounts = [
            'good'    => $responses->where('verdict', 'good')->count(),
            'neutral' => $responses->where('verdict', 'neutral')->count(),
            'bad'     => $responses->where('verdict', 'bad')->count(),
        ];

        // Durchschnittlicher Score je Frage
        $questionStats = [];
        foreach ($survey->template->questions as $question) {
            if ($question->type === 'text') continue;
            $qAnswers = $responses->flatMap->answers->where('survey_question_id', $question->id);
            $scores   = $qAnswers->whereNotNull('score')->pluck('score');
            $questionStats[$question->id] = [
                'question' => $question,
                'avg'      => $scores->isEmpty() ? null : round($scores->avg(), 1),
                'count'    => $scores->count(),
            ];
        }

        return view('surveys.show', compact(
            'survey', 'totalCount', 'scoredCount', 'avgScore', 'verdictCounts', 'questionStats'
        ));
    }

    public function edit(Survey $survey)
    {
        $customers = Customer::orderBy('name')->get();
        return view('surveys.edit', compact('survey', 'customers'));
    }

    public function update(Request $request, Survey $survey)
    {
        $data = $request->validate([
            'customer_id'   => 'nullable|exists:customers,id',
            'title'         => 'required|string|max:255',
            'max_responses' => 'nullable|integer|min:1',
            'expires_at'    => 'nullable|date',
            'is_active'     => 'nullable|boolean',
        ]);

        $survey->update([
            'customer_id'   => $data['customer_id'] ?? null,
            'title'         => $data['title'],
            'max_responses' => $data['max_responses'] ?? null,
            'expires_at'    => $data['expires_at'] ?? null,
            'is_active'     => $request->boolean('is_active'),
        ]);

        return redirect()->route('surveys.show', $survey)
            ->with('success', 'Umfrage wurde aktualisiert.');
    }

    public function destroy(Survey $survey)
    {
        $survey->delete();
        return redirect()->route('surveys.index')
            ->with('success', 'Umfrage wurde gelöscht.');
    }

    /** Admin: Einzelne Antwort ansehen */
    public function showResponse(Survey $survey, SurveyResponse $response)
    {
        $response->load(['answers.question.options', 'survey.template.sections.questions']);
        return view('surveys.response', compact('survey', 'response'));
    }

    /** Admin: Einzelne Antwort löschen */
    public function destroyResponse(Survey $survey, SurveyResponse $response)
    {
        $response->delete();
        return redirect()->route('surveys.show', $survey)
            ->with('success', 'Antwort wurde gelöscht.');
    }

    /** Globale Auswertung über alle Umfragen */
    public function globalStats()
    {
        $surveys = Survey::with(['template', 'customer', 'responses'])->get();

        $totalResponses = $surveys->sum(fn ($s) => $s->responses->count());
        $allScored      = $surveys->flatMap->responses->whereNotNull('total_score');
        $globalAvg      = $allScored->isNotEmpty() ? round($allScored->avg('total_score'), 1) : null;
        $globalVerdicts = [
            'good'    => $surveys->flatMap->responses->where('verdict', 'good')->count(),
            'neutral' => $surveys->flatMap->responses->where('verdict', 'neutral')->count(),
            'bad'     => $surveys->flatMap->responses->where('verdict', 'bad')->count(),
        ];

        return view('surveys.global', compact('surveys', 'totalResponses', 'globalAvg', 'globalVerdicts'));
    }
}
