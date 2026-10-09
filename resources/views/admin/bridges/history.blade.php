@extends('layouts.admin')

@section('title', $bridge->name . ' — History')

@section('content')
    @php $completedCount = $history->where('status', 'completed')->count(); @endphp
    <x-page-header :title="$bridge->name . ' — Maintenance History'"
                   :subtitle="$completedCount . ' completed works • ' . $history->count() . ' total records'"
                   :breadcrumbs="[[ 'label' => 'Bridges', 'url' => route('admin.bridges.index') ], [ 'label' => $bridge->name, 'url' => route('admin.bridges.show', $bridge) ]]">
        <a href="{{ route('admin.bridges.show', $bridge) }}" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-arrow-left"></i> Back to Bridge</a>
        <a href="{{ route('admin.maintenance.create', ['infrastructure_id' => $bridge->id]) }}" class="btn-bc btn-bc-primary btn-bc-sm"><i class="bi bi-plus-lg"></i> New Maintenance</a>
    </x-page-header>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="bc-card">
                <div class="bc-card-header">
                    <h3>Maintenance Timeline</h3>
                    <span class="bc-badge badge-blue">{{ $history->count() }} records</span>
                </div>
                <div class="bc-card-body">
                    @forelse ($history as $request)
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
                            :status="$request->status"
                        />
                    @empty
                        <div class="bc-empty">
                            <i class="bi bi-clock-history"></i>
                            <h4>No maintenance history</h4>
                            <p>This bridge has no recorded maintenance activity yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="bc-card mb-3">
                <div class="bc-card-header">
                    <h3>Summary</h3>
                </div>
                <div class="bc-card-body">
                    <div class="bc-info-grid" style="grid-template-columns:1fr 1fr;">
                        <div class="bc-info-item">
                            <span class="label">Total Works</span>
                            <span class="value">{{ $history->count() }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Completed</span>
                            <span class="value" style="color:var(--bc-green);">{{ $history->where('status', 'completed')->count() }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Total Cost</span>
                            <span class="value">₹{{ number_format($history->sum('actual_cost')) }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Last Work</span>
                            <span class="value" style="font-size:12px;">{{ $history->first() ? ($history->first()->completion_date ?? $history->first()->created_at)->format('d M Y') : '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bc-card">
                <div class="bc-card-header">
                    <h3>Quick Actions</h3>
                </div>
                <div class="bc-card-body d-flex flex-column gap-2">
                    <a href="{{ route('admin.maintenance.create', ['infrastructure_id' => $bridge->id]) }}" class="btn-bc btn-bc-primary btn-bc-sm justify-content-center"><i class="bi bi-wrench"></i> Schedule Maintenance</a>
                    <a href="{{ route('admin.bridges.edit', $bridge) }}" class="btn-bc btn-bc-outline btn-bc-sm justify-content-center"><i class="bi bi-pencil"></i> Update Bridge Details</a>
                    <a href="{{ route('admin.bridges.show', $bridge) }}" class="btn-bc btn-bc-ghost btn-bc-sm justify-content-center"><i class="bi bi-eye"></i> View Bridge Profile</a>
                </div>
            </div>
        </div>
    </div>
@endsection
