<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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
}
