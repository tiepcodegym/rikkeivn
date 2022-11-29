<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTestResultDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('test_result_details')) {
            return;
        }
        Schema::create('test_result_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('result_id');
            $table->unsignedInteger('question_id');
            $table->smallInteger('answer');
            $table->boolean('correct_flg')->default(0);
            $table->smallInteger('point')->default(0);
            
            $table->index('question_id');
            $table->index('result_id');
            $table->foreign('result_id')
                ->references('id')
                ->on('test_results');
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
        Schema::drop('test_result_details');
    }
}
