<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableNewTestQuestionAnswer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('ntest_question_answer')) {
            Schema::create('ntest_question_answer', function (Blueprint $table) {
               $table->unsignedInteger('question_id');
               $table->unsignedInteger('answer_id');
               $table->boolean('is_correct');
               $table->primary(['question_id', 'answer_id']);
               $table->foreign('question_id')->references('id')->on('ntest_questions')->onDelete('cascade');
               $table->foreign('answer_id')->references('id')->on('ntest_answers')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ntest_question_answer');
    }
}
