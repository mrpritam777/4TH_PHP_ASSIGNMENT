@extends('layouts.admin')

@section('title', $road->name)

@section('content')
    <x-page-header :title="$road->name" :breadcrumbs="[[ 'label' => 'Roads', 'url' => route('admin.roads.index') ]]">
        <a href="{{ route('admin.roads.edit', $road) }}" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-pencil"></i> Edit</a>
        <a href="{{ route('admin.maintenance.create', ['infrastructure_id' => $road->id]) }}" class="btn-bc btn-bc-primary btn-bc-sm"><i class="bi bi-wrench"></i> New Maintenance</a>
    </x-page-header>

    <div class="bc-hero-card">
        <img src="{{ $road->imageUrl() }}" alt="{{ $road->name }}">
        <div class="bc-hero-overlay">
            <div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <h1>{{ $road->name }}</h1>
                    <span class="bc-badge {{ $road->getConditionBadgeClass() }}" style="font-size:13px;padding:6px 14px;"><span class="dot"></span> {{ ucfirst($road->condition) }}</span>
                </div>
                <div class="bc-hero-meta">
                    <span><i class="bi bi-hash"></i>{{ $road->code }}</span>
                    <span><i class="bi bi-geo-alt"></i>{{ $road->location }}</span>
                    <span><i class="bi bi-signpost"></i>{{ $road->roadDetails?->road_type }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <h3><i class="bi bi-signpost me-2" style="color:var(--bc-blue);"></i>Road Details</h3>
                </div>
                <div class="bc-card-body">
                    <div class="bc-info-grid">
                        <div class="bc-info-item">
                            <span class="label">Road Type</span>
                            <span class="value">{{ $road->roadDetails?->road_type ?? '—' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Construction Year</span>
                            <span class="value">{{ $road->construction_year ?? '—' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Start Point</span>
                            <span class="value">{{ $road->roadDetails?->start_point ?? '—' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">End Point</span>
                            <span class="value">{{ $road->roadDetails?->end_point ?? '—' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Total Length</span>
                            <span class="value">{{ $road->roadDetails?->length ? number_format($road->roadDetails->length, 2) . ' km' : '—' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Width</span>
                            <span class="value">{{ $road->roadDetails?->width ? number_format($road->roadDetails->width, 2) . ' m' : '—' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Lanes</span>
                            <span class="value">{{ $road->roadDetails?->lanes ?? '—' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Condition</span>
                            <span class="value"><x-condition-badge :condition="$road->condition" /></span>
                        </div>
                    </div>
                    @if ($road->description)
                        <div class="mt-4">
                            <h4 style="font-size:14px;margin-bottom:8px;">Description</h4>
                            <p style="font-size:13.5px;color:#475569;line-height:1.7;margin:0;">{{ $road->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <h3><i class="bi bi-wrench me-2" style="color:var(--bc-blue);"></i>Maintenance Records</h3>
                    <a href="{{ route('admin.maintenance.create', ['infrastructure_id' => $road->id]) }}" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-plus-lg"></i> Add</a>
                </div>
                <div class="bc-card-body flush">
                    @forelse ($road->maintenanceRequests->sortByDesc('created_at')->take(5) as $request)
                        <a href="{{ route('admin.maintenance.show', $request) }}" class="d-block p-3" style="border-bottom:1px solid #f1f5f9;">
                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <strong style="font-size:12.5px;color:var(--bc-blue);">{{ $request->request_number }}</strong>
                                <x-status-badge :status="$request->status" />
                            </div>
                            <div style="font-size:12.5px;margin-top:4px;color:#475569;">{{ Str::limit($request->title, 50) }}</div>
                            <small style="color:#94a3b8;">{{ $request->created_at->format('d M Y') }}</small>
                        </a>
                    @empty
                        <div class="bc-empty py-4">
                            <i class="bi bi-wrench"></i>
                            <h4>No maintenance records</h4>
                            <p>No maintenance has been recorded for this road yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
