<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Delivery extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'route',
        'logistic_partners',
        'no_dn',
        'customers',
        'dock',
        'cycle',
        'address',
        'scan_to_delivery',
        'moved_by',
    ];

    protected $casts = [
        'scan_to_delivery' => 'datetime',
    ];

    /**
     * Hitung business hours antara 2 datetime (exclude Sabtu & Minggu)
     * Asumsi: 24 jam per hari kerja (Senin-Jumat)
     */
    public static function calculateBusinessHours(Carbon $start, Carbon $end): float
    {
        $hours = 0;
        $current = $start->copy();
        
        while ($current < $end) {
            // Skip weekend (Sabtu = 6, Minggu = 0)
            if (!$current->isWeekend()) {
                // Hitung sampai akhir hari atau sampai end, mana yang lebih dulu
                $endOfDay = $current->copy()->endOfDay();
                $stopTime = $end < $endOfDay ? $end : $endOfDay;
                
                $hours += $current->floatDiffInHours($stopTime);
            }
            
            // Pindah ke awal hari berikutnya
            $current = $current->copy()->addDay()->startOfDay();
        }
        
        return $hours;
    }

    /**
     * Get calculated status attribute
     * Status dihitung dari scan_to_delivery → sekarang
     * ≥ 48 jam business hours → Delay
     * < 48 jam → Normal
     */
    public function getStatusAttribute(): string
    {
        if (!$this->scan_to_delivery) {
            return 'normal';
        }

        $now = Carbon::now();
        $businessHours = self::calculateBusinessHours($this->scan_to_delivery, $now);
        
        return $businessHours >= 48 ? 'delay' : 'normal';
    }

    /**
     * Get business hours elapsed
     */
    public function getBusinessHoursElapsedAttribute(): float
    {
        if (!$this->scan_to_delivery) {
            return 0;
        }

        return round(self::calculateBusinessHours($this->scan_to_delivery, Carbon::now()), 1);
    }

    /**
     * Get delay duration (formatted string)
     */
    public function getDelayDurationAttribute(): string
    {
        $hours = $this->business_hours_elapsed;
        
        if ($hours < 48) {
            return '';
        }
        
        $delayHours = $hours - 48;
        
        if ($delayHours >= 24) {
            $days = floor($delayHours / 24);
            $remainingHours = round($delayHours % 24);
            return "{$days}d {$remainingHours}h";
        }
        
        return round($delayHours) . " jam";
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute(): string
    {
        return $this->status === 'delay' ? 'bg-danger' : 'bg-success';
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'delay' ? 'Delay' : 'Normal';
    }

    /**
     * Get formatted scan time
     */
    public function getFormattedScanTimeAttribute(): ?string
    {
        return $this->scan_to_delivery?->format('d-m-y H:i');
    }

    /**
     * Scope untuk filter by status (calculated)
     */
    public function scopeByStatus($query, string $status)
    {
        // Karena status adalah calculated attribute, kita perlu filter di PHP level
        // atau gunakan raw query untuk performance
        return $query;
    }

    /**
     * Check if delivery is delayed
     */
    public function isDelayed(): bool
    {
        return $this->status === 'delay';
    }
}