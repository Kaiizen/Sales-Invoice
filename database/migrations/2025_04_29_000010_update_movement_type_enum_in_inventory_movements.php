<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateMovementTypeEnumInInventoryMovements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // First, update any existing 'adjustment' values to 'out'
        DB::table('inventory_movements')
            ->where('movement_type', 'adjustment')
            ->update(['movement_type' => 'out']);
            
        // Then modify the column to include 'adjustment'
        DB::statement("ALTER TABLE inventory_movements MODIFY COLUMN movement_type ENUM('in', 'out', 'adjustment')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Update any 'adjustment' values to 'out' before removing the enum value
        DB::table('inventory_movements')
            ->where('movement_type', 'adjustment')
            ->update(['movement_type' => 'out']);
            
        // Revert to the previous enum values
        DB::statement("ALTER TABLE inventory_movements MODIFY COLUMN movement_type ENUM('in', 'out')");
    }
}