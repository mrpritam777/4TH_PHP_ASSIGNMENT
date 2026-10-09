@extends('layouts.admin')

@section('title', 'User Management')

@section('content')
    <x-page-header title="User Management" subtitle="Manage system users and their roles.">
        <a href="{{ route('admin.users.create') }}" class="btn-bc btn-bc-primary"><i class="bi bi-person-plus"></i> Add User</a>
    </x-page-header>

    <div class="d-flex gap-2 flex-wrap mb-3">
        <a href="{{ route('admin.users.index') }}" class="btn-bc btn-bc-outline {{ request()->routeIs('admin.users.index') ? 'btn-bc-primary' : '' }}">All Users</a>
        <a href="{{ route('admin.users.admins') }}" class="btn-bc btn-bc-outline {{ request()->routeIs('admin.users.admins') ? 'btn-bc-primary' : '' }}">Admins</a>
        <a href="{{ route('admin.users.staff') }}" class="btn-bc btn-bc-outline {{ request()->routeIs('admin.users.staff') ? 'btn-bc-primary' : '' }}">Staff</a>
    </div>

    <form class="bc-filter-bar" method="GET" action="{{ request()->routeIs('admin.users.admins') || request()->routeIs('admin.users.staff') ? route('admin.users.index') : route('admin.users.index') }}">
        <input type="hidden" name="role" value="{{ request()->input('role', '') }}">
        <div class="bc-search-box">
            <i class="bi bi-search"></i>
            <input type="text" name="search" class="bc-input" placeholder="Search by name or email..." value="{{ $search }}">
        </div>
        <button type="submit" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-search"></i> Search</button>
        @if ($search)
            <a href="{{ url()->current() }}" class="btn-bc btn-bc-ghost btn-bc-sm"><i class="bi bi-x-circle"></i> Clear</a>
        @endif
    </form>

    <div class="bc-card">
        <div class="bc-card-body flush">
            <div class="bc-table-wrap">
                <table class="bc-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Assigned Tasks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        @if ($user->profile_image)
                                            <img src="{{ asset('storage/' . $user->profile_image) }}" class="bc-avatar" alt="">
                                        @else
                                            <span class="bc-avatar-fallback">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                        @endif
                                        <div>
                                            <div class="row-title">{{ $user->name }}</div>
                                            <div class="row-sub">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="font-size:12.5px;">{{ $user->email }}</td>
                                <td style="font-size:12.5px;white-space:nowrap;">
                                    <i class="bi bi-telephone text-muted me-1"></i>{{ $user->phone ?? '—' }}
                                </td>
                                <td>
                                    <span class="bc-badge {{ $user->role === 'admin' ? 'badge-navy' : 'badge-blue' }}">{{ ucfirst($user->role) }}</span>
                                </td>
                                <td>
                                    @if ($user->isActive())
                                        <span class="bc-badge badge-green"><span class="dot"></span> Active</span>
                                    @else
                                        <span class="bc-badge badge-red"><span class="dot"></span> Inactive</span>
                                    @endif
                                </td>
                                <td style="text-align:center;">{{ $user->assignedRequests()->count() }}</td>
                                <td>
                                    <div class="bc-actions">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="bc-action-btn edit" title="Edit"><i class="bi bi-pencil"></i></a>
                                        @if ($user->id !== auth()->id())
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="bc-action-btn delete" title="Delete" data-bc-delete="bcDelUser{{ $user->id }}"
                                                        data-message="User &quot;{{ $user->name }}&quot; will be permanently deleted.">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-empty-state icon="bi-people" title="No users found"
                                                   text="Add users to start managing the system." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-pagination :paginator="$users" />
@endsection
