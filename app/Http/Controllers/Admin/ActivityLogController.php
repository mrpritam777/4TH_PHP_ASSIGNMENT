<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $module = $request->input('module');

        $logs = ActivityLog::with('user')
            ->when($module, fn ($q) => $q->where('module', $module))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $modules = ActivityLog::selectRaw('module, COUNT(*) as total')
            ->groupBy('module')
            ->pluck('total', 'module');

        return view('admin.activity-logs.index', compact('logs', 'module', 'modules'));
    }
}
