<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PullingMatrix extends Model
{
    use HasFactory;

    protected $table = 'pulling_matrices';

    protected $fillable = [
        'route',
        'dock',
        'cycle',
        'pulling_time',
    ];

    /**
     * Cari pulling time berdasarkan route, dock, cycle.
     * Return null jika tidak ditemukan (fallback ke logika default).
     */
    public static function findPullingTime(string $route, string $dock, string|int $cycle): ?string
    {
        $matrix = static::where('route', strtoupper(trim($route)))
            ->where('dock', strtoupper(trim($dock)))
            ->where('cycle', (string) $cycle)
            ->first();

        return $matrix ? $matrix->pulling_time : null;
    }
}