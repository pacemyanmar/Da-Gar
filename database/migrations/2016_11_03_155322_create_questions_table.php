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
            $table->string('qnum');
            $table->string('question');
            $table->string('css_id');
            $table->text('raw_ans');
            $table->string('layout');
            $table->integer('section')->unsigned();
            $table->integer('sort')->unsigned();
            $table->boolean('double_entry')->default(false); // should do double entry?
            $table->boolean('report')->default(false); //should be in report index or not?
            $table->boolean('optional')->default(false);
            $table->enum('qstatus', ['new', 'modified', 'published'])->default('new');
            $table->integer('project_id')->unsigned();
            $table->foreign('project_id')->references('id')->on('projects');
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
