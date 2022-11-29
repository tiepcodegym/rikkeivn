<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCandidateProgramming extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('candidate_programming')) {
            return;
        }
        Schema::create('candidate_programming', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('candidate_id');
            $table->unsignedInteger('programming_id');
            $table->foreign('candidate_id')->references('id')->on('candidates');
            $table->foreign('programming_id')->references('id')->on('programming_languages');
            $table->unique(['candidate_id', 'programming_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('candidate_programming');
    }
}
