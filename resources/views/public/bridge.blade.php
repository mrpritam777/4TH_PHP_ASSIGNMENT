@extends('layouts.public')

@section('title', $bridge->name)

@section('content')
    <x-page-header :title="$bridge->name" :subtitle="$bridge->type->name . ' • ' . $bridge->location">
        <a href="{{ route('public.infrastructure') }}" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-arrow-left"></i> Back to Directory</a>
    </x-page-header>

    <div class="bc-hero-card">
        <img src="{{ $bridge->imageUrl() }}" alt="{{ $bridge->name }}">
        <div class="bc-hero-overlay">
            <div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <h1>{{ $bridge->name }}</h1>
                    <span class="bc-badge {{ $bridge->getConditionBadgeClass() }}" style="font-size:13px;padding:6px 14px;"><span class="dot"></span> {{ ucfirst($bridge->condition) }}</span>
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

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="bc-card">
                <div class="bc-card-header">
                    <h3>Bridge Overview</h3>
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
                        <div class="bc-info-item>
                            <span class="label">Load Capacity</span>
                            <span class="value">{{ $bridge->bridgeDetails?->load_capacity ?? '—' }}</span>
                        </div>
                        <div class="bc-info-item>
                            <span class="label">Material</span>
                            <span class="value">{{ $bridge->bridgeDetails?->material ?? '—' }}</span>
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

            <div class="bc-card mt-4">
                <div class="bc-card-header">
                    <h3>Contact Information</h3>
                </div>
                <div class="bc-card-body">
                    @if ($bridge->contacts->isNotEmpty())
                        @foreach ($bridge->contacts as $contact)
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
                        @endforeach
                    @else
                        <div class="bc-empty py-4">
                            <i class="bi bi-person-dash"></i>
                            <h4>No contacts</h4>
                            <p>No emergency or support contacts are listed for this bridge.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="bc-card">
                <div class="bc-card-header">
                    <h3>Recent Maintenance</h3>
                </div>
                <div class="bc-card-body">
                    @if ($bridge->maintenanceRequests->isNotEmpty())
                        @foreach ($bridge->maintenanceRequests->sortByDesc('created_at')->take(3) as $request)
                            <div class="d-flex gap-3 p-3" style="border-bottom:1px solid #f1f5f9;">
                                <div style="min-width:0;">
                                    <strong style="font-size:13.5px;color:var(--bc-blue);">{{ $request->request_number }}</strong>
                                    <div class="mt-1" style="font-size:12px;color:#64748b;">{{ Str::limit($request->title, 30) }}</div>
                                </div>
                                <span class="bc-badge {{ $request->status === 'completed' ? 'badge-green' : ($request->status === 'in_progress' ? 'badge-blue' : 'badge-yellow') }}"><span class="dot"></span>{{ ucfirst($request->status) }}</span>
                            </div>
                        @endforeach
                    @else
                        <div class="bc-empty py-4">
                            <i class="bi bi-wrench"></i>
                            <h4>No records</h4>
                            <p>No maintenance has been performed on this bridge yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="bc-card mt-4">
        <div class="bc-card-header">
            <h3>Maintenance Timeline</h3>
        </div>
        <div class="bc-card-body">
            @if ($bridge->maintenanceRequests->isNotEmpty())
                <div class="bc-timeline" style="padding-left:34px;">
                    @foreach ($bridge->maintenanceRequests->sortByDesc('created_at') as $request)
                        @php
                            $dotClass = match ($request->status) {
                                'completed' => 'green',
                                'in_progress', 'assigned' => '',
                                'on_hold' => 'orange',
                                'cancelled' => 'gray',
                                default => 'red',
                            };
                            $icon = match ($request->status) {
                                'completed' => 'bi-check2',
                                'in_progress' => 'bi-arrow-repeat',
                                'cancelled' => 'bi-x',
                                default => 'bi-wrench',
                            };
                        @endphp
                        <x-timeline-item
                            :date="$request->completion_date?->format('d M Y') ?? $request->created_at->format('d M Y')"
                            :title="$request->title"
                            :text="Str::limit($request->description, 110)"
                            :dot-class="$dotClass"
                            :icon="$icon"
                        />
                    @endforeach
                </div>
            @else
                <div class="bc-empty py-4">
                    <i class="bi bi-clock-history"></i>
                    <h4>No maintenance history</h4>
                    <p>This bridge has no recorded maintenance activity yet.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
