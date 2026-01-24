# Dynamic Boost Pricing System - Implementation Documentation

## Overview
This document outlines the implementation of a dynamic boost pricing system for Marketplace Nigeria, allowing administrators to manage boost types and durations dynamically through the admin panel.

## Components Implemented

### 1. Database Migrations

#### Tables Created:
- **`boost_types`** - Stores boost type information
  - `id` (primary key)
  - `name` (string, unique)
  - `daily_rate` (decimal 10,2)
  - `display_order` (integer)
  - `is_active` (boolean)
  - `description` (text, nullable)
  - `timestamps`

- **`boost_durations`** - Stores boost duration options with discounts
  - `id` (primary key)
  - `days` (integer, unique)
  - `discount_percentage` (decimal 5,2)
  - `label` (string)
  - `display_order` (integer)
  - `is_active` (boolean)
  - `timestamps`

- **`advert_boosts` (updated)** - Added foreign key relationships
  - `boost_type_id` (foreign key, nullable)
  - `duration_id` (foreign key, nullable)

#### Migration Files:
- `database/migrations/2026_01_24_131520_create_boost_types_table.php`
- `database/migrations/2026_01_24_131521_create_boost_durations_table.php`
- `database/migrations/2026_01_24_131556_add_boost_type_and_duration_to_advert_boosts_table.php`

### 2. Seeders

#### Initial Data Seeded:

**Boost Types:**
1. Highlight - ₦214.29/day
2. Repeated - ₦500/day
3. Top - ₦1071.43/day
4. Gallery - ₦1428.57/day

**Boost Durations:**
1. 7 Days - 0% discount
2. 14 Days - 3% discount
3. 30 Days - 5% discount
4. 90 Days - 7% discount
5. 120 Days - 10% discount

#### Seeder Files:
- `database/seeders/BoostTypeSeeder.php`
- `database/seeders/BoostDurationSeeder.php`

### 3. Eloquent Models

#### Created Models:

**BoostType** (`app/Models/BoostType.php`)
- Fillable: name, daily_rate, display_order, is_active, description
- Scopes: `active()`, `ordered()`
- Relationships: `hasMany(AdvertBoost)`
- Methods: `calculatePrice($days, $discountPercentage)`

**BoostDuration** (`app/Models/BoostDuration.php`)
- Fillable: days, discount_percentage, label, display_order, is_active
- Scopes: `active()`, `ordered()`
- Relationships: `hasMany(AdvertBoost)`

**AdvertBoost (updated)** (`app/Models/AdvertBoost.php`)
- Added relationships: `belongsTo(BoostType)`, `belongsTo(BoostDuration)`
- Added fillable: boost_type_id, duration_id

### 4. Admin Controllers

#### AdminBoostTypeController
**Location:** `app/Http/Controllers/Admin/AdminBoostTypeController.php`

**Methods:**
- `index()` - Display all boost types
- `store()` - Create new boost type
- `update($id)` - Update existing boost type
- `destroy($id)` - Delete boost type (with validation)
- `toggleStatus($id)` - Toggle active/inactive status

#### AdminBoostDurationController
**Location:** `app/Http/Controllers/Admin/AdminBoostDurationController.php`

**Methods:**
- `index()` - Display all durations
- `store()` - Create new duration
- `update($id)` - Update existing duration
- `destroy($id)` - Delete duration (with validation)
- `toggleStatus($id)` - Toggle active/inactive status

### 5. Admin Views

#### Boost Types Management
**Location:** `resources/views/backend/settings/boost-types/index.blade.php`

**Features:**
- CRUD operations for boost types
- Search functionality
- Status toggle
- Modal-based create/edit forms
- AJAX-powered operations
- Real-time validation

#### Boost Durations Management
**Location:** `resources/views/backend/settings/boost-durations/index.blade.php`

**Features:**
- CRUD operations for boost durations
- Search functionality
- Status toggle
- Modal-based create/edit forms
- AJAX-powered operations
- Discount percentage management

