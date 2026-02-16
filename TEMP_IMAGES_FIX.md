# 📸 TEMP IMAGES VALIDATION FIX

**Date:** 2026-02-16
**Issue:** "Please select at least one image" error even when temp images are retained
**Status:** ✅ FIXED

---

## 🐛 THE PROBLEM

**User Report:**
> "The images are retained, but still get error: Please select at least one image"

### What Was Happening:

```
1. User uploads images
   ↓
2. Validation fails (e.g., description missing)
   ↓
3. Images stored in temp folder ✅
   ↓
4. Temp images displayed in form ✅
   ↓
5. User fixes description and submits again
   ↓
6. Validation runs: 'images' => 'required|array'
   ↓
7. No new files in $request->file('images')
   ↓
8. ERROR: "Please select at least one image" ❌
   ↓
9. But temp images ARE there! Just not counted...
```

### Root Cause:

The validation service required `images` to be present in the request, but:
- Temp images from previous upload are stored in session/temp folder
- They're displayed via hidden inputs: `<input type="hidden" name="temp_image_paths[]">`
- But `$request->hasFile('images')` returns `false` (no new upload)
- Validation fails even though we have temp images

---

## ✅ THE SOLUTION

### Strategy: Skip Image Requirement if Temp Images Exist

Modified the validation logic to:
1. Check if `temp_image_paths` exist in request
2. If yes, skip the `'images' => 'required|array'` rule
3. Allow form submission with temp images only

---

## 📝 CODE CHANGES

### File 1: `app/Services/AdvertValidationService.php`

**Lines:** 25-51 (getRules method)

**Added Parameter:**
```php
public function getRules(
    int $categoryId,
    ?int $subcategoryId = null,
    bool $isUpdate = false,
    bool $hasTempImages = false  // ✅ NEW PARAMETER
): array
```

**Modified Logic:**
```php
// ✅ Images required only for create (not for jobs category)
// Skip image requirement if temp images exist (from previous validation error)
if (!$isUpdate && $categoryId != 3 && !$hasTempImages) {
    $rules['images'] = 'required|array';
}
```

**What This Does:**
- Images are required ONLY when:
  - Not an update (`!$isUpdate`)
  - Not Jobs category (`$categoryId != 3`)
  - No temp images exist (`!$hasTempImages`) ✅ NEW CHECK
- If temp images exist, skip the requirement

---

### File 2: `app/Http/Controllers/UserManageAdverts.php`

**Lines:** 113-125 (post_ad method)

**Added Check:**
```php
// ✅ Check if temp images exist (from previous validation error)
$hasTempImages = !empty($request->input('temp_image_paths', []));

// Use dynamic validation service based on Category UI Config
$validationService = new AdvertValidationService();
$rules = $validationService->getRules($category, $subcat, false, $hasTempImages);
```

**What This Does:**
- Checks if `temp_image_paths` array exists in request
- Passes `$hasTempImages` flag to validation service
- Validation service skips image requirement if flag is true

---

## 🔄 COMPLETE FLOW (AFTER FIX)

### First Submission (With Validation Error):

```
1. User selects images (3 files)
   ↓
2. Images uploaded and stored in temp folder
   temp/post-ad-images/temp_xxx_0.jpg
   temp/post-ad-images/temp_xxx_1.jpg
   temp/post-ad-images/temp_xxx_2.jpg
   ↓
3. Validation fails (description empty)
   ↓
4. Temp image data flashed to session
   session()->flash('temp_images', [...])
   ↓
5. Form reloads with validation errors
```

### Form Display (With Temp Images):

```html
<!-- Temp images displayed -->
@if (session('temp_images'))
    @foreach (session('temp_images') as $index => $tempImage)
        <div class="relative group">
            <img src="{{ $tempImage['url'] }}" alt="Preview">
            <!-- ✅ Hidden input passes temp path to next submission -->
            <input type="hidden" name="temp_image_paths[]" value="{{ $tempImage['path'] }}">
        </div>
    @endforeach
@endif
```

### Second Submission (With Temp Images):

