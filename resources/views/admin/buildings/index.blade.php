@extends('layouts.admin')

@section('title', 'Buildings')

@section('content')
    <x-page-header title="Building Management" subtitle="Manage institutional and public buildings in the network.">
        <a href="{{ route('admin.buildings.create') }}" class="btn-bc btn-bc-primary"><i class="bi bi-plus-lg"></i> Add Building</a>
    </x-page-header>

    <form class="bc-filter-bar" method="GET" action="{{ route('admin.buildings.index') }}">
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
            <a href="{{ route('admin.buildings.index') }}" class="btn-bc btn-bc-ghost btn-bc-sm"><i class="bi bi-x-circle"></i> Clear</a>
        @endif
    </form>

    <div class="bc-card">
        <div class="bc-card-body flush">
            <div class="bc-table-wrap">
                <table class="bc-table">
                    <thead>
                        <tr>
                            <th>Building</th>
                            <th>Code</th>
                            <th>Type</th>
                            <th>Location</th>
                            <th>Floors</th>
                            <th>Area</th>
                            <th>Condition</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($buildings as $building)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ $building->imageUrl() }}" class="bc-thumb" alt="{{ $building->name }}">
                                        <div>
                                            <div class="row-title">{{ $building->name }}</div>
                                            <div class="row-sub">{{ $building->buildingDetails?->department ?? '—' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><code style="font-size:12px;background:#f1f5f9;padding:3px 8px;border-radius:6px;">{{ $building->code }}</code></td>
                                <td style="font-size:12.5px;">{{ $building->buildingDetails?->building_type ?? '—' }}</td>
                                <td style="max-width:170px;font-size:12.5px;">{{ Str::limit($building->location, 28) }}</td>
                                <td style="text-align:center;">{{ $building->buildingDetails?->floors ?? '—' }}</td>
                                <td style="white-space:nowrap;">{{ $building->buildingDetails?->area ? number_format($building->buildingDetails->area) . ' sq.ft' : '—' }}</td>
                                <td><x-condition-badge :condition="$building->condition" /></td>
                                <td>
                                    <div class="bc-actions">
                                        <a href="{{ route('admin.buildings.show', $building) }}" class="bc-action-btn view" title="View"><i class="bi bi-eye"></i></a>
                                        <a href="{{ route('admin.buildings.edit', $building) }}" class="bc-action-btn edit" title="Edit"><i class="bi bi-pencil"></i></a>
                                        <a href="{{ route('admin.maintenance.create', ['infrastructure_id' => $building->id]) }}" class="bc-action-btn maint" title="Maintenance"><i class="bi bi-wrench"></i></a>
                                        <form action="{{ route('admin.buildings.destroy', $building) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bc-action-btn delete" title="Delete" data-bc-delete="bcDelBuilding{{ $building->id }}"
                                                    data-message="Building &quot;{{ $building->name }}&quot; will be permanently deleted.">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <x-empty-state icon="bi-buildings" title="No buildings found"
                                                   text="Try adjusting your search, or add a new building."
                                                   action-label="Add Building" action-url="{{ route('admin.buildings.create') }}" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-pagination :paginator="$buildings" />
@endsection
