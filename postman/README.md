# Marketplace Nigeria API - Postman Collection

Complete Postman collection with all 135 API endpoints for local and production testing.

**✨ NEW: All requests include sample data - ready to test immediately!**

## 📦 What's Included

- **Collection File**: `Marketplace-API-Complete.postman_collection.json` (135 endpoints with sample data)
- **Local Environment**: `Local-Environment.postman_environment.json`
- **Production Environment**: `Production-Environment.postman_environment.json`
- **Sample Data Guide**: `SAMPLE_DATA_EXAMPLES.md` (Complete reference of all sample data)
- **Quick Start**: `QUICK_START.md` (3-minute setup guide)

## 🚀 Quick Start

### Step 1: Import Collection

1. Open Postman
2. Click **Import** button
3. Select `Marketplace-API-Complete.postman_collection.json`
4. Collection will appear in your sidebar

### Step 2: Import Environment

**For Local Testing:**
1. Click **Import** button
2. Select `Local-Environment.postman_environment.json`
3. Select "Marketplace Nigeria - Local" from environment dropdown

**For Production Testing:**
1. Click **Import** button
2. Select `Production-Environment.postman_environment.json`
3. Update your credentials in the environment
4. Select "Marketplace Nigeria - Production" from environment dropdown

### Step 3: Get Authentication Token

1. Open `01. Authentication` folder
2. Click `Login` request
3. **Sample data is already there!** Just update email/password if needed:
   ```json
   {
     "email": "john.doe@example.com",
     "password": "password123"
   }
   ```
4. Click **Send**
5. Token will be **automatically saved** to environment
6. All subsequent requests will use this token

**🎉 All requests include sample data - no manual editing needed!**

## 📁 Collection Structure

The collection is organized into logical folders:

```
Marketplace Nigeria API - Complete
├── 01. Authentication (10 endpoints)
│   ├── Register
│   ├── Login
│   ├── Verify OTP
│   ├── Forgot Password
│   └── ...
├── 02. Adverts (17 endpoints)
│   ├── Get All Adverts
│   ├── Get Advert Details
│   ├── Featured Adverts
│   └── ...
├── 03. Categories (4 endpoints)
├── 04. Search (8 endpoints)
├── 05. Locations (6 endpoints)
├── 06. Messages (11 endpoints)
├── 07. Payments (6 endpoints)
├── 08. Ad Boosts (6 endpoints)
├── 09. Notifications (5 endpoints)
├── 10. Testing - Local Only (2 endpoints)
├── 11. User Management (48 endpoints)
│   ├── User Dashboard
│   ├── User Adverts
│   ├── User Profile
│   ├── User Settings
│   └── ...
└── 12. Real-time (1 endpoint)
```

## 🔑 Authentication

### Automatic Token Management

The collection automatically:
- ✅ Saves auth token after login
- ✅ Applies token to all protected endpoints
- ✅ Shows which endpoints require authentication (🔒 icon)

### Manual Token Setup (if needed)

1. Login via `POST /api/login`
2. Copy the `token` from response
3. Go to Environment variables
4. Set `auth_token` = your_token

## 🌍 Environments

### Local Environment Variables

| Variable | Default Value | Description |
|----------|---------------|-------------|
| `base_url` | http://127.0.0.1:8030 | Local API URL |
| `auth_token` | (empty) | Auto-filled after login |
| `test_email` | test@example.com | Test account email |
| `test_password` | password | Test account password |
| `user_id` | (empty) | Current user ID |
| `advert_id` | (empty) | Test advert ID |
| `device_token` | (empty) | FCM device token |

### Production Environment Variables

| Variable | Default Value | Description |
|----------|---------------|-------------|
| `base_url` | https://www.marketplace.ng | Production API URL |
| `auth_token` | (empty) | Auto-filled after login |
| `test_email` | your-email@example.com | ⚠️ Update this |
| `test_password` | your-password | ⚠️ Update this |
| `user_id` | (empty) | Current user ID |
| `advert_id` | (empty) | Test advert ID |
| `device_token` | (empty) | FCM device token |

## 📝 Usage Examples

### Example 1: Basic Workflow

```
1. Login
   POST /api/login
   Body: {"email": "{{test_email}}", "password": "{{test_password}}"}
   → Token auto-saved

2. Get Adverts
   GET /api/adverts
   → Returns all adverts

3. Get My Profile
   GET /api/user/profile
   → Returns your profile (uses auto-saved token)
```

### Example 2: Create and Manage Advert

```
1. Login (get token)

2. Create Advert
   POST /api/user/adverts
   Body: {
     "title": "New Item",
     "price": 5000,
     "category": 4,
     ...
   }

3. Get My Adverts
   GET /api/user/adverts

4. Update Advert
   PUT /api/user/adverts/{id}

5. Mark as Sold
   PATCH /api/user/adverts/{id}/mark-sold
```

### Example 3: Test Push Notifications

```
1. Login

2. Register Device Token
   POST /api/device-tokens
   Body: {"token": "test-token-123", "platform": "android"}

3. Send Test Notification
   POST /api/test/notification
   Body: {"message": "Test notification"}

4. View My Tokens
   GET /api/test/my-tokens
```

## 🎯 Endpoint Types

### 🌐 Public Endpoints (No Auth Required)
- `GET /api/adverts` - Get all adverts
- `GET /api/categories` - Get categories
- `POST /api/register` - Register account
- `POST /api/login` - Login
- And more...

### 🔒 Protected Endpoints (Auth Required)
- `GET /api/user/profile` - Get user profile
- `POST /api/user/adverts` - Create advert
- `POST /api/messages` - Send message
- `GET /api/user/dashboard` - Get dashboard
- Most user-specific endpoints

