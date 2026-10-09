@extends('layouts.admin')

@section('title', $maintenance->request_number)

@section('content')
    <x-page-header :title="$maintenance->title"
                   :subtitle="$maintenance->request_number . ' • ' . $maintenance->infrastructure->name . ' (' . $maintenance->infrastructure->type->name . ')'"
                   :breadcrumbs="[[ 'label' => 'Maintenance', 'url' => route('admin.maintenance.index') ]]">
        <a href="{{ route('admin.maintenance.edit', $maintenance) }}" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-pencil"></i> Edit</a>
        @if (! $maintenance->assigned_to)
            <button class="btn-bc btn-bc-primary btn-bc-sm" data-bc-dropdown="bcAssignModal"><i class="bi bi-person-plus"></i> Assign Staff</button>
        @endif
    </x-page-header>

    {{-- Status timeline --}}
    @php
        $steps = ['pending', 'under_review', 'assigned', 'in_progress', 'completed'];
        $currentIdx = array_search($maintenance->status, $steps);
        if (in_array($maintenance->status, ['on_hold', 'cancelled'])) {
            $currentIdx = -1;
        }
    @endphp
    <div class="bc-card mb-4">
        <div class="bc-card-body">
            @if (in_array($maintenance->status, ['on_hold', 'cancelled']))
                <div class="bc-alert {{ $maintenance->status === 'cancelled' ? 'bc-alert-danger' : 'bc-alert-warning' }}" style="margin-bottom:18px;">
                    <i class="bi {{ $maintenance->status === 'cancelled' ? 'bi-x-octagon-fill' : 'bi-pause-circle-fill' }}"></i>
                    <div><strong>{{ $maintenance->status === 'cancelled' ? 'This request has been cancelled.' : 'This request is on hold.' }}</strong> The workflow is paused.</div>
                </div>
            @endif
            <div class="bc-track-steps">
                @foreach (['Pending', 'Under Review', 'Assigned', 'In Progress', 'Completed'] as $i => $label)
                    <div class="bc-track-step {{ $currentIdx >= $i ? 'done' : '' }} {{ $currentIdx === $i && !in_array($maintenance->status, ['on_hold', 'cancelled']) ? 'current' : '' }}">
                        <div class="circle">
                            @if ($currentIdx > $i || $maintenance->status === 'completed')
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
                    <h3>Request Details</h3>
                    <div class="d-flex gap-2">
                        <x-priority-badge :priority="$maintenance->priority" />
                        <x-status-badge :status="$maintenance->status" />
                    </div>
                </div>
                <div class="bc-card-body">
                    <p style="font-size:13.5px;color:#475569;line-height:1.7;">{{ $maintenance->description ?: 'No description provided.' }}</p>

                    <div class="bc-info-grid mt-3">
                        <div class="bc-info-item">
                            <span class="label">Reported By</span>
                            <span class="value">{{ $maintenance->reporter?->name ?? 'Public User' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Assigned Staff</span>
                            <span class="value">
                                @if ($maintenance->assignedStaff)
                                    {{ $maintenance->assignedStaff->name }}
                                @else
                                    <span class="bc-badge badge-gray">Unassigned</span>
                                @endif
                            </span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Start Date</span>
                            <span class="value">{{ $maintenance->start_date?->format('d M Y') ?? '—' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Expected Completion</span>
                            <span class="value" @class(['text-danger' => $maintenance->isOverdue()])>
                                {{ $maintenance->expected_completion_date?->format('d M Y') ?? '—' }}
                                @if ($maintenance->isOverdue()) <i class="bi bi-exclamation-triangle-fill" style="font-size:11px;"></i> @endif
                            </span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Completion Date</span>
                            <span class="value">{{ $maintenance->completion_date?->format('d M Y') ?? '—' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Created</span>
                            <span class="value">{{ $maintenance->created_at->format('d M Y') }}</span>
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-6">
                            <div class="p-3" style="background:#f8fafc;border-radius:12px;border:1px solid #f1f5f9;">
                                <span class="label" style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#94a3b8;">Estimated Cost</span>
                                <div style="font-size:20px;font-weight:700;margin-top:4px;">₹{{ number_format($maintenance->estimated_cost) }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3" style="background:#f8fafc;border-radius:12px;border:1px solid #f1f5f9;">
                                <span class="label" style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#94a3b8;">Actual Cost</span>
                                <div style="font-size:20px;font-weight:700;margin-top:4px;color:var(--bc-green);">
                                    {{ $maintenance->actual_cost ? '₹' . number_format($maintenance->actual_cost) : '—' }}
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
                    @if ($maintenance->before_image && $maintenance->after_image)
                        <span class="bc-badge badge-green"><i class="bi bi-images"></i> Complete</span>
                    @elseif ($maintenance->before_image)
                        <span class="bc-badge badge-yellow"><i class="bi bi-image"></i> Before only</span>
                    @else
                        <span class="bc-badge badge-gray">No photos yet</span>
                    @endif
                </div>
                <div class="bc-card-body">
                    @if ($maintenance->before_image && $maintenance->after_image)
                        <x-before-after
                            before="{{ asset('storage/' . $maintenance->before_image) }}"
                            after="{{ asset('storage/' . $maintenance->after_image) }}" />
                        <p class="text-center mt-2 mb-0" style="font-size:11.5px;color:#94a3b8;"><i class="bi bi-hand-index"></i> Drag the handle to compare</p>
                    @elseif ($maintenance->before_image)
                        <img src="{{ asset('storage/' . $maintenance->before_image) }}" alt="Before" style="width:100%;border-radius:12px;border:1px solid #e2e8f0;">
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
                    <form method="POST" action="{{ route('admin.maintenance.updates', $maintenance) }}" enctype="multipart/form-data" data-bc-loading>
                        @csrf
                        <div class="mb-3">
                            <label class="bc-form-label">Update Status</label>
                            <select name="status" class="bc-select">
                                <option value="">— No status change —</option>
                                @foreach (\App\Models\MaintenanceRequest::STATUSES as $s)
                                    @if ($s !== $maintenance->status)
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

    {{-- Assign modal --}}
    <div class="bc-modal-backdrop" id="bcAssignModal">
        <div class="bc-modal">
            <div class="bc-modal-header">
                <h3><i class="bi bi-person-plus me-2" style="color:var(--bc-blue);"></i>Assign Staff</h3>
                <button type="button" class="bc-icon-btn" style="width:34px;height:34px;" onclick="document.getElementById('bcAssignModal').classList.remove('show')"><i class="bi bi-x"></i></button>
            </div>
            <form method="POST" action="{{ route('admin.maintenance.assign', $maintenance) }}">
                @csrf
                <div class="bc-modal-body">
                    <label class="bc-form-label">Select staff member</label>
                    <select name="assigned_to" class="bc-select" required>
                        <option value="">— Select —</option>
                        @foreach (\App\Models\User::where('role', 'staff')->where('status', 'active')->orderBy('name')->get() as $member)
                            <option value="{{ $member->id }}">{{ $member->name }} ({{ $member->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="bc-modal-footer">
                    <button type="button" class="btn-bc btn-bc-outline btn-bc-sm" onclick="document.getElementById('bcAssignModal').classList.remove('show')">Cancel</button>
                    <button type="submit" class="btn-bc btn-bc-primary btn-bc-sm"><i class="bi bi-check-lg"></i> Assign</button>
                </div>
            </form>
        </div>
    </div>
@endsection
