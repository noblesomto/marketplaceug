<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Brands extends Model
{
    use HasFactory;
    use Sluggable;
    protected $fillable = [
        'cat_id',
        'subcat_id',
        'brand_id',
        'brand',
    ];

    public function sluggable(): array
    {
        return [
            'brand_slug' => [
                'source' => 'brand'
            ]
        ];
    }

    public function adverts()
    {
        return $this->hasMany(Advert::class, 'brand');
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class, 'subcat_id');
    }

    public function models()
    {
        return $this->hasMany(Models::class, 'brand_id');
    }
}
