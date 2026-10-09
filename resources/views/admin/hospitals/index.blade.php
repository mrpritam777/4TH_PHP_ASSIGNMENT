@extends('layouts.admin')

@section('title', 'Hospitals')

@section('content')
    <x-page-header title="Hospital Management" subtitle="Manage hospitals and healthcare facilities in the network.">
        <a href="{{ route('admin.hospitals.create') }}" class="btn-bc btn-bc-primary"><i class="bi bi-plus-lg"></i> Add Hospital</a>
    </x-page-header>

    <form class="bc-filter-bar" method="GET" action="{{ route('admin.hospitals.index') }}">
        <div class="bc-search-box">
            <i class="bi bi-search"></i>
            <input type="text" name="search" class="bc-input" placeholder="Search by name, code or location..." value="{{ $search }}">
        </div>
        <button type="submit" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-funnel"></i> Search</button>
        @if ($search)
            <a href="{{ route('admin.hospitals.index') }}" class="btn-bc btn-bc-ghost btn-bc-sm"><i class="bi bi-x-circle"></i> Clear</a>
        @endif
    </form>

    @if ($hospitals->count())
        <div class="row g-3">
            @foreach ($hospitals as $hospital)
                <div class="col-md-6 col-xl-4">
                    <div class="bc-infra-card">
                        <div class="img-wrap">
                            <img src="{{ $hospital->imageUrl() }}" alt="{{ $hospital->name }}">
                            <span class="type-chip"><i class="bi bi-hospital me-1"></i>Hospital</span>
                        </div>
                        <div class="body">
                            <h3>{{ $hospital->name }}</h3>
                            <div class="loc"><i class="bi bi-geo-alt"></i> {{ Str::limit($hospital->location, 40) }}</div>
                            <div class="d-flex gap-2 flex-wrap mb-3">
                                <x-condition-badge :condition="$hospital->condition" />
                                @if ($hospital->hospitalDetails?->emergency_facility)
                                    <span class="bc-badge badge-red"><i class="bi bi-ambulance"></i> Emergency</span>
                                @endif
                            </div>
                            <div class="row g-2 small mb-3" style="font-size:12.5px;color:#64748b;">
                                <div class="col-4"><i class="bi bi-hospital me-1"></i>{{ $hospital->hospitalDetails?->beds ?? 0 }} beds</div>
                                <div class="col-8"><i class="bi bi-telephone me-1"></i>{{ $hospital->hospitalDetails?->emergency_phone ?? '—' }}</div>
                            </div>
                            <div class="foot">
                                <a href="{{ route('admin.hospitals.show', $hospital) }}" class="btn-bc btn-bc-outline btn-bc-sm">View Details</a>
                                <div class="bc-actions">
                                    <a href="{{ route('admin.hospitals.edit', $hospital) }}" class="bc-action-btn edit" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <form action="{{ route('admin.hospitals.destroy', $hospital) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bc-action-btn delete" title="Delete" data-bc-delete="bcDelHospital{{ $hospital->id }}"
                                                data-message="Hospital &quot;{{ $hospital->name }}&quot; will be permanently deleted.">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <x-pagination :paginator="$hospitals" />
    @else
        <div class="bc-card">
            <x-empty-state icon="bi-hospital" title="No hospitals found"
                           text="Try adjusting your search, or add a new hospital to the network."
                           action-label="Add Hospital" action-url="{{ route('admin.hospitals.create') }}" />
        </div>
    @endif
@endsection