```
1. User fixes description and submits
   ↓
2. Controller checks: $request->input('temp_image_paths')
   → ['temp/post-ad-images/temp_xxx_0.jpg', ...]
   ↓
3. $hasTempImages = !empty($tempImagePaths) → TRUE ✅
   ↓
4. Validation service: getRules($category, $subcat, false, true)
   ↓
5. Images NOT required (temp images exist) ✅
   ↓
6. Validation passes ✅
   ↓
7. Advert created
   ↓
8. Process temp images (move to final location)
   ↓
9. Success! ✅
```

---

## 🧪 TESTING

### Test Case 1: Normal Submission (No Errors)

**Steps:**
1. Fill all required fields
2. Upload 3 images
3. Submit

**Expected:**
```
✅ Validation passes
✅ Advert created
✅ Images moved to final location
```

**Result:** ✅ PASSED

---

### Test Case 2: Validation Error, Then Fix

**Steps:**
1. Upload 3 images
2. Leave description empty
3. Submit (validation error)
4. Verify: 3 temp images shown
5. Fill description
6. Submit again (WITHOUT uploading new images)

**Expected:**
```
✅ First submission: Images stored in temp
✅ Form reload: 3 temp images displayed
✅ Second submission: Validation passes (temp images count)
✅ Advert created with temp images
✅ NO "Please select at least one image" error
```

**Result:** ✅ PASSED

---

### Test Case 3: Add More Images to Temp Images

**Steps:**
1. Upload 2 images
2. Leave description empty
3. Submit (validation error)
4. Verify: 2 temp images shown
5. Upload 1 more image
6. Fill description
7. Submit

**Expected:**
```
✅ First submission: 2 images in temp
✅ Form reload: 2 temp images displayed
✅ Second submission: 2 temp + 1 new = 3 total
✅ Advert created with 3 images
```

**Result:** ✅ PASSED

---

## 🗑️ TEMP IMAGE CLEANUP SYSTEM

### Problem:
If user abandons the form after validation error, temp images remain forever.

### Solution:
Automatic cleanup command that runs daily.

---

## 📋 CLEANUP COMMAND

**File:** `app/Console/Commands/CleanupTempImages.php`

**Command Signature:**
```bash
php artisan temp:cleanup-images [--hours=24]
```

**What It Does:**
1. Scans `storage/app/public/temp/post-ad-images/` directory
2. Finds files older than specified hours (default: 24)
3. Deletes old temp images
4. Removes empty directories
5. Reports files deleted and space freed

**Features:**
- ✅ Configurable age threshold (default 24 hours)
- ✅ Reports files deleted and space freed
- ✅ Removes empty directories after cleanup
- ✅ Safe (only deletes from temp folder)

---

## 📅 SCHEDULED CLEANUP

**File:** `app/Console/Kernel.php`

**Schedule:**
```php
$schedule->command('temp:cleanup-images --hours=24')->daily()->at('02:00');
```

**When It Runs:**
- Every day at 2:00 AM
- Deletes temp images older than 24 hours
- Automatic, no manual intervention needed

**Why 2:00 AM?**
- Low traffic time
- Users likely finished or abandoned submissions
- Won't interfere with active form submissions

---

## 🛠️ MANUAL CLEANUP

### Delete All Temp Images (Testing):
```bash
php artisan temp:cleanup-images --hours=0
```

### Delete Images Older Than 48 Hours:
```bash
php artisan temp:cleanup-images --hours=48
```

### Delete Images Older Than 1 Hour:
```bash
php artisan temp:cleanup-images --hours=1
```

**Example Output:**
```
🗑️  Cleaning up temp images older than 24 hours...
  🗑️  Deleted: temp/post-ad-images/temp_xxx_0.jpg
  🗑️  Deleted: temp/post-ad-images/temp_xxx_1.jpg
  🗑️  Deleted: temp/post-ad-images/temp_xxx_2.jpg
  📁 Removed empty directory: temp/post-ad-images

✅ Cleanup complete!
   Files deleted: 15
   Space freed: 2.02 MB
```

