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
            $table->string('location_code')->index();
            $table->string('type')->default('enumerator');
            $table->unsignedSmallInteger('dbgroup')->default(1);
            $table->unsignedSmallInteger('sample')->default(1);
            $table->string('ps_code')->nullable(); // polling station
            $table->string('area_type')->default('rural');
            $table->string('level6')->index()->nullable();
            $table->string('level5')->index()->nullable();
            $table->string('level4')->index()->nullable();
            $table->string('level3')->index()->nullable();
            $table->string('level2')->index()->nullable();
            $table->string('level1')->index()->nullable();

            $table->string('level6_trans')->index()->nullable();
            $table->string('level5_trans')->index()->nullable();
            $table->string('level4_trans')->index()->nullable();
            $table->string('level3_trans')->index()->nullable();
            $table->string('level2_trans')->index()->nullable();
            $table->string('level1_trans')->index()->nullable();

            $table->string('observer_field')->index()->nullable();
            $table->string('supervisor_field')->index()->nullable();
            $table->string('supervisor_name')->index()->nullable();
            $table->string('supervisor_name_trans')->index()->nullable();
            $table->string('supervisor_gender')->index()->nullable();
            $table->string('supervisor_mobile')->index()->nullable();
            $table->string('supervisor_mail1')->index()->nullable();
            $table->string('supervisor_mail2')->index()->nullable();
            $table->string('supervisor_address')->index()->nullable();
            $table->date('supervisor_dob')->index()->nullable();

            $table->text('parties')->nullable();
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
