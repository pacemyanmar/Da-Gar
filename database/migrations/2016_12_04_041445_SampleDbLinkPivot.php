<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SampleDbLinkPivot extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sample_dblink', function (Blueprint $table) {
            $table->integer('dblink_id')->unsigned()->index();
            $table->integer('project_id')->unsigned()->index();
            $table->string('dblink_type')->index(); // table name to link
            $table->string('sample')->index();
            $table->string('code')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sample_dblink');
    }
}
