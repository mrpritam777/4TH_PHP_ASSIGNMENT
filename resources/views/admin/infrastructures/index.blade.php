@extends('layouts.admin')

@section('title', 'All Infrastructure')

@section('content')
    <x-page-header title="All Infrastructure" subtitle="Unified view of every asset in the network — bridges, roads, buildings and hospitals.">
    </x-page-header>

    <form class="bc-filter-bar" method="GET" action="{{ route('admin.infrastructures.index') }}">
        <div class="bc-search-box">
            <i class="bi bi-search"></i>
            <input type="text" name="search" class="bc-input" placeholder="Search by name, code or location..." value="{{ $search }}">
        </div>
        <select name="type" class="bc-select">
            <option value="">All types</option>
            @foreach (['bridge' => 'Bridges', 'road' => 'Roads', 'building' => 'Buildings', 'hospital' => 'Hospitals'] as $slug => $label)
                <option value="{{ $slug }}" @selected($type === $slug)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="condition" class="bc-select">
            <option value="">All conditions</option>
            @foreach (['excellent', 'good', 'fair', 'poor', 'critical'] as $c)
                <option value="{{ $c }}" @selected($condition === $c)>{{ ucfirst($c) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-funnel"></i> Filter</button>
        @if ($search || $type || $condition)
            <a href="{{ route('admin.infrastructures.index') }}" class="btn-bc btn-bc-ghost btn-bc-sm"><i class="bi bi-x-circle"></i> Clear</a>
        @endif
    </form>

    {{-- Type summary chips --}}
    @php
        $typeMeta = [
            'bridge' => ['label' => 'Bridges', 'icon' => 'bi-signpost-split', 'route' => 'admin.bridges.index', 'class' => 'ic-blue'],
            'road' => ['label' => 'Roads', 'icon' => 'bi-signpost', 'route' => 'admin.roads.index', 'class' => 'ic-green'],
            'building' => ['label' => 'Buildings', 'icon' => 'bi-buildings', 'route' => 'admin.buildings.index', 'class' => 'ic-purple'],
            'hospital' => ['label' => 'Hospitals', 'icon' => 'bi-hospital', 'route' => 'admin.hospitals.index', 'class' => 'ic-red'],
        ];
        $typeIds = \App\Models\InfrastructureType::pluck('id', 'slug');
    @endphp
    <div class="row g-3 mb-4">
        @foreach ($typeMeta as $slug => $meta)
            <div class="col-6 col-lg-3">
                <a href="{{ route($meta['route']) }}" class="bc-stat-card" style="text-decoration:none;">
                    <div class="bc-stat-icon {{ $meta['class'] }}"><i class="bi {{ $meta['icon'] }}"></i></div>
                    <div class="bc-stat-info">
                        <div class="bc-stat-value">{{ $typeCounts[$typeIds[$slug] ?? 0] ?? 0 }}</div>
                        <div class="bc-stat-label">{{ $meta['label'] }}</div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="bc-card">
        <div class="bc-card-body flush">
            <div class="bc-table-wrap">
                <table class="bc-table">
                    <thead>
                        <tr>
                            <th>Asset</th>
                            <th>Code</th>
                            <th>Type</th>
                            <th>Location</th>
                            <th>Built</th>
                            <th>Condition</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($infrastructures as $infra)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ $infra->imageUrl() }}" class="bc-thumb" alt="{{ $infra->name }}">
                                        <div>
                                            <div class="row-title">{{ $infra->name }}</div>
                                            <div class="row-sub">{{ Str::limit($infra->description, 40) }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><code style="font-size:12px;background:#f1f5f9;padding:3px 8px;border-radius:6px;">{{ $infra->code }}</code></td>
                                <td>
                                    <span class="bc-badge badge-navy"><i class="bi {{ $typeMeta[$infra->type->slug]['icon'] ?? 'bi-circle' }}"></i> {{ $infra->type->name }}</span>
                                </td>
                                <td style="max-width:180px;font-size:12.5px;">{{ Str::limit($infra->location, 30) }}</td>
                                <td style="white-space:nowrap;">{{ $infra->construction_year ?? '—' }}</td>
                                <td><x-condition-badge :condition="$infra->condition" /></td>
                                <td>
                                    <div class="bc-actions">
                                        <a href="{{ $infra->getDetailsUrl() }}" class="bc-action-btn view" title="View"><i class="bi bi-eye"></i></a>
                                        <a href="{{ route('admin.maintenance.create', ['infrastructure_id' => $infra->id]) }}" class="bc-action-btn maint" title="Maintenance"><i class="bi bi-wrench"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-empty-state icon="bi-diagram-3" title="No infrastructure found"
                                                   text="Try adjusting your search or filters." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-pagination :paginator="$infrastructures" />
@endsection
