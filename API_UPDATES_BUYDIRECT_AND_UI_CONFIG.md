# API Updates: Buy Direct & Category UI Config

## Overview
Added missing Buy Direct purchase endpoints to API routes and Postman collection, plus comprehensive Category UI Config documentation for mobile team.

## 1. Buy Direct Endpoints

### Routes Added to `routes/api.php`

**Location:** Lines 97-100 (Protected routes with `auth:sanctum`)

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/adverts/{id}/report', [AdvertController::class, 'reportAdvert']);
    Route::post('/adverts/{id}/apply', [AdvertController::class, 'applyJob']);
    Route::get('/adverts/{id}/buy-direct', [AdvertController::class, 'buy_direct']);
    Route::post('/adverts/{id}/buy-direct-payment', [AdvertController::class, 'buy_direct_payment']);
});
```

### Endpoint Details

#### 1.1 GET /api/adverts/{id}/buy-direct

**Controller:** `Api\AdvertController@buy_direct` (line 933)

**Authentication:** Required (Bearer token)

**Purpose:** Get initial details for direct purchase flow

**Response:**
```json
{
  "success": true,
  "data": {
    "ad": {
      "id": 1,
      "ad_title": "Product Name",
      "ad_price": 50000,
      "buy_direct": "Yes",
      "images": [...],
      "shippings": [...]
    },
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "states": [
      {"id": 1, "name": "Lagos"},
      {"id": 2, "name": "Abuja"}
    ]
  }
}
```

**Mobile Implementation:**
1. User clicks "Buy Direct" button on ad
2. App calls this endpoint with ad ID
3. Display purchase form with:
   - Ad details (title, price, images)
   - Shipping address form
   - State/city dropdowns (from states array)
4. Collect shipping information from user

#### 1.2 POST /api/adverts/{id}/buy-direct-payment

**Controller:** `Api\AdvertController@buy_direct_payment` (line 970)

**Authentication:** Required (Bearer token)

**Purpose:** Get payment details with shipping information

**Request Body:**
```json
{
  "shipping_method_id": 1,
  "shipping_data": {
    "recipient_name": "John Doe",
    "recipient_phone": "08012345678",
    "delivery_address": "123 Main Street, Ikeja",
    "state": "Lagos",
    "city": "Ikeja",
    "postal_code": "100001"
  }
}
```

**Validation:**
- `shipping_method_id`: required, integer
- `shipping_data`: required, object
  - Contains recipient details and delivery address

**Response:**
```json
{
  "success": true,
  "data": {
    "ad": {...},
    "user": {...},
    "shipping_method": {
      "id": 1,
      "name": "Express Delivery",
      "cost": 2500,
      "duration": "1-2 days"
    },
    "shipping_data": {
      "recipient_name": "John Doe",
      "recipient_phone": "08012345678",
      "delivery_address": "123 Main Street, Ikeja",
      "state": "Lagos",
      "city": "Ikeja",
      "postal_code": "100001"
    }
  }
}
```

**Mobile Implementation:**
1. User fills shipping form and selects shipping method
2. App calls this endpoint with shipping data
3. Display payment confirmation page showing:
   - Item total
   - Shipping cost
   - Total amount
   - Delivery details for confirmation
4. User confirms and proceeds to payment gateway

### Buy Direct Flow (Complete)

```
1. User views ad with "Buy Direct: Yes"
   ↓
2. User clicks "Buy Direct" button
   ↓
3. GET /api/adverts/{id}/buy-direct
   → Receive ad details, user info, states list
   ↓
4. User fills shipping form (name, phone, address, state, city)
   ↓
5. User selects shipping method from available options
   ↓
6. POST /api/adverts/{id}/buy-direct-payment
   → Send shipping data and method ID
   → Receive total calculation and confirmation data
   ↓
7. Display payment confirmation page
   ↓
8. User confirms and proceeds to payment gateway
   (Payment integration handled separately)
