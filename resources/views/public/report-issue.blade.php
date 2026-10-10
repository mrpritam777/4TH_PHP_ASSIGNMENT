@extends('layouts.public')

@section('title', 'Report an Infrastructure Issue')

@section('content')
    <x-page-header title="Report an Infrastructure Issue" subtitle="Help us maintain safe infrastructure by reporting problems you observe.">
    </x-page-header>

    <form method="POST" action="{{ route('public.report.store') }}" data-bc-loading>
        <div class="bc-form-section">
            <div class="bc-form-section-header">
                <i class="bi bi-exclamation-triangle"></i>
                <div>
                    <h3>Issue Details</h3>
                    <small>Tell us about the problem you've observed</small>
                </div>
            </div>
            <div class="bc-form-section-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="bc-form-label">Infrastructure Type <span class="req">*</span></label>
                        <select name="infrastructure_type" class="bc-select @error('infrastructure_type') is-invalid @enderror" required>
                            <option value="">Select type...</option>
                            @foreach (['bridge' => 'Bridge', 'road' => 'Road', 'building' => 'Building', 'hospital' => 'Hospital'] as $val => $lbl)
                                <option value="{{ $val }}" @selected(old('infrastructure_type') === $val)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                        @error('infrastructure_type') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="bc-form-label">Specific Infrastructure <span class="req">*</span></label>
                        <select name="infrastructure_id" class="bc-select @error('infrastructure_id') is-invalid @enderror" required>
                            <option value="">Select infrastructure first...</option>
                        </select>
                        @error('infrastructure_id') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="bc-form-label">Problem Title <span class="req">*</span></label>
                        <input type="text" name="title" class="bc-input @error('title') is-invalid @enderror" placeholder="e.g. Pothole on northbound lane" required>
                        @error('title') <div class="text-danger" style="font-size:12px;margin-top:4px;>{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="bc-form-label">Problem Description</label>
                        <textarea name="description" class="bc-textarea" placeholder="Describe the issue, its location, and any safety concerns..." rows="4"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="bc-form-label">Location (optional)</label>
                        <input type="text" name="location" class="bc-input" placeholder="e.g. Near XYZ landmark">
                    </div>
                    <div class="col-md-6">
                        <label class="bc-form-label">Your Contact Information</label>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="bc-form-label">Your Name <span class="req">*</span></label>
                                <input type="text" name="reporter_name" class="bc-input @error('reporter_name') is-invalid @enderror" value="{{ old('reporter_name') }}" required>
                                @error('reporter_name') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="bc-form-label">Email <span class="req">*</span></label>
                                <input type="email" name="email" class="bc-input @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                                @error('email') <div class="text-danger" style="font-size:12px;margin-top:4px;>{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="bc-form-label">Phone Number</label>
                            <input type="text" name="phone" class="bc-input @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="+91 98765 43210">
                            @error('phone') <div class="text-danger" style="font-size:12px;margin-top:4px;>{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bc-form-section">
            <div class="bc-form-section-header">
                <i class="bi bi-image"></i>
                <div>
                    <h3>Supporting Photo (Optional)</h3>
                    <small>Upload a photo to help us understand the issue better</small>
                </div>
            </div>
            <div class="bc-form-section-body">
                <div class="bc-image-drop" data-input="bcReportImageInput" data-preview="bcReportImagePreview">
                    <i class="bi bi-cloud-arrow-up"></i>
                    <p>Click to upload a photo</p>
                    <small>JPG, PNG or WEBP • Max 3 MB</small>
                </div>
                <input type="file" id="bcReportImageInput" name="image" accept="image/*" class="d-none">
                <div class="bc-image-preview" id="bcReportImagePreview"></div>
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap mb-4">
            <button type="submit" class="btn-bc btn-bc-primary"><i class="bi bi-check-lg"></i> Submit Report</button>
            <a href="{{ route('public.home') }}" class="btn-bc btn-bc-ghost"><i class="bi bi-x-lg"></i> Cancel</a>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        // Dynamic infrastructure loading based on type
        document.addEventListener('DOMContentLoaded', function () {
            var typeSelect = document.querySelector('select[name="infrastructure_type"]');
            var infraSelect = document.querySelector('select[name="infrastructure_id"]');

            if (!typeSelect || !infraSelect) return;

            function loadInfrastructures() {
                var type = typeSelect.value;
                if (!type) {
                    infraSelect.innerHTML = '<option value="">Select infrastructure first...</option>';
                    return;
                }

                infraSelect.innerHTML = '<option value="">Loading...</option>';
                fetch('/infrastructure/ajax?type=' + encodeURIComponent(type))
                    .then(r => r.json())
                    .then(data => {
                        infraSelect.innerHTML = '<option value="">Select infrastructure...</option>';
                        data.forEach(function (infra) {
                            var opt = document.createElement('option');
                            opt.value = infra.id;
                            opt.textContent = infra.name + ' (' + infra.code + ')';
                            infraSelect.appendChild(opt);
                        });
                    })
                    .catch(() => {
                        infraSelect.innerHTML = '<option value="">Error loading list</option>';
                    });
            }

            typeSelect.addEventListener('change', loadInfrastructures);
            // Initialize if there's an old value
            if (typeSelect.value) {
                loadInfrastructures();
            }
        });
    </script>
@endpush
