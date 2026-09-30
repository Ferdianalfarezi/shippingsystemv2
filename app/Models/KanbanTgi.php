<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KanbanTgi extends Model
{
    protected $table = 'kanbantgi';

    protected $fillable = [
        'token',
        'original_filename',
        'file_path',
        'dn_no',
        'po_no',
        'total_parts',
        'matched',
        'unmatched',
        'items_meta',
    ];

    protected $casts = [
        'items_meta' => 'array',
    ];
}