<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEcommerceIntegrationTables extends Migration
{
    public function up()
    {
        Schema::create('ecommerce_platforms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('api_url');
            $table->string('api_key')->nullable();
            $table->string('api_secret')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();
        });
        
        Schema::create('product_ecommerce_platforms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('platform_id');
            $table->string('platform_product_id');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->json('platform_data')->nullable();
            $table->timestamps();
            
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('platform_id')->references('id')->on('ecommerce_platforms');
            
            // A product can only be linked once to a platform
            $table->unique(['product_id', 'platform_id']);
        });
        
        Schema::create('ecommerce_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('platform_id');
            $table->string('platform_order_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', [
                'pending', 
                'processing', 
                'shipped', 
                'delivered', 
                'cancelled'
            ])->default('pending');
            $table->json('order_data');
            $table->timestamp('platform_created_at');
            $table->timestamps();
            
            $table->foreign('platform_id')->references('id')->on('ecommerce_platforms');
            $table->foreign('customer_id')->references('id')->on('customers');
            
            // An order should be unique per platform
            $table->unique(['platform_id', 'platform_order_id']);
        });
        
        Schema::create('ecommerce_order_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ecommerce_order_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('platform_product_id');
            $table->string('name');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->json('item_data')->nullable();
            $table->timestamps();
            
            $table->foreign('ecommerce_order_id')->references('id')->on('ecommerce_orders');
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ecommerce_order_items');
        Schema::dropIfExists('ecommerce_orders');
        Schema::dropIfExists('product_ecommerce_platforms');
        Schema::dropIfExists('ecommerce_platforms');
    }
}