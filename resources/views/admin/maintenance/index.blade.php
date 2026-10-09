@extends('layouts.admin')

@section('title', 'Maintenance Requests')

@section('content')
    <x-page-header title="Maintenance Requests" subtitle="Track every maintenance request across the infrastructure network.">
        <a href="{{ route('admin.maintenance.history') }}" class="btn-bc btn-bc-outline"><i class="bi bi-clock-history"></i> History</a>
        <a href="{{ route('admin.maintenance.create') }}" class="btn-bc btn-bc-primary"><i class="bi bi-plus-lg"></i> New Request</a>
    </x-page-header>

    {{-- Status tabs --}}
    <div class="d-flex gap-2 flex-wrap mb-3">
        @php
            $tabs = [
                '' => 'All',
                'pending' => 'Pending',
                'under_review' => 'Under Review',
                'assigned' => 'Assigned',
                'in_progress' => 'In Progress',
                'on_hold' => 'On Hold',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
            ];
        @endphp
        @foreach ($tabs as $tabStatus => $tabLabel)
            <a href="{{ route('admin.maintenance.index', array_filter(['status' => $tabStatus ?: null, 'search' => $search, 'priority' => $priority])) }}"
               class="btn-bc btn-bc-sm {{ $status === ($tabStatus ?: null) ? 'btn-bc-primary' : 'btn-bc-outline' }}">
                {{ $tabLabel }}
                @if ($tabStatus === '' && isset($counts['all']))
                    <span style="opacity:.75;">({{ $counts['all'] }})</span>
                @endif
            </a>
        @endforeach
    </div>

    <form class="bc-filter-bar" method="GET" action="{{ route('admin.maintenance.index') }}">
        <input type="hidden" name="status" value="{{ $status }}">
        <div class="bc-search-box">
            <i class="bi bi-search"></i>
            <input type="text" name="search" class="bc-input" placeholder="Search by title, request number or infrastructure..." value="{{ $search }}">
        </div>
        <select name="priority" class="bc-select">
            <option value="">All priorities</option>
            @foreach (['low', 'medium', 'high', 'critical'] as $p)
                <option value="{{ $p }}" @selected($priority === $p)>{{ ucfirst($p) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-funnel"></i> Filter</button>
    </form>

    <div class="bc-card">
        <div class="bc-card-body flush">
            <div class="bc-table-wrap">
                <table class="bc-table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Infrastructure</th>
                            <th>Issue</th>
                            <th>Priority</th>
                            <th>Assigned Staff</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($requests as $request)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.maintenance.show', $request) }}" class="row-title" style="color:var(--bc-blue);">
                                        {{ $request->request_number }}
                                    </a>
                                </td>
                                <td>
                                    <div class="row-title">{{ $request->infrastructure?->name }}</div>
                                    <div class="row-sub">{{ $request->infrastructure?->type?->name }}</div>
                                </td>
                                <td style="max-width:220px;"><span style="font-size:12.5px;">{{ Str::limit($request->title, 44) }}</span></td>
                                <td><x-priority-badge :priority="$request->priority" /></td>
                                <td>
                                    @if ($request->assignedStaff)
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="bc-avatar-fallback">{{ strtoupper(substr($request->assignedStaff->name, 0, 1)) }}</span>
                                            <span style="font-size:12.5px;">{{ $request->assignedStaff->name }}</span>
                                        </div>
                                    @else
                                        <span class="bc-badge badge-gray">Unassigned</span>
                                    @endif
                                </td>
                                <td><x-status-badge :status="$request->status" /></td>
                                <td style="white-space:nowrap;font-size:12.5px;color:#64748b;">{{ $request->created_at->format('d M Y') }}</td>
                                <td>
                                    <div class="bc-actions">
                                        <a href="{{ route('admin.maintenance.show', $request) }}" class="bc-action-btn view" title="View"><i class="bi bi-eye"></i></a>
                                        <a href="{{ route('admin.maintenance.edit', $request) }}" class="bc-action-btn edit" title="Edit"><i class="bi bi-pencil"></i></a>
                                        <form action="{{ route('admin.maintenance.destroy', $request) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bc-action-btn delete" title="Delete" data-bc-delete="bcDelMaint{{ $request->id }}"
                                                    data-message="Request &quot;{{ $request->request_number }}&quot; and its updates will be permanently deleted.">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <x-empty-state icon="bi-wrench" title="No maintenance requests"
                                                   text="No requests match the current filters."
                                                   action-label="Create request" action-url="{{ route('admin.maintenance.create') }}" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-pagination :paginator="$requests" />
@endsection
