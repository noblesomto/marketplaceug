# 🗑️ TEMP IMAGE CLEANUP - QUICK GUIDE

## For System Administrators

### What Are Temp Images?

When users submit the post ad form with validation errors, their uploaded images are temporarily stored in:
```
storage/app/public/temp/post-ad-images/
```

These images are:
- Displayed when the form reloads (so users don't re-upload)
- Moved to permanent location on successful submission
- **Abandoned if user closes browser/never completes submission**

---

## Automatic Cleanup

### Schedule:
```
Daily at 2:00 AM (server time)
Deletes images older than 24 hours
```

### Command Running:
```bash
php artisan temp:cleanup-images --hours=24
```

### How to Verify It's Running:

**Check scheduler is active:**
```bash
php artisan schedule:list
```

**Expected output:**
```
temp:cleanup-images --hours=24 ......... Daily at 02:00
```

**Check if cron is set up:**
```bash
crontab -l | grep artisan
```

**Should see:**
```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

---

## Manual Cleanup Commands

### Clean Images Older Than 24 Hours:
```bash
php artisan temp:cleanup-images --hours=24
```

### Clean ALL Temp Images (Testing):
```bash
php artisan temp:cleanup-images --hours=0
```

### Clean Images Older Than 48 Hours:
```bash
php artisan temp:cleanup-images --hours=48
```

### Clean Images Older Than 1 Hour:
```bash
php artisan temp:cleanup-images --hours=1
```

---

## Monitoring

### Check Temp Folder Size:
```bash
du -sh storage/app/public/temp/post-ad-images/
```

### Count Temp Images:
```bash
find storage/app/public/temp/post-ad-images/ -type f | wc -l
```

### List Old Temp Images (> 24 hours):
```bash
find storage/app/public/temp/post-ad-images/ -type f -mtime +1 -ls
```

### View Recent Temp Images:
```bash
ls -lht storage/app/public/temp/post-ad-images/ | head -20
```

---

## Troubleshooting

### Temp Folder Growing Too Large?

**Check size:**
```bash
du -sh storage/app/public/temp/post-ad-images/
```

**If > 100MB, run immediate cleanup:**
```bash
php artisan temp:cleanup-images --hours=1
```

**Check scheduler logs:**
```bash
tail -f storage/logs/laravel.log | grep cleanup-images
```

---

### Cleanup Not Running Automatically?

**1. Verify schedule is registered:**
```bash
php artisan schedule:list | grep cleanup
```

**2. Test manual run:**
```bash
php artisan temp:cleanup-images --hours=24
```

**3. Check cron job:**
```bash
crontab -l
```

**Should have:**
```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

**4. If cron missing, add it:**
```bash
crontab -e
```

**Add line:**
```
* * * * * cd /home/www/laravel/marketplace && php artisan schedule:run >> /dev/null 2>&1
```

---

### Permissions Issues?

**Fix storage permissions:**
```bash
chmod -R 775 storage/app/public/temp/
chown -R www-data:www-data storage/app/public/temp/
```

---

## Performance Considerations

### Expected Storage Usage:

**Normal operation:**
- 10-50 temp images at any time
- 5-20 MB total size
- Cleaned up within 24 hours

**High volume days:**
- 100-200 temp images
- 50-100 MB total size
- Still cleaned up within 24 hours

### Disk Space Alerts:

Set up monitoring if temp folder exceeds:
- **Warning:** > 500 MB (many abandoned submissions)
- **Critical:** > 1 GB (cleanup may not be running)

---

## Customization

### Change Cleanup Time:

**Edit:** `app/Console/Kernel.php`

**Find:**
```php
$schedule->command('temp:cleanup-images --hours=24')->daily()->at('02:00');
```

**Change time:**
```php
->daily()->at('03:00')  // Run at 3:00 AM
->daily()->at('01:30')  // Run at 1:30 AM
```

### Change Age Threshold:

**Run cleanup every 12 hours, delete files > 12 hours old:**
```php
$schedule->command('temp:cleanup-images --hours=12')->everyTwelveHours();
```

**Run daily, delete files > 48 hours old (more conservative):**
```php
$schedule->command('temp:cleanup-images --hours=48')->daily()->at('02:00');
```

---

## Emergency Procedures

### Temp Folder Full / Disk Space Critical:

**1. Immediate cleanup (all temp files):**
```bash
php artisan temp:cleanup-images --hours=0
```

**2. Verify disk space freed:**
```bash
df -h
```

**3. Check temp folder:**
```bash
du -sh storage/app/public/temp/post-ad-images/
```

**4. If still full, manual deletion:**
```bash
rm -rf storage/app/public/temp/post-ad-images/*
```

**5. Verify uploads folder not affected:**
```bash
ls -la storage/app/public/uploads/adverts/
```

---

## Logs & Reporting

### Cleanup Command Output:

**Example:**
```
🗑️  Cleaning up temp images older than 24 hours...
  🗑️  Deleted: temp/post-ad-images/temp_xxx_0.jpg
  🗑️  Deleted: temp/post-ad-images/temp_xxx_1.jpg
  ...
  📁 Removed empty directory: temp/post-ad-images

✅ Cleanup complete!
   Files deleted: 15
   Space freed: 2.02 MB
```

### What to Monitor:

- **Files deleted:** Normal: 10-50/day, High: 100-200/day
- **Space freed:** Normal: 5-50 MB/day, High: 100-500 MB/day
- **Errors:** Should be 0

---

## FAQ

### Q: Will cleanup delete active submissions?
**A:** No. Only files older than 24 hours are deleted. Active submissions are < 1 hour old.

### Q: What if user is slow and takes > 24 hours?
**A:** Extremely rare. If it happens, they just re-upload images (minor inconvenience).

### Q: Can I safely run cleanup manually?
**A:** Yes. It only deletes from temp folder, never from main uploads.

### Q: Will cleanup run if no temp images exist?
**A:** Yes, but it does nothing. Output: "No temp directory found. Nothing to clean."

### Q: How much disk space will this save?
**A:** Varies. Testing showed 2-50 MB/day depending on traffic.

---

## Quick Commands Cheat Sheet

```bash
# Check temp folder size
du -sh storage/app/public/temp/post-ad-images/

# Count temp files
find storage/app/public/temp/post-ad-images/ -type f | wc -l

# Run cleanup (24h)
php artisan temp:cleanup-images --hours=24

# Run cleanup (all)
php artisan temp:cleanup-images --hours=0

# List scheduled tasks
php artisan schedule:list

# Check cron
crontab -l

# View cleanup logs
tail -f storage/logs/laravel.log | grep cleanup
```

---

## Support

**If cleanup fails or temp folder grows too large:**

1. Check cron is running
2. Check permissions on storage/app/public/temp/
3. Run manual cleanup
4. Contact developer if issue persists

**Developer Contact:** [Your contact info]

---

**Last Updated:** 2026-02-16
**Command:** `temp:cleanup-images`
**Schedule:** Daily at 2:00 AM
