<?php

namespace App\Http\Controllers\Admin;

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
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $cancellations = CancellationRequest::with(['tender.type', 'requester', 'decider'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search, fn ($q) => $q->whereHas('tender', fn ($t) => $t->search($search)))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $statusCounts = CancellationRequest::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.cancellations.index', compact('cancellations', 'search', 'status', 'statusCounts'));
    }

    public function create(Request $request): View
    {
        $preselected = $request->integer('tender_id') ?: null;

        $tenders = Tender::whereIn('status', Tender::CANCELLABLE_STATUSES)
            ->orderByDesc('updated_at')
            ->get();

        return view('admin.cancellations.create', compact('tenders', 'preselected'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tender_id' => ['required', 'integer', 'exists:tenders,id'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ], [], [
            'tender_id' => 'related tender',
            'reason' => 'cancellation reason',
        ]);

        $tender = Tender::findOrFail($data['tender_id']);

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

        $this->notifyCounterparty($tender, 'admin', $request->user()->name);

        ActivityLog::record('created', 'Cancellation', $tender->id, "Admin requested cancellation of tender \"{$tender->title}\"");

        return redirect()->route('admin.cancellations.index')->with('success', 'Cancellation request submitted — the other party has been notified.');
    }

    public function show(CancellationRequest $cancellation): View
    {
        $cancellation->load(['tender.type', 'requester', 'decider']);

        $history = CancellationRequest::where('tender_id', $cancellation->tender_id)
            ->with(['requester', 'decider'])
            ->latest()
            ->get();

        $canDecide = $cancellation->canBeDecidedBy(request()->user());

        return view('admin.cancellations.show', compact('cancellation', 'history', 'canDecide'));
    }

    /**
     * Approve or reject the request. The tender is only moved to "cancelled"
     * after the authorised decision — never merely because a request exists.
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

        $this->notifyCounterparty(
            $tender,
            $cancellation->requester_role === 'admin' ? 'contractor' : 'admin',
            $request->user()->name,
            $approved
                ? "The cancellation of \"{$tender->title}\" has been approved. The tender/project is now cancelled."
                : 'The cancellation of "'. $tender->title .' has been rejected'.($data['decision_note'] ? " — {$data['decision_note']}" : '.'),
            $approved ? 'cancellation_approved' : 'cancellation_rejected'
        );

        ActivityLog::record($approved ? 'approved' : 'rejected', 'Cancellation', $tender->id, "Cancellation request for \"{$tender->title}\" ".($approved ? 'approved' : 'rejected').' by admin');

        return redirect()->route('admin.cancellations.show', $cancellation)->with('success', $approved ? 'Cancellation approved — the tender has been cancelled.' : 'Cancellation request rejected.');
    }

    public function withdraw(CancellationRequest $cancellation): RedirectResponse
    {
        abort_unless((int) $cancellation->requested_by === auth()->id(), 403);
        abort_unless($cancellation->isPending(), 403, 'Only pending requests can be withdrawn.');

        $cancellation->update([
            'status' => CancellationRequest::STATUS_CANCELLED,
            'decided_at' => now(),
        ]);

        ActivityLog::record('cancelled', 'Cancellation', $cancellation->tender_id, 'Admin withdrew a pending cancellation request');

        return back()->with('success', 'Cancellation request withdrawn.');
    }

    /**
     * Notify the party on the other side of the table. Designed so that an
     * email/push channel can be attached to the same helper later.
     */
    private function notifyCounterparty(Tender $tender, string $target, string $actorName, ?string $message = null, string $type = 'cancellation_requested'): void
    {
        if ($target === 'admin') {
            AppNotification::sendToUsers(
                AppNotification::adminIds(),
                'Cancellation request submitted',
                $message ?? "{$actorName} requested cancellation of \"{$tender->title}\". Please review the request.",
                $type,
                route('admin.cancellations.index')
            );

            return;
        }

        if ($tender->awarded_contractor_id) {
            $userIds = [(int) $tender->awardedContractor()->value('user_id')];
        } else {
            $userIds = $tender->bids()->with('contractor')->get()
                ->map(fn ($bid) => $bid->contractor?->user_id)
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        AppNotification::sendToUsers(
            $userIds,
            'Cancellation request update',
            $message ?? "{$actorName} requested cancellation of \"{$tender->title}\". Please review the request.",
            $type,
            route('contractor.cancellations.index')
        );
    }
}