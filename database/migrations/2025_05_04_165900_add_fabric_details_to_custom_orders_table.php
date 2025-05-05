<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFabricDetailsToCustomOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->string('fabric_composition')->nullable()->after('fabric_type');
            $table->string('fabric_weight')->nullable()->after('fabric_composition');
            $table->string('fabric_origin')->nullable()->after('fabric_weight');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->dropColumn(['fabric_composition', 'fabric_weight', 'fabric_origin']);
        });
    }
}