<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddressFji extends Model
{
    // nama tabel literal 'addressfji' (bukan default konvensi plural)
    protected $table = 'addressfji';

    protected $fillable = [
        'part_no',
        'customer_code',
        'part_name',
        'rack_no',
    ];
}