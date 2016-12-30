<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class SampleDatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sample_datas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('idcode')->index();
            $table->string('type')->default('enumerator');
            $table->unsignedSmallInteger('dbgroup')->default(1)->nullable();
            $table->unsignedSmallInteger('sample')->default(1)->nullable();
            $table->string('name')->index()->nullable();
            $table->string('gender')->index()->nullable();
            $table->string('nrc_id')->index()->nullable();
            $table->datetime('dob')->index()->nullable();
            $table->string('father')->index()->nullable();
            $table->string('mother')->index()->nullable();
            $table->string('ethnicity')->index()->nullable();
            $table->string('current_org')->index()->nullable();
            $table->string('mobile')->index()->nullable();
            $table->string('line_phone')->index()->nullable();
            $table->string('education')->index()->nullable();
            $table->string('email')->index()->nullable();
            $table->text('address')->nullable();
            $table->string('village')->index()->nullable(); // village or ward
            $table->string('village_tract')->index()->nullable(); // village tract or town
            $table->string('township')->index()->nullable();
            $table->string('district')->index()->nullable();
            $table->string('state')->index()->nullable();
            $table->unsignedInteger('parent_id')->index()->nullable();

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
        Schema::drop('sample_datas');
    }
}
