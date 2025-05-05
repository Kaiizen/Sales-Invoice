<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQrCodeToProductsTable extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('qr_code')->nullable()->after('barcode');
            $table->boolean('auto_reorder')->default(false)->after('is_active');
            $table->decimal('cost_price', 10, 2)->nullable();
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['qr_code', 'auto_reorder', 'cost_price']);
        });
    }
}