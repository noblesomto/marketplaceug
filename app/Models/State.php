<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'station_id',
        'slug',
    ];

    public function gigLogistics()
    {
        return $this->hasMany(GigLogistic::class, 'state_id');
    }
}
