<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\InfrastructureType;
use App\Models\Tender;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenderController extends Controller
{
    /**
     * Public, read-only listing of published tenders.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $type = $request->input('type');

        $tenders = Tender::with('type')
            ->published()
            ->search($search)
            ->when($type, fn ($q) => $q->where('infrastructure_type_id', $type))
            ->orderByDesc('published_at')
            ->paginate(10)
            ->withQueryString();

        $types = InfrastructureType::active()->orderBy('name')->get();

        return view('public.tenders.index', compact('tenders', 'search', 'type', 'types'));
    }

    public function show(Tender $tender): View
    {
        abort_unless($tender->status === Tender::STATUS_PUBLISHED, 404);

        $tender->load('type');

        $bidCount = $tender->bids()->count();

        return view('public.tenders.show', compact('tender', 'bidCount'));
    }
}