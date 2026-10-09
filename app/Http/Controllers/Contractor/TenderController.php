<?php

namespace App\Http\Controllers\Contractor;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\InfrastructureType;
use App\Models\Tender;
use App\Models\TenderBid;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TenderController extends Controller
{
    /**
     * Browse all available (eligible) tenders for the logged-in contractor.
     */
    public function index(Request $request): View
    {
        $contractor = $request->user()->contractor;
        $search = $request->input('search');
        $type = $request->input('type');
        $showAll = $request->boolean('all');

        $query = Tender::with('type')
            ->search($search)
            ->when($type, fn ($q) => $q->where('infrastructure_type_id', $type));

        if ($showAll) {
            $query->whereIn('status', [Tender::STATUS_PUBLISHED, Tender::STATUS_CLOSED, Tender::STATUS_AWARDED]);
        } else {
            $query->published()->where('deadline', '>', now());
        }

        $tenders = $query->latest('deadline')->paginate(10)->withQueryString();

        $bidsByTender = $contractor
            ? $contractor->bids()->pluck('status', 'tender_id')
            : collect();

        $types = InfrastructureType::active()->orderBy('name')->get();

        return view('contractor.tenders.index', compact('tenders', 'search', 'type', 'showAll', 'bidsByTender', 'types'));
    }

    public function show(Request $request, Tender $tender): View
    {
        $contractor = $request->user()->contractor;

        abort_unless(in_array($tender->status, [Tender::STATUS_PUBLISHED, Tender::STATUS_CLOSED, Tender::STATUS_AWARDED], true), 404);

        $tender->load('type');

        $existingBid = $tender->bids()->where('contractor_id', $contractor->id)->first();

        $canBid = $tender->acceptsBids()
            && $existingBid === null
            && ! $tender->hasPendingCancellation();

        $canEditBid = $tender->acceptsBids()
            && $existingBid !== null
            && $existingBid->status === TenderBid::STATUS_SUBMITTED
            && $tender->allow_bid_edit;

        $projectCancellation = $tender->hasPendingCancellation()
            ? $tender->cancellationRequests()->where('status', 'pending')->first()
            : null;

        return view('contractor.tenders.show', compact('tender', 'contractor', 'existingBid', 'canBid', 'canEditBid', 'projectCancellation'));
    }

    /**
     * Submit (or explicitly edit) a bid. Duplicate bids, expired tenders and
     * closed tenders are all rejected server-side.
     */
    public function bid(Request $request, Tender $tender): RedirectResponse
    {
        $contractor = $request->user()->contractor;
        abort_unless($contractor && $contractor->isApproved(), 403);

        if (! $tender->acceptsBids()) {
            return redirect()->route('contractor.tenders.show', $tender)->with('error', 'Bidding for this tender is closed — the deadline has passed or the tender is no longer open.');
        }

        if ($tender->hasPendingCancellation()) {
            return redirect()->route('contractor.tenders.show', $tender)->with('error', 'A cancellation request for this tender is pending a decision — no new bids are accepted.');
        }

        $existingBid = $tender->bids()->where('contractor_id', $contractor->id)->first();

        if ($existingBid && ! ($tender->allow_bid_edit && $existingBid->status === TenderBid::STATUS_SUBMITTED)) {
            return redirect()->route('contractor.tenders.show', $tender)->with('error', 'You have already submitted a bid for this tender. Duplicate bids are not allowed.');
        }

        $rules = [
            'bid_amount' => ['required', 'numeric', 'min:1'],
            'proposal' => ['required', 'string', 'min:20', 'max:5000'],
        ];

        $labels = ['bid_amount' => 'bid amount', 'proposal' => 'proposal'];

        foreach ($tender->required_documents ?? [] as $index => $documentName) {
            $key = 'doc_'.$index;
            $rules[$key] = ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'];
            $labels[$key] = $documentName;
        }

        $data = $request->validate($rules, [
            'bid_amount.numeric' => 'The bid amount must be a number.',
        ], $labels);

        $savedDocuments = $existingBid ? ($existingBid->documents ?? []) : [];

        DB::transaction(function () use ($request, $tender, $contractor, $data, &$savedDocuments) {
            foreach ($tender->required_documents ?? [] as $index => $documentName) {
                $key = 'doc_'.$index;
                if (! $request->hasFile($key)) {
                    continue;
                }

                $path = $request->file($key)->store("bids/{$tender->id}/{$contractor->id}", 'local');
                $savedDocuments[$index] = [
                    'label' => $documentName,
                    'name' => $request->file($key)->getClientOriginalName(),
                    'path' => $path,
                ];
            }

            if ($existingBid) {
                $existingBid->update([
                    'bid_amount' => $data['bid_amount'],
                    'proposal' => $data['proposal'],
                    'documents' => $savedDocuments,
                    'submitted_at' => now(),
                ]);
            } else {
                TenderBid::create([
                    'tender_id' => $tender->id,
                    'contractor_id' => $contractor->id,
                    'bid_amount' => $data['bid_amount'],
                    'proposal' => $data['proposal'],
                    'documents' => $savedDocuments,
                    'status' => TenderBid::STATUS_SUBMITTED,
                    'submitted_at' => now(),
                ]);
            }
        });

        AppNotification::sendToUsers(
            AppNotification::adminIds(),
            'New bid submitted',
            "{$contractor->company_name} submitted a bid for \"{$tender->title}\" (₹".number_format((float) $data['bid_amount'], 2).').',
            'bid_submitted',
            route('admin.tenders.show', $tender)
        );

        ActivityLog::record($existingBid ? 'updated' : 'created', 'Bid', $tender->id, "Contractor {$contractor->company_name} ".($existingBid ? 'updated' : 'submitted')." a bid for \"{$tender->title}\"");

        return redirect()->route('contractor.bids.index')->with('success', 'Your bid has been submitted successfully — you can track its status under My Bids.');
    }

    /**
     * Download one of the contractor's own private bid documents.
     */
    public function downloadDocument(Request $request, TenderBid $bid, int $index)
    {
        $contractor = $request->user()->contractor;
        abort_unless($contractor && (int) $bid->contractor_id === (int) $contractor->id, 403);

        $document = ($bid->documents ?? [])[$index] ?? null;
        abort_unless($document && ! empty($document['path']), 404);
        abort_unless(Storage::disk('local')->exists($document['path']), 404);

        return Storage::disk('local')->download($document['path'], $document['name'] ?? basename($document['path']));
    }
}