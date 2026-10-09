<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\InfrastructureType;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function index(): View
    {
        $categories = InfrastructureType::active()->orderBy('name')->get();

        $contact = [
            'organization' => 'BridgeCare Infrastructure Authority',
            'email' => config('app.support_email') ?? 'support@bridgecare.gov',
            'phone' => config('app.support_phone') ?? '+91 80 4610 4610',
            'address' => config('app.address') ?? 'Infrastructure House, MG Road, New Delhi 110001',
        ];

        return view('public.about', compact('categories', 'contact'));
    }
}