<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ModifySizeColumnInCustomOrders extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE custom_orders MODIFY COLUMN size VARCHAR(191) NULL AFTER flag_type');
    }

    public function down()
    {
        DB::statement('ALTER TABLE custom_orders MODIFY COLUMN size VARCHAR(191) NOT NULL AFTER flag_type');
    }
}