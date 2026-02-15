# Testing Guide - Authentication & Settings Pages

## 🧪 Quick Test Checklist

---

## 1️⃣ AUTHENTICATION FLOW TESTING

### Test 1.1: Standard Login
**URL:** `/login`
**Steps:**
1. Enter email and password
2. Click "Log in" button
3. **Expected:** Redirected to OTP page or dashboard
4. **Check:** Form submits successfully, no 405 errors

### Test 1.2: OTP Verification
**URL:** `/authenticate` (after login)
**Steps:**
1. Enter 6-digit OTP code
2. Click "Verify OTP" button
3. **Expected:** Redirected to dashboard
4. **Check:** Form submits successfully

### Test 1.3: Resend OTP (FIXED) ✅
**URL:** `/authenticate`
**Steps:**
1. On OTP page, click "Resend OTP" button (now a form submit button)
2. **Expected:** New OTP sent, success message displayed
3. **Check:**
   - Form submits with POST method
   - CSRF token included
   - No 405 Method Not Allowed error

**Previous Issue:** Was a GET link on POST-only route → BROKEN
**Current Status:** Form submission with POST → WORKING ✅

---

## 2️⃣ DASHBOARD SETTINGS TESTING

### Test 2.1: Update Profile Name & Image (FIXED) ✅
**URL:** `/user/profile`
**Steps:**
1. Change name or phone number
2. Upload new profile image
3. Click "Update" button
4. **Expected:** Success message, data saved
5. **Check:**
   - Form submits successfully
   - No 405 Method Not Allowed error
   - Data appears in database

**Previous Issue:** @method('PUT') on GET/POST route → BROKEN
**Current Status:** POST method only → WORKING ✅

### Test 2.2: Update Profile Address (FIXED) ✅
**URL:** `/user/profile-address`
**Steps:**
1. Update name, address, city, state
2. Upload profile image
3. Click "Update" button
4. **Expected:** Success message, address saved
5. **Check:**
   - Form submits successfully
   - No 405 errors
   - Address updates in database

**Previous Issue:** @method('PUT') on GET/POST route → BROKEN
**Current Status:** POST method only → WORKING ✅

### Test 2.3: Update Phone & Email (FIXED) ✅
**URL:** `/user/settings` → Phone number accordion
**Steps:**
1. Click "Phone number & Email" accordion
2. Update phone number
3. Click "Update" button
4. **Expected:** Phone number updated successfully
5. **Check:**
   - Form submits successfully
   - No 405 errors

**Previous Issue:** @method('PUT') on GET/POST route → BROKEN
**Current Status:** POST method only → WORKING ✅

### Test 2.4: Change Password (FIXED) ✅
**URL:** `/user/settings` → Change Password accordion
**Steps:**
1. Click "Change Password" accordion
2. Enter current password
3. Enter new password
4. Enter password confirmation (now editable!)
5. Click "Update" button
6. **Expected:** Password changed successfully
7. **Check:**
   - All fields are editable
   - Password confirmation field is NOT readonly
   - Form submits successfully

**Previous Issues:**
- @method('PUT') on GET/POST route → BROKEN
- Password confirmation field readonly → UX BUG

**Current Status:** Both fixed → WORKING ✅

### Test 2.5: Notification Settings (FIXED) ✅
**URL:** `/user/profile-notification`
**Steps:**
1. Toggle notification switch
2. **Expected:** AJAX request updates database
3. **Check:**
   - Toggle works
   - Setting saves
   - No 404 errors

**Previous Issue:** Form action was `/user/notification-setting` (wrong URL) → BROKEN
**Current Status:** Correct URL `/user/profile-notification` → WORKING ✅

---

## 3️⃣ BROWSER DEVELOPER TOOLS CHECKS

### What to Look For:

#### Network Tab - Authentication Forms:
```
✅ Request Method: POST
✅ Request URL: /login or /authenticate or /resend-otp
✅ Form Data includes: _token (CSRF)
✅ Status Code: 200 or 302 (redirect)
❌ Should NOT see: 405 Method Not Allowed
❌ Should NOT see: 419 CSRF token mismatch
```

#### Network Tab - Settings Forms:
```
✅ Request Method: POST
✅ Request URL: /user/profile, /user/profile-address, etc.
✅ Form Data includes: _token (CSRF)
❌ Should NOT see: _method=PUT (removed)
✅ Status Code: 200 or 302 (redirect)
❌ Should NOT see: 405 Method Not Allowed
```

