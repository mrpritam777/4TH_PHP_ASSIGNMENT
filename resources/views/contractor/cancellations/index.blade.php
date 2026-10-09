@extends('layouts.admin')

@section('title', 'Cancellation Requests')

@section('content')
    <x-page-header title="Cancellation Requests" subtitle="Cancellation requests for tenders you are involved in.">
        <a href="{{ route('contractor.cancellations.create') }}" class="btn-bc btn-bc-primary"><i class="bi bi-x-octagon"></i> Request Cancellation</a>
    </x-page-header>

    <div class="d-flex gap-2 flex-wrap mb-3">
        <a href="{{ route('contractor.cancellations.index') }}" class="btn-bc btn-bc-outline {{ ! $status ? 'btn-bc-primary' : '' }}">All</a>
        @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'cancelled' => 'Withdrawn'] as $key => $label)
            <a href="{{ route('contractor.cancellations.index', ['status' => $key]) }}"
               class="btn-bc btn-bc-outline {{ $status === $key ? 'btn-bc-primary' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

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
                                    <a href="{{ route('contractor.cancellations.show', $req) }}" class="row-title" style="color:var(--bc-blue);">{{ $req->tender?->title ?? '—' }}</a>
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
                                    <a href="{{ route('contractor.cancellations.show', $req) }}" class="bc-action-btn view" title="View"><i class="bi bi-eye"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-empty-state icon="bi-x-octagon" title="No cancellation requests"
                                                   text="Cancellation requests you raise — or admin requests affecting your tenders — will appear here."
                                                   action-label="Request Cancellation" action-url="{{ route('contractor.cancellations.create') }}" />
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
