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
            $table->string('inputid')->index();
            $table->string('sort')->index(); //get from question table sort column
            $table->integer('samplable_id')->unsigned(); //unique id for each sample type in a project (eg. if type is voter, this is id from voter list table)
            $table->string('samplable_type'); // get from project or manual input
            $table->text('samplable_data');// store all related sample data
            $table->string('survey_input_id')->index();
            $table->integer('project_id')->unsigned();
            //$table->foreign('survey_input_id')->references('id')->on('survey_inputs');
            $table->foreign('project_id')->references('id')->on('projects');
            $table->index(['project_id', 'samplable_id', 'samplable_type', 'survey_input_id'], 'survey_results_p_si_st_sii_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('survey_results', function ($table) {
            //$table->dropForeign('survey_results_survey_input_id_foreign'); // Drops foreign key for inputs
            $table->dropForeign('survey_results_project_id_foreign'); // Drops foreign key for project
            $table->dropIndex(['p_si_st_sii']); // Drops index 'p_si_st_sii
        });
        Schema::drop('survey_results');
    }
}
