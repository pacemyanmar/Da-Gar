<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSampleFileAndAutoUpdateToProjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('sample_file')->nullable();
            $table->enum('file_type',['local', 'remote'])->default('local'); // local or remote
            $table->string('auto_update')->default(0);
            $table->string('report_by')->default('location'); // location or people
            $table->string('store_by')->default('location'); // location or people
            $table->string('idcolumn')->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('sample_file');
            $table->dropColumn('file_type');
            $table->dropColumn('auto_update');
            $table->dropColumn('report_by');
            $table->dropColumn('store_by');
            $table->dropColumn('idcolumn');
        });
    }
}
