<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Infrastructure;
use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function infrastructure(Request $request): View
    {
        $search = $request->input('search');

        $totals = [
            'all' => Infrastructure::count(),
            'bridge' => Infrastructure::ofType('bridge')->count(),
            'road' => Infrastructure::ofType('road')->count(),
            'building' => Infrastructure::ofType('building')->count(),
            'hospital' => Infrastructure::ofType('hospital')->count(),
        ];

        $conditionOrder = array_flip(['excellent', 'good', 'fair', 'poor', 'critical']);

        $conditionSummary = Infrastructure::selectRaw('`condition`, COUNT(*) as total')
            ->groupBy('condition')
            ->get()
            ->sortBy(fn ($row) => $conditionOrder[$row->condition] ?? PHP_INT_MAX)
            ->values();

        $typeBreakdown = Infrastructure::with('type')
            ->selectRaw('type_id, COUNT(*) as total')
            ->groupBy('type_id')
            ->get()
            ->mapWithKeys(fn ($row) => [
                $row->type->name => [
                    'total' => $row->total,
                    'good' => Infrastructure::where('type_id', $row->type_id)->whereIn('condition', ['excellent', 'good'])->count(),
                    'attention' => Infrastructure::where('type_id', $row->type_id)->whereIn('condition', ['fair', 'poor', 'critical'])->count(),
                ],
            ]);

        $list = Infrastructure::with('type')
            ->search($search)
            ->latest()
            ->take(100)
            ->get();

        return view('admin.reports.infrastructure', compact('totals', 'conditionSummary', 'typeBreakdown', 'list', 'search'));
    }

    public function maintenance(Request $request): View
    {
        $status = $request->input('status');
        $from = $request->input('from');
        $to = $request->input('to');

        $base = MaintenanceRequest::query()
            ->status($status)
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to));

        $summary = [
            'total' => (clone $base)->count(),
            'pending' => (clone $base)->whereIn('status', ['pending', 'under_review'])->count(),
            'in_progress' => (clone $base)->where('status', 'in_progress')->count(),
            'completed' => (clone $base)->where('status', 'completed')->count(),
            'cancelled' => (clone $base)->where('status', 'cancelled')->count(),
        ];

        $byPriority = (clone $base)
            ->selectRaw('priority, COUNT(*) as total')
            ->groupBy('priority')
            ->get();

        $list = MaintenanceRequest::with(['infrastructure.type', 'assignedStaff'])
            ->status($status)
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reports.maintenance', compact('summary', 'byPriority', 'list', 'status', 'from', 'to'));
    }

    public function cost(Request $request): View
    {
        $from = $request->input('from');
        $to = $request->input('to');
        $type = $request->input('type');
        $status = $request->input('status');

        $base = MaintenanceRequest::query()
            ->status($status)
            ->when($type, fn ($q) => $q->whereHas('infrastructure.type', fn ($t) => $t->where('slug', $type)))
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to));

        $totals = (clone $base)->get()->map(function ($r) {
            return [
                'estimated' => (float) $r->estimated_cost,
                'actual' => (float) ($r->actual_cost ?? 0),
            ];
        });

        $summary = [
            'estimated' => $totals->sum('estimated'),
            'actual' => $totals->sum('actual'),
            'completed' => (clone $base)->where('status', 'completed')->count(),
        ];

        $monthly = (clone $base)
            ->whereNotNull('actual_cost')
            ->whereNotNull('completion_date')
            ->select('completion_date', 'actual_cost')
            ->get()
            ->groupBy(fn ($row) => $row->completion_date->format('Y-m'))
            ->map(fn ($rows, $month) => (object) [
                'month' => $month,
                'total' => round((float) $rows->sum('actual_cost'), 2),
            ])
            ->sortKeys()
            ->values();

        $byInfrastructure = (clone $base)
            ->whereNotNull('actual_cost')
            ->with('infrastructure.type')
            ->selectRaw('infrastructure_id, SUM(actual_cost) as total')
            ->groupBy('infrastructure_id')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        $list = MaintenanceRequest::with(['infrastructure.type', 'assignedStaff'])
            ->status($status)
            ->when($type, fn ($q) => $q->whereHas('infrastructure.type', fn ($t) => $t->where('slug', $type)))
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reports.cost', compact('summary', 'monthly', 'byInfrastructure', 'list', 'from', 'to', 'type', 'status'));
    }
}
