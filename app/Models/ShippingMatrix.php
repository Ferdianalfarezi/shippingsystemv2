<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingMatrix extends Model
{
    protected $fillable = [
        'customers',
        'dock',
        'cycle',
        'address',
    ];
}