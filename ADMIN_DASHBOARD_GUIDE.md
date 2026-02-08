# Admin Dashboard Guide

## Overview

The admin dashboard has been completely redesigned with **role-based statistics** and a **modern, responsive UI**. Each admin sees only the statistics and actions relevant to their assigned permissions.

---

## ✨ Key Features

### 1. **Role-Based Visibility**

Admins only see statistics for areas they have permission to access:

| Permission | Stats Visible |
|------------|---------------|
| `view_users` | User management stats (total, active, verified, new users) |
| `view_adverts` | Advertisement stats (total, active, pending, sold, disabled) |
| `view_active_boosts`, `view_completed_boosts`, `view_unpaid_boosts` | Boost performance stats and revenue |
| `view_payments`, `view_settlements` | Payment stats, revenue, settlement amounts |
| `manage_shipping` | Shipping and delivery status |
| `manage_categories` | Category, subcategory, and brand counts |
| `view_reports` | Report statistics (pending, resolved, new) |
| `view_blog` | Blog post counts (published, drafts) |

**If an admin has no permissions**, they see a "Limited Access" message.

### 2. **Comprehensive Statistics**

#### 📊 Dashboard Sections

**User Management** (requires `view_users`):
- Total users
- Active vs inactive users
- Verified vs unverified users
- New users (today, this week, this month)

**Advertisement Management** (requires `view_adverts`):
- Total adverts
- Active ads with breakdown (active, sold)
- Pending approval count
- New ads (today, this month)

**Boost Management** (requires boost permissions):
- Active boosts
- Completed boosts
- Unpaid boosts
- Revenue today (₦)
- Revenue this month (₦)

**Payments & Settlements** (requires payment permissions):
- Total revenue (₦)
- Revenue this month (₦)
- Pending settlements count
- Pending settlement amount (₦)

**Shipping Management** (requires `manage_shipping`):
- Pending shipping
- In transit
- Delivered

**Other Modules**:
- **Reports**: Pending, total, new today
- **Categories**: Total categories, subcategories, brands
- **Blog**: Total posts, published, drafts

### 3. **Quick Actions**

Dynamic action buttons appear based on admin permissions:

| Permission | Quick Action Button |
|------------|---------------------|
| `create_advert` | Create Advert |
| `view_users` | Manage Users |
| `view_reports` | View Reports |
| `view_settlements` | Settlements |
| `manage_categories` | Categories |
| `create_blog` | Write Blog Post |
| Role: `super_admin` | System Settings |

### 4. **Recent Activities**

Shows recent activity feeds for:

**Recent Adverts** (if `view_adverts`):
- Last 5 adverts created
- User who posted
- Status badge (active/pending/etc.)
- Time posted

**Recent Users** (if `view_users`):
- Last 5 registered users
- Email address
- Verification status badge
- Registration time

**Recent Reports** (if `view_reports`):
- Last 5 reports submitted
- Reporter name
- Reported ad
- Status (pending/resolved)

### 5. **Modern UI Design**

- **Gradient stat cards** with distinct colors for each module
- **Hover effects** - cards lift slightly on hover
- **Mini stats** - additional metrics within primary cards
- **Welcome banner** - personalized greeting with admin name and role
- **Responsive layout** - works on all screen sizes
- **Icon-rich interface** - Bootstrap Icons throughout
- **Activity feed** - clean, list-based recent activity display

---

## 🎯 Dashboard by Role

### Super Admin

Sees **everything**:
- All 9 stat sections
- All quick actions (7 buttons)
- All recent activities (adverts, users, reports)
- Total visibility across the platform

### Customer Care

Sees:
- User management stats
- Advertisement stats
- Shipping stats
- Recent adverts and users
- Quick actions: Manage Users, View Reports

### Advert Manager

Sees:
- Advertisement stats
- Boost stats
- Category stats
- Recent adverts
- Quick actions: Create Advert, Categories

### SEO Manager

Sees:
- Blog stats only
- Quick action: Write Blog Post

### Resolution

Sees:
- Report stats only
- Recent reports
- Quick action: View Reports

### Finance Manager (custom role example)

Would see (if permissions assigned):
- Payment & settlement stats
- Revenue figures
- Quick action: Settlements

---

## 🎨 Visual Design

### Color Scheme

Each module has a unique gradient:

