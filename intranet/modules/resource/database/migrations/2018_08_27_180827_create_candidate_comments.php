<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCandidateComments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('candidate_comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('candidate_id');
            $table->text('content');
            $table->smallInteger('type')->nullable();
            $table->unsignedInteger('created_by');
            $table->index('candidate_id');
            $table->foreign('candidate_id')
                  ->references('id')
                  ->on('candidates');
            $table->foreign('created_by')
                  ->references('id')
                  ->on('employees');
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
        Schema::drop('candidate_comments');
    }
}
