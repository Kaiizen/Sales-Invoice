<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusToCustomOrders extends Migration
{
    public function up()
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('custom_orders', 'status')) {
                $table->enum('status', ['pending', 'in_production', 'ready', 'delivered'])
                      ->default('pending')
                      ->after('customer_id');
            }
        });
    }

    public function down()
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}