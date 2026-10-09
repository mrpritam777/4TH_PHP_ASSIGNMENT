@extends('layouts.admin')

@section('title', $bridge->name)

@section('content')
    <x-page-header :title="$bridge->name" :breadcrumbs="[[ 'label' => 'Bridges', 'url' => route('admin.bridges.index') ]]">
        <a href="{{ route('admin.bridges.edit', $bridge) }}" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-pencil"></i> Edit</a>
        <a href="{{ route('admin.bridges.history', $bridge) }}" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-clock-history"></i> History</a>
        <a href="{{ route('admin.maintenance.create', ['infrastructure_id' => $bridge->id]) }}" class="btn-bc btn-bc-primary btn-bc-sm"><i class="bi bi-wrench"></i> New Maintenance</a>
    </x-page-header>

    {{-- Hero --}}
    <div class="bc-hero-card">
        <img src="{{ $bridge->imageUrl() }}" alt="{{ $bridge->name }}">
        <div class="bc-hero-overlay">
            <div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <h1>{{ $bridge->name }}</h1>
                    <span class="bc-badge {{ $bridge->getConditionBadgeClass() }}" style="font-size:13px;padding:6px 14px;">
                        <span class="dot"></span> {{ ucfirst($bridge->condition) }}
                    </span>
                </div>
                <div class="bc-hero-meta">
                    <span><i class="bi bi-hash"></i>{{ $bridge->code }}</span>
                    <span><i class="bi bi-geo-alt"></i>{{ $bridge->location }}</span>
                    <span><i class="bi bi-calendar3"></i>Built {{ $bridge->construction_year ?? '—' }}</span>
                    <span><i class="bi bi-signpost-split"></i>{{ $bridge->bridgeDetails?->bridge_type }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        {{-- Overview --}}
        <div class="col-lg-6">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <h3><i class="bi bi-signpost-split me-2" style="color:var(--bc-blue);"></i>Bridge Overview</h3>
                </div>
                <div class="bc-card-body">
                    <div class="bc-info-grid">
                        <div class="bc-info-item">
                            <span class="label">Bridge Type</span>
                            <span class="value">{{ $bridge->bridgeDetails?->bridge_type ?? '—' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Length</span>
                            <span class="value">{{ $bridge->bridgeDetails?->length ? number_format($bridge->bridgeDetails->length, 2) . ' m' : '—' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Width</span>
                            <span class="value">{{ $bridge->bridgeDetails?->width ? number_format($bridge->bridgeDetails->width, 2) . ' m' : '—' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Construction Year</span>
                            <span class="value">{{ $bridge->construction_year ?? '—' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Number of Lanes</span>
                            <span class="value">{{ $bridge->bridgeDetails?->lanes ?? '—' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Load Capacity</span>
                            <span class="value">{{ $bridge->bridgeDetails?->load_capacity ?? '—' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Material</span>
                            <span class="value">{{ $bridge->bridgeDetails?->material ?? '—' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Last Inspection</span>
                            <span class="value">{{ $bridge->bridgeDetails?->last_inspection_date?->format('d M Y') ?? '—' }}</span>
                        </div>
                    </div>

                    @if ($bridge->description)
                        <div class="mt-4">
                            <h4 style="font-size:14px;margin-bottom:8px;">Description</h4>
                            <p style="font-size:13.5px;color:#475569;line-height:1.7;margin:0;">{{ $bridge->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Location + Condition --}}
        <div class="col-lg-6">
            <div class="bc-card mb-3">
                <div class="bc-card-header">
                    <h3><i class="bi bi-geo-alt me-2" style="color:var(--bc-blue);"></i>Location Information</h3>
                </div>
                <div class="bc-card-body">
                    <div class="bc-info-grid">
                        <div class="bc-info-item" style="grid-column:span 2;">
                            <span class="label">Address</span>
                            <span class="value">{{ $bridge->address ?? $bridge->location ?? '—' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">District</span>
                            <span class="value">{{ $bridge->location ? (explode(',', $bridge->location)[1] ?? '—') : '—' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">City</span>
                            <span class="value">{{ $bridge->location ? (explode(',', $bridge->location)[2] ?? '—') : '—' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Latitude</span>
                            <span class="value">{{ $bridge->latitude ?? '—' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Longitude</span>
                            <span class="value">{{ $bridge->longitude ?? '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bc-card">
                <div class="bc-card-header">
                    <h3><i class="bi bi-clipboard2-check me-2" style="color:var(--bc-blue);"></i>Current Condition</h3>
                    <x-condition-badge :condition="$bridge->condition" />
                </div>
                <div class="bc-card-body">
                    @php
                        $conditionPct = ['excellent' => 100, 'good' => 80, 'fair' => 55, 'poor' => 30, 'critical' => 10];
                        $barClass = ['excellent' => 'green', 'good' => 'green', 'fair' => 'yellow', 'poor' => 'orange', 'critical' => 'red'];
                        $safetyBadge = ['safe' => 'badge-green', 'caution' => 'badge-orange', 'unsafe' => 'badge-red'];
                    @endphp

                    <div class="bc-condition-row">
                        <div class="head">
                            <span>Structural Condition</span>
                            <span>{{ ucfirst($bridge->bridgeDetails?->structural_condition ?? 'good') }}</span>
                        </div>
                        <div class="bc-progress">
                            <div class="bc-progress-bar {{ $barClass[$bridge->bridgeDetails?->structural_condition ?? 'good'] }}"
                                 style="width:{{ $conditionPct[$bridge->bridgeDetails?->structural_condition ?? 'good'] }}%;"></div>
                        </div>
                    </div>

                    <div class="bc-condition-row">
                        <div class="head">
                            <span>Road Surface Condition</span>
                            <span>{{ ucfirst($bridge->bridgeDetails?->surface_condition ?? 'good') }}</span>
                        </div>
                        <div class="bc-progress">
                            <div class="bc-progress-bar {{ $barClass[$bridge->bridgeDetails?->surface_condition ?? 'good'] }}"
                                 style="width:{{ $conditionPct[$bridge->bridgeDetails?->surface_condition ?? 'good'] }}%;"></div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-3">
                        <div>
                            <span class="label" style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#94a3b8;">Safety Status</span>
                            <div class="mt-1">
                                <span class="bc-badge {{ $safetyBadge[$bridge->bridgeDetails?->safety_status ?? 'safe'] }}">
                                    <span class="dot"></span>
                                    {{ ucfirst($bridge->bridgeDetails?->safety_status ?? 'safe') }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <span class="label" style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#94a3b8;">Last Inspection</span>
                            <div class="mt-1" style="font-weight:600;font-size:14px;">{{ $bridge->bridgeDetails?->last_inspection_date?->format('d M Y') ?? 'Not recorded' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Gallery --}}
        <div class="col-lg-5">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <h3><i class="bi bi-images me-2" style="color:var(--bc-blue);"></i>Gallery</h3>
                </div>
                <div class="bc-card-body">
                    <div class="row g-2">
                        @foreach ($gallery as $img)
                            <div class="col-6">
                                <img src="{{ $img }}" alt="Gallery" style="width:100%;height:120px;object-fit:cover;border-radius:10px;border:1px solid #e2e8f0;">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Contacts --}}
        <div class="col-lg-4">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <h3><i class="bi bi-person-lines-fill me-2" style="color:var(--bc-blue);"></i>Contacts</h3>
                    <a href="{{ route('admin.contacts.create') }}" class="btn-bc btn-bc-outline btn-bc-sm">Add</a>
                </div>
                <div class="bc-card-body flush">
                    @forelse ($bridge->contacts as $contact)
                        <div class="d-flex gap-3 p-3" style="border-bottom:1px solid #f1f5f9;">
                            <span class="bc-avatar-fallback">{{ strtoupper(substr($contact->name, 0, 1)) }}</span>
                            <div style="min-width:0;">
                                <strong style="font-size:13.5px;display:block;">{{ $contact->name }}</strong>
                                <small style="color:#64748b;">{{ $contact->designation ?? $contact->organization }}</small>
                                <div class="mt-1" style="font-size:12px;color:#64748b;">
                                    @if ($contact->phone) <i class="bi bi-telephone"></i> {{ $contact->phone }} @endif
                                    @if ($contact->email) <i class="bi bi-envelope ms-2"></i> {{ $contact->email }} @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="bc-empty py-4">
                            <i class="bi bi-person-dash"></i>
                            <h4>No contacts</h4>
                            <p>No contacts are linked to this bridge yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Recent maintenance --}}
        <div class="col-lg-3">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <h3><i class="bi bi-wrench me-2" style="color:var(--bc-blue);"></i>Maintenance</h3>
                </div>
                <div class="bc-card-body flush">
                    @forelse ($bridge->maintenanceRequests->sortByDesc('created_at')->take(4) as $request)
                        <a href="{{ route('admin.maintenance.show', $request) }}" class="d-block p-3" style="border-bottom:1px solid #f1f5f9;">
                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <strong style="font-size:12.5px;color:var(--bc-blue);">{{ $request->request_number }}</strong>
                                <x-status-badge :status="$request->status" />
                            </div>
                            <div style="font-size:12.5px;margin-top:4px;color:#475569;">{{ Str::limit($request->title, 40) }}</div>
                            <small style="color:#94a3b8;">{{ $request->created_at->format('d M Y') }}</small>
                        </a>
                    @empty
                        <div class="bc-empty py-4">
                            <i class="bi bi-wrench"></i>
                            <h4>No records</h4>
                            <p>No maintenance has been recorded for this bridge.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
