@extends('layouts.admin')

@section('title', 'Contractor Dashboard')

@section('content')
    <x-page-header title="Welcome, {{ $contractor?->company_name ?? auth()->user()->name }}" subtitle="Your tendering workspace at a glance.">
        <a href="{{ route('contractor.tenders.index') }}" class="btn-bc btn-bc-primary"><i class="bi bi-search"></i> Browse Tenders</a>
    </x-page-header>

    @unless ($stats)
        @if ($contractor && $contractor->status === 'pending')
            <div class="bc-card mb-4">
                <div class="bc-card-body">
                    <div class="bc-steps">
                        <div class="bc-step done"><span class="dotn"><i class="bi bi-check-lg"></i></span><div>Application Submitted</div></div>
                        <div class="bc-step now"><span class="dotn">2</span><div>Admin Review</div></div>
                        <div class="bc-step"><span class="dotn">3</span><div>Approved — Start Bidding</div></div>
                    </div>
                    <div class="bc-note-box info mt-3 mb-0">
                        <i class="bi bi-hourglass-split me-1"></i> Your application is <strong>pending approval</strong>. You can browse tenders once approved; bidding unlocks automatically. Submitted bids so far: <strong>{{ $pendingBids }}</strong>.
                    </div>
                </div>
            </div>
        @elseif ($contractor && $contractor->status === 'rejected')
            <div class="bc-card mb-4">
                <div class="bc-card-body">
                    <div class="bc-note-box danger mb-0">
                        <i class="bi bi-x-circle me-1"></i> Your application was rejected.
                        @if ($contractor->rejection_reason) Reason: "{{ $contractor->rejection_reason }}" @endif
                        Contact the administrator or update your documents, then re-apply from the public website.
                    </div>
                </div>
            </div>
        @else
            <div class="bc-card mb-4">
                <div class="bc-card-body">
                    <div class="bc-note-box mb-0">
                        <i class="bi bi-person-exclamation me-1"></i> No contractor profile is linked to this account. Please register as a contractor from the public website.
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <x-stat-card icon="bi-file-earmark-rupee" icon-class="ic-blue" :value="$stats['open_tenders']" label="Open Tenders" trend="Accepting bids" trend-class="trend-flat" />
            </div>
            <div class="col-6 col-lg-3">
                <x-stat-card icon="bi-cash-coin" icon-class="ic-navy" :value="$stats['my_bids']" label="My Bids" trend="All time" trend-class="trend-flat" />
            </div>
            <div class="col-6 col-lg-3">
                <x-stat-card icon="bi-trophy" icon-class="ic-green" :value="$stats['awarded']" label="Awards Won" trend="Contracts secured" trend-class="trend-up" />
            </div>
            <div class="col-6 col-lg-3">
                <x-stat-card icon="bi-bell" icon-class="ic-yellow" :value="$stats['unread_notifications']" label="Notifications" trend="Unread" trend-class="trend-flat" />
            </div>
        </div>

        @if ($pendingCancellations->isNotEmpty())
            <div class="bc-note-box mb-3">
                <i class="bi bi-exclamation-triangle me-1"></i> {{ $pendingCancellations->count() }} cancellation request(s) are pending a decision for tenders you are involved in —
                <a href="{{ route('contractor.cancellations.index', ['status' => 'pending']) }}" style="color:inherit;text-decoration:underline;">review them</a>.
            </div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="bc-card h-100">
                    <div class="bc-card-header">
                        <h3><i class="bi bi-file-earmark-rupee me-2" style="color:var(--bc-blue);"></i>Open Tenders</h3>
                        <a href="{{ route('contractor.tenders.index') }}" class="sub" style="color:var(--bc-blue);">View all →</a>
                    </div>
                    <div class="bc-card-body">
                        @forelse ($openTenders as $tender)
                            <div class="bc-tender-card" style="padding:14px 16px;">
                                <div class="bc-tender-top">
                                    <div>
                                        <a href="{{ route('contractor.tenders.show', $tender) }}" style="font-size:13.5px;font-weight:700;color:var(--bc-text);">{{ $tender->title }}</a>
                                        <div class="bc-tender-meta" style="margin:6px 0 0;">
                                            <span><i class="bi bi-geo-alt"></i>{{ Str::limit($tender->location, 28) }}</span>
                                            <span><i class="bi bi-diagram-3"></i>{{ $tender->type?->name }}</span>
                                        </div>
                                    </div>
                                    <div style="text-align:right;font-size:12.5px;">
                                        <div class="{{ $tender->deadline->diffInDays(now()) <= 3 ? 'bc-deadline-soon' : 'bc-deadline-ok' }}"><i class="bi bi-clock"></i> {{ $tender->deadline->format('d M') }}</div>
                                        <strong style="font-size:13px;">₹{{ number_format($tender->estimated_budget, 0) }}</strong>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted" style="font-size:13.5px;margin:0;">No open tenders right now — check back soon.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="bc-card h-100">
                    <div class="bc-card-header">
                        <h3><i class="bi bi-clock-history me-2" style="color:var(--bc-blue);"></i>Recent Bids</h3>
                        <a href="{{ route('contractor.bids.index') }}" class="sub" style="color:var(--bc-blue);">View all →</a>
                    </div>
                    <div class="bc-card-body">
                        @forelse ($recentBids as $bid)
                            <div class="bc-tender-card" style="padding:14px 16px;">
                                <div class="bc-tender-top">
                                    <div>
                                        <strong style="font-size:13.5px;">{{ $bid->tender?->title }}</strong>
                                        <div style="font-size:12.5px;color:#64748b;margin-top:4px;">
                                            ₹{{ number_format($bid->bid_amount, 2) }} · {{ $bid->submitted_at?->format('d M Y') }}
                                        </div>
                                    </div>
                                    <x-status-badge :status="$bid->status" />
                                </div>
                            </div>
                        @empty
                            <div class="bc-empty" style="padding:24px 12px;">
                                <i class="bi bi-cash-stack"></i>
                                <h4>No bids yet</h4>
                                <p>Browse open tenders and submit your first bid.</p>
                                <a href="{{ route('contractor.tenders.index') }}" class="btn-bc btn-bc-primary btn-bc-sm mt-2"><i class="bi bi-search"></i> Find Tenders</a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endunless
@endsection
