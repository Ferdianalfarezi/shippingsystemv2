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
        'moved_by',
        'pulling_date',
        'pulling_time',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'arrival' => 'datetime',
        'scan_to_shipping' => 'datetime',
        'pulling_date' => 'date',
    ];

    /**
     * Boot method untuk auto-update status
     */
    protected static function boot()
    {
        parent::boot();

        // Update status setiap kali model di-retrieve (kecuali sudah ON LOADING)
        static::retrieved(function ($model) {
            if ($model->arrival === null) {
                $newStatus = $model->calculateStatus();
                if ($newStatus !== $model->status) {
                    $model->status = $newStatus;
                    $model->saveQuietly();
                }
            }
        });
    }

    /**
     * Calculate status based on arrival and delivery time
     * - ON LOADING: sudah di-scan (arrival terisi)
     * - DELAY: delivery datetime sudah lewat (belum scan)
     * - NORMAL: dalam 4 jam sebelum delivery time (belum scan)
     * - ADVANCE: lebih dari 4 jam sebelum delivery time (belum scan)
     */
    public function calculateStatus(): string
    {
        // Jika sudah ada arrival = ON LOADING
        if ($this->arrival !== null) {
            return 'on_loading';
        }

        try {
            $deliveryDateTime = Carbon::parse($this->delivery_date->format('Y-m-d') . ' ' . $this->delivery_time);
            $now = Carbon::now();
            
            // 4 jam sebelum delivery time
            $normalStartTime = $deliveryDateTime->copy()->subHours(4);
            
            // Jika sudah melewati delivery time = DELAY
            if ($now->greaterThan($deliveryDateTime)) {
                return 'delay';
            }
            
            // Jika dalam range 4 jam sebelum delivery time = NORMAL
            if ($now->greaterThanOrEqualTo($normalStartTime)) {
                return 'normal';
            }
            
            // Jika lebih dari 4 jam sebelum delivery time = ADVANCE
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
        $status = $this->calculateStatus();
        
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
        $status = $this->calculateStatus();
        
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
                return 'Terlambat ' . $now->diffForHumans($deliveryDateTime, true);
            } else {
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
     * Scope untuk filter by route
     */
    public function scopeByRoute($query, string $route)
    {
        return $query->where('route', $route);
    }

    /**
     * Scope untuk filter ADVANCE (belum scan DAN > 4 jam sebelum delivery)
     */
    public function scopeAdvance($query)
    {
        return $query->whereNull('arrival')
            ->whereRaw("CONCAT(delivery_date, ' ', delivery_time) > DATE_ADD(NOW(), INTERVAL 4 HOUR)");
    }

    /**
     * Scope untuk filter NORMAL (belum scan DAN <= 4 jam sebelum delivery DAN belum lewat)
     */
    public function scopeNormal($query)
    {
        return $query->whereNull('arrival')
            ->whereRaw("CONCAT(delivery_date, ' ', delivery_time) <= DATE_ADD(NOW(), INTERVAL 4 HOUR)")
            ->whereRaw("CONCAT(delivery_date, ' ', delivery_time) >= NOW()");
    }

    /**
     * Scope untuk filter DELAY (belum scan DAN delivery datetime sudah lewat)
     */
    public function scopeDelay($query)
    {
        return $query->whereNull('arrival')
            ->whereRaw("CONCAT(delivery_date, ' ', delivery_time) < NOW()");
    }

    /**
     * Scope untuk filter ON LOADING (sudah scan / arrival terisi)
     */
    public function scopeOnLoading($query)
    {
        return $query->whereNotNull('arrival');
    }
}