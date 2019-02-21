<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NullableQuestionLayout extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//        Schema::table('questions', function (Blueprint $table) {
//            $table->string('layout')->nullable()->change();
//        });
        // workaround because changing columns in table with enum column somewhere not support by DBAL
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE questions MODIFY COLUMN layout varchar(20) NULL;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
//        Schema::table('questions', function (Blueprint $table) {
//            $table->string('layout')->change();
//        });
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE questions MODIFY COLUMN layout varchar(20) NOT NULL;');
    }
}
