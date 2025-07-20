<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AdvertBoost extends Model
{
    use HasFactory;
    protected $fillable = [
        'advert_id', 
        'user_id',
        'payment_reference',
        'trans_id', 
        'amount',
        'duration',
        'payment_status',
        'boost_status',
        'boost_type',
        'start_date'
    ];

    protected $casts = [
        'start_date' => 'datetime',
    ];

    public function advert()
    {
        return $this->belongsTo(Advert::class, 'advert_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getDaysRemainingAttribute()
    {
        $expiry = Carbon::parse($this->start_date)->addDays($this->duration);
        return Carbon::now()->diffInDays($expiry, false); // negative means expired
    }


    public function getExpiresAtAttribute()
    {
        return $this->start_date->copy()->addDays($this->duration);
    }

    public function getIsActiveAttribute()
    {
        return $this->boost_status === 'active' && $this->expires_at->isFuture();
    }

}
