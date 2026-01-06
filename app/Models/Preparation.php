<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Preparation extends Model
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
        'pulling_date',     
        'pulling_time', 
        'arrival',
        'cycle',
        'address',
        'status',
        'scan_to_shipping',
        'moved_by',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'pulling_date' => 'date',
    ];

   
    public function getStatusAttribute()
    {
        try {
            $pullingDateTime = Carbon::parse($this->pulling_date->format('Y-m-d') . ' ' . $this->pulling_time);
            $now = Carbon::now();
            
            // Jika waktu sekarang sudah melewati pulling time = DELAY
            if ($now->greaterThan($pullingDateTime)) {
                return 'delay';
            }
            
            return 'open';
        } catch (\Exception $e) {
            return 'unknown';
        }
    }


    /**
     * Get status badge class untuk styling
     */
    public function getStatusBadgeAttribute()
    {
        switch ($this->status) {
            case 'delay':
                return 'bg-danger';
            case 'open':
                return 'bg-success';
            default:
                return 'bg-secondary';
        }
    }

    /**
     * Get status label untuk ditampilkan
     */
    public function getStatusLabelAttribute()
    {
        switch ($this->status) {
            case 'delay':
                return 'DELAY';
            case 'open':
                return 'OPEN';
            default:
                return 'UNKNOWN';
        }
    }

    /**
     * Get delay duration in human readable format
     */
    public function getDelayDurationAttribute()
    {
        if ($this->status !== 'delay') {
            return null;
        }

        try {
            $deliveryDateTime = Carbon::parse($this->delivery_date->format('Y-m-d') . ' ' . $this->delivery_time);
            $pullingDateTime = Carbon::parse($this->pulling_date->format('Y-m-d') . ' ' . $this->pulling_time);
            $now = Carbon::now();
            
            // Ambil yang paling awal terlambat
            $earliestDelay = $pullingDateTime->lessThan($deliveryDateTime) ? $pullingDateTime : $deliveryDateTime;
            
            return $now->diffForHumans($earliestDelay, true);
        } catch (\Exception $e) {
            return null;
        }
    }
}