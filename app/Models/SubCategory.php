<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class SubCategory extends Model
{
    use HasFactory;
    use Sluggable;
    protected $fillable = [
        'cat_id',
        'subcat_id',
        'sub_category',
        'keywords',
        'meta_title',
        'meta_description'
    ];

    public function sluggable(): array
    {
        return [
            'sub_cat_slug' => [
                'source' => 'sub_category'
            ]
        ];
    }

   public function category()
    {
        return $this->belongsTo(Category::class, 'cat_id');
    }

    public function brands()
    {
        return $this->hasMany(Brands::class, 'subcat_id');
    }
}
