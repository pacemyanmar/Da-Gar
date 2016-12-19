<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSurveyInputsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('survey_inputs', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('inputid')->index();
            $table->string('type')->index();
            $table->string('name')->index();
            $table->string('label')->index();
            $table->string('value')->index();
            $table->unsignedSmallInteger('sort')->index();
            $table->unsignedTinyInteger('section')->index();
            $table->enum('status', ['new', 'published'])->default('new');
            $table->boolean('index')->default(false);
            $table->boolean('optional')->default(false);
            $table->text('logic')->nullable();
            $table->integer('question_id')->unsigned();
            $table->foreign('question_id')->references('id')->on('questions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('survey_inputs');
    }
}
