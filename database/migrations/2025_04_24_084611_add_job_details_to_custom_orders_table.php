<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJobDetailsToCustomOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->string('job_type')->nullable()->after('customer_id');
            $table->string('fabric_type')->nullable()->after('job_type');
            $table->string('contact_through')->nullable()->after('fabric_type');
            $table->string('received_by')->nullable()->after('contact_through');
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
            $table->dropColumn(['job_type', 'fabric_type', 'contact_through', 'received_by']);
        });
    }
}
