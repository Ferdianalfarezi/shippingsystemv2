<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Token extends Model
{
    use HasFactory;

    protected $fillable = [
        'passcode',
        'expired_at'
    ];

    protected $casts = [
        'expired_at' => 'date'
    ];

    public function getExpiredAtAttribute($value)
    {
        return Carbon::parse($value);
    }
}