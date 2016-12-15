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
            $table->text('index_columns')->nullable();
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
