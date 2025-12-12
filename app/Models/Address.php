<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'part_no',
        'customer_code',
        'model',
        'part_name',
        'qty_kbn',
        'line',
        'rack_no',
    ];
}
