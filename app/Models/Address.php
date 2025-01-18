<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'country',
        'city',
        'street',
        'building',
        'apartment',
        'postal_code',
        'is_default'
    ];

    protected $casts = [
        'is_default' => 'boolean'
    ];

    public function addressable()
    {
        return $this->morphTo();
    }

    public function shippingOrders()
    {
        return $this->hasMany(Order::class, 'shipping_address_id');
    }

    public function billingOrders()
    {
        return $this->hasMany(Order::class, 'billing_address_id');
    }
} 