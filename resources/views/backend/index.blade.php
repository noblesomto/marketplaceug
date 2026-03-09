@include('backend.layouts.header')
@include('backend.layouts.nav')

<style>
    /* ── Base card ──────────────────────────────────────────────── */
    .stat-card {
        background: #fff;
        border-radius: 10px;
        padding: 20px 24px;
        margin-bottom: 20px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.07);
        border-top: 4px solid #dee2e6;
        position: relative;
        overflow: hidden;
    }

    /* Colour accents per section */
    .stat-card.users        { border-top-color: #4361ee; }
    .stat-card.adverts      { border-top-color: #e63946; }
    .stat-card.boosts       { border-top-color: #0096c7; }
    .stat-card.payments     { border-top-color: #2d9a4e; }
    .stat-card.shipping     { border-top-color: #e76f51; }
    .stat-card.settlements  { border-top-color: #457b9d; }
    .stat-card.reports      { border-top-color: #6c757d; }
    .stat-card.categories   { border-top-color: #7209b7; }
    .stat-card.blog         { border-top-color: #f4a261; }

    /* ── Stat icon (top-right badge) ────────────────────────────── */
    .stat-icon {
        position: absolute;
        top: 18px;
        right: 20px;
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }
    .stat-icon.users        { background: #eef0fd; color: #4361ee; }
    .stat-icon.adverts      { background: #fdeaeb; color: #e63946; }
    .stat-icon.boosts       { background: #e0f4fb; color: #0096c7; }
    .stat-icon.payments     { background: #e8f5ec; color: #2d9a4e; }
    .stat-icon.shipping     { background: #fdf0ec; color: #e76f51; }
    .stat-icon.settlements  { background: #eaf1f6; color: #457b9d; }
    .stat-icon.reports      { background: #f0f0f0; color: #6c757d; }
    .stat-icon.categories   { background: #f3e8fd; color: #7209b7; }
    .stat-icon.blog         { background: #fef7ec; color: #f4a261; }

    /* ── Stat numbers & labels ──────────────────────────────────── */
    .stat-number {
        font-size: 2.1rem;
        font-weight: 700;
        color: #212529;
        line-height: 1.2;
        margin-bottom: 4px;
        padding-right: 56px; /* clear the icon */
    }

    .stat-label {
        font-size: 0.78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #6c757d;
    }

    /* ── Mini stat grid (trends cards) ─────────────────────────── */
    .mini-stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(90px, 1fr));
        gap: 10px;
        margin-top: 16px;
    }

    /* Trend grids: 2 cols on mobile → 3 on sm → 5 on lg */
    .trend-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        margin-top: 16px;
    }

    @media (min-width: 576px) {
        .trend-grid { grid-template-columns: repeat(3, 1fr); }
    }

    @media (min-width: 992px) {
        .trend-grid { grid-template-columns: repeat(5, 1fr); }
    }

    .mini-stat {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        padding: 12px 8px;
        border-radius: 6px;
        text-align: center;
    }

    .mini-stat-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #212529;
        margin-bottom: 3px;
        line-height: 1.2;
    }

    .mini-stat-value.currency {
        font-size: 1rem;
    }

    .mini-stat-label {
        font-size: 0.7rem;
        font-weight: 500;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    /* ── Section headers ────────────────────────────────────────── */
    .section-header {
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #e9ecef;
    }

    .section-header h2 {
        color: #212529;
        font-weight: 700;
        font-size: 1.2rem;
        margin-bottom: 2px;
    }

    .section-header p {
        color: #6c757d;
        font-size: 0.875rem;
        margin-bottom: 0;
    }

    /* ── Welcome card ───────────────────────────────────────────── */
    .welcome-card {
        background: #1e3a5f;
        color: #fff;
        border-radius: 10px;
        padding: 28px 32px;
        margin-bottom: 28px;
    }

    .welcome-card h1 {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .welcome-card p {
        color: rgba(255,255,255,0.8);
        margin-bottom: 0;
        font-size: 0.95rem;
    }

    .role-badge {
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.25);
        padding: 5px 14px;
        border-radius: 20px;
        display: inline-block;
        margin-top: 12px;
        font-size: 0.82rem;
    }

    /* ── Quick actions ──────────────────────────────────────────── */
    .quick-action-btn {
        border-radius: 8px;
        padding: 13px 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 12px;
        transition: transform 0.2s, box-shadow 0.2s;
        text-decoration: none;
    }

    .quick-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .quick-action-btn i {
        font-size: 1.2rem;
    }

    /* ── Activity feed cards ────────────────────────────────────── */
    .dashboard-card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.07);
        margin-bottom: 20px;
    }

    .activity-item {
        padding: 12px 16px;
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.15s;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .activity-item:hover { background: #f8f9fa; }
    .activity-item:last-child { border-bottom: none; }

    .activity-icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .activity-badge {
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 0.72rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .activity-badge.success    { background: #d1edda; color: #155724; }
    .activity-badge.warning    { background: #fff3cd; color: #856404; }
    .activity-badge.danger     { background: #f8d7da; color: #721c24; }
    .activity-badge.secondary  { background: #e2e3e5; color: #383d41; }
</style>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Dashboard</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/index">Home</a></li>
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="container-fluid">

            {{-- Welcome Banner --}}
            <div class="welcome-card">
                <h1>Welcome back, {{ $admin->username }}!</h1>
                <p>Here's what's happening with your marketplace today.</p>
                <div class="role-badge">
                    <i class="bi bi-shield-check me-1"></i>
                    {{ $admin->getRoleNames()->implode(', ') ?: 'No Role Assigned' }}
                </div>
            </div>

            {{-- Quick Actions --}}
            @if(count($quickActions) > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="section-header">
                        <h2><i class="bi bi-lightning-charge me-2"></i>Quick Actions</h2>
                        <p>Common tasks you can perform</p>
                    </div>
                </div>
                @foreach($quickActions as $action)
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <a href="{{ $action['url'] }}" class="btn btn-{{ $action['color'] }} quick-action-btn w-100">
                        <i class="bi {{ $action['icon'] }}"></i>
                        <span>{{ $action['title'] }}</span>
                    </a>
                </div>
                @endforeach
            </div>
            @endif

            {{-- ── User Management ─────────────────────────────── --}}
            @if(isset($stats['users']))
            <div class="row mb-4">
                <div class="col-12">
                    <div class="section-header">
                        <h2><i class="bi bi-people me-2"></i>User Management</h2>
                        <p>Overview of registered users</p>
                    </div>
                </div>

                {{-- Total --}}
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card users">
                        <div class="stat-icon users"><i class="bi bi-people"></i></div>
                        <div class="stat-number">{{ number_format($stats['users']['total']) }}</div>
                        <div class="stat-label">Total Users</div>
                        <div class="mini-stat-grid">
                            <div class="mini-stat">
                                <div class="mini-stat-value">{{ number_format($stats['users']['active']) }}</div>
                                <div class="mini-stat-label">Active</div>
                            </div>
                            <div class="mini-stat">
                                <div class="mini-stat-value">{{ number_format($stats['users']['verified']) }}</div>
                                <div class="mini-stat-label">Verified</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Registration Trends --}}
                <div class="col-lg-9 col-md-12">
                    <div class="stat-card users">
                        <div class="stat-label"><i class="bi bi-person-plus me-1"></i>Registration Trends</div>
                        <div class="trend-grid">
                            <div class="mini-stat">
                                <div class="mini-stat-value">{{ number_format($stats['users']['new_today']) }}</div>
                                <div class="mini-stat-label">Today</div>
                            </div>
                            <div class="mini-stat">
                                <div class="mini-stat-value">{{ number_format($stats['users']['new_this_week']) }}</div>
                                <div class="mini-stat-label">This Week</div>
                            </div>
                            <div class="mini-stat">
                                <div class="mini-stat-value">{{ number_format($stats['users']['new_last_week']) }}</div>
                                <div class="mini-stat-label">Last Week</div>
                            </div>
                            <div class="mini-stat">
                                <div class="mini-stat-value">{{ number_format($stats['users']['new_this_month']) }}</div>
                                <div class="mini-stat-label">This Month</div>
                            </div>
                            <div class="mini-stat">
                                <div class="mini-stat-value">{{ number_format($stats['users']['new_last_month']) }}</div>
                                <div class="mini-stat-label">Last Month</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- ── Advertisement Management ─────────────────────── --}}
            @if(isset($stats['adverts']))
            <div class="row mb-4">
                <div class="col-12">
                    <div class="section-header">
                        <h2><i class="bi bi-badge-ad me-2"></i>Advertisement Management</h2>
                        <p>Active listings and performance</p>
                    </div>
                </div>

                {{-- Total --}}
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card adverts">
                        <div class="stat-icon adverts"><i class="bi bi-badge-ad"></i></div>
                        <div class="stat-number">{{ number_format($stats['adverts']['total']) }}</div>
                        <div class="stat-label">Total Adverts</div>
                        <div class="mini-stat-grid">
                            <div class="mini-stat">
                                <div class="mini-stat-value">{{ number_format($stats['adverts']['active']) }}</div>
                                <div class="mini-stat-label">Active</div>
                            </div>
                            <div class="mini-stat">
                                <div class="mini-stat-value">{{ number_format($stats['adverts']['sold']) }}</div>
                                <div class="mini-stat-label">Sold</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pending --}}
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card adverts">
                        <div class="stat-icon adverts"><i class="bi bi-hourglass-split"></i></div>
                        <div class="stat-number">{{ number_format($stats['adverts']['pending']) }}</div>
                        <div class="stat-label">Pending Approval</div>
                    </div>
                </div>

                {{-- Listing Trends --}}
                <div class="col-lg-6 col-md-12">
                    <div class="stat-card adverts">
                        <div class="stat-label"><i class="bi bi-graph-up me-1"></i>Listing Trends</div>
                        <div class="trend-grid">
                            <div class="mini-stat">
                                <div class="mini-stat-value">{{ number_format($stats['adverts']['new_today']) }}</div>
                                <div class="mini-stat-label">Today</div>
                            </div>
                            <div class="mini-stat">
                                <div class="mini-stat-value">{{ number_format($stats['adverts']['new_this_week']) }}</div>
                                <div class="mini-stat-label">This Week</div>
                            </div>
                            <div class="mini-stat">
                                <div class="mini-stat-value">{{ number_format($stats['adverts']['new_last_week']) }}</div>
                                <div class="mini-stat-label">Last Week</div>
                            </div>
                            <div class="mini-stat">
                                <div class="mini-stat-value">{{ number_format($stats['adverts']['new_this_month']) }}</div>
                                <div class="mini-stat-label">This Month</div>
                            </div>
                            <div class="mini-stat">
                                <div class="mini-stat-value">{{ number_format($stats['adverts']['new_last_month']) }}</div>
                                <div class="mini-stat-label">Last Month</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- ── Boost Management ─────────────────────────────── --}}
            @if(isset($stats['boosts']))
            <div class="row mb-4">
                <div class="col-12">
                    <div class="section-header">
                        <h2><i class="bi bi-rocket-takeoff me-2"></i>Boost Management</h2>
                        <p>Promoted advertisements performance</p>
                    </div>
                </div>

                {{-- Active --}}
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card boosts">
                        <div class="stat-icon boosts"><i class="bi bi-rocket-takeoff"></i></div>
                        <div class="stat-number">{{ number_format($stats['boosts']['active']) }}</div>
                        <div class="stat-label">Active Boosts</div>
                    </div>
                </div>

                {{-- Unpaid --}}
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card boosts">
                        <div class="stat-icon boosts"><i class="bi bi-exclamation-triangle"></i></div>
                        <div class="stat-number">{{ number_format($stats['boosts']['unpaid']) }}</div>
                        <div class="stat-label">Unpaid Boosts</div>
                    </div>
                </div>

                {{-- Revenue Trends --}}
                <div class="col-lg-6 col-md-12">
                    <div class="stat-card boosts">
                        <div class="stat-label"><i class="bi bi-cash-stack me-1"></i>Boost Revenue</div>
                        <div class="trend-grid">
                            <div class="mini-stat">
                                <div class="mini-stat-value currency">₦{{ number_format($stats['boosts']['revenue_today'], 0) }}</div>
                                <div class="mini-stat-label">Today</div>
                            </div>
                            <div class="mini-stat">
                                <div class="mini-stat-value currency">₦{{ number_format($stats['boosts']['revenue_this_week'], 0) }}</div>
                                <div class="mini-stat-label">This Week</div>
                            </div>
                            <div class="mini-stat">
                                <div class="mini-stat-value currency">₦{{ number_format($stats['boosts']['revenue_last_week'], 0) }}</div>
                                <div class="mini-stat-label">Last Week</div>
                            </div>
                            <div class="mini-stat">
                                <div class="mini-stat-value currency">₦{{ number_format($stats['boosts']['revenue_this_month'], 0) }}</div>
                                <div class="mini-stat-label">This Month</div>
                            </div>
                            <div class="mini-stat">
                                <div class="mini-stat-value currency">₦{{ number_format($stats['boosts']['revenue_last_month'], 0) }}</div>
                                <div class="mini-stat-label">Last Month</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- ── Payments & Settlements ───────────────────────── --}}
            @if(isset($stats['payments']) || isset($stats['settlements']))
            <div class="row mb-4">
                <div class="col-12">
                    <div class="section-header">
                        <h2><i class="bi bi-credit-card me-2"></i>Payments & Settlements</h2>
                        <p>Financial transactions overview</p>
                    </div>
                </div>

                @if(isset($stats['payments']))
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card payments">
                        <div class="stat-icon payments"><i class="bi bi-currency-dollar"></i></div>
                        <div class="stat-number" style="font-size:1.6rem;">₦{{ number_format($stats['payments']['total_revenue'], 0) }}</div>
                        <div class="stat-label">Total Revenue</div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="stat-card payments">
                        <div class="stat-icon payments"><i class="bi bi-calendar-month"></i></div>
                        <div class="stat-number" style="font-size:1.6rem;">₦{{ number_format($stats['payments']['revenue_this_month'], 0) }}</div>
                        <div class="stat-label">Revenue This Month</div>
                    </div>
                </div>
                @endif

                @if(isset($stats['settlements']))
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card settlements">
                        <div class="stat-icon settlements"><i class="bi bi-cash-coin"></i></div>
                        <div class="stat-number">{{ number_format($stats['settlements']['pending']) }}</div>
                        <div class="stat-label">Pending Settlements</div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="stat-card settlements">
                        <div class="stat-icon settlements"><i class="bi bi-wallet2"></i></div>
                        <div class="stat-number" style="font-size:1.6rem;">₦{{ number_format($stats['settlements']['pending_amount'], 0) }}</div>
                        <div class="stat-label">Pending Amount</div>
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- ── Shipping Management ──────────────────────────── --}}
            @if(isset($stats['shipping']))
            <div class="row mb-4">
                <div class="col-12">
                    <div class="section-header">
                        <h2><i class="bi bi-truck me-2"></i>Shipping Management</h2>
                        <p>Delivery status overview</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="stat-card shipping">
                        <div class="stat-icon shipping"><i class="bi bi-hourglass-split"></i></div>
                        <div class="stat-number">{{ number_format($stats['shipping']['pending']) }}</div>
                        <div class="stat-label">Pending Shipping</div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="stat-card shipping">
                        <div class="stat-icon shipping"><i class="bi bi-truck-front"></i></div>
                        <div class="stat-number">{{ number_format($stats['shipping']['shipped']) }}</div>
                        <div class="stat-label">In Transit</div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="stat-card shipping">
                        <div class="stat-icon shipping"><i class="bi bi-check-circle"></i></div>
                        <div class="stat-number">{{ number_format($stats['shipping']['delivered']) }}</div>
                        <div class="stat-label">Delivered</div>
                    </div>
                </div>
            </div>
            @endif

            {{-- ── Reports / Categories / Blog ──────────────────── --}}
            @if(isset($stats['reports']) || isset($stats['categories']) || isset($stats['blog']))
            <div class="row mb-4">
                @if(isset($stats['reports']))
                <div class="col-lg-4 col-md-6">
                    <div class="stat-card reports">
                        <div class="stat-icon reports"><i class="bi bi-flag"></i></div>
                        <div class="stat-number">{{ number_format($stats['reports']['pending']) }}</div>
                        <div class="stat-label">Pending Reports</div>
                        <div class="mini-stat-grid">
                            <div class="mini-stat">
                                <div class="mini-stat-value">{{ number_format($stats['reports']['total']) }}</div>
                                <div class="mini-stat-label">Total</div>
                            </div>
                            <div class="mini-stat">
                                <div class="mini-stat-value">{{ number_format($stats['reports']['new_today']) }}</div>
                                <div class="mini-stat-label">New Today</div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if(isset($stats['categories']))
                <div class="col-lg-4 col-md-6">
                    <div class="stat-card categories">
                        <div class="stat-icon categories"><i class="bi bi-grid"></i></div>
                        <div class="stat-number">{{ number_format($stats['categories']['total_categories']) }}</div>
                        <div class="stat-label">Categories</div>
                        <div class="mini-stat-grid">
                            <div class="mini-stat">
                                <div class="mini-stat-value">{{ number_format($stats['categories']['total_subcategories']) }}</div>
                                <div class="mini-stat-label">Subcategories</div>
                            </div>
                            <div class="mini-stat">
                                <div class="mini-stat-value">{{ number_format($stats['categories']['total_brands']) }}</div>
                                <div class="mini-stat-label">Brands</div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if(isset($stats['blog']))
                <div class="col-lg-4 col-md-6">
                    <div class="stat-card blog">
                        <div class="stat-icon blog"><i class="bi bi-newspaper"></i></div>
                        <div class="stat-number">{{ number_format($stats['blog']['total_posts']) }}</div>
                        <div class="stat-label">Blog Posts</div>
                        <div class="mini-stat-grid">
                            <div class="mini-stat">
                                <div class="mini-stat-value">{{ number_format($stats['blog']['published']) }}</div>
                                <div class="mini-stat-label">Published</div>
                            </div>
                            <div class="mini-stat">
                                <div class="mini-stat-value">{{ number_format($stats['blog']['draft']) }}</div>
                                <div class="mini-stat-label">Drafts</div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- ── Recent Activity ──────────────────────────────── --}}
            @if(count($recentActivities) > 0)
            <div class="row">
                @if(isset($recentActivities['adverts']) && count($recentActivities['adverts']) > 0)
                <div class="col-lg-6">
                    <div class="card dashboard-card">
                        <div class="card-header bg-white border-bottom d-flex align-items-center">
                            <i class="bi bi-badge-ad me-2 text-danger"></i>
                            <h6 class="mb-0 fw-semibold">Recent Adverts</h6>
                        </div>
                        <div class="card-body p-0">
                            @foreach($recentActivities['adverts'] as $advert)
                            <div class="activity-item">
                                <div class="activity-icon bg-danger bg-opacity-10 text-danger">
                                    <i class="bi bi-badge-ad"></i>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="fw-semibold text-truncate" style="font-size:0.9rem;">{{ Str::limit($advert->title, 45) }}</div>
                                    <small class="text-muted">
                                        by {{ $advert->user->name ?? 'Unknown' }} &bull;
                                        {{ $advert->created_at->diffForHumans() }}
                                    </small>
                                </div>
                                @php
                                    $statusClass = match($advert->ad_status) {
                                        'active'   => 'success',
                                        'pending'  => 'warning',
                                        'disabled' => 'danger',
                                        default    => 'secondary',
                                    };
                                @endphp
                                <span class="activity-badge {{ $statusClass }}">{{ ucfirst($advert->ad_status) }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                @if(isset($recentActivities['users']) && count($recentActivities['users']) > 0)
                <div class="col-lg-6">
                    <div class="card dashboard-card">
                        <div class="card-header bg-white border-bottom d-flex align-items-center">
                            <i class="bi bi-people me-2 text-primary"></i>
                            <h6 class="mb-0 fw-semibold">Recent Users</h6>
                        </div>
                        <div class="card-body p-0">
                            @foreach($recentActivities['users'] as $user)
                            <div class="activity-item">
                                <div class="activity-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="fw-semibold text-truncate" style="font-size:0.9rem;">{{ $user->name }}</div>
                                    <small class="text-muted text-truncate d-block">
                                        {{ $user->email }} &bull; {{ $user->created_at->diffForHumans() }}
                                    </small>
                                </div>
                                <span class="activity-badge {{ $user->verified ? 'success' : 'secondary' }}">
                                    {{ $user->verified ? 'Verified' : 'Unverified' }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                @if(isset($recentActivities['reports']) && count($recentActivities['reports']) > 0)
                <div class="col-12">
                    <div class="card dashboard-card">
                        <div class="card-header bg-white border-bottom d-flex align-items-center">
                            <i class="bi bi-flag me-2 text-warning"></i>
                            <h6 class="mb-0 fw-semibold">Recent Reports</h6>
                        </div>
                        <div class="card-body p-0">
                            @foreach($recentActivities['reports'] as $report)
                            <div class="activity-item">
                                <div class="activity-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="bi bi-flag"></i>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="fw-semibold text-truncate" style="font-size:0.9rem;">{{ Str::limit($report->reason ?? 'Report', 55) }}</div>
                                    <small class="text-muted">
                                        by {{ $report->user->name ?? 'Unknown' }} &bull;
                                        Ad: {{ Str::limit($report->adverts->title ?? 'N/A', 30) }} &bull;
                                        {{ $report->created_at->diffForHumans() }}
                                    </small>
                                </div>
                                <span class="activity-badge {{ $report->status === 'resolved' ? 'success' : 'danger' }}">
                                    {{ ucfirst($report->status) }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- No Permission Message --}}
            @if(count($stats) === 0)
            <div class="row">
                <div class="col-12">
                    <div class="card dashboard-card text-center py-5">
                        <div class="card-body">
                            <i class="bi bi-shield-exclamation text-muted" style="font-size:3.5rem;"></i>
                            <h4 class="mt-3 fw-bold">Limited Access</h4>
                            <p class="text-muted mb-0">
                                You don't have permission to view dashboard statistics.<br>
                                Contact your administrator to request access.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </section>
</main>

@include('backend.layouts.footer')
