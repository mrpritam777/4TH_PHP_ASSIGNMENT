@extends('layouts.admin')

@section('title', $task->request_number)

@section('content')
    <x-page-header :title="$task->title" :subtitle="$task->request_number . ' - ' . $task->infrastructure->name" :breadcrumbs="[['label' => 'My Tasks', 'url' => route('staff.tasks.index')]]" />

    {{-- Status timeline --}}
    @php
        $steps = ['pending', 'under_review', 'assigned', 'in_progress', 'completed'];
        $currentIdx = array_search($task->status, $steps);
        if (in_array($task->status, ['on_hold', 'cancelled'])) {
            $currentIdx = -1;
        }
    @endphp
    <div class="bc-card mb-4">
        <div class="bc-card-body">
            @if (in_array($task->status, ['on_hold', 'cancelled']))
                <div class="bc-alert {{ $task->status === 'cancelled' ? 'bc-alert-danger' : 'bc-alert-warning' }}" style="margin-bottom:18px;">
                    <i class="bi {{ $task->status === 'cancelled' ? 'bi-x-octagon-fill' : 'bi-pause-circle-fill' }}"></i>
                    <div><strong>{{ $task->status === 'cancelled' ? 'This task has been cancelled.' : 'This task is on hold.' }}</strong> Work cannot proceed until status changes.</div>
                </div>
            @endif
            <div class="bc-track-steps">
                @foreach (['Pending', 'Under Review', 'Assigned', 'In Progress', 'Completed'] as $i => $label)
                    <div class="bc-track-step {{ $currentIdx >= $i ? 'done' : '' }} {{ $currentIdx === $i && !in_array($task->status, ['on_hold', 'cancelled']) ? 'current' : '' }}">
                        <div class="circle">
                            @if ($currentIdx > $i || $task->status === 'completed')
                                <i class="bi bi-check"></i>
                            @else
                                <i class="bi {{ ['bi-hourglass', 'bi-search', 'bi-person', 'bi-activity', 'bi-flag'][min($i, 4)] }}"></i>
                            @endif
                        </div>
                        <span>{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        {{-- Details --}}
        <div class="col-lg-7">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <h3>Task Details</h3>
                    <div class="d-flex gap-2">
                        <x-priority-badge :priority="$task->priority" />
                        <x-status-badge :status="$task->status" />
                    </div>
                </div>
                <div class="bc-card-body">
                    <p style="font-size:13.5px;color:#475569;line-height:1.7;">{{ $task->description ?: 'No description provided.' }}</p>

                    <div class="bc-info-grid mt-3">
                        <div class="bc-info-item">
                            <span class="label">Reported By</span>
                            <span class="value">{{ $task->reporter?->name ?? 'Public User' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Assigned To</span>
                            <span class="value">
                                @if ($task->assignedStaff)
                                    {{ $task->assignedStaff->name }}
                                @else
                                    <span class="bc-badge badge-gray">Awaiting Assignment</span>
                                @endif
                            </span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Start Date</span>
                            <span class="value">{{ $task->start_date?->format('d M Y') ?? '—' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Expected Completion</span>
                            <span class="value" @class(['text-danger' => $task->isOverdue()])>
                                {{ $task->expected_completion_date?->format('d M Y') ?? '—' }}
                                @if ($task->isOverdue()) <i class="bi bi-exclamation-triangle-fill" style="font-size:11px;"></i> @endif
                            </span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Completion Date</span>
                            <span class="value">{{ $task->completion_date?->format('d M Y') ?? '—' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Created</span>
                            <span class="value">{{ $task->created_at->format('d M Y') }}</span>
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-6">
                            <div class="p-3" style="background:#f8fafc;border-radius:12px;border:1px solid #f1f5f9;">
                                <span class="label" style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#94a3b8;">Estimated Cost</span>
                                <div style="font-size:20px;font-weight:700;margin-top:4px;">₹{{ number_format($task->estimated_cost) }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3" style="background:#f8fafc;border-radius:12px;border:1px solid #f1f5f9;">
                                <span class="label" style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#94a3b8;">Actual Cost</span>
                                <div style="font-size:20px;font-weight:700;margin-top:4px;color:var(--bc-green);">
                                    {{ $task->actual_cost ? '₹' . number_format($task->actual_cost) : '—' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Before / After --}}
        <div class="col-lg-5">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <h3>Before / After Comparison</h3>
                    @if ($task->before_image && $task->after_image)
                        <span class="bc-badge badge-green"><i class="bi bi-images"></i> Complete</span>
                    @elseif ($task->before_image)
                        <span class="bc-badge badge-yellow"><i class="bi bi-image"></i> Before only</span>
                    @else
                        <span class="bc-badge badge-gray">No photos yet</span>
                    @endif
                </div>
                <div class="bc-card-body">
                    @if ($task->before_image && $task->after_image)
                        <x-before-after
                            before="{{ asset('storage/' . $task->before_image) }}"
                            after="{{ asset('storage/' . $task->after_image) }}" />
                        <p class="text-center mt-2 mb-0" style="font-size:11.5px;color:#94a3b8;"><i class="bi bi-hand-index"></i> Drag the handle to compare</p>
                    @elseif ($task->before_image)
                        <img src="{{ asset('storage/' . $task->before_image) }}" alt="Before" style="width:100%;border-radius:12px;border:1px solid #e2e8f0;">
                        <p class="text-center mt-2 mb-0" style="font-size:12px;color:#64748b;">After photo will appear here once maintenance is complete.</p>
                    @else
                        <div class="bc-empty py-4">
                            <i class="bi bi-images"></i>
                            <h4>No photos attached</h4>
                            <p>Before/after photos will be shown here.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Progress updates timeline --}}
        <div class="col-lg-7">
            <div class="bc-card">
                <div class="bc-card-header">
                    <h3>Progress Updates</h3>
                    <span class="bc-badge badge-blue">{{ $timeline->count() }} updates</span>
                </div>
                <div class="bc-card-body">
                    @forelse ($timeline as $update)
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
                    @empty
                        <div class="bc-empty py-4">
                            <i class="bi bi-chat-square-text"></i>
                            <h4>No updates yet</h4>
                            <p>Progress notes and status changes will appear here.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Add update form --}}
        <div class="col-lg-5">
            <div class="bc-card">
                <div class="bc-card-header">
                    <h3>Add Progress Update</h3>
                </div>
                <div class="bc-card-body">
                    <form method="POST" action="{{ route('staff.tasks.update', $task) }}" enctype="multipart/form-data" data-bc-loading>
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="bc-form-label">Update Status</label>
                            <select name="status" class="bc-select">
                                <option value="">— No status change —</option>
                                @foreach (\App\Models\MaintenanceRequest::STATUSES as $s)
                                    @if ($s !== $task->status)
                                        <option value="{{ $s }}">{{ str($s)->replace('_', ' ')->title() }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="bc-form-label">Notes</label>
                            <textarea name="notes" class="bc-textarea" placeholder="Describe the progress made..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="bc-form-label">Actual Cost (₹) — if completed</label>
                            <input type="number" step="0.01" name="actual_cost" class="bc-input" min="0" placeholder="Enter final cost">
                        </div>
                        <div class="mb-3">
                            <label class="bc-form-label">Photo</label>
                            <div class="bc-image-drop" data-input="bcUpdateImageInput" data-preview="bcUpdateImagePreview" style="padding:14px;">
                                <i class="bi bi-cloud-arrow-up" style="font-size:22px;"></i>
                                <p style="margin-top:4px;font-size:12.5px;">Attach a progress photo</p>
                            </div>
                            <input type="file" id="bcUpdateImageInput" name="image" accept="image/*" class="d-none">
                            <div class="bc-image-preview" id="bcUpdateImagePreview"></div>
                        </div>
                        <button type="submit" class="btn-bc btn-bc-primary btn-bc-sm w-100 justify-content-center"><i class="bi bi-send"></i> Submit Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
