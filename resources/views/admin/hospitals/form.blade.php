@extends('layouts.admin')

@section('title', isset($hospital) ? 'Edit Hospital' : 'Add Hospital')

@section('content')
    <x-page-header
        :title="isset($hospital) ? 'Edit Hospital' : 'Add New Hospital'"
        :subtitle="isset($hospital) ? 'Update the details for ' . $hospital->name . '.' : 'Fill in the details for the new hospital.'"
        :breadcrumbs="isset($hospital) ? [[ 'label' => 'Hospitals', 'url' => route('admin.hospitals.index') ]] : []">
    </x-page-header>

    <form method="POST" action="{{ isset($hospital) ? route('admin.hospitals.update', $hospital) : route('admin.hospitals.store') }}" enctype="multipart/form-data" data-bc-loading>
        @csrf
        @if (isset($hospital)) @method('PUT') @endif

        <div class="bc-form-section">
            <div class="bc-form-section-header">
                <i class="bi bi-hospital"></i>
                <div>
                    <h3>Hospital Information</h3>
                    <small>Identity, location and facilities</small>
                </div>
            </div>
            <div class="bc-form-section-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="bc-form-label">Hospital Name <span class="req">*</span></label>
                        <input type="text" name="name" class="bc-input @error('name') is-invalid @enderror" value="{{ old('name', $hospital->name ?? '') }}" placeholder="e.g. District Civil Hospital" required>
                        @error('name') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="bc-form-label">Hospital Code <span class="req">*</span></label>
                        <input type="text" name="code" class="bc-input @error('code') is-invalid @enderror" value="{{ old('code', $hospital->code ?? '') }}" placeholder="e.g. HSP-002" required>
                        @error('code') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-5">
                        <label class="bc-form-label">Hospital Type <span class="req">*</span></label>
                        <select name="hospital_type" class="bc-select @error('hospital_type') is-invalid @enderror" required>
                            <option value="">Select type...</option>
                            @foreach (['Government General Hospital', 'District Hospital', 'Community Health Centre', 'Specialty Hospital', 'Multi-specialty Hospital', 'Primary Health Centre', 'Other'] as $type)
                                <option value="{{ $type }}" @selected(old('hospital_type', $hospital?->hospitalDetails?->hospital_type) === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                        @error('hospital_type') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-8">
                        <label class="bc-form-label">Address <span class="req">*</span></label>
                        <input type="text" name="address" class="bc-input @error('address') is-invalid @enderror" value="{{ old('address', $hospital->address ?? '') }}" placeholder="Full address" required>
                        @error('address') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="bc-form-label">Emergency Phone <span class="req">*</span></label>
                        <input type="text" name="emergency_phone" class="bc-input @error('emergency_phone') is-invalid @enderror" value="{{ old('emergency_phone', $hospital?->hospitalDetails?->emergency_phone ?? '') }}" placeholder="+91 80 1234 5678" required>
                        @error('emergency_phone') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="bc-form-label">Email</label>
                        <input type="email" name="email" class="bc-input @error('email') is-invalid @enderror" value="{{ old('email', $hospital?->hospitalDetails?->email ?? '') }}" placeholder="info@hospital.gov.in">
                        @error('email') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="bc-form-label">Website</label>
                        <input type="url" name="website" class="bc-input @error('website') is-invalid @enderror" value="{{ old('website', $hospital?->hospitalDetails?->website ?? '') }}" placeholder="https://...">
                        @error('website') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="bc-form-label">Number of Beds</label>
                        <input type="number" name="beds" class="bc-input" value="{{ old('beds', $hospital?->hospitalDetails?->beds ?? 0) }}" min="0">
                    </div>
                    <div class="col-md-3">
                        <label class="bc-form-label">Construction Year</label>
                        <input type="number" name="construction_year" class="bc-input" value="{{ old('construction_year', $hospital->construction_year ?? '') }}" min="1900" max="{{ date('Y') + 1 }}">
                    </div>
                    <div class="col-md-3">
                        <label class="bc-form-label">Condition <span class="req">*</span></label>
                        <select name="condition" class="bc-select @error('condition') is-invalid @enderror" required>
                            @foreach (['excellent' => 'Excellent', 'good' => 'Good', 'fair' => 'Fair', 'poor' => 'Poor', 'critical' => 'Critical'] as $val => $lbl)
                                <option value="{{ $val }}" @selected(old('condition', $hospital->condition ?? 'good') === $val)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                        @error('condition') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="emergency_facility" id="emergencyFacility" @checked(old('emergency_facility', $hospital?->hospitalDetails?->emergency_facility ?? true))>
                            <label class="form-check-label" for="emergencyFacility" style="font-size:13px;font-weight:600;">24×7 Emergency Facility</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="bc-form-label">Available Departments</label>
                        <input type="text" name="departments" class="bc-input" value="{{ old('departments', $hospital?->hospitalDetails?->departments ?? '') }}" placeholder="e.g. Cardiology, Orthopedics, Pediatrics, General Medicine">
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
                        <label class="bc-form-label">Hospital Image</label>
                        <div class="bc-image-drop" data-input="bcHospitalImageInput" data-preview="bcHospitalImagePreview">
                            <i class="bi bi-cloud-arrow-up"></i>
                            <p>Click to upload hospital image</p>
                            <small>JPG, PNG or WEBP • Max 3 MB</small>
                        </div>
                        <input type="file" id="bcHospitalImageInput" name="image" accept="image/*" class="d-none">
                        <div class="bc-image-preview" id="bcHospitalImagePreview"></div>
                    </div>
                    <div class="col-md-7">
                        <label class="bc-form-label">Description</label>
                        <textarea name="description" class="bc-textarea" placeholder="Services offered, capacity, notable facilities...">{{ old('description', $hospital->description ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap mb-4">
            <button type="submit" class="btn-bc btn-bc-primary"><i class="bi bi-check-lg"></i> @if(isset($hospital)) Save Changes @else Save Hospital @endif</button>
            <a href="{{ route('admin.hospitals.index') }}" class="btn-bc btn-bc-ghost"><i class="bi bi-x-lg"></i> Cancel</a>
        </div>
    </form>
@endsection
