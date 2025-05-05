<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSyncLogsTable extends Migration
{
    public function up()
    {
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('device_id')->nullable();
            $table->enum('sync_type', ['download', 'upload']);
            $table->integer('data_size')->default(0);
            $table->string('ip_address')->nullable();
            $table->enum('status', ['success', 'failed', 'partial'])->default('success');
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users');
        });
        
        Schema::create('offline_actions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('device_id');
            $table->string('action_type');
            $table->json('action_data');
            $table->string('client_generated_id')->nullable();
            $table->boolean('is_processed')->default(false);
            $table->text('processing_result')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_actions');
        Schema::dropIfExists('sync_logs');
    }
}