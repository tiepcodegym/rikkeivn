<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCheckpointQuestionTableV3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checkpoint_question', function (Blueprint $table) {
            $table->increments('id');
            $table->string('content');
            $table->unsignedInteger('category_id');
            $table->integer('sort_order');
            $table->integer('weight');
            $table->string('rank1_text');
            $table->string('rank2_text');
            $table->string('rank3_text');
            $table->string('rank4_text');
            $table->timestamps();
            $table->index('category_id');
            $table->foreign('category_id')
                ->references('id')
                ->on('checkpoint_category');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('checkpoint_question');
    }
}
