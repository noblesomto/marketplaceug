<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvertImage extends Model
{
    use HasFactory;
    protected $fillable = [
        'ad_id',
        'image',
        'position',
    ];

    public function advert()
    {
        return $this->belongsTo(Advert::class);
    }

    
}
