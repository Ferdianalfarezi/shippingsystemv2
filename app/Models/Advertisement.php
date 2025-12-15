<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'file_path',
        'start_time',
        'duration',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'duration' => 'integer',
    ];

    /**
     * Cek apakah start_time sudah dipakai iklan active lain
     */
    public static function isTimeSlotTaken($startTime, $excludeId = null)
    {
        $query = self::where('start_time', $startTime)
            ->where('is_active', true);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Get iklan yang harus tampil sekarang
     */
    public static function getCurrentAd()
    {
        $now = now()->format('H:i:s');

        return self::where('is_active', true)
            ->where('start_time', '<=', $now)
            ->orderBy('start_time', 'desc')
            ->first();
    }

    /**
     * Get iklan yang match dengan jam sekarang (exact match untuk trigger)
     */
    public static function getAdForCurrentMinute()
    {
        $currentTime = now()->format('H:i');

        return self::where('is_active', true)
            ->whereRaw("DATE_FORMAT(start_time, '%H:%i') = ?", [$currentTime])
            ->first();
    }
}