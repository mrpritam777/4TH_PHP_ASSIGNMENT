@extends('layouts.public')

@section('title', 'Track Maintenance Request')

@section('content')
    <x-page-header title="Track Maintenance Request" subtitle="Enter your request number to check the status of a reported issue.">
    </x-page-header>

    <div class="bc-card">
        <div class="bc-card-body">
            <form method="GET" action="{{ route('public.track') }}" class="d-flex gap-2 flex-wrap">
                <div class="bc-search-box" style="flex:1;min-width:280px;">
                    <i class="bi bi-search"></i>
                    <input type="text" name="requestNumber" class="bc-input" placeholder="Enter your request number (e.g. BRG-2026-000145)" value="{{ old('requestNumber') }}">
                </div>
                <button type="submit" class="btn-bc btn-bc-primary btn-bc-lg"><i class="bi bi-search"></i> Track Request</button>
            </form>
        </div>
    </div>

    <div class="bc-card mt-4">
        <div class="bc-card-body">
            <div class="bc-empty" style="text-align:center;padding:48px 24px;">
                <i class="bi bi-search"></i>
                <h4>Ready to track</h4>
                <p>Enter your maintenance request number above to see its current status, progress updates, and expected completion date.</p>
            </div>
        </div>
    </div>
@endsection
