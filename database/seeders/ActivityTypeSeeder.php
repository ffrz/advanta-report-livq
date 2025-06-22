<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActivityTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('activity_types')->insert([
            [
                'name' => 'ODP',
                'description' => 'One Day Promo',
                'target_period' => 'monthly',
                'default_target' => 1,
                'weight' => 0,
                'active' => true,
            ],
            [
                'name' => 'FM',
                'description' => 'Farmers Meeting',
                'target_period' => 'monthly',
                'default_target' => 3,
                'weight' => 0,
                'active' => true,
            ],
            [
                'name' => 'FT',
                'description' => 'Field Trip',
                'target_period' => 'monthly',
                'default_target' => 1,
                'weight' => 0,
                'active' => true,
            ],
            [
                'name' => 'FFD',
                'description' => 'Farm Field Day',
                'target_period' => 'quarterly',
                'default_target' => 1,
                'weight' => 0,
                'active' => true,
            ],
        ]);
    }
}
