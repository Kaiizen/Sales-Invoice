<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(AdminUserSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(CategoryStockTypeSeeder::class);
        $this->call(DefaultProductSeeder::class);
        $this->call(FabricRollSeeder::class);
        $this->call(FabricProductSeeder::class);
    }
}