- **Users**: Purple gradient (`#667eea` to `#764ba2`)
- **Adverts**: Pink gradient (`#f093fb` to `#f5576c`)
- **Boosts**: Blue gradient (`#4facfe` to `#00f2fe`)
- **Payments**: Green gradient (`#43e97b` to `#38f9d7`)
- **Shipping**: Orange/yellow gradient (`#fa709a` to `#fee140`)
- **Settlements**: Teal gradient (`#30cfd0` to `#330867`)
- **Reports**: Soft pastel (`#a8edea` to `#fed6e3`)
- **Categories**: Rose gradient (`#ff9a9e` to `#fecfef`)
- **Blog**: Warm gradient (`#ffecd2` to `#fcb69f`)

### Stat Card Layout

```
┌──────────────────────────────┐
│  [Large Number]       [Icon] │
│  LABEL TEXT                  │
│                              │
│  ┌─────────┐  ┌─────────┐   │
│  │  Sub    │  │  Sub    │   │
│  │  Stat   │  │  Stat   │   │
│  └─────────┘  └─────────┘   │
└──────────────────────────────┘
```

### Responsive Breakpoints

- **Desktop** (lg): 4 columns per row
- **Tablet** (md): 2 columns per row
- **Mobile** (sm): 1 column per row

---

## 🔧 Technical Implementation

### Controller Logic

**File**: `app/Http/Controllers/AdminController.php`

```php
public function index()
{
    $admin = Auth::guard('admin')->user();
    $stats = [];

    // Check permissions and add stats
    if ($admin->can('view_users')) {
        $stats['users'] = [ /* user stats */ ];
    }

    if ($admin->can('view_adverts')) {
        $stats['adverts'] = [ /* advert stats */ ];
    }

    // ... more permission checks ...

    return view('backend.index', compact('stats', 'admin', ...));
}
```

### View Logic

**File**: `resources/views/backend/index.blade.php`

```blade
@if(isset($stats['users']))
    {{-- Display user stats --}}
@endif

@if(isset($stats['adverts']))
    {{-- Display advert stats --}}
@endif
```

### Permission Checks

Uses **Spatie Laravel Permission** package:

```php
$admin->can('permission_name')     // Check single permission
$admin->hasRole('role_name')       // Check role
$admin->getRoleNames()             // Get all role names
```

---

## 📈 Analytics & Insights

### Time-Based Metrics

All applicable modules show:
- **Today**: Created/updated today
- **This Week**: Within current week (Monday-Sunday)
- **This Month**: Within current calendar month

### Revenue Tracking

Payment and boost modules show:
- Total lifetime revenue
- Today's revenue
- This month's revenue
- Formatted in Nigerian Naira (₦)

### Status Breakdowns

Multiple stats show sub-categories:
- **Users**: Active/Inactive, Verified/Unverified
- **Adverts**: Active/Sold, Pending/Disabled
- **Boosts**: Active/Completed/Unpaid
- **Payments**: Paid/Pending
- **Shipping**: Pending/Shipped/Delivered
- **Reports**: Pending/Resolved
- **Blog**: Published/Draft

---

## 🚀 Usage Examples

### Scenario 1: Super Admin Login

**Sees**:
1. Welcome banner: "Welcome back, admin! Role: super_admin"
2. 7 quick action buttons
3. All 9 stat sections (40+ individual metrics)
4. All 3 recent activity feeds

**Can do**:
- Monitor entire platform at a glance
- Quickly access any admin function
- See recent activity across all modules

### Scenario 2: Customer Care Login

**Sees**:
1. Welcome banner with their role
2. 2 quick action buttons (Manage Users, View Reports)
3. User management stats
4. Advertisement stats
5. Shipping stats
6. Recent users and adverts

**Can do**:
- Monitor user registrations
- Track ad activity
- Check shipping status
- Access user management directly

### Scenario 3: Content Creator Login

**Sees**:
1. Welcome banner
2. 1 quick action (Write Blog Post)
3. Blog stats only (if they have `view_blog` permission)

**Limited view** - focused only on their content management role.

---

## ⚙️ Customization

### Adding New Stats

1. **Add permission check** in `AdminController::index()`:

```php
if ($admin->can('view_analytics')) {
    $stats['analytics'] = [
        'total_visits' => /* query */,
        'bounce_rate' => /* query */,
    ];
}
```

2. **Add view section** in `resources/views/backend/index.blade.php`:

