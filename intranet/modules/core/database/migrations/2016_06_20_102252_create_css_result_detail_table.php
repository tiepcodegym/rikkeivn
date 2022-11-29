<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCssResultDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('css_result_detail', function (Blueprint $table) {
            $table->unsignedInteger('css_result_id');
            $table->unsignedInteger('question_id');
            $table->integer('point');
            $table->string('comment');
            $table->primary(['css_result_id', 'question_id']);
            $table->index('question_id');
            $table->foreign('css_result_id')
                ->references('id')
                ->on('css_result');
            $table->foreign('question_id')
                ->references('id')
                ->on('css_question');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('css_result_detail');
    }
}
