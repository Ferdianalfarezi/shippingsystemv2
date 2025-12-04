<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Preparation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Users
        User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
        ]);

        User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Scanner User',
            'username' => 'scanner',
            'password' => Hash::make('password'),
            'role' => 'scanner',
        ]);

        // Create Sample Preparations
        Preparation::create([
            'route' => 'Jakarta - Bandung',
            'logistic_partners' => 'JNE Express',
            'no_dn' => 'DN-2024-001',
            'customers' => 'PT Maju Jaya',
            'dock' => 'A1',
            'delivery_date' => now()->addDays(2),
            'delivery_time' => '09:00:00',
            'cycle' => 1,
            'pulling_date' => now()->addDay(),
            'pulling_time' => '07:00:00',
        ]);

        Preparation::create([
            'route' => 'Jakarta - Surabaya',
            'logistic_partners' => 'TIKI',
            'no_dn' => 'DN-2024-002',
            'customers' => 'CV Sejahtera',
            'dock' => 'B2',
            'delivery_date' => now()->addDays(3),
            'delivery_time' => '10:30:00',
            'cycle' => 2,
            'pulling_date' => now()->addDays(2),
            'pulling_time' => '08:00:00',
        ]);

        Preparation::create([
            'route' => 'Jakarta - Semarang',
            'logistic_partners' => 'SiCepat',
            'no_dn' => 'DN-2024-003',
            'customers' => 'PT Berkah Selalu',
            'dock' => 'C3',
            'delivery_date' => now()->addDays(1),
            'delivery_time' => '14:00:00',
            'cycle' => 1,
            'pulling_date' => now(),
            'pulling_time' => '06:00:00',
        ]);
    }
}