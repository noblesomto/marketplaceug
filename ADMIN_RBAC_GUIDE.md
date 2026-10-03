# Admin Role-Based Access Control (RBAC) System Guide

## Overview

This marketplace has a **fully dynamic** Role-Based Access Control system powered by Spatie Laravel Permission. You can create and manage everything through the admin panel without touching code!

---

## ✅ What's Already Dynamic

### 1. **Create New Roles**
- Add custom roles like "Content Manager", "Finance Manager", etc.
- No coding required

### 2. **Create New Permissions**
- Add new permissions as your system grows
- Examples: `manage_promotions`, `view_analytics`, `export_reports`

### 3. **Assign Permissions to Roles**
- Mix and match permissions for each role
- Update anytime through the UI

### 4. **Create New Admin Users**
- Add new team members
- Set username, email, password

### 5. **Assign Roles to Admins**
- Give admins one or multiple roles
- Update roles anytime

---

## 🎯 How to Use the System

### Access Points

1. **Role & Permission Management**
   - URL: `/admin/settings/roles`
   - Middleware: `adminrole:super_admin` (only super admins can access)
   - Features:
     - Create roles
     - Create permissions
     - Assign permissions to roles
     - Assign roles to admins

2. **Admin User Management**
   - URL: `/admin/settings/manage-admins`
   - Middleware: `adminrole:super_admin`
   - Features:
     - Create admin users
     - Edit admin users
     - Delete admin users
     - Search admins

---

## 📋 Step-by-Step Workflows

### Creating a New Admin with a Custom Role

**Scenario:** You want to add a "Marketing Manager" who can manage blog posts and view analytics.

#### Step 1: Create the Role
1. Go to `/admin/settings/roles`
2. In the "Add New Role" card, enter: `Marketing_Manager`
3. Click "Create Role"

#### Step 2: Create Required Permissions (if they don't exist)
1. In the "Add New Permission" card, add:
   - `manage_blog_posts`
   - `view_analytics`
   - `manage_seo`
2. Click "Create Permission" for each

#### Step 3: Assign Permissions to the Role
1. Scroll down to "Assign Permissions to Roles"
2. Find your `Marketing_Manager` role
3. Check the boxes for:
   - `manage_blog_posts`
   - `view_analytics`
   - `manage_seo`
   - `view_blog` (already exists)
   - `create_blog` (already exists)
4. Click "Update Permissions"

#### Step 4: Create the Admin User
1. Go to `/admin/settings/manage-admins`
2. Click "Create New User"
3. Fill in username, email, and password
4. Click "Create User"

#### Step 5: Assign Role to the Admin
1. Back on `/admin/settings/roles`
2. Scroll to "Assign Roles to Admins" table
3. Find the new admin in the table
4. In the "Manage Roles" dropdown, select `Marketing_Manager`
5. Click "Update"

**Done!** The new admin can now log in and access features based on their permissions.

---

## 🔐 Current Roles & Permissions

### Existing Roles

| Role | Purpose |
|------|---------|
| **super_admin** | Full system access |
| **Advert_manager** | Manage adverts, boosts, categories |
| **Customer_care** | User management + adverts |
| **Resolution** | Handle reports |
| **SEO_Manager** | Blog & SEO settings |

### All Permissions

#### Advert Management
- `create_advert`
- `update_advert`
- `delete_advert`
- `view_adverts`
- `manage_advert_status`
- `manage_sold_status`

#### Boost Management
- `view_active_boosts`
- `view_completed_boosts`
- `view_unpaid_boosts`
- `manage_boost_status`
- `manage_boost_payment`

#### Category Management
- `manage_categories`
- `manage_subcategories`
- `manage_brands`
- `manage_models`

#### User Management
- `view_users`
- `manage_user_status`
- `verify_users`
- `search_users`

#### Reports
- `view_reports`
- `manage_report_status`

#### Settings & SEO
- `manage_shipping`
- `manage_settings`
- `manage_seo`
- `view_analytics`

#### Payments & Blog
- `view_payments`
- `manage_payments`
- `view_settlements`
- `manage_settlements`
- `view_blog`
- `create_blog`
- `edit_blog`
- `delete_blog`

---

## 🛡️ Permission Middleware Usage

### In Routes (Example)

```php
// Single permission
Route::middleware(['admin.permission:manage_categories'])
    ->get('/admin/categories', [CategoryController::class, 'index']);

// Multiple permissions (user needs ANY of these)
Route::middleware(['admin.permission:view_adverts,create_advert'])
    ->get('/admin/adverts', [AdvertController::class, 'index']);

// Role middleware (user needs this role)
Route::middleware(['adminrole:super_admin'])
    ->get('/admin/settings', [SettingController::class, 'index']);
```

