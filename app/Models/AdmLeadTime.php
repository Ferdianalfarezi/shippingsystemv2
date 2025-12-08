<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmLeadTime extends Model
{
    use HasFactory;

    protected $table = 'adm_lead_times';

    protected $fillable = [
        'route',
        'dock',
        'cycle',
        'lead_time',
    ];

    /**
     * Get lead time for specific route, dock, and cycle
     * Returns default 3 hours if not found
     *
     * @param string $route
     * @param string $dock
     * @param string|int $cycle
     * @return string Lead time in H:i:s format
     */
    public static function getLeadTime(string $route, string $dock, $cycle): string
    {
        $config = self::where('route', $route)
            ->where('dock', $dock)
            ->where('cycle', $cycle)
            ->first();

        return $config ? $config->lead_time : '03:00:00'; // Default 3 jam
    }

    /**
     * Get lead time in minutes
     *
     * @param string $route
     * @param string $dock
     * @param string|int $cycle
     * @return int Minutes
     */
    public static function getLeadTimeInMinutes(string $route, string $dock, $cycle): int
    {
        $leadTime = self::getLeadTime($route, $dock, $cycle);
        $parts = explode(':', $leadTime);
        
        $hours = (int) ($parts[0] ?? 0);
        $minutes = (int) ($parts[1] ?? 0);
        $seconds = (int) ($parts[2] ?? 0);

        return ($hours * 60) + $minutes + (int)($seconds / 60);
    }
}