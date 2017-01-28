<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAreaWardColumnsSampleDatas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sample_datas', function (Blueprint $table) {
            $table->integer('area_type')->index()->nullable();
            $table->string('ward')->index()->nullable();
            $table->text('ward_trans')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
