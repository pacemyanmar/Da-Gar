<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProjectsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->increments('id');
            $table->string('project');
            $table->string('dbname');
            $table->string('dblink')->nullable(); // voter | location | enumerator | none
            $table->string('type')->nullable(); // db2sample | sample2db | none
            $table->text('sections');
            $table->text('samples');
            $table->unsignedTinyInteger('copies')->default(1);
            $table->text('index_columns')->nullable(); // array of columns to show in index tables and reporting and for export
            $table->enum('status', ['new', 'modified', 'published'])->default('new');
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
        Schema::drop('projects');
    }
}
