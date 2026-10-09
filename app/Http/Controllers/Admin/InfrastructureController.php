<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Infrastructure;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InfrastructureController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $type = $request->input('type');
        $condition = $request->input('condition');

        $infrastructures = Infrastructure::with(['type'])
            ->search($search)
            ->condition($condition)
            ->when($type, fn ($q) => $q->whereHas('type', fn ($t) => $t->where('slug', $type)))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $typeCounts = Infrastructure::selectRaw('type_id, COUNT(*) as total')
            ->groupBy('type_id')
            ->pluck('total', 'type_id');

        return view('admin.infrastructures.index', compact('infrastructures', 'search', 'type', 'condition', 'typeCounts'));
    }
}
