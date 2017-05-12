<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateObeserversTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('obeservers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('code');
            $table->integer('sample_id');
            $table->string('national_id');
            $table->string('phone_1');
            $table->string('phone_2');
            $table->text('address');
            $table->string('language');
            $table->string('ethnicity');
            $table->string('occupation');
            $table->string('gender');
            $table->date('dob');
            $table->string('education');
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
        Schema::drop('obeservers');
    }
}
