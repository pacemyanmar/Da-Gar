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
            $table->string('dblink')->default('enumerator'); // voter | location | enumerator
            $table->string('type')->default('db2sample'); // db2sample | sample2db
            $table->integer('dbgroup')->default(1);
            $table->text('parties')->nullable();
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
