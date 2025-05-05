<?php

namespace Database\Seeders;

use App\Category;
use Illuminate\Database\Seeder;

class CategoryStockTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Find the flag fabric parent category and set it to track by square feet
        $flagFabricCategory = Category::where('name', 'Flag Fabric')->first();
        
        if ($flagFabricCategory) {
            $flagFabricCategory->update([
                'stock_type' => Category::STOCK_TYPE_SQUARE_FEET
            ]);
            
            $this->command->info('Flag Fabric category updated to track by square feet');
        } else {
            // If the category doesn't exist, create it
            Category::create([
                'name' => 'Flag Fabric',
                'description' => 'Fabrics used for flag production',
                'stock_type' => Category::STOCK_TYPE_SQUARE_FEET
            ]);
            
            $this->command->info('Flag Fabric category created with square feet tracking');
        }
        
        // Ensure all other categories use quantity tracking
        Category::where('name', '!=', 'Flag Fabric')
            ->update(['stock_type' => Category::STOCK_TYPE_QUANTITY]);
            
        $this->command->info('All other categories updated to track by quantity');
    }
}