@extends('layouts.admin')

@section('title', 'Contractor Management')

@section('content')
    <x-page-header title="Contractor Management" subtitle="Review applications, approve contractors and manage their accounts.">
        <span class="btn-bc btn-bc-outline"><i class="bi bi-hourglass-split"></i> {{ $statusCounts['pending'] ?? 0 }} pending</span>
    </x-page-header>

    <div class="d-flex gap-2 flex-wrap mb-3">
        <a href="{{ route('admin.contractors.index') }}" class="btn-bc btn-bc-outline {{ ! $status ? 'btn-bc-primary' : '' }}">All</a>
        @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $key => $label)
            <a href="{{ route('admin.contractors.index', array_merge(request()->except('page'), ['status' => $key])) }}"
               class="btn-bc btn-bc-outline {{ $status === $key ? 'btn-bc-primary' : '' }}">{{ $label }} ({{ $statusCounts[$key] ?? 0 }})</a>
        @endforeach
    </div>

    <form class="bc-filter-bar" method="GET" action="{{ route('admin.contractors.index') }}">
        @if ($status) <input type="hidden" name="status" value="{{ $status }}"> @endif
        <div class="bc-search-box">
            <i class="bi bi-search"></i>
            <input type="text" name="search" class="bc-input" placeholder="Search by name, company, email or phone..." value="{{ $search }}">
        </div>
        <button type="submit" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-search"></i> Search</button>
        @if ($search || $status)
            <a href="{{ route('admin.contractors.index') }}" class="btn-bc btn-bc-ghost btn-bc-sm"><i class="bi bi-x-circle"></i> Clear</a>
        @endif
    </form>

    <div class="bc-card">
        <div class="bc-card-body flush">
            <div class="bc-table-wrap">
                <table class="bc-table">
                    <thead>
                        <tr>
                            <th>Contractor</th>
                            <th>Company</th>
                            <th>Categories</th>
                            <th>Applied</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contractors as $row)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="bc-avatar-fallback">{{ strtoupper(substr($row->user?->name ?? '?', 0, 1)) }}</span>
                                        <div>
                                            <div class="row-title">{{ $row->user?->name ?? '—' }}</div>
                                            <div class="row-sub">{{ $row->user?->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="font-size:12.5px;font-weight:600;">{{ $row->company_name }}</td>
                                <td style="max-width:190px;font-size:12px;">
                                    {{ \App\Models\InfrastructureType::whereIn('id', (array) ($row->work_categories ?? []))->pluck('name')->join(', ') ?: '—' }}
                                </td>
                                <td style="white-space:nowrap;font-size:12.5px;color:#64748b;">{{ $row->created_at->format('d M Y') }}</td>
                                <td>
                                    <x-status-badge :status="$row->status" />
                                    @if ($row->status === 'approved' && ! $row->user?->isActive())
                                        <div><span class="bc-badge badge-red mt-1"><span class="dot"></span>Deactivated</span></div>
                                    @endif
                                </td>
                                <td>
                                    <div class="bc-actions">
                                        <a href="{{ route('admin.contractors.show', $row) }}" class="bc-action-btn view" title="Review"><i class="bi bi-eye"></i></a>
                                        @if ($row->status === 'pending')
                                            <form action="{{ route('admin.contractors.approve', $row) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="bc-action-btn view" title="Approve" data-bc-confirm="Approve the application from {{ $row->company_name }}? The account will be activated." data-bc-confirm-title="Approve application?" data-bc-confirm-text="Approve" data-bc-confirm-danger="no"><i class="bi bi-check-circle"></i></button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-empty-state icon="bi-person-workspace" title="No contractor applications"
                                                   text="Contractor applications submitted through the public registration page will appear here." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-pagination :paginator="$contractors" />
@endsection

