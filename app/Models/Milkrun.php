<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Milkrun extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customers',
        'route',
        'logistic_partners',
        'cycle',
        'dock',
        'delivery_date',
        'delivery_time',
        'arrival',
        'departure',
        'status',
        'dn_count',
        'no_dns',
        'address',
        'moved_by',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'arrival' => 'datetime',
        'departure' => 'datetime',
        'no_dns' => 'array',
    ];

    /**
     * Calculate status based on delivery_datetime and arrival
     * 
     * Target = delivery_date + delivery_time
     * 
     * - advance  : arrival < target - 15 menit (datang lebih dari 15 menit sebelum jadwal)
     * - on_time  : target - 15 menit <= arrival <= target + 30 menit (rentang normal)
     * - delay    : arrival > target + 30 menit (datang lebih dari 30 menit setelah jadwal)
     */
    public function calculateStatus(): string
    {
        if (!$this->arrival) {
            return 'pending';
        }

        $targetDateTime = Carbon::parse($this->delivery_date->format('Y-m-d') . ' ' . $this->delivery_time);
        $arrivalDateTime = Carbon::parse($this->arrival);
        
        // Bulatkan biar gak ada sisa desimal
        $diffInMinutes = round($arrivalDateTime->diffInMinutes($targetDateTime, false) * -1);
        
        if ($diffInMinutes < -15) {
            return 'advance';
        } elseif ($diffInMinutes > 30) {
            return 'delay';
        } else {
            return 'on_time';
        }
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        $status = $this->arrival ? $this->calculateStatus() : $this->status;
        
        return match($status) {
            'advance' => 'Advance',
            'on_time' => 'On Time',
            'delay' => 'Delay',
            'pending' => 'Pending',
            default => ucfirst($status),
        };
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute(): string
    {
        $status = $this->arrival ? $this->calculateStatus() : $this->status;
        
        return match($status) {
            'advance' => 'bg-warning text-dark',
            'on_time' => 'bg-success',
            'delay' => 'bg-danger',
            'pending' => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    /**
     * Get time difference info
     */
    public function getTimeDiffInfoAttribute(): string
    {
        if (!$this->arrival) {
            return 'Belum arrival';
        }

        $targetDateTime = Carbon::parse($this->delivery_date->format('Y-m-d') . ' ' . $this->delivery_time);
        $arrivalDateTime = Carbon::parse($this->arrival);
        
        // Bulatkan ke menit terdekat
        $diffInMinutes = round($arrivalDateTime->diffInMinutes($targetDateTime, false) * -1);
        
        if ($diffInMinutes < 0) {
            return abs($diffInMinutes) . " menit lebih awal";
        } elseif ($diffInMinutes > 0) {
            return $diffInMinutes . " menit terlambat";
        } else {
            return "Tepat waktu";
        }
    }

    /**
     * Get delivery datetime combined
     */
    public function getDeliveryDatetimeAttribute(): Carbon
    {
        return Carbon::parse($this->delivery_date->format('Y-m-d') . ' ' . $this->delivery_time);
    }

    /**
     * Scope for pending milkruns
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for completed milkruns (has arrival)
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('arrival');
    }
}