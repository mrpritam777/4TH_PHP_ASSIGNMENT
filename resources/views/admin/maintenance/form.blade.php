@extends('layouts.admin')

@section('title', isset($maintenance) ? 'Edit Maintenance Request' : 'New Maintenance Request')

@section('content')
    <x-page-header
        :title="isset($maintenance) ? 'Edit Request ' . $maintenance->request_number : 'Create Maintenance Request'"
        :subtitle="isset($maintenance) ? 'Update the details of this maintenance request.' : 'Report a new maintenance issue for an infrastructure asset.'"
        :breadcrumbs="isset($maintenance) ? [[ 'label' => 'Maintenance', 'url' => route('admin.maintenance.index') ]] : []">
    </x-page-header>

    <form method="POST" action="{{ isset($maintenance) ? route('admin.maintenance.update', $maintenance) : route('admin.maintenance.store') }}" enctype="multipart/form-data" data-bc-loading>
        @csrf
        @if (isset($maintenance)) @method('PUT') @endif

        <div class="bc-form-section">
            <div class="bc-form-section-header">
                <i class="bi bi-diagram-3"></i>
                <div>
                    <h3>Infrastructure &amp; Problem</h3>
                    <small>What needs maintenance and where</small>
                </div>
            </div>
            <div class="bc-form-section-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="bc-form-label">Infrastructure <span class="req">*</span></label>
                        <select name="infrastructure_id" class="bc-select @error('infrastructure_id') is-invalid @enderror" required>
                            <option value="">Select infrastructure...</option>
                            @foreach ($infrastructures as $typeName => $items)
                                <optgroup label="{{ $typeName }}">
                                    @foreach ($items as $infra)
                                        <option value="{{ $infra->id }}" @selected(old('infrastructure_id', $maintenance->infrastructure_id ?? $preselected ?? null) == $infra->id)>
                                            {{ $infra->name }} ({{ $infra->code }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @error('infrastructure_id') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="bc-form-label">Problem Title <span class="req">*</span></label>
                        <input type="text" name="title" class="bc-input @error('title') is-invalid @enderror" value="{{ old('title', $maintenance->title ?? '') }}" placeholder="e.g. Deck surface cracking on span 3" required>
                        @error('title') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="bc-form-label">Problem Description</label>
                        <textarea name="description" class="bc-textarea" placeholder="Describe the issue, its extent, and any immediate safety concerns...">{{ old('description', $maintenance->description ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="bc-form-section">
            <div class="bc-form-section-header">
                <i class="bi bi-sliders"></i>
                <div>
                    <h3>Priority, Assignment &amp; Schedule</h3>
                    <small>How urgent, who handles it, and when</small>
                </div>
            </div>
            <div class="bc-form-section-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="bc-form-label">Priority <span class="req">*</span></label>
                        <select name="priority" class="bc-select @error('priority') is-invalid @enderror" required>
                            @foreach (['low' => 'Low — Normal maintenance', 'medium' => 'Medium — Requires attention', 'high' => 'High — Important issue', 'critical' => 'Critical — Immediate action'] as $val => $lbl)
                                <option value="{{ $val }}" @selected(old('priority', $maintenance->priority ?? 'medium') === $val)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                        @error('priority') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="bc-form-label">Assign To Staff</label>
                        <select name="assigned_to" class="bc-select">
                            <option value="">— Unassigned —</option>
                            @foreach ($staff as $member)
                                <option value="{{ $member->id }}" @selected(old('assigned_to', $maintenance->assigned_to ?? null) == $member->id)>
                                    {{ $member->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="bc-form-label">Estimated Cost (₹)</label>
                        <input type="number" step="0.01" name="estimated_cost" class="bc-input" value="{{ old('estimated_cost', $maintenance->estimated_cost ?? 0) }}" min="0">
                    </div>
                    <div class="col-md-3">
                        <label class="bc-form-label">Start Date</label>
                        <input type="date" name="start_date" class="bc-input" value="{{ old('start_date', isset($maintenance) ? $maintenance->start_date?->format('Y-m-d') : '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="bc-form-label">Expected Completion Date</label>
                        <input type="date" name="expected_completion_date" class="bc-input @error('expected_completion_date') is-invalid @enderror" value="{{ old('expected_completion_date', isset($maintenance) ? $maintenance->expected_completion_date?->format('Y-m-d') : '') }}">
                        @error('expected_completion_date') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="bc-form-section">
            <div class="bc-form-section-header">
                <i class="bi bi-images"></i>
                <div>
                    <h3>Before / After Images</h3>
                    <small>Attach evidence photos of the issue and (later) the repaired state</small>
                </div>
            </div>
            <div class="bc-form-section-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="bc-form-label">Before Image</label>
                        <div class="bc-image-drop" data-input="bcBeforeInput" data-preview="bcBeforePreview">
                            <i class="bi bi-cloud-arrow-up"></i>
                            <p>Upload the "before" photo</p>
                            <small>JPG, PNG or WEBP • Max 3 MB</small>
                        </div>
                        <input type="file" id="bcBeforeInput" name="before_image" accept="image/*" class="d-none">
                        <div class="bc-image-preview" id="bcBeforePreview"></div>
                        @if (isset($maintenance) && $maintenance->before_image)
                            <div class="mt-2">
                                <small style="color:#64748b;">Current before image:</small>
                                <img src="{{ asset('storage/' . $maintenance->before_image) }}" alt="" style="max-width:180px;border-radius:10px;border:1px solid #e2e8f0;">
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="bc-form-label">After Image</label>
                        <div class="bc-image-drop" data-input="bcAfterInput" data-preview="bcAfterPreview">
                            <i class="bi bi-cloud-arrow-up"></i>
                            <p>Upload the "after" photo (optional)</p>
                            <small>JPG, PNG or WEBP • Max 3 MB</small>
                        </div>
                        <input type="file" id="bcAfterInput" name="after_image" accept="image/*" class="d-none">
                        <div class="bc-image-preview" id="bcAfterPreview"></div>
                        @if (isset($maintenance) && $maintenance->after_image)
                            <div class="mt-2">
                                <small style="color:#64748b;">Current after image:</small>
                                <img src="{{ asset('storage/' . $maintenance->after_image) }}" alt="" style="max-width:180px;border-radius:10px;border:1px solid #e2e8f0;">
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap mb-4">
            <button type="submit" class="btn-bc btn-bc-primary"><i class="bi bi-check-lg"></i> @if(isset($maintenance)) Save Changes @else Create Request @endif</button>
            <a href="{{ route('admin.maintenance.index') }}" class="btn-bc btn-bc-ghost"><i class="bi bi-x-lg"></i> Cancel</a>
        </div>
    </form>
@endsection
