@extends('layouts.public')

@section('title', 'Home')

@section('content')
    @php
        $typeIcons = [
            'bridge' => 'bi-signpost-split',
            'road' => 'bi-signpost',
            'building' => 'bi-buildings',
            'hospital' => 'bi-hospital',
        ];
        $cards = $featured->flatten(1);
    @endphp

    <div class="bc-pub-flush">
        {{-- Hero --}}
        <section class="bc-hero">
            <div class="container">
                <div class="bc-hero-content">
                    <span class="bc-hero-kicker"><i class="bi bi-shield-check"></i> Smart Infrastructure Management</span>
                    <h1>Building a <span>Smarter, Safer</span><br>Infrastructure Network</h1>
                    <p>Monitor, manage and maintain bridges, roads, buildings and hospitals through one centralized, secure platform — with real-time insights, transparent tracking and faster maintenance.</p>
                    <div class="bc-hero-actions">
                        <a href="{{ route('public.report.create') }}" class="btn-bc btn-bc-primary btn-bc-lg"><i class="bi bi-megaphone"></i> Report an Issue</a>
                        <a href="{{ route('public.infrastructure') }}" class="btn-bc btn-bc-outline btn-bc-lg"><i class="bi bi-grid-3x3-gap"></i> Explore Infrastructure</a>
                    </div>
                    <div class="bc-hero-trust">
                        <span><i class="bi bi-patch-check-fill"></i> Verified Asset Records</span>
                        <span><i class="bi bi-clock-history"></i> Real-time Tracking</span>
                        <span><i class="bi bi-shield-lock-fill"></i> Secure &amp; Transparent</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Stat strip (overlaps the hero) --}}
        <div class="container">
            <div class="bc-pub-stats">
                <div class="bc-pub-stat">
                    <div class="num">{{ $stats['bridges'] }}</div>
                    <div class="lbl">Bridges</div>
                </div>
                <div class="bc-pub-stat">
                    <div class="num">{{ $stats['roads'] }}</div>
                    <div class="lbl">Roads</div>
                </div>
                <div class="bc-pub-stat">
                    <div class="num">{{ $stats['buildings'] }}</div>
                    <div class="lbl">Buildings</div>
                </div>
                <div class="bc-pub-stat">
                    <div class="num">{{ $stats['hospitals'] }}</div>
                    <div class="lbl">Hospitals</div>
                </div>
            </div>
        </div>

        {{-- What you can do --}}
        <section class="bc-pub-section">
            <div class="container">
                <div class="bc-section-title">
                    <small>What You Can Do</small>
                    <h2>Everything in one place</h2>
                    <p>Report problems, follow their progress and explore the infrastructure network around you.</p>
                </div>
                <div class="row g-4">
                    @foreach ([
                        ['icon' => 'bi-megaphone', 'tone' => 'ic-blue', 'title' => 'Report an Issue', 'text' => 'Spotted a pothole, damaged bridge or unsafe building? Report it in seconds with photos and location details.', 'route' => 'public.report.create', 'cta' => 'Report now'],
                        ['icon' => 'bi-geo-alt', 'tone' => 'ic-green', 'title' => 'Explore Infrastructure', 'text' => 'Browse bridges, roads, buildings and hospitals with live condition status and complete asset details.', 'route' => 'public.infrastructure', 'cta' => 'Open directory'],
                        ['icon' => 'bi-clipboard2-pulse', 'tone' => 'ic-purple', 'title' => 'Track Requests', 'text' => 'Follow every maintenance request from report to completion with a transparent status timeline.', 'route' => 'public.track', 'cta' => 'Track a request'],
                        ['icon' => 'bi-graph-up-arrow', 'tone' => 'ic-orange', 'title' => 'Data-driven Insights', 'text' => 'Administrators gain reports, cost analytics and condition monitoring to prioritise what matters most.', 'route' => 'login', 'cta' => 'Sign in'],
                    ] as $feature)
                        <div class="col-md-6 col-lg-3">
                            <div class="bc-feature-card">
                                <div class="bc-feature-icon {{ $feature['tone'] }}"><i class="bi {{ $feature['icon'] }}"></i></div>
                                <h3>{{ $feature['title'] }}</h3>
                                <p>{{ $feature['text'] }}</p>
                                <a href="{{ route($feature['route']) }}" class="bc-feature-link">{{ $feature['cta'] }} <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Featured infrastructure --}}
        @if ($cards->isNotEmpty())
            <section class="bc-pub-section">
                <div class="container">
                    <div class="bc-section-title">
                        <small>Featured Infrastructure</small>
                        <h2>Highlighted Assets</h2>
                        <p>A snapshot of the key infrastructure assets currently under management.</p>
                    </div>
                    <div class="row g-4">
                        @foreach ($cards as $infra)
                            <div class="col-md-6 col-lg-3">
                                <div class="bc-infra-card">
                                    <div class="img-wrap">
                                        <img src="{{ $infra->imageUrl() }}" alt="{{ $infra->name }}">
                                        <span class="type-chip"><i class="bi {{ $typeIcons[$infra->type->slug] ?? 'bi-geo-alt' }} me-1"></i>{{ ucfirst($infra->type->name) }}</span>
                                    </div>
                                    <div class="body">
                                        <h3>{{ $infra->name }}</h3>
                                        <div class="loc"><i class="bi bi-geo-alt"></i> {{ Str::limit($infra->location, 28) }}</div>
                                        <div class="mb-3">
                                            <x-condition-badge :condition="$infra->condition" />
                                        </div>
                                        <div class="foot">
                                            <a href="{{ $infra->getDetailsUrl() }}" class="btn-bc btn-bc-outline btn-bc-sm">View Details</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="text-center mt-4">
                        <a href="{{ route('public.infrastructure') }}" class="btn-bc btn-bc-outline btn-bc-lg"><i class="bi bi-grid-3x3-gap"></i> View All Infrastructure</a>
                    </div>
                </div>
            </section>
        @endif

        {{-- Call to action --}}
        <section class="bc-cta-band">
            <div class="container">
                <div>
                    <h2>Help us keep our infrastructure safe</h2>
                    <p>Every report you submit helps our teams act faster and keeps roads, bridges, buildings and hospitals safe for everyone.</p>
                </div>
                <a href="{{ route('public.report.create') }}" class="btn-bc btn-bc-primary btn-bc-lg"><i class="bi bi-megaphone"></i> Report an Issue</a>
            </div>
        </section>
    </div>
@endsection
