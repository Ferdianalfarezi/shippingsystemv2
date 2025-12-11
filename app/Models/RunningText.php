<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RunningText extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'is_active',
        'speed',
        'background_color',
        'text_color',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Get active running text
    public static function getActive()
    {
        return self::where('is_active', true)->first();
    }
}