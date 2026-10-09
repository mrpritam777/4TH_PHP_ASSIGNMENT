@extends('layouts.admin')

@section('title', 'Cancellation Request')

@section('content')
    <x-page-header :title="'Cancellation — ' . ($cancellation->tender?->title ?? 'Tender')" :breadcrumbs="[[ 'label' => 'Cancellation Requests', 'url' => route('admin.cancellations.index') ]]">
        <x-status-badge :status="$cancellation->status" />
    </x-page-header>

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <h3><i class="bi bi-flag me-2" style="color:var(--bc-blue);"></i>Request Details</h3>
                </div>
                <div class="bc-card-body">
                    <div class="bc-detail-list">
                        <div class="row-item"><span class="k">Tender</span><span class="v"><a href="{{ route('admin.tenders.show', $cancellation->tender) }}">{{ $cancellation->tender?->title }}</a></span></div>
                        <div class="row-item"><span class="k">Tender status</span><span class="v">{{ ucfirst($cancellation->tender?->status ?? '—') }}</span></div>
                        <div class="row-item"><span class="k">Requested by</span><span class="v">{{ $cancellation->requester?->name }} ({{ ucfirst($cancellation->requester_role) }})</span></div>
                        <div class="row-item"><span class="k">Requested on</span><span class="v">{{ $cancellation->created_at->format('d M Y, H:i') }}</span></div>
                    </div>
                    <h4 style="font-size:14px;margin:16px 0 8px;">Reason</h4>
                    <p style="font-size:13.5px;color:#475569;line-height:1.7;">{{ $cancellation->reason }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <h3><i class="bi bi-patch-check me-2" style="color:var(--bc-blue);"></i>Decision</h3>
                </div>
                <div class="bc-card-body">
                    @if ($cancellation->isPending())
                        @if ($canDecide)
                            <div class="bc-note-box info mb-3">
                                <i class="bi bi-info-circle me-1"></i> Approving will immediately mark this tender as <strong>cancelled</strong>. Rejecting keeps the tender unchanged — a reason is required.
                            </div>
                            <form method="POST" action="{{ route('admin.cancellations.decision', $cancellation) }}" data-bc-loading>
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <label class="bc-form-label">Decision <span class="req">*</span></label>
                                        <select name="decision" class="bc-select" required>
                                            <option value="">Select...</option>
                                            <option value="approved" @selected(old('decision') === 'approved')>Approve (cancel tender)</option>
                                            <option value="rejected" @selected(old('decision') === 'rejected')>Reject (keep tender)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-7">
                                        <label class="bc-form-label">Decision note <small style="font-weight:400;">(required when rejecting)</small></label>
                                        <input type="text" name="decision_note" class="bc-input" value="{{ old('decision_note') }}" maxlength="1000" placeholder="Reason for rejection...">
                                    </div>
                                </div>
                                <button type="submit" class="btn-bc btn-bc-primary mt-3"><i class="bi bi-check-lg"></i> Record Decision</button>
                            </form>
                        @else
                            <div class="bc-note-box mb-3">
                                <i class="bi bi-hourglass-split me-1"></i> Awaiting a decision from the other party.
                            </div>
                            @if ((int) $cancellation->requested_by === auth()->id())
                                <form action="{{ route('admin.cancellations.withdraw', $cancellation) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-x-lg"></i> Withdraw Request</button>
                                </form>
                            @endif
                        @endif
                    @else
                        <div class="bc-detail-list">
                            <div class="row-item"><span class="k">Decision</span><span class="v">{{ ucfirst($cancellation->status) }}</span></div>
                            @if ($cancellation->decision_note)
                                <div class="row-item"><span class="k">Note</span><span class="v">{{ $cancellation->decision_note }}</span></div>
                            @endif
                            <div class="row-item"><span class="k">Decided by</span><span class="v">{{ $cancellation->decider?->name ?? '—' }}</span></div>
                            <div class="row-item"><span class="k">Decided on</span><span class="v">{{ $cancellation->decided_at?->format('d M Y, H:i') ?? '—' }}</span></div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="bc-card mb-4">
        <div class="bc-card-header">
            <h3><i class="bi bi-clock-history me-2" style="color:var(--bc-blue);"></i>Full History for This Tender</h3>
        </div>
        <div class="bc-card-body">
            @foreach ($history as $item)
                <div class="bc-tender-card" style="padding:14px 16px;">
                    <div class="bc-tender-top">
                        <div>
                            <x-status-badge :status="$item->status" />
                            <strong class="ms-2" style="font-size:13.5px;">{{ $item->requester?->name }} ({{ ucfirst($item->requester_role) }})</strong>
                            <div style="font-size:12.5px;color:#64748b;margin-top:4px;">
                                {{ $item->created_at->format('d M Y, H:i') }} · "{{ Str::limit($item->reason, 120) }}"
                                @if ($item->decider)
                                    · Decided by {{ $item->decider->name }} on {{ $item->decided_at?->format('d M Y') }}
                                @endif
                            </div>
                        </div>
                        @if ($item->id !== $cancellation->id)
                            <a href="{{ route('admin.cancellations.show', $item) }}" class="btn-bc btn-bc-outline btn-bc-sm">Open</a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
