<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    protected $table = 'courier_shipping_methods';

    protected $fillable = [
        'name',
        'courier',
        'code',
        'fee',
        'description',
        'active',
        'estimated_days_min',
        'estimated_days_max',
    ];

    protected $casts = [
        'fee' => 'decimal:2',
        'active' => 'boolean',
        'estimated_days_min' => 'integer',
        'estimated_days_max' => 'integer',
    ];
}
