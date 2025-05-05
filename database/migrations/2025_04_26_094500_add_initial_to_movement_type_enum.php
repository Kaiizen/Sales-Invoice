<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddInitialToMovementTypeEnum extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE inventory_movements MODIFY COLUMN movement_type ENUM('purchase', 'sale', 'adjustment', 'transfer', 'initial', 'return', 'damage')");
    }

    public function down()
    {
        DB::statement("ALTER TABLE inventory_movements MODIFY COLUMN movement_type ENUM('purchase', 'sale', 'adjustment', 'transfer')");
        
        // Note: This will remove any records with movement_type 'initial', 'return', or 'damage'
    }
}