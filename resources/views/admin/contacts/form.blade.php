@extends('layouts.admin')

@section('title', $contact ? 'Edit Contact' : 'Add Contact')

@section('content')
    <x-page-header
        :title="$contact ? 'Edit Contact — ' . $contact->name : 'Add New Contact'"
        :subtitle="$contact ? 'Update the contact details.' : 'Add a new contact to the directory.'">
    </x-page-header>

    <form method="POST" action="{{ $contact ? route('admin.contacts.update', $contact) : route('admin.contacts.store') }}" data-bc-loading>
        @csrf
        @if ($contact) @method('PUT') @endif

        <div class="bc-form-section">
            <div class="bc-form-section-header">
                <i class="bi bi-person-badge"></i>
                <div>
                    <h3>Contact Details</h3>
                    <small>Identity and contact information</small>
                </div>
            </div>
            <div class="bc-form-section-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="bc-form-label">Contact Name <span class="req">*</span></label>
                        <input type="text" name="name" class="bc-input @error('name') is-invalid @enderror" value="{{ old('name', $contact->name ?? '') }}" required>
                        @error('name') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="bc-form-label">Organization</label>
                        <input type="text" name="organization" class="bc-input" value="{{ old('organization', $contact->organization ?? '') }}" placeholder="e.g. NHAI, PWD">
                    </div>
                    <div class="col-md-4">
                        <label class="bc-form-label">Department</label>
                        <input type="text" name="department" class="bc-input" value="{{ old('department', $contact->department ?? '') }}" placeholder="e.g. Bridges Division">
                    </div>
                    <div class="col-md-4">
                        <label class="bc-form-label">Designation</label>
                        <input type="text" name="designation" class="bc-input" value="{{ old('designation', $contact->designation ?? '') }}" placeholder="e.g. Executive Engineer">
                    </div>
                    <div class="col-md-4">
                        <label class="bc-form-label">Phone</label>
                        <input type="text" name="phone" class="bc-input @error('phone') is-invalid @enderror" value="{{ old('phone', $contact->phone ?? '') }}" placeholder="+91 98765 43210">
                        @error('phone') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="bc-form-label">Email</label>
                        <input type="email" name="email" class="bc-input @error('email') is-invalid @enderror" value="{{ old('email', $contact->email ?? '') }}" placeholder="name@organization.gov.in">
                        @error('email') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="bc-form-label">Contact Type <span class="req">*</span></label>
                        <select name="contact_type" class="bc-select @error('contact_type') is-invalid @enderror" required>
                            @foreach (['emergency' => 'Emergency', 'government' => 'Government', 'maintenance' => 'Maintenance', 'hospital' => 'Hospital', 'contractor' => 'Contractor', 'other' => 'Other'] as $val => $lbl)
                                <option value="{{ $val }}" @selected(old('contact_type', $contact->contact_type ?? 'other') === $val)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                        @error('contact_type') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="bc-form-label">Related Infrastructure</label>
                        <select name="infrastructure_id" class="bc-select">
                            <option value="">— None —</option>
                            @foreach ($infrastructures as $infra)
                                <option value="{{ $infra->id }}" @selected(old('infrastructure_id', $contact->infrastructure_id ?? null) == $infra->id)>
                                    {{ $infra->name }} ({{ $infra->type->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="bc-form-label">Address</label>
                        <input type="text" name="address" class="bc-input" value="{{ old('address', $contact->address ?? '') }}" placeholder="Office address">
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap mb-4">
            <button type="submit" class="btn-bc btn-bc-primary"><i class="bi bi-check-lg"></i> {{ $contact ? 'Save Changes' : 'Add Contact' }}</button>
            <a href="{{ route('admin.contacts.index') }}" class="btn-bc btn-bc-ghost"><i class="bi bi-x-lg"></i> Cancel</a>
        </div>
    </form>
@endsection
