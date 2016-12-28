<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEnumeratorsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enumerators', function (Blueprint $table) {
            $table->increments('id');
            $table->string('idcode')->index();
            $table->string('name')->index();
            $table->string('gender')->index();
            $table->string('nrc_id')->index();
            $table->timestamp('dob')->index();
            $table->text('address');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('enumerators');
    }
}
