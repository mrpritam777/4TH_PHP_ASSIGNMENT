@extends('layouts.admin')

@section('title', isset($building) ? 'Edit Building' : 'Add Building')

@section('content')
    <x-page-header
        :title="isset($building) ? 'Edit Building' : 'Add New Building'"
        :subtitle="isset($building) ? 'Update the details for ' . $building->name . '.' : 'Fill in the details for the new building.'"
        :breadcrumbs="isset($building) ? [[ 'label' => 'Buildings', 'url' => route('admin.buildings.index') ]] : []">
    </x-page-header>

    <form method="POST" action="{{ isset($building) ? route('admin.buildings.update', $building) : route('admin.buildings.store') }}" enctype="multipart/form-data" data-bc-loading>
        @csrf
        @if (isset($building)) @method('PUT') @endif

        <div class="bc-form-section">
            <div class="bc-form-section-header">
                <i class="bi bi-buildings"></i>
                <div>
                    <h3>Building Information</h3>
                    <small>Identity, location and structure</small>
                </div>
            </div>
            <div class="bc-form-section-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="bc-form-label">Building Name <span class="req">*</span></label>
                        <input type="text" name="name" class="bc-input @error('name') is-invalid @enderror" value="{{ old('name', $building->name ?? '') }}" placeholder="e.g. City Civil Hospital Block A" required>
                        @error('name') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="bc-form-label">Building Code <span class="req">*</span></label>
                        <input type="text" name="code" class="bc-input @error('code') is-invalid @enderror" value="{{ old('code', $building->code ?? '') }}" placeholder="e.g. BLD-007" required>
                        @error('code') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-5">
                        <label class="bc-form-label">Building Type <span class="req">*</span></label>
                        <select name="building_type" class="bc-select @error('building_type') is-invalid @enderror" required>
                            <option value="">Select type...</option>
                            @foreach (['Government Office', 'Public Building', 'Residential', 'Educational', 'Healthcare', 'Commercial', 'Industrial', 'Warehouse', 'Other'] as $type)
                                <option value="{{ $type }}" @selected(old('building_type', $building?->buildingDetails?->building_type) === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                        @error('building_type') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-8">
                        <label class="bc-form-label">Address <span class="req">*</span></label>
                        <input type="text" name="address" class="bc-input @error('address') is-invalid @enderror" value="{{ old('address', $building->address ?? '') }}" placeholder="Full address including city and state" required>
                        @error('address') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="bc-form-label">Owner Department</label>
                        <input type="text" name="department" class="bc-input" value="{{ old('department', $building?->buildingDetails?->department ?? '') }}" placeholder="e.g. Public Works Department">
                    </div>
                    <div class="col-md-3">
                        <label class="bc-form-label">Number of Floors</label>
                        <input type="number" name="floors" class="bc-input" value="{{ old('floors', $building?->buildingDetails?->floors ?? 1) }}" min="1">
                    </div>
                    <div class="col-md-3">
                        <label class="bc-form-label">Area (sq. ft)</label>
                        <input type="number" step="0.01" name="area" class="bc-input" value="{{ old('area', $building?->buildingDetails?->area ?? '') }}" placeholder="e.g. 25000">
                    </div>
                    <div class="col-md-3">
                        <label class="bc-form-label">Construction Year</label>
                        <input type="number" name="construction_year" class="bc-input" value="{{ old('construction_year', $building->construction_year ?? '') }}" min="1900" max="{{ date('Y') + 1 }}">
                    </div>
                    <div class="col-md-3">
                        <label class="bc-form-label">Condition <span class="req">*</span></label>
                        <select name="condition" class="bc-select @error('condition') is-invalid @enderror" required>
                            @foreach (['excellent' => 'Excellent', 'good' => 'Good', 'fair' => 'Fair', 'poor' => 'Poor', 'critical' => 'Critical'] as $val => $lbl)
                                <option value="{{ $val }}" @selected(old('condition', $building->condition ?? 'good') === $val)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                        @error('condition') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="bc-form-label">Contact</label>
                        <input type="text" name="contact" class="bc-input" value="{{ old('contact') }}" placeholder="In-charge name / phone">
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
                        <label class="bc-form-label">Building Image</label>
                        <div class="bc-image-drop" data-input="bcBuildingImageInput" data-preview="bcBuildingImagePreview">
                            <i class="bi bi-cloud-arrow-up"></i>
                            <p>Click to upload building image</p>
                            <small>JPG, PNG or WEBP • Max 3 MB</small>
                        </div>
                        <input type="file" id="bcBuildingImageInput" name="image" accept="image/*" class="d-none">
                        <div class="bc-image-preview" id="bcBuildingImagePreview"></div>
                    </div>
                    <div class="col-md-7">
                        <label class="bc-form-label">Description</label>
                        <textarea name="description" class="bc-textarea" placeholder="Purpose, occupancy, known issues...">{{ old('description', $building->description ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap mb-4">
            <button type="submit" class="btn-bc btn-bc-primary"><i class="bi bi-check-lg"></i> @if(isset($building)) Save Changes @else Save Building @endif</button>
            <a href="{{ route('admin.buildings.index') }}" class="btn-bc btn-bc-ghost"><i class="bi bi-x-lg"></i> Cancel</a>
        </div>
    </form>
@endsection
