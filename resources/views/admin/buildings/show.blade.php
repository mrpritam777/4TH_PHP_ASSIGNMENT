@extends('layouts.admin')

@section('title', $building->name)

@section('content')
    <x-page-header :title="$building->name" :breadcrumbs="[[ 'label' => 'Buildings', 'url' => route('admin.buildings.index') ]]">
        <a href="{{ route('admin.buildings.edit', $building) }}" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-pencil"></i> Edit</a>
        <a href="{{ route('admin.maintenance.create', ['infrastructure_id' => $building->id]) }}" class="btn-bc btn-bc-primary btn-bc-sm"><i class="bi bi-wrench"></i> New Maintenance</a>
    </x-page-header>

    <div class="bc-hero-card">
        <img src="{{ $building->imageUrl() }}" alt="{{ $building->name }}">
        <div class="bc-hero-overlay">
            <div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <h1>{{ $building->name }}</h1>
                    <span class="bc-badge {{ $building->getConditionBadgeClass() }}" style="font-size:13px;padding:6px 14px;"><span class="dot"></span> {{ ucfirst($building->condition) }}</span>
                </div>
                <div class="bc-hero-meta">
                    <span><i class="bi bi-hash"></i>{{ $building->code }}</span>
                    <span><i class="bi bi-geo-alt"></i>{{ Str::limit($building->location, 50) }}</span>
                    <span><i class="bi bi-buildings"></i>{{ $building->buildingDetails?->building_type }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <h3><i class="bi bi-buildings me-2" style="color:var(--bc-blue);"></i>Building Details</h3>
                </div>
                <div class="bc-card-body">
                    <div class="bc-info-grid">
                        <div class="bc-info-item">
                            <span class="label">Building Type</span>
                            <span class="value">{{ $building->buildingDetails?->building_type ?? '—' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Construction Year</span>
                            <span class="value">{{ $building->construction_year ?? '—' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Number of Floors</span>
                            <span class="value">{{ $building->buildingDetails?->floors ?? '—' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Area</span>
                            <span class="value">{{ $building->buildingDetails?->area ? number_format($building->buildingDetails->area) . ' sq.ft' : '—' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Owner Department</span>
                            <span class="value">{{ $building->buildingDetails?->department ?? '—' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Condition</span>
                            <span class="value"><x-condition-badge :condition="$building->condition" /></span>
                        </div>
                    </div>
                    @if ($building->description)
                        <div class="mt-4">
                            <h4 style="font-size:14px;margin-bottom:8px;">Description</h4>
                            <p style="font-size:13.5px;color:#475569;line-height:1.7;margin:0;">{{ $building->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <h3><i class="bi bi-wrench me-2" style="color:var(--bc-blue);"></i>Maintenance Records</h3>
                    <a href="{{ route('admin.maintenance.create', ['infrastructure_id' => $building->id]) }}" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-plus-lg"></i> Add</a>
                </div>
                <div class="bc-card-body flush">
                    @forelse ($building->maintenanceRequests->sortByDesc('created_at')->take(5) as $request)
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
                            <p>No maintenance has been recorded for this building yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
