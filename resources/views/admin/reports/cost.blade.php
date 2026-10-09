@extends('layouts.admin')

@section('title', 'Cost Report')

@section('content')
    <x-page-header title="Cost Report" subtitle="Analysis of maintenance expenditure and budgeting.">
    </x-page-header>

    <form class="bc-filter-bar" method="GET" action="{{ route('admin.reports.cost') }}">
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
                <label class="bc-form-label">Type</label>
                <select name="type" class="bc-select">
                    <option value="">All Types</option>
                    @foreach (['bridge' => 'Bridges', 'road' => 'Roads', 'building' => 'Buildings', 'hospital' => 'Hospitals'] as $val => $lbl)
                        <option value="{{ $val }}" @selected($type === $val)>{{ $lbl }}</option>
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
        @if ($type || $from || $to)
            <a href="{{ route('admin.reports.cost') }}" class="btn-bc btn-bc-ghost btn-bc-sm"><i class="bi bi-x-circle"></i> Reset</a>
        @endif
    </form>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <h3>Cost Summary</h3>
                </div>
                <div class="bc-card-body">
                    <div class="bc-info-grid" style="grid-template-columns:repeat(2, 1fr); gap:16px;">
                        <div class="bc-info-item">
                            <span class="label">Total Estimated Cost</span>
                            <span class="value">₹{{ number_format($summary['estimated']) }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Total Actual Cost</span>
                            <span class="value">₹{{ number_format($summary['actual']) }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Completed Works</span>
                            <span class="value">{{ $summary['completed'] }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Cost Variance</span>
                            <span class="value" @class(['text-danger' => $summary['actual'] > $summary['estimated']])>
                                @php
                                    $diff = $summary['actual'] - $summary['estimated'];
                                    $pct = $summary['estimated'] > 0 ? round(($diff / $summary['estimated']) * 100) : 0;
                                @endphp
                                ₹{{ number_format(abs($diff)) }} ({@if($diff > 0)+@else@-@endif{{ $pct }}%)
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <h3>Monthly Cost Trend</h3>
                </div>
                <div class="bc-card-body">
                    @if ($monthly->isNotEmpty())
                        <div style="height:260px;">
                            <canvas id="bcCostChart"></canvas>
                        </div>
                    @else
                        <div class="bc-empty py-4">
                            <i class="bi bi-currency-rupee"></i>
                            <h4>No cost data</h4>
                            <p>No completed maintenance records found for the selected filters.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-4">
        <div class="col-md-6">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <h3>Top 10 Costliest Assets</h3>
                </div>
                <div class="bc-card-body">
                    @if ($byInfrastructure->isNotEmpty())
                        <div class="bc-table-wrap">
                            <table class="bc-table">
                                <thead>
                                    <tr>
                                        <th>Asset</th>
                                        <th>Type</th>
                                        <th>Total Cost (₹)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($byInfrastructure as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="{{ $item->infrastructure->imageUrl() }}" class="bc-avatar" alt="">
                                                    <div class="row-title">{{ $item->infrastructure->name }}</div>
                                                </div>
                                            </td>
                                            <td>{{ $item->infrastructure->type->name }}</td>
                                            <td style="text-align:right;font-weight:600;">₹{{ number_format($item->total) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="bc-empty py-4">
                            <i class="bi bi-graph-down"></i>
                            <h4>No cost data</h4>
                            <p>No completed maintenance records with costs found.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <h3>Recent Maintenance Activity</h3>
                </div>
                <div class="bc-card-body flush">
                    <div class="bc-table-wrap">
                        <table class="bc-table">
                            <thead>
                                <tr>
                                    <th>Request ID</th>
                                    <th>Infrastructure</th>
                                    <th>Cost (₹)</th>
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
                                        <td style="text-align:right;">{{ $request->actual_cost ? '₹' . number_format($request->actual_cost) : '—' }}</td>
                                        <td style="white-space:nowrap;font-size:12.5px;color:#64748b;">{{ $request->created_at->format('d M Y') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4"><x-empty-state icon="bi-journal-text" title="No activity" text="No maintenance records found." /></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-pagination :paginator="$list" />
    @endsection

@push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
        <script>
            // Cost trend line chart
            (function () {
                var el = document.getElementById('bcCostChart');
                if (!el) return;
                var data = @json($monthly);
                new Chart(el, {
                    type: 'line',
                    data: {
                        labels: data.map(function (m) { return m.month; }),
                        datasets: [{
                            label: 'Monthly Cost (₹)',
                            data: data.map(function (m) { return parseFloat(m.total); }),
                            borderColor: '#7c3aed',
                            backgroundColor: 'rgba(124,58,237,.1)',
                            tension: .4,
                            pointRadius: 4,
                            pointBackgroundColor: '#7c3aed',
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (ctx) {
                                        return '₹' + Number(ctx.parsed.y).toLocaleString('en-IN');
                                    }
                                }
                            }
                        },
                        scales: {
                            y: { beginAtZero: true, ticks: { callback: function (v) { return '₹' + Number(v).toLocaleString('en-IN'); } } }
                        }
                    }
                });
            })();
        </script>
    @endpush