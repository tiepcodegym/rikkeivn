<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCandidateRequest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('candidate_request')) {
            return;
        }
        Schema::create('candidate_request', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('candidate_id');
            $table->unsignedInteger('request_id');
            $table->unique(['candidate_id', 'request_id']);
            $table->foreign('candidate_id')
                ->references('id')->on('candidates');
            $table->foreign('request_id')
                ->references('id')->on('requests');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('candidate_request');
    }
}
