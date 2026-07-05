<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = [
        'advert_id',
        'order_code',
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'payment_reference',
        'trans_id', 
        'amount',
        'commission',
        'shipping',
        'amount_paid',
        'payment_status',
        'shipping_cost',
        'shipping_method',
        'city',
        'state',
        'shipping_status',
        'shipping_status_date',
        'buyer_status',
        'ship_code',
        'tracking_id',
        'seller_settlement',
        'settlement_date',
        'source',
    ];

    public function advert()
    {
        return $this->belongsTo(Advert::class, 'advert_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shipping()
    {
        return $this->belongsTo(Shipping::class, 'shipping_method');
    }

    public function stateRel()
    {
        return $this->belongsTo(State::class, 'state', 'id');
    }

    public function shippingMethod()
    {
        return $this->belongsTo(Shipping::class, 'shipping_method');
    }

    public function cityLocation()
    {
        return $this->belongsTo(GigLogistic::class, 'city');
    }

    public function stateLocation()
    {
        return $this->belongsTo(State::class, 'state');
    }
}
