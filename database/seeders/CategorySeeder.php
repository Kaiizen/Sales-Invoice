<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Flag Fabric parent category
        $flagFabricCategory = Category::create([
            'name' => 'Flag Fabric',
            'slug' => Str::slug('Flag Fabric'),
            'status' => 1,
            'parent_id' => null
        ]);

        // Create child categories for Flag Fabric
        $flagFabricTypes = [
            'Polyester',
            'Nylon',
            'Cotton',
            'Silk',
            'Knitted Polyester',
            'Satin'
        ];

        foreach ($flagFabricTypes as $fabricType) {
            Category::create([
                'name' => $fabricType,
                'slug' => Str::slug($fabricType),
                'status' => 1,
                'parent_id' => $flagFabricCategory->id
            ]);
        }
    }
}