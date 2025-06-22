<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'crop' => 'WAXY CORN',
                'name' => 'LILAC 22 F1',
                'price_1' => 306000,
                'price_2' => 76500,
                'uom_1' => 'kg',
                'uom_2' => 'pcs',
            ],
            [
                'crop' => 'SWEET CORN',
                'name' => 'MADU 59 F1',
                'price_1' => 380000,
                'price_2' => 95000,
                'uom_1' => 'kg',
                'uom_2' => 'pcs',
            ],
            [
                'crop' => 'SWEET CORN',
                'name' => 'ANARA 81 F1',
                'price_1' => 380000,
                'price_2' => 95000,
                'uom_1' => 'kg',
                'uom_2' => 'pcs',
            ],
            [
                'crop' => 'SWEET CORN',
                'name' => 'REVA F1',
                'price_1' => 380000,
                'price_2' => 95000,
                'uom_1' => 'kg',
                'uom_2' => 'pcs',
            ],
            [
                'crop' => 'CUCUMBER',
                'name' => 'GOGOR 22 F1',
                'price_1' => 2250000,
                'price_2' => 45000,
                'uom_1' => 'kg',
                'uom_2' => 'pcs',
            ],
            [
                'crop' => 'CUCUMBER',
                'name' => 'LAVANTA F1',
                'price_1' => 1850000,
                'price_2' => 37000,
                'uom_1' => 'kg',
                'uom_2' => 'pcs',
            ],
            [
                'crop' => 'LONG BEAN',
                'name' => 'HERRA 22',
                'price_1' => 200000,
                'price_2' => 100000,
                'uom_1' => 'kg',
                'uom_2' => 'pcs',
            ],
            [
                'crop' => 'TOMATO',
                'name' => 'NONA 23 F1',
                'price_1' => 34000000,
                'price_2' => 170000,
                'uom_1' => 'kg',
                'uom_2' => 'pcs',
            ],
            [
                'crop' => 'TOMATO',
                'name' => 'DEBY 23 F1',
                'price_1' => 21000000,
                'price_2' => 105000,
                'uom_1' => 'kg',
                'uom_2' => 'pcs',
            ],
            [
                'crop' => 'BITTER GOURD',
                'name' => 'BEIJING 23 F1',
                'price_1' => 2100000,
                'price_2' => 21000,
                'uom_1' => 'kg',
                'uom_2' => 'pcs',
            ],
            [
                'crop' => 'CHILLI - BIRD PAPER',
                'name' => 'SHIMA',
                'price_1' => 5000000,
                'price_2' => 50000,
                'uom_1' => 'kg',
                'uom_2' => 'pcs',
            ],
        ];

        // Tambahkan timestamp jika dibutuhkan
        foreach ($products as &$product) {
            $product['category_id'] = DB::table('product_categories')
                ->where('name', $product['crop'])
                ->value('id');
            unset($product['crop']);
        }

        DB::table('products')->insert($products);
    }
}
