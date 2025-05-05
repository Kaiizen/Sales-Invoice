<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInventoryLocationsTable extends Migration
{
    public function up()
    {
        Schema::create('inventory_locations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('warehouse_id');
            $table->string('name');
            $table->string('code')->unique();
            $table->string('zone')->nullable();
            $table->string('aisle')->nullable();
            $table->string('shelf')->nullable();
            $table->string('bin')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_locations');
    }
}