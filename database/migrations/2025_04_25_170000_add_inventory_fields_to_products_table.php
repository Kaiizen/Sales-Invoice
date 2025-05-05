<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInventoryFieldsToProductsTable extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('current_stock')->default(0)->after('tax_id');
            $table->integer('minimum_stock')->default(5)->after('current_stock');
            $table->string('barcode')->nullable()->after('minimum_stock');
            $table->string('location')->nullable()->after('barcode');
            $table->boolean('is_active')->default(true)->after('location');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['current_stock', 'minimum_stock', 'barcode', 'location', 'is_active']);
        });
    }
}