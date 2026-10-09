<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Infrastructure;
use App\Models\MaintenanceRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'bridges' => Infrastructure::ofType('bridge')->count(),
            'roads' => Infrastructure::ofType('road')->count(),
            'buildings' => Infrastructure::ofType('building')->count(),
            'hospitals' => Infrastructure::ofType('hospital')->count(),
            'total_infrastructure' => Infrastructure::count(),
            'total_requests' => MaintenanceRequest::count(),
            'pending' => MaintenanceRequest::whereIn('status', ['pending', 'under_review'])->count(),
            'in_progress' => MaintenanceRequest::where('status', 'in_progress')->count(),
            'completed' => MaintenanceRequest::where('status', 'completed')->count(),
            'critical_infra' => Infrastructure::where('condition', 'critical')->count(),
            'active_staff' => User::where('role', 'staff')->where('status', 'active')->count(),
        ];

        $conditionCounts = Infrastructure::query()
            ->selectRaw('`condition`, COUNT(*) as total')
            ->groupByRaw('`condition`')
            ->pluck('total', 'condition');

        $condition = [
            'Good' => ((int) ($conditionCounts['excellent'] ?? 0) + (int) ($conditionCounts['good'] ?? 0)),
            'Average' => (int) ($conditionCounts['fair'] ?? 0),
            'Poor' => (int) ($conditionCounts['poor'] ?? 0),
            'Critical' => (int) ($conditionCounts['critical'] ?? 0),
        ];

        $now = now();
        $startOfLast6Months = $now->copy()->subMonths(5)->startOfMonth();

        $monthlyRequests = MaintenanceRequest::where('created_at', '>=', $startOfLast6Months)
            ->get()
            ->groupBy(fn ($r) => $r->created_at->format('Y-m'))
            ->map(fn ($group) => $group->count());

        $monthlyCompleted = MaintenanceRequest::whereNotNull('completion_date')
            ->where('completion_date', '>=', $startOfLast6Months)
            ->get()
            ->groupBy(fn ($r) => $r->completion_date->format('Y-m'))
            ->map(fn ($group) => $group->count());

        $monthlyPending = MaintenanceRequest::whereIn('status', ['pending', 'under_review', 'assigned', 'in_progress'])
            ->get()
            ->groupBy(fn ($r) => $r->created_at->format('Y-m'))
            ->map(fn ($group) => $group->count());

        $monthlyCost = MaintenanceRequest::whereNotNull('actual_cost')
            ->where('completion_date', '>=', $startOfLast6Months)
            ->get()
            ->groupBy(fn ($r) => $r->completion_date->format('Y-m'))
            ->map(fn ($group) => $group->sum('actual_cost'));

        $labels = [];
        $requests = [];
        $completed = [];
        $pending = [];
        $cost = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i)->format('Y-m');
            $labels[] = $now->copy()->subMonths($i)->format('M Y');
            $requests[] = (int) ($monthlyRequests[$month] ?? 0);
            $completed[] = (int) ($monthlyCompleted[$month] ?? 0);
            $pending[] = (int) ($monthlyPending[$month] ?? 0);
            $cost[] = (float) ($monthlyCost[$month] ?? 0);
        }

        $chart = [
            'labels' => $labels,
            'requests' => $requests,
            'completed' => $completed,
            'pending' => $pending,
            'cost' => $cost,
        ];

        $recentRequests = MaintenanceRequest::with(['infrastructure.type', 'assignedStaff'])
            ->latest()
            ->take(6)
            ->get();

        $criticalInfra = Infrastructure::where('condition', 'critical')
            ->with('type')
            ->latest()
            ->take(4)
            ->get();

        return view('admin.dashboard', compact('stats', 'condition', 'chart', 'recentRequests', 'criticalInfra'));
    }
}
