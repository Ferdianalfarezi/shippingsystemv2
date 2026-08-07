<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KanbanAdmSplit extends Model
{
    // nama tabel gak ngikutin konvensi plural Eloquent, jadi diset manual
    protected $table = 'kanbanadmsplits';

    protected $fillable = [
        'token',
        'original_filename',
        'file_path',
        'total_labels',
        'matched',
        'unmatched',
        'unmatched_no_text',
        'unmatched_no_extract',
        'unmatched_no_master',
        'unmatched_no_rack',
        'labels_meta',
    ];

    protected $casts = [
        'labels_meta' => 'array',
    ];
}