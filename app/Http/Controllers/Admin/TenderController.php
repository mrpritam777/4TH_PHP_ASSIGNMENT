<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\Contractor;
use App\Models\InfrastructureType;
use App\Models\Tender;
use App\Models\TenderBid;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TenderController extends Controller
{
    /**
     * Document options an admin can require from bidders.
     *
     * @return array<int, string>
     */
    public static function documentOptions(): array
    {
        return [
            'Registration Certificate',
            'Tax Compliance Certificate',
            'Financial Statements',
            'Safety & Quality Plan',
            'Previous Project Experience',
            'Insurance Certificate',
        ];
    }

    public function index(Request $request): View
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $type = $request->input('type');

        $tenders = Tender::with('type')
            ->search($search)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($type, fn ($q) => $q->where('infrastructure_type_id', $type))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $statusCounts = Tender::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $types = InfrastructureType::orderBy('name')->get();

        return view('admin.tenders.index', compact('tenders', 'search', 'status', 'type', 'statusCounts', 'types'));
    }

    public function create(): View
    {
        return view('admin.tenders.form', [
            'tender' => null,
            'types' => InfrastructureType::active()->orderBy('name')->get(),
            'documentOptions' => self::documentOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateTender($request);

        $publish = $request->input('intent') === 'publish';

        $tender = Tender::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'infrastructure_type_id' => $data['infrastructure_type_id'],
            'location' => $data['location'],
            'estimated_budget' => $data['estimated_budget'],
            'deadline' => $data['deadline'],
            'required_documents' => $data['required_documents'] ?? [],
            'status' => $publish ? Tender::STATUS_PUBLISHED : Tender::STATUS_DRAFT,
            'allow_bid_edit' => $request->boolean('allow_bid_edit'),
            'published_at' => $publish ? now() : null,
            'created_by' => $request->user()->id,
        ]);

        ActivityLog::record($publish ? 'published' : 'created', 'Tender', $tender->id, "Admin created tender: {$tender->title}".($publish ? ' (published)' : ''));

        if ($publish) {
            $this->notifyContractors($tender);

            return redirect()->route('admin.tenders.show', $tender)->with('success', "Tender \"{$tender->title}\" was published and all approved contractors have been notified.");
        }

        return redirect()->route('admin.tenders.show', $tender)->with('success', "Tender \"{$tender->title}\" was saved as a draft.");
    }

    public function show(Tender $tender): View
    {
        $tender->load(['type', 'creator', 'awardedContractor.user']);

        $bids = $tender->bids()
            ->with('contractor.user')
            ->orderBy('bid_amount')
            ->get();

        $eligible = $bids->where('status', '!=', TenderBid::STATUS_REJECTED);
        $lowestBidId = $eligible->count() ? $eligible->sortBy('bid_amount')->first()->id : null;

        $cancellationRequests = $tender->cancellationRequests()
            ->with(['requester', 'decider'])
            ->latest()
            ->get();

        return view('admin.tenders.show', compact('tender', 'bids', 'lowestBidId', 'cancellationRequests'));
    }

    public function edit(Tender $tender): View
    {
        return view('admin.tenders.form', [
            'tender' => $tender,
            'types' => InfrastructureType::active()->orderBy('name')->get(),
            'documentOptions' => self::documentOptions(),
        ]);
    }

    public function update(Request $request, Tender $tender): RedirectResponse
    {
        $data = $this->validateTender($request);

        $intent = $request->input('intent');

        $tender->update([
            'title' => $data['title'],
            'description' => $data['description'],
            'infrastructure_type_id' => $data['infrastructure_type_id'],
            'location' => $data['location'],
            'estimated_budget' => $data['estimated_budget'],
            'deadline' => $data['deadline'],
            'required_documents' => $data['required_documents'] ?? [],
            'allow_bid_edit' => $request->boolean('allow_bid_edit'),
        ]);

        $message = "Tender \"{$tender->title}\" was updated.";

        if ($intent === 'publish' && $tender->status === Tender::STATUS_DRAFT) {
            $tender->update(['status' => Tender::STATUS_PUBLISHED, 'published_at' => now()]);
            $this->notifyContractors($tender);
            $message = "Tender \"{$tender->title}\" was published and all approved contractors have been notified.";
        }

        ActivityLog::record('updated', 'Tender', $tender->id, "Admin updated tender: {$tender->title}");

        return redirect()->route('admin.tenders.show', $tender)->with('success', $message);
    }

    public function publish(Tender $tender): RedirectResponse
    {
        abort_unless($tender->status === Tender::STATUS_DRAFT, 403, 'Only draft tenders can be published.');

        if ($tender->isExpired()) {
            return back()->with('error', 'The submission deadline has already passed. Please update the deadline before publishing.');
        }

        $tender->update(['status' => Tender::STATUS_PUBLISHED, 'published_at' => now()]);
        $this->notifyContractors($tender);

        ActivityLog::record('published', 'Tender', $tender->id, "Admin published tender: {$tender->title}");

        return back()->with('success', 'Tender published — all approved contractors have been notified.');
    }

    public function close(Tender $tender): RedirectResponse
    {
        abort_unless($tender->status === Tender::STATUS_PUBLISHED, 403, 'Only published tenders can be closed.');

        $tender->update(['status' => Tender::STATUS_CLOSED]);

        ActivityLog::record('closed', 'Tender', $tender->id, "Admin closed tender for bidding: {$tender->title}");

        return back()->with('success', 'Tender closed — no further bids can be submitted.');
    }

    /**
     * Award a tender to one of the bidders inside a DB transaction and notify
     * every bidder about the outcome.
     */
    public function award(Request $request, Tender $tender): RedirectResponse
    {
        $data = $request->validate(['bid_id' => ['required', 'integer']], [], ['bid_id' => 'bid']);

        abort_unless(in_array($tender->status, [Tender::STATUS_PUBLISHED, Tender::STATUS_CLOSED], true), 403, 'This tender cannot be awarded in its current status.');

        $bid = $tender->bids()->with('contractor.user')->findOrFail($data['bid_id']);

        DB::transaction(function () use ($tender, $bid) {
            $tender->bids()->where('id', $bid->id)->update(['status' => TenderBid::STATUS_AWARDED]);
            $tender->bids()->where('id', '!=', $bid->id)->update(['status' => TenderBid::STATUS_REJECTED]);

            $tender->update([
                'status' => Tender::STATUS_AWARDED,
                'awarded_contractor_id' => $bid->contractor_id,
                'awarded_at' => now(),
                'awarded_amount' => $bid->bid_amount,
            ]);

            if ($bid->contractor?->user_id) {
                AppNotification::send(
                    $bid->contractor->user_id,
                    'Tender awarded',
                    "Congratulations — your bid for \"{$tender->title}\" has been selected (₹".number_format($bid->bid_amount, 2).').',
                    'tender_awarded',
                    route('contractor.bids.index')
                );
            }

            foreach ($tender->bids()->with('contractor')->where('id', '!=', $bid->id)->get() as $loser) {
                if ($loser->contractor?->user_id) {
                    AppNotification::send(
                        $loser->contractor->user_id,
                        'Bid not selected',
                        "Your bid for \"{$tender->title}\" was not selected this time. Thank you for participating.",
                        'tender_awarded',
                        route('contractor.bids.index')
                    );
                }
            }
        });

        ActivityLog::record('awarded', 'Tender', $tender->id, "Admin awarded tender \"{$tender->title}\" to {$bid->contractor?->company_name}");

        return redirect()->route('admin.tenders.show', $tender)->with('success', "Tender awarded to {$bid->contractor?->company_name}. All bidders have been notified.");
    }

    public function destroy(Tender $tender): RedirectResponse
    {
        abort_if($tender->status !== Tender::STATUS_DRAFT, 403, 'Only draft tenders can be deleted.');

        if ($tender->bids()->exists() || $tender->cancellationRequests()->exists()) {
            return back()->with('error', 'This tender has related records and cannot be deleted. Close it instead.');
        }

        $title = $tender->title;
        $tender->delete();

        ActivityLog::record('deleted', 'Tender', null, "Admin deleted draft tender: {$title}");

        return redirect()->route('admin.tenders.index')->with('success', "Draft \"{$title}\" was deleted.");
    }

    /**
     * Download a private bid document (admins only — files are never public).
     */
    public function downloadBidDocument(Tender $tender, TenderBid $bid, int $index)
    {
        abort_unless((int) $bid->tender_id === (int) $tender->id, 404);

        $documents = $bid->documents ?? [];
        $document = $documents[$index] ?? null;

        abort_unless($document && ! empty($document['path']), 404);
        abort_unless(Storage::disk('local')->exists($document['path']), 404);

        return Storage::disk('local')->download($document['path'], $document['name'] ?? basename($document['path']));
    }

    private function notifyContractors(Tender $tender): void
    {
        $userIds = Contractor::where('status', Contractor::STATUS_APPROVED)
            ->whereHas('user', fn ($q) => $q->where('status', 'active'))
            ->pluck('user_id')
            ->all();

        AppNotification::sendToUsers(
            $userIds,
            'New tender published',
            "\"{$tender->title}\" ({$tender->type?->name}) in {$tender->location} is now open for bidding until {$tender->deadline->format('d M Y, H:i')}.",
            'tender_published',
            route('contractor.tenders.show', $tender)
        );
    }

    private function validateTender(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'infrastructure_type_id' => [
                'required', 'integer',
                Rule::exists('infrastructure_types', 'id')->where('status', 'active'),
            ],
            'location' => ['required', 'string', 'max:200'],
            'estimated_budget' => ['required', 'numeric', 'min:1'],
            'deadline' => ['required', 'date', 'after:now'],
            'required_documents' => ['nullable', 'array'],
            'required_documents.*' => [Rule::in(self::documentOptions())],
        ], [
            'deadline.after' => 'The submission deadline must be a future date and time.',
        ], [
            'title' => 'tender title',
            'description' => 'description',
            'infrastructure_type_id' => 'infrastructure category',
            'location' => 'location',
            'estimated_budget' => 'estimated budget',
            'deadline' => 'submission deadline',
            'required_documents' => 'required documents',
        ]);
    }
}