---

## 🔐 SECURITY CONSIDERATIONS

### Temp Image Storage:
- ✅ Stored in `storage/app/public/temp/post-ad-images/`
- ✅ Random filenames prevent guessing
- ✅ Timestamp in filename for age tracking
- ✅ Auto-deleted after 24 hours

### File Validation:
- ✅ Mime type validation still applies
- ✅ File size limits still enforced (max 21MB)
- ✅ Only image files allowed (jpeg, png, jpg, gif)

### Cleanup Safety:
- ✅ Only deletes from temp folder (not main uploads)
- ✅ Checks file age before deletion
- ✅ Won't delete active temp images (< 24 hours)

---

## 📊 BEFORE vs AFTER

### Before Fix:

```
User uploads images → Validation error
    ↓
Temp images displayed ✅
    ↓
User fixes error and submits
    ↓
Validation: 'images' => 'required|array'
    ↓
No new files in request
    ↓
ERROR: "Please select at least one image" ❌
    ↓
User confused: "But I uploaded images!" 🤷
```

### After Fix:

```
User uploads images → Validation error
    ↓
Temp images displayed ✅
    ↓
Temp paths stored in hidden inputs ✅
    ↓
User fixes error and submits
    ↓
Check: temp_image_paths exist? YES ✅
    ↓
Validation: Images optional (temp images exist) ✅
    ↓
Validation passes ✅
    ↓
Advert created with temp images ✅
    ↓
User happy! 🎉
```

---

## ✅ SUCCESS CRITERIA

All criteria met:

- [x] Temp images are displayed after validation error
- [x] Temp image paths passed via hidden inputs
- [x] Validation skips image requirement if temp images exist
- [x] User can submit without re-uploading images
- [x] NO "Please select at least one image" error
- [x] Abandoned temp images cleaned up automatically
- [x] Manual cleanup command available
- [x] Scheduled daily cleanup at 2:00 AM

---

## 🚀 DEPLOYMENT CHECKLIST

### Files Modified:
- [x] `app/Services/AdvertValidationService.php` (added $hasTempImages param)
- [x] `app/Http/Controllers/UserManageAdverts.php` (check temp images)
- [x] `app/Console/Commands/CleanupTempImages.php` (NEW - cleanup command)
- [x] `app/Console/Kernel.php` (added scheduled cleanup)

### Testing:
- [x] Normal submission (no validation errors)
- [x] Validation error with temp images
- [x] Resubmit with temp images only
- [x] Add new images to temp images
- [x] Manual cleanup command
- [x] Scheduled cleanup (verify cron)

### Post-Deployment:
- [ ] Monitor temp folder size
- [ ] Verify scheduled cleanup runs at 2:00 AM
- [ ] Check logs for cleanup reports
- [ ] User acceptance testing

---

## 📚 USAGE EXAMPLES

### For Developers:

**Check temp images in request:**
```php
$tempImagePaths = $request->input('temp_image_paths', []);
$hasTempImages = !empty($tempImagePaths);

if ($hasTempImages) {
    // User has temp images from previous submission
}
```

**Get validation rules with temp images:**
```php
$validationService = new AdvertValidationService();
$rules = $validationService->getRules(
    $categoryId,
    $subcategoryId,
    $isUpdate = false,
    $hasTempImages = true  // Skip image requirement
);
```

### For Admins:

**Check temp folder size:**
```bash
du -sh storage/app/public/temp/post-ad-images/
```

**Clean up temp images manually:**
```bash
php artisan temp:cleanup-images --hours=24
```

**List scheduled tasks:**
```bash
php artisan schedule:list
```

---

## 🎉 FINAL STATUS

**Issue:** Temp images displayed but validation fails
**Root Cause:** Validation required new files even when temp images exist
**Solution:** Skip image requirement if temp images present
**Cleanup:** Automatic daily cleanup of abandoned temp images

**All functionality working correctly!** ✅

---

**Last Updated:** 2026-02-16
**Tested:** Manual testing + automated cleanup
**Status:** ✅ PRODUCTION READY
