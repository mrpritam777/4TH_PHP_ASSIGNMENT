@extends('layouts.admin')

@section('title', $user ? 'Edit Staff' : 'Add Staff')

@section('content')
    <x-page-header
        :title="$user ? 'Edit Staff — ' . $user->name : 'Add Maintenance Staff'"
        :subtitle="$user ? 'Update the staff account details.' : 'Create a new staff account for the field team.'">
    </x-page-header>

    <form method="POST" action="{{ $user ? route('admin.staff.update', $user) : route('admin.staff.store') }}" enctype="multipart/form-data" data-bc-loading>
        @csrf
        @if ($user) @method('PUT') @endif
        <input type="hidden" name="user_id" value="{{ $user->id ?? 0 }}">

        <div class="bc-form-section">
            <div class="bc-form-section-header">
                <i class="bi bi-person-badge"></i>
                <div>
                    <h3>Staff Information</h3>
                    <small>Account and contact details</small>
                </div>
            </div>
            <div class="bc-form-section-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="bc-form-label">Full Name <span class="req">*</span></label>
                        <input type="text" name="name" class="bc-input @error('name') is-invalid @enderror" value="{{ old('name', $user->name ?? '') }}" required>
                        @error('name') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="bc-form-label">Email <span class="req">*</span></label>
                        <input type="email" name="email" class="bc-input @error('email') is-invalid @enderror" value="{{ old('email', $user->email ?? '') }}" required>
                        @error('email') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="bc-form-label">Phone</label>
                        <input type="text" name="phone" class="bc-input @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone ?? '') }}" placeholder="+91 98765 43210">
                        @error('phone') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="bc-form-label">{{ $user ? 'New Password' : 'Password' }} {{ $user ? '(leave blank to keep)' : '<span class="req">*</span>' }}</label>
                        <input type="password" name="password" class="bc-input" {{ $user ? '' : 'required' }} placeholder="Min 8 characters">
                        @error('password') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="bc-form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="bc-input">
                    </div>
                    <div class="col-md-4">
                        <label class="bc-form-label">Status <span class="req">*</span></label>
                        <select name="status" class="bc-select" required>
                            <option value="active" @selected(old('status', $user->status ?? 'active') === 'active')>Active</option>
                            <option value="inactive" @selected(old('status', $user->status ?? '') === 'inactive')>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="bc-form-label">Profile Image</label>
                        <div class="bc-image-drop" data-input="bcStaffImageInput" data-preview="bcStaffImagePreview">
                            <i class="bi bi-cloud-arrow-up"></i>
                            <p>Upload profile photo</p>
                            <small>JPG, PNG or WEBP • Max 2 MB</small>
                        </div>
                        <input type="file" id="bcStaffImageInput" name="profile_image" accept="image/*" class="d-none">
                        <div class="bc-image-preview" id="bcStaffImagePreview"></div>
                        @if ($user?->profile_image)
                            <div class="mt-2">
                                <small style="color:#64748b;">Current photo:</small>
                                <img src="{{ asset('storage/' . $user->profile_image) }}" alt="" style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:2px solid #e2e8f0;">
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap mb-4">
            <button type="submit" class="btn-bc btn-bc-primary"><i class="bi bi-check-lg"></i> {{ $user ? 'Save Changes' : 'Add Staff' }}</button>
            <a href="{{ route('admin.staff.index') }}" class="btn-bc btn-bc-ghost"><i class="bi bi-x-lg"></i> Cancel</a>
        </div>
    </form>
@endsection
