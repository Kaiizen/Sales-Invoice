<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CleanupInvoiceProductId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Check if column exists before trying to remove it
            if (Schema::hasColumn('invoices', 'product_id')) {
                // Drop foreign key first if it exists
                if (DB::getDriverName() !== 'sqlite') {
                    $table->dropForeign(['product_id']);
                }
                $table->dropColumn('product_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
