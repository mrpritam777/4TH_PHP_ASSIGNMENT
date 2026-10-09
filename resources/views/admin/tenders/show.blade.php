@extends('layouts.admin')

@section('title', $tender->title)

@section('content')
    <x-page-header :title="$tender->title" :breadcrumbs="[[ 'label' => 'Tenders', 'url' => route('admin.tenders.index') ]]">
        <x-status-badge :status="$tender->statusLabel()" />
        @if ($tender->status === 'draft')
            <form action="{{ route('admin.tenders.publish', $tender) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn-bc btn-bc-primary btn-bc-sm"><i class="bi bi-megaphone"></i> Publish</button>
            </form>
        @endif
        @if ($tender->status === 'published')
            <form action="{{ route('admin.tenders.close', $tender) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-door-closed"></i> Close Bidding</button>
            </form>
        @endif
        @if ($tender->isCancellable() && ! $tender->hasPendingCancellation())
            <a href="{{ route('admin.cancellations.create', ['tender_id' => $tender->id]) }}" class="btn-bc btn-bc-danger btn-bc-sm"><i class="bi bi-x-octagon"></i> Request Cancellation</a>
        @endif
        @unless (in_array($tender->status, ['awarded', 'cancelled']))
            <a href="{{ route('admin.tenders.edit', $tender) }}" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-pencil"></i> Edit</a>
        @endunless
    </x-page-header>

    @if ($tender->hasPendingCancellation())
        <div class="bc-note-box mb-3"><i class="bi bi-exclamation-triangle me-1"></i> A cancellation request for this tender is pending a decision. No new bids are accepted while it is unresolved.</div>
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
                            <span class="value">{{ $tender->deadline->format('d M Y, H:i') }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Bid Editing</span>
                            <span class="value">{{ $tender->allow_bid_edit ? 'Allowed' : 'Not allowed' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Published</span>
                            <span class="value">{{ $tender->published_at?->format('d M Y, H:i') ?? '—' }}</span>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h4 style="font-size:14px;margin-bottom:8px;">Description</h4>
                        <p style="font-size:13.5px;color:#475569;line-height:1.7;margin:0;">{{ $tender->description }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="bc-card mb-3">
                <div class="bc-card-header">
                    <h3><i class="bi bi-graph-up me-2" style="color:var(--bc-blue);"></i>Bid Summary</h3>
                </div>
                <div class="bc-card-body">
                    <div class="bc-detail-list">
                        <div class="row-item"><span class="k">Bids received</span><span class="v">{{ $bids->count() }}</span></div>
                        <div class="row-item"><span class="k">Lowest eligible bid</span><span class="v">{{ $bids->isNotEmpty() ? '₹'.number_format($bids->where('status', '!=', 'rejected')->min('bid_amount') ?? $bids->min('bid_amount'), 2) : '—' }}</span></div>
                        <div class="row-item">
                            <span class="k">Bidding window</span>
                            <span class="v">@if ($tender->acceptsBids())<span class="bc-deadline-ok">Open</span>@else<span class="bc-deadline-over">Closed</span>@endif</span>
                        </div>
                    </div>
                </div>
            </div>
            @if ($tender->required_documents)
                <div class="bc-card mb-3">
                    <div class="bc-card-header">
                        <h3><i class="bi bi-paperclip me-2" style="color:var(--bc-blue);"></i>Required Documents</h3>
                    </div>
                    <div class="bc-card-body">
                        @foreach ($tender->required_documents as $doc)
                            <span class="bc-doc-chip"><i class="bi bi-file-text"></i> {{ $doc }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
            @if ($tender->status === 'awarded' && $tender->awardedContractor)
                <div class="bc-card">
                    <div class="bc-card-header">
                        <h3><i class="bi bi-trophy me-2" style="color:var(--bc-blue);"></i>Awarded To</h3>
                    </div>
                    <div class="bc-card-body">
                        <div class="bc-detail-list">
                            <div class="row-item"><span class="k">Contractor</span><span class="v">{{ $tender->awardedContractor->company_name }}</span></div>
                            <div class="row-item"><span class="k">Awarded amount</span><span class="v">₹{{ number_format($tender->awarded_amount, 2) }}</span></div>
                            <div class="row-item"><span class="k">Awarded on</span><span class="v">{{ $tender->awarded_at?->format('d M Y, H:i') }}</span></div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="bc-card mb-4">
        <div class="bc-card-header">
            <h3><i class="bi bi-cash-stack me-2" style="color:var(--bc-blue);"></i>Submitted Bids ({{ $bids->count() }}) — sorted by amount</h3>
            <span class="sub">Compare bids side by side and award the tender to the most suitable contractor.</span>
        </div>
        <div class="bc-card-body flush">
            <div class="bc-table-wrap">
                <table class="bc-table">
                    <thead>
                        <tr>
                            <th>Contractor</th>
                            <th>Bid Amount</th>
                            <th>Proposal</th>
                            <th>Documents</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bids as $bid)
                            <tr class="{{ $bid->id === $lowestBidId ? 'bc-winner-row' : '' }}">
                                <td>
                                    <div class="row-title">{{ $bid->contractor?->company_name ?? '—' }}</div>
                                    <div class="row-sub">{{ $bid->contractor?->user?->name }} · {{ $bid->contractor?->user?->email }}</div>
                                    @if ($bid->id === $lowestBidId && $bid->status !== 'rejected')
                                        <span class="bc-badge badge-green mt-1"><i class="bi bi-arrow-down-circle me-1"></i> Lowest bid</span>
                                    @endif
                                </td>
                                <td style="white-space:nowrap;font-weight:800;font-size:14.5px;">₹{{ number_format($bid->bid_amount, 2) }}</td>
                                <td style="max-width:230px;"><span style="font-size:12.5px;">{{ Str::limit($bid->proposal, 120) }}</span></td>
                                <td style="font-size:12.5px;">
                                    @forelse ($bid->documents ?? [] as $idx => $doc)
                                        <a class="bc-doc-chip" href="{{ route('admin.tenders.bids.document', [$tender, $bid, $idx]) }}"><i class="bi bi-download"></i> {{ $doc['label'] ?? $doc['name'] }}</a>
                                    @empty
                                        <span class="text-muted">—</span>
                                    @endforelse
                                </td>
                                <td style="white-space:nowrap;font-size:12.5px;color:#64748b;">{{ $bid->submitted_at?->format('d M Y, H:i') ?? $bid->created_at->format('d M Y, H:i') }}</td>
                                <td><x-status-badge :status="$bid->status" /></td>
                                <td>
                                    @if (in_array($tender->status, ['published', 'closed']) && $bid->status !== 'awarded')
                                        <form action="{{ route('admin.tenders.award', $tender) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="bid_id" value="{{ $bid->id }}">
                                            <button type="submit" class="btn-bc btn-bc-success btn-bc-sm" data-bc-confirm="Award this tender to {{ $bid->contractor?->company_name }} for ₹{{ number_format($bid->bid_amount, 2) }}?">
                                                <i class="bi bi-trophy"></i> Award
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-empty-state icon="bi-inbox" title="No bids yet"
                                                   text="Published tenders appear on contractor dashboards — bids will be listed here." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="bc-card mb-4">
        <div class="bc-card-header">
            <h3><i class="bi bi-clock-history me-2" style="color:var(--bc-blue);"></i>Cancellation History</h3>
            <a href="{{ route('admin.cancellations.create', ['tender_id' => $tender->id]) }}" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-plus-lg"></i> Request Cancellation</a>
        </div>
        <div class="bc-card-body">
            @forelse ($cancellationRequests as $req)
                <div class="bc-tender-card" style="padding:14px 16px;">
                    <div class="bc-tender-top">
                        <div>
                            <x-status-badge :status="$req->status" />
                            <strong class="ms-2" style="font-size:13.5px;">Requested by {{ $req->requester?->name }} ({{ ucfirst($req->requester_role) }})</strong>
                            <div style="font-size:12.5px;color:#64748b;margin-top:4px;">
                                {{ $req->created_at->format('d M Y, H:i') }} · "{{ Str::limit($req->reason, 140) }}"
                            </div>
                        </div>
                        <a href="{{ route('admin.cancellations.show', $req) }}" class="btn-bc btn-bc-outline btn-bc-sm">Review</a>
                    </div>
                </div>
            @empty
                <p class="text-muted" style="font-size:13.5px;margin:0;">No cancellation requests for this tender yet.</p>
            @endforelse
        </div>
    </div>
@endsection
