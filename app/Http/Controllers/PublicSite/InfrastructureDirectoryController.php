<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Infrastructure;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InfrastructureDirectoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $type = $request->input('type');
        $condition = $request->input('condition');
        $location = $request->input('location');

        $infrastructures = Infrastructure::with('type')
            ->search($search)
            ->when($type, fn ($q) => $q->whereHas('type', fn ($t) => $t->where('slug', $type)))
            ->condition($condition)
            ->when($location, fn ($q) => $q->where('location', 'like', "%{$location}%"))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $locations = Infrastructure::distinct()
            ->whereNotNull('location')
            ->pluck('location')
            ->filter()
            ->unique()
            ->values();

        return view('public.directory', compact('infrastructures', 'search', 'type', 'condition', 'location', 'locations'));
    }

    public function ajaxByType(Request $request)
    {
        $type = $request->query('type');

        if (! $type) {
            return response()->json([]);
        }

        $infrastructures = Infrastructure::with('type')
            ->whereHas('type', fn ($t) => $t->where('slug', $type))
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json($infrastructures);
    }
}
