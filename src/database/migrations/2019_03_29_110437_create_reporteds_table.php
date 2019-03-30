<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reported', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('channel');
            $table->string('inputid');
            $table->string('sid');
            $table->string('scode');
            $table->string('followup');
            $table->bigInteger('project_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reported');
    }
}
