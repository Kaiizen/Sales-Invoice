<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeProductIdNullableInSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['product_id']);
            
            // Modify columns
            $table->unsignedBigInteger('product_id')->nullable()->change();
            $table->integer('qty')->nullable()->change();
            
            // Re-add foreign key with proper constraint
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            // Remove foreign key first
            $table->dropForeign(['product_id']);
        });

        Schema::table('sales', function (Blueprint $table) {
            // Restore columns without foreign key
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
            $table->integer('qty')->nullable(false)->change();
        });

        Schema::table('sales', function (Blueprint $table) {
            // Recreate foreign key with CASCADE to maintain integrity
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('CASCADE');
        });
    }
}
