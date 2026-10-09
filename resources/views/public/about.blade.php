@extends('layouts.public')

@section('title', 'About Us')

@section('content')
    <x-page-header title="About BridgeCare" subtitle="Who we are, what we do, and how we work with contractors to keep infrastructure safe.">
    </x-page-header>

    <div class="bc-about-hero mb-4">
        <span class="bc-hero-kicker" style="background:rgba(255,255,255,.12);"><i class="bi bi-building"></i> BridgeCare Infrastructure Authority</span>
        <h1 style="margin-top:14px; color:var(--bc-blue);">Smart Infrastructure Management for Safer Communities</h1>
        <p>We monitor, maintain and improve the public infrastructure network — bridges, roads, buildings and hospitals — through a transparent, technology-driven platform. From citizen issue reports to competitive tendering, every step is tracked, auditable and accountable.</p>
    </div>

    <div class="bc-about-grid mb-5">
        <div class="bc-about-card">
            <div class="num" style="background:var(--bc-blue-light);color:var(--bc-blue);"><i class="bi bi-eye"></i></div>
            <h3>Our Mission</h3>
            <p>Deliver safe, reliable and future-ready public infrastructure through proactive maintenance, transparent procurement and data-led decision making.</p>
        </div>
        <div class="bc-about-card">
            <div class="num" style="background:#dcfce7;color:#16a34a;"><i class="bi bi-diagram-3"></i></div>
            <h3>What We Manage</h3>
            <p>The full lifecycle of infrastructure assets — inspection, maintenance requests, condition tracking, contractor tendering and project awards.</p>
        </div>
        <div class="bc-about-card">
            <div class="num" style="background:#fef3c7;color:#d97706;"><i class="bi bi-megaphone"></i></div>
            <h3>Open Tendering</h3>
            <p>All tenders are published publicly with clear deadlines and requirements, so every approved contractor competes on a level playing field.</p>
        </div>
        <div class="bc-about-card">
            <div class="num" style="background:#ede9fe;color:#7c3aed;"><i class="bi bi-patch-check"></i></div>
            <h3>Accountability</h3>
            <p>Every decision — maintenance assignment, bid review, tender award or cancellation — is logged and auditable through our activity trail.</p>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-lg-6">
            <div class="bc-card h-100">
                <div class="bc-card-body">
                    <h2 class="bc-section-h">How It Works</h2>
                    <p class="section-sub bc-section-sub">From a citizen's report to a completed, verified project.</p>
                    <div class="bc-timeline">
                        <div class="t-item">
                            <h4>1. Report & Track</h4>
                            <p>Citizens report infrastructure issues online and track progress with a unique request number.</p>
                        </div>
                        <div class="t-item">
                            <h4>2. Inspect & Plan</h4>
                            <p>Our team assesses the asset, records its condition and plans the maintenance or improvement work.</p>
                        </div>
                        <div class="t-item">
                            <h4>3. Publish Tender</h4>
                            <p>Eligible projects are published as open tenders with a clear scope, budget and submission deadline.</p>
                        </div>
                        <div class="t-item">
                            <h4>4. Evaluate & Award</h4>
                            <p>Approved contractors submit bids with documents and proposals; the best bid is awarded transparently.</p>
                        </div>
                        <div class="t-item">
                            <h4>5. Deliver & Verify</h4>
                            <p>Work is executed, tracked and closed with full history retained for future reference.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="bc-card mb-4">
                <div class="bc-card-body">
                    <h2 class="bc-section-h">Infrastructure We Cover</h2>
                    <p class="bc-section-sub">Categories currently managed on the platform.</p>
                    <div class="bc-about-grid">
                        @forelse ($categories as $cat)
                            <div class="bc-about-card" style="box-shadow:none;">
                                <div class="num" style="background:var(--bc-blue-light);color:var(--bc-blue);width:34px;height:34px;font-size:13px;">
                                    <i class="bi bi-signpost-split"></i>
                                </div>
                                <h3 style="margin-bottom:4px;">{{ $cat->name }}</h3>
                                <p style="font-size:12.5px;">{{ \Illuminate\Support\Str::limit($cat->description, 90) ?: 'Managed infrastructure category.' }}</p>
                            </div>
                        @empty
                            <p class="text-muted" style="font-size:13.5px;">Categories will appear here once configured.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="bc-card">
                <div class="bc-card-body">
                    <h2 class="bc-section-h">Get In Touch</h2>
                    <p class="bc-section-sub">We'd love to hear from you.</p>
                    <div class="bc-detail-list">
                        <div class="row-item"><span class="k"><i class="bi bi-building me-2"></i>Organization</span><span class="v">{{ $contact['organization'] }}</span></div>
                        <div class="row-item"><span class="k"><i class="bi bi-envelope me-2"></i>Email</span><span class="v">{{ $contact['email'] }}</span></div>
                        <div class="row-item"><span class="k"><i class="bi bi-telephone me-2"></i>Phone</span><span class="v">{{ $contact['phone'] }}</span></div>
                        <div class="row-item"><span class="k"><i class="bi bi-geo-alt me-2"></i>Address</span><span class="v">{{ $contact['address'] }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bc-cta-band mb-4">
        <div class="container">
            <h3 style="color:var(--bc-blue)">Are you an infrastructure contractor?</h3>
            <p>Register your company, get approved, and bid on open tenders across the region.</p>
            <div class="d-flex gap-2 justify-content-center flex-wrap">
                <a href="{{ route('contractor.register') }}" class="btn-bc btn-bc-primary btn-bc-lg"><i class="bi bi-person-plus"></i> Register as a Contractor</a>
                <a href="{{ route('public.tenders.index') }}" class="btn-bc btn-bc-outline btn-bc-lg"><i class="bi bi-file-earmark-rupee"></i> Browse Tenders</a>
            </div>
        </div>
    </div>
@endsection
