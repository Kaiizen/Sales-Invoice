<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFlagDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('flag_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('custom_order_id');
            $table->string('flag_type');
            $table->decimal('height', 8, 2);
            $table->decimal('breadth', 8, 2);
            $table->decimal('square_feet', 8, 2);
            $table->decimal('price_per_square_feet', 8, 2);
            $table->integer('quantity')->default(1);
            $table->boolean('stitching')->default(false);
            $table->decimal('total_price', 10, 2);
            $table->timestamps();

            $table->foreign('custom_order_id')
                  ->references('id')
                  ->on('custom_orders')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('flag_details');
    }
}