<?php

namespace App\Http\Controllers;

use App\Mail\CustomerApprovalDecided;
use App\Models\CustomerApproval;
use App\Models\EmailLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CustomerApprovalPublicController extends Controller
{
    /** Öffentliche Freigabeanfrage anzeigen (kein Login erforderlich). */
    public function show(string $token)
    {
        $approval = CustomerApproval::where('token', $token)
            ->with(['customer', 'project'])
            ->firstOrFail();

        if ($approval->status !== 'pending') {
            return view('customer-approvals.public.responded', compact('approval'));
        }

        if ($approval->isExpired()) {
            return view('customer-approvals.public.expired', compact('approval'));
        }

        return view('customer-approvals.public.show', compact('approval'));
    }

    /** Kunde übermittelt seine Entscheidung (Erlauben/Ablehnen). */
    public function decide(Request $request, string $token)
    {
        $approval = CustomerApproval::where('token', $token)
            ->with(['customer', 'project'])
            ->firstOrFail();

        if (! $approval->isOpen()) {
            return redirect()->route('customer-approval.show', $token);
        }

        $data = $request->validate([
            'decision' => 'required|in:approve,reject',
            'comment'  => 'nullable|string|max:2000',
        ]);

        $approval->update([
            'status'           => $data['decision'] === 'approve' ? 'approved' : 'rejected',
            'responded_at'     => now(),
            'response_comment' => $data['comment'] ?? null,
            'responder_ip'     => $request->ip(),
        ]);

        $this->notifyBusiness($approval);

        return redirect()->route('customer-approval.show', $token);
    }

    /** Geschäftsmail über die Kundenentscheidung informieren. */
    private function notifyBusiness(CustomerApproval $approval): void
    {
        $businessEmail = Setting::get('company_email');

        if (! $businessEmail) {
            return;
        }

        $subject = ($approval->status === 'approved' ? '✅ Freigabe erteilt: ' : '❌ Freigabe abgelehnt: ') . $approval->title;

        try {
            Mail::to($businessEmail)->send(new CustomerApprovalDecided($approval));
            EmailLog::record('customer_approval_decision', $businessEmail, $subject, 'sent');
        } catch (\Throwable $e) {
            Log::error('Freigabe-Entscheidung-Benachrichtigung fehlgeschlagen: ' . $e->getMessage());
            EmailLog::record('customer_approval_decision', $businessEmail, $subject, 'failed', $e->getMessage());
        }
    }
}
