@extends('layouts.admin')

@section('title', 'Roads')

@section('content')
    <x-page-header title="Road Management" subtitle="Manage the road network — add, inspect and maintain all roads.">
        <a href="{{ route('admin.roads.create') }}" class="btn-bc btn-bc-primary"><i class="bi bi-plus-lg"></i> Add Road</a>
    </x-page-header>

    <form class="bc-filter-bar" method="GET" action="{{ route('admin.roads.index') }}">
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
            <a href="{{ route('admin.roads.index') }}" class="btn-bc btn-bc-ghost btn-bc-sm"><i class="bi bi-x-circle"></i> Clear</a>
        @endif
    </form>

    <div class="bc-card">
        <div class="bc-card-body flush">
            <div class="bc-table-wrap">
                <table class="bc-table">
                    <thead>
                        <tr>
                            <th>Road</th>
                            <th>Code</th>
                            <th>Type</th>
                            <th>Route</th>
                            <th>Length</th>
                            <th>Lanes</th>
                            <th>Condition</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roads as $road)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ $road->imageUrl() }}" class="bc-thumb" alt="{{ $road->name }}">
                                        <div>
                                            <div class="row-title">{{ $road->name }}</div>
                                            <div class="row-sub">{{ $road->code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><code style="font-size:12px;background:#f1f5f9;padding:3px 8px;border-radius:6px;">{{ $road->code }}</code></td>
                                <td style="font-size:12.5px;">{{ $road->roadDetails?->road_type ?? '—' }}</td>
                                <td style="max-width:180px;font-size:12.5px;">
                                    {{ $road->roadDetails?->start_point ?? '—' }}
                                    <i class="bi bi-arrow-right" style="font-size:9px;"></i>
                                    {{ $road->roadDetails?->end_point ?? '—' }}
                                </td>
                                <td style="white-space:nowrap;">{{ $road->roadDetails?->length ? number_format($road->roadDetails->length) . ' km' : '—' }}</td>
                                <td style="text-align:center;">{{ $road->roadDetails?->lanes ?? '—' }}</td>
                                <td><x-condition-badge :condition="$road->condition" /></td>
                                <td>
                                    <div class="bc-actions">
                                        <a href="{{ route('admin.roads.show', $road) }}" class="bc-action-btn view" title="View"><i class="bi bi-eye"></i></a>
                                        <a href="{{ route('admin.roads.edit', $road) }}" class="bc-action-btn edit" title="Edit"><i class="bi bi-pencil"></i></a>
                                        <a href="{{ route('admin.maintenance.create', ['infrastructure_id' => $road->id]) }}" class="bc-action-btn maint" title="Maintenance"><i class="bi bi-wrench"></i></a>
                                        <form action="{{ route('admin.roads.destroy', $road) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bc-action-btn delete" title="Delete" data-bc-delete="bcDelRoad{{ $road->id }}"
                                                    data-message="Road &quot;{{ $road->name }}&quot; will be permanently deleted.">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <x-empty-state icon="bi-signpost" title="No roads found"
                                                   text="Try adjusting your search, or add a new road to the network."
                                                   action-label="Add Road" action-url="{{ route('admin.roads.create') }}" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-pagination :paginator="$roads" />
@endsection
