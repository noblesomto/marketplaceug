# 🧪 POST AD FORM - TESTING GUIDE

## ✅ Automated Tests Passed

All code implementations verified:
- ✅ Blade template has old() helpers for all fields
- ✅ Controller has image retention methods
- ✅ Early validation implemented
- ✅ Null-safe removeEmojis
- ✅ Storage directories configured
- ✅ Validation service working
- ✅ Routes configured

## 📱 Manual Testing Steps

### Access the Application
```
URL: http://localhost:8030/user/post-ad
(Login required - create test account if needed)
```

### TEST 1: Basic Value Retention ⭐
**Time: 2 minutes**

1. Fill the form:
   ```
   Title: "Toyota Camry 2020"
   Category: "Vehicles"
   Subcategory: "Cars"
   Brand: Select any
   Price: "5000000"
   State: "Lagos"
   LGA: "Ikeja"
   ```

2. **Leave Description EMPTY**

3. Click "Post Ad"

**Expected Results:**
```
✅ Error message: "Description is required."
✅ Title still shows: "Toyota Camry 2020"
✅ Category still selected: "Vehicles"
✅ Subcategory still selected: "Cars"
✅ Brand still selected
✅ Price shows: "5,000,000" (formatted)
✅ State still selected: "Lagos"
✅ LGA still selected: "Ikeja"
```

### TEST 2: Image Retention ⭐⭐⭐
**Time: 3 minutes**

1. Repeat Test 1 steps

2. **Upload 3 test images**

3. Submit without description

**Expected Results:**
```
✅ All form values retained (as Test 1)
✅ 3 image thumbnails displayed
✅ Green success box appears:
   "✓ Your images are retained!
    The 3 image(s) you uploaded are still here.
    You can add more or remove them."
✅ Each image has delete (×) button on hover
✅ Image numbers shown: 1, 2, 3
```

4. Add description and submit

**Expected Results:**
```
✅ Ad created successfully
✅ All 3 images attached to the ad
```

### TEST 3: Car-Specific Fields ⭐⭐
**Time: 3 minutes**

1. Category: Vehicles → Subcategory: Cars

2. Fill car fields:
   ```
   Vehicle Condition: "Foreign used"
   Fuel: "Petrol"
   Transmission: "Automatic"
   Body Type: "SUV/Off Road Vehicle"
   Exterior Color: "Black"
   ```

3. Check equipment boxes:
   ```
   ☑ Air conditioning
   ☑ Bluetooth
   ☑ Anti-lock braking system (ABS)
   ```

4. Submit without description

**Expected Results:**
```
✅ All car fields retained
✅ All checkboxes still checked
```

### TEST 4: Remove Temp Images ⭐⭐
**Time: 2 minutes**

1. Upload 4 images

2. Submit with error

3. See 4 previews

4. **Click × on 2nd image**

**Expected Results:**
```
✅ 2nd image removed instantly
✅ Now showing 3 images
✅ Success message updates: "3 image(s)"
```

5. Submit successfully

**Expected Results:**
```
✅ Ad created with only 3 images
✅ 2nd image NOT included
```

### TEST 5: Multiple Errors ⭐⭐
**Time: 3 minutes**

1. Fill ONLY:
   ```
   Title: "Test"
   ```

2. Upload 2 images

3. Submit (many errors)

**Expected Results:**
```
✅ Errors shown for category, subcategory, etc.
✅ Title "Test" retained
✅ 2 images retained
```

4. Fix category, subcategory, brand, state, LGA

5. Submit again (still no description)

**Expected Results:**
```
✅ All previously filled fields retained
✅ Images still there
✅ Only description error shown
```

6. Add description and submit

**Expected Results:**
```
✅ Success!
```

## 🎯 Quick Visual Checklist

After submitting form with validation error, you should see:

```
┌─────────────────────────────────────────┐
│ ⚠ There were issues with your          │
│   submission:                            │
│   • Description is required.             │
└─────────────────────────────────────────┘

Form still has:
✅ Title filled
✅ Dropdowns selected
✅ Checkboxes checked
✅ Radio buttons selected

Images section:
┌─────────────────────────────────────────┐
│ ✓ Your images are retained!            │
│   The 3 image(s) you uploaded are       │
│   still here.                            │
└─────────────────────────────────────────┘

[Image 1] [×]  [Image 2] [×]  [Image 3] [×]
```

## ❌ What NOT to See

These would indicate problems:

```
❌ Form completely reset (blank)
❌ Dropdowns show "Select..."
❌ Images disappeared
❌ No success message for images
❌ Have to re-upload images
```

## 🔍 Browser Console Check

Open DevTools (F12) → Console

Should NOT see:
```
❌ JavaScript errors
❌ Failed to load resources
❌ 500 Internal Server Error
```

Should see (when images retained):
```
✅ Old values being restored
✅ Dropdowns populating correctly
```

## 📊 Expected vs Actual

| Feature | Expected | Status |
|---------|----------|--------|
| Text inputs retain | ✅ Yes | Test it |
| Dropdowns retain | ✅ Yes | Test it |
| Radios retain | ✅ Yes | Test it |
| Checkboxes retain | ✅ Yes | Test it |
| Images retain | ✅ Yes | Test it |
| Can remove images | ✅ Yes | Test it |
| Can add more images | ✅ Yes | Test it |

## ✅ Success Criteria

All tests pass if:
1. ✅ No data lost on validation error
2. ✅ Images shown as previews
3. ✅ Can modify and resubmit
4. ✅ Final submission works correctly

## 🚀 Ready to Test!

**Start here:** http://localhost:8030/user/post-ad

**Questions to answer:**
- Do form values persist? 
- Do images show as previews?
- Can you remove/add images?
- Does final submission work?

Happy testing! 🎉
