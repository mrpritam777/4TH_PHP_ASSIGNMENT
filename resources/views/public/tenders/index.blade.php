@extends('layouts.public')

@section('title', 'Public Tenders')

@section('content')
    <x-page-header title="Open Tenders" subtitle="Browse currently published infrastructure tenders — open to all approved contractors.">
        <a href="{{ route('contractor.register') }}" class="btn-bc btn-bc-primary"><i class="bi bi-person-plus"></i> Become a Contractor</a>
    </x-page-header>

    <form class="bc-filter-bar" method="GET" action="{{ route('public.tenders.index') }}">
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
        <button type="submit" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-funnel"></i> Filter</button>
        @if ($search || $type)
            <a href="{{ route('public.tenders.index') }}" class="btn-bc btn-bc-ghost btn-bc-sm"><i class="bi bi-x-circle"></i> Clear</a>
        @endif
    </form>

    @if ($tenders->isNotEmpty())
        <div class="row g-3 mb-2">
            @foreach ($tenders as $tender)
                <div class="col-lg-6">
                    <div class="bc-tender-card h-100">
                        <div class="bc-tender-top">
                            <div>
                                <h3><a href="{{ route('public.tenders.show', $tender) }}">{{ $tender->title }}</a></h3>
                                <div class="bc-tender-meta">
                                    <span><i class="bi bi-geo-alt"></i>{{ $tender->location }}</span>
                                    <span><i class="bi bi-diagram-3"></i>{{ $tender->type?->name ?? '—' }}</span>
                                </div>
                            </div>
                            <span class="bc-badge {{ $tender->isExpired() ? 'badge-orange' : 'badge-blue' }}"><span class="dot"></span>{{ $tender->isExpired() ? 'Closed' : 'Open' }}</span>
                        </div>
                        <p style="font-size:13px;color:#64748b;margin:6px 0 0;line-height:1.6;">{{ Str::limit($tender->description, 130) }}</p>
                        <div class="bc-tender-foot">
                            <div style="font-size:12.5px;">
                                <div class="{{ $tender->isExpired() ? 'bc-deadline-over' : ($tender->deadline->diffInDays(now()) <= 3 ? 'bc-deadline-soon' : 'bc-deadline-ok') }}">
                                    <i class="bi bi-clock"></i> {{ $tender->deadline->isFuture() ? 'Closes '.$tender->deadline->format('d M Y, H:i') : 'Deadline passed' }}
                                </div>
                                <div style="color:#64748b;margin-top:4px;"><i class="bi bi-wallet2"></i> Budget ₹{{ number_format($tender->estimated_budget, 0) }}</div>
                            </div>
                            <a href="{{ route('public.tenders.show', $tender) }}" class="btn-bc btn-bc-outline btn-bc-sm">View Details <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <x-pagination :paginator="$tenders" />
    @else
        <div class="bc-card">
            <x-empty-state icon="bi-file-earmark-x" title="No tenders published"
                           text="No published tenders match your search. Check back later — new tenders are posted regularly."
                           actionLabel="Register as a Contractor" actionUrl="{{ route('contractor.register') }}" />
        </div>
    @endif

    <div class="bc-cta-band mt-4">
        <div class="container">
            <h3 style="color:var(--bc-blue)">Ready to bid on these tenders?</h3>
            <p>Register your company, get approved by our team, and start submitting competitive bids.</p>
            <div class="d-flex gap-2 justify-content-center flex-wrap">
                <a href="{{ route('contractor.register') }}" class="btn-bc btn-bc-primary btn-bc-lg"><i class="bi bi-person-plus"></i> Register as a Contractor</a>
                <a href="{{ route('login') }}" class="btn-bc btn-bc-outline btn-bc-lg"><i class="bi bi-box-arrow-in-right"></i> Contractor Login</a>
            </div>
        </div>
    </div>
@endsection
