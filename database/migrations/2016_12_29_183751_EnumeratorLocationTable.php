<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EnumeratorLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enumerator_location', function (Blueprint $table) {
            $table->unsignedInteger('enumerator_id')->index();
            $table->unsignedInteger('location_id')->index();
            $table->string('location_type')->index();
            $table->index(['enumerator_id', 'location_id'], 'enumerator_location');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('enumerator_location', function (Blueprint $table) {
            $table->dropIndex('enumerator_location');
        });
        Schema::drop('enumerator_location');
    }
}
