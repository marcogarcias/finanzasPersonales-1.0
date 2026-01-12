<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FictitiousUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::updateOrCreate(
            ['email' => 'premium@example.com'],
            [
                'name' => 'Premium User',
                'password' => \Illuminate\Support\Facades\Hash::make('userpremium123'),
                'plan_type' => 'paid',
                'license_expires_at' => now()->addWeek(),
                'remote_user_id' => 12345,
            ]
        );
    }
}
