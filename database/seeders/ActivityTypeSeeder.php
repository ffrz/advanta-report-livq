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
                'default_quarter_target' => 3,
                'default_month1_target' => 1,
                'default_month2_target' => 1,
                'default_month3_target' => 1,
                'weight' => 0,
                'require_product' => false,
                'active' => true,
            ],
            [
                'name' => 'FM',
                'description' => 'Farmers Meeting',
                'default_quarter_target' => 9,
                'default_month1_target' => 3,
                'default_month2_target' => 3,
                'default_month3_target' => 3,
                'weight' => 0,
                'require_product' => false,
                'active' => true,
            ],
            [
                'name' => 'FT',
                'description' => 'Field Trip',
                'default_quarter_target' => 3,
                'default_month1_target' => 1,
                'default_month2_target' => 1,
                'default_month3_target' => 1,
                'weight' => 0,
                'require_product' => false,
                'active' => true,
            ],
            [
                'name' => 'FFD',
                'description' => 'Farm Field Day',
                'default_quarter_target' => 1,
                'default_month1_target' => 0,
                'default_month2_target' => 0,
                'default_month3_target' => 1,
                'weight' => 0,
                'require_product' => true,
                'active' => true,
            ],
        ]);
    }
}
