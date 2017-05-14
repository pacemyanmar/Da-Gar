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
        Schema::table('questions', function (Blueprint $table) {
            $table->string('qnum_trans')->index()->nullable();
            $table->string('question_trans')->index()->nullable();
        });
        Schema::table('survey_inputs', function (Blueprint $table) {
            $table->string('label_trans')->index()->nullable();
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->string('project_trans')->index()->nullable();
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
