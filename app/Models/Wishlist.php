<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    use HasFactory;
    protected $fillable = [
        'ad_id',
        'user_id',
    ];

    public function advert()
    {
        return $this->belongsTo(Advert::class);
    }
}
