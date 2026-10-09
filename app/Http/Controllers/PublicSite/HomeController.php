<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Infrastructure;
use App\Models\MaintenanceRequest;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $stats = [
            'bridges' => Infrastructure::ofType('bridge')->count(),
            'roads' => Infrastructure::ofType('road')->count(),
            'buildings' => Infrastructure::ofType('building')->count(),
            'hospitals' => Infrastructure::ofType('hospital')->count(),
            'maintenance_completed' => MaintenanceRequest::where('status', 'completed')->count(),
        ];

        $featured = Infrastructure::with('type')
            ->inRandomOrder()
            ->take(8)
            ->get()
            ->groupBy(fn ($i) => $i->type->slug);

        return view('public.home', compact('stats', 'featured'));
    }
}
