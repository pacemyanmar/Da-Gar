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
            $table->increments('id');
            $table->string('service_id')->index()->nullable();
            $table->string('form_code')->index();
            $table->string('from_number')->index();
            $table->string('from_number_e164')->index()->nullable();
            $table->string('to_number')->index();
            $table->string('event')->index();
            $table->string('message_type')->nullable();
            $table->string('content')->index();
            $table->string('status_url')->index()->nullable();
            $table->string('status_secret')->unique()->nullable();
            $table->text('status_message');
            $table->string('status');
            $table->text('remark');
            $table->unsignedSmallInteger('section')->nullable()->index();
            $table->unsignedInteger('result_id')->nullable()->index();
            $table->unsignedInteger('project_id')->nullable()->index();
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
