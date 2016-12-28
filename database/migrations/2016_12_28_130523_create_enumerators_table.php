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
            $table->string('idcode')->index()->nullable();
            $table->string('type')->default('enumerator');
            $table->string('name')->index();
            $table->string('gender')->index()->nullable();
            $table->string('nrc_id')->index()->nullable();
            $table->datetime('dob')->index()->nullable();
            $table->text('address')->nullable();
            $table->integer('village')->index()->nullable();
            $table->integer('village_tract')->index()->nullable();
            $table->integer('township')->index()->nullable();
            $table->integer('district')->index()->nullable();
            $table->integer('state')->index()->nullable();
            $table->timestamps();
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
