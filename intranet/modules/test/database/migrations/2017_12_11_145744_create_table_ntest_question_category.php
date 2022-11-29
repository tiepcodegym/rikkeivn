<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableNtestQuestionCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('ntest_question_category')) {
            return;
        }
        
        Schema::create('ntest_question_category', function (Blueprint $table) {
            $table->unsignedInteger('question_id');
            $table->unsignedBigInteger('cat_id');
            
            $table->primary(['question_id', 'cat_id']);
            $table->foreign('question_id')->references('id')->on('ntest_questions')
                    ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('cat_id')->references('id')->on('ntest_categories')
                    ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ntest_question_category');
    }
}