```

---

## 2. Category UI Config Endpoints

### Already Available in API ✅

**Routes:** Lines 134-137 in `routes/api.php`

```php
Route::get('/ui-config/all', [CategoryUIController::class, 'getUIConfig']);
Route::get('/ui-config/category/{id}', [CategoryUIController::class, 'getCategoryConfig']);
Route::get('/ui-config/subcategory/{id}', [CategoryUIController::class, 'getSubcategoryConfig']);
Route::post('/ui-config/clear-cache', [CategoryUIController::class, 'clearCache']);
```

### Endpoint Details

#### 2.1 GET /api/ui-config/all

**Purpose:** Get all UI configurations for all categories and subcategories

**Authentication:** Not required (public endpoint)

**Cache:** 24 hours server-side

**Response:**
```json
{
  "success": true,
  "data": {
    "categories": {
      "1": {
        "show": ["salary"],
        "hide": ["price", "shipment", "itemCondition", "buyDirect"],
        "labels": {
          "brand": "Select Job Type:"
        }
      },
      "2": {
        "show": ["price", "quantity", "shipment"],
        "hide": ["salary", "expectedSalary"],
        "labels": {
          "brand": "Select Brand:"
        }
      }
    },
    "subcategories": {
      "1": {
        "show": ["divCar", "divModel"],
        "hide": ["divPhone"],
        "labels": {
          "brand": "Select Car Brand:"
        },
        "required": ["model"]
      }
    }
  },
  "cache_expires_at": "2026-02-01 12:00:00"
}
```

**Mobile Implementation:**
```javascript
// Fetch on app start
async function initializeUIConfig() {
  try {
    const response = await fetch('/api/ui-config/all');
    const config = await response.json();

    // Cache locally for 24 hours
    localStorage.setItem('uiConfig', JSON.stringify(config.data));
    localStorage.setItem('uiConfigExpiry', config.cache_expires_at);

    return config.data;
  } catch (error) {
    // Use embedded fallback config
    return getEmbeddedFallbackConfig();
  }
}

// Check if cache is still valid
function isConfigCacheValid() {
  const expiry = localStorage.getItem('uiConfigExpiry');
  return expiry && new Date(expiry) > new Date();
}
```

#### 2.2 GET /api/ui-config/category/{id}

**Purpose:** Get UI configuration for a specific category

**Authentication:** Not required

**Example:** `/api/ui-config/category/1`

**Response:**
```json
{
  "success": true,
  "data": {
    "show": ["salary"],
    "hide": ["price", "shipment", "itemCondition"],
    "labels": {
      "brand": "Select Job Type:"
    }
  }
}
```

**Mobile Implementation:**
```javascript
// When user selects category
function onCategorySelect(categoryId) {
  const config = uiConfig.categories[categoryId];

  // Hide fields
  config.hide.forEach(field => {
    document.querySelector(`#${field}`).style.display = 'none';
  });

  // Show fields
  config.show.forEach(field => {
    document.querySelector(`#${field}`).style.display = 'block';
  });

  // Apply custom labels
  if (config.labels?.brand) {
    document.querySelector('#brandLabel').textContent = config.labels.brand;
  }
}
```

#### 2.3 GET /api/ui-config/subcategory/{id}

**Purpose:** Get UI configuration for a specific subcategory

**Authentication:** Not required

**Example:** `/api/ui-config/subcategory/1`

**Response:**
```json
{
  "success": true,
  "data": {
    "show": ["divCar", "divModel"],
    "hide": ["divPhone"],
    "labels": {
      "brand": "Select Car Brand:"
    },
    "required": ["model"]
  }
}
```

**Important:** Subcategory rules are ADDITIVE to category rules!

**Mobile Implementation:**
```javascript
// When user selects subcategory (after category)
function onSubcategorySelect(subcategoryId) {
  const subConfig = uiConfig.subcategories[subcategoryId];

  // Apply subcategory show/hide ON TOP of category rules
  subConfig.hide?.forEach(field => {
    document.querySelector(`#${field}`).style.display = 'none';
  });

  subConfig.show?.forEach(field => {
    document.querySelector(`#${field}`).style.display = 'block';
  });

  // Override labels if specified
  if (subConfig.labels?.brand) {
    document.querySelector('#brandLabel').textContent = subConfig.labels.brand;
  }

  // Mark required fields
  subConfig.required?.forEach(field => {
    const input = document.querySelector(`[name="${field}"]`);
    input.setAttribute('required', true);
    input.classList.add('required');
  });
}
```

#### 2.4 POST /api/ui-config/clear-cache

**Purpose:** Clear the UI config cache (admin use)

**Authentication:** Not enforced but intended for admin

**Response:**
```json
{
  "success": true,
  "message": "UI config cache cleared successfully"
}
```

**Use Case:** After admin updates UI config in admin panel, call this to force refresh

---

## 3. Available Form Elements

### Form Fields
- `price` - Price input field
- `salary` - Salary input field
- `expectedSalary` - Expected salary field
- `services` - Services description field
- `quantity` - Quantity input

### Sections
- `divCar` - Car details section (year, mileage, condition)
- `divPhone` - Phone details section (storage, RAM, condition)
- `divModel` - Model dropdown (dependent on brand)

### Options
- `shipment` - Shipment availability options
- `shipping` - Shipping methods
- `itemCondition` - Item condition dropdown
- `buyDirect` - Buy direct option checkbox

---

## 4. Common Category Configurations

### Jobs Category (ID: 1)
```json
{
  "show": ["salary"],
  "hide": ["price", "shipment", "itemCondition", "buyDirect", "expectedSalary", "quantity"],
  "labels": {
    "brand": "Select Job Type:"
  }
}
```

### Services Category (ID: 2)
```json
{
  "show": ["services"],
  "hide": ["price", "salary", "expectedSalary", "quantity", "shipment"],
  "labels": {
    "brand": "Select Service Type:"
  }
}
```

### Vehicles Category (ID: 4)
```json
{
  "show": ["price", "itemCondition", "shipment", "buyDirect"],
  "hide": ["salary", "expectedSalary", "services"],
  "labels": {
    "brand": "Select Brand:"
  }
}
```

### Cars Subcategory (ID: 1)
```json
{
  "show": ["divCar", "divModel"],
  "hide": ["divPhone"],
  "labels": {
    "brand": "Select Car Brand:"
  },
  "required": ["model"]
}
```

### Phones Subcategory (ID: 2)
```json
{
  "show": ["divPhone", "divModel"],
  "hide": ["divCar"],
  "labels": {
    "brand": "Select Phone Brand:"
  },
  "required": ["model"]
}
```

---

## 5. Postman Collection Updates

### Added to Collection

**File:** `postman/Marketplace-API-Complete.postman_collection.json`

**New Section:** "13. Category UI Config" (new folder)

**Added Endpoints:**

1. **In "02. Adverts" section:**
   - `Adverts {Id} Buy Direct` - GET /api/adverts/{id}/buy-direct
   - `Adverts {Id} Buy Direct Payment` - POST /api/adverts/{id}/buy-direct-payment

2. **New "13. Category UI Config" section:**
   - `UI Config All` - GET /api/ui-config/all
   - `UI Config Category {CategoryId}` - GET /api/ui-config/category/{id}
   - `UI Config Subcategory {SubcategoryId}` - GET /api/ui-config/subcategory/{id}
   - `UI Config Clear Cache` - POST /api/ui-config/clear-cache

**Total Endpoints:** 138 (6 new endpoints added)

### How to Use Postman Collection

1. **Import Collection:**
   - Open Postman
   - Import `postman/Marketplace-API-Complete.postman_collection.json`

2. **Set Environment Variables:**
   - `base_url`: Your API base URL (e.g., `http://localhost:8030`)
   - `auth_token`: Bearer token for authenticated endpoints

