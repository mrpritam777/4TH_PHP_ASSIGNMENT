@extends('layouts.admin')

@section('title', 'Infrastructure Categories')

@section('content')
    <x-page-header title="Infrastructure Categories" subtitle="These categories power the expandable All Infrastructure menu and tender classification.">
        <a href="{{ route('admin.infrastructures.index') }}" class="btn-bc btn-bc-outline"><i class="bi bi-diagram-3"></i> All Infrastructure</a>
    </x-page-header>

    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="bc-card">
                <div class="bc-card-header">
                    <h3><i class="bi bi-plus-circle me-2" style="color:var(--bc-blue);"></i>Add More</h3>
                </div>
                <div class="bc-card-body">
                    <form method="POST" action="{{ route('admin.types.store') }}" data-bc-loading>
                        @csrf
                        <label class="bc-form-label">Category Name <span class="req">*</span></label>
                        <input type="text" name="name" class="bc-input @error('name') is-invalid @enderror" value="{{ old('name') }}" required minlength="2" maxlength="60" placeholder="e.g. Flyovers">
                        @error('name') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                        <div class="mt-3">
                            <label class="bc-form-label">Description</label>
                            <textarea name="description" class="bc-textarea" rows="2" maxlength="255" placeholder="Optional short description...">{{ old('description') }}</textarea>
                        </div>
                        <div class="mt-3">
                            <label class="bc-form-label">Status <span class="req">*</span></label>
                            <select name="status" class="bc-select" required>
                                <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                                <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-bc btn-bc-primary w-100 mt-3"><i class="bi bi-check-lg"></i> Add Category</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="bc-card">
                <div class="bc-card-body flush">
                    <div class="bc-table-wrap">
                        <table class="bc-table">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Usage</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($types as $row)
                                    <tr>
                                        <td>
                                            <div class="row-title">{{ Str::plural($row->name) }}</div>
                                            <div class="row-sub">{{ Str::limit($row->description ?? 'No description', 60) }}</div>
                                        </td>
                                        <td style="font-size:12.5px;">
                                            <span class="bc-badge badge-blue">{{ $row->infrastructures_count }} assets</span>
                                            <span class="bc-badge badge-navy">{{ $row->tenders_count }} tenders</span>
                                        </td>
                                        <td>
                                            @if ($row->isActive())
                                                <span class="bc-badge badge-green"><span class="dot"></span> Active</span>
                                            @else
                                                <span class="bc-badge badge-gray"><span class="dot"></span> Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="bc-actions">
                                                <button type="button" class="bc-action-btn edit" data-bs-toggle="collapse" data-bs-target="#bcTypeEdit{{ $row->id }}" title="Edit"><i class="bi bi-pencil"></i></button>
                                                <form action="{{ route('admin.types.toggle-status', $row) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="bc-action-btn {{ $row->isActive() ? 'delete' : 'view' }}" title="{{ $row->isActive() ? 'Deactivate' : 'Activate' }}">
                                                        <i class="bi {{ $row->isActive() ? 'bi-power' : 'bi-check-circle' }}"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.types.destroy', $row) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="bc-action-btn delete" title="Delete" data-bc-delete="bcDelType{{ $row->id }}" data-message="Category will be deleted if it is not used by any assets or tenders.">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="collapse" id="bcTypeEdit{{ $row->id }}">
                                        <td colspan="4" style="background:#f8fafc;">
                                            <form method="POST" action="{{ route('admin.types.update', $row) }}" class="d-flex gap-2 flex-wrap align-items-end">
                                                @csrf
                                                @method('PUT')
                                                <div style="flex:2;min-width:180px;">
                                                    <label class="bc-form-label">Name <span class="req">*</span></label>
                                                    <input type="text" name="name" class="bc-input" value="{{ $row->name }}" required minlength="2" maxlength="60">
                                                </div>
                                                <div style="flex:3;min-width:220px;">
                                                    <label class="bc-form-label">Description</label>
                                                    <input type="text" name="description" class="bc-input" value="{{ $row->description }}" maxlength="255">
                                                </div>
                                                <div style="min-width:140px;">
                                                    <label class="bc-form-label">Status <span class="req">*</span></label>
                                                    <select name="status" class="bc-select" required>
                                                        <option value="active" @selected($row->status === 'active')>Active</option>
                                                        <option value="inactive" @selected($row->status === 'inactive')>Inactive</option>
                                                    </select>
                                                </div>
                                                <button type="submit" class="btn-bc btn-bc-primary btn-bc-sm"><i class="bi bi-check-lg"></i> Save</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">
                                            <x-empty-state icon="bi-diagram-3" title="No categories yet" text="Use the Add More panel to create your first category." />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="bc-note-box info mt-3">
                <i class="bi bi-info-circle me-1"></i> Categories still used by infrastructure assets or tenders cannot be deleted — deactivate them instead, or reassign the records first.
            </div>
        </div>
    </div>
@endsection
