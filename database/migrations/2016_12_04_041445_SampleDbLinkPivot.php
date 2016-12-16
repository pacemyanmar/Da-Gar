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
            $table->integer('dblink_id')->unsigned()->index(); // primary key from linked database
            $table->integer('project_id')->unsigned()->index(); // project primary id
            $table->string('dblink_type')->index(); // database table name to link
            $table->string('sample')->index(); // sample group
            $table->string('code')->nullable()->index(); // unique code for project
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
