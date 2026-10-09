@extends('layouts.admin')

@section('title', $tender ? 'Edit Tender' : 'New Tender')

@section('content')
    <x-page-header
        :title="$tender ? 'Edit Tender' : 'Publish New Tender'"
        :subtitle="$tender ? 'Update the tender details.' : 'Publishing a tender notifies all approved contractors automatically.'"
        :breadcrumbs="[[ 'label' => 'Tenders', 'url' => route('admin.tenders.index') ]]">
    </x-page-header>

    <form method="POST" action="{{ $tender ? route('admin.tenders.update', $tender) : route('admin.tenders.store') }}" data-bc-loading>
        @csrf
        @if ($tender) @method('PUT') @endif

        <div class="bc-form-section">
            <div class="bc-form-section-header">
                <i class="bi bi-file-earmark-text"></i>
                <div>
                    <h3>Tender Details</h3>
                    <small>Title, category, budget and location</small>
                </div>
            </div>
            <div class="bc-form-section-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="bc-form-label">Title <span class="req">*</span></label>
                        <input type="text" name="title" class="bc-input @error('title') is-invalid @enderror" value="{{ old('title', $tender?->title) }}" required maxlength="200" placeholder="e.g. Repair of the Old Howrah Bridge approach road">
                        @error('title') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="bc-form-label">Infrastructure Category <span class="req">*</span></label>
                        <select name="infrastructure_type_id" class="bc-select @error('infrastructure_type_id') is-invalid @enderror" required>
                            <option value="">Select category...</option>
                            @foreach ($types as $opt)
                                <option value="{{ $opt->id }}" @selected((string) old('infrastructure_type_id', $tender?->infrastructure_type_id) === (string) $opt->id)>{{ $opt->name }}</option>
                            @endforeach
                        </select>
                        @error('infrastructure_type_id') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="bc-form-label">Description <span class="req">*</span></label>
                        <textarea name="description" class="bc-textarea @error('description') is-invalid @enderror" required minlength="20" rows="4" placeholder="Scope of work, deliverables, payment terms...">{{ old('description', $tender?->description) }}</textarea>
                        @error('description') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="bc-form-label">Location <span class="req">*</span></label>
                        <input type="text" name="location" class="bc-input @error('location') is-invalid @enderror" value="{{ old('location', $tender?->location) }}" required maxlength="200" placeholder="District, State">
                        @error('location') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="bc-form-label">Estimated Budget (₹) <span class="req">*</span></label>
                        <input type="number" step="0.01" min="1" name="estimated_budget" class="bc-input @error('estimated_budget') is-invalid @enderror" value="{{ old('estimated_budget', $tender?->estimated_budget) }}" required>
                        @error('estimated_budget') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="bc-form-label">Submission Deadline <span class="req">*</span></label>
                        <input type="datetime-local" name="deadline" class="bc-input @error('deadline') is-invalid @enderror"
                               value="{{ old('deadline', $tender?->deadline ? $tender->deadline->format('Y-m-d\TH:i') : '') }}" required
                               min="{{ now()->format('Y-m-d\TH:i') }}">
                        @error('deadline') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="bc-form-label">Required Documents from Bidders</label>
                        <div class="bc-check-grid">
                            @foreach ($documentOptions as $option)
                                <label class="bc-check">
                                    <input type="checkbox" name="required_documents[]" value="{{ $option }}"
                                        @checked(in_array($option, old('required_documents', $tender?->required_documents ?? [])))>
                                    {{ $option }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="bc-form-label">Bidding Rules</label>
                        <label class="bc-check">
                            <input type="checkbox" name="allow_bid_edit" value="1" @checked(old('allow_bid_edit', $tender?->allow_bid_edit))>
                            Allow contractors to edit their bids before the deadline
                        </label>
                        <small style="color:#64748b;font-size:12px;display:block;margin-top:8px;">When disabled, duplicate bids from the same contractor are strictly rejected.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap mb-4">
            @if (! $tender || $tender->status === 'draft')
                <button type="submit" name="intent" value="publish" class="btn-bc btn-bc-primary"><i class="bi bi-megaphone"></i> {{ $tender ? 'Save & Publish' : 'Publish Tender' }}</button>
                <button type="submit" name="intent" value="draft" class="btn-bc btn-bc-outline"><i class="bi bi-save"></i> Save as Draft</button>
            @else
                <button type="submit" name="intent" value="save" class="btn-bc btn-bc-primary"><i class="bi bi-check-lg"></i> Save Changes</button>
            @endif
            <a href="{{ $tender ? route('admin.tenders.show', $tender) : route('admin.tenders.index') }}" class="btn-bc btn-bc-ghost"><i class="bi bi-x-lg"></i> Cancel</a>
        </div>
    </form>
@endsection
