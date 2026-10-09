@extends('layouts.admin')

@section('title', 'Bridges')

@section('content')
    <x-page-header title="Bridge Management" subtitle="Manage the complete bridge network — add, inspect, maintain and track every bridge.">
        <a href="{{ route('admin.bridges.create') }}" class="btn-bc btn-bc-primary"><i class="bi bi-plus-lg"></i> Add Bridge</a>
    </x-page-header>

    {{-- Filter bar --}}
    <form class="bc-filter-bar" method="GET" action="{{ route('admin.bridges.index') }}">
        <div class="bc-search-box">
            <i class="bi bi-search"></i>
            <input type="text" name="search" class="bc-input" placeholder="Search by name, code or location..." value="{{ $search }}">
        </div>
        <select name="condition" class="bc-select">
            <option value="">All conditions</option>
            @foreach (['excellent', 'good', 'fair', 'poor', 'critical'] as $c)
                <option value="{{ $c }}" @selected($condition === $c)>{{ ucfirst($c) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-funnel"></i> Filter</button>
        @if ($search || $condition)
            <a href="{{ route('admin.bridges.index') }}" class="btn-bc btn-bc-ghost btn-bc-sm"><i class="bi bi-x-circle"></i> Clear</a>
        @endif
    </form>

    <div class="bc-card">
        <div class="bc-card-body flush">
            <div class="bc-table-wrap">
                <table class="bc-table">
                    <thead>
                        <tr>
                            <th>Bridge</th>
                            <th>Code</th>
                            <th>Location</th>
                            <th>Length</th>
                            <th>Built</th>
                            <th>Condition</th>
                            <th>Last Maintenance</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bridges as $bridge)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ $bridge->imageUrl() }}" class="bc-thumb" alt="{{ $bridge->name }}">
                                        <div>
                                            <div class="row-title">{{ $bridge->name }}</div>
                                            <div class="row-sub">{{ $bridge->bridgeDetails?->bridge_type }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><code style="font-size:12px;background:#f1f5f9;padding:3px 8px;border-radius:6px;">{{ $bridge->code }}</code></td>
                                <td style="max-width:180px;"><span style="font-size:12.5px;">{{ Str::limit($bridge->location, 30) }}</span></td>
                                <td style="white-space:nowrap;">{{ $bridge->bridgeDetails?->length ? number_format($bridge->bridgeDetails->length) . ' m' : '—' }}</td>
                                <td style="white-space:nowrap;">{{ $bridge->construction_year ?? '—' }}</td>
                                <td><x-condition-badge :condition="$bridge->condition" /></td>
                                <td style="white-space:nowrap;font-size:12.5px;color:#64748b;">
                                    @php $last = $bridge->maintenanceRequests->where('status', 'completed')->sortByDesc(fn ($r) => $r->completion_date ?? $r->created_at)->first(); @endphp
                                    {{ $last ? ($last->completion_date ?? $last->created_at)->format('d M Y') : 'Never' }}
                                </td>
                                <td>
                                    @if ($bridge->maintenanceRequests->whereIn('status', ['pending', 'under_review', 'assigned', 'in_progress'])->count())
                                        <span class="bc-badge badge-orange"><span class="dot"></span> Under Maintenance</span>
                                    @elseif ($bridge->condition === 'critical')
                                        <span class="bc-badge badge-red"><span class="dot"></span> Attention</span>
                                    @else
                                        <span class="bc-badge badge-green"><span class="dot"></span> Operational</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="bc-actions">
                                        <a href="{{ route('admin.bridges.show', $bridge) }}" class="bc-action-btn view" title="View"><i class="bi bi-eye"></i></a>
                                        <a href="{{ route('admin.bridges.edit', $bridge) }}" class="bc-action-btn edit" title="Edit"><i class="bi bi-pencil"></i></a>
                                        <a href="{{ route('admin.maintenance.create', ['infrastructure_id' => $bridge->id]) }}" class="bc-action-btn maint" title="Maintenance"><i class="bi bi-wrench"></i></a>
                                        <a href="{{ route('admin.bridges.history', $bridge) }}" class="bc-action-btn" title="History" style="color:#7c3aed;border-color:#c4b5fd;background:#ede9fe;"><i class="bi bi-clock-history"></i></a>
                                        <form action="{{ route('admin.bridges.destroy', $bridge) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bc-action-btn delete" title="Delete" data-bc-delete="bcDelBridge{{ $bridge->id }}"
                                                    data-message="Bridge &quot;{{ $bridge->name }}&quot; and its maintenance records will be permanently deleted.">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <x-empty-state icon="bi-signpost-split" title="No bridges found"
                                                   text="Try adjusting your search or filters, or add a new bridge to get started."
                                                   action-label="Add Bridge" action-url="{{ route('admin.bridges.create') }}" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-pagination :paginator="$bridges" />
@endsection
