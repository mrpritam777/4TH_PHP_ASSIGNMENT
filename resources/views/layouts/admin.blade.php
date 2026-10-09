<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>
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

    {{-- Sidebar --}}
    <aside class="bc-sidebar" id="bcSidebar">
        <div class="bc-brand">
            <x-logo imgClass="bc-logo-img-lg" />
            <!-- <div class="bc-brand-text">
                <strong>BridgeCare</strong>
                <small>Infra Authority</small>
            </div> -->
        </div>

        <nav class="bc-sidebar-nav">
            @if (auth()->user()->isAdmin())
                <div class="bc-nav-label">Dashboard</div>
                <a href="{{ route('admin.dashboard') }}" class="bc-nav-link @if(request()->routeIs('admin.dashboard')) active @endif">
                    <i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span>
                </a>

                <div class="bc-nav-label">Infrastructure</div>
                @php
                    $sidebarTypes = \App\Models\InfrastructureType::active()->orderBy('name')->get();
                    $infraOpen = request()->routeIs('admin.infrastructures.*', 'admin.types.*', 'admin.bridges.*', 'admin.roads.*', 'admin.buildings.*', 'admin.hospitals.*');
                @endphp
                <button type="button" class="bc-nav-link bc-nav-toggle {{ $infraOpen ? 'active' : '' }}"
                        data-bc-submenu="bcInfraSubmenu" aria-expanded="{{ $infraOpen ? 'true' : 'false' }}">
                    <i class="bi bi-diagram-3"></i><span>All Infrastructure</span>
                    <i class="bi bi-chevron-down bc-nav-caret"></i>
                </button>
                <div class="bc-nav-sub {{ $infraOpen ? 'open' : '' }}" id="bcInfraSubmenu">
                    @php
                        $dedicatedRoutes = [
                            'bridge' => 'admin.bridges.*',
                            'road' => 'admin.roads.*',
                            'building' => 'admin.buildings.*',
                            'hospital' => 'admin.hospitals.*',
                        ];
                    @endphp
                    @forelse ($sidebarTypes as $sidebarType)
                        @php
                            $subActive = isset($dedicatedRoutes[$sidebarType->slug])
                                ? request()->routeIs($dedicatedRoutes[$sidebarType->slug])
                                : request()->input('type') === $sidebarType->slug && request()->routeIs('admin.infrastructures.*');
                        @endphp
                        <a href="{{ $sidebarType->adminUrl() }}" class="bc-nav-sublink {{ $subActive ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>{{ $sidebarType->menuLabel() }}</span>
                        </a>
                    @empty
                        <span class="bc-nav-sublink" style="cursor:default;opacity:.6;">No categories yet</span>
                    @endforelse
                    <a href="{{ route('admin.types.index') }}" class="bc-nav-sublink bc-nav-sublink-add {{ request()->routeIs('admin.types.*') ? 'active' : '' }}">
                        <i class="bi bi-plus-circle"></i><span>Add More</span>
                    </a>
                </div>

                <div class="bc-nav-label">Tenders</div>
                <a href="{{ route('admin.tenders.index') }}" class="bc-nav-link @if(request()->routeIs('admin.tenders.*')) active @endif">
                    <i class="bi bi-file-earmark-rupee"></i><span>Tenders &amp; Bids</span>
                </a>
                <a href="{{ route('admin.cancellations.index') }}" class="bc-nav-link @if(request()->routeIs('admin.cancellations.*')) active @endif">
                    <i class="bi bi-x-octagon"></i><span>Cancellation Requests</span>
                    @php $pendingCancellations = \App\Models\CancellationRequest::where('status', 'pending')->count(); @endphp
                    @if ($pendingCancellations > 0) <span class="bc-nav-badge">{{ $pendingCancellations }}</span> @endif
                </a>

                <div class="bc-nav-label">Contractors</div>
                <a href="{{ route('admin.contractors.index') }}" class="bc-nav-link @if(request()->routeIs('admin.contractors.index') && request()->input('status') !== 'pending') active @endif">
                    <i class="bi bi-person-workspace"></i><span>All Contractors</span>
                </a>
                <a href="{{ route('admin.contractors.index', ['status' => 'pending']) }}" class="bc-nav-link @if(request()->routeIs('admin.contractors.index') && request()->input('status') === 'pending') active @endif">
                    <i class="bi bi-hourglass-split"></i><span>Pending Applications</span>
                    @php $pendingApplications = \App\Models\Contractor::where('status', 'pending')->count(); @endphp
                    @if ($pendingApplications > 0) <span class="bc-nav-badge">{{ $pendingApplications }}</span> @endif
                </a>

                <div class="bc-nav-label">Maintenance</div>
                <a href="{{ route('admin.maintenance.index') }}" class="bc-nav-link @if(request()->routeIs('admin.maintenance.index', 'admin.maintenance.create', 'admin.maintenance.store')) active @endif">
                    <i class="bi bi-wrench-adjustable-circle"></i><span>Maintenance Requests</span>
                    @php $pendingCount = \App\Models\MaintenanceRequest::whereIn('status', ['pending', 'under_review'])->count(); @endphp
                    @if ($pendingCount > 0) <span class="bc-nav-badge">{{ $pendingCount }}</span> @endif
                </a>
                <a href="{{ route('admin.maintenance.index', ['status' => 'in_progress']) }}" class="bc-nav-link @if(request()->input('status') === 'in_progress' && request()->routeIs('admin.maintenance.*')) active @endif">
                    <i class="bi bi-activity"></i><span>Assigned Tasks</span>
                </a>
                <a href="{{ route('admin.maintenance.history') }}" class="bc-nav-link @if(request()->routeIs('admin.maintenance.history')) active @endif">
                    <i class="bi bi-clock-history"></i><span>Maintenance History</span>
                </a>

                <div class="bc-nav-label">Contacts</div>
                <a href="{{ route('admin.contacts.index') }}" class="bc-nav-link @if(request()->routeIs('admin.contacts.*')) active @endif">
                    <i class="bi bi-person-lines-fill"></i><span>Contact Directory</span>
                </a>

                <div class="bc-nav-label">Reports</div>
                <a href="{{ route('admin.reports.infrastructure') }}" class="bc-nav-link @if(request()->routeIs('admin.reports.infrastructure')) active @endif">
                    <i class="bi bi-bar-chart-line"></i><span>Infrastructure Reports</span>
                </a>
                <a href="{{ route('admin.reports.maintenance') }}" class="bc-nav-link @if(request()->routeIs('admin.reports.maintenance')) active @endif">
                    <i class="bi bi-clipboard-data"></i><span>Maintenance Reports</span>
                </a>
                <a href="{{ route('admin.reports.cost') }}" class="bc-nav-link @if(request()->routeIs('admin.reports.cost')) active @endif">
                    <i class="bi bi-currency-rupee"></i><span>Cost Reports</span>
                </a>

                <div class="bc-nav-label">Users</div>
                <a href="{{ route('admin.users.index') }}" class="bc-nav-link @if(request()->routeIs('admin.users.index')) active @endif">
                    <i class="bi bi-people"></i><span>All Users</span>
                </a>
                <a href="{{ route('admin.users.admins') }}" class="bc-nav-link @if(request()->routeIs('admin.users.admins')) active @endif">
                    <i class="bi bi-shield-lock"></i><span>Admins</span>
                </a>
                <a href="{{ route('admin.users.staff') }}" class="bc-nav-link @if(request()->routeIs('admin.users.staff')) active @endif">
                    <i class="bi bi-tools"></i><span>Maintenance Staff</span>
                </a>
                <div class="bc-nav-label">System</div>
                <a href="{{ route('admin.settings.index') }}" class="bc-nav-link @if(request()->routeIs('admin.settings.*')) active @endif">
                    <i class="bi bi-gear"></i><span>Settings</span>
                </a>
                <a href="{{ route('admin.activity-logs.index') }}" class="bc-nav-link @if(request()->routeIs('admin.activity-logs.*')) active @endif">
                    <i class="bi bi-journal-text"></i><span>Activity Logs</span>
                </a>
            @elseif (auth()->user()->isContractor())
                <div class="bc-nav-label">My Workspace</div>
                <a href="{{ route('contractor.dashboard') }}" class="bc-nav-link @if(request()->routeIs('contractor.dashboard')) active @endif">
                    <i class="bi bi-grid-1x2"></i><span>Dashboard</span>
                </a>
                <a href="{{ route('contractor.tenders.index') }}" class="bc-nav-link @if(request()->routeIs('contractor.tenders.*')) active @endif">
                    <i class="bi bi-file-earmark-rupee"></i><span>Available Tenders</span>
                </a>
                <a href="{{ route('contractor.bids.index') }}" class="bc-nav-link @if(request()->routeIs('contractor.bids.*')) active @endif">
                    <i class="bi bi-cash-coin"></i><span>My Bids</span>
                </a>
                <a href="{{ route('contractor.cancellations.index') }}" class="bc-nav-link @if(request()->routeIs('contractor.cancellations.*')) active @endif">
                    <i class="bi bi-x-octagon"></i><span>Cancellation Requests</span>
                </a>
                <a href="{{ route('contractor.profile.show') }}" class="bc-nav-link @if(request()->routeIs('contractor.profile.*')) active @endif">
                    <i class="bi bi-person-badge"></i><span>My Profile</span>
                </a>
                <div class="bc-nav-label">Public</div>
                <a href="{{ route('public.home') }}" class="bc-nav-link">
                    <i class="bi bi-globe2"></i><span>Public Website</span>
                </a>
            @else
                <div class="bc-nav-label">My Workspace</div>
                <a href="{{ route('staff.tasks.index') }}" class="bc-nav-link @if(request()->routeIs('staff.*')) active @endif">
                    <i class="bi bi-list-check"></i><span>My Tasks</span>
                </a>
                <div class="bc-nav-label">Public</div>
                <a href="{{ route('public.home') }}" class="bc-nav-link">
                    <i class="bi bi-globe2"></i><span>Public Website</span>
                </a>
            @endif
        </nav>

        <div class="bc-sidebar-footer">
            <div class="bc-user-chip">
                @if (auth()->user()->profile_image)
                    <img src="{{ asset('storage/' . auth()->user()->profile_image) }}" alt="{{ auth()->user()->name }}">
                @else
                    <span class="avatar-fallback">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                @endif
                <div>
                    <strong>{{ auth()->user()->name }}</strong>
                    <small>{{ ucfirst(auth()->user()->role) }}</small>
                </div>
            </div>
        </div>
    </aside>

    <div class="bc-sidebar-mobile-overlay"></div>

    {{-- Main --}}
    <div class="bc-main">
        {{-- Topbar --}}
        <header class="bc-topbar">
            <button class="bc-sidebar-toggle" id="bcSidebarToggle" aria-label="Toggle sidebar">
                <i class="bi bi-list"></i>
            </button>

            <div class="bc-search">
                <i class="bi bi-search"></i>
                <input type="text" id="bcGlobalSearch" placeholder="Search bridges, roads, buildings, hospitals, requests, contacts..." autocomplete="off">
                <div class="bc-search-results" id="bcSearchResults"></div>
            </div>

            <div class="bc-topbar-actions">
                {{-- Notifications --}}
                <div class="position-relative">
                    <button class="bc-icon-btn" data-bc-dropdown="bcNotificationDropdown" aria-label="Notifications">
                        <i class="bi bi-bell"></i>
                        @php
                            $unreadCount = \App\Models\AppNotification::forUser(auth()->id())->unread()->count();
                        @endphp
                        @if ($unreadCount > 0) <span class="bc-dot"></span> @endif
                    </button>
                    <div class="bc-dropdown" id="bcNotificationDropdown">
                        <div class="bc-dropdown-header">
                            <strong>Notifications</strong>
                            <span class="bc-badge badge-blue">{{ $unreadCount }} unread</span>
                        </div>
                        <div class="bc-dropdown-body">
                            @php
                                $notifications = \App\Models\AppNotification::forUser(auth()->id())->latest()->take(8)->get();
                            @endphp
                            @forelse ($notifications as $notification)
                                <a href="{{ route('notifications.read', $notification) }}" class="bc-notification {{ $notification->read ? '' : 'unread' }}">
                                    @if ($notification->type === 'maintenance_request')
                                        <span class="bc-notification-icon ic-orange"><i class="bi bi-wrench-adjustable"></i></span>
                                    @elseif ($notification->type === 'task_assigned')
                                        <span class="bc-notification-icon ic-blue"><i class="bi bi-person-check"></i></span>
                                    @elseif ($notification->type === 'maintenance_completed')
                                        <span class="bc-notification-icon ic-green"><i class="bi bi-check2-circle"></i></span>
                                    @elseif ($notification->type === 'critical_alert')
                                        <span class="bc-notification-icon ic-red"><i class="bi bi-exclamation-triangle"></i></span>
                                    @elseif ($notification->type === 'tender_published')
                                        <span class="bc-notification-icon ic-blue"><i class="bi bi-file-earmark-medal"></i></span>
                                    @elseif ($notification->type === 'bid_submitted')
                                        <span class="bc-notification-icon ic-green"><i class="bi bi-cash-coin"></i></span>
                                    @elseif ($notification->type === 'tender_awarded')
                                        <span class="bc-notification-icon ic-purple"><i class="bi bi-trophy"></i></span>
                                    @elseif ($notification->type === 'contractor_applied')
                                        <span class="bc-notification-icon ic-orange"><i class="bi bi-person-badge"></i></span>
                                    @elseif ($notification->type === 'contractor_approved')
                                        <span class="bc-notification-icon ic-green"><i class="bi bi-person-check"></i></span>
                                    @elseif ($notification->type === 'contractor_rejected')
                                        <span class="bc-notification-icon ic-red"><i class="bi bi-person-x"></i></span>
                                    @elseif (str_starts_with((string) $notification->type, 'cancellation'))
                                        <span class="bc-notification-icon ic-red"><i class="bi bi-x-octagon"></i></span>
                                    @else
                                        <span class="bc-notification-icon ic-purple"><i class="bi bi-info-circle"></i></span>
                                    @endif
                                    <div>
                                        <strong>{{ $notification->title }}</strong>
                                        <p>{{ Str::limit($notification->message, 90) }}</p>
                                        <time>{{ $notification->created_at->diffForHumans() }}</time>
                                    </div>
                                </a>
                            @empty
                                <div style="padding:30px;text-align:center;color:#94a3b8;font-size:13px;">
                                    <i class="bi bi-bell-slash" style="font-size:26px;display:block;margin-bottom:8px;"></i>
                                    No notifications yet
                                </div>
                            @endforelse
                            <div style="padding:10px 12px;border-top:1px solid #f1f5f9;margin-top:6px;text-align:center;">
                                <a href="{{ route('notifications.index') }}" style="font-size:13px;font-weight:600;color:var(--bc-blue);text-decoration:none;">
                                    <i class="bi bi-bell me-1"></i> View all notifications
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- User menu --}}
                <div class="position-relative">
                    <div class="bc-user-menu" data-bc-dropdown="bcUserDropdown">
                        @if (auth()->user()->profile_image)
                            <img src="{{ asset('storage/' . auth()->user()->profile_image) }}" alt="">
                        @else
                            <span class="avatar-fallback">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        @endif
                        <div>
                            <strong>{{ auth()->user()->name }}</strong>
                            <small>{{ ucfirst(auth()->user()->role) }}</small>
                        </div>
                        <i class="bi bi-chevron-down" style="font-size:11px;color:#94a3b8;"></i>
                    </div>
                    <div class="bc-dropdown bc-user-dropdown" id="bcUserDropdown">
                        <div class="bc-dropdown-body">
                            <div style="padding:10px 12px;border-bottom:1px solid #f1f5f9;margin-bottom:6px;">
                                <strong style="font-size:13.5px;display:block;">{{ auth()->user()->name }}</strong>
                                <small style="color:#64748b;font-size:12px;">{{ auth()->user()->email }}</small>
                            </div>
                            @if (auth()->user()->isAdmin())
                                <a href="{{ route('admin.settings.index') }}"><i class="bi bi-gear"></i> Settings</a>
                                <a href="{{ route('admin.activity-logs.index') }}"><i class="bi bi-journal-text"></i> Activity Logs</a>
                            @endif
                            <a href="{{ route('notifications.index') }}"><i class="bi bi-bell"></i> Notifications</a>
                            @if (auth()->user()->isContractor())
                                <a href="{{ route('contractor.profile.show') }}"><i class="bi bi-person-badge"></i> My Profile</a>
                            @endif
                            <hr>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="bc-user-dropdown-link" style="display:flex;align-items:center;gap:10px;width:100%;padding:10px 12px;border-radius:10px;border:none;background:none;color:#dc2626;font-size:13.5px;font-weight:500;cursor:pointer;">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

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

        <footer style="padding:18px 28px;border-top:1px solid #e2e8f0;background:#fff;font-size:12.5px;color:#94a3b8;display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;">
            <span>© {{ date('Y') }} {{ config('app.name') }} — Smart Bridge &amp; Infrastructure Management System</span>
            <span>v1.0.0</span>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
