@extends('layouts.public')

@section('title', 'Infrastructure Directory')

@section('content')
    <x-page-header title="Infrastructure Directory" subtitle="Explore bridges, roads, buildings and hospitals in our network.">
    </x-page-header>

    <form class="bc-filter-bar" method="GET" action="{{ route('public.infrastructure') }}">
        <div class="bc-search-box">
            <i class="bi bi-search"></i>
            <input type="text" name="search" class="bc-input" placeholder="Search by name, code or location..." value="{{ $search }}">
        </div>
        <select name="type" class="bc-select">
            <option value="">All Types</option>
            @foreach (['bridge' => 'Bridges', 'road' => 'Roads', 'building' => 'Buildings', 'hospital' => 'Hospitals'] as $val => $lbl)
                <option value="{{ $val }}" @selected($type === $val)>{{ $lbl }}</option>
            @endforeach
        </select>
        <select name="condition" class="bc-select">
            <option value="">All Conditions</option>
            @foreach (['excellent' => 'Excellent', 'good' => 'Good', 'fair' => 'Fair', 'poor' => 'Poor', 'critical' => 'Critical'] as $val => $lbl)
                <option value="{{ $val }}" @selected($condition === $val)>{{ $lbl }}</option>
            @endforeach
        </select>
        <input type="text" name="location" class="bc-input" placeholder="Location (city/district)..." value="{{ $location }}">
        <button type="submit" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-funnel"></i> Filter</button>
        @if ($search || $type || $condition || $location)
            <a href="{{ route('public.infrastructure') }}" class="btn-bc btn-bc-ghost btn-bc-sm"><i class="bi bi-x-circle"></i> Clear</a>
        @endif
    </form>

    @if ($infrastructures->isNotEmpty())
        <div class="row g-4">
            @foreach ($infrastructures as $infra)
                <div class="col-md-6 col-lg-4">
                    <div class="bc-infra-card">
                        <div class="img-wrap">
                            <img src="{{ $infra->imageUrl() }}" alt="{{ $infra->name }}">
                            <span class="type-chip"><i class="bi {{ ['bi-signpost-split', 'bi-signpost', 'bi-buildings', 'bi-hospital'][array_search($infra->type->slug, ['bridge', 'road', 'building', 'hospital'])] }} me-1"></i>{{ ucfirst($infra->type->name) }}</span>
                        </div>
                        <div class="body">
                            <h3>{{ $infra->name }}</h3>
                            <div class="loc"><i class="bi bi-geo-alt"></i> {{ Str::limit($infra->location, 30) }}</div>
                            <div class="d-flex gap-2 mb-3" style="font-size:12.5px;color:#64748b;">
                                <x-condition-badge :condition="$infra->condition" />
                            </div>
                            <div class="foot">
                                <a href="{{ $infra->getDetailsUrl() }}" class="btn-bc btn-bc-outline btn-bc-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <x-pagination :paginator="$infrastructures" />
    @else
        <div class="bc-card">
            <x-empty-state icon="bi-diagram-3" title="No infrastructure found"
                           text="Try adjusting your search or filters." />
        </div>
    @endif
@endsection
