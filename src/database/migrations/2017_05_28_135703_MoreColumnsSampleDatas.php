<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MoreColumnsSampleDatas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sample_datas', function (Blueprint $table) {
            $table->string('sms_primary')->nullable();
            $table->string('sms_backup')->nullable();
            $table->string('call_primary')->nullable();
            $table->string('call_backup')->nullable();
            $table->string('hotline1')->nullable();
            $table->string('hotline2')->nullable();
            $table->string('sms_time')->nullable();
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
            $table->dropColumn('sms_primary');
            $table->dropColumn('sms_backup');
            $table->dropColumn('call_primary');
            $table->dropColumn('call_backup');
            $table->dropColumn('hotline1');
            $table->dropColumn('hotline2');
            $table->dropColumn('sms_time');
        });
    }
}
