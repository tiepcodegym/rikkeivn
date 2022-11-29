<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCssQuestionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('css_question', function (Blueprint $table) {
            $table->increments('id');
            $table->string('content');
            $table->unsignedInteger('category_id');
            $table->integer('sort_order');
            $table->boolean('is_overview_question');
            $table->index('category_id');
            $table->foreign('category_id')
                ->references('id')
                ->on('css_category');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('css_question');
    }
}
