<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSmsLogsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->string('id')->index();
            $table->string('service_id')->index();
            $table->string('from_number')->index();
            $table->string('to_number')->index();
            $table->string('name')->index();
            $table->string('content')->index();
            $table->text('error_message');
            $table->text('search_result');
            $table->text('phone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sms_logs');
    }
}
