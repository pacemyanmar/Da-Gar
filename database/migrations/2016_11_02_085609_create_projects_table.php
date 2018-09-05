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
            $table->string('unique_code')->unique();
            $table->string('dbname');
            $table->string('type')->default('fixed'); // fixed | dynamic
            $table->unsignedTinyInteger('copies')->default(1);
            $table->boolean('training')->nullable();
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