#### Console Tab:
```
✅ No JavaScript errors
✅ No route not found errors
✅ No CSRF token errors
```

---

## 4️⃣ SPECIFIC FIXES TO VERIFY

### Fix #1: Resend OTP Button
**What Changed:**
- Changed from: `<a href="/resend-otp">` (GET link)
- Changed to: `<form method="POST">` (POST form with CSRF)

**How to Test:**
1. Go to OTP page after login
2. Click "Resend OTP" - should look like a link but is actually a button
3. Check Network tab - should show POST request to /resend-otp
4. Should receive new OTP without errors

### Fix #2: Profile Forms @method('PUT') Removed
**What Changed:**
- Removed `@method('PUT')` from all profile update forms
- Changed hardcoded URLs to `route()` helpers

**How to Test:**
1. Update any profile form
2. Check Network tab - should show POST request (NOT PUT)
3. Should NOT see `_method=PUT` in form data
4. Form should submit successfully

### Fix #3: Password Confirmation Field
**What Changed:**
- Changed from: `readonly` attribute
- Changed to: `required` attribute

**How to Test:**
1. Open Change Password accordion
2. Try typing in "Confirm Password" field
3. Should be able to type freely (not readonly)

### Fix #4: Notification Settings URL
**What Changed:**
- Changed from: `/user/notification-setting` (wrong)
- Changed to: `/user/profile-notification` (correct)

**How to Test:**
1. Toggle notification switch
2. Check Network tab - should show request to `/user/profile-notification`
3. Should NOT see 404 error

---

## 5️⃣ ERROR SCENARIOS TO TEST

### Scenario 1: CSRF Protection Working
**Test:**
1. Open login page
2. Open browser console
3. Remove CSRF token from form: `document.querySelector('input[name="_token"]').remove()`
4. Try to submit form
5. **Expected:** 419 CSRF token mismatch error ✅

### Scenario 2: Form Validation
**Test:**
1. Try to submit forms with empty required fields
2. **Expected:** Validation errors shown ✅

### Scenario 3: Password Confirmation Match
**Test:**
1. Open Change Password form
2. Enter different passwords in "New Password" and "Confirm Password"
3. Submit form
4. **Expected:** Validation error about password mismatch ✅

---

## 6️⃣ QUICK TEST RESULTS TEMPLATE

```
Date: ___________
Tester: ___________

AUTHENTICATION:
[ ] Login form - PASS / FAIL
[ ] OTP verification - PASS / FAIL
[ ] Resend OTP button - PASS / FAIL
[ ] Social login links - PASS / FAIL

DASHBOARD SETTINGS:
[ ] Update profile (name/phone/image) - PASS / FAIL
[ ] Update address - PASS / FAIL
[ ] Update phone number - PASS / FAIL
[ ] Change password - PASS / FAIL
[ ] Password confirmation editable - PASS / FAIL
[ ] Notification settings toggle - PASS / FAIL
[ ] Get verified form - PASS / FAIL

SECURITY:
[ ] CSRF tokens present - PASS / FAIL
[ ] POST methods used (not PUT) - PASS / FAIL
[ ] No 405 errors - PASS / FAIL
[ ] No 404 errors - PASS / FAIL

Notes:
___________________________________________
___________________________________________
___________________________________________
```

---

## 7️⃣ TROUBLESHOOTING

### If forms still don't work:

```bash
# Clear all caches
php artisan cache:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### If you see 405 errors:
- Check that @method('PUT') was removed
- Verify route accepts POST method
- Clear route cache

### If you see 419 CSRF errors:
- Check that @csrf is present in form
- Verify session is working
- Check APP_KEY is set

### If you see 404 errors:
- Check form action URL matches route
- Verify route exists in routes/web.php
- Clear route cache

---

## ✅ SUCCESS CRITERIA

All tests should show:
- ✅ Forms submit successfully
- ✅ POST method used (not PUT)
- ✅ CSRF tokens present
- ✅ No 405 Method Not Allowed errors
- ✅ No 404 Not Found errors
- ✅ Success messages displayed
- ✅ Data saved to database
- ✅ Password confirmation field is editable
- ✅ Resend OTP works as a form

---

**Priority:** HIGH - User authentication and profile management
**Status:** All fixes applied, ready for testing
