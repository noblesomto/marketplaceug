<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Enums\Fit;

class User extends Authenticatable implements HasMedia
{
    use HasApiTokens, HasFactory, Notifiable, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'user_id',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'password',
        'verified',
        'acc_status',
        'acc_type',
        'token',
        'OTP',
        'profile_picture',
        'notification',
        'disable_account',
        'disable_account_date',
        'bank_name',
        'bank_code',
        'account_name',
        'account_number',
        'remember_token'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            do {
                $user->user_id = str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
            } while (User::where('user_id', $user->user_id)->exists());
        });
    }

    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';

    public function trustedDevices()
    {
        return $this->hasMany(TrustedDevice::class, 'user_id', 'user_id');
    }


    public function adverts()
    {
        return $this->hasMany(Advert::class, 'user_id', 'user_id');
    }

    public function verification()
    {
        return $this->hasOne(UserVerification::class, 'user_id');
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id', 'user_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id', 'user_id');
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class, 'user_id', 'user_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'user_id', 'user_id');
    }

    public function feedbacksGiven()
    {
        return $this->hasMany(Feedback::class, 'user_id', 'user_id');
    }

    public function feedbacksReceived()
    {
        return $this->hasMany(Feedback::class, 'seller_id', 'user_id');
    }

    public function followers()
    {
        return $this->hasMany(Followers::class, 'follow', 'user_id');
    }

    public function following()
    {
        return $this->hasMany(Followers::class, 'user_id', 'user_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile_image')
            ->singleFile()
            ->onlyKeepLatest(1)
            ->acceptsMimeTypes(['image/jpeg','image/png','image/webp','image/gif'])
            ->useDisk('spatie')
            ->useFallbackUrl('/images/placeholder-profile.png');
    }



    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('optimized')
            ->format('webp')
            ->quality(70)
            ->width(1200)
            ->fit(Fit::Max)
            ->optimize()
            ->performOnCollections('profile_image')
            ->nonQueued();

        $this->addMediaConversion('thumbnail')
            ->width(200)
            ->height(200)
            ->format('webp')
            ->quality(50)
            ->fit(Fit::Crop)
            ->optimize()
            ->performOnCollections('profile_image')
            ->nonQueued();
    }

    // ADD THIS METHOD TO YOUR USER MODEL:
    public function afterMediaConversion(Media $media): void
    {
        // Wait 2 seconds then delete original file
        sleep(2);

        if ($media->collection_name === 'profile_image') {
            $disk = $media->getDisk();
            $originalPath = $media->getPath();

            if ($disk->exists($originalPath)) {
                $disk->delete($originalPath);
            }
        }
    }

    public function getProfileImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('profile_image', 'optimized');
    }

    public function getProfileThumbnailUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('profile_image', 'thumbnail');
    }
}
