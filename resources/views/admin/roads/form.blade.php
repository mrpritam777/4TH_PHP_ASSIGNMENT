@extends('layouts.admin')

@section('title', isset($road) ? 'Edit Road' : 'Add Road')

@section('content')
    <x-page-header
        :title="isset($road) ? 'Edit Road' : 'Add New Road'"
        :subtitle="isset($road) ? 'Update the details for ' . $road->name . '.' : 'Fill in the details for the new road.'"
        :breadcrumbs="isset($road) ? [[ 'label' => 'Roads', 'url' => route('admin.roads.index') ]] : []">
    </x-page-header>

    <form method="POST" action="{{ isset($road) ? route('admin.roads.update', $road) : route('admin.roads.store') }}" enctype="multipart/form-data" data-bc-loading>
        @csrf
        @if (isset($road)) @method('PUT') @endif

        <div class="bc-form-section">
            <div class="bc-form-section-header">
                <i class="bi bi-signpost"></i>
                <div>
                    <h3>Road Information</h3>
                    <small>Identity, classification and route</small>
                </div>
            </div>
            <div class="bc-form-section-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="bc-form-label">Road Name <span class="req">*</span></label>
                        <input type="text" name="name" class="bc-input @error('name') is-invalid @enderror" value="{{ old('name', $road->name ?? '') }}" placeholder="e.g. NH-66 Coastal Highway" required>
                        @error('name') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="bc-form-label">Road Code / Number <span class="req">*</span></label>
                        <input type="text" name="code" class="bc-input @error('code') is-invalid @enderror" value="{{ old('code', $road->code ?? '') }}" placeholder="e.g. RD-014" required>
                        @error('code') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-5">
                        <label class="bc-form-label">Road Type <span class="req">*</span></label>
                        <select name="road_type" class="bc-select @error('road_type') is-invalid @enderror" required>
                            <option value="">Select type...</option>
                            @foreach (['National Highway', 'State Highway', 'District Road', 'Rural Road', 'City Road', 'Expressway', 'Feeder Road', 'Other'] as $type)
                                <option value="{{ $type }}" @selected(old('road_type', $road?->roadDetails?->road_type) === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                        @error('road_type') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="bc-form-label">Start Point</label>
                        <input type="text" name="start_point" class="bc-input" value="{{ old('start_point', $road?->roadDetails?->start_point ?? '') }}" placeholder="e.g. Puri Junction">
                    </div>
                    <div class="col-md-4">
                        <label class="bc-form-label">End Point</label>
                        <input type="text" name="end_point" class="bc-input" value="{{ old('end_point', $road?->roadDetails?->end_point ?? '') }}" placeholder="e.g. Paradip Port">
                    </div>
                    <div class="col-md-4">
                        <label class="bc-form-label">Location <span class="req">*</span></label>
                        <input type="text" name="location" class="bc-input @error('location') is-invalid @enderror" value="{{ old('location', $road->location ?? '') }}" placeholder="e.g. Odisha" required>
                        @error('location') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="bc-form-label">Total Length (km)</label>
                        <input type="number" step="0.01" name="length" class="bc-input" value="{{ old('length', $road?->roadDetails?->length ?? '') }}" placeholder="e.g. 45.5">
                    </div>
                    <div class="col-md-4">
                        <label class="bc-form-label">Width (meters)</label>
                        <input type="number" step="0.01" name="width" class="bc-input" value="{{ old('width', $road?->roadDetails?->width ?? '') }}" placeholder="e.g. 10.5">
                    </div>
                    <div class="col-md-4">
                        <label class="bc-form-label">Number of Lanes</label>
                        <input type="number" name="lanes" class="bc-input" value="{{ old('lanes', $road?->roadDetails?->lanes ?? 2) }}" min="1" max="16">
                    </div>
                    <div class="col-md-4">
                        <label class="bc-form-label">Construction Year</label>
                        <input type="number" name="construction_year" class="bc-input" value="{{ old('construction_year', $road->construction_year ?? '') }}" min="1900" max="{{ date('Y') + 1 }}">
                    </div>
                    <div class="col-md-4">
                        <label class="bc-form-label">Condition <span class="req">*</span></label>
                        <select name="condition" class="bc-select @error('condition') is-invalid @enderror" required>
                            @foreach (['excellent' => 'Excellent', 'good' => 'Good', 'fair' => 'Fair', 'poor' => 'Poor', 'critical' => 'Critical'] as $val => $lbl)
                                <option value="{{ $val }}" @selected(old('condition', $road->condition ?? 'good') === $val)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                        @error('condition') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="bc-form-section">
            <div class="bc-form-section-header">
                <i class="bi bi-images"></i>
                <div>
                    <h3>Image &amp; Description</h3>
                    <small>Visual record and notes</small>
                </div>
            </div>
            <div class="bc-form-section-body">
                <div class="row g-4">
                    <div class="col-md-5">
                        <label class="bc-form-label">Road Image</label>
                        <div class="bc-image-drop" data-input="bcRoadImageInput" data-preview="bcRoadImagePreview">
                            <i class="bi bi-cloud-arrow-up"></i>
                            <p>Click to upload road image</p>
                            <small>JPG, PNG or WEBP • Max 3 MB</small>
                        </div>
                        <input type="file" id="bcRoadImageInput" name="image" accept="image/*" class="d-none">
                        <div class="bc-image-preview" id="bcRoadImagePreview"></div>
                        @if (isset($road) && $road->image)
                            <div class="mt-2">
                                <small style="color:#64748b;">Current image:</small>
                                <img src="{{ $road->imageUrl() }}" alt="" style="max-width:180px;border-radius:10px;border:1px solid #e2e8f0;">
                            </div>
                        @endif
                    </div>
                    <div class="col-md-7">
                        <label class="bc-form-label">Description</label>
                        <textarea name="description" class="bc-textarea" placeholder="Surface type, recent works, known issues...">{{ old('description', $road->description ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap mb-4">
            <button type="submit" class="btn-bc btn-bc-primary"><i class="bi bi-check-lg"></i> @if(isset($road)) Save Changes @else Save Road @endif</button>
            <a href="{{ route('admin.roads.index') }}" class="btn-bc btn-bc-ghost"><i class="bi bi-x-lg"></i> Cancel</a>
        </div>
    </form>
@endsection
