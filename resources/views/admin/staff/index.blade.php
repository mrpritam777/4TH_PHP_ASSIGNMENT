@extends('layouts.admin')

@section('title', 'Maintenance Staff')

@section('content')
    <x-page-header title="Maintenance Staff" subtitle="Manage the field team — add staff, activate accounts and assign work.">
        <a href="{{ route('admin.staff.create') }}" class="btn-bc btn-bc-primary"><i class="bi bi-person-plus"></i> Add Staff</a>
    </x-page-header>

    <form class="bc-filter-bar" method="GET" action="{{ route('admin.staff.index') }}">
        <div class="bc-search-box">
            <i class="bi bi-search"></i>
            <input type="text" name="search" class="bc-input" placeholder="Search by name, email or phone..." value="{{ $search }}">
        </div>
        <button type="submit" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-search"></i> Search</button>
        @if ($search)
            <a href="{{ route('admin.staff.index') }}" class="btn-bc btn-bc-ghost btn-bc-sm"><i class="bi bi-x-circle"></i> Clear</a>
        @endif
    </form>

    <div class="bc-card">
        <div class="bc-card-body flush">
            <div class="bc-table-wrap">
                <table class="bc-table">
                    <thead>
                        <tr>
                            <th>Staff Member</th>
                            <th>Contact</th>
                            <th>Active Tasks</th>
                            <th>Total Tasks</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($staff as $member)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        @if ($member->profile_image)
                                            <img src="{{ asset('storage/' . $member->profile_image) }}" class="bc-avatar" alt="">
                                        @else
                                            <span class="bc-avatar-fallback">{{ strtoupper(substr($member->name, 0, 1)) }}</span>
                                        @endif
                                        <div>
                                            <div class="row-title">{{ $member->name }}</div>
                                            <div class="row-sub">{{ $member->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="font-size:12.5px;">
                                    <i class="bi bi-telephone text-muted me-1"></i>{{ $member->phone ?? '—' }}
                                </td>
                                <td>
                                    <span class="bc-badge badge-blue">{{ $member->active_tasks_count }} active</span>
                                </td>
                                <td style="text-align:center;font-weight:600;">{{ $member->assigned_requests_count }}</td>
                                <td>
                                    @if ($member->isActive())
                                        <span class="bc-badge badge-green"><span class="dot"></span> Active</span>
                                    @else
                                        <span class="bc-badge badge-gray"><span class="dot"></span> Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="bc-actions">
                                        <a href="{{ route('admin.staff.edit', $member) }}" class="bc-action-btn edit" title="Edit"><i class="bi bi-pencil"></i></a>
                                        <form action="{{ route('admin.staff.toggle-status', $member) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="bc-action-btn {{ $member->isActive() ? 'delete' : 'view' }}"
                                                    title="{{ $member->isActive() ? 'Deactivate' : 'Activate' }}">
                                                <i class="bi {{ $member->isActive() ? 'bi-power' : 'bi-check-circle' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.staff.destroy', $member) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bc-action-btn delete" title="Delete" data-bc-delete="bcDelStaff{{ $member->id }}"
                                                    data-message="Staff account &quot;{{ $member->name }}&quot; will be permanently deleted.">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-empty-state icon="bi-people" title="No staff found"
                                                   text="Add maintenance staff to start assigning tasks."
                                                   action-label="Add Staff" action-url="{{ route('admin.staff.create') }}" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-pagination :paginator="$staff" />
@endsection
