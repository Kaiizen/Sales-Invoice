<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Product;
use App\Category;
use App\Unit;
use App\Tax;
use Illuminate\Support\Str;

class FabricProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get or create a category for fabrics
        $category = Category::firstOrCreate(
            ['name' => 'Fabrics'],
            [
                'slug' => 'fabrics',
                'status' => true,
                'stock_type' => 'quantity'
            ]
        );

        // Get or create a unit for fabrics (yards, meters, etc.)
        $unit = Unit::firstOrCreate(
            ['name' => 'Yard'],
            [
                'slug' => 'yard',
                'status' => true
            ]
        );

        // Get or create a tax for fabrics
        $tax = Tax::firstOrCreate(
            ['name' => 'Standard Tax'],
            [
                'slug' => 'standard-tax',
                'status' => true
            ]
        );

        // Create a fabric product that is tracked by roll
        Product::create([
            'name' => 'Cotton Fabric',
            'slug' => Str::slug('Cotton Fabric'),
            'serial_number' => 1001,
            'model' => 'Cotton',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'tax_id' => $tax->id,
            'image' => 'default.jpg',
            'current_stock' => 0,
            'minimum_stock' => 5,
            'is_active' => true,
            'is_fabric' => true,
            'track_by_roll' => true,
            'roll_width' => 60, // 60 inches width
            'roll_length' => 36, // 36 inches length (1 yard)
            'total_square_feet' => 0, // Will be calculated based on rolls
            'alert_threshold_percent' => 20,
            'sales_price' => '12.99',
            'cost_price' => 8.99
        ]);

        // Create another fabric product that is not tracked by roll
        Product::create([
            'name' => 'Silk Fabric',
            'slug' => Str::slug('Silk Fabric'),
            'serial_number' => 1002,
            'model' => 'Silk',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'tax_id' => $tax->id,
            'image' => 'default.jpg',
            'current_stock' => 10,
            'minimum_stock' => 3,
            'is_active' => true,
            'is_fabric' => true,
            'track_by_roll' => false,
            'sales_price' => '24.99',
            'cost_price' => 18.99
        ]);
    }
}
