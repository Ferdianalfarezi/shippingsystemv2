<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kanbanadm extends Model
{
    use HasFactory;

    protected $table = 'kanbanadms';

    protected $fillable = [
        'plant_code',
        'shop_code',
        'part_category',
        'route',
        'lp',
        'trip',
        'vendor_code',
        'vendor_alias',
        'vendor_site',
        'vendor_site_alias',
        'order_no',
        'po_number',
        'calc_date',
        'order_date',
        'order_time',
        'del_date',
        'del_time',
        'del_cycle',
        'doc_no',
        'rec_status',
        'dn_type',
        'rec_date',
        'rec_by',
        'part_no',
        'part_name',
        'job_no',
        'lane',
        'qty_kbn',
        'order_kbn',
        'order_pcs',
        'qty_receive',
        'qty_balance',
        'cancel_status',
        'remark',
        'seq',   
        'uploaded_by',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];
}