<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In — {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
</head>
<body>
<div class="bc-login-wrap">
    {{-- Left: brand panel --}}
    <div class="bc-login-left">
        <x-logo />
        <h1>Smart Infrastructure<br>Management <span>Platform</span></h1>
        <p>Monitor, manage and maintain bridges, roads, buildings and hospitals through one centralized, secure platform.</p>

        <div class="bc-login-points">
            <div class="bc-login-point">
                <i class="bi bi-signpost-split"></i>
                <div>
                    <strong>Bridge-First Management</strong>
                    <small>Complete lifecycle tracking for every bridge in your network</small>
                </div>
            </div>
            <div class="bc-login-point">
                <i class="bi bi-wrench-adjustable-circle"></i>
                <div>
                    <strong>Maintenance Workflow</strong>
                    <small>Assign tasks, track progress and record before/after evidence</small>
                </div>
            </div>
            <div class="bc-login-point">
                <i class="bi bi-shield-check"></i>
                <div>
                    <strong>Secure &amp; Auditable</strong>
                    <small>Role-based access with full activity logging</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Right: form --}}
    <div class="bc-login-right">
        <div class="bc-login-card">
            <x-logo />
            <h2>Welcome back</h2>
            <p>Sign in to your account to access the infrastructure dashboard.</p>

            @if (session('error'))
                <div class="bc-alert bc-alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif
            @if (session('success'))
                <div class="bc-alert bc-alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}" data-bc-loading>
                @csrf
                <div class="bc-login-field">
                    <i class="bi bi-person"></i>
                    <label for="email" class="bc-form-label d-block" style="position:absolute;top:-24px;left:0;">Email address</label>
                    <input type="email" class="bc-input @error('email') is-invalid @enderror" id="email" name="email"
                           value="{{ old('email') }}" placeholder="admin@bridgecare.gov" required autofocus>
                    @error('email')
                        <div style="color:#dc2626;font-size:12px;margin-top:5px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="bc-login-field">
                    <i class="bi bi-lock"></i>
                    <label for="password" class="bc-form-label d-block" style="position:absolute;top:-24px;left:0;">Password</label>
                    <input type="password" class="bc-input @error('password') is-invalid @enderror" id="password" name="password"
                           placeholder="••••••••" required>
                    <button type="button" class="bc-toggle-pass" id="bcPassToggle" aria-label="Show password">
                        <i class="bi bi-eye"></i>
                    </button>
                    @error('password')
                        <div style="color:#dc2626;font-size:12px;margin-top:5px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="bc-login-remember">
                    <label>
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    <a href="#" style="font-size:13px;">Forgot password?</a>
                </div>

                <button type="submit" class="btn-bc btn-bc-primary">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In
                </button>
            </form>

            <div class="bc-login-alt">
                <a href="{{ route('public.home') }}"><i class="bi bi-globe2"></i> Visit public website</a>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>
