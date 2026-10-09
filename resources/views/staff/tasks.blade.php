@extends('layouts.admin')

@section('title', 'My Tasks')

@section('content')
    <x-page-header title="My Tasks" subtitle="Your assigned maintenance requests and progress tracking." />

    <div class="d-flex gap-2 flex-wrap mb-3">
        <a href="{{ route('staff.tasks.index') }}" class="btn-bc btn-bc-outline {{ request()->input('status') === null ? 'btn-bc-primary' : '' }}">All My Tasks</a>
        <a href="{{ route('staff.tasks.index', ['status' => 'in_progress']) }}" class="btn-bc btn-bc-outline {{ request()->input('status') === 'in_progress' ? 'btn-bc-primary' : '' }}">In Progress</a>
        <a href="{{ route('staff.tasks.index', ['status' => 'pending']) }}" class="btn-bc btn-bc-outline {{ request()->input('status') === 'pending' ? 'btn-bc-primary' : '' }}">Pending Assignment</a>
        <a href="{{ route('staff.tasks.index', ['status' => 'completed']) }}" class="btn-bc btn-bc-outline {{ request()->input('status') === 'completed' ? 'btn-bc-primary' : '' }}">Completed</a>
    </div>

    <form class="bc-filter-bar" method="GET" action="{{ route('staff.tasks.index') }}">
        <input type="hidden" name="status" value="{{ request()->input('status', '') }}">
        <div class="bc-search-box">
            <i class="bi bi-search"></i>
            <input type="text" name="search" class="bc-input" placeholder="Search by title or infrastructure..." value="{{ $search }}">
        </div>
        <button type="submit" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-search"></i> Search</button>
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
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tasks as $task)
                            <tr>
                                <td>
                                    <a href="{{ route('staff.tasks.show', $task) }}" class="row-title" style="color:var(--bc-blue);">
                                        {{ $task->request_number }}
                                    </a>
                                </td>
                                <td>
                                    <div class="row-title">{{ $task->infrastructure?->name }}</div>
                                    <div class="row-sub">{{ $task->infrastructure?->type->name }}</div>
                                </td>
                                <td style="max-width:220px;"><span style="font-size:12.5px;">{{ Str::limit($task->title, 44) }}</span></td>
                                <td><x-priority-badge :priority="$task->priority" /></td>
                                <td><x-status-badge :status="$task->status" /></td>
                                <td style="white-space:nowrap;font-size:12.5px;color:#64748b;">{{ $task->created_at->format('d M Y') }}</td>
                                <td>
                                    <div class="bc-actions">
                                        <a href="{{ route('staff.tasks.show', $task) }}" class="bc-action-btn view" title="View Details"><i class="bi bi-eye"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-empty-state icon="bi-list-check" title="No tasks assigned"
                                                   text="You have no maintenance tasks assigned yet. Contact your supervisor for work assignments." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-pagination :paginator="$tasks" />
@endsection
