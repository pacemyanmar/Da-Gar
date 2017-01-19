<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTranslationColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sample_datas', function (Blueprint $table) {
            $table->text('name_trans')->nullable();
            $table->text('gender_trans')->nullable();
            $table->text('father_trans')->nullable();
            $table->text('mother_trans')->nullable();
            $table->text('ethnicity_trans')->nullable();
            $table->text('village_trans')->nullable();
            $table->text('village_tract_trans')->nullable();
            $table->text('township_trans')->nullable();
            $table->text('district_trans')->nullable();
            $table->text('state_trans')->nullable();
        });
        Schema::table('questions', function (Blueprint $table) {
            $table->text('qnum_trans')->nullable();
            $table->text('question_trans')->nullable();
        });
        Schema::table('survey_inputs', function (Blueprint $table) {
            $table->text('label_trans')->nullable();
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->text('project_trans')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
