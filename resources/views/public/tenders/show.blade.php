@extends('layouts.public')

@section('title', $tender->title)

@section('content')
    <x-page-header :title="$tender->title" :breadcrumbs="[[ 'label' => 'Tenders', 'url' => route('public.tenders.index') ]]">
        <span class="bc-badge {{ $tender->isExpired() ? 'badge-orange' : 'badge-blue' }}"><span class="dot"></span>{{ $tender->isExpired() ? 'Closed' : 'Open for Bids' }}</span>
        @auth
            @if (auth()->user()->isContractor())
                <a href="{{ route('contractor.tenders.show', $tender) }}" class="btn-bc btn-bc-primary"><i class="bi bi-cash-coin"></i> View &amp; Bid</a>
            @endif
        @else
            <a href="{{ route('login') }}" class="btn-bc btn-bc-primary"><i class="bi bi-box-arrow-in-right"></i> Login to Bid</a>
        @endauth
    </x-page-header>

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
                            <span class="label">Published On</span>
                            <span class="value">{{ $tender->published_at?->format('d M Y, H:i') ?? '—' }}</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="label">Bids Received</span>
                            <span class="value">{{ $bidCount }}</span>
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
                    <h3><i class="bi bi-hourglass-split me-2" style="color:var(--bc-blue);"></i>Key Dates</h3>
                </div>
                <div class="bc-card-body">
                    <div class="bc-detail-list">
                        <div class="row-item"><span class="k">Published</span><span class="v">{{ $tender->published_at?->format('d M Y') ?? '—' }}</span></div>
                        <div class="row-item"><span class="k">Deadline</span><span class="v {{ $tender->isExpired() ? 'bc-deadline-over' : '' }}">{{ $tender->deadline->format('d M Y, H:i') }}</span></div>
                        <div class="row-item"><span class="k">Time left</span><span class="v">{{ $tender->deadline->isFuture() ? $tender->deadline->diffForHumans() : 'Closed' }}</span></div>
                    </div>
                </div>
            </div>
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
                        <p class="text-muted" style="font-size:13px;margin:0;">No supporting documents are required.</p>
                    @endif
                </div>
            </div>

            <div class="bc-card">
                <div class="bc-card-header">
                    <h3><i class="bi bi-megaphone me-2" style="color:var(--bc-blue);"></i>Participate</h3>
                </div>
                <div class="bc-card-body">
                    @auth
                        @if (auth()->user()->isContractor())
                            <p style="font-size:13px;color:#475569;line-height:1.65;">You are signed in as a contractor. Open the tender to review the scope and submit your bid before the deadline.</p>
                            <a href="{{ route('contractor.tenders.show', $tender) }}" class="btn-bc btn-bc-primary w-100"><i class="bi bi-cash-coin"></i> View &amp; Bid</a>
                        @else
                            <p style="font-size:13px;color:#475569;line-height:1.65;">Sign in with your contractor account to submit a bid for this tender.</p>
                            <a href="{{ route('login') }}" class="btn-bc btn-bc-primary w-100"><i class="bi bi-box-arrow-in-right"></i> Contractor Login</a>
                        @endif
                    @else
                        <p style="font-size:13px;color:#475569;line-height:1.65;">Approved contractors can bid on this tender. Register your company to get started — approval usually takes 1–2 working days.</p>
                        <a href="{{ route('contractor.register') }}" class="btn-bc btn-bc-primary w-100"><i class="bi bi-person-plus"></i> Register as a Contractor</a>
                        <a href="{{ route('login') }}" class="btn-bc btn-bc-outline w-100 mt-2"><i class="bi bi-box-arrow-in-right"></i> Already Registered? Login</a>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <div class="bc-note-box info">
        <i class="bi bi-info-circle me-1"></i> Bids are accepted only from <strong>approved contractors</strong>, before the deadline, and only while the tender is open and not subject to a pending cancellation request.
    </div>
@endsection
