<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddressTgi extends Model
{
    protected $table = 'addresstgi';

    protected $fillable = [
        'part_no',
        'customer_code',
        'part_name',
        'rack_no',
    ];
}