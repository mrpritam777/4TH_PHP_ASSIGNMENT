@extends('layouts.admin')

@section('title', $tender->title)

@section('content')
    <x-page-header :title="$tender->title" :breadcrumbs="[[ 'label' => 'Available Tenders', 'url' => route('contractor.tenders.index') ]]">
        <x-status-badge :status="$tender->statusLabel()" />
        <a href="{{ route('contractor.tenders.index') }}" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-arrow-left"></i> Back</a>
    </x-page-header>

    @if ($projectCancellation)
        <div class="bc-note-box mb-3"><i class="bi bi-exclamation-triangle me-1"></i> A cancellation request for this tender is <strong>pending a decision</strong> — no new or edited bids are accepted while it is unresolved.</div>
    @endif
    @if (! $tender->acceptsBids() && ! $existingBid)
        <div class="bc-note-box danger mb-3"><i class="bi bi-clock-history me-1"></i> The submission deadline for this tender has passed — bidding is closed.</div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <h3><i class="bi bi-file-earmark-text me-2" style="color:var(--bc-blue);"></i>Tender Details</h3>
                </div>
                <div class="bc-card-body">
                    <div class="bc-info-grid">
                        <div class="bc-info-item">
                            <span class="label">Category</span>
                            <span class="value">{{ $tender->type?->name ?? '—' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Location</span>
                            <span class="value">{{ $tender->location }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Estimated Budget</span>
                            <span class="value">₹{{ number_format($tender->estimated_budget, 2) }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Submission Deadline</span>
                            <span class="value {{ $tender->isExpired() ? 'bc-deadline-over' : '' }}">{{ $tender->deadline->format('d M Y, H:i') }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Bid Editing</span>
                            <span class="value">{{ $tender->allow_bid_edit ? 'Allowed until deadline' : 'Not allowed after submission' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Published</span>
                            <span class="value">{{ $tender->published_at?->format('d M Y, H:i') ?? '—' }}</span>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h4 style="font-size:14px;margin-bottom:8px;">Scope of Work</h4>
                        <p style="font-size:13.5px;color:#475569;line-height:1.7;margin:0;">{{ $tender->description }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="bc-card mb-3">
                <div class="bc-card-header">
                    <h3><i class="bi bi-paperclip me-2" style="color:var(--bc-blue);"></i>Required Documents</h3>
                </div>
                <div class="bc-card-body">
                    @if ($tender->required_documents)
                        @foreach ($tender->required_documents as $doc)
                            <span class="bc-doc-chip"><i class="bi bi-file-text"></i> {{ $doc }}</span>
                        @endforeach
                    @else
                        <p class="text-muted" style="font-size:13px;margin:0;">No supporting documents are required for this tender.</p>
                    @endif
                </div>
            </div>
            <div class="bc-card">
                <div class="bc-card-header">
                    <h3><i class="bi bi-hourglass-split me-2" style="color:var(--bc-blue);"></i>Bidding Window</h3>
                </div>
                <div class="bc-card-body">
                    <div class="bc-detail-list">
                        <div class="row-item"><span class="k">Status</span><span class="v">@if ($tender->acceptsBids())<span class="bc-deadline-ok">Open</span>@else<span class="bc-deadline-over">Closed</span>@endif</span></div>
                        <div class="row-item"><span class="k">Time left</span><span class="v">{{ $tender->deadline->isFuture() ? $tender->deadline->diffForHumans() : 'Passed' }}</span></div>
                        <div class="row-item"><span class="k">Your bid</span><span class="v">{{ $existingBid ? 'Submitted' : 'Not submitted' }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($canBid || $canEditBid)
        <div class="bc-form-section mb-4">
            <div class="bc-form-section-header">
                <div><i class="bi bi-cash-coin"></i></div>
                <div>
                    <h3>{{ $existingBid ? 'Edit Your Bid' : 'Submit Your Bid' }}</h3>
                    <small>{{ $existingBid ? 'Your previous submission will be replaced.' : 'All fields are required. Documents must be PDF, JPG or PNG (max 5 MB each).' }}</small>
                </div>
            </div>
            <div class="bc-form-section-body">
                <form method="POST" action="{{ route('contractor.tenders.bids.store', $tender) }}" enctype="multipart/form-data" data-bc-loading>
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="bc-form-label">Bid Amount (₹) <span class="req">*</span></label>
                            <input type="number" step="0.01" min="1" name="bid_amount" class="bc-input"
                                   value="{{ old('bid_amount', $existingBid?->bid_amount) }}" placeholder="e.g. 2500000.00" required>
                            @error('bid_amount') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-12">
                            <label class="bc-form-label">Technical Proposal <span class="req">*</span></label>
                            <textarea name="proposal" class="bc-input bc-textarea" minlength="20" maxlength="5000" rows="6"
                                      placeholder="Describe your approach, team, timeline and relevant experience (min 20 characters)..." required>{{ old('proposal', $existingBid?->proposal) }}</textarea>
                            @error('proposal') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    @if ($tender->required_documents)
                        <div class="mt-3">
                            <label class="bc-form-label">Required Documents <span class="req">*</span></label>
                            <div class="row g-3">
                                @foreach ($tender->required_documents as $index => $documentName)
                                    <div class="col-md-6">
                                        <div style="background:var(--bc-gray-bg);border:1px dashed var(--bc-border);border-radius:10px;padding:12px 14px;">
                                            <label class="bc-form-label" style="margin-bottom:6px;"><i class="bi bi-file-earmark-arrow-up me-1"></i>{{ $documentName }}</label>
                                            <input type="file" name="doc_{{ $index }}" class="bc-input" accept=".pdf,.jpg,.jpeg,.png" required>
                                            @error('doc_'.$index) <small class="text-danger d-block">{{ $message }}</small> @enderror
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if ($existingBid)
                                <small class="text-muted d-block mt-2"><i class="bi bi-info-circle me-1"></i> Documents are required on every submission — please re-select your files when editing.</small>
                            @endif
                        </div>
                    @endif

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn-bc btn-bc-primary"><i class="bi bi-send"></i> {{ $existingBid ? 'Update Bid' : 'Submit Bid' }}</button>
                        <a href="{{ route('contractor.tenders.index') }}" class="btn-bc btn-bc-ghost">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($existingBid)
        <div class="bc-card mb-4">
            <div class="bc-card-header">
                <h3><i class="bi bi-clipboard-check me-2" style="color:var(--bc-blue);"></i>Your Submitted Bid</h3>
                <x-status-badge :status="$existingBid->status" />
            </div>
            <div class="bc-card-body">
                <div class="row g-4">
                    <div class="col-lg-5">
                        <div class="bc-detail-list">
                            <div class="row-item"><span class="k">Bid amount</span><span class="v">₹{{ number_format($existingBid->bid_amount, 2) }}</span></div>
                            <div class="row-item"><span class="k">Submitted</span><span class="v">{{ $existingBid->submitted_at?->format('d M Y, H:i') }}</span></div>
                            <div class="row-item"><span class="k">Status</span><span class="v"><x-status-badge :status="$existingBid->status" /></span></div>
                        </div>
                        @if (! $canEditBid && $tender->acceptsBids() && ! $tender->allow_bid_edit)
                            <div class="bc-note-box info mt-3 mb-0"><i class="bi bi-lock me-1"></i> Bid editing is disabled for this tender.</div>
                        @endif
                        @if (! $canEditBid && $existingBid->status !== 'submitted')
                            <div class="bc-note-box mt-3 mb-0"><i class="bi bi-hourglass-split me-1"></i> Your bid is under review — it can no longer be edited.</div>
                        @endif
                    </div>
                    <div class="col-lg-7">
                        <h4 style="font-size:14px;margin-bottom:8px;">Proposal</h4>
                        <p style="font-size:13.5px;color:#475569;line-height:1.7;margin:0 0 14px;">{{ $existingBid->proposal }}</p>
                        @if ($existingBid->documents)
                            <h4 style="font-size:14px;margin-bottom:8px;">Attached Documents</h4>
                            @foreach ($existingBid->documents as $index => $document)
                                <a href="{{ route('contractor.bids.document', ['bid' => $existingBid, 'index' => $index]) }}" class="bc-doc-chip">
                                    <i class="bi bi-file-earmark-check"></i> {{ $document['name'] ?? 'Document '.($index + 1) }}
                                </a>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @elseif (! $canBid)
        <div class="bc-card mb-4">
            <x-empty-state icon="bi-cash-stack" title="Bidding not possible"
                           text="This tender is not accepting new bids — the deadline has passed, the tender is closed, or a cancellation request is pending." />
        </div>
    @endif

    @if ($tender->isCancellable() && ! $projectCancellation)
        <div class="d-flex justify-content-end mb-2">
            <a href="{{ route('contractor.cancellations.create', ['tender_id' => $tender->id]) }}" class="btn-bc btn-bc-outline btn-bc-sm">
                <i class="bi bi-x-octagon"></i> Request Cancellation
            </a>
        </div>
    @endif
@endsection
