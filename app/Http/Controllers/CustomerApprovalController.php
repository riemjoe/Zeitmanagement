<?php

namespace App\Http\Controllers;

use App\Mail\CustomerApprovalRequest as CustomerApprovalRequestMail;
use App\Models\Customer;
use App\Models\CustomerApproval;
use App\Models\EmailLog;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CustomerApprovalController extends Controller
{
    /** Übersicht aller Kundenfreigaben (mit optionalem Status-Filter). */
    public function index(Request $request)
    {
        $status = $request->get('status');

        $approvals = CustomerApproval::with(['customer', 'project'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'all'      => CustomerApproval::count(),
            'pending'  => CustomerApproval::where('status', 'pending')->count(),
            'approved' => CustomerApproval::where('status', 'approved')->count(),
            'rejected' => CustomerApproval::where('status', 'rejected')->count(),
        ];

        return view('customer-approvals.index', compact('approvals', 'counts', 'status'));
    }

    public function create(Request $request)
    {
        $customers = Customer::orderBy('name')->get();
        $projects  = Project::orderBy('name')->get(['id', 'name', 'customer_id']);
        $preselectCustomerId = $request->get('customer_id');

        return view('customer-approvals.create', compact('customers', 'projects', 'preselectCustomerId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'project_id'  => 'nullable|exists:projects,id',
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'expires_at'  => 'nullable|date|after_or_equal:today',
        ]);

        $customer = Customer::findOrFail($data['customer_id']);

        if (! $customer->email) {
            return back()->withInput()->with('error', 'Der Kunde hat keine E-Mail-Adresse hinterlegt.');
        }

        $approval = CustomerApproval::create([
            'customer_id'  => $customer->id,
            'project_id'   => $data['project_id'] ?? null,
            'requested_by' => Auth::id(),
            'title'        => $data['title'],
            'description'  => $data['description'],
            'token'        => Str::random(48),
            'status'       => 'pending',
            'expires_at'   => ! empty($data['expires_at']) ? Carbon::parse($data['expires_at'])->endOfDay() : null,
        ]);

        return $this->sendRequestMail($approval, 'Freigabeanfrage wurde an ' . $customer->email . ' gesendet.');
    }

    public function show(CustomerApproval $customerApproval)
    {
        $customerApproval->load(['customer', 'project', 'requestedBy']);
        $url = route('customer-approval.show', $customerApproval->token);

        return view('customer-approvals.show', ['approval' => $customerApproval, 'url' => $url]);
    }

    public function edit(CustomerApproval $customerApproval)
    {
        if ($customerApproval->status !== 'pending') {
            return back()->with('error', 'Beantwortete Freigaben können nicht bearbeitet werden.');
        }

        $customers = Customer::orderBy('name')->get();
        $projects  = Project::orderBy('name')->get(['id', 'name', 'customer_id']);

        return view('customer-approvals.edit', [
            'approval'  => $customerApproval,
            'customers' => $customers,
            'projects'  => $projects,
        ]);
    }

    public function update(Request $request, CustomerApproval $customerApproval)
    {
        if ($customerApproval->status !== 'pending') {
            return back()->with('error', 'Beantwortete Freigaben können nicht bearbeitet werden.');
        }

        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'project_id'  => 'nullable|exists:projects,id',
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'expires_at'  => 'nullable|date',
        ]);

        $customerApproval->update([
            'customer_id' => $data['customer_id'],
            'project_id'  => $data['project_id'] ?? null,
            'title'       => $data['title'],
            'description' => $data['description'],
            'expires_at'  => ! empty($data['expires_at']) ? Carbon::parse($data['expires_at'])->endOfDay() : null,
        ]);

        return redirect()->route('customer-approvals.show', $customerApproval)
            ->with('success', 'Freigabeanfrage wurde aktualisiert.');
    }

    /** Anfrage erneut an den Kunden senden (z.B. als Erinnerung). */
    public function resend(CustomerApproval $customerApproval)
    {
        if ($customerApproval->status !== 'pending') {
            return back()->with('error', 'Diese Freigabeanfrage wurde bereits beantwortet.');
        }

        return $this->sendRequestMail($customerApproval, 'Anfrage wurde erneut an ' . $customerApproval->customer->email . ' gesendet.', true);
    }

    public function destroy(CustomerApproval $customerApproval)
    {
        if ($customerApproval->status !== 'pending') {
            return back()->with('error', 'Beantwortete Freigaben können nicht gelöscht werden.');
        }

        $customerApproval->delete();

        return redirect()->route('customer-approvals.index')->with('success', 'Freigabeanfrage wurde gelöscht.');
    }

    /** Freigabe-Mail an den Kunden senden + protokollieren. */
    private function sendRequestMail(CustomerApproval $approval, string $successMessage, bool $isResend = false)
    {
        $customer = $approval->customer;

        if (! $customer->email) {
            return back()->with('error', 'Der Kunde hat keine E-Mail-Adresse hinterlegt.');
        }

        $url = route('customer-approval.show', $approval->token);
        $logSubject = ($isResend ? 'Freigabe angefragt (erneut): ' : 'Freigabe angefragt: ') . $approval->title;

        try {
            Mail::to($customer->email, $customer->name)->send(new CustomerApprovalRequestMail($approval, $url));
            EmailLog::record('customer_approval_request', $customer->email, $logSubject, 'sent');

            if ($isResend) {
                return back()->with('success', $successMessage);
            }

            return redirect()->route('customer-approvals.show', $approval)->with('success', $successMessage);
        } catch (\Throwable $e) {
            Log::error('Freigabeanfrage-Mail fehlgeschlagen: ' . $e->getMessage());
            EmailLog::record('customer_approval_request', $customer->email, $logSubject, 'failed', $e->getMessage());

            $errorMessage = 'E-Mail-Versand fehlgeschlagen. Link: ' . $url;

            if ($isResend) {
                return back()->with('error', $errorMessage);
            }

            return redirect()->route('customer-approvals.show', $approval)->with('error', $errorMessage);
        }
    }
}
