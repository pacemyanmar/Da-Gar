<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSurveyResultsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('survey_results', function (Blueprint $table) {
            $table->increments('id');
            $table->text('value');
            $table->string('qnum')->index();
            $table->integer('sort')->index();
            $table->integer('samplable_id')->unsigned();
            $table->string('samplable_type');
            $table->integer('survey_input_id')->unsigned();
            $table->integer('project_id')->unsigned();
            $table->foreign('survey_input_id')->references('id')->on('survey_inputs');
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
        Schema::drop('survey_results');
    }
}
