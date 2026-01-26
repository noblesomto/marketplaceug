# Marketplace Nigeria - Postman Collection Summary

## 🎉 Collection Complete!

This document summarizes the complete, production-ready Postman collection for Marketplace Nigeria API.

---

## 📦 What's Included

### 1. Main Collection File
**File:** `Marketplace_Nigeria_API_Complete.postman_collection.json`

- **Total Folders:** 14
- **Total Endpoints:** 89
- **Features:**
  - Automatic token management
  - Built-in test scripts
  - Environment variable extraction
  - Organized by functionality
  - Sample request bodies included

### 2. Environment Files

**Location:** `environments/` folder

#### Local Environment
- **File:** `Local.postman_environment.json`
- **Base URL:** `http://127.0.0.1:8030`
- **Purpose:** Local development and testing
- **Variables:** 17 pre-configured variables

#### Production Environment
- **File:** `Production.postman_environment.json`
- **Base URL:** `https://www.marketplace.ng`
- **Purpose:** Live API testing
- **Variables:** Same 17 variables as Local

### 3. Documentation Files

1. **README.md** - Quick start guide
2. **POSTMAN_SETUP_GUIDE.md** - Detailed setup instructions with troubleshooting
3. **API_ENDPOINTS_REFERENCE.md** - Complete endpoint reference (127+ endpoints documented)
4. **COLLECTION_SUMMARY.md** - This file

---

## 📊 Endpoint Breakdown by Category

| # | Folder Name | Endpoints | Description |
|---|-------------|-----------|-------------|
| 1 | Authentication | 8 | Register, Login, OTP, Password Reset, Social Auth |
| 2 | Adverts (Public) | 5 | Browse, View, Featured, Report |
| 3 | Categories & Browse | 7 | Categories, Subcategories, Brands, Models, Locations |
| 4 | Location & Shipping | 4 | States, Cities, Shipping Calculator |
| 5 | Search & Filter | 3 | Basic Search, Advanced Filter, Suggestions |
| 6 | User Dashboard | 9 | Dashboard, Ads Management, Wishlist, Following |
| 7 | Advert Management | 5 | Create, Read, Update, Delete Adverts |
| 8 | Messages | 6 | Conversations, Send, Read Status, Unread Count |
| 9 | Payments | 5 | Initialize, Track, Confirm Delivery, Shipping Status |
| 10 | Boost Management | 8 | Options, Calculate, Create, Status, History |
| 11 | User Profile | 12 | Profile, Address, Phone, Verification, Payment Info, Settings |
| 12 | Block Users | 4 | Block, Unblock, List Blocked, Check Status |
| 13 | Statistics & Analytics | 7 | Stats, Notifications, Followers, Feedback |
| 14 | Device Tokens & Notifications | 6 | Register Token, FCM, Notification Settings |
| **TOTAL** | **14 Folders** | **89 Endpoints** | **Complete API Coverage** |

---

## 🔑 Key Features

### Automatic Token Management
After successful login/register, the collection automatically:
- Extracts `access_token` from response
- Saves to environment variable
- Uses in all subsequent protected requests
- No manual token copying needed!

### Built-in Tests
Every request includes validation tests:
- HTTP status code checks
- Response structure validation
- Required field verification
- Auto-extraction of useful IDs

### Environment Variables
Auto-saved variables include:
- `access_token` - Bearer token
- `user_id` - Current user ID
- `advert_id` - Last viewed advert
- `seller_id` - Last viewed seller
- `category_id`, `subcategory_id`, `brand_id`
- `message_id`, `payment_id`, `boost_id`
- And more...

---

## 🚀 Quick Start (3 Steps)

### Step 1: Import Everything
1. Open Postman
2. Click **Import**
3. Select these files:
   - `Marketplace_Nigeria_API_Complete.postman_collection.json`
   - `environments/Local.postman_environment.json`
   - `environments/Production.postman_environment.json`

### Step 2: Select Environment
- Click environment dropdown (top right)
- Select **"Marketplace Nigeria - Local"** or **"Production"**

### Step 3: Login
1. Navigate to: **1. Authentication > Login**
2. Update email/password in Body
3. Click **Send**
4. ✅ Token auto-saved!
5. All other requests now work automatically!

---

## 📱 Mobile App Integration

### Recommended Flow

#### 1. Initial Setup
```
GET /api/boost/options         → Get boost types and durations
GET /api/categories            → Get all categories
GET /api/locations/states      → Get all states
```

#### 2. Authentication
```
POST /api/register             → Create account
POST /api/verify-otp           → Verify OTP
POST /api/login                → Login (get token)
```

#### 3. Browse & Search
```
GET /api/adverts               → List adverts
GET /api/adverts/{id}          → View details
POST /api/search/filter        → Advanced search
```

