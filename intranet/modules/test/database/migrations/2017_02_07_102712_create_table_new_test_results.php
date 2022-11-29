<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableNewTestResults extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('ntest_test_results')) {
            Schema::create('ntest_test_results', function (Blueprint $table) {
               $table->bigIncrements('id');
               $table->unsignedInteger('employee_id')->nullable();
               $table->string('employee_name');
               $table->string('employee_email');
               $table->unsignedInteger('test_id');
               $table->unsignedInteger('question_id');
               $table->unsignedBigInteger('parent_id')->nullable();
               $table->unsignedInteger('answer_id')->nullable();
               $table->string('answer_content')->nullable();
               $table->boolean('is_correct');
               $table->timestamps();
               $table->foreign('employee_id')->references('id')->on('employees');
               $table->foreign('test_id')->references('id')->on('ntest_tests')->onDelete('cascade');
               $table->foreign('question_id')->references('id')->on('ntest_questions')->onDelete('cascade');
               $table->foreign('parent_id')->references('id')->on('ntest_test_results')->onDelete('cascade');
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
        Schema::dropIfExists('ntest_test_results');
    }
}
