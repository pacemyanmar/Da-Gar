<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddObserver2Columns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sample_datas', function (Blueprint $table) {
            $table->string('ps_number', 20)->index()->nullable()->after('sample');
            $table->string('code', 20)->index()->nullable()->after('ps_number');
            $table->string('code2', 20)->index()->nullable();
            $table->string('name2', 50)->index()->nullable();
            $table->string('gender2', 20)->index()->nullable();
            $table->string('nrc_id2', 30)->index()->nullable();
            $table->datetime('dob2')->index()->nullable();
            $table->string('father2', 50)->index()->nullable();
            $table->string('mother2', 50)->index()->nullable();
            $table->string('ethnicity2', 20)->index()->nullable();
            $table->string('language2', 20)->index()->nullable();
            $table->string('current_org2', 50)->index()->nullable();
            $table->string('mobile2', 50)->index()->nullable();
            $table->string('line_phone2', 20)->index()->nullable();
            $table->string('education2', 30)->index()->nullable();
            $table->string('email2', 30)->index()->nullable();
            $table->text('address2')->nullable();
            $table->text('name2_trans')->nullable();
            $table->text('gender2_trans')->nullable();
            $table->text('father2_trans')->nullable();
            $table->text('mother2_trans')->nullable();
            $table->text('ethnicity2_trans')->nullable();
            $table->text('address2_trans')->nullable();
            $table->text('nrc_id2_trans')->nullable();
            $table->text('education2_trans')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sample_datas', function (Blueprint $table) {
            $table->dropColumn(['ps_number', 'code', 'code2', 'name2', 'father2', 'mother2', 'gender2', 'nrc_id2', 'ethnicity2', 'language2', 'current_org2', 'mobile2', 'line_phone2', 'education2', 'email2', 'address2', 'name2_trans', 'gender2_trans', 'father2_trans', 'mother2_trans', 'ethnicity2_trans', 'address2_trans', 'nrc_id2_trans', 'education2_trans']);
        });
    }
}
