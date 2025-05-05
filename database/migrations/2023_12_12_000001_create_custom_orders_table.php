<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('custom_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('flag_type');
            $table->string('size');
            $table->integer('quantity');
            $table->string('stitching_option');
            $table->text('special_instructions')->nullable();
            $table->string('design_file')->nullable();
            $table->string('status')->default('Pending');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('custom_orders');
    }
}