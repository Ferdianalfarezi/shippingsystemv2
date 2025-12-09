<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Shipping extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'route',
        'logistic_partners',
        'no_dn',
        'customers',
        'dock',
        'delivery_date',
        'delivery_time',
        'arrival',
        'cycle',
        'address',
        'status',
        'scan_to_shipping',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'arrival' => 'datetime',
        'scan_to_shipping' => 'datetime',
    ];

    /**
     * Boot method untuk auto-update status
     */
    protected static function boot()
    {
        parent::boot();

        // Update status setiap kali model di-retrieve (kecuali sudah ON LOADING)
        static::retrieved(function ($model) {
            if ($model->status !== 'on_loading' && $model->arrival === null) {
                $newStatus = $model->calculateStatus();
                if ($newStatus !== $model->status) {
                    $model->status = $newStatus;
                    $model->saveQuietly(); // Save tanpa trigger events
                }
            }
        });
    }

    /**
     * Calculate status based on delivery time
     * - ADVANCE: lebih dari 15 menit sebelum delivery time
     * - NORMAL: dalam 15 menit sebelum delivery time sampai delivery time
     * - DELAY: sudah melewati delivery time
     * - ON LOADING: sudah di-scan (arrival terisi)
     */
    public function calculateStatus(): string
    {
        // Jika sudah ada arrival, berarti ON LOADING
        if ($this->arrival !== null) {
            return 'on_loading';
        }

        try {
            $deliveryDateTime = Carbon::parse($this->delivery_date->format('Y-m-d') . ' ' . $this->delivery_time);
            $now = Carbon::now();
            
            // 15 menit sebelum delivery time
            $normalStartTime = $deliveryDateTime->copy()->subMinutes(15);
            
            // Jika sudah melewati delivery time = DELAY
            if ($now->greaterThan($deliveryDateTime)) {
                return 'delay';
            }
            
            // Jika dalam range 15 menit sebelum delivery time = NORMAL
            if ($now->greaterThanOrEqualTo($normalStartTime)) {
                return 'normal';
            }
            
            // Jika masih lebih dari 15 menit sebelum delivery time = ADVANCE
            return 'advance';
            
        } catch (\Exception $e) {
            return 'normal';
        }
    }

    /**
     * Get computed status (real-time calculation)
     */
    public function getComputedStatusAttribute(): string
    {
        return $this->calculateStatus();
    }

    /**
     * Get status badge class untuk styling
     */
    public function getStatusBadgeAttribute(): string
    {
        $status = $this->arrival !== null ? 'on_loading' : $this->calculateStatus();
        
        return match($status) {
            'advance' => 'bg-warning text-dark',
            'normal' => 'bg-success',
            'delay' => 'bg-danger',
            'on_loading' => 'bg-primary',
            default => 'bg-secondary',
        };
    }

    /**
     * Get status label untuk ditampilkan
     */
    public function getStatusLabelAttribute(): string
    {
        $status = $this->arrival !== null ? 'on_loading' : $this->calculateStatus();
        
        return match($status) {
            'advance' => 'ADVANCE',
            'normal' => 'NORMAL',
            'delay' => 'DELAY',
            'on_loading' => 'ON LOADING',
            default => 'UNKNOWN',
        };
    }

    /**
     * Get time remaining or delay duration
     */
    public function getTimeInfoAttribute(): ?string
    {
        if ($this->arrival !== null) {
            return 'Arrived: ' . $this->arrival->format('H:i:s');
        }

        try {
            $deliveryDateTime = Carbon::parse($this->delivery_date->format('Y-m-d') . ' ' . $this->delivery_time);
            $now = Carbon::now();
            
            if ($now->greaterThan($deliveryDateTime)) {
                // Delay duration
                return 'Terlambat ' . $now->diffForHumans($deliveryDateTime, true);
            } else {
                // Time remaining
                return 'Sisa ' . $now->diffForHumans($deliveryDateTime, true);
            }
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Scope untuk filter by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk filter yang belum di-scan (arrival null)
     */
    public function scopeNotScanned($query)
    {
        return $query->whereNull('arrival');
    }

    /**
     * Scope untuk filter yang sudah di-scan (on loading)
     */
    public function scopeOnLoading($query)
    {
        return $query->whereNotNull('arrival');
    }

    /**
     * Scope untuk filter by route
     */
    public function scopeByRoute($query, string $route)
    {
        return $query->where('route', $route);
    }

    /**
     * Mark as on loading (scan arrival)
     */
    public function markAsOnLoading(): bool
    {
        $this->arrival = Carbon::now();
        $this->status = 'on_loading';
        return $this->save();
    }
}