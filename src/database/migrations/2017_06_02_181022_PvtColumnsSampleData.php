<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PvtColumnsSampleData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sample_datas', function (Blueprint $table) {
            $table->unsignedInteger('obs_type')->nullable();
            $table->boolean('sbo')->default(false);
            $table->boolean('pvt1')->default(false);
            $table->boolean('pvt2')->default(false);
            $table->boolean('pvt3')->default(false);
            $table->boolean('pvt4')->default(false);
            $table->unsignedBigInteger('registered_voters')->nullable();
            $table->unsignedInteger('level1_id')->nullable();
            $table->string('incident_center')->nullable();
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
            $table->dropColumn('obs_type');
            $table->dropColumn('sbo');
            $table->dropColumn('pvt1');
            $table->dropColumn('pvt2');
            $table->dropColumn('pvt3');
            $table->dropColumn('pvt4');
            $table->dropColumn('registered_voters');
            $table->dropColumn('level1_id');
            $table->dropColumn('incident_center');
        });
    }
}
