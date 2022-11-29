<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRicodeTestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ricode_test', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('level_easy')->nullable();
            $table->integer('level_medium')->nullable();
            $table->integer('level_hard')->nullable();
            $table->integer('duration')->nullable();
            $table->integer('exam_id')->nullable();
            $table->string('url', 255)->nullable();
            $table->string('url_view_source', 255)->nullable();
            $table->integer('total_correct_answers')->nullable();
            $table->string('title', 255)->nullable();
            $table->dateTime('start_time')->nullable();
            $table->integer('time_remaining')->nullable();
            $table->integer('penalty_point')->nullable();
            $table->unsignedInteger('candidate_id');
            $table->foreign('candidate_id')->references('id')->on('candidates')->onDelete('cascade');
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
        Schema::drop('ricode_test');
    }
}