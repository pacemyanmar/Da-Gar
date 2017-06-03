<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ObserverIdsInSampleData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sample_datas', function (Blueprint $table) {
            $table->string('observer1_id')->nullable();
            $table->string('observer2_id')->nullable();
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
            $table->dropColumn('observer1_id');
            $table->dropColumn('observer2_id');
        });
    }
}
