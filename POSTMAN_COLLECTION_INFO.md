# Postman Collection - Complete with Sample Data

## ✅ What You Have

Your complete Postman collection is ready in the `postman/` folder with **all 135 API endpoints** and **sample request data** for testing.

### 📂 Files in `postman/` Folder

```
postman/
├── Marketplace-API-Complete.postman_collection.json (254KB)
│   └── 135 endpoints with sample data
├── Local-Environment.postman_environment.json
│   └── Environment for local testing
├── Production-Environment.postman_environment.json
│   └── Environment for production testing
├── QUICK_START.md
│   └── 3-minute setup guide
├── README.md
│   └── Complete documentation
└── SAMPLE_DATA_EXAMPLES.md
    └── All sample data reference
```

## 🎯 Quick Import (30 seconds)

1. **Open Postman**
2. **Click "Import"** button
3. **Drag these files:**
   - `Marketplace-API-Complete.postman_collection.json`
   - `Local-Environment.postman_environment.json`
   - `Production-Environment.postman_environment.json`
4. **Done!** Ready to test

## ✨ What's Included

### All Requests Have Sample Data!

**Authentication:**
```json
// Login
{
  "email": "john.doe@example.com",
  "password": "password123"
}

// Register
{
  "name": "John Doe",
  "email": "john.doe@example.com",
  "phone": "+2348012345678",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Create Advert:**
```json
{
  "title": "Samsung Galaxy S24",
  "description": "Brand new Samsung Galaxy S24...",
  "price": 500000,
  "category": "4",
  "sub_category": "6",
  "state": "Lagos",
  ...
}
```

**Send Message:**
```json
{
  "receiver_id": "12345",
  "advert_id": "67890",
  "body": "Hello, is this item still available?"
}
```

**And 50+ more endpoints with sample data!**

## 🚀 Test Your First Request (60 seconds)

1. **Select Environment**: "Marketplace Nigeria - Local"
2. **Open** `01. Authentication` → `Login`
3. **Update** email/password (or use sample)
4. **Click Send**
5. ✅ **Token automatically saved!**
6. **Test any endpoint** - authentication works automatically!

## 📊 Collection Stats

| Feature | Details |
|---------|---------|
| **Total Endpoints** | 135 |
| **Folders** | 37 organized folders |
| **Sample Data** | ✅ All POST/PUT/PATCH requests |
| **Auto Token** | ✅ Saves after login |
| **Environments** | Local + Production |
| **Documentation** | Complete guides included |

## 📁 Organized Folders

```
├── 01. Authentication (10 endpoints)
├── 02. Adverts (17 endpoints)
├── 03. Categories (4 endpoints)
├── 04. Search (8 endpoints)
├── 05. Locations (6 endpoints)
├── 06. Messages (11 endpoints)
├── 07. Payments (6 endpoints)
├── 08. Ad Boosts (6 endpoints)
├── 09. Notifications (5 endpoints)
├── 10. Testing - Local Only (2 endpoints)
├── 11. User Management (48 endpoints)
└── 12. Real-time (1 endpoint)
```

## 🌍 Dual Environment Support

### Local (Default)
- **URL**: `http://127.0.0.1:8030`
- **Ready to use** - just start your server
- **Test credentials** pre-filled

### Production
- **URL**: `https://www.marketplace.ng`
- **Update credentials** before testing
- **Same collection** - just switch environment

## 💡 Key Features

### 1. Auto-Save Token ✨
Login once → Token saved → All protected endpoints work automatically

### 2. Sample Data 📝
Every POST/PUT/PATCH request includes ready-to-use sample data

### 3. Smart Variables 🔧
- `{{base_url}}` - Automatically switches with environment
- `{{auth_token}}` - Auto-saved after login
- `{{user_id}}` - Auto-saved from responses
- `{{advert_id}}` - Auto-saved when creating adverts

### 4. Complete Documentation 📖
- Quick Start guide
- Full README with troubleshooting
- Sample data reference
- Testing workflows

## 🎓 Common Workflows

### Workflow 1: Browse Adverts (Public)
```
1. GET /api/adverts
2. GET /api/adverts/{id}
3. GET /api/categories
```
No authentication needed!

### Workflow 2: Create & Manage (Protected)
```
1. POST /api/login              → Token saved ✅
2. POST /api/user/adverts       → Sample data included ✅
3. GET /api/user/adverts        → View your adverts
4. PUT /api/user/adverts/{id}   → Update with sample data ✅
```

### Workflow 3: Messaging
```
1. POST /api/login              → Token saved
2. POST /api/messages           → Sample data included
3. GET /api/messages/conversations
4. PUT /api/messages/{id}/read
```

### Workflow 4: Test Notifications
```
1. POST /api/login              → Token saved
2. POST /api/device-tokens      → Sample data included
3. POST /api/test/notification  → Sample data included
4. Check your device! 📱
```

## 📖 Documentation

### Quick Start
Read: `postman/QUICK_START.md`
- 3-minute setup
- First request walkthrough
- Common issues solved

### Complete Guide
Read: `postman/README.md`
- All features explained
- Detailed troubleshooting
- Tips & tricks

### Sample Data Reference
Read: `postman/SAMPLE_DATA_EXAMPLES.md`
- All sample data shown
- Field explanations
- Common values reference

## 🐛 Troubleshooting

| Issue | Solution |
|-------|----------|
| **No sample data?** | Re-import latest collection |
| **401 Error** | Login first to get token |
| **Token not saving** | Check environment is selected |
| **Wrong URL** | Select correct environment |
| **Connection refused** | Start server: `php artisan serve --port=8030` |

## 🔄 Regenerating Collection

If routes change in the future:

```bash
# Regenerate with updated routes
php artisan scribe:generate

# Or use the Scribe-generated collection
# Location: public/docs/collection.json
```

The collection in `public/docs/collection.json` is auto-generated by Scribe but doesn't include sample data. The one in `postman/` includes complete sample data for all endpoints.

## ✅ Ready to Test!

**You have:**
- ✅ Complete collection with 135 endpoints
- ✅ Sample data for all requests
- ✅ Auto token management
- ✅ Dual environment support
- ✅ Complete documentation

**Just import and start testing!** 🚀

## 📞 Support

- **Setup questions?** → Read `QUICK_START.md`
- **Sample data format?** → Read `SAMPLE_DATA_EXAMPLES.md`
- **Troubleshooting?** → Read `README.md`
- **API docs?** → Visit `http://127.0.0.1:8030/docs`

---

**Generated**: 2026-01-13
**Version**: 3.0
**Total Endpoints**: 135
**Sample Data**: ✅ Included

🎉 **Happy Testing!**
