<?php

namespace App\Http\Controllers\Contractor;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\CancellationRequest;
use App\Models\Tender;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CancellationRequestController extends Controller
{
    /**
     * Cancellation requests affecting tenders this contractor is involved in,
     * plus any the contractor raised themselves.
     */
    public function index(Request $request): View
    {
        $contractor = $request->user()->contractor;
        $status = $request->input('status');

        $cancellations = CancellationRequest::with(['tender.type', 'requester', 'decider'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->where(function ($q) use ($contractor) {
                $q->where('requested_by', auth()->id())
                    ->orWhereHas('tender', fn ($t) => $t
                        ->where('awarded_contractor_id', $contractor->id)
                        ->orWhereHas('bids', fn ($b) => $b->where('contractor_id', $contractor->id)));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('contractor.cancellations.index', compact('cancellations', 'status'));
    }

    public function create(Request $request): View
    {
        $contractor = $request->user()->contractor;
        $preselected = $request->integer('tender_id') ?: null;

        $tenders = Tender::whereIn('status', Tender::CANCELLABLE_STATUSES)
            ->where(function ($q) use ($contractor) {
                $q->where('awarded_contractor_id', $contractor->id)
                    ->orWhereHas('bids', fn ($b) => $b->where('contractor_id', $contractor->id));
            })
            ->orderBy('title')
            ->get();

        return view('contractor.cancellations.create', compact('tenders', 'preselected'));
    }

    public function store(Request $request): RedirectResponse
    {
        $contractor = $request->user()->contractor;

        $data = $request->validate([
            'tender_id' => ['required', 'integer', 'exists:tenders,id'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ], [], ['tender_id' => 'related tender', 'reason' => 'cancellation reason']);

        $tender = Tender::findOrFail($data['tender_id']);

        $involved = (int) $tender->awarded_contractor_id === (int) $contractor->id
            || $tender->bids()->where('contractor_id', $contractor->id)->exists();

        abort_unless($involved, 403, 'You can only request cancellation for tenders you are involved in.');

        if (! $tender->isCancellable()) {
            return back()->with('error', 'This tender can no longer be cancelled in its current status.');
        }

        if ($tender->hasPendingCancellation()) {
            return back()->with('error', 'A cancellation request for this tender is already pending a decision.');
        }

        CancellationRequest::create([
            'tender_id' => $tender->id,
            'requested_by' => $request->user()->id,
            'requester_role' => $request->user()->role,
            'reason' => $data['reason'],
            'status' => CancellationRequest::STATUS_PENDING,
        ]);

        AppNotification::sendToUsers(
            AppNotification::adminIds(),
            'Cancellation request submitted',
            "{$contractor->company_name} requested cancellation of \"{$tender->title}\". Please review the request.",
            'cancellation_requested',
            route('admin.cancellations.index')
        );

        ActivityLog::record('created', 'Cancellation', $tender->id, "Contractor requested cancellation of tender \"{$tender->title}\"");

        return redirect()->route('contractor.cancellations.index')->with('success', 'Cancellation request submitted — the administrators have been notified.');
    }

    public function show(Request $request, CancellationRequest $cancellation): View
    {
        $contractor = $request->user()->contractor;

        $involved = (int) $cancellation->requested_by === (int) $request->user()->id
            || (int) $cancellation->tender->awarded_contractor_id === (int) $contractor->id
            || $cancellation->tender->bids()->where('contractor_id', $contractor->id)->exists();

        abort_unless($involved, 403);

        $cancellation->load(['tender.type', 'requester', 'decider']);

        $history = CancellationRequest::where('tender_id', $cancellation->tender_id)
            ->with(['requester', 'decider'])
            ->latest()
            ->get();

        $canDecide = $cancellation->canBeDecidedBy($request->user());

        return view('contractor.cancellations.show', compact('cancellation', 'history', 'canDecide'));
    }

    /**
     * Approve / reject an admin-raised cancellation request (counterparty
     * decision). The tender status changes only after this decision.
     */
    public function decide(Request $request, CancellationRequest $cancellation): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'decision_note' => ['nullable', 'string', 'max:1000'],
        ], [], ['decision' => 'decision', 'decision_note' => 'rejection reason']);

        if ($data['decision'] === 'rejected' && blank($data['decision_note'] ?? null)) {
            throw ValidationException::withMessages(['decision_note' => 'A rejection reason is required.']);
        }

        abort_unless($cancellation->canBeDecidedBy($request->user()), 403, 'You are not authorised to decide on this cancellation request.');

        DB::transaction(function () use ($cancellation, $data, $request) {
            $cancellation->update([
                'status' => $data['decision'],
                'decided_by' => $request->user()->id,
                'decided_at' => now(),
                'decision_note' => $data['decision_note'] ?? null,
            ]);

            if ($data['decision'] === CancellationRequest::STATUS_APPROVED) {
                $cancellation->tender()->update(['status' => Tender::STATUS_CANCELLED]);
            }
        });

        $tender = $cancellation->tender;
        $approved = $data['decision'] === CancellationRequest::STATUS_APPROVED;

        AppNotification::sendToUsers(
            AppNotification::adminIds(),
            'Cancellation decision recorded',
            $approved
                ? "The cancellation of \"{$tender->title}\" was approved by the contractor. The tender/project is now cancelled."
                : "The cancellation of \"{$tender->title}\" was rejected by the contractor".($data['decision_note'] ? " — {$data['decision_note']}" : '.'),
            $approved ? 'cancellation_approved' : 'cancellation_rejected',
            route('admin.cancellations.index')
        );

        ActivityLog::record($approved ? 'approved' : 'rejected', 'Cancellation', $tender->id, 'Contractor '.($approved ? 'approved' : 'rejected')." cancellation of \"{$tender->title}\"");

        return redirect()->route('contractor.cancellations.show', $cancellation)->with('success', $approved ? 'Cancellation approved — the tender has been cancelled.' : 'Cancellation request rejected.');
    }

    public function withdraw(CancellationRequest $cancellation): RedirectResponse
    {
        abort_unless((int) $cancellation->requested_by === auth()->id(), 403);
        abort_unless($cancellation->isPending(), 403, 'Only pending requests can be withdrawn.');

        $cancellation->update([
            'status' => CancellationRequest::STATUS_CANCELLED,
            'decided_at' => now(),
        ]);

        ActivityLog::record('cancelled', 'Cancellation', $cancellation->tender_id, 'Contractor withdrew a pending cancellation request');

        return back()->with('success', 'Cancellation request withdrawn.');
    }
}