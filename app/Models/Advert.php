<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Advert extends Model
{
    use HasFactory;
    use Sluggable;

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
        return $query->where('ad_status', 1)
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
            ->where('ad_status', 1)
            ->whereHas('boost', function ($q) {
                $q->where('boost_status', 'active');
            });
    }


}
