@extends('layouts.admin')

@section('title', 'Maintenance Report')

@section('content')
    <x-page-header title="Maintenance Report" subtitle="Analysis of maintenance requests and workload.">
    </x-page-header>

    <form class="bc-filter-bar" method="GET" action="{{ route('admin.reports.maintenance') }}">
        <div class="d-flex gap-2 flex-wrap">
            <div>
                <label class="bc-form-label">Status</label>
                <select name="status" class="bc-select">
                    <option value="">All Statuses</option>
                    @foreach (['pending' => 'Pending (incl. under review)', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $val => $lbl)
                        <option value="{{ $val }}" @selected($status === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="bc-form-label">From Date</label>
                <input type="date" name="from" class="bc-input" value="{{ $from }}">
            </div>
            <div>
                <label class="bc-form-label">To Date</label>
                <input type="date" name="to" class="bc-input" value="{{ $to }}">
            </div>
        </div>
        <button type="submit" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-funnel"></i> Apply Filters</button>
        @if ($status || $from || $to)
            <a href="{{ route('admin.reports.maintenance') }}" class="btn-bc btn-bc-ghost btn-bc-sm"><i class="bi bi-x-circle"></i> Reset</a>
        @endif
    </form>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <h3>Request Summary</h3>
                </div>
                <div class="bc-card-body">
                    <div class="bc-info-grid" style="grid-template-columns:repeat(2, 1fr); gap:16px;">
                        <div class="bc-info-item">
                            <span class="label">Total Requests</span>
                            <span class="value">{{ $summary['total'] }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Pending &amp; Under Review</span>
                            <span class="value">{{ $summary['pending'] }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">In Progress</span>
                            <span class="value">{{ $summary['in_progress'] }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Completed</span>
                            <span class="value">{{ $summary['completed'] }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Cancelled</span>
                            <span class="value">{{ $summary['cancelled'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <h3>Priority Distribution</h3>
                </div>
                <div class="bc-card-body">
                    @foreach ($byPriority as $item)
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div style="width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;
                                background:@php
                                    $colors = ['low' => '#f1f5f9', 'medium' => '#dbeafe', 'high' => '#fed7aa', 'critical' => '#fecaca'];
                                    echo $colors[$item->priority] ?? '#f1f5f9';
                                @endphp;">
                                <i class="bi {{ ['bi-circle', 'bi-circle-fill', 'bi-arrow-up-circle-fill', 'bi-exclamation-octagon-fill'][array_search($item->priority, ['low', 'medium', 'high', 'critical'])] }}"></i>
                            </div>
                            <div>
                                <div style="font-size:13px;font-weight:600;">{{ ucfirst($item->priority) }}</div>
                                <div style="font-size:11px;color:#64748b;">{{ $item->total }} requests</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="bc-card mt-4">
        <div class="bc-card-header">
            <h3>Recent Maintenance Requests</h3>
        </div>
        <div class="bc-card-body flush">
            <div class="bc-table-wrap">
                <table class="bc-table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Infrastructure</th>
                            <th>Issue</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($list as $request)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.maintenance.show', $request) }}" class="row-title" style="color:var(--bc-blue);">
                                        {{ $request->request_number }}
                                    </a>
                                </td>
                                <td>
                                    <div class="row-title">{{ $request->infrastructure?->name }}</div>
                                    <div class="row-sub">{{ $request->infrastructure?->type->name }}</div>
                                </td>
                                <td style="max-width:220px;"><span style="font-size:12.5px;">{{ Str::limit($request->title, 44) }}</span></td>
                                <td><x-priority-badge :priority="$request->priority" /></td>
                                <td><x-status-badge :status="$request->status" /></td>
                                <td style="white-space:nowrap;font-size:12.5px;color:#64748b;">{{ $request->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-empty-state icon="bi-wrench" title="No requests" text="No maintenance requests found matching the filters." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-pagination :paginator="$list" />
@endsection
