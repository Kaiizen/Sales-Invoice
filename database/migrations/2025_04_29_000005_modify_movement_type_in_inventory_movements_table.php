<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ModifyMovementTypeInInventoryMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // First, check the current ENUM values
        $columns = DB::select("SHOW COLUMNS FROM inventory_movements WHERE Field = 'movement_type'");
        $currentType = $columns[0]->Type;
        
        // If it's an ENUM, modify it to include 'in' and 'out'
        if (strpos($currentType, 'enum') !== false) {
            // Convert the column to a string type first to avoid ENUM constraints
            DB::statement("ALTER TABLE inventory_movements MODIFY movement_type VARCHAR(20)");
            
            // Now we can safely update any existing values if needed
            DB::statement("UPDATE inventory_movements SET movement_type = 'in' WHERE movement_type = 'stock_in'");
            DB::statement("UPDATE inventory_movements SET movement_type = 'out' WHERE movement_type = 'stock_out'");
            
            // Finally, convert back to ENUM with the correct values
            DB::statement("ALTER TABLE inventory_movements MODIFY movement_type ENUM('in', 'out', 'initial') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No need to revert as this is a corrective migration
    }
}