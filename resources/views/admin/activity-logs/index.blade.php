@extends('layouts.admin')

@section('title', 'Activity Log')

@section('content')
    <x-page-header title="Activity Log" subtitle="Audit trail of all system actions and changes.">
    </x-page-header>

    <form class="bc-filter-bar" method="GET" action="{{ route('admin.activity-logs.index') }}">
        <div class="bc-search-box">
            <i class="bi bi-search"></i>
            <input type="text" name="module" class="bc-input" placeholder="Filter by module (e.g. Bridge, Maintenance)..." value="{{ $module }}">
        </div>
        <button type="submit" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-funnel"></i> Filter</button>
        @if ($module)
            <a href="{{ route('admin.activity-logs.index') }}" class="btn-bc btn-bc-ghost btn-bc-sm"><i class="bi bi-x-circle"></i> Clear</a>
        @endif
    </form>

    <div class="bc-card">
        <div class="bc-card-body flush">
            <div class="bc-table-wrap">
                <table class="bc-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Module</th>
                            <th>Record</th>
                            <th>Description</th>
                            <th>IP Address</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if ($log->user)
                                            @if ($log->user->profile_image)
                                                <img src="{{ asset('storage/' . $log->user->profile_image) }}" class="bc-avatar" alt="">
                                            @else
                                                <span class="bc-avatar-fallback">{{ strtoupper(substr($log->user->name, 0, 1)) }}</span>
                                            @endif
                                        @else
                                            <span class="bc-avatar-fallback">SYS</span>
                                        @endif
                                        <div>
                                            <div class="row-title">{{ $log->user?->name ?? 'System' }}</div>
                                            <div class="row-sub">{{ $log->user?->role ?? '' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="font-size:12.5px;">{{ $log->action }}</td>
                                <td style="font-size:12.5px;">{{ $log->module }}</td>
                                <td style="font-size:12.5px;">{{ $log->record_id ?? '—' }}</td>
                                <td style="max-width:280px;font-size:12.5px;">{{ Str::limit($log->description, 60) }}</td>
                                <td style="font-size:12px;white-space:nowrap;">{{ $log->ip_address ?? '—' }}</td>
                                <td style="white-space:nowrap;font-size:12.5px;">{{ $log->created_at->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><x-empty-state icon="bi-journal-text" title="No activity" text="No actions have been logged yet." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-pagination :paginator="$logs" />
@endsection
