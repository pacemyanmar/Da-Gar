<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSamplesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('samples', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sample_data_id')->index();
            $table->string('sample_data_type')->index();
            $table->unsignedInteger('form_id')->index();
            $table->unsignedInteger('project_id')->index();
            $table->unsignedInteger('user_id')->index()->nullable();
            $table->unsignedInteger('update_user_id')->index()->nullable();
            $table->unsignedInteger('qc_user_id')->index()->nullable();
            $table->text('extras')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('samples');
    }
}
