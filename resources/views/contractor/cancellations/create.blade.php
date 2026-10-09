@extends('layouts.admin')

@section('title', 'Request Cancellation')

@section('content')
    <x-page-header title="Request Cancellation" subtitle="A mandatory reason is required — the administrator will be notified and must decide." :breadcrumbs="[[ 'label' => 'Cancellation Requests', 'url' => route('contractor.cancellations.index') ]]">
    </x-page-header>

    <form method="POST" action="{{ route('contractor.cancellations.store') }}" data-bc-loading>
        @csrf
        <div class="bc-form-section">
            <div class="bc-form-section-header">
                <i class="bi bi-x-octagon"></i>
                <div>
                    <h3>Cancellation Request</h3>
                    <small>Select a tender you are involved in (bid submitted or awarded) and explain why it should be cancelled</small>
                </div>
            </div>
            <div class="bc-form-section-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="bc-form-label">Related Tender / Project <span class="req">*</span></label>
                        <select name="tender_id" class="bc-select @error('tender_id') is-invalid @enderror" required>
                            <option value="">Select a tender...</option>
                            @foreach ($tenders as $opt)
                                <option value="{{ $opt->id }}" @selected((string) old('tender_id', $preselected) === (string) $opt->id)>
                                    {{ $opt->title }} ({{ ucfirst($opt->status) }})
                                </option>
                            @endforeach
                        </select>
                        @error('tender_id') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="bc-form-label">Cancellation Reason <span class="req">*</span></label>
                        <textarea name="reason" class="bc-textarea @error('reason') is-invalid @enderror" required minlength="10" rows="4" placeholder="Explain clearly why this tender or project should be cancelled...">{{ old('reason') }}</textarea>
                        @error('reason') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                        <small style="color:#64748b;font-size:12px;">Submitting a request does not cancel the tender — it is only cancelled after the administrator's decision.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap mb-4">
            <button type="submit" class="btn-bc btn-bc-danger"><i class="bi bi-send"></i> Submit Request</button>
            <a href="{{ route('contractor.cancellations.index') }}" class="btn-bc btn-bc-ghost"><i class="bi bi-x-lg"></i> Cancel</a>
        </div>
    </form>
@endsection
