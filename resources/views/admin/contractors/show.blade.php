@extends('layouts.admin')

@section('title', 'Review Contractor')

@section('content')
    <x-page-header :title="'Review: ' . $contractor->company_name" :breadcrumbs="[[ 'label' => 'Contractors', 'url' => route('admin.contractors.index') ]]">
        <x-status-badge :status="$contractor->status" />
        @if ($contractor->status === 'approved')
            <form action="{{ route('admin.contractors.toggle-status', $contractor) }}" method="POST" class="d-inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn-bc btn-bc-outline btn-bc-sm">
                    <i class="bi bi-power"></i> {{ $contractor->user->isActive() ? 'Deactivate Account' : 'Activate Account' }}
                </button>
            </form>
        @endif
    </x-page-header>

    @if ($contractor->status === 'pending')
        <div class="bc-note-box info mb-3">
            <i class="bi bi-info-circle me-1"></i> This application is awaiting review. Approving activates the contractor account and notifies them; rejecting requires a reason, which is shared with the contractor.
        </div>
    @elseif ($contractor->status === 'rejected')
        <div class="bc-note-box danger mb-3">
            <i class="bi bi-x-circle me-1"></i> <strong>Rejected {{ $contractor->reviewed_at?->format('d M Y') }}:</strong> {{ $contractor->rejection_reason }}
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <h3><i class="bi bi-person-badge me-2" style="color:var(--bc-blue);"></i>Personal &amp; Contact</h3>
                </div>
                <div class="bc-card-body">
                    <div class="bc-detail-list">
                        <div class="row-item"><span class="k">Full name</span><span class="v">{{ $contractor->user->name }}</span></div>
                        <div class="row-item"><span class="k">Mobile</span><span class="v">{{ $contractor->user->phone }}</span></div>
                        <div class="row-item"><span class="k">Email</span><span class="v">{{ $contractor->user->email }}</span></div>
                        <div class="row-item"><span class="k">Address</span><span class="v">{{ $contractor->address }}</span></div>
                        <div class="row-item"><span class="k">Login status</span><span class="v">{{ $contractor->user->isActive() ? 'Active' : 'Inactive' }}</span></div>
                        <div class="row-item"><span class="k">Applied on</span><span class="v">{{ $contractor->created_at->format('d M Y, H:i') }}</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <h3><i class="bi bi-building me-2" style="color:var(--bc-blue);"></i>Business Profile</h3>
                </div>
                <div class="bc-card-body">
                    <div class="bc-detail-list">
                        <div class="row-item"><span class="k">Company</span><span class="v">{{ $contractor->company_name }}</span></div>
                        <div class="row-item"><span class="k">Registration</span><span class="v">{{ $contractor->business_registration_no ?? '—' }}</span></div>
                    </div>
                    <div class="mt-3">
                        <div class="k" style="font-size:12px;color:var(--bc-text-muted);margin-bottom:6px;">Work categories</div>
                        @foreach ($categories as $cat)
                            <span class="bc-doc-chip"><i class="bi bi-diagram-3"></i> {{ $cat->name }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="bc-card mb-4">
        <div class="bc-card-header">
            <h3><i class="bi bi-briefcase me-2" style="color:var(--bc-blue);"></i>Experience &amp; Credentials</h3>
        </div>
        <div class="bc-card-body">
            <h4 style="font-size:14px;margin-bottom:8px;">Previous work experience &amp; portfolio</h4>
            <p style="font-size:13.5px;color:#475569;line-height:1.7;">{{ $contractor->experience }}</p>
            <h4 style="font-size:14px;margin:16px 0 8px;">Licenses &amp; certifications</h4>
            <p style="font-size:13.5px;color:#475569;line-height:1.7;">{{ $contractor->licenses }}</p>
        </div>
    </div>

    <div class="bc-card mb-4">
        <div class="bc-card-header">
            <h3><i class="bi bi-file-earmark-lock me-2" style="color:var(--bc-blue);"></i>Verification Documents</h3>
            <span class="sub">Stored privately — accessible only to administrators and the applicant.</span>
        </div>
        <div class="bc-card-body">
            <a class="bc-doc-chip" href="{{ route('admin.contractors.document', [$contractor, 'id_document']) }}"><i class="bi bi-download"></i> Identity document</a>
            <a class="bc-doc-chip" href="{{ route('admin.contractors.document', [$contractor, 'business_document']) }}"><i class="bi bi-download"></i> Business verification document</a>
        </div>
    </div>

    @if ($contractor->status === 'pending')
        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="bc-card">
                    <div class="bc-card-header"><h3><i class="bi bi-check-circle me-2" style="color:var(--bc-green);"></i>Approve</h3></div>
                    <div class="bc-card-body">
                        <p style="font-size:13.5px;color:#475569;">Activates the contractor account. The contractor will be notified and can immediately browse tenders and bid.</p>
                        <form action="{{ route('admin.contractors.approve', $contractor) }}" method="POST" data-bc-loading>
                            @csrf
                            <button type="submit" class="btn-bc btn-bc-success"><i class="bi bi-check-lg"></i> Approve Application</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="bc-card">
                    <div class="bc-card-header"><h3><i class="bi bi-x-circle me-2" style="color:var(--bc-red);"></i>Reject</h3></div>
                    <div class="bc-card-body">
                        <form action="{{ route('admin.contractors.reject', $contractor) }}" method="POST" data-bc-loading>
                            @csrf
                            <label class="bc-form-label">Rejection reason <span class="req">*</span> <small style="font-weight:400;">(shared with the applicant)</small></label>
                            <textarea name="reason" class="bc-textarea @error('reason') is-invalid @enderror" required minlength="10" rows="3" placeholder="e.g. Identity document is expired — please re-apply with a valid document.">{{ old('reason') }}</textarea>
                            @error('reason') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                            <button type="submit" class="btn-bc btn-bc-danger mt-3"><i class="bi bi-x-lg"></i> Reject Application</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="bc-card mb-4">
        <div class="bc-card-header">
            <h3><i class="bi bi-cash-stack me-2" style="color:var(--bc-blue);"></i>Bid Activity ({{ $contractor->bids->count() }})</h3>
        </div>
        <div class="bc-card-body">
            @forelse ($contractor->bids as $bid)
                <div class="bc-tender-card" style="padding:14px 16px;">
                    <div class="bc-tender-top">
                        <div>
                            <strong style="font-size:13.5px;">{{ $bid->tender?->title }}</strong>
                            <div style="font-size:12.5px;color:#64748b;margin-top:4px;">
                                Bid ₹{{ number_format($bid->bid_amount, 2) }} · {{ $bid->submitted_at?->format('d M Y, H:i') }}
                            </div>
                        </div>
                        <x-status-badge :status="$bid->status" />
                    </div>
                </div>
            @empty
                <p class="text-muted" style="font-size:13.5px;margin:0;">This contractor has not submitted any bids yet.</p>
            @endforelse
        </div>
    </div>
@endsection
