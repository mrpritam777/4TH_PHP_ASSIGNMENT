<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Infrastructure;
use App\Models\MaintenanceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function global(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [
            'bridges' => $this->searchInfra($term, 'bridge', 4),
            'roads' => $this->searchInfra($term, 'road', 3),
            'buildings' => $this->searchInfra($term, 'building', 3),
            'hospitals' => $this->searchInfra($term, 'hospital', 3),
            'maintenance' => MaintenanceRequest::with('infrastructure')
                ->where(fn ($q) => $q
                    ->where('title', 'like', "%{$term}%")
                    ->orWhere('request_number', 'like', "%{$term}%"))
                ->latest()
                ->take(4)
                ->get()
                ->map(fn ($r) => [
                    'id' => $r->id,
                    'title' => $r->request_number . ' — ' . $r->title,
                    'subtitle' => $r->infrastructure?->name,
                    'url' => route('admin.maintenance.show', $r),
                ]),
            'contacts' => Contact::search($term)
                ->latest()
                ->take(4)
                ->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'title' => $c->name,
                    'subtitle' => $c->organization ?? $c->designation ?? 'Contact',
                    'url' => route('admin.contacts.index', ['search' => $c->name]),
                ]),
        ];

        return response()->json(['results' => $results, 'term' => $term]);
    }

    protected function searchInfra(string $term, string $slug, int $limit): \Illuminate\Support\Collection
    {
        return Infrastructure::ofType($slug)
            ->with('type')
            ->search($term)
            ->latest()
            ->take($limit)
            ->get()
            ->map(fn ($i) => [
                'id' => $i->id,
                'title' => $i->name,
                'subtitle' => $i->code . ' • ' . ($i->location ?? ''),
                'url' => $i->getDetailsUrl(),
            ]);
    }
}
