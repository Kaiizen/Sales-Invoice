<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameStitchingColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->boolean('stitching')->default(false)->after('quantity');
        });

        // Copy data from old column to new column
        \DB::statement('UPDATE custom_orders SET stitching = (stitching_option = "Yes")');

        Schema::table('custom_orders', function (Blueprint $table) {
            $table->dropColumn('stitching_option');
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
            $table->string('stitching_option')->nullable()->after('quantity');
        });

        // Copy data back to old column
        \DB::statement('UPDATE custom_orders SET stitching_option = IF(stitching, "Yes", "No")');

        Schema::table('custom_orders', function (Blueprint $table) {
            $table->dropColumn('stitching');
        });
    }
}