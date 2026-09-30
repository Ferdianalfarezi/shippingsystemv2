<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KanbanBankTgi extends Model
{
    protected $table = 'kanbanbanktgi';

        protected $fillable = [
        'part_no',
        'jumlah_kbn',
        'original_filename',
        'file_path',
        'labels_per_page',
    ];
}