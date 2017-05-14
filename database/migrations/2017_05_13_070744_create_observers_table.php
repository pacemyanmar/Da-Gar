<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateObserversTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('observers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('given_name')->nullable();
            $table->string('family_name')->nullable();
            $table->string('full_name');
            $table->string('observer_field')->nullable();
            $table->string('code');
            $table->unsignedInteger('sample_id');

            $table->string('email1')->nullable();
            $table->string('email2')->nullable();
            $table->string('national_id')->nullable();
            $table->string('phone_1')->nullable();
            $table->string('phone_2')->nullable();
            $table->text('address')->nullable();
            $table->string('language')->nullable();
            $table->string('ethnicity')->nullable();
            $table->string('occupation')->nullable();
            $table->string('gender')->nullable();
            $table->date('dob')->nullable();
            $table->string('education')->nullable();

            $table->string('mobile_provider')->nullable();
            $table->string('sms_primary')->nullable();
            $table->string('sms_backup')->nullable();
            $table->string('call_primary')->nullable();
            $table->string('call_backup')->nullable();
            $table->string('hotline1')->nullable();
            $table->string('hotline2')->nullable();

            $table->string('form_type')->nullable(); // SBO or PVT or Incident

            $table->string('full_name_trans')->index()->nullable();
            $table->string('phone_1_trans')->index()->nullable();
            $table->string('phone_2_trans')->index()->nullable();
            $table->string('language_trans')->index()->nullable();
            $table->string('ethnicity_trans')->index()->nullable();
            $table->string('occupation_trans')->index()->nullable();


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
        Schema::drop('observers');
    }
}
