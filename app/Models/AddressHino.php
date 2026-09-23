<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddressHino extends Model
{
    protected $table = 'addresshino';

    protected $fillable = [
        'part_no',
        'customer_code',
        'part_name',
        'rack_no',
    ];
}