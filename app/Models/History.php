<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class History extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'histories';

    protected $fillable = [
        'route',
        'logistic_partners',
        'no_dn',
        'customers',
        'dock',
        'cycle',
        'address',
        'pulling_date',
        'pulling_time',
        'delivery_date',
        'delivery_time',
        'scan_to_shipping',
        'arrival',
        'scan_to_delivery',
        'completed_at',
        'final_status',
        'total_business_hours',
        'moved_by',
    ];

    protected $casts = [
        'pulling_date' => 'date',
        'delivery_date' => 'date',
        'scan_to_shipping' => 'datetime',
        'arrival' => 'datetime',
        'scan_to_delivery' => 'datetime',
        'completed_at' => 'datetime',
        'total_business_hours' => 'float',
    ];

    /**
     * Get formatted completed time
     */
    public function getFormattedCompletedAtAttribute(): ?string
    {
        return $this->completed_at?->format('d-m-y H:i');
    }

    /**
     * Get formatted scan to shipping time
     */
    public function getFormattedScanToShippingAttribute(): ?string
    {
        return $this->scan_to_shipping?->format('d-m-y H:i');
    }

    /**
     * Get formatted arrival time
     */
    public function getFormattedArrivalAttribute(): ?string
    {
        return $this->arrival?->format('d-m-y H:i');
    }

    /**
     * Get formatted scan to delivery time
     */
    public function getFormattedScanToDeliveryAttribute(): ?string
    {
        return $this->scan_to_delivery?->format('d-m-y H:i');
    }

    /**
     * Get formatted pulling datetime
     */
    public function getFormattedPullingDatetimeAttribute(): ?string
    {
        if (!$this->pulling_date) return null;
        return $this->pulling_date->format('d-m-y') . ' ' . ($this->pulling_time ?? '');
    }

    /**
     * Get formatted delivery datetime
     */
    public function getFormattedDeliveryDatetimeAttribute(): ?string
    {
        if (!$this->delivery_date) return null;
        return $this->delivery_date->format('d-m-y') . ' ' . ($this->delivery_time ?? '');
    }

    /**
     * Get formatted total duration
     */
    public function getFormattedDurationAttribute(): string
    {
        $hours = $this->total_business_hours;
        
        if ($hours >= 24) {
            $days = floor($hours / 24);
            $remainingHours = round($hours % 24);
            return "{$days}d {$remainingHours}h";
        }
        
        return round($hours, 1) . " jam";
    }

    /**
     * Calculate duration between two timestamps
     */
    public function getDurationBetween(?Carbon $start, ?Carbon $end): ?string
    {
        if (!$start || !$end) return '-';
        
        $diffMinutes = $start->diffInMinutes($end);
        
        if ($diffMinutes < 60) {
            return $diffMinutes . ' menit';
        }
        
        $hours = floor($diffMinutes / 60);
        $minutes = $diffMinutes % 60;
        
        if ($hours >= 24) {
            $days = floor($hours / 24);
            $remainingHours = $hours % 24;
            return "{$days}d {$remainingHours}h {$minutes}m";
        }
        
        return "{$hours}h {$minutes}m";
    }

    /**
     * Get shipping duration (scan_to_shipping → arrival)
     */
    public function getShippingDurationAttribute(): string
    {
        return $this->getDurationBetween($this->scan_to_shipping, $this->arrival);
    }

    /**
     * Get loading duration (arrival → scan_to_delivery)
     */
    public function getLoadingDurationAttribute(): string
    {
        return $this->getDurationBetween($this->arrival, $this->scan_to_delivery);
    }

    /**
     * Get delivery duration (scan_to_delivery → completed_at)
     */
    public function getDeliveryDurationAttribute(): string
    {
        return $this->getDurationBetween($this->scan_to_delivery, $this->completed_at);
    }

    /**
     * Get total journey duration (scan_to_shipping → completed_at)
     */
    public function getTotalJourneyDurationAttribute(): string
    {
        return $this->getDurationBetween($this->scan_to_shipping, $this->completed_at);
    }
}