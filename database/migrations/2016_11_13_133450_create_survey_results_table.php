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
            /**
             * Composite unique key qnum, samplable_id, samplable_type, survey_input_id, project_id
             */
            $table->increments('id');
            $table->text('value');
            $table->string('qnum')->index();
            $table->integer('sort')->index(); //get from question table sort column
            $table->integer('samplable_id')->unsigned(); //unique id for each sample type in a project (eg. if type is voter, this is id from voter list table)
            $table->string('samplable_type'); // get from project or manual input
            $table->text('samplable_data');// store all related sample data
            $table->string('survey_input_id')->index();
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
