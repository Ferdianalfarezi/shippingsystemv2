<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HpmAddress extends Model
{
    use HasFactory;

    protected $table = 'hpm_addresses';

    protected $fillable = [
        'part_no',
        'part_name',
        'rack_no',
    ];
}