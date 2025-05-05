<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomFieldsTables extends Migration
{
    public function up()
    {
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('label');
            $table->string('entity_type'); // 'product', 'supplier', etc.
            $table->enum('field_type', [
                'text', 
                'textarea', 
                'number', 
                'date', 
                'select', 
                'checkbox', 
                'radio'
            ]);
            $table->json('options')->nullable(); // For select, checkbox, radio
            $table->boolean('is_required')->default(false);
            $table->boolean('is_filterable')->default(false);
            $table->boolean('is_searchable')->default(false);
            $table->integer('display_order')->default(0);
            $table->unsignedBigInteger('category_id')->nullable(); // For category-specific fields
            $table->timestamps();
            
            $table->foreign('category_id')->references('id')->on('categories');
        });
        
        Schema::create('custom_field_values', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('custom_field_id');
            $table->unsignedBigInteger('entity_id');
            $table->text('value')->nullable();
            $table->timestamps();
            
            $table->foreign('custom_field_id')->references('id')->on('custom_fields');
            
            // An entity can only have one value per custom field
            $table->unique(['custom_field_id', 'entity_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('custom_field_values');
        Schema::dropIfExists('custom_fields');
    }
}