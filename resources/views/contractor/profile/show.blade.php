@extends('layouts.admin')

@section('title', 'My Profile')

@section('content')
    <x-page-header title="My Profile" subtitle="Keep your company details, work categories and documents up to date.">
    </x-page-header>

    @unless ($contractor)
        <div class="bc-card">
            <x-empty-state icon="bi-person-exclamation" title="No contractor profile"
                           text="No contractor profile is linked to this account. Please register as a contractor from the public website."
                           action-label="Register" action-url="{{ route('contractor.register') }}" />
        </div>
    @else
        @if ($contractor->status === 'pending')
            <div class="bc-note-box mb-3"><i class="bi bi-hourglass-split me-1"></i> Your application is <strong>pending approval</strong>. You may update your details while an administrator reviews it.</div>
        @elseif ($contractor->status === 'rejected')
            <div class="bc-note-box danger mb-3">
                <i class="bi bi-x-circle me-1"></i> Your application was rejected.
                @if ($contractor->rejection_reason) Reason: "{{ $contractor->rejection_reason }}" @endif
                Update your details and contact the administrator to re-apply.
            </div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-lg-4">
                <div class="bc-card h-100">
                    <div class="bc-card-header">
                        <h3><i class="bi bi-person-badge me-2" style="color:var(--bc-blue);"></i>Account</h3>
                    </div>
                    <div class="bc-card-body">
                        <div class="bc-detail-list">
                            <div class="row-item"><span class="k">Status</span><span class="v"><x-status-badge :status="$contractor->status" /></span></div>
                            <div class="row-item"><span class="k">Company</span><span class="v">{{ $contractor->company_name }}</span></div>
                            <div class="row-item"><span class="k">Contact person</span><span class="v">{{ $contractor->user?->name }}</span></div>
                            <div class="row-item"><span class="k">Email</span><span class="v">{{ $contractor->user?->email }}</span></div>
                            <div class="row-item"><span class="k">Reviewed</span><span class="v">{{ $contractor->reviewed_at?->format('d M Y') ?? '—' }}</span></div>
                        </div>
                        <div class="mt-3">
                            <h4 style="font-size:13.5px;margin-bottom:8px;">Verification Documents</h4>
                            @if ($contractor->id_document)
                                <a href="{{ route('contractor.profile.document', 'id_document') }}" class="bc-doc-chip"><i class="bi bi-file-earmark-check"></i> Identity Document</a>
                            @endif
                            @if ($contractor->business_document)
                                <a href="{{ route('contractor.profile.document', 'business_document') }}" class="bc-doc-chip"><i class="bi bi-file-earmark-check"></i> Business Document</a>
                            @endif
                            @if (! $contractor->id_document && ! $contractor->business_document)
                                <small class="text-muted" style="font-size:12.5px;">No documents uploaded.</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <form method="POST" action="{{ route('contractor.profile.update') }}" data-bc-loading>
                    @csrf
                    @method('PUT')
                    <div class="bc-form-section mb-3">
                        <div class="bc-form-section-header">
                            <div><i class="bi bi-building"></i></div>
                            <div>
                                <h3>Company Details</h3>
                                <small>Business contact and registration information</small>
                            </div>
                        </div>
                        <div class="bc-form-section-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="bc-form-label">Company / Business Name <span class="req">*</span></label>
                                    <input type="text" name="company_name" class="bc-input" value="{{ old('company_name', $contractor->company_name) }}" required>
                                    @error('company_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="bc-form-label">Mobile Number <span class="req">*</span></label>
                                    <input type="text" name="phone" class="bc-input" value="{{ old('phone', $contractor->user?->phone) }}" required>
                                    @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="bc-form-label">Address <span class="req">*</span></label>
                                    <input type="text" name="address" class="bc-input" value="{{ old('address', $contractor->address) }}" required>
                                    @error('address') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="bc-form-label">Business Registration No.</label>
                                    <input type="text" name="business_registration_no" class="bc-input" value="{{ old('business_registration_no', $contractor->business_registration_no) }}">
                                    @error('business_registration_no') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bc-form-section mb-3">
                        <div class="bc-form-section-header">
                            <div><i class="bi bi-diagram-3"></i></div>
                            <div>
                                <h3>Work Categories</h3>
                                <small>Select every infrastructure category you tender for</small>
                            </div>
                        </div>
                        <div class="bc-form-section-body">
                            @php
                                $selectedCategories = collect(old('work_categories', $contractor->work_categories ?? []))->map(fn ($v) => (int) $v);
                            @endphp
                            <div class="bc-check-grid">
                                @foreach ($categories as $cat)
                                    <label class="d-flex align-items-center gap-2" style="font-size:13px;background:var(--bc-gray-bg);border:1px solid var(--bc-border);border-radius:8px;padding:9px 12px;cursor:pointer;">
                                        <input type="checkbox" name="work_categories[]" value="{{ $cat->id }}" @checked($selectedCategories->contains($cat->id))>
                                        {{ $cat->name }}
                                    </label>
                                @endforeach
                            </div>
                            @error('work_categories') <div class="text-danger" style="font-size:12px;margin-top:6px;">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="bc-form-section mb-3">
                        <div class="bc-form-section-header">
                            <div><i class="bi bi-award"></i></div>
                            <div>
                                <h3>Experience & Qualifications</h3>
                                <small>Shown to administrators when reviewing your bids</small>
                            </div>
                        </div>
                        <div class="bc-form-section-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="bc-form-label">Previous Work Experience <span class="req">*</span></label>
                                    <textarea name="experience" class="bc-input bc-textarea" rows="4" minlength="20" maxlength="3000" required>{{ old('experience', $contractor->experience) }}</textarea>
                                    @error('experience') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="bc-form-label">Licenses & Certifications <span class="req">*</span></label>
                                    <textarea name="licenses" class="bc-input bc-textarea" rows="3" minlength="5" maxlength="2000" required>{{ old('licenses', $contractor->licenses) }}</textarea>
                                    @error('licenses') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bc-form-section mb-3">
                        <div class="bc-form-section-header">
                            <div><i class="bi bi-shield-lock"></i></div>
                            <div>
                                <h3>Change Password</h3>
                                <small>Leave blank to keep your current password</small>
                            </div>
                        </div>
                        <div class="bc-form-section-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="bc-form-label">New Password</label>
                                    <input type="password" name="password" class="bc-input" minlength="8" autocomplete="new-password" placeholder="Min 8 characters">
                                    @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="bc-form-label">Confirm Password</label>
                                    <input type="password" name="password_confirmation" class="bc-input" minlength="8" autocomplete="new-password">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap mb-4">
                        <button type="submit" class="btn-bc btn-bc-primary"><i class="bi bi-check-lg"></i> Save Changes</button>
                        <a href="{{ route('contractor.dashboard') }}" class="btn-bc btn-bc-ghost"><i class="bi bi-x-lg"></i> Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    @endunless
@endsection
