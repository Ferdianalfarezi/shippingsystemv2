<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SlipHpm extends Model
{
    use HasFactory;

    protected $table = 'sliphpms';

    protected $fillable = [
        'di_no',
        'part_no',
        'part_name',
        'part_color',
        'from',
        'to',
        'ps_code',
        'inv_cat',
        'kd_lot_no',
        'supply_address',
        'ms_id',
        'ship',
        'rcv_type', 
        'seq_no',
        'datetime',
        'qty',
        'ps_code',
        'prod_seq',
        'uploaded_by',
        'last_upload_at',
        'expires_at',
    ];

    public static function getLatestUploadInfo()
    {
        return static::orderBy('created_at', 'desc')->first();
    }
}