3. **Test Buy Direct Flow:**
   - Navigate to "02. Adverts" folder
   - Run "Adverts {Id} Buy Direct" (change {id} to actual ad ID)
   - Fill shipping data in "Adverts {Id} Buy Direct Payment"
   - Submit to get payment details

4. **Test UI Config:**
   - Navigate to "13. Category UI Config" folder
   - Run "UI Config All" to see all configurations
   - Run individual category/subcategory endpoints with specific IDs

---

## 6. Mobile Team Integration Guide

### Initial Setup (App Launch)

```javascript
// app.js - On app start
async function initializeApp() {
  // 1. Check if cached config is still valid
  if (!isConfigCacheValid()) {
    // 2. Fetch fresh config
    const uiConfig = await fetch('/api/ui-config/all');

    // 3. Cache locally
    localStorage.setItem('uiConfig', JSON.stringify(uiConfig.data));
    localStorage.setItem('uiConfigExpiry', uiConfig.cache_expires_at);
  }

  // 4. Load other app data
  await loadCategories();
  await checkAuthentication();
}
```

### Post Ad / Edit Ad Form

```javascript
// post-ad.js
class AdFormManager {
  constructor() {
    this.uiConfig = JSON.parse(localStorage.getItem('uiConfig'));
  }

  onCategoryChange(categoryId) {
    // Get category config
    const config = this.uiConfig.categories[categoryId];

    // Apply show/hide rules
    this.applyConfig(config);

    // Reset subcategory
    this.selectedSubcategory = null;
  }

  onSubcategoryChange(subcategoryId) {
    // Get subcategory config
    const subConfig = this.uiConfig.subcategories[subcategoryId];

    // Apply ADDITIVE rules
    this.applyConfig(subConfig);

    // Mark required fields
    subConfig.required?.forEach(field => {
      this.markRequired(field);
    });
  }

  applyConfig(config) {
    // Hide elements
    config.hide?.forEach(element => {
      const el = document.getElementById(element);
      if (el) {
        el.style.display = 'none';
        el.querySelectorAll('input, textarea').forEach(input => {
          input.removeAttribute('required');
        });
      }
    });

    // Show elements
    config.show?.forEach(element => {
      const el = document.getElementById(element);
      if (el) el.style.display = 'block';
    });

    // Apply labels
    if (config.labels?.brand) {
      document.querySelector('#brandLabel').textContent = config.labels.brand;
    }
  }

  markRequired(fieldName) {
    const field = document.querySelector(`[name="${fieldName}"]`);
    if (field) {
      field.setAttribute('required', true);
      // Add visual indicator (asterisk, color, etc.)
      const label = document.querySelector(`label[for="${field.id}"]`);
      if (label && !label.textContent.includes('*')) {
        label.textContent += ' *';
      }
    }
  }
}
```

