<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSampleDatasTable extends Migration
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
            $table->string('name')->index();
            $table->string('type')->index();
            $table->string('unique')->nullable()->index();
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
        Schema::drop('sample_datas');
    }
}
