<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Infrastructure;
use Illuminate\View\View;

class BridgeShowController extends Controller
{
    public function show(Infrastructure $bridge): View
    {
        abort_unless($bridge->type?->slug === 'bridge', 404);

        $bridge->load(['bridgeDetails', 'type', 'maintenanceRequests', 'contacts']);

        $lastMaintenance = $bridge->maintenanceRequests
            ->where('status', 'completed')
            ->sortByDesc(fn ($r) => $r->completion_date ?? $r->created_at)
            ->first();

        $emergencyContacts = $bridge->contacts
            ->where('contact_type', 'emergency')
            ->take(3)
            ->values();

        return view('public.bridge', compact('bridge', 'lastMaintenance', 'emergencyContacts'));
    }
}
