@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
    <x-page-header title="System Settings" subtitle="Configure organisation details and platform preferences.">
    </x-page-header>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="bc-card">
                <div class="bc-card-header">
                    <div>
                        <h3>Organisation Profile</h3>
                        <div class="sub">Shown on public pages and reports</div>
                    </div>
                    <span class="bc-badge badge-blue"><i class="bi bi-building"></i> General</span>
                </div>
                <div class="bc-card-body">
                    <form method="POST" action="{{ route('admin.settings.update') }}" data-bc-loading>
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="bc-form-label">Organisation Name <span class="req">*</span></label>
                            <input type="text" name="organization_name" class="bc-input @error('organization_name') is-invalid @enderror"
                                   value="{{ old('organization_name', $settings['organization_name']) }}" required>
                            @error('organization_name') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="bc-form-label">Support Email <span class="req">*</span></label>
                                <input type="email" name="support_email" class="bc-input @error('support_email') is-invalid @enderror"
                                       value="{{ old('support_email', $settings['support_email']) }}" required>
                                @error('support_email') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="bc-form-label">Support Phone <span class="req">*</span></label>
                                <input type="text" name="support_phone" class="bc-input @error('support_phone') is-invalid @enderror"
                                       value="{{ old('support_phone', $settings['support_phone']) }}" required>
                                @error('support_phone') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="bc-form-label">Address</label>
                            <textarea name="address" class="bc-textarea @error('address') is-invalid @enderror" style="min-height:80px;">{{ old('address', $settings['address']) }}</textarea>
                            @error('address') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                        </div>
                        <button type="submit" class="btn-bc btn-bc-primary"><i class="bi bi-check-lg"></i> Save Settings</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="bc-card">
                <div class="bc-card-header"><h3>Platform Information</h3></div>
                <div class="bc-card-body">
                    <div class="bc-detail-list">
                        <div class="row-item"><span class="k">Application</span><span class="v">{{ config('app.name') }}</span></div>
                        <div class="row-item"><span class="k">Version</span><span class="v">v1.0.0</span></div>
                        <div class="row-item"><span class="k">Environment</span><span class="v">{{ ucfirst(config('app.env')) }}</span></div>
                        <div class="row-item"><span class="k">Framework</span><span class="v">Laravel {{ app()->version() }}</span></div>
                        <div class="row-item"><span class="k">Database</span><span class="v">{{ config('database.connections.mysql.database') }}</span></div>
                        <div class="row-item"><span class="k">PHP Version</span><span class="v">{{ phpversion() }}</span></div>
                    </div>
                </div>
            </div>

            <div class="bc-card mt-3">
                <div class="bc-card-header"><h3>Quick Actions</h3></div>
                <div class="bc-card-body d-flex flex-column gap-2">
                    <a href="{{ route('admin.activity-logs.index') }}" class="btn-bc btn-bc-outline"><i class="bi bi-journal-text"></i> View Activity Logs</a>
                    <a href="{{ route('admin.users.index') }}" class="btn-bc btn-bc-outline"><i class="bi bi-people"></i> Manage Users</a>
                    <a href="{{ route('admin.contractors.index') }}" class="btn-bc btn-bc-outline"><i class="bi bi-person-workspace"></i> Manage Contractors</a>
                </div>
            </div>
        </div>
    </div>
@endsection
