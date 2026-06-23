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

    public function lgas()
    {
        return $this->hasMany(Lga::class);
    }

    public function gigLogistics()
    {
        return $this->hasMany(GigLogistic::class, 'state_id');
    }

    public function cities()
    {
        return $this->hasMany(GigLogistic::class, 'state_id');
    }
}
