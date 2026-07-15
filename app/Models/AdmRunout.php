<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmRunout extends Model
{
    use HasFactory;

    protected $table = 'adm_runouts';

    protected $fillable = [
        'part_no',
    ];
}