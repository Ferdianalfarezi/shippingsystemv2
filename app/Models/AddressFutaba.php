<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddressFutaba extends Model
{
    protected $table = 'addressfutaba';

    protected $fillable = [
        'part_no',
        'customer_code',
        'part_name',
        'rack_no',
    ];
}