### In Controllers (Example)

```php
public function __construct()
{
    // Check permission
    $this->middleware('admin.permission:manage_categories');

    // Or check role
    $this->middleware('adminrole:super_admin');
}
```

### In Blade Views (Example)

```blade
@can('manage_categories')
    <a href="/admin/categories/create">Add Category</a>
@endcan

@role('super_admin')
    <a href="/admin/settings">Settings</a>
@endrole
```

---

## 🚀 Common Use Cases

### 1. Adding a Finance Team Member

**Role:** Create `Finance_Manager` role
**Permissions:** `view_payments`, `manage_payments`, `view_settlements`, `manage_settlements`

### 2. Adding a Content Creator

**Role:** Create `Content_Creator` role
**Permissions:** `view_blog`, `create_blog`, `view_adverts`

### 3. Adding a Support Staff

**Role:** Create `Support_Staff` role
**Permissions:** `view_users`, `search_users`, `view_reports`, `view_adverts`

### 4. Promoting an Admin

**Scenario:** Promote `Customer_care` to `super_admin`

1. Go to `/admin/settings/roles`
2. Find the admin in "Assign Roles to Admins" table
3. Change dropdown from `Customer_care` to `super_admin`
4. Click "Update"

---

## 🔧 Technical Details

### Database Tables (Spatie)

```
permissions          - Stores all permissions
roles                - Stores all roles
role_has_permissions - Links permissions to roles
model_has_roles      - Links admins to roles
model_has_permissions - Direct permission assignments (optional)
```

### Guard Name

All admin permissions and roles use `guard_name = 'admin'`. This separates admin permissions from regular user permissions.

### Admin Model Setup

```php
use Spatie\Permission\Traits\HasRoles;

class Admin extends Model
{
    use HasRoles;

    protected $guard_name = 'admin';
}
```

### Permission Check Methods

```php
// Check if admin has permission
$admin->can('manage_categories');

// Check if admin has role
$admin->hasRole('super_admin');

// Get all admin permissions
$admin->getAllPermissions();

// Get all admin roles
$admin->getRoleNames();
```

---

## 📊 Best Practices

### 1. Naming Conventions

**Permissions:** snake_case, verb_noun pattern — `create_advert`, `manage_users`, `view_reports`

**Roles:** PascalCase or snake_case — `Marketing_Manager`, `super_admin`, `Content_Creator`

### 2. Permission Granularity

Prefer specific permissions over broad ones: `view_users`, `create_users`, `edit_users`, `delete_users` rather than one catch-all `manage_users`.

### 3. Role Hierarchy

- `super_admin` → all permissions
- `Customer_care` → user management + adverts
- `Advert_manager` → adverts only
- `Resolution` → reports only

### 4. Regular Audits

Periodically review inactive admins, unused permissions, and over-permissioned roles.

---

## 🐛 Troubleshooting

### Issue: "Permission does not exist" error

Create the permission in `/admin/settings/roles` → "Add New Permission".

### Issue: Admin can't access a route

Check the route's required permission/role, then assign it to the admin's role (or directly to the admin) via `/admin/settings/roles`.

### Issue: Changes not reflecting

```bash
php artisan permission:cache-reset
php artisan cache:clear
```

### Issue: "No permission named X for guard admin"

The permission exists under the wrong guard (likely `web` instead of `admin`):

```php
Permission::where('name', 'permission_name')->update(['guard_name' => 'admin']);
```

---

## 🎓 Advanced Features

### Multiple Roles per Admin

```php
$admin->assignRole(['Content_Creator', 'SEO_Manager']);
$admin->hasAnyRole(['super_admin', 'Marketing_Manager']);
$admin->hasAllRoles(['Content_Creator', 'SEO_Manager']);
```

### Direct Permission Assignment

```php
$admin->givePermissionTo('special_feature');
$admin->revokePermissionTo('special_feature');
```

---

## 📈 Scaling the System

### When Adding New Features

1. Create permissions for the feature (e.g. `view_analytics`, `export_analytics`)
2. Update existing roles that should have access
3. Add middleware to new routes
4. Document the new permissions in this guide

---

## 🔗 Quick Links

- **Spatie Documentation:** https://spatie.be/docs/laravel-permission
- **Role Management UI:** `/admin/settings/roles`
- **Admin Management UI:** `/admin/settings/manage-admins`
