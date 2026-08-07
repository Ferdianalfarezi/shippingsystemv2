<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdmAddressv2 extends Model
{
    protected $table = 'adm_addressv2s'; // sesuaiin kalo mau nama tabel beda

    protected $fillable = [
        'part_no',
        'customer_code',
        'part_name',
        'qty_kbn',
        'rack_no',
    ];
}