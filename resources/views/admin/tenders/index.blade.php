@extends('layouts.admin')

@section('title', 'Tenders & Bids')

@section('content')
    <x-page-header title="Tenders & Bids" subtitle="Publish infrastructure tenders, review bids and award projects.">
        <a href="{{ route('admin.tenders.create') }}" class="btn-bc btn-bc-primary"><i class="bi bi-file-earmark-plus"></i> New Tender</a>
    </x-page-header>

    <div class="d-flex gap-2 flex-wrap mb-3">
        <a href="{{ route('admin.tenders.index') }}" class="btn-bc btn-bc-outline {{ ! $status ? 'btn-bc-primary' : '' }}">All</a>
        @foreach (['draft' => 'Drafts', 'published' => 'Published', 'closed' => 'Closed', 'awarded' => 'Awarded', 'cancelled' => 'Cancelled'] as $key => $label)
            <a href="{{ route('admin.tenders.index', array_merge(request()->except('page'), ['status' => $key])) }}"
               class="btn-bc btn-bc-outline {{ $status === $key ? 'btn-bc-primary' : '' }}">{{ $label }} ({{ $statusCounts[$key] ?? 0 }})</a>
        @endforeach
    </div>

    <form class="bc-filter-bar" method="GET" action="{{ route('admin.tenders.index') }}">
        @if ($status) <input type="hidden" name="status" value="{{ $status }}"> @endif
        <div class="bc-search-box">
            <i class="bi bi-search"></i>
            <input type="text" name="search" class="bc-input" placeholder="Search by title or location..." value="{{ $search }}">
        </div>
        <select name="type" class="bc-select" onchange="this.form.submit()">
            <option value="">All categories</option>
            @foreach ($types as $opt)
                <option value="{{ $opt->id }}" @selected((string) $type === (string) $opt->id)>{{ $opt->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-search"></i> Search</button>
        @if ($search || $type || $status)
            <a href="{{ route('admin.tenders.index') }}" class="btn-bc btn-bc-ghost btn-bc-sm"><i class="bi bi-x-circle"></i> Clear</a>
        @endif
    </form>

    <div class="bc-card">
        <div class="bc-card-body flush">
            <div class="bc-table-wrap">
                <table class="bc-table">
                    <thead>
                        <tr>
                            <th>Tender</th>
                            <th>Category</th>
                            <th>Budget</th>
                            <th>Deadline</th>
                            <th>Bids</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tenders as $tender)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.tenders.show', $tender) }}" class="row-title" style="color:var(--bc-blue);">{{ $tender->title }}</a>
                                    <div class="row-sub">{{ Str::limit($tender->location, 40) }}</div>
                                </td>
                                <td><span class="bc-badge badge-navy">{{ $tender->type?->name ?? '—' }}</span></td>
                                <td style="white-space:nowrap;font-weight:700;">₹{{ number_format($tender->estimated_budget, 2) }}</td>
                                <td style="white-space:nowrap;font-size:12.5px;color:#64748b;">
                                    {{ $tender->deadline->format('d M Y, H:i') }}
                                    @if ($tender->status === 'published' && $tender->isExpired())
                                        <div class="text-danger" style="font-size:11.5px;">Expired</div>
                                    @endif
                                </td>
                                <td style="text-align:center;">
                                    <span class="bc-badge badge-blue">{{ $tender->bids()->count() }}</span>
                                </td>
                                <td><x-status-badge :status="$tender->statusLabel()" /></td>
                                <td>
                                    <div class="bc-actions">
                                        <a href="{{ route('admin.tenders.show', $tender) }}" class="bc-action-btn view" title="View"><i class="bi bi-eye"></i></a>
                                        <a href="{{ route('admin.tenders.edit', $tender) }}" class="bc-action-btn edit" title="Edit"><i class="bi bi-pencil"></i></a>
                                        @if ($tender->status === 'draft')
                                            <form action="{{ route('admin.tenders.publish', $tender) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="bc-action-btn view" title="Publish"><i class="bi bi-megaphone"></i></button>
                                            </form>
                                            <form action="{{ route('admin.tenders.destroy', $tender) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="bc-action-btn delete" title="Delete Draft" data-bc-delete="bcDelTender{{ $tender->id }}" data-message="Draft tender will be permanently deleted.">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-empty-state icon="bi-file-earmark-rupee" title="No tenders found"
                                                   text="Publish your first tender to invite contractors to bid."
                                                   action-label="New Tender" action-url="{{ route('admin.tenders.create') }}" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-pagination :paginator="$tenders" />
@endsection
