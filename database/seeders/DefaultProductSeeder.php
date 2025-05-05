<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Product;

class DefaultProductSeeder extends Seeder
{
    public function run()
    {
        Product::firstOrCreate(
            ['name' => 'Default Product'],
            [
                'code' => 'DEFPROD',
                'description' => 'Default product for system operations',
                'price' => 0.00,
                'quantity' => 9999,
                'unit_id' => 1,
                'category_id' => 1,
                'supplier_id' => 1
            ]
        );
    }
}