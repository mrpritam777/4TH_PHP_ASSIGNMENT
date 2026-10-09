@extends('layouts.public')

@section('title', 'Register as a Contractor')

@section('content')
    <x-page-header title="Contractor Registration" subtitle="Create your company profile to bid on open infrastructure tenders. Approval typically takes 1–2 working days."
                   :breadcrumbs="[[ 'label' => 'Home', 'url' => route('public.home') ]]">
    </x-page-header>

    <div class="bc-note-box info mb-4">
        <i class="bi bi-info-circle me-1"></i> Already registered? <a href="{{ route('login') }}" style="color:inherit;text-decoration:underline;">Sign in</a> to check your application status. Your documents are stored securely and only visible to administrators.
    </div>

    <form method="POST" action="{{ route('contractor.register.store') }}" enctype="multipart/form-data" data-bc-loading>
        @csrf
        <div class="row g-3 mb-4">
            <div class="col-lg-8">
                <div class="bc-form-section mb-3">
                    <div class="bc-form-section-header">
                        <div><i class="bi bi-person"></i></div>
                        <div>
                            <h3>Contact Person</h3>
                            <small>Primary contact for this application</small>
                        </div>
                    </div>
                    <div class="bc-form-section-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="bc-form-label">Full Name <span class="req">*</span></label>
                                <input type="text" name="name" class="bc-input" value="{{ old('name') }}" required>
                                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="bc-form-label">Mobile Number <span class="req">*</span></label>
                                <input type="text" name="phone" class="bc-input" value="{{ old('phone') }}" placeholder="e.g. +91 98765 43210" required>
                                @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="bc-form-label">Email Address <span class="req">*</span></label>
                                <input type="email" name="email" class="bc-input" value="{{ old('email') }}" required>
                                @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-12">
                                <label class="bc-form-label">Address <span class="req">*</span></label>
                                <input type="text" name="address" class="bc-input" value="{{ old('address') }}" placeholder="Street, city, district, state, PIN" required>
                                @error('address') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bc-form-section mb-3">
                    <div class="bc-form-section-header">
                        <div><i class="bi bi-building"></i></div>
                        <div>
                            <h3>Company Details</h3>
                            <small>Legal business information</small>
                        </div>
                    </div>
                    <div class="bc-form-section-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="bc-form-label">Company / Business Name <span class="req">*</span></label>
                                <input type="text" name="company_name" class="bc-input" value="{{ old('company_name') }}" required>
                                @error('company_name') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="bc-form-label">Business Registration No.</label>
                                <input type="text" name="business_registration_no" class="bc-input" value="{{ old('business_registration_no') }}" placeholder="CIN / GST / local registration">
                                @error('business_registration_no') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="bc-form-label">Infrastructure Work Categories <span class="req">*</span> <small style="font-weight:400;">(select at least one)</small></label>
                            <div class="bc-check-grid">
                                @foreach ($categories as $cat)
                                    <label class="d-flex align-items-center gap-2" style="font-size:13px;background:var(--bc-gray-bg);border:1px solid var(--bc-border);border-radius:8px;padding:9px 12px;cursor:pointer;">
                                        <input type="checkbox" name="work_categories[]" value="{{ $cat->id }}" @checked(in_array($cat->id, collect(old('work_categories', []))->map(fn ($v) => (int) $v)->all()))>
                                        {{ $cat->name }}
                                    </label>
                                @endforeach
                            </div>
                            @error('work_categories') <div class="text-danger" style="font-size:12px;margin-top:6px;">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="bc-form-section mb-3">
                    <div class="bc-form-section-header">
                        <div><i class="bi bi-award"></i></div>
                        <div>
                            <h3>Experience & Qualifications</h3>
                            <small>Help administrators assess your capability</small>
                        </div>
                    </div>
                    <div class="bc-form-section-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="bc-form-label">Previous Work Experience <span class="req">*</span></label>
                                <textarea name="experience" class="bc-input bc-textarea" rows="4" minlength="20" maxlength="3000" placeholder="Describe notable projects, clients, sizes and outcomes (min 20 characters)..." required>{{ old('experience') }}</textarea>
                                @error('experience') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-12">
                                <label class="bc-form-label">Licenses & Certifications <span class="req">*</span></label>
                                <textarea name="licenses" class="bc-input bc-textarea" rows="3" minlength="5" maxlength="2000" placeholder="List relevant licenses, ISO certifications, empanelments..." required>{{ old('licenses') }}</textarea>
                                @error('licenses') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bc-form-section mb-3">
                    <div class="bc-form-section-header">
                        <div><i class="bi bi-file-earmark-lock"></i></div>
                        <div>
                            <h3>Verification Documents</h3>
                            <small>PDF, JPG or PNG — max 5 MB each (private &amp; secure)</small>
                        </div>
                    </div>
                    <div class="bc-form-section-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="bc-form-label">Identity Document <span class="req">*</span></label>
                                <input type="file" name="id_document" class="bc-input" accept=".pdf,.jpg,.jpeg,.png" required>
                                @error('id_document') <small class="text-danger d-block">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="bc-form-label">Business Verification Document <span class="req">*</span></label>
                                <input type="file" name="business_document" class="bc-input" accept=".pdf,.jpg,.jpeg,.png" required>
                                @error('business_document') <small class="text-danger d-block">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="bc-form-section mb-3">
                    <div class="bc-form-section-header">
                        <div><i class="bi bi-shield-lock"></i></div>
                        <div>
                            <h3>Account Credentials</h3>
                            <small>Used to sign in after approval</small>
                        </div>
                    </div>
                    <div class="bc-form-section-body">
                        <div class="mb-3">
                            <label class="bc-form-label">Password <span class="req">*</span></label>
                            <input type="password" name="password" class="bc-input" minlength="8" required autocomplete="new-password" placeholder="Min 8 characters">
                            @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div>
                            <label class="bc-form-label">Confirm Password <span class="req">*</span></label>
                            <input type="password" name="password_confirmation" class="bc-input" minlength="8" required autocomplete="new-password">
                        </div>
                    </div>
                </div>

                <div class="bc-card mb-3">
                    <div class="bc-card-header">
                        <h3><i class="bi bi-check2-circle me-2" style="color:var(--bc-blue);"></i>What Happens Next</h3>
                    </div>
                    <div class="bc-card-body">
                        <div class="bc-timeline">
                            <div class="t-item">
                                <h4>1. Submit application</h4>
                                <p>Your details and documents are sent to our review team.</p>
                            </div>
                            <div class="t-item">
                                <h4>2. Admin verification</h4>
                                <p>We verify your company information — usually within 1–2 working days.</p>
                            </div>
                            <div class="t-item">
                                <h4>3. Start bidding</h4>
                                <p>Once approved, you can submit bids on every open tender in your categories.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bc-card mb-3">
                    <div class="bc-card-body">
                        <h3 style="font-size:14px;margin-bottom:10px;"><i class="bi bi-lock me-2" style="color:var(--bc-blue);"></i>Your Privacy</h3>
                        <p style="font-size:12.5px;color:#64748b;line-height:1.7;margin:0;">Uploaded documents are stored on a private disk and can only be downloaded by administrators during review. They are never exposed through public URLs.</p>
                    </div>
                </div>

                <button type="submit" class="btn-bc btn-bc-primary btn-bc-lg w-100" style="justify-content:center;">
                    <i class="bi bi-send"></i> Submit Application
                </button>
                <small class="d-block text-muted mt-2" style="font-size:12px;text-align:center;">By submitting, you confirm the information provided is accurate.</small>
            </div>
        </div>
    </form>
@endsection
