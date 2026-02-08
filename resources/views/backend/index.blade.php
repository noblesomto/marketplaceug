@include('backend.layouts.header')
@include('backend.layouts.nav')

<style>
    .dashboard-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        margin-bottom: 20px;
    }

    .dashboard-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.12);
    }

    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    }

    .stat-card.users { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .stat-card.adverts { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
    .stat-card.boosts { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .stat-card.payments { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
    .stat-card.shipping { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
    .stat-card.settlements { background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); }
    .stat-card.reports { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333; }
    .stat-card.categories { background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); color: #333; }
    .stat-card.blog { background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); color: #333; }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 0.9rem;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-icon {
        font-size: 3rem;
        opacity: 0.3;
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
    }

    .quick-action-btn {
        border: none;
        border-radius: 10px;
        padding: 15px 20px;
        text-align: center;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        font-weight: 600;
        margin-bottom: 15px;
    }

    .quick-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }

    .quick-action-btn i {
        font-size: 1.5rem;
    }

    .section-header {
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
    }

    .section-header h2 {
        color: #2c3e50;
        font-weight: 700;
        font-size: 1.5rem;
        margin-bottom: 5px;
    }

    .section-header p {
        color: #6c757d;
        margin-bottom: 0;
    }

    .mini-stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }

    .mini-stat {
        background: rgba(255,255,255,0.2);
        padding: 15px;
        border-radius: 8px;
        text-align: center;
    }

    .mini-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .mini-stat-label {
        font-size: 0.75rem;
        opacity: 0.9;
    }

    .activity-item {
        padding: 15px;
        border-bottom: 1px solid #e9ecef;
        transition: background 0.2s ease;
    }

    .activity-item:hover {
        background: #f8f9fa;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-right: 15px;
    }

    .badge-custom {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .welcome-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 30px;
    }

    .welcome-card h1 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .welcome-card p {
        opacity: 0.9;
        margin-bottom: 0;
    }

    .role-badge {
        background: rgba(255,255,255,0.2);
        padding: 8px 15px;
        border-radius: 20px;
        display: inline-block;
        margin-top: 10px;
    }
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
            {{-- Welcome Card --}}
            <div class="welcome-card">
                <h1>Welcome back, {{ $admin->username }}!</h1>
                <p>Here's what's happening with your marketplace today.</p>
                <div class="role-badge">
                    <i class="bi bi-shield-check me-2"></i>
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

            {{-- User Stats --}}
            @if(isset($stats['users']))
            <div class="row mb-4">
                <div class="col-12">
                    <div class="section-header">
                        <h2><i class="bi bi-people me-2"></i>User Management</h2>
                        <p>Overview of registered users</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="stat-card users position-relative">
                        <div class="stat-number">{{ number_format($stats['users']['total']) }}</div>
                        <div class="stat-label">Total Users</div>
                        <i class="bi bi-people stat-icon"></i>

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

                <div class="col-lg-3 col-md-6">
                    <div class="stat-card users position-relative">
                        <div class="stat-number">{{ number_format($stats['users']['new_today']) }}</div>
                        <div class="stat-label">New Users Today</div>
                        <i class="bi bi-person-plus stat-icon"></i>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="stat-card users position-relative">
                        <div class="stat-number">{{ number_format($stats['users']['new_this_week']) }}</div>
                        <div class="stat-label">This Week</div>
                        <i class="bi bi-calendar-week stat-icon"></i>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="stat-card users position-relative">
                        <div class="stat-number">{{ number_format($stats['users']['new_this_month']) }}</div>
                        <div class="stat-label">This Month</div>
                        <i class="bi bi-calendar-month stat-icon"></i>
                    </div>
                </div>
            </div>
            @endif

            {{-- Advert Stats --}}
            @if(isset($stats['adverts']))
            <div class="row mb-4">
                <div class="col-12">
                    <div class="section-header">
                        <h2><i class="bi bi-badge-ad me-2"></i>Advertisement Management</h2>
                        <p>Active listings and performance</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="stat-card adverts position-relative">
                        <div class="stat-number">{{ number_format($stats['adverts']['total']) }}</div>
                        <div class="stat-label">Total Adverts</div>
                        <i class="bi bi-badge-ad stat-icon"></i>

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

                <div class="col-lg-3 col-md-6">
                    <div class="stat-card adverts position-relative">
                        <div class="stat-number">{{ number_format($stats['adverts']['pending']) }}</div>
                        <div class="stat-label">Pending Approval</div>
                        <i class="bi bi-hourglass-split stat-icon"></i>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="stat-card adverts position-relative">
                        <div class="stat-number">{{ number_format($stats['adverts']['new_today']) }}</div>
                        <div class="stat-label">New Today</div>
                        <i class="bi bi-calendar-check stat-icon"></i>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="stat-card adverts position-relative">
                        <div class="stat-number">{{ number_format($stats['adverts']['new_this_month']) }}</div>
                        <div class="stat-label">This Month</div>
                        <i class="bi bi-graph-up stat-icon"></i>
                    </div>
                </div>
            </div>
            @endif

            {{-- Boost Stats --}}
            @if(isset($stats['boosts']))
            <div class="row mb-4">
                <div class="col-12">
                    <div class="section-header">
                        <h2><i class="bi bi-rocket-takeoff me-2"></i>Boost Management</h2>
                        <p>Promoted advertisements performance</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="stat-card boosts position-relative">
                        <div class="stat-number">{{ number_format($stats['boosts']['active']) }}</div>
                        <div class="stat-label">Active Boosts</div>
                        <i class="bi bi-rocket-takeoff stat-icon"></i>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="stat-card boosts position-relative">
                        <div class="stat-number">{{ number_format($stats['boosts']['unpaid']) }}</div>
                        <div class="stat-label">Unpaid Boosts</div>
                        <i class="bi bi-exclamation-triangle stat-icon"></i>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="stat-card boosts position-relative">
                        <div class="stat-number">₦{{ number_format($stats['boosts']['revenue_today'], 2) }}</div>
                        <div class="stat-label">Revenue Today</div>
                        <i class="bi bi-cash stat-icon"></i>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="stat-card boosts position-relative">
                        <div class="stat-number">₦{{ number_format($stats['boosts']['revenue_this_month'], 2) }}</div>
                        <div class="stat-label">Revenue This Month</div>
                        <i class="bi bi-graph-up-arrow stat-icon"></i>
                    </div>
                </div>
            </div>
            @endif

            {{-- Payment & Settlement Stats --}}
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
                    <div class="stat-card payments position-relative">
                        <div class="stat-number">₦{{ number_format($stats['payments']['total_revenue'], 2) }}</div>
                        <div class="stat-label">Total Revenue</div>
                        <i class="bi bi-currency-dollar stat-icon"></i>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="stat-card payments position-relative">
                        <div class="stat-number">₦{{ number_format($stats['payments']['revenue_this_month'], 2) }}</div>
                        <div class="stat-label">This Month</div>
                        <i class="bi bi-calendar-month stat-icon"></i>
                    </div>
                </div>
                @endif

                @if(isset($stats['settlements']))
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card settlements position-relative">
                        <div class="stat-number">{{ number_format($stats['settlements']['pending']) }}</div>
                        <div class="stat-label">Pending Settlements</div>
                        <i class="bi bi-cash-coin stat-icon"></i>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="stat-card settlements position-relative">
                        <div class="stat-number">₦{{ number_format($stats['settlements']['pending_amount'], 2) }}</div>
                        <div class="stat-label">Pending Amount</div>
                        <i class="bi bi-wallet2 stat-icon"></i>
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- Shipping Stats --}}
            @if(isset($stats['shipping']))
            <div class="row mb-4">
                <div class="col-12">
                    <div class="section-header">
                        <h2><i class="bi bi-truck me-2"></i>Shipping Management</h2>
                        <p>Delivery status overview</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="stat-card shipping position-relative">
                        <div class="stat-number">{{ number_format($stats['shipping']['pending']) }}</div>
                        <div class="stat-label">Pending Shipping</div>
                        <i class="bi bi-hourglass-split stat-icon"></i>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="stat-card shipping position-relative">
                        <div class="stat-number">{{ number_format($stats['shipping']['shipped']) }}</div>
                        <div class="stat-label">In Transit</div>
                        <i class="bi bi-truck-front stat-icon"></i>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="stat-card shipping position-relative">
                        <div class="stat-number">{{ number_format($stats['shipping']['delivered']) }}</div>
                        <div class="stat-label">Delivered</div>
                        <i class="bi bi-check-circle stat-icon"></i>
                    </div>
                </div>
            </div>
            @endif

            {{-- Other Stats Row --}}
            <div class="row mb-4">
                {{-- Report Stats --}}
                @if(isset($stats['reports']))
                <div class="col-lg-4 col-md-6">
                    <div class="stat-card reports position-relative">
                        <div class="stat-number">{{ number_format($stats['reports']['pending']) }}</div>
                        <div class="stat-label">Pending Reports</div>
                        <i class="bi bi-flag stat-icon"></i>

                        <div class="mini-stat-grid mt-3">
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

                {{-- Category Stats --}}
                @if(isset($stats['categories']))
                <div class="col-lg-4 col-md-6">
                    <div class="stat-card categories position-relative">
                        <div class="stat-number">{{ number_format($stats['categories']['total_categories']) }}</div>
                        <div class="stat-label">Categories</div>
                        <i class="bi bi-grid stat-icon"></i>

                        <div class="mini-stat-grid mt-3">
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

                {{-- Blog Stats --}}
                @if(isset($stats['blog']))
                <div class="col-lg-4 col-md-6">
                    <div class="stat-card blog position-relative">
                        <div class="stat-number">{{ number_format($stats['blog']['total_posts']) }}</div>
                        <div class="stat-label">Blog Posts</div>
                        <i class="bi bi-newspaper stat-icon"></i>

                        <div class="mini-stat-grid mt-3">
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

            {{-- Recent Activities --}}
            @if(count($recentActivities) > 0)
            <div class="row">
                {{-- Recent Adverts --}}
                @if(isset($recentActivities['adverts']) && count($recentActivities['adverts']) > 0)
                <div class="col-lg-6">
                    <div class="card dashboard-card">
                        <div class="card-header bg-transparent border-bottom">
                            <h5 class="mb-0"><i class="bi bi-badge-ad me-2"></i>Recent Adverts</h5>
                        </div>
                        <div class="card-body p-0">
                            @foreach($recentActivities['adverts'] as $advert)
                            <div class="activity-item d-flex align-items-center">
                                <div class="activity-icon bg-primary text-white">
                                    <i class="bi bi-badge-ad"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ Str::limit($advert->title, 40) }}</h6>
                                    <small class="text-muted">
                                        by {{ $advert->user->name ?? 'Unknown' }} •
                                        {{ $advert->created_at->diffForHumans() }}
                                    </small>
                                </div>
                                <span class="badge-custom bg-{{ $advert->ad_status === 'active' ? 'success' : 'warning' }}">
                                    {{ ucfirst($advert->ad_status) }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                {{-- Recent Users --}}
                @if(isset($recentActivities['users']) && count($recentActivities['users']) > 0)
                <div class="col-lg-6">
                    <div class="card dashboard-card">
                        <div class="card-header bg-transparent border-bottom">
                            <h5 class="mb-0"><i class="bi bi-people me-2"></i>Recent Users</h5>
                        </div>
                        <div class="card-body p-0">
                            @foreach($recentActivities['users'] as $user)
                            <div class="activity-item d-flex align-items-center">
                                <div class="activity-icon bg-info text-white">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $user->name }}</h6>
                                    <small class="text-muted">
                                        {{ $user->email }} •
                                        {{ $user->created_at->diffForHumans() }}
                                    </small>
                                </div>
                                <span class="badge-custom bg-{{ $user->verified ? 'success' : 'secondary' }}">
                                    {{ $user->verified ? 'Verified' : 'Unverified' }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                {{-- Recent Reports --}}
                @if(isset($recentActivities['reports']) && count($recentActivities['reports']) > 0)
                <div class="col-lg-12">
                    <div class="card dashboard-card">
                        <div class="card-header bg-transparent border-bottom">
                            <h5 class="mb-0"><i class="bi bi-flag me-2"></i>Recent Reports</h5>
                        </div>
                        <div class="card-body p-0">
                            @foreach($recentActivities['reports'] as $report)
                            <div class="activity-item d-flex align-items-center">
                                <div class="activity-icon bg-warning text-white">
                                    <i class="bi bi-flag"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ Str::limit($report->reason ?? 'Report', 50) }}</h6>
                                    <small class="text-muted">
                                        by {{ $report->user->name ?? 'Unknown' }} •
                                        Ad: {{ Str::limit($report->adverts->title ?? 'N/A', 30) }} •
                                        {{ $report->created_at->diffForHumans() }}
                                    </small>
                                </div>
                                <span class="badge-custom bg-{{ $report->status === 'resolved' ? 'success' : 'danger' }}">
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
                            <i class="bi bi-shield-exclamation" style="font-size: 4rem; color: #6c757d;"></i>
                            <h3 class="mt-3">Limited Access</h3>
                            <p class="text-muted">
                                You don't have permissions to view dashboard statistics.<br>
                                Please contact your administrator to request access.
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
