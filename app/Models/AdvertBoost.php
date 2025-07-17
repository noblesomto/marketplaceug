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
        'boost_type'
    ];

    public function boost()
    {
        return $this->belongsTo(Advert::class, 'advert_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }



    public function getExpiresAtAttribute()
    {
        return $this->updated_at->copy()->addDays($this->duration);
    }

    public function getIsActiveAttribute()
    {
        return $this->boost_status === 'active' && $this->expires_at->isFuture();
    }

}
