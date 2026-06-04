@include('admin.layouts.header')
@include('admin.layouts.nav')

<style>
/* ─── Welcome card ─────────────────────────────────────────── */
.welcome-card {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
    color: #fff;
    border-radius: 12px;
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.welcome-card::after {
    content: '';
    position: absolute;
    right: -40px; top: -40px;
    width: 220px; height: 220px;
    border-radius: 50%;
    background: rgba(255,255,255,0.04);
}
.welcome-card h1 { font-size: 1.65rem; font-weight: 700; margin-bottom: 4px; }
.welcome-card p  { color: rgba(255,255,255,0.78); margin-bottom: 0; font-size: 0.92rem; }
.role-badge {
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.22);
    padding: 4px 14px;
    border-radius: 20px;
    display: inline-block;
    margin-top: 12px;
    font-size: 0.8rem;
    letter-spacing: 0.3px;
}

/* ─── Quick actions ─────────────────────────────────────────── */
.quick-action-btn {
    border-radius: 8px;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-weight: 600;
    font-size: 0.88rem;
    margin-bottom: 12px;
    transition: transform 0.15s, box-shadow 0.15s;
    text-decoration: none;
}
.quick-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 14px rgba(0,0,0,0.14);
}
.quick-action-btn i { font-size: 1.1rem; }

