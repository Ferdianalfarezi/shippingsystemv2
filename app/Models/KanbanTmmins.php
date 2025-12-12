<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KanbanTmmins extends Model
{
    use HasFactory;

    protected $table = 'kanbantmmins';
    
    protected $fillable = [
        'qr_code',
        'manifest_no',
        'keterangan',
        'departure_time',
        'arrival_time',
        'dock_code',
        'part_address',
        'part_no',
        'order_no',
        'unique_no',
        'pcs',
        'route',
        'part_name',
        'supplier',
        'supplier_code',
        'customer_address',
        'out_time',
        'dock',
        'cycle',
        'address',
        'plo',
        'conveyance_no',
        'last_upload_at',
        'uploaded_by'
    ];

    protected $casts = [
        'last_upload_at' => 'datetime',
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
        'out_time' => 'datetime',
    ];

    /**
     * Get the latest upload information
     */
    public static function getLatestUploadInfo()
    {
        return self::select('last_upload_at', 'uploaded_by')
                   ->whereNotNull('last_upload_at')
                   ->orderBy('last_upload_at', 'desc')
                   ->first();
    }

    /**
     * Update upload information for all records
     */
    public static function updateUploadInfo($uploadedBy = null)
    {
        if (!$uploadedBy) {
            $uploadedBy = 'System';
        }
        
        return self::whereNull('last_upload_at')
                   ->orWhere('last_upload_at', '<', now()->subMinutes(1))
                   ->update([
                       'last_upload_at' => now(),
                       'uploaded_by' => $uploadedBy
                   ]);
    }

    /**
     * Get unique dock codes for filtering
     */
    public static function getUniqueDockCodes()
    {
        return self::select('dock_code')
                   ->whereNotNull('dock_code')
                   ->where('dock_code', '!=', '')
                   ->distinct()
                   ->orderBy('dock_code')
                   ->pluck('dock_code');
    }
}