@extends('layouts.admin')

@section('title', 'Available Tenders')

@section('content')
    <x-page-header title="Available Tenders" subtitle="Open tenders you can bid on — closing soonest first.">
        <a href="{{ route('contractor.bids.index') }}" class="btn-bc btn-bc-outline"><i class="bi bi-cash-coin"></i> My Bids</a>
    </x-page-header>

    <form class="bc-filter-bar" method="GET" action="{{ route('contractor.tenders.index') }}">
        <div class="bc-search-box">
            <i class="bi bi-search"></i>
            <input type="text" name="search" class="bc-input" placeholder="Search by title or location..." value="{{ $search }}">
        </div>
        <select name="type" class="bc-select">
            <option value="">All Categories</option>
            @foreach ($types as $t)
                <option value="{{ $t->id }}" @selected((string) $type === (string) $t->id)>{{ $t->name }}</option>
            @endforeach
        </select>
        <label class="d-flex align-items-center gap-2" style="font-size:13px;color:#475569;white-space:nowrap;">
            <input type="checkbox" name="all" value="1" @checked($showAll)> Include closed/awarded
        </label>
        <button type="submit" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-funnel"></i> Filter</button>
        @if ($search || $type || $showAll)
            <a href="{{ route('contractor.tenders.index') }}" class="btn-bc btn-bc-ghost btn-bc-sm"><i class="bi bi-x-circle"></i> Clear</a>
        @endif
    </form>

    @if ($tenders->isNotEmpty())
        <div class="row g-3 mb-2">
            @foreach ($tenders as $tender)
                <div class="col-lg-6">
                    <div class="bc-tender-card h-100">
                        <div class="bc-tender-top">
                            <div>
                                <h3><a href="{{ route('contractor.tenders.show', $tender) }}">{{ $tender->title }}</a></h3>
                                <div class="bc-tender-meta">
                                    <span><i class="bi bi-geo-alt"></i>{{ $tender->location }}</span>
                                    <span><i class="bi bi-diagram-3"></i>{{ $tender->type?->name ?? '—' }}</span>
                                </div>
                            </div>
                            <x-status-badge :status="$tender->statusLabel()" />
                        </div>
                        <p style="font-size:13px;color:#64748b;margin:6px 0 0;line-height:1.6;">{{ Str::limit($tender->description, 130) }}</p>
                        <div class="bc-tender-foot">
                            <div style="font-size:12.5px;">
                                <div class="{{ $tender->isExpired() ? 'bc-deadline-over' : ($tender->deadline->diffInDays(now()) <= 3 ? 'bc-deadline-soon' : 'bc-deadline-ok') }}">
                                    <i class="bi bi-clock"></i> {{ $tender->deadline->isFuture() ? 'Closes '.$tender->deadline->format('d M Y, H:i') : 'Deadline passed' }}
                                </div>
                                <div style="color:#64748b;margin-top:4px;"><i class="bi bi-wallet2"></i> Budget ₹{{ number_format($tender->estimated_budget, 0) }}</div>
                            </div>
                            <div class="d-flex gap-2 align-items-center">
                                @php $bidStatus = $bidsByTender->get($tender->id); @endphp
                                @if ($bidStatus)
                                    <span class="bc-badge badge-green"><i class="bi bi-check2-circle me-1"></i>Bid Submitted</span>
                                @endif
                                <a href="{{ route('contractor.tenders.show', $tender) }}" class="btn-bc btn-bc-outline btn-bc-sm">
                                    {{ $bidStatus ? 'View' : ($tender->acceptsBids() ? 'Bid Now' : 'Details') }} <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <x-pagination :paginator="$tenders" />
    @else
        <div class="bc-card">
            <x-empty-state icon="bi-file-earmark-x" title="No tenders found"
                           text="No tenders match your search and filters. Clear the filters or check back later — new tenders are published regularly." />
        </div>
    @endif
@endsection