#### 4. User Actions
```
POST /api/messages             → Contact seller
POST /api/user/wishlist/{id}   → Save to wishlist
POST /api/user/following/toggle → Follow seller
```

#### 5. Create Advert
```
GET /api/adverts/create/data   → Get form data
POST /api/adverts              → Create advert (with images)
POST /api/adverts/{id}/boost   → Boost advert
```

#### 6. Payments
```
POST /api/payments/initialize  → Start payment
POST /api/user/payments/{id}/confirm-delivery → Confirm received
```

---

## 🔐 Authentication Header Format

For all protected endpoints (marked "Yes" in Auth column):

```http
Authorization: Bearer {access_token}
Accept: application/json
Content-Type: application/json
```

In Postman:
- Collection handles this automatically
- Uses `{{access_token}}` variable
- Auto-set after login

---

## 📝 Common Request Examples

### Login
```json
POST /api/login
{
  "email": "john@example.com",
  "password": "Password123!"
}
```

### Create Advert
```
POST /api/adverts
Content-Type: multipart/form-data

Fields:
- ad_title: "iPhone 13 Pro Max"
- category: 1
- subcategory: 6
- brand: 15
- price: 450000
- description: "Brand new in box"
- state: 25
- lga: "Ikeja"
- images[]: [file]
- images[]: [file]
```

### Advanced Search
```json
POST /api/search/filter
{
  "category": 1,
  "subcategory": 6,
  "min_price": 100000,
  "max_price": 500000,
  "state": "lagos",
  "condition": "new",
  "brand": 15
}
```

### Calculate Boost Price
```json
POST /api/boost/calculate
{
  "boost_type_id": 1,
  "duration_id": 3
}
```

### Send Message
```json
POST /api/messages
{
  "advert_id": "123",
  "receiver_id": "USR456",
  "message": "Is this still available?"
}
```

---

## 🎯 Response Formats

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    // Response data
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### Paginated Response
```json
{
  "data": [...],
  "current_page": 1,
  "last_page": 10,
  "per_page": 20,
  "total": 195,
  "from": 1,
  "to": 20
}
```

---

## 🛠️ Testing Workflow

### Manual Testing
1. Select request
2. Update variables/body if needed
3. Click **Send**
4. Check **Test Results** tab
5. Verify response data

### Automated Testing (Collection Runner)
1. Right-click collection name
2. Select **"Run collection"**
3. Select environment
4. Choose folders/requests
5. Click **Run**
6. View results summary

---

## 📖 Documentation Reference

| Document | Purpose |
|----------|---------|
| **README.md** | Quick overview and setup |
| **POSTMAN_SETUP_GUIDE.md** | Detailed setup, troubleshooting, best practices |
| **API_ENDPOINTS_REFERENCE.md** | Complete list of all 127+ API endpoints |
| **COLLECTION_SUMMARY.md** | This document - overview of collection |

---

## 💡 Pro Tips

1. **Use Collection Runner** for regression testing
2. **Export results** to share with team
3. **Check Test Results** tab after every request
4. **Use Postman Console** (View > Show Console) for debugging
5. **Save examples** of successful responses for reference
6. **Never hardcode IDs** - always use variables like `{{advert_id}}`
7. **Test in Local first** before Production
8. **Run Login again** if you get 401 errors

---

## 🔧 Troubleshooting

### 401 Unauthorized
→ Run Login request again to refresh token

### 422 Validation Error
→ Check response errors object for missing/invalid fields

### 404 Not Found
→ Verify ID variables are set correctly

### 500 Server Error
→ Check environment selection (Local vs Production)
→ Verify server is running (for Local)

### Variables Not Saving
→ Check Test Results tab
→ View Postman Console for errors
→ Re-import collection if needed

---

## ✅ Validation Checklist

Before using the collection, verify:

- [ ] Collection imported successfully
- [ ] Both environments imported
- [ ] Environment selected (Local or Production)
- [ ] Login request works
- [ ] Token auto-saved to environment
- [ ] Protected endpoints work with token
- [ ] Test scripts running (check Test Results tab)
- [ ] Variables auto-extracting from responses

---

## 🎉 You're Ready!

The Marketplace Nigeria Postman collection is complete and production-ready with:

✅ 89 endpoints across 14 categories
✅ Automatic authentication
✅ Built-in validation tests
✅ Two environments (Local & Production)
✅ Comprehensive documentation
✅ Sample requests and responses
✅ Mobile app integration examples

**Start testing and building amazing features!** 🚀

---

## 📞 Support

For API issues:
- Check **POSTMAN_SETUP_GUIDE.md** for detailed troubleshooting
- Review **API_ENDPOINTS_REFERENCE.md** for endpoint details
- Contact backend team with:
  - Environment used
  - Request details
  - Full error response
  - Steps to reproduce

---

*Last Updated: January 24, 2026*
*Marketplace Nigeria - Complete API Collection v1.0*
