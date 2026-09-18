<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrepMonitoring extends Model
{
    protected $fillable = [
        'customers', 'cycle', 'dock', 'route', 'kbn', 'skid',
        'pr_number', 'pr_count', 'shipping_address', 'ship_count',
        'status', 'scanned_dns', 'group_date', 'start_prep_at', 'start_ship_at', 'etd_time',
    ];

    protected $casts = [
        'scanned_dns'   => 'array',
        'group_date'    => 'date',
        'start_prep_at' => 'datetime',
        'start_ship_at' => 'datetime',
    ];
}