/* ─── Section heading ───────────────────────────────────────── */
.section-header { margin-bottom: 16px; padding-bottom: 10px; border-bottom: 2px solid #e9ecef; }
.section-header h2 { color: #212529; font-weight: 700; font-size: 1.1rem; margin-bottom: 2px; }
.section-header p  { color: #6c757d; font-size: 0.85rem; margin-bottom: 0; }

/* ─── KPI cards ─────────────────────────────────────────────── */
.kpi-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 6px rgba(0,0,0,0.07);
    margin-bottom: 24px;
    overflow: hidden;
    border-left: 4px solid transparent;
}
.kpi-card.users      { border-left-color: #4361ee; }
.kpi-card.adverts    { border-left-color: #e63946; }
.kpi-card.boosts     { border-left-color: #0096c7; }
.kpi-card.payments   { border-left-color: #2d9a4e; }

.kpi-header {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 20px;
    background: #fafafa;
    border-bottom: 1px solid #f0f2f5;
}
.kpi-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}
.kpi-icon.users    { background: #eef0fd; color: #4361ee; }
.kpi-icon.adverts  { background: #fdeaeb; color: #e63946; }
.kpi-icon.boosts   { background: #e0f4fb; color: #0096c7; }
.kpi-icon.payments { background: #e8f5ec; color: #2d9a4e; }

.kpi-header-title { font-weight: 700; font-size: 0.92rem; color: #1a1a2e; line-height: 1.3; }
.kpi-header-sub   { font-size: 0.76rem; color: #9ca3af; margin: 0; }

/* ─── Metric cells ──────────────────────────────────────────── */
.kpi-metrics {
    display: flex;
    flex-wrap: wrap;
    align-items: stretch;
}
.kpi-metric {
    padding: 18px 20px;
    border-right: 1px solid #f0f2f5;
    flex: 1 1 120px;
    min-width: 0;
}
.kpi-metric:last-child { border-right: none; }
.kpi-metric.main   { flex: 1.5 1 160px; }
.kpi-metric.source { flex: 1 1 140px; }

.kpi-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1a1a2e;
    line-height: 1.1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.kpi-metric.main .kpi-value { font-size: 2.1rem; }
.kpi-value.currency { font-size: 1.3rem; }
.kpi-metric.main .kpi-value.currency { font-size: 1.65rem; }

.kpi-label {
    font-size: 0.67rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    color: #9ca3af;
    margin-top: 5px;
}
.kpi-sub {
    font-size: 0.74rem;
    color: #6b7280;
    margin-top: 3px;
    line-height: 1.4;
}

/* Source pair (App / Web) */
.kpi-source-pair { display: flex; gap: 20px; margin-top: 4px; }
.kpi-source-pair .kpi-value { font-size: 1.45rem; }
.kpi-source-pair .kpi-source-label {
    font-size: 0.67rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.5px; color: #9ca3af; margin-top: 2px;
}

/* ─── Trend sub-row ─────────────────────────────────────────── */
.kpi-trends { border-top: 1px dashed #e9ecef; background: #fafbfc; }
.kpi-trend-label {
    padding: 7px 20px 0;
    font-size: 0.64rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    color: #c0c4cc;
}
.kpi-metrics.compact .kpi-metric {
    flex: 1 1 90px;
    padding: 10px 16px 14px;
}
.kpi-metrics.compact .kpi-value          { font-size: 1.2rem; color: #374151; }
.kpi-metrics.compact .kpi-value.currency { font-size: 1.05rem; }
.kpi-metrics.compact .kpi-label          { margin-top: 3px; }

/* ─── Simple stat card (Shipping, Reports, etc.) ─────────────── */
.stat-card {
    background: #fff;
    border-radius: 10px;
    padding: 20px 22px;
    margin-bottom: 20px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07);
    border-top: 4px solid #dee2e6;
    position: relative;
    overflow: hidden;
}
.stat-card.shipping   { border-top-color: #e76f51; }
.stat-card.reports    { border-top-color: #6c757d; }
.stat-card.categories { border-top-color: #7209b7; }
.stat-card.blog       { border-top-color: #f4a261; }

.stat-icon {
    position: absolute; top: 16px; right: 18px;
    width: 46px; height: 46px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.35rem;
    flex-shrink: 0;
}
.stat-icon.shipping   { background: #fdf0ec; color: #e76f51; }
.stat-icon.reports    { background: #f0f0f0; color: #6c757d; }
.stat-icon.categories { background: #f3e8fd; color: #7209b7; }
.stat-icon.blog       { background: #fef7ec; color: #f4a261; }

.stat-number {
    font-size: 2rem; font-weight: 700; color: #212529;
    line-height: 1.2; margin-bottom: 4px; padding-right: 56px;
}
.stat-label {
    font-size: 0.75rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.6px; color: #6c757d;
}
.mini-stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
    gap: 10px;
    margin-top: 14px;
}
.mini-stat {
    background: #f8f9fa; border: 1px solid #e9ecef;
    padding: 10px 8px; border-radius: 6px; text-align: center;
}
.mini-stat-value { font-size: 1.2rem; font-weight: 700; color: #212529; margin-bottom: 3px; }
.mini-stat-label {
    font-size: 0.67rem; font-weight: 600;
    color: #6c757d; text-transform: uppercase; letter-spacing: 0.4px;
}

/* ─── Activity feed ─────────────────────────────────────────── */
.dashboard-card {
    border: none; border-radius: 10px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07); margin-bottom: 20px;
}
.activity-item {
    padding: 11px 16px; border-bottom: 1px solid #f0f0f0;
    transition: background 0.15s;
    display: flex; align-items: center; gap: 12px;
}
.activity-item:hover  { background: #f8f9fa; }
.activity-item:last-child { border-bottom: none; }
.activity-icon {
    width: 36px; height: 36px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.95rem; flex-shrink: 0;
}
.activity-badge {
    padding: 3px 10px; border-radius: 12px;
    font-size: 0.7rem; font-weight: 600; white-space: nowrap;
}
.activity-badge.success   { background: #d1edda; color: #155724; }
.activity-badge.warning   { background: #fff3cd; color: #856404; }
.activity-badge.danger    { background: #f8d7da; color: #721c24; }
.activity-badge.secondary { background: #e2e3e5; color: #383d41; }

/* ─── Responsive tweaks ─────────────────────────────────────── */
@media (max-width: 575px) {
    .kpi-metric        { flex: 1 1 46%; border-bottom: 1px solid #f0f2f5; }
    .kpi-metric.main   { flex: 1 1 100%; }
    .kpi-metrics.compact .kpi-metric { flex: 1 1 30%; }
    .welcome-card { padding: 22px 20px; }
    .welcome-card h1 { font-size: 1.35rem; }
}
@media (max-width: 400px) {
    .kpi-metrics.compact .kpi-metric { flex: 1 1 46%; }
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
        <div class="container-fluid px-0 px-sm-2">

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
                        <p>Common admin tasks</p>
                    </div>
                </div>
                @foreach($quickActions as $action)
                <div class="col-xl-3 col-lg-4 col-sm-6">
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
            <div class="kpi-card users">
                <div class="kpi-header">
                    <div class="kpi-icon users"><i class="bi bi-people"></i></div>
                    <div>
                        <div class="kpi-header-title">User Management</div>
                        <p class="kpi-header-sub">Registered users overview</p>
                    </div>
                </div>
                <div class="kpi-metrics">
                    <div class="kpi-metric main">
                        <div class="kpi-value">{{ number_format($stats['users']['total']) }}</div>
                        <div class="kpi-label">Total Users</div>
                        <div class="kpi-sub">
                            {{ number_format($stats['users']['active']) }} active &middot;
                            {{ number_format($stats['users']['verified']) }} verified
                        </div>
                    </div>
                    <div class="kpi-metric">
                        <div class="kpi-value">{{ number_format($stats['users']['active']) }}</div>
                        <div class="kpi-label">Active</div>
                    </div>
                    <div class="kpi-metric">
                        <div class="kpi-value">{{ number_format($stats['users']['verified']) }}</div>
                        <div class="kpi-label">Verified</div>
                    </div>
                    <div class="kpi-metric">
                        <div class="kpi-value">{{ number_format($stats['users']['inactive'] ?? ($stats['users']['total'] - $stats['users']['active'])) }}</div>
                        <div class="kpi-label">Inactive</div>
                    </div>
                </div>
                <div class="kpi-trends">
                    <div class="kpi-trend-label"><i class="bi bi-person-plus me-1"></i>Registration Trends</div>
                    <div class="kpi-metrics compact">
                        <div class="kpi-metric">
                            <div class="kpi-value">{{ number_format($stats['users']['new_today']) }}</div>
                            <div class="kpi-label">Today</div>
                        </div>
                        <div class="kpi-metric">
                            <div class="kpi-value">{{ number_format($stats['users']['new_this_week']) }}</div>
                            <div class="kpi-label">This Week</div>
                        </div>
                        <div class="kpi-metric">
                            <div class="kpi-value">{{ number_format($stats['users']['new_last_week']) }}</div>
                            <div class="kpi-label">Last Week</div>
                        </div>
                        <div class="kpi-metric">
                            <div class="kpi-value">{{ number_format($stats['users']['new_this_month']) }}</div>
                            <div class="kpi-label">This Month</div>
                        </div>
                        <div class="kpi-metric">
                            <div class="kpi-value">{{ number_format($stats['users']['new_last_month']) }}</div>
                            <div class="kpi-label">Last Month</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- ── Advertisement Management ─────────────────────── --}}
            @if(isset($stats['adverts']))
            <div class="kpi-card adverts">
                <div class="kpi-header">
                    <div class="kpi-icon adverts"><i class="bi bi-badge-ad"></i></div>
                    <div>
                        <div class="kpi-header-title">Advertisement Management</div>
                        <p class="kpi-header-sub">Active listings and performance</p>
                    </div>
                </div>
                <div class="kpi-metrics">
                    <div class="kpi-metric main">
                        <div class="kpi-value">{{ number_format($stats['adverts']['total']) }}</div>
                        <div class="kpi-label">Total Adverts</div>
                        <div class="kpi-sub">
                            {{ number_format($stats['adverts']['active']) }} active &middot;
                            {{ number_format($stats['adverts']['sold']) }} sold
                        </div>
                    </div>
                    <div class="kpi-metric">
                        <div class="kpi-value">{{ number_format($stats['adverts']['active']) }}</div>
                        <div class="kpi-label">Active</div>
                    </div>
                    <div class="kpi-metric">
                        <div class="kpi-value text-warning">{{ number_format($stats['adverts']['pending']) }}</div>
                        <div class="kpi-label">Pending Approval</div>
                    </div>
                    <div class="kpi-metric">
                        <div class="kpi-value">{{ number_format($stats['adverts']['sold']) }}</div>
                        <div class="kpi-label">Sold</div>
                    </div>
                    <div class="kpi-metric source">
                        <div class="kpi-label" style="margin-bottom:6px;">Posted By Source</div>
                        <div class="kpi-source-pair">
                            <div>
                                <div class="kpi-value">{{ number_format($stats['adverts']['source_api']) }}</div>
                                <div class="kpi-source-label">App</div>
                            </div>
                            <div>
                                <div class="kpi-value">{{ number_format($stats['adverts']['source_web']) }}</div>
                                <div class="kpi-source-label">Web</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="kpi-trends">
                    <div class="kpi-trend-label"><i class="bi bi-graph-up me-1"></i>Listing Trends</div>
                    <div class="kpi-metrics compact">
                        <div class="kpi-metric">
                            <div class="kpi-value">{{ number_format($stats['adverts']['new_today']) }}</div>
                            <div class="kpi-label">Today</div>
                        </div>
                        <div class="kpi-metric">
                            <div class="kpi-value">{{ number_format($stats['adverts']['new_this_week']) }}</div>
                            <div class="kpi-label">This Week</div>
                        </div>
                        <div class="kpi-metric">
                            <div class="kpi-value">{{ number_format($stats['adverts']['new_last_week']) }}</div>
                            <div class="kpi-label">Last Week</div>
                        </div>
                        <div class="kpi-metric">
                            <div class="kpi-value">{{ number_format($stats['adverts']['new_this_month']) }}</div>
                            <div class="kpi-label">This Month</div>
                        </div>
                        <div class="kpi-metric">
                            <div class="kpi-value">{{ number_format($stats['adverts']['new_last_month']) }}</div>
                            <div class="kpi-label">Last Month</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- ── Boost Management ─────────────────────────────── --}}
            @if(isset($stats['boosts']))
            <div class="kpi-card boosts">
                <div class="kpi-header">
                    <div class="kpi-icon boosts"><i class="bi bi-rocket-takeoff"></i></div>
                    <div>
                        <div class="kpi-header-title">Boost Management</div>
                        <p class="kpi-header-sub">Promoted advertisements performance</p>
                    </div>
                </div>
                <div class="kpi-metrics">
                    <div class="kpi-metric main">
                        <div class="kpi-value">{{ number_format($stats['boosts']['active']) }}</div>
                        <div class="kpi-label">Active Boosts</div>
                        <div class="kpi-sub">{{ number_format($stats['boosts']['completed']) }} completed</div>
                    </div>
                    <div class="kpi-metric">
                        <div class="kpi-value text-danger">{{ number_format($stats['boosts']['unpaid']) }}</div>
                        <div class="kpi-label">Unpaid / Pending</div>
                    </div>
                    <div class="kpi-metric">
                        <div class="kpi-value">{{ number_format($stats['boosts']['total']) }}</div>
                        <div class="kpi-label">Total Boosts</div>
                    </div>
                    <div class="kpi-metric">
                        <div class="kpi-value currency">₦{{ number_format($stats['boosts']['revenue_this_month'], 0) }}</div>
                        <div class="kpi-label">Revenue This Month</div>
                    </div>
                    <div class="kpi-metric">
                        <div class="kpi-value currency">₦{{ number_format($stats['boosts']['revenue_last_month'], 0) }}</div>
                        <div class="kpi-label">Revenue Last Month</div>
                    </div>
                </div>
                <div class="kpi-trends">
                    <div class="kpi-trend-label"><i class="bi bi-cash-stack me-1"></i>Boost Revenue Trends</div>
                    <div class="kpi-metrics compact">
                        <div class="kpi-metric">
                            <div class="kpi-value currency">₦{{ number_format($stats['boosts']['revenue_today'], 0) }}</div>
                            <div class="kpi-label">Today</div>
                        </div>
                        <div class="kpi-metric">
                            <div class="kpi-value currency">₦{{ number_format($stats['boosts']['revenue_this_week'], 0) }}</div>
                            <div class="kpi-label">This Week</div>
                        </div>
                        <div class="kpi-metric">
                            <div class="kpi-value currency">₦{{ number_format($stats['boosts']['revenue_last_week'], 0) }}</div>
                            <div class="kpi-label">Last Week</div>
                        </div>
                        <div class="kpi-metric">
                            <div class="kpi-value currency">₦{{ number_format($stats['boosts']['revenue_this_month'], 0) }}</div>
                            <div class="kpi-label">This Month</div>
                        </div>
                        <div class="kpi-metric">
                            <div class="kpi-value currency">₦{{ number_format($stats['boosts']['revenue_last_month'], 0) }}</div>
                            <div class="kpi-label">Last Month</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- ── Payments & Settlements ───────────────────────── --}}
            @if(isset($stats['payments']) || isset($stats['settlements']))
            <div class="kpi-card payments">
                <div class="kpi-header">
                    <div class="kpi-icon payments"><i class="bi bi-credit-card"></i></div>
                    <div>
                        <div class="kpi-header-title">Payments &amp; Settlements</div>
                        <p class="kpi-header-sub">Financial transactions overview</p>
                    </div>
                </div>
                <div class="kpi-metrics">
                    @if(isset($stats['payments']))
                    <div class="kpi-metric main">
                        <div class="kpi-value currency">₦{{ number_format($stats['payments']['total_revenue'], 0) }}</div>
                        <div class="kpi-label">Total Revenue</div>
                        <div class="kpi-sub">{{ number_format($stats['payments']['paid']) }} paid transactions</div>
                    </div>
                    <div class="kpi-metric">
                        <div class="kpi-value currency">₦{{ number_format($stats['payments']['revenue_this_month'], 0) }}</div>
                        <div class="kpi-label">This Month</div>
                    </div>
                    <div class="kpi-metric">
                        <div class="kpi-value text-warning">{{ number_format($stats['payments']['pending']) }}</div>
                        <div class="kpi-label">Pending Payments</div>
                    </div>
                    <div class="kpi-metric source">
                        <div class="kpi-label" style="margin-bottom:6px;">Purchases By Source</div>
                        <div class="kpi-source-pair">
                            <div>
                                <div class="kpi-value">{{ number_format($stats['payments']['source_api']) }}</div>
                                <div class="kpi-source-label">App</div>
                            </div>
                            <div>
                                <div class="kpi-value">{{ number_format($stats['payments']['source_web']) }}</div>
                                <div class="kpi-source-label">Web</div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if(isset($stats['settlements']))
                    <div class="kpi-metric">
                        <div class="kpi-value text-danger">{{ number_format($stats['settlements']['pending']) }}</div>
                        <div class="kpi-label">Pending Settlements</div>
                    </div>
                    <div class="kpi-metric">
                        <div class="kpi-value currency">₦{{ number_format($stats['settlements']['pending_amount'], 0) }}</div>
                        <div class="kpi-label">Pending Amount</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- ── Shipping ────────────────────────────────────── --}}
            @if(isset($stats['shipping']))
            <div class="row mb-4">
                <div class="col-12">
                    <div class="section-header">
                        <h2><i class="bi bi-truck me-2"></i>Shipping Management</h2>
                        <p>Delivery status overview</p>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="stat-card shipping">
                        <div class="stat-icon shipping"><i class="bi bi-hourglass-split"></i></div>
                        <div class="stat-number">{{ number_format($stats['shipping']['pending']) }}</div>
                        <div class="stat-label">Pending Shipping</div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="stat-card shipping">
                        <div class="stat-icon shipping"><i class="bi bi-truck-front"></i></div>
                        <div class="stat-number">{{ number_format($stats['shipping']['shipped']) }}</div>
                        <div class="stat-label">In Transit</div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
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
                <div class="col-xl-4 col-md-6">
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
                <div class="col-xl-4 col-md-6">
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
                <div class="col-xl-4 col-md-6">
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
                                    <div class="fw-semibold text-truncate" style="font-size:0.88rem;">{{ Str::limit($advert->title, 45) }}</div>
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
                                    <div class="fw-semibold text-truncate" style="font-size:0.88rem;">{{ $user->name }}</div>
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
                                    <div class="fw-semibold text-truncate" style="font-size:0.88rem;">{{ Str::limit($report->reason ?? 'Report', 55) }}</div>
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

            {{-- No Permissions --}}
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

@include('admin.layouts.footer')
