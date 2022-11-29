<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTableNtestWrittenQuestionAnswers extends Migration
{
    protected $tbl = 'ntest_written_question_answers';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->tbl)) {
            return;
        }
        Schema::create($this->tbl, function (Blueprint $table) {
            $table->increments('id');
            $table->string('employee_email')->nullable();
            $table->unsignedInteger('written_id')->nullable();
            $table->text('answer')->nullable();
            $table->unsignedInteger('result_id')->nullable();
            $table->foreign('result_id')->references('id')->on('ntest_results');
            $table->foreign('written_id')->references('id')->on('ntest_written_questions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
