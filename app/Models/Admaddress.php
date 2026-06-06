<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admaddress extends Model
{
    protected $table = 'admaddresses';

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