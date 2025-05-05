<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRfidTagsTable extends Migration
{
    public function up()
    {
        Schema::create('rfid_tags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tag_id')->unique();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->timestamp('last_scanned_at')->nullable();
            $table->unsignedBigInteger('last_location_id')->nullable();
            $table->enum('status', ['active', 'inactive', 'lost', 'damaged'])->default('active');
            $table->timestamps();
            
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('batch_id')->references('id')->on('product_batches');
            $table->foreign('last_location_id')->references('id')->on('inventory_locations');
        });
    }

    public function down()
    {
        Schema::dropIfExists('rfid_tags');
    }
}