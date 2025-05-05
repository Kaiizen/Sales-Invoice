<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductForecastsTable extends Migration
{
    public function up()
    {
        Schema::create('product_forecasts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_id');
            $table->date('forecast_date');
            $table->integer('predicted_demand');
            $table->decimal('confidence_level', 5, 2)->default(0); // 0-100%
            $table->json('factors_considered')->nullable();
            $table->integer('recommended_stock_level');
            $table->timestamps();
            
            $table->foreign('product_id')->references('id')->on('products');
            
            // A product can only have one forecast per date
            $table->unique(['product_id', 'forecast_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_forecasts');
    }
}