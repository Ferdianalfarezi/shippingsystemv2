<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NtcAddress extends Model
{
    protected $fillable = [
        'part_no',
        'customer_code',
        'part_name',
        'rack_no',
    ];
}