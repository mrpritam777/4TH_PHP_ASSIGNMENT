@extends('layouts.public')

@section('title', 'Request #{{ $request->request_number }}')

@section('content')
    <x-page-header title="Request #{{ $request->request_number }}" subtitle="{{ $request->infrastructure->name }} • {{ $request->infrastructure->type->name }}">
        <a href="{{ route('public.track') }}" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-arrow-left"></i> Track Another Request</a>
    </x-page-header>

    @if ($request)
        <div class="bc-card mb-4">
            <div class="bc-card-header">
                <h3>Request Overview</h3>
            </div>
            <div class="bc-card-body">
                <div class="bc-info-grid" style="grid-template-columns:repeat(2, 1fr); gap:16px;">
                    <div class="bc-info-item">
                        <span class="label">Request ID</span>
                        <span class="value">{{ $request->request_number }}</span>
                        <i class="bi bi-info-circle ms-1" style="font-size:12px;" data-bs-toggle="tooltip" title="Unique identifier for this maintenance request"></i>
                    </div>
                    <div class="bc-info-item">
                        <span class="label">Infrastructure</span>
                        <span class="value">{{ $request->infrastructure->name }} ({{ $request->infrastructure->code }})</span>
                    </div>
                    <div class="bc-info-item">
                        <span class="label">Issue</span>
                        <span class="value" style="font-size:13.5px;">{{ $request->title }}</span>
                    </div>
                    <div class="bc-info-item">
                        <span class="label">Reported By</span>
                        <span class="value">{{ $request->reporter?->name ?? 'Public User' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bc-card mb-4">
            <div class="bc-card-header">
                <h3>Status &amp; Timeline</h3>
                <span class="bc-badge badge-blue">@if($request->isOverdue()) OVERDUE @endif</span>
            </div>
            <div class="bc-card-body">
                @php
                    $steps = ['pending', 'under_review', 'assigned', 'in_progress', 'completed'];
                    $currentIdx = array_search($request->status, $steps);
                    if (in_array($request->status, ['on_hold', 'cancelled'])) {
                        $currentIdx = -1;
                    }
                @endphp
                <div class="bc-track-steps">
                    @foreach (['Pending', 'Under Review', 'Assigned', 'In Progress', 'Completed'] as $i => $label)
                        <div class="bc-track-step {{ $currentIdx >= $i ? 'done' : '' }} {{ $currentIdx === $i && !in_array($request->status, ['on_hold', 'cancelled']) ? 'current' : '' }}">
                            <div class="circle">
                                @if ($currentIdx > $i || $request->status === 'completed')
                                    <i class="bi bi-check"></i>
                                @else
                                    <i class="bi {{ ['bi-hourglass', 'bi-search', 'bi-person', 'bi-activity', 'bi-flag'][min($i, 4)] }}"></i>
                                @endif
                            </div>
                            <span>{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
                <p class="mt-3" style="font-size:13.5px;color:#64748b;">
                    <strong>Current Status:</strong> <span class="bc-badge {{ $request->status === 'completed' ? 'badge-green' : ($request->status === 'in_progress' ? 'badge-blue' : ($request->status === 'on_hold' ? 'badge-orange' : ($request->status === 'cancelled' ? 'badge-red' : 'badge-yellow'))) }}">{{ ucfirst($request->status) }}</span>
                </p>
                @if ($request->isOverdue())
                    <div class="bc-alert bc-alert-warning mt-2">
                        <i class="bi bi-exclamation-triangle"></i>
                        <div>This request is past its expected completion date ({{ $request->expected_completion_date?->format('d M Y') }}). Please contact the maintenance team for updates.</div>
                    </div>
                @endif
            </div>
        </div>

        @if ($request->before_image || $request->after_image)
            <div class="bc-card mb-4">
                <div class="bc-card-header">
                    <h3>Before / After Comparison</h3>
                </div>
                <div class="bc-card-body">
                    @if ($request->before_image && $request->after_image)
                        <x-before-after
                            before="{{ asset('storage/' . $request->before_image) }}"
                            after="{{ asset('storage/' . $request->after_image) }}" />
                        <p class="text-center mt-2 mb-0" style="font-size:11.5px;color:#94a3b8;"><i class="bi bi-hand-index"></i> Drag the handle to compare</p>
                    @elseif ($request->before_image)
                        <img src="{{ asset('storage/' . $request->before_image) }}" alt="Before" style="width:100%;border-radius:12px;border:1px solid #e2e8f0;">
                        <p class="text-center mt-2 mb-0" style="font-size:12px;color:#64748b;">After photo will be shown here once maintenance is complete.</p>
                    @else
                        <div class="bc-empty py-4">
                            <i class="bi bi-images"></i>
                            <h4>Waiting for after photo</h4>
                            <p>The before photo has been uploaded. The after photo will appear here once maintenance work is finished.</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <div class="bc-card">
            <div class="bc-card-header">
                <h3>Progress Updates</h3>
            </div>
            <div class="bc-card-body">
                @if ($request->updates->isNotEmpty())
                    @foreach ($request->updates->sortByDesc('created_at') as $update)
                        <x-timeline-item
                            :date="$update->created_at->format('d M Y H:i')"
                            :title="$update->status ? 'Status: ' . str($update->status)->replace('_', ' ')->title() : 'Progress update'"
                            :text="$update->notes"
                            :dot-class="in_array($update->status, ['completed']) ? 'green' : (in_array($update->status, ['on_hold', 'cancelled']) ? 'orange' : '')"
                            :icon="in_array($update->status, ['completed']) ? 'bi-check2' : 'bi-pencil'"
                        />
                        @if ($update->image)
                            <div style="margin:-14px 0 22px 34px;">
                                <img src="{{ asset('storage/' . $update->image) }}" alt="Update" style="max-width:220px;border-radius:10px;border:1px solid #e2e8f0;">
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="bc-empty py-4">
                        <i class="bi bi-chat-square-text"></i>
                        <h4>No updates yet</h4>
                        <p>No progress reports have been submitted for this request.</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="bc-card mt-4">
            <div class="bc-card-header">
                <h3>Details</h3>
            </div>
            <div class="bc-card-body">
                <div class="bc-info-grid">
                    <div class="bc-info-item">
                        <span class="label">Priority</span>
                        <span class="value"><x-priority-badge :priority="$request->priority" /></span>
                    </div>
                    <div class="bc-info-item">
                        <span class="label">Estimated Cost</span>
                        <span class="value">₹{{ number_format($request->estimated_cost) }}</span>
                    </div>
                    <div class="bc-info-item">
                        <span class="label">Actual Cost</span>
                        <span class="value">{{ $request->actual_cost ? '₹' . number_format($request->actual_cost) : '—' }}</span>
                    </div>
                    <div class="bc-info-item">
                        <span class="label">Start Date</span>
                        <span class="value">{{ $request->start_date?->format('d M Y') ?? '—' }}</span>
                    </div>
                    <div class="bc-info-item">
                        <span class="label">Expected Completion Date</span>
                        <span class="value">{{ $request->expected_completion_date?->format('d M Y') ?? '—' }}</span>
                    </div>
                    <div class="bc-info-item">
                        <span class="label">Completion Date</span>
                        <span class="value">{{ $request->completion_date?->format('d M Y') ?? '—' }}</span>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="bc-card">
            <div class="bc-alert bc-alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>
                    <strong>Request Not Found</strong>
                    <p>We could not find a maintenance request with the number "{{ old('requestNumber') }}". Please check the number and try again.</p>
                </div>
            </div>
        </div>
    @endif
@endsection
