@extends('layouts.admin')

@section('title', 'My Bids')

@section('content')
    <x-page-header title="My Bids" subtitle="Track the status of every bid you have submitted.">
        <a href="{{ route('contractor.tenders.index') }}" class="btn-bc btn-bc-primary"><i class="bi bi-plus-lg"></i> Find Tenders</a>
    </x-page-header>

    @php
        $filters = ['' => 'All', 'submitted' => 'Submitted', 'under_review' => 'Under Review', 'awarded' => 'Awarded', 'rejected' => 'Rejected'];
    @endphp

    <div class="bc-filter-bar">
        @foreach ($filters as $val => $lbl)
            <a href="{{ route('contractor.bids.index', array_filter(['status' => $val, 'search' => $search])) }}"
               @class(['btn-bc btn-bc-sm' => true, 'btn-bc-primary' => $status === $val, 'btn-bc-outline' => $status !== $val])>
                {{ $lbl }} <span class="badge" style="background:rgba(255,255,255,.25);">{{ $val === '' ? $statusCounts->sum() : ($statusCounts[$val] ?? 0) }}</span>
            </a>
        @endforeach
        <form method="GET" action="{{ route('contractor.bids.index') }}" class="d-flex gap-2 ms-auto flex-wrap">
            @if ($status) <input type="hidden" name="status" value="{{ $status }}"> @endif
            <div class="bc-search-box">
                <i class="bi bi-search"></i>
                <input type="text" name="search" class="bc-input" placeholder="Search tender title or location..." value="{{ $search }}">
            </div>
            <button type="submit" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-funnel"></i> Search</button>
        </form>
    </div>

    @if ($bids->isNotEmpty())
        <div class="bc-card mb-3">
            <div class="bc-card-body flush">
                <div class="bc-table-wrap">
                    <table class="bc-table">
                        <thead>
                            <tr>
                                <th>Tender</th>
                                <th>Bid Amount</th>
                                <th>Submitted</th>
                                <th>Tender Deadline</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bids as $bid)
                                <tr>
                                    <td>
                                        <strong style="font-size:13.5px;">{{ $bid->tender?->title }}</strong>
                                        <div style="font-size:12px;color:#64748b;">{{ $bid->tender?->type?->name }} · {{ \Illuminate\Support\Str::limit($bid->tender?->location, 30) }}</div>
                                    </td>
                                    <td><strong>₹{{ number_format($bid->bid_amount, 2) }}</strong></td>
                                    <td style="font-size:13px;">{{ $bid->submitted_at?->format('d M Y, H:i') }}</td>
                                    <td style="font-size:13px;" class="{{ $bid->tender?->isExpired() ? 'bc-deadline-over' : '' }}">
                                        {{ $bid->tender?->deadline?->format('d M Y, H:i') }}
                                    </td>
                                    <td><x-status-badge :status="$bid->status" /></td>
                                    <td class="text-end">
                                        <a href="{{ route('contractor.tenders.show', $bid->tender_id) }}" class="btn-bc btn-bc-outline btn-bc-sm">
                                            <i class="bi bi-eye"></i> View Tender
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <x-pagination :paginator="$bids" />
    @else
        <div class="bc-card">
            <x-empty-state icon="bi-cash-stack" title="No bids found"
                           text="You have not submitted any bids matching these filters yet."
                           actionLabel="Browse Tenders" actionUrl="{{ route('contractor.tenders.index') }}" />
        </div>
    @endif
@endsection
