@extends('layouts.admin')

@section('title', 'Contact Directory')

@section('content')
    <x-page-header title="Contact Directory" subtitle="Manage all infrastructure contacts — emergency, government, maintenance, hospitals and contractors.">
        <a href="{{ route('admin.contacts.create') }}" class="btn-bc btn-bc-primary"><i class="bi bi-person-plus"></i> Add Contact</a>
    </x-page-header>

    <form class="bc-filter-bar" method="GET" action="{{ route('admin.contacts.index') }}">
        <div class="bc-search-box">
            <i class="bi bi-search"></i>
            <input type="text" name="search" class="bc-input" placeholder="Search by name, organization, email or phone..." value="{{ $search }}">
        </div>
        <select name="type" class="bc-select">
            <option value="">All types</option>
            @foreach (['emergency' => 'Emergency', 'government' => 'Government', 'maintenance' => 'Maintenance', 'hospital' => 'Hospital', 'contractor' => 'Contractor', 'other' => 'Other'] as $val => $lbl)
                <option value="{{ $val }}" @selected($type === $val)>{{ $lbl }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-funnel"></i> Filter</button>
        @if ($search || $type)
            <a href="{{ route('admin.contacts.index') }}" class="btn-bc btn-bc-ghost btn-bc-sm"><i class="bi bi-x-circle"></i> Clear</a>
        @endif
    </form>

    <div class="bc-card">
        <div class="bc-card-body flush">
            <div class="bc-table-wrap">
                <table class="bc-table">
                    <thead>
                        <tr>
                            <th>Contact</th>
                            <th>Organization</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Type</th>
                            <th>Related Infrastructure</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contacts as $contact)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="bc-avatar-fallback">{{ strtoupper(substr($contact->name, 0, 1)) }}</span>
                                        <div>
                                            <div class="row-title">{{ $contact->name }}</div>
                                            <div class="row-sub">{{ $contact->designation ?? '—' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="font-size:12.5px;">
                                    {{ $contact->organization ?? '—' }}
                                    @if ($contact->department)
                                        <div class="row-sub">{{ $contact->department }}</div>
                                    @endif
                                </td>
                                <td style="font-size:12.5px;white-space:nowrap;">
                                    <i class="bi bi-telephone text-muted me-1"></i>{{ $contact->phone ?? '—' }}
                                </td>
                                <td style="font-size:12.5px;">
                                    <i class="bi bi-envelope text-muted me-1"></i>{{ $contact->email ?? '—' }}
                                </td>
                                <td>
                                    @php
                                        $typeBadge = ['emergency' => 'badge-red', 'government' => 'badge-navy', 'maintenance' => 'badge-blue', 'hospital' => 'badge-purple', 'contractor' => 'badge-orange', 'other' => 'badge-gray'];
                                    @endphp
                                    <span class="bc-badge {{ $typeBadge[$contact->contact_type] ?? 'badge-gray' }}">{{ ucfirst($contact->contact_type) }}</span>
                                </td>
                                <td style="font-size:12.5px;max-width:160px;">
                                    @if ($contact->infrastructure)
                                        <a href="{{ $contact->infrastructure->getDetailsUrl() }}" style="color:var(--bc-blue);">{{ $contact->infrastructure->name }}</a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="bc-actions">
                                        <a href="{{ route('admin.contacts.edit', $contact) }}" class="bc-action-btn edit" title="Edit"><i class="bi bi-pencil"></i></a>
                                        <form action="{{ route('admin.contacts.destroy', $contact) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bc-action-btn delete" title="Delete" data-bc-delete="bcDelContact{{ $contact->id }}"
                                                    data-message="Contact &quot;{{ $contact->name }}&quot; will be permanently deleted.">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-empty-state icon="bi-person-lines-fill" title="No contacts found"
                                                   text="Try adjusting your search, or add a new contact."
                                                   action-label="Add Contact" action-url="{{ route('admin.contacts.create') }}" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-pagination :paginator="$contacts" />
@endsection
