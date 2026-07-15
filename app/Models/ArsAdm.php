<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArsAdm extends Model
{
    use HasFactory;

    protected $table = 'ars_adms';

    protected $fillable = [
        'part_no',
        'min',
        'max',
        'part_cat',
        'packing_type',
        'area_code',
        'part_type',
        'wh_zone',
        'rack_no',
        'rack_layer',
    ];
}
