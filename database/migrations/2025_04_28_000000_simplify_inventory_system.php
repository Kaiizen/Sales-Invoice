<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SimplifyInventorySystem extends Migration
{
    public function up()
    {
        // 1. Simplify the movement_type enum to focus on in/out movements
        // First, update any existing values to match the new enum
        DB::table('inventory_movements')
            ->whereIn('movement_type', ['purchase', 'initial'])
            ->update(['movement_type' => 'in']);
            
        DB::table('inventory_movements')
            ->whereIn('movement_type', ['sale', 'adjustment', 'transfer', 'return', 'damage'])
            ->update(['movement_type' => 'out']);
            
        // Then modify the column
        DB::statement("ALTER TABLE inventory_movements MODIFY COLUMN movement_type ENUM('in', 'out')");
        
        // 2. Add unit_type and amount columns to handle different units (pieces, square feet, etc.)
        Schema::table('inventory_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_movements', 'unit_type')) {
                $table->string('unit_type')->default('piece')->after('quantity');
            }
            if (!Schema::hasColumn('inventory_movements', 'amount')) {
                $table->decimal('amount', 10, 2)->nullable()->after('unit_type');
            }
        });
        
        // 3. Update fabric_rolls table to simplify tracking
        Schema::table('fabric_rolls', function (Blueprint $table) {
            // Make sure we have the essential fields and remove unnecessary complexity
            if (!Schema::hasColumn('fabric_rolls', 'remaining_percentage')) {
                $table->decimal('remaining_percentage', 5, 2)->default(100)->after('remaining_square_feet');
            }
        });
        
        // 4. Add a simple view to track inventory movements
        DB::statement("
            CREATE OR REPLACE VIEW inventory_movement_summary AS
            SELECT 
                p.id as product_id,
                p.name as product_name,
                p.is_fabric,
                p.track_by_roll,
                SUM(CASE WHEN im.movement_type = 'in' THEN im.quantity ELSE 0 END) as total_in,
                SUM(CASE WHEN im.movement_type = 'out' THEN im.quantity ELSE 0 END) as total_out,
                SUM(CASE WHEN im.movement_type = 'in' THEN im.quantity ELSE -im.quantity END) as current_stock,
                SUM(CASE WHEN im.movement_type = 'in' AND p.is_fabric = 1 THEN im.amount ELSE 0 END) as total_square_feet_in,
                SUM(CASE WHEN im.movement_type = 'out' AND p.is_fabric = 1 THEN im.amount ELSE 0 END) as total_square_feet_out,
                SUM(CASE WHEN im.movement_type = 'in' AND p.is_fabric = 1 THEN im.amount ELSE -im.amount END) as current_square_feet
            FROM products p
            LEFT JOIN inventory_movements im ON p.id = im.product_id
            GROUP BY p.id, p.name, p.is_fabric, p.track_by_roll
        ");
    }

    public function down()
    {
        // Drop the view
        DB::statement("DROP VIEW IF EXISTS inventory_movement_summary");
        
        // Remove the added columns
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropColumn(['unit_type', 'amount']);
        });
        
        // Restore the original movement_type enum
        DB::statement("ALTER TABLE inventory_movements MODIFY COLUMN movement_type ENUM('purchase', 'sale', 'adjustment', 'transfer', 'initial', 'return', 'damage')");
        
        // Remove the added column from fabric_rolls if it was added
        if (Schema::hasColumn('fabric_rolls', 'remaining_percentage')) {
            Schema::table('fabric_rolls', function (Blueprint $table) {
                $table->dropColumn('remaining_percentage');
            });
        }
    }
}