```blade
@if(isset($stats['analytics']))
<div class="row mb-4">
    <div class="col-12">
        <div class="section-header">
            <h2><i class="bi bi-graph-up me-2"></i>Analytics</h2>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="stat-card analytics">
            <div class="stat-number">{{ $stats['analytics']['total_visits'] }}</div>
            <div class="stat-label">Total Visits</div>
        </div>
    </div>
</div>
@endif
```

3. **Add gradient color** in the `<style>` section:

```css
.stat-card.analytics {
    background: linear-gradient(135deg, #YOUR_COLOR_1 0%, #YOUR_COLOR_2 100%);
}
```

### Adding Quick Actions

In `AdminController::getQuickActions()`:

```php
if ($admin->can('export_reports')) {
    $actions[] = [
        'title' => 'Export Reports',
        'icon' => 'bi-download',
        'url' => '/admin/export-reports',
        'color' => 'success'
    ];
}
```

### Adding Recent Activity Feeds

In `AdminController::index()`:

```php
if ($admin->can('view_transactions')) {
    $recentTransactions = Transaction::with('user')
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();
    $recentActivities['transactions'] = $recentTransactions;
}
```

Then add view code similar to existing activity feeds.

---

## 🐛 Troubleshooting

### Issue: "Limited Access" message shown to super_admin

**Cause**: Admin doesn't have permissions assigned
**Solution**:
```bash
php artisan tinker
$admin = Admin::find(1);
$admin->assignRole('super_admin');
```

### Issue: Stats not updating

**Cause**: Cache issue
**Solution**:
```bash
php artisan cache:clear
php artisan view:clear
```

### Issue: Blank dashboard

**Cause**: Admin guard not authenticated
**Solution**: Check `Auth::guard('admin')->check()` is true

### Issue: Recent activities showing errors

**Cause**: Missing relationships in models
**Solution**: Ensure `Advert::user()`, `Report::user()`, etc. relationships exist

### Issue: Quick actions showing 404

**Cause**: Route doesn't exist
**Solution**:
1. Check route with `php artisan route:list`
2. Update URL in `getQuickActions()` method

---

## 📊 Performance Considerations

### Query Optimization

All stats use:
- **Indexed columns** (created_at, status fields)
- **Count queries** instead of loading full models
- **Conditional loading** (only fetch data if admin has permission)
- **Eager loading** for relationships (`with(['user', 'adverts'])`)

### Caching Strategy (Optional)

For high-traffic sites, consider caching:

```php
$stats['users'] = Cache::remember('admin.stats.users', 300, function() use ($admin) {
    if (!$admin->can('view_users')) return null;

    return [
        'total' => User::count(),
        // ... other stats
    ];
});
```

Cache TTL: 5 minutes (300 seconds)

### Database Indexes

Ensure these indexes exist:
- `users.created_at`
- `adverts.created_at`, `adverts.ad_status`
- `payments.created_at`, `payments.payment_status`
- `reports.created_at`, `reports.status`

---

## 🎓 Best Practices

1. **Always check permissions** before displaying stats
2. **Use number_format()** for readability (1,234 instead of 1234)
3. **Show currency symbol** for monetary values (₦)
4. **Use relative time** for recent activities (`diffForHumans()`)
5. **Provide context** - mini stats within cards help admins understand breakdowns
6. **Keep it fast** - dashboard should load quickly (optimize queries)
7. **Mobile-first** - ensure responsive design works on all devices

---

## 📖 Related Documentation

- [ADMIN_RBAC_GUIDE.md](ADMIN_RBAC_GUIDE.md) - Complete RBAC system guide
- [Spatie Laravel Permission Docs](https://spatie.be/docs/laravel-permission)
- [Bootstrap Icons](https://icons.getbootstrap.com/)

---

## ✅ Summary

The enhanced admin dashboard provides:

✨ **Beautiful, modern UI** with gradient cards and smooth animations
🔒 **Role-based security** - admins only see what they have permission to
📊 **Comprehensive stats** covering all platform modules
⚡ **Quick actions** for common tasks
📈 **Time-based analytics** (today, week, month)
💰 **Revenue tracking** for financial oversight
🎯 **Recent activity feeds** for monitoring
📱 **Fully responsive** design
🚀 **High performance** with optimized queries

**Each admin gets a personalized, permission-based dashboard experience!**

---

*Last Updated: 2026-02-08*
*Dashboard Version: 2.0 - Role-Based Statistics*
