@extends('layouts.public')

@section('title', 'Report Submitted — ' . $request->request_number)

@section('content')
    <x-page-header title="Report Submitted Successfully" subtitle="Thank you for helping keep our infrastructure safe. Save the tracking code below to check your report's progress anytime.">
        <a href="{{ route('public.track') }}" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-search"></i> Track a Request</a>
    </x-page-header>

    <div class="bc-card mb-4">
        <div class="bc-card-body" style="text-align:center;padding:44px 24px;">
            <div style="width:74px;height:74px;border-radius:50%;background:#dcfce7;color:#16a34a;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:34px;">
                <i class="bi bi-check-lg"></i>
            </div>
            <h3 style="margin-bottom:8px;">Your issue report has been received</h3>
            <p style="color:#64748b;font-size:14.5px;max-width:560px;margin:0 auto 22px;">
                Our team will review your report and take the necessary action. Use the tracking code below to follow its status and progress updates.
            </p>

            <div style="display:inline-block;background:#f1f5f9;border:1px dashed #94a3b8;border-radius:12px;padding:14px 30px;">
                <div style="font-size:11.5px;letter-spacing:.08em;text-transform:uppercase;color:#64748b;margin-bottom:6px;">Tracking Code</div>
                <div style="font-size:24px;font-weight:800;letter-spacing:.06em;color:#1d4ed8;">{{ $request->request_number }}</div>
            </div>

            <div style="margin-top:20px;font-size:13.5px;color:#475569;display:flex;align-items:center;justify-content:center;gap:8px;">
                <span>Current Status:</span>
                <x-status-badge :status="$request->status" />
            </div>
        </div>
    </div>

    <div class="bc-card mb-4">
        <div class="bc-card-header">
            <h3>Report Summary</h3>
        </div>
        <div class="bc-card-body">
            <div class="bc-info-grid" style="grid-template-columns:repeat(2, 1fr); gap:16px;">
                <div class="bc-info-item">
                    <span class="label">Tracking ID</span>
                    <span class="value">{{ $request->request_number }}</span>
                </div>
                <div class="bc-info-item">
                    <span class="label">Status</span>
                    <span class="value"><x-status-badge :status="$request->status" /></span>
                </div>
                <div class="bc-info-item">
                    <span class="label">Issue</span>
                    <span class="value" style="font-size:13.5px;">{{ $request->title }}</span>
                </div>
                <div class="bc-info-item">
                    <span class="label">Infrastructure</span>
                    <span class="value">{{ $request->infrastructure->name }} ({{ $request->infrastructure->code }})</span>
                </div>
                <div class="bc-info-item">
                    <span class="label">Infrastructure Type</span>
                    <span class="value">{{ $request->infrastructure->type->name }}</span>
                </div>
                <div class="bc-info-item">
                    <span class="label">Submitted On</span>
                    <span class="value">{{ $request->created_at->format('d M Y, h:i A') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 flex-wrap mb-4">
        <a href="{{ route('public.track.show', $request->request_number) }}" class="btn-bc btn-bc-primary"><i class="bi bi-pin-map"></i> Track This Report</a>
        <a href="{{ route('public.report.create') }}" class="btn-bc btn-bc-ghost"><i class="bi bi-plus-circle"></i> Report Another Issue</a>
        <a href="{{ route('public.home') }}" class="btn-bc btn-bc-ghost"><i class="bi bi-house"></i> Back to Home</a>
    </div>
@endsection
