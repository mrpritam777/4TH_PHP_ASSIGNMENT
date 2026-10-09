<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrackController extends Controller
{
    public function index(Request $request): View
    {
        if ($request->filled('requestNumber')) {
            return $this->show($request->input('requestNumber'));
        }

        return view('public.track');
    }

    public function show(string $requestNumber): View
    {
        $requestNumber = strtoupper(trim($requestNumber));

        $request = MaintenanceRequest::where('request_number', $requestNumber)
            ->with(['infrastructure.type', 'updates.updater'])
            ->first();

        abort_if(! $request, 404, 'No request found with that number. Please check and try again.');

        $timeline = $request->updates()->with('updater')->latest()->get();

        return view('public.track-result', compact('request', 'timeline'));
    }
}
