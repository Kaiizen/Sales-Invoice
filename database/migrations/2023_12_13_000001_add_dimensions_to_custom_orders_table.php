<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDimensionsToCustomOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->decimal('height', 8, 2)->nullable()->after('size');
            $table->decimal('breadth', 8, 2)->nullable()->after('height');
            $table->decimal('square_feet', 8, 2)->nullable()->after('breadth');
            $table->decimal('price_per_square_feet', 8, 2)->nullable()->after('square_feet');
            $table->decimal('total_price', 10, 2)->nullable()->after('price_per_square_feet');
        });
    }

    public function down()
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->dropColumn(['height', 'breadth', 'square_feet', 'price_per_square_feet', 'total_price']);
        });
    }
}