<?php

namespace App\Http\Controllers\Contractor;

use App\Http\Controllers\Controller;
use App\Models\TenderBid;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BidController extends Controller
{
    /**
     * The contractor's own bids with their current status.
     */
    public function index(Request $request): View
    {
        $contractor = $request->user()->contractor;
        $search = $request->input('search');
        $status = $request->input('status');

        $bids = TenderBid::with('tender.type')
            ->where('contractor_id', $contractor->id)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search, fn ($q) => $q->whereHas('tender', fn ($t) => $t->search($search)))
            ->latest('submitted_at')
            ->paginate(10)
            ->withQueryString();

        $statusCounts = TenderBid::where('contractor_id', $contractor->id)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('contractor.bids.index', compact('bids', 'search', 'status', 'statusCounts'));
    }
}