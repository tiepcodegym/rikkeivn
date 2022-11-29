<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTestExamDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('test_exam_details')) {
            return;
        }
        Schema::create('test_exam_details', function (Blueprint $table) {
            $table->unsignedInteger('exam_id');
            $table->unsignedInteger('question_id');
            $table->string('answers_order', 20)->nullable();
            $table->integer('point');
            
            $table->primary(['exam_id', 'question_id']);
            $table->index('question_id');
            $table->foreign('exam_id')
                ->references('id')
                ->on('test_exams');
            $table->foreign('question_id')
                ->references('id')
                ->on('test_questions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('test_exam_details');
    }
}
