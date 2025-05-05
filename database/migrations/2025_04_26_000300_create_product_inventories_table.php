<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductInventoriesTable extends Migration
{
    public function up()
    {
        Schema::create('product_inventories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('location_id');
            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->timestamps();
            
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('location_id')->references('id')->on('inventory_locations');
            
            // A product can only appear once per location
            $table->unique(['product_id', 'location_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_inventories');
    }
}