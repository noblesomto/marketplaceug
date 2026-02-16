# 🐛 DEBUG: Image Upload Not Working

## Issue
Images are not moving from temp to Spatie. Ad posted without images.

## Debug Steps Added

I've added comprehensive logging to track the entire image processing flow.

---

## Test Again

### 1. Clear Logs
```bash
echo "" > storage/logs/laravel.log
```

### 2. Submit Form with Validation Error
```
→ Go to: http://localhost:8030/user/post-ad
→ Category: Vehicles → Cars
→ Upload: 3 images
→ Fill all fields EXCEPT description
→ Submit → Get validation error
→ Verify temp images are displayed
```

### 3. Fix and Resubmit
```
→ Fill description
→ DO NOT upload new images
→ Submit
```

### 4. Check Logs
```bash
tail -200 storage/logs/laravel.log | grep -A 2 -B 2 "Image Processing\|processAdvertImages\|Temp image\|media added"
```

---

## What to Look For in Logs

### Expected Log Flow:

**1. Initial Check:**
```
Image Processing Debug: {
    "temp_paths": ["temp/post-ad-images/xxx.jpg", ...],
    "has_new_images": false,
    "has_temp_images": true
}
```

**2. Temp Path Verification:**
```
Checking temp image: {
    "path": "temp/post-ad-images/xxx.jpg",
    "exists": true
}
```

**3. Images to Process:**
```
Images to process: {
    "count": 3,
    "data": [...]
}
```

**4. Method Called:**
```
processAdvertImages called: {
    "advert_id": 56xxx,
    "images_count": 3
}
```

**5. Each Image Processed:**
```
Processing image 0: {"type": "temp"}

Temp image processing: {
    "temp_path": "temp/post-ad-images/xxx.jpg",
    "full_path": "/home/www/laravel/marketplace/storage/app/public/temp/...",
    "file_exists": true
}

Adding temp media to Spatie: {
    "path": "..."
}

Temp media added successfully: {
    "media_id": 1175,
    "file_name": "6xxxxx.webp"
}

Temp file deleted: {
    "path": "..."
}
```

**6. Main Image Set:**
```
Setting main ad_image: {
    "has_first_media": true,
    "media_id": 1175
}

Main ad_image set: {
    "url": "..."
}
```

---

## Possible Issues

### Issue 1: temp_paths Empty
**Log shows:**
```
"temp_paths": []
```
**Cause:** Hidden inputs not being submitted
**Solution:** Check form has `<input name="temp_image_paths[]">`

---

### Issue 2: File Not Exists
**Log shows:**
```
"file_exists": false
```
**Cause:** Temp files deleted or wrong path
**Solution:** Check temp folder has files

---

### Issue 3: Spatie Error
**Log shows:**
```
Failed to process image: [error message]
```
**Cause:** Spatie configuration issue
**Solution:** Check Spatie setup

---

### Issue 4: Method Not Called
**Log shows:** No "processAdvertImages called"
**Cause:** Logic condition failed
**Solution:** Check if/else conditions

---

## Quick Checks

### Check Temp Folder
```bash
ls -la storage/app/public/temp/post-ad-images/
```

### Check Form HTML
```bash
curl -s http://localhost:8030/user/post-ad | grep -A 3 'temp_image_paths'
```

### Check Latest Advert
```bash
php artisan tinker
>>> $ad = App\Models\Advert::latest()->first()
>>> $ad->getMedia('images')->count()
>>> $ad->ad_image
```

---

## After Testing

Send me the log output from step 4 so I can see exactly what's happening!