### Buy Direct Flow

```javascript
// buy-direct.js
class BuyDirectManager {
  async initiatePurchase(adId) {
    try {
      // Step 1: Get purchase details
      const response = await fetch(`/api/adverts/${adId}/buy-direct`, {
        headers: {
          'Authorization': `Bearer ${this.authToken}`,
          'Accept': 'application/json'
        }
      });

      const data = await response.json();

      if (data.success) {
        // Display shipping form
        this.showShippingForm(data.data);
      }
    } catch (error) {
      this.showError('Failed to load purchase details');
    }
  }

  async submitShipping(adId, shippingData) {
    try {
      // Step 2: Submit shipping and get payment details
      const response = await fetch(`/api/adverts/${adId}/buy-direct-payment`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${this.authToken}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(shippingData)
      });

      const data = await response.json();

      if (data.success) {
        // Show payment confirmation
        this.showPaymentConfirmation(data.data);
      } else {
        this.showError(data.message);
      }
    } catch (error) {
      this.showError('Failed to process shipping details');
    }
  }

  showPaymentConfirmation(data) {
    // Display:
    // - Item: data.ad.ad_title
    // - Price: data.ad.ad_price
    // - Shipping: data.shipping_method.cost
    // - Total: price + shipping
    // - Delivery to: data.shipping_data.delivery_address
    // - Expected: data.shipping_method.duration
  }
}
```

---

## 7. Testing Checklist

### Buy Direct Endpoints

- [ ] Test GET /api/adverts/{id}/buy-direct with valid ad ID
- [ ] Test with invalid ad ID (should return 404)
- [ ] Test without authentication (should return 401)
- [ ] Test POST /api/adverts/{id}/buy-direct-payment with valid data
- [ ] Test with missing shipping_data (should return 422 validation error)
- [ ] Test with invalid shipping_method_id (should return 404)

### Category UI Config

- [ ] Test GET /api/ui-config/all (should return all configs)
- [ ] Test caching (second request should be faster)
- [ ] Test GET /api/ui-config/category/1 (Jobs category)
- [ ] Test GET /api/ui-config/subcategory/1 (Cars subcategory)
- [ ] Test POST /api/ui-config/clear-cache
- [ ] Verify cache is cleared (fetch /all again should hit database)

### Mobile Integration

- [ ] App fetches UI config on launch
- [ ] Config is cached locally for 24 hours
- [ ] Category selection applies correct show/hide rules
- [ ] Subcategory selection applies additive rules
- [ ] Custom labels display correctly
- [ ] Required fields are marked properly
- [ ] Buy Direct flow works end-to-end
- [ ] Offline support with embedded fallback config

---

## 8. Files Modified

### Routes
- `routes/api.php` - Added 2 buy_direct routes (lines 99-100)

### Controllers
- `app/Http/Controllers/Api/AdvertController.php` - Methods already exist:
  - `buy_direct()` at line 933
  - `buy_direct_payment()` at line 970

### Postman Collection
- `postman/Marketplace-API-Complete.postman_collection.json`
  - Added 2 endpoints to "02. Adverts" section
  - Added new "13. Category UI Config" section with 4 endpoints

### Documentation
- `update_postman_buydirect_ui.py` - Script to update Postman collection
- `API_UPDATES_BUYDIRECT_AND_UI_CONFIG.md` - This file

---

## 9. Next Steps

### For Backend Team
1. ✅ Routes registered in api.php
2. ✅ Controllers implemented
3. ✅ Postman collection updated
4. ⏳ Test endpoints in Postman
5. ⏳ Verify authentication works correctly

### For Mobile Team
1. ⏳ Import updated Postman collection
2. ⏳ Review API documentation (this file)
3. ⏳ Implement UI config caching in mobile app
4. ⏳ Implement dynamic form show/hide logic
5. ⏳ Implement Buy Direct purchase flow
6. ⏳ Test with real API endpoints

### For QA Team
1. ⏳ Test Buy Direct flow end-to-end
2. ⏳ Test UI config with different categories
3. ⏳ Verify subcategory additive rules work
4. ⏳ Test cache expiration and refresh
5. ⏳ Test authentication requirements

---

## 10. Support

For questions or issues:
- Backend API: Review controller methods in `app/Http/Controllers/Api/`
- UI Config: Check admin panel at `/admin/category-ui`
- Postman: Import latest collection from `postman/` folder
- Mobile integration: Reference code examples in Section 6

**Admin Panel Access:**
- URL: `/admin/category-ui`
- Permission: `manage_categories`
- Can configure show/hide rules for any category/subcategory
- Changes cached for 24 hours automatically
