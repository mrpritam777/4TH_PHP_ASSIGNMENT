@extends('layouts.admin')

@section('title', 'Infrastructure Report')

@section('content')
    <x-page-header title="Infrastructure Report" subtitle="Summary of all infrastructure assets in the network.">
    </x-page-header>

    <form class="bc-filter-bar" method="GET" action="{{ route('admin.reports.infrastructure') }}">
        <div class="bc-search-box">
            <i class="bi bi-search"></i>
            <input type="text" name="search" class="bc-input" placeholder="Search by name or code..." value="{{ $search }}">
        </div>
        <button type="submit" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-search"></i> Search</button>
        @if ($search)
            <a href="{{ route('admin.reports.infrastructure') }}" class="btn-bc btn-bc-ghost btn-bc-sm"><i class="bi bi-x-circle"></i> Clear</a>
        @endif
    </form>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <h3>Asset Summary</h3>
                </div>
                <div class="bc-card-body">
                    <div class="bc-info-grid" style="grid-template-columns:repeat(2, 1fr); gap:16px;">
                        <div class="bc-info-item">
                            <span class="label">Total Assets</span>
                            <span class="value">{{ $totals['all'] }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Bridges</span>
                            <span class="value">{{ $totals['bridge'] }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Roads</span>
                            <span class="value">{{ $totals['road'] }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Buildings</span>
                            <span class="value">{{ $totals['building'] }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Hospitals</span>
                            <span class="value">{{ $totals['hospital'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <h3>Condition Overview</h3>
                </div>
                <div class="bc-card-body">
                    <div class="d-flex flex-wrap gap-3" style="margin-bottom:24px;">
                        @php
                            $conditionRows = $conditionSummary->mapWithKeys(fn ($row) => [$row->condition => (int) $row->total]);
                            $totalCondition = max(1, $conditionRows->sum());
                        @endphp
                        @foreach ($conditionRows as $label => $count)
                            <div class="d-flex align-items-center gap-2">
                                <span style="width:12px;height:12px;border-radius:3px;background:@php
                                    $colors = ['excellent' => '#16a34a', 'good' => '#16a34a', 'fair' => '#ca8a04', 'poor' => '#ea580c', 'critical' => '#dc2626'];
                                    echo $colors[$label] ?? '#64748b';
                                @endphp;flex-shrink:0;"></span>
                                <div>
                                    <div style="font-size:12px;">{{ $label }}</div>
                                    <div style="font-size:10px;color:#64748b;">{{ round(($count / $totalCondition) * 100) }}%</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-4">
        <div class="col-md-6">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <h3>Type Breakdown</h3>
                </div>
                <div class="bc-card-body">
                    @foreach ($typeBreakdown as $type => $data)
                        <div class="p-3 mb-3" style="background:#f8fafc;border-radius:12px;border:1px solid #f1f5f9;">
                            <div class="d-flex justify-content-between align-items-start">
                                <h4 style="margin:0;font-size:14px;">{{ $type }}</h4>
                                <span class="bc-badge badge-navy">{{ $data['total'] }}</span>
                            </div>
                            <div class="mt-2">
                                <div class="d-flex gap-3">
                                    <span class="bc-badge badge-green">Good: {{ $data['good'] }}</span>
                                    <span class="bc-badge badge-orange">Attention: {{ $data['attention'] }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <h3>Recent Activity</h3>
                </div>
                <div class="bc-card-body">
                    <div class="bc-table-wrap">
                        <table class="bc-table">
                            <thead>
                                <tr>
                                    <th>Asset</th>
                                    <th>Action</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($list as $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ $item->imageUrl() }}" class="bc-avatar" alt="">
                                                <div>
                                                    <div class="row-title">{{ $item->name }}</div>
                                                    <div class="row-sub">{{ $item->type->name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Viewed in report</td>
                                        <td style="white-space:nowrap;">{{ $item->created_at->format('d M Y') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3"><x-empty-state icon="bi-journal-text" title="No activity" text="No infrastructure assets found matching the filters." /></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