### 6. Admin Routes

**Route Group:** `/admin/boost-settings`
**Middleware:** `adminsession`, `admin.permission:manage_settings`

**Routes Added to `routes/web.php`:**
```php
// Boost Types
GET    /admin/boost-settings/types           - admin.boost-types.index
POST   /admin/boost-settings/types           - admin.boost-types.store
PUT    /admin/boost-settings/types/{id}      - admin.boost-types.update
DELETE /admin/boost-settings/types/{id}      - admin.boost-types.destroy
POST   /admin/boost-settings/types/{id}/toggle - admin.boost-types.toggle

// Boost Durations
GET    /admin/boost-settings/durations       - admin.boost-durations.index
POST   /admin/boost-settings/durations       - admin.boost-durations.store
PUT    /admin/boost-settings/durations/{id}  - admin.boost-durations.update
DELETE /admin/boost-settings/durations/{id}  - admin.boost-durations.destroy
POST   /admin/boost-settings/durations/{id}/toggle - admin.boost-durations.toggle
```

### 7. API Controller

**BoostController** (`app/Http/Controllers/Api/BoostController.php`)

**Methods:**
1. `getOptions()` - GET `/api/boost/options`
   - Returns all active boost types and durations
   - No authentication required
   - Ordered by display_order

2. `calculatePrice(Request $request)` - POST `/api/boost/calculate`
   - Calculates boost price with discounts
   - Required params: boost_type_id, duration_id
   - Returns: boost_type, duration, pricing breakdown

### 8. API Routes

**Added to `routes/api.php`:**
```php
Route::prefix('boost')->group(function () {
    Route::get('/options', [BoostController::class, 'getOptions']);
    Route::post('/calculate', [BoostController::class, 'calculatePrice']);
});
```

### 9. Postman Collection

**Location:** `postman/Boost_API_Collection.json`

**Test Cases Included:**
1. Get Boost Options - Success case
2. Calculate Price - Valid request
3. Calculate Price - Missing boost_type_id
4. Calculate Price - Missing duration_id
5. Calculate Price - Invalid boost_type_id
6. Calculate Price - Invalid duration_id

**Features:**
- Automated tests for each endpoint
- Environment variable setup
- Response validation
- Price calculation verification

## API Documentation

### GET /api/boost/options

**Response:**
```json
{
  "success": true,
  "data": {
    "boost_types": [
      {
        "id": 1,
        "name": "Highlight",
        "daily_rate": "214.29",
        "description": "Highlight your ad with a colored border",
        "display_order": 1
      }
    ],
    "durations": [
      {
        "id": 1,
        "days": 7,
        "discount_percentage": "0.00",
        "label": "7 Days",
        "display_order": 1
      }
    ]
  }
}
```

### POST /api/boost/calculate

**Request:**
```json
{
  "boost_type_id": 1,
  "duration_id": 3
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "boost_type": {
      "id": 1,
      "name": "Highlight",
      "daily_rate": "214.29",
      "description": "Highlight your ad"
    },
    "duration": {
      "id": 3,
      "days": 30,
      "discount_percentage": "5.00",
      "label": "30 Days (5% off)"
    },
    "pricing": {
      "base_price": "6428.70",
      "discount_percentage": "5.00",
      "discount_amount": "321.44",
      "final_price": "6107.26",
      "currency": "NGN"
    }
  }
}
```

## Installation Instructions

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Seed Data
```bash
php artisan db:seed --class=BoostTypeSeeder
php artisan db:seed --class=BoostDurationSeeder
```

### 3. Access Admin Panel
Navigate to:
- Boost Types: `/admin/boost-settings/types`
- Boost Durations: `/admin/boost-settings/durations`

### 4. Import Postman Collection
1. Open Postman
2. Click Import
3. Select `postman/Boost_API_Collection.json`
4. Update `base_url` variable to your application URL
5. Run tests