### 🧪 Test Endpoints (Local Only)
- `POST /api/test/notification` - Send test notification
- `GET /api/test/my-tokens` - Get registered tokens

*Test endpoints automatically disabled in production*

## 🔧 Request Examples

### POST Request with Body

```json
POST {{base_url}}/api/register
Headers:
  Content-Type: application/json
  Accept: application/json
Body:
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+2348012345678",
  "password": "password123"
}
```

### GET Request with Auth

```
GET {{base_url}}/api/user/profile
Headers:
  Authorization: Bearer {{auth_token}}
  Accept: application/json
```

### PUT Request with Parameters

```json
PUT {{base_url}}/api/user/adverts/123
Headers:
  Authorization: Bearer {{auth_token}}
  Content-Type: application/json
Body:
{
  "title": "Updated Title",
  "price": 10000
}
```

## 📊 Testing Workflow

### Local Development Testing

1. **Setup**
   - Import collection and local environment
   - Start local server: `php artisan serve --port=8030`

2. **Authentication**
   - Register new account OR
   - Login with existing account
   - Token auto-saved

3. **Test Endpoints**
   - Browse through folders
   - Click endpoints to see details
   - Click "Send" to test
   - View responses

4. **Debug**
   - Check response status
   - View response body
   - Check Laravel logs if needed

### Production Testing

1. **Setup**
   - Import production environment
   - Update `test_email` and `test_password`

2. **Caution**
   - Use test account only
   - Don't create spam data
   - Test endpoints are disabled

3. **Test Flow**
   - Login first
   - Test read-only endpoints
   - Test write endpoints carefully
   - Verify data in app/web

## 🔄 Updating the Collection

### Auto-Generate Updated Collection

Run the generator script when routes change:

```bash
php generate-postman-collection.php
```

This will:
- ✅ Scan all current API routes
- ✅ Update the collection file
- ✅ Preserve your environment files
- ✅ Organize endpoints by category

### Manual Updates

1. Edit `generate-postman-collection.php` if needed
2. Run the script
3. Re-import the collection in Postman
4. Existing requests will be updated

## 📱 Features

### Auto-Save Token
- Login response automatically saves token
- No manual copy-paste needed
- Token used for all protected endpoints

### Request Descriptions
- Each request shows authentication requirement
- Controller and method information included
- Endpoint path clearly displayed

### Environment Variables
- Easy switching between local/production
- Reusable test credentials
- Save common IDs for quick testing

### Organized Folders
- Logical grouping by feature
- Easy navigation
- Quick find with search

## 🐛 Troubleshooting

### Issue: Requests failing with 401

**Solution:**
1. Check if you're logged in
2. Verify `auth_token` in environment
3. Check token hasn't expired
4. Re-login if needed

### Issue: Wrong base URL

**Solution:**
1. Select correct environment (Local/Production)
2. Verify `base_url` variable
3. Check server is running (for local)

### Issue: Token not auto-saving

**Solution:**
1. Check test script in collection
2. Verify response has `data.token` field
3. Manually set `auth_token` if needed

### Issue: 404 Not Found

**Solution:**
1. Verify route exists: `php artisan route:list --path=api/...`
2. Check for typos in URL
3. Regenerate collection if routes changed
4. Clear route cache: `php artisan route:clear`

### Issue: Test endpoints return 403

**Reason:** Test endpoints only work in local environment

**Solution:**
- Use local environment
- Check `APP_ENV=local` in `.env`

## 📚 Additional Resources

- **API Documentation**: http://127.0.0.1:8030/docs
- **OpenAPI Spec**: `public/docs/openapi.yaml`
- **Collection in Docs**: `public/docs/collection.json` (auto-generated by Scribe)

## 🔐 Security Notes

### Local Testing
- ✅ Use test accounts only
- ✅ Don't commit credentials
- ✅ Keep tokens private

### Production Testing
- ⚠️ Use dedicated test account
- ⚠️ Don't test destructive operations
- ⚠️ Monitor for any issues
- ⚠️ Test endpoints disabled automatically

## 📋 Quick Reference

### Common Endpoints

| Endpoint | Method | Auth | Purpose |
|----------|--------|------|---------|
| /api/register | POST | ❌ | Create account |
| /api/login | POST | ❌ | Get auth token |
| /api/adverts | GET | ❌ | List all adverts |
| /api/adverts/{id} | GET | ❌ | Get advert details |
| /api/user/profile | GET | ✅ | Get my profile |
| /api/user/adverts | GET | ✅ | Get my adverts |
| /api/user/adverts | POST | ✅ | Create advert |
| /api/messages | POST | ✅ | Send message |
| /api/test/notification | POST | ✅ | Test notification (local) |

### Environment Quick Switch

**Local → Production:**
1. Click environment dropdown
2. Select "Marketplace Nigeria - Production"
3. Update credentials if needed

**Production → Local:**
1. Click environment dropdown
2. Select "Marketplace Nigeria - Local"

## 🎉 Tips & Tricks

1. **Use Variables**: Save IDs to variables for quick reuse
   ```javascript
   // In Tests tab:
   pm.environment.set("advert_id", pm.response.json().data.id);
   ```

2. **Bulk Testing**: Use Postman Runner to test multiple endpoints

3. **Share Collection**: Export and share with team members

4. **Pre-request Scripts**: Add custom logic before requests

5. **Environment-Specific Data**: Use different test data per environment

## 📞 Support

Having issues?
1. Check this README
2. Review API documentation
3. Check Laravel logs
4. Verify environment configuration

---

**Version**: 3.0
**Last Updated**: 2026-01-13
**Total Endpoints**: 135
**Total Folders**: 37

**Happy Testing! 🚀**
