<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateQuestionsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('qnum', 50);
            $table->text('question');
            $table->text('raw_ans');
            $table->text('render');
            $table->integer('section')->unsigned();
            $table->string('layout');
            $table->integer('sort')->unsigned();
            $table->integer('project_id')->unsigned()->foreign('projects','id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('questions');
    }
}
