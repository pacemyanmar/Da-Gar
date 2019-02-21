<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLocationMetasTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('location_metas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('label')->nullable();
            $table->string('field_name');
            $table->string('field_type'); // primary, code, text or textarea
            $table->string('sample_type')->default('location'); // location or people
            $table->unsignedInteger('sort')->nullable();
            $table->string('status')->default('new'); // new or created
            $table->boolean('show_index')->default(1);
            $table->boolean('export')->default(1);
            $table->unsignedInteger('project_id');
            $table->string('filter_type')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('location_metas');
    }
}
