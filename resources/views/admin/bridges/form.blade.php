@extends('layouts.admin')

@section('title', isset($bridge) ? 'Edit Bridge' : 'Add Bridge')

@section('content')
    <x-page-header
        :title="isset($bridge) ? 'Edit Bridge' : 'Add New Bridge'"
        :subtitle="isset($bridge) ? 'Update the details for ' . $bridge->name . '.' : 'Fill in the complete profile for the new bridge.'"
        :breadcrumbs="isset($bridge) ? [[ 'label' => 'Bridges', 'url' => route('admin.bridges.index') ]] : []">
    </x-page-header>

    <form method="POST" action="{{ isset($bridge) ? route('admin.bridges.update', $bridge) : route('admin.bridges.store') }}" enctype="multipart/form-data" data-bc-loading>
        @csrf
        @if (isset($bridge)) @method('PUT') @endif

        {{-- Basic Information --}}
        <div class="bc-form-section">
            <div class="bc-form-section-header">
                <i class="bi bi-signpost-split"></i>
                <div>
                    <h3>Basic Information</h3>
                    <small>Identity and classification of the bridge</small>
                </div>
            </div>
            <div class="bc-form-section-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="bc-form-label">Bridge Name <span class="req">*</span></label>
                        <input type="text" name="name" class="bc-input @error('name') is-invalid @enderror" value="{{ old('name', $bridge->name ?? '') }}" placeholder="e.g. Ghatsil Bridge" required>
                        @error('name') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="bc-form-label">Bridge Code <span class="req">*</span></label>
                        <input type="text" name="code" class="bc-input @error('code') is-invalid @enderror" value="{{ old('code', $bridge->code ?? '') }}" placeholder="e.g. BRG-001" required>
                        @error('code') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="bc-form-label">Bridge Number</label>
                        <input type="text" name="bridge_number" class="bc-input" value="{{ old('bridge_number') }}" placeholder="e.g. NB-14">
                    </div>
                    <div class="col-md-6">
                        <label class="bc-form-label">Bridge Type <span class="req">*</span></label>
                        <select name="bridge_type" class="bc-select @error('bridge_type') is-invalid @enderror" required>
                            <option value="">Select type...</option>
                            @foreach (['Suspension Bridge', 'Arch Bridge', 'Girder Bridge', 'Cable-Stayed Bridge', 'Beam Bridge', 'Truss Bridge', 'Culvert', 'Flyover / Overpass', 'Foot Bridge', 'Other'] as $type)
                                <option value="{{ $type }}" @selected(old('bridge_type', $bridge?->bridgeDetails?->bridge_type) === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                        @error('bridge_type') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Location --}}
        <div class="bc-form-section">
            <div class="bc-form-section-header">
                <i class="bi bi-geo-alt"></i>
                <div>
                    <h3>Location</h3>
                    <small>Administrative and geographic position</small>
                </div>
            </div>
            <div class="bc-form-section-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="bc-form-label">State <span class="req">*</span></label>
                        <input type="text" name="state" class="bc-input @error('state') is-invalid @enderror" value="{{ old('state', isset($bridge) ? ($bridge->location ? explode(',', $bridge->location)[0] : '') : '') }}" placeholder="e.g. West Bengal" required>
                        @error('state') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="bc-form-label">District <span class="req">*</span></label>
                        <input type="text" name="district" class="bc-input @error('district') is-invalid @enderror" value="{{ old('district', isset($bridge) && $bridge->location && str_contains($bridge->location, ',') ? explode(',', $bridge->location)[1] ?? '' : '') }}" placeholder="e.g. Howrah" required>
                        @error('district') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="bc-form-label">City <span class="req">*</span></label>
                        <input type="text" name="city" class="bc-input @error('city') is-invalid @enderror" value="{{ old('city', isset($bridge) && $bridge->location && str_contains($bridge->location, ',') ? explode(',', $bridge->location)[2] ?? '' : '') }}" placeholder="e.g. Howrah" required>
                        @error('city') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="bc-form-label">Area</label>
                        <input type="text" name="area" class="bc-input" value="{{ old('area') }}" placeholder="e.g. Ghatsil">
                    </div>
                    <div class="col-md-6">
                        <label class="bc-form-label">Full Address</label>
                        <input type="text" name="address" class="bc-input" value="{{ old('address', $bridge->address ?? '') }}" placeholder="Complete street address">
                    </div>
                    <div class="col-md-3">
                        <label class="bc-form-label">Latitude</label>
                        <input type="number" step="any" name="latitude" class="bc-input" value="{{ old('latitude', $bridge->latitude ?? '') }}" placeholder="22.585000">
                    </div>
                    <div class="col-md-3">
                        <label class="bc-form-label">Longitude</label>
                        <input type="number" step="any" name="longitude" class="bc-input" value="{{ old('longitude', $bridge->longitude ?? '') }}" placeholder="88.350000">
                    </div>
                </div>
            </div>
        </div>

        {{-- Construction Details --}}
        <div class="bc-form-section">
            <div class="bc-form-section-header">
                <i class="bi bi-rulers"></i>
                <div>
                    <h3>Construction Details</h3>
                    <small>Physical specifications of the structure</small>
                </div>
            </div>
            <div class="bc-form-section-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="bc-form-label">Construction Year</label>
                        <input type="number" name="construction_year" class="bc-input" value="{{ old('construction_year', $bridge->construction_year ?? '') }}" min="1900" max="{{ date('Y') + 1 }}" placeholder="e.g. 1962">
                    </div>
                    <div class="col-md-3">
                        <label class="bc-form-label">Length (meters)</label>
                        <input type="number" step="0.01" name="length" class="bc-input" value="{{ old('length', $bridge?->bridgeDetails?->length ?? '') }}" placeholder="e.g. 450">
                    </div>
                    <div class="col-md-3">
                        <label class="bc-form-label">Width (meters)</label>
                        <input type="number" step="0.01" name="width" class="bc-input" value="{{ old('width', $bridge?->bridgeDetails?->width ?? '') }}" placeholder="e.g. 12.5">
                    </div>
                    <div class="col-md-3">
                        <label class="bc-form-label">Number of Lanes</label>
                        <input type="number" name="lanes" class="bc-input" value="{{ old('lanes', $bridge?->bridgeDetails?->lanes ?? 2) }}" min="1" max="12">
                    </div>
                    <div class="col-md-6">
                        <label class="bc-form-label">Bridge Material</label>
                        <select name="material" class="bc-select">
                            <option value="">Select material...</option>
                            @foreach (['Reinforced Concrete', 'Steel', 'Composite (Steel + Concrete)', 'Pre-Stressed Concrete', 'Timber', 'Stone', 'Other'] as $mat)
                                <option value="{{ $mat }}" @selected(old('material', $bridge?->bridgeDetails?->material) === $mat)>{{ $mat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="bc-form-label">Designed Load Capacity</label>
                        <input type="text" name="load_capacity" class="bc-input" value="{{ old('load_capacity', $bridge?->bridgeDetails?->load_capacity ?? '') }}" placeholder="e.g. 70 tonnes (IS: Class A)">
                    </div>
                </div>
            </div>
        </div>

        {{-- Condition --}}
        <div class="bc-form-section">
            <div class="bc-form-section-header">
                <i class="bi bi-clipboard2-check"></i>
                <div>
                    <h3>Current Condition</h3>
                    <small>Latest assessment of structural and surface health</small>
                </div>
            </div>
            <div class="bc-form-section-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="bc-form-label">Current Condition <span class="req">*</span></label>
                        <select name="condition" class="bc-select @error('condition') is-invalid @enderror" required>
                            @foreach (['excellent' => 'Excellent', 'good' => 'Good', 'fair' => 'Fair', 'poor' => 'Poor', 'critical' => 'Critical'] as $val => $lbl)
                                <option value="{{ $val }}" @selected(old('condition', $bridge->condition ?? 'good') === $val)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                        @error('condition') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="bc-form-label">Structural Condition <span class="req">*</span></label>
                        <select name="structural_condition" class="bc-select @error('structural_condition') is-invalid @enderror" required>
                            @foreach (['excellent' => 'Excellent', 'good' => 'Good', 'fair' => 'Fair', 'poor' => 'Poor', 'critical' => 'Critical'] as $val => $lbl)
                                <option value="{{ $val }}" @selected(old('structural_condition', $bridge?->bridgeDetails?->structural_condition ?? 'good') === $val)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                        @error('structural_condition') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="bc-form-label">Road Surface Condition <span class="req">*</span></label>
                        <select name="surface_condition" class="bc-select @error('surface_condition') is-invalid @enderror" required>
                            @foreach (['excellent' => 'Excellent', 'good' => 'Good', 'fair' => 'Fair', 'poor' => 'Poor', 'critical' => 'Critical'] as $val => $lbl)
                                <option value="{{ $val }}" @selected(old('surface_condition', $bridge?->bridgeDetails?->surface_condition ?? 'good') === $val)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                        @error('surface_condition') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="bc-form-label">Safety Status <span class="req">*</span></label>
                        <select name="safety_status" class="bc-select @error('safety_status') is-invalid @enderror" required>
                            <option value="safe" @selected(old('safety_status', $bridge?->bridgeDetails?->safety_status ?? 'safe') === 'safe')>Safe</option>
                            <option value="caution" @selected(old('safety_status', $bridge?->bridgeDetails?->safety_status) === 'caution')>Caution</option>
                            <option value="unsafe" @selected(old('safety_status', $bridge?->bridgeDetails?->safety_status) === 'unsafe')>Unsafe</option>
                        </select>
                        @error('safety_status') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="bc-form-label">Last Inspection Date</label>
                        <input type="date" name="last_inspection_date" class="bc-input" value="{{ old('last_inspection_date', isset($bridge) ? $bridge?->bridgeDetails?->last_inspection_date?->format('Y-m-d') : '') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Images --}}
        <div class="bc-form-section">
            <div class="bc-form-section-header">
                <i class="bi bi-images"></i>
                <div>
                    <h3>Images</h3>
                    <small>Main bridge image and optional gallery</small>
                </div>
            </div>
            <div class="bc-form-section-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="bc-form-label">Main Bridge Image</label>
                        <div class="bc-image-drop" data-input="bcMainImageInput" data-preview="bcMainImagePreview">
                            <i class="bi bi-cloud-arrow-up"></i>
                            <p>Click to upload the main bridge image</p>
                            <small>JPG, PNG or WEBP • Max 3 MB</small>
                        </div>
                        <input type="file" id="bcMainImageInput" name="image" accept="image/*" class="d-none">
                        <div class="bc-image-preview" id="bcMainImagePreview"></div>
                        @if (isset($bridge) && $bridge->image)
                            <div class="mt-2">
                                <small style="color:#64748b;">Current image:</small>
                                <img src="{{ $bridge->imageUrl() }}" alt="" style="max-width:180px;border-radius:10px;border:1px solid #e2e8f0;">
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="bc-form-label">Gallery Images (optional)</label>
                        <div class="bc-image-drop" data-input="bcGalleryInput" data-preview="bcGalleryPreviews" style="padding:16px;">
                            <i class="bi bi-images" style="font-size:24px;"></i>
                            <p style="margin-top:6px;">Click to add multiple gallery images</p>
                        </div>
                        <input type="file" id="bcGalleryInput" name="gallery[]" accept="image/*" multiple class="d-none">
                        <div class="bc-gallery-previews" id="bcGalleryPreviews"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Description --}}
        <div class="bc-form-section">
            <div class="bc-form-section-header">
                <i class="bi bi-text-paragraph"></i>
                <div>
                    <h3>Description &amp; Notes</h3>
                    <small>Additional context about the bridge</small>
                </div>
            </div>
            <div class="bc-form-section-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="bc-form-label">Bridge Description</label>
                        <textarea name="description" class="bc-textarea" placeholder="History, significance, key features...">{{ old('description', $bridge->description ?? '') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="bc-form-label">Additional Notes</label>
                        <textarea name="notes" class="bc-textarea" placeholder="Internal notes, upcoming works, restrictions...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="d-flex gap-2 flex-wrap mb-4">
            <button type="submit" class="btn-bc btn-bc-primary"><i class="bi bi-check-lg"></i> @if(isset($bridge)) Save Changes @else Save Bridge @endif</button>
            @if (!isset($bridge))
                <button type="submit" class="btn-bc btn-bc-outline" formnovalidate onclick="var b=this.closest('form').querySelector('button[type=submit]');if(b){b.innerHTML='Saving...';}"><i class="bi bi-plus-square"></i> Save &amp; Add Another</button>
            @endif
            <a href="{{ route('admin.bridges.index') }}" class="btn-bc btn-bc-ghost"><i class="bi bi-x-lg"></i> Cancel</a>
        </div>
    </form>
@endsection
