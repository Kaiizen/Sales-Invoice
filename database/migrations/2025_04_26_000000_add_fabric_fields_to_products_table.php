<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFabricFieldsToProductsTable extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_fabric')->default(false)->after('is_active');
            $table->boolean('track_by_roll')->default(false)->after('is_fabric');
            $table->decimal('roll_width', 8, 2)->nullable()->after('track_by_roll');
            $table->decimal('roll_length', 8, 2)->nullable()->after('roll_width');
            $table->decimal('total_square_feet', 10, 2)->nullable()->after('roll_length');
            $table->integer('alert_threshold_percent')->default(20)->after('total_square_feet');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'is_fabric',
                'track_by_roll',
                'roll_width',
                'roll_length',
                'total_square_feet',
                'alert_threshold_percent'
            ]);
        });
    }
}