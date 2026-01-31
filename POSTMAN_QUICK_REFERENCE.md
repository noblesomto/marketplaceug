# Postman Collection Update - Quick Reference

## New Endpoints to Test

### 1. Get Boost Options
```
GET {{base_url}}/api/boost/options
Headers:
  Authorization: Bearer {{auth_token}}
  Accept: application/json
```

### 2. Calculate Boost Price
```
POST {{base_url}}/api/boost/calculate
Headers:
  Authorization: Bearer {{auth_token}}
  Content-Type: application/json
Body:
{
    "package_id": 1,
    "duration_days": 7,
    "advert_id": 123
}
```

---

## Updated Endpoints (Variable Changes)

| Old Endpoint | New Endpoint |
|-------------|-------------|
| `/boosts/{boostId}` | `/boosts/{{boost_id}}` |
| `/boosts/user/{userId}` | `/boosts/user/{{user_id}}` |
| `/adverts/{advertId}/boost-info` | `/adverts/{{advert_id}}/boost-info` |
| `/adverts/{advertId}/boost-status` | `/adverts/{{advert_id}}/boost-status` |
| `/adverts/{advertId}/boost` | `/adverts/{{advert_id}}/boost` |
| `/messages/conversation/{advertId}/{receiverId}` | `/messages/conversation/{{advert_id}}/{{receiver_id}}` |
| `/messages/advert/{advertId}` | `/messages/advert/{{advert_id}}` |

---

## Required Postman Environment Variables

Make sure your Postman environment has these variables defined:

| Variable | Example Value | Description |
|----------|--------------|-------------|
| `base_url` | `https://api.example.com` | Base API URL |
| `auth_token` | `eyJ0eXAiOiJKV1QiLCJhbGc...` | JWT authentication token |
| `user_id` | `1` | User ID for testing |
| `advert_id` | `123` | Advert ID for testing |
| `boost_id` | `1` | Boost ID for testing |
| `receiver_id` | `456` | Receiver user ID for messages |

---

## Files Changed

- **Updated:** `/home/www/laravel/marketplace/postman/Marketplace-API-Complete.postman_collection.json`
- **Backup:** `/home/www/laravel/marketplace/postman/Marketplace-API-Complete.postman_collection.json.backup`
- **Script:** `/home/www/laravel/marketplace/update_postman.py`

---

## Quick Test Checklist

- [ ] Import updated collection into Postman
- [ ] Set up environment variables
- [ ] Test GET /boost/options
- [ ] Test POST /boost/calculate
- [ ] Verify existing boost endpoints work with {{boost_id}}
- [ ] Verify message endpoints work with {{advert_id}} and {{receiver_id}}
- [ ] Confirm only one "Create New Advert" endpoint exists
