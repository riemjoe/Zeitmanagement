<?php

namespace App\Http\Controllers;

use App\Models\Webhook;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    // ── Liste ─────────────────────────────────────────────────────────────────

    public function index()
    {
        $webhooks = Webhook::withCount('automations')
            ->orderByDesc('updated_at')
            ->get();

        return view('webhooks.index', compact('webhooks'));
    }

    // ── Erstellen ─────────────────────────────────────────────────────────────

    public function create()
    {
        $webhook = new Webhook(['is_active' => true]);
        return view('webhooks.edit', compact('webhook'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $webhook = Webhook::create($data);

        return redirect()->route('webhooks.index')
            ->with('success', "Webhook «{$webhook->name}» wurde erstellt.");
    }

    // ── Bearbeiten ────────────────────────────────────────────────────────────

    public function edit(Webhook $webhook)
    {
        $webhook->loadCount('automations');
        $automations = $webhook->automations()->orderBy('name')->get();

        return view('webhooks.edit', compact('webhook', 'automations'));
    }

    public function update(Request $request, Webhook $webhook)
    {
        $data = $this->validated($request, $webhook);
        $webhook->update($data);

        return redirect()->route('webhooks.index')
            ->with('success', "Webhook «{$webhook->name}» wurde gespeichert.");
    }

    // ── Löschen ───────────────────────────────────────────────────────────────

    public function destroy(Webhook $webhook)
    {
        $name = $webhook->name;

        // Automationen trennen (webhook_id auf null setzen)
        $webhook->automations()->update(['webhook_id' => null]);

        $webhook->delete();

        return redirect()->route('webhooks.index')
            ->with('success', "Webhook «{$name}» wurde gelöscht.");
    }

    // ── Token neu generieren ──────────────────────────────────────────────────

    public function regenerateToken(Webhook $webhook)
    {
        $webhook->update(['token' => \Illuminate\Support\Str::random(48)]);

        return back()->with('success', 'Token wurde neu generiert. Bitte aktualisiere die URL in allen externen Systemen.');
    }

    // ── Hilfsmethoden ────────────────────────────────────────────────────────

    private function validated(Request $request, ?Webhook $webhook = null): array
    {
        $rules = [
            'name'        => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'secret'      => 'nullable|string|max:255',
            'is_active'   => 'boolean',
        ];

        return $request->validate($rules);
    }
}
