<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Home') — {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    @stack('styles')
</head>
<body>
    {{-- Public Navbar --}}
    <nav class="bc-pub-nav">
        <div class="container">
            <a href="{{ route('public.home') }}" class="bc-pub-logo">
                <x-logo icon="bi-building" />
            </a>

            <button type="button" class="bc-pub-nav-toggle" id="bcPubNavToggle" aria-label="Toggle navigation">
                <i class="bi bi-list"></i>
            </button>

            <div class="bc-pub-nav-links">
                <a href="{{ route('public.home') }}" @class(['active' => request()->routeIs('public.home')])>Home</a>
                <a href="{{ route('public.about') }}" @class(['active' => request()->routeIs('public.about')])>About</a>
                <a href="{{ route('public.tenders.index') }}" @class(['active' => request()->routeIs('public.tenders.*')])>Tenders</a>
                <a href="{{ route('public.infrastructure') }}" @class(['active' => request()->routeIs('public.infrastructure', 'public.bridge')])>Directory</a>
                <a href="{{ route('public.track') }}" @class(['active' => request()->routeIs('public.track', 'public.track.show')])>Track Request</a>
                <a href="{{ route('public.report.create') }}" @class(['bc-pub-cta', 'active' => request()->routeIs('public.report.create')])><i class="bi bi-plus-circle"></i> Report an Issue</a>

                @auth
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : (auth()->user()->isContractor() ? route('contractor.dashboard') : (auth()->user()->isStaff() ? route('staff.tasks.index') : route('public.home'))) }}" class="bc-pub-login">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="bc-pub-login">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Content --}}
    <main class="bc-content">
        @if (session('success'))
            <div class="bc-alert bc-alert-success">
                <i class="bi bi-check-circle-fill"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif
        @if (session('error'))
            <div class="bc-alert bc-alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif
        @if ($errors->any())
            <div class="bc-alert bc-alert-danger">
                <i class="bi bi-exclamation-circle-fill"></i>
                <div>
                    <strong>Please fix the following:</strong>
                    <ul style="margin:6px 0 0;padding-left:18px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bc-pub-footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-3">
                    <h5>About BridgeCare</h5>
                    <p>Smart Infrastructure Management System for bridges, roads, buildings and hospitals.</p>
                </div>
                <div class="col-md-3">
                    <h5>Resources</h5>
                    <a href="{{ route('public.home') }}">Home</a>
                    <a href="{{ route('public.infrastructure') }}">Infrastructure Directory</a>
                    <a href="{{ route('public.report.create') }}">Report an Issue</a>
                    <a href="{{ route('public.track') }}">Track Request</a>
                </div>
                <div class="col-md-3">
                    <h5>Contact</h5>
                    <p><i class="bi bi-telephone me-2"></i>{{ config('app.support_phone') ?? '+91 80 4610 4610' }}</p>
                    <p><i class="bi bi-envelope me-2"></i>{{ config('app.support_email') ?? 'support@bridgecare.gov' }}</p>
                    <p><i class="bi bi-geo-alt me-2"></i>{{ config('app.address') ?? 'Infrastructure House, New Delhi 110001' }}</p>
                </div>
                <div class="col-md-3">
                    <h5>Follow Us</h5>
                    <p>Stay updated with infrastructure maintenance and safety notices.</p>
                </div>
            </div>
            <div class="bc-footer-bottom">
                <span>© {{ date('Y') }} {{ config('app.name') }} — All Rights Reserved</span>
                <span>v1.0.0</span>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
