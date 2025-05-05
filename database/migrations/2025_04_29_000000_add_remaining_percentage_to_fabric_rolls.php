<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemainingPercentageToFabricRolls extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fabric_rolls', function (Blueprint $table) {
            if (!Schema::hasColumn('fabric_rolls', 'remaining_percentage')) {
                $table->decimal('remaining_percentage', 8, 2)->default(100)->after('remaining_square_feet');
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
        Schema::table('fabric_rolls', function (Blueprint $table) {
            $table->dropColumn('remaining_percentage');
        });
    }
}