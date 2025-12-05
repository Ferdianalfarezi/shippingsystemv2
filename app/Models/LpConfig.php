<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LpConfig extends Model
{
    protected $fillable = [
        'route',
        'logistic_partner',
    ];

    // Relationship dengan Preparation
    public function preparations()
    {
        return $this->hasMany(Preparation::class, 'route', 'route');
    }
}