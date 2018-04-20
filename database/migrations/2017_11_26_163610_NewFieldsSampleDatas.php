<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewFieldsSampleDatas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sample_datas', function (Blueprint $table) {
            $table->string('ward')->nullable();
            $table->string('sample_area_name')->nullable();
            $table->unsignedInteger('sample_area_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sample_datas', function (Blueprint $table) {
            $table->dropColumn('ward');
            $table->dropColumn('sample_area_name');
            $table->dropColumn('sample_area_type');
        });
    }
}
