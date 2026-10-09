<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        return view('admin.settings', [
            'settings' => [
                'organization_name' => 'BridgeCare Infrastructure Authority',
                'support_email' => 'support@bridgecare.gov',
                'support_phone' => '+91 80 4610 4610',
                'address' => 'Infrastructure House, MG Road, New Delhi 110001',
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'organization_name' => ['required', 'string', 'max:150'],
            'support_email' => ['required', 'email'],
            'support_phone' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:300'],
        ]);

        return back()->with('success', 'Settings saved successfully.');
    }
}
