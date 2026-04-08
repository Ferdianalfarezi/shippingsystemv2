<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KanbanHpm extends Model
{
    use HasFactory;

    protected $table = 'kanbanhpms';

    protected $fillable = [
        'di_no',
        'item_seq',
        'part_no',
        'part_name',
        'seq_no',
        'kd_lot_no',
        'supply_address',
        'ms_id',   
        'from',
        'to',
        'inventory_category',
        'ps_code',
        'order_class',
        'datetime',
        'ship',
        'barcode',
        'last_upload_at',
        'uploaded_by',
    ];

    /**
     * Get latest upload info
     */
    public static function getLatestUploadInfo()
    {
        return static::orderBy('created_at', 'desc')->first();
    }
}