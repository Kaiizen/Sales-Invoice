<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProductIdToFlagDetailsTable extends Migration
{
    public function up()
    {
        Schema::table('flag_details', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->after('custom_order_id');
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    public function down()
    {
        Schema::table('flag_details', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
        });
    }
}