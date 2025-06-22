<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            'WAXY CORN',
            'SWEET CORN',
            'CUCUMBER',
            'LONG BEAN',
            'TOMATO',
            'BITTER GOURD',
            'CHILLI - BIRD PAPER',
        ];

        DB::table('product_categories')->insert(array_map(fn($item) => [
            'name' => $item,
        ], $items));
    }
}
