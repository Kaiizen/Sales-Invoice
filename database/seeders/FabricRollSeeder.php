<?php

namespace Database\Seeders;

use App\Category;
use App\FabricRoll;
use App\Product;
use App\ProductSupplier;
use App\Supplier;
use App\Tax;
use App\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FabricRollSeeder extends Seeder
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
                'status' => 1,
                'stock_type' => 'square_feet'
            ]
        );

        // Get a tax
        $tax = Tax::first() ?: Tax::create(['name' => 'Default Tax', 'percentage' => 13]);

        // Get a unit
        $unit = Unit::first() ?: Unit::create(['name' => 'Meter', 'short_name' => 'm']);

        // Get a supplier
        $supplier = Supplier::first() ?: Supplier::create([
            'name' => 'Fabric Supplier',
            'email' => 'fabric@example.com',
            'phone' => '1234567890',
            'address' => 'Supplier Address',
            'company' => 'Fabric Company'
        ]);

        // Create a fabric product
        // Dimensions now in feet (5 ft × 583.33 ft)
        $product = Product::create([
            'name' => 'Cotton Fabric',
            'slug' => Str::slug('Cotton Fabric'),
            'serial_number' => 1001, // Using an integer instead of a string
            'model' => 'CTN-100',
            'category_id' => $category->id,
            'sales_price' => 1200.00,
            'unit_id' => $unit->id,
            'tax_id' => $tax->id,
            'image' => 'default.jpg',
            'is_fabric' => true,
            'track_by_roll' => true,
            'roll_width' => 5, // 5 feet (was 60 inches)
            'roll_length' => 583.33, // 583.33 feet (was 7000 inches)
            'total_square_feet' => 2916.67, // 5 × 583.33 = 2916.65
            'alert_threshold_percent' => 20,
            'current_stock' => 1
        ]);

        // Add supplier information
        ProductSupplier::create([
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
            'price' => 1000.00
        ]);

        // Create a fabric roll
        FabricRoll::create([
            'product_id' => $product->id,
            'roll_number' => 'R' . time() . rand(100, 999),
            'width' => 5, // 5 feet
            'length' => 583.33, // 583.33 feet
            'original_square_feet' => 2916.67, // 5 × 583.33 = 2916.65
            'remaining_square_feet' => 2916.67,
            'supplier_id' => $supplier->id,
            'received_date' => now(),
            'status' => 'active',
            'notes' => 'Initial fabric roll created by seeder'
        ]);

        // Create a second fabric product
        // Dimensions now in feet (6 ft × 416.67 ft)
        $product2 = Product::create([
            'name' => 'Polyester Fabric',
            'slug' => Str::slug('Polyester Fabric'),
            'serial_number' => 1002, // Using an integer instead of a string
            'model' => 'PLY-200',
            'category_id' => $category->id,
            'sales_price' => 1500.00,
            'unit_id' => $unit->id,
            'tax_id' => $tax->id,
            'image' => 'default.jpg',
            'is_fabric' => true,
            'track_by_roll' => true,
            'roll_width' => 6, // 6 feet (was 72 inches)
            'roll_length' => 416.67, // 416.67 feet (was 5000 inches)
            'total_square_feet' => 2500.00, // 6 × 416.67 = 2500.02
            'alert_threshold_percent' => 20,
            'current_stock' => 1
        ]);

        // Add supplier information for second product
        ProductSupplier::create([
            'product_id' => $product2->id,
            'supplier_id' => $supplier->id,
            'price' => 1300.00
        ]);

        // Create a fabric roll for second product
        FabricRoll::create([
            'product_id' => $product2->id,
            'roll_number' => 'R' . time() . rand(100, 999),
            'width' => 6, // 6 feet
            'length' => 416.67, // 416.67 feet
            'original_square_feet' => 2500.00, // 6 × 416.67 = 2500.02
            'remaining_square_feet' => 2500.00,
            'supplier_id' => $supplier->id,
            'received_date' => now(),
            'status' => 'active',
            'notes' => 'Initial fabric roll created by seeder'
        ]);
    }
}
