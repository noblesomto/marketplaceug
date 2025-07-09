<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Models extends Model
{
    use HasFactory;
    use Sluggable;
    protected $fillable = [
        'cat_id',
        'subcat_id',
        'brand_id',
        'model_id',
        'model',
    ];

    public function sluggable(): array
    {
        return [
            'model_slug' => [
                'source' => 'model'
            ]
        ];
    }

    public function brand()
    {
        return $this->belongsTo(Brands::class, 'brand_id');
    }
}
