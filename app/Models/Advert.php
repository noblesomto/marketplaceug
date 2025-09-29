<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Enums\Fit;

class Advert extends Model implements HasMedia
{
    use HasFactory;
    use Sluggable;
    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'ad_id',
        'ad_type',
        'ad_title',
        'category',
        'sub_category',
        'brand',
        'price',
        'contact_price',
        'salary',
        'expected_salary',
        'quantity',
        'price_type',
        'item_condition',
        'buy_direct',
        'description',
        'state',
        'lga',
        'state_slug',
        'ad_status',
        'shipment',
        'shipping',
        'keyword',
        'meta_description',
        'views',
        'featured',
        'sold',
        'sold_date',
        'show_contact',
    ];


    public function sluggable(): array
    {
        return [
            'title_slug' => [
                'source' => 'ad_title'
            ]
        ];
    }

    public function images()
    {
        return $this->hasMany(AdvertImage::class, 'advert_id')->orderBy('position');
    }

    public function firstImage()
    {
        return $this->hasOne(AdvertImage::class)->orderBy('position', 'asc');
    }

    public function brands()
    {
        return $this->belongsTo(Brands::class, 'brand');
    }
    public function phone()
    {
        return $this->hasOne(PhoneDetail::class, 'advert_id');
    }

    public function car()
    {
        return $this->hasOne(CarDetail::class, 'advert_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class, 'advert_id');
    }

    public function boost()
    {
        return $this->hasMany(AdvertBoost::class, 'advert_id');
    }

    public function reports()
    {
        return $this->hasMany(Reports::class, 'advert_id');
    }

    public function shippings()
    {
        return $this->belongsToMany(Shipping::class, 'advert_shipping');
    }

    public function payment()
    {
        return $this->hasMany(Payment::class, 'advert_id');
    }

    public function feedback()
    {
        return $this->hasMany(Feedback::class, 'advert_id');
    }

    public function scopeActiveNotRecentlySold($query)
    {
        return $query->where('ad_status', 'active')
            ->where(function ($q) {
                $q->where('sold', '!=', 'Yes')
                  ->orWhere(function ($q) {
                      $q->where('sold', 'Yes')
                        ->whereNotNull('sold_date')
                        ->where('sold_date', '>=', now()->subDays(7));
                  });
            });
    }

    public function scopeFeaturedBoosted($query)
    {
        return $query->where('featured', 'Yes')
            ->where('sold', 'No')
            ->where('ad_status', 'active')
            ->whereHas('boost', function ($q) {
                $q->where('boost_status', 'active');
            });
    }

    protected static function boot()
    {
        parent::boot();

        // Automatically handle media when deleting the advert
        static::deleting(function ($advert) {
            // Special handling for Job category (category 3)
            if ($advert->category == 3) {
                // For Job category, only delete non-default images
                $media = $advert->getMedia('images');
                foreach ($media as $mediaItem) {
                    // Only delete if it's not a default image
                    if (!$mediaItem->getCustomProperty('is_default', false)) {
                        $mediaItem->delete();
                    }
                }
            } else {
                // For all other categories, clear all images
                $advert->clearMediaCollection('images');
            }

        });
    }

    public function scopeOrderWithFeatured($query)
    {
        return $query->selectRaw('adverts.*, (featured = "yes") as is_featured')
                     ->orderByDesc('is_featured') // Featured first
                     ->orderByRaw('CASE WHEN featured = "yes" THEN RAND() END') // Random featured
                     ->orderByDesc('created_at'); // Others newest
    }


    public function registerMediaConversions(?Media $media = null): void
    {
        // Optimized version - no size constraints, just format and quality optimization
        $this->addMediaConversion('optimized')
            ->format('webp')
            ->quality(65)
            ->width(1600)
            ->fit(Fit::Max)
            ->optimize()
            ->performOnCollections('images')
            ->nonQueued();

        // Responsive versions
        $this->addMediaConversion('large')
            ->format('webp')
            ->quality(70)
            ->width(1200)
            ->fit(Fit::Max)
            ->optimize()
            ->performOnCollections('images')
            ->nonQueued();


        $this->addMediaConversion('thumbnail')
            ->format('webp')
            ->quality(70)
            ->width(300)
            ->height(300)
            ->fit(Fit::Crop)
            ->optimize()
            ->performOnCollections('images')
            ->nonQueued();

    }

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes([
                'image/jpeg', 'image/png', 'image/gif',
                'image/webp', 'image/bmp', 'image/tiff',
                'image/heic', 'image/heif'
            ]);
    }

    public function getImages()
    {
        return $this->getMedia('images');
    }

    public function getFirstImageUrl($conversion = 'optimized')
    {
        $media = $this->getFirstMedia('images');
        return $media ? $media->getUrl($conversion) : null;
    }

    public function getFirstImage()
    {
        return $this->getFirstMedia('images');
    }

    public function getAllImageUrls($conversion = 'optimized')
    {
        return $this->getMedia('images')->map(function ($media) use ($conversion) {
            return [
                'id' => $media->id,
                'url' => $media->getUrl($conversion),
                'thumbnail' => $media->getUrl('thumbnail'),
                'original' => $media->getUrl(),
                'order' => $media->getCustomProperty('position', $media->order_column)
            ];
        })->sortBy('order')->values();
    }

    /**
     * Add default image for specific categories
     */
    public function addDefaultImage($imageName = 'jobs.png')
    {
        $imagePath = public_path('images/' . $imageName); // Adjust path as needed

        if (file_exists($imagePath)) {
            $this->addMedia($imagePath)
                ->withCustomProperties(['position' => 1])
                ->usingName($imageName)
                ->usingFileName($imageName)
                ->toMediaCollection('images');
        }
    }



}
