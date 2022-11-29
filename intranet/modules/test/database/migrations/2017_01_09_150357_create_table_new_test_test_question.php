<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableNewTestTestQuestion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('ntest_test_question')) {
            Schema::create('ntest_test_question', function (Blueprint $table) {
               $table->unsignedInteger('test_id');
               $table->unsignedInteger('question_id');
               $table->tinyInteger('order')->default(0);
               $table->primary(['test_id', 'question_id']);
               $table->foreign('test_id')->references('id')->on('ntest_tests')->onDelete('cascade');
               $table->foreign('question_id')->references('id')->on('ntest_questions')->onDelete('cascade');
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
        Schema::dropIfExists('ntest_test_question');
    }
}
