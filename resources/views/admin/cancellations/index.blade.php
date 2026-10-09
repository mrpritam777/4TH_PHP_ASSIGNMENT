@extends('layouts.admin')

@section('title', 'Cancellation Requests')

@section('content')
    <x-page-header title="Cancellation Requests" subtitle="Review tender cancellation requests and record authorised decisions.">
        <a href="{{ route('admin.cancellations.create') }}" class="btn-bc btn-bc-primary"><i class="bi bi-x-octagon"></i> Request Cancellation</a>
    </x-page-header>

    <div class="d-flex gap-2 flex-wrap mb-3">
        <a href="{{ route('admin.cancellations.index') }}" class="btn-bc btn-bc-outline {{ ! $status ? 'btn-bc-primary' : '' }}">All</a>
        @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'cancelled' => 'Withdrawn'] as $key => $label)
            <a href="{{ route('admin.cancellations.index', array_merge(request()->except('page'), ['status' => $key])) }}"
               class="btn-bc btn-bc-outline {{ $status === $key ? 'btn-bc-primary' : '' }}">{{ $label }} ({{ $statusCounts[$key] ?? 0 }})</a>
        @endforeach
    </div>

    <form class="bc-filter-bar" method="GET" action="{{ route('admin.cancellations.index') }}">
        @if ($status) <input type="hidden" name="status" value="{{ $status }}"> @endif
        <div class="bc-search-box">
            <i class="bi bi-search"></i>
            <input type="text" name="search" class="bc-input" placeholder="Search by tender title or location..." value="{{ $search }}">
        </div>
        <button type="submit" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-search"></i> Search</button>
        @if ($search || $status)
            <a href="{{ route('admin.cancellations.index') }}" class="btn-bc btn-bc-ghost btn-bc-sm"><i class="bi bi-x-circle"></i> Clear</a>
        @endif
    </form>

    <div class="bc-card">
        <div class="bc-card-body flush">
            <div class="bc-table-wrap">
                <table class="bc-table">
                    <thead>
                        <tr>
                            <th>Tender</th>
                            <th>Requested By</th>
                            <th>Reason</th>
                            <th>Requested</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cancellations as $req)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.tenders.show', $req->tender) }}" class="row-title" style="color:var(--bc-blue);">{{ $req->tender?->title ?? '—' }}</a>
                                    <div class="row-sub">{{ $req->tender?->type?->name }} · {{ Str::limit($req->tender?->location, 30) }}</div>
                                </td>
                                <td style="font-size:12.5px;">
                                    {{ $req->requester?->name ?? '—' }}
                                    <div class="row-sub">{{ ucfirst($req->requester_role) }}</div>
                                </td>
                                <td style="max-width:230px;font-size:12.5px;">{{ Str::limit($req->reason, 90) }}</td>
                                <td style="white-space:nowrap;font-size:12.5px;color:#64748b;">{{ $req->created_at->format('d M Y, H:i') }}</td>
                                <td><x-status-badge :status="$req->status" /></td>
                                <td>
                                    <a href="{{ route('admin.cancellations.show', $req) }}" class="bc-action-btn view" title="Review"><i class="bi bi-eye"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-empty-state icon="bi-x-octagon" title="No cancellation requests"
                                                   text="Cancellation requests from admins and contractors will appear here."
                                                   action-label="Request Cancellation" action-url="{{ route('admin.cancellations.create') }}" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-pagination :paginator="$cancellations" />
@endsection