## Usage Examples

### Admin Panel Usage

**Creating a New Boost Type:**
1. Navigate to `/admin/boost-settings/types`
2. Click "Create New Boost Type"
3. Fill in:
   - Name: "Premium"
   - Daily Rate: 2000
   - Display Order: 5
   - Description: "Premium positioning"
   - Active: Checked
4. Click "Create Boost Type"

**Creating a New Duration:**
1. Navigate to `/admin/boost-settings/durations`
2. Click "Create New Duration"
3. Fill in:
   - Days: 180
   - Label: "180 Days (15% off)"
   - Discount: 15
   - Display Order: 6
   - Active: Checked
4. Click "Create Duration"

### API Usage

**Get Available Options (JavaScript):**
```javascript
fetch('/api/boost/options')
  .then(response => response.json())
  .then(data => {
    console.log('Boost Types:', data.data.boost_types);
    console.log('Durations:', data.data.durations);
  });
```

**Calculate Price (JavaScript):**
```javascript
fetch('/api/boost/calculate', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    boost_type_id: 1,
    duration_id: 3
  })
})
  .then(response => response.json())
  .then(data => {
    console.log('Final Price:', data.data.pricing.final_price);
    console.log('Discount:', data.data.pricing.discount_amount);
  });
```

## Backward Compatibility

The system maintains backward compatibility with existing boost functionality:

- Old `boost_type` and `duration` string fields in `advert_boosts` table are preserved
- New `boost_type_id` and `duration_id` foreign keys are nullable
- Existing boost records continue to work without modification
- Admin can gradually migrate old records to use new system

## Price Calculation Logic

```php
// Formula:
base_price = daily_rate × days
discount_amount = (base_price × discount_percentage) / 100
final_price = base_price - discount_amount

// Example:
// Boost Type: Highlight (₦214.29/day)
// Duration: 30 Days (5% discount)
base_price = 214.29 × 30 = ₦6,428.70
discount_amount = (6,428.70 × 5) / 100 = ₦321.44
final_price = 6,428.70 - 321.44 = ₦6,107.26
```

## Security Features

1. **Admin Access Control:**
   - Routes protected by `adminsession` middleware
   - Permission-based access (`admin.permission:manage_settings`)

2. **Validation:**
   - Input validation on all create/update operations
   - Foreign key constraints ensure data integrity
   - Prevention of deletion when active boosts exist

3. **CSRF Protection:**
   - All POST/PUT/DELETE requests include CSRF tokens

## Testing Checklist

- [ ] Run migrations successfully
- [ ] Seed data populates correctly
- [ ] Admin can view boost types list
- [ ] Admin can create new boost type
- [ ] Admin can edit boost type
- [ ] Admin can toggle boost type status
- [ ] Admin can delete unused boost type
- [ ] Admin can view durations list
- [ ] Admin can create new duration
- [ ] Admin can edit duration
- [ ] Admin can toggle duration status
- [ ] Admin can delete unused duration
- [ ] API returns active options
- [ ] API calculates price correctly
- [ ] API validates required fields
- [ ] Postman tests pass

## Future Enhancements

Potential improvements for future versions:

1. **Bulk Operations:**
   - Import/export boost configurations
   - Bulk status updates

2. **Analytics:**
   - Track which boost types are most popular
   - Revenue reporting by boost type/duration

3. **Advanced Features:**
   - Seasonal pricing
   - Category-specific boost types
   - Bundle discounts
   - Promotional codes

4. **UI Enhancements:**
   - Drag-and-drop reordering
   - Price preview calculator in admin
   - Visual pricing comparison charts

## Support

For issues or questions:
- Check the implementation files listed in this document
- Review the Postman collection test cases
- Ensure all migrations and seeders have run successfully

## Version History

- **v1.0.0** (2026-01-24) - Initial implementation
  - Database schema
  - Admin CRUD interface
  - API endpoints
  - Postman collection
  - Complete documentation
