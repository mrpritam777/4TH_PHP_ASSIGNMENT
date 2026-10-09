@extends('layouts.admin')

@section('title', $hospital->name)

@section('content')
    <x-page-header :title="$hospital->name" :breadcrumbs="[[ 'label' => 'Hospitals', 'url' => route('admin.hospitals.index') ]]">
        <a href="{{ route('admin.hospitals.edit', $hospital) }}" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-pencil"></i> Edit</a>
        <a href="{{ route('admin.maintenance.create', ['infrastructure_id' => $hospital->id]) }}" class="btn-bc btn-bc-primary btn-bc-sm"><i class="bi bi-wrench"></i> New Maintenance</a>
    </x-page-header>

    <div class="bc-hero-card">
        <img src="{{ $hospital->imageUrl() }}" alt="{{ $hospital->name }}">
        <div class="bc-hero-overlay">
            <div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <h1>{{ $hospital->name }}</h1>
                    <span class="bc-badge {{ $hospital->getConditionBadgeClass() }}" style="font-size:13px;padding:6px 14px;"><span class="dot"></span> {{ ucfirst($hospital->condition) }}</span>
                    @if ($hospital->hospitalDetails?->emergency_facility)
                        <span class="bc-badge badge-red" style="font-size:13px;padding:6px 14px;"><i class="bi bi-ambulance"></i> 24×7 Emergency</span>
                    @endif
                </div>
                <div class="bc-hero-meta">
                    <span><i class="bi bi-hash"></i>{{ $hospital->code }}</span>
                    <span><i class="bi bi-geo-alt"></i>{{ Str::limit($hospital->location, 50) }}</span>
                    <span><i class="bi bi-hospital"></i>{{ $hospital->hospitalDetails?->hospital_type }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <h3><i class="bi bi-hospital me-2" style="color:var(--bc-blue);"></i>Hospital Details</h3>
                </div>
                <div class="bc-card-body">
                    <div class="bc-info-grid">
                        <div class="bc-info-item">
                            <span class="label">Hospital Type</span>
                            <span class="value">{{ $hospital->hospitalDetails?->hospital_type ?? '—' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Number of Beds</span>
                            <span class="value">{{ $hospital->hospitalDetails?->beds ?? 0 }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Construction Year</span>
                            <span class="value">{{ $hospital->construction_year ?? '—' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Emergency Facility</span>
                            <span class="value">
                                @if ($hospital->hospitalDetails?->emergency_facility)
                                    <span class="bc-badge badge-red"><i class="bi bi-ambulance"></i> 24×7 Available</span>
                                @else
                                    <span class="bc-badge badge-gray">Not available</span>
                                @endif
                            </span>
                        </div>
                        <div class="bc-info-item" style="grid-column:span 2;">
                            <span class="label">Departments</span>
                            <span class="value" style="font-size:13px;">{{ $hospital->hospitalDetails?->departments ?? '—' }}</span>
                        </div>
                    </div>

                    <div class="mt-4 p-3" style="background:#f8fafc;border-radius:12px;border:1px solid #f1f5f9;">
                        <h4 style="font-size:14px;margin-bottom:10px;"><i class="bi bi-telephone me-2" style="color:var(--bc-red);"></i>Contact Information</h4>
                        <div class="row g-2" style="font-size:13px;">
                            <div class="col-md-4"><i class="bi bi-telephone me-2 text-muted"></i>{{ $hospital->hospitalDetails?->emergency_phone ?? '—' }}</div>
                            <div class="col-md-4"><i class="bi bi-envelope me-2 text-muted"></i>{{ $hospital->hospitalDetails?->email ?? '—' }}</div>
                            <div class="col-md-4"><i class="bi bi-globe me-2 text-muted"></i>{{ $hospital->hospitalDetails?->website ?? '—' }}</div>
                        </div>
                    </div>

                    @if ($hospital->description)
                        <div class="mt-4">
                            <h4 style="font-size:14px;margin-bottom:8px;">Description</h4>
                            <p style="font-size:13.5px;color:#475569;line-height:1.7;margin:0;">{{ $hospital->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <h3><i class="bi bi-wrench me-2" style="color:var(--bc-blue);"></i>Maintenance Records</h3>
                    <a href="{{ route('admin.maintenance.create', ['infrastructure_id' => $hospital->id]) }}" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-plus-lg"></i> Add</a>
                </div>
                <div class="bc-card-body flush">
                    @forelse ($hospital->maintenanceRequests->sortByDesc('created_at')->take(5) as $request)
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
                            <p>No maintenance has been recorded for this hospital yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
