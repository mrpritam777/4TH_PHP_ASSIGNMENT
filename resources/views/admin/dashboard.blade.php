@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    {{-- Welcome --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4"
         style="background:linear-gradient(120deg,#0b2447,#12325a 70%,#1b4375);border-radius:16px;padding:26px 30px;box-shadow:0 8px 30px rgba(11,36,71,.25);">
        <div>
            <h1 style="color:#fff;font-size:24px;font-weight:700;margin:0;">Welcome back, {{ auth()->user()->name }}</h1>
            <p style="color:#b8cde4;margin:6px 0 0;font-size:13.5px;">Monitor and manage your infrastructure from one centralized platform.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.bridges.create') }}" class="btn-bc btn-bc-primary"><i class="bi bi-plus-lg"></i> Add Bridge</a>
            <a href="{{ route('admin.infrastructures.index') }}" class="btn-bc" style="background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.25);color:#fff;"><i class="bi bi-diagram-3"></i> All Infrastructure</a>
            <a href="{{ route('admin.maintenance.create') }}" class="btn-bc" style="background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.25);color:#fff;"><i class="bi bi-wrench-adjustable"></i> Create Maintenance Request</a>
        </div>
    </div>

    {{-- Stats row 1 --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-3">
            <x-stat-card icon="bi-signpost-split" icon-class="ic-blue" :value="$stats['bridges']" label="Total Bridges" trend="Bridge network" trend-class="trend-flat" />
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <x-stat-card icon="bi-signpost" icon-class="ic-green" :value="$stats['roads']" label="Total Roads" trend="Road network" trend-class="trend-flat" />
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <x-stat-card icon="bi-buildings" icon-class="ic-purple" :value="$stats['buildings']" label="Total Buildings" trend="Managed assets" trend-class="trend-flat" />
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <x-stat-card icon="bi-hospital" icon-class="ic-red" :value="$stats['hospitals']" label="Total Hospitals" trend="Health assets" trend-class="trend-flat" />
        </div>
    </div>

    {{-- Stats row 2 --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <x-stat-card icon="bi-wrench-adjustable-circle" icon-class="ic-navy" :value="$stats['total_requests']" label="Maintenance Requests" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="bi-hourglass-split" icon-class="ic-yellow" :value="$stats['pending']" label="Pending Maintenance" trend="Needs attention" trend-class="trend-down" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="bi-activity" icon-class="ic-blue" :value="$stats['in_progress']" label="In Progress" trend="Active work" trend-class="trend-flat" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="bi-check2-circle" icon-class="ic-green" :value="$stats['completed']" label="Completed" trend="All time" trend-class="trend-up" />
        </div>
    </div>

    <div class="row g-3 mb-4">
        {{-- Condition overview --}}
        <div class="col-lg-4">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <div>
                        <h3>Infrastructure Condition</h3>
                        <div class="sub">Live overview across all assets</div>
                    </div>
                    <span class="bc-badge badge-blue"><i class="bi bi-pie-chart"></i> {{ $stats['total_infrastructure'] }} assets</span>
                </div>
                <div class="bc-card-body d-flex align-items-center gap-4">
                    <div style="position:relative;width:190px;height:190px;flex-shrink:0;">
                        <canvas id="bcConditionChart"></canvas>
                        <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                            <strong style="font-size:26px;letter-spacing:-1px;">{{ $stats['total_infrastructure'] }}</strong>
                            <small style="color:#64748b;">Total</small>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        @php
                            $totalCondition = max(1, array_sum($condition));
                            $colors = ['Good' => '#16a34a', 'Average' => '#ca8a04', 'Poor' => '#ea580c', 'Critical' => '#dc2626'];
                        @endphp
                        @foreach ($condition as $label => $count)
                            <div class="d-flex align-items-center gap-2 mb-2" style="font-size:13px;">
                                <span style="width:10px;height:10px;border-radius:3px;background:{{ $colors[$label] }};flex-shrink:0;"></span>
                                <span class="text-muted flex-grow-1">{{ $label }}</span>
                                <strong>{{ $count }}</strong>
                                <span style="color:#94a3b8;width:44px;text-align:right;">{{ round(($count / $totalCondition) * 100) }}%</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Maintenance analytics --}}
        <div class="col-lg-8">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <div>
                        <h3>Maintenance Analytics</h3>
                        <div class="sub">Monthly requests, completions and cost (last 6 months)</div>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="bc-badge badge-blue"><i class="bi bi-bar-chart"></i> Requests</span>
                        <span class="bc-badge badge-green"><i class="bi bi-check2"></i> Completed</span>
                        <span class="bc-badge badge-yellow"><i class="bi bi-hourglass"></i> Pending</span>
                    </div>
                </div>
                <div class="bc-card-body">
                    <div style="height:290px;">
                        <canvas id="bcMaintenanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Recent maintenance requests --}}
        <div class="col-lg-8">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <div>
                        <h3>Recent Maintenance Requests</h3>
                        <div class="sub">Latest activity across the network</div>
                    </div>
                    <a href="{{ route('admin.maintenance.index') }}" class="btn-bc btn-bc-outline btn-bc-sm">View all <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="bc-card-body flush">
                    <div class="bc-table-wrap">
                        <table class="bc-table">
                            <thead>
                                <tr>
                                    <th>Request</th>
                                    <th>Infrastructure</th>
                                    <th>Priority</th>
                                    <th>Assigned</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentRequests as $request)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.maintenance.show', $request) }}" class="row-title" style="color:var(--bc-blue);">
                                                {{ $request->request_number }}
                                            </a>
                                            <div class="row-sub">{{ Str::limit($request->title, 34) }}</div>
                                        </td>
                                        <td>
                                            <div class="row-title">{{ $request->infrastructure?->name }}</div>
                                            <div class="row-sub">{{ $request->infrastructure?->type?->name }}</div>
                                        </td>
                                        <td><x-priority-badge :priority="$request->priority" /></td>
                                        <td>
                                            @if ($request->assignedStaff)
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="bc-avatar-fallback">{{ strtoupper(substr($request->assignedStaff->name, 0, 1)) }}</span>
                                                    <span style="font-size:12.5px;">{{ $request->assignedStaff->name }}</span>
                                                </div>
                                            @else
                                                <span class="text-muted" style="font-size:12.5px;">—</span>
                                            @endif
                                        </td>
                                        <td><x-status-badge :status="$request->status" /></td>
                                        <td style="font-size:12.5px;color:#64748b;white-space:nowrap;">{{ $request->created_at->format('d M Y') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6"><x-empty-state icon="bi-wrench" title="No maintenance requests yet" text="Create your first maintenance request to start tracking work." action-label="Create request" action-url="{{ route('admin.maintenance.create') }}" /></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Critical infrastructure --}}
        <div class="col-lg-4">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <div>
                        <h3>Critical Alerts</h3>
                        <div class="sub">Infrastructure in critical condition</div>
                    </div>
                    @if ($criticalInfra->count())
                        <span class="bc-badge badge-red"><i class="bi bi-exclamation-triangle-fill"></i> {{ $criticalInfra->count() }}</span>
                    @endif
                </div>
                <div class="bc-card-body flush">
                    @forelse ($criticalInfra as $infra)
                        <a href="{{ $infra->getDetailsUrl() }}" class="d-flex gap-3 p-3" style="border-bottom:1px solid #f1f5f9;transition:background .12s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                            <img src="{{ $infra->imageUrl() }}" class="bc-thumb" alt="">
                            <div class="flex-grow-1" style="min-width:0;">
                                <strong style="font-size:13.5px;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $infra->name }}</strong>
                                <small style="color:#64748b;">{{ $infra->type->name }} • {{ $infra->location }}</small>
                                <div class="mt-1"><x-condition-badge :condition="$infra->condition" /></div>
                            </div>
                            <i class="bi bi-chevron-right text-muted mt-2"></i>
                        </a>
                    @empty
                        <div class="bc-empty py-5">
                            <i class="bi bi-shield-check" style="color:#16a34a;"></i>
                            <h4>All clear</h4>
                            <p>No infrastructure is currently in critical condition.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        // Condition doughnut
        (function () {
            var el = document.getElementById('bcConditionChart');
            if (!el) return;
            new Chart(el, {
                type: 'doughnut',
                data: {
                    labels: ['Good', 'Average', 'Poor', 'Critical'],
                    datasets: [{
                        data: @json(array_values($condition)),
                        backgroundColor: ['#16a34a', '#ca8a04', '#ea580c', '#dc2626'],
                        borderWidth: 0,
                        hoverOffset: 6
                    }]
                },
                options: {
                    cutout: '72%',
                    plugins: { legend: { display: false } },
                    maintainAspectRatio: false
                }
            });
        })();

        // Maintenance analytics
        (function () {
            var el = document.getElementById('bcMaintenanceChart');
            if (!el) return;
            var data = @json($chart);
            new Chart(el, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'Requests',
                            data: data.requests,
                            backgroundColor: 'rgba(37,99,235,.85)',
                            borderRadius: 6,
                            barPercentage: .55
                        },
                        {
                            label: 'Completed',
                            data: data.completed,
                            backgroundColor: 'rgba(22,163,74,.85)',
                            borderRadius: 6,
                            barPercentage: .55
                        },
                        {
                            label: 'Pending',
                            data: data.pending,
                            backgroundColor: 'rgba(202,138,4,.85)',
                            borderRadius: 6,
                            barPercentage: .55
                        },
                        {
                            label: 'Cost (₹)',
                            data: data.cost,
                            type: 'line',
                            borderColor: '#7c3aed',
                            backgroundColor: 'rgba(124,58,237,.12)',
                            fill: true,
                            tension: .4,
                            pointRadius: 4,
                            pointBackgroundColor: '#7c3aed',
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { usePointStyle: true, boxWidth: 8, font: { size: 11.5 } }
                        },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    if (ctx.dataset.label === 'Cost (₹)') {
                                        return 'Cost: ₹' + Number(ctx.parsed.y).toLocaleString('en-IN');
                                    }
                                    return ctx.dataset.label + ': ' + ctx.parsed.y;
                                }
                            }
                        }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                        y1: {
                            position: 'right',
                            beginAtZero: true,
                            grid: { display: false },
                            ticks: { callback: function (v) { return '₹' + Number(v).toLocaleString('en-IN'); } }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        })();
    </script>
@endpush
