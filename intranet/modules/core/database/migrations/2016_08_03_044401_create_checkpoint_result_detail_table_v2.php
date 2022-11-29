<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCheckpointResultDetailTableV2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('checkpoint_result_detail')) {
            return;
        }
        Schema::create('checkpoint_result_detail', function (Blueprint $table) {
            $table->unsignedInteger('result_id');
            $table->unsignedInteger('question_id');
            $table->integer('point');
            $table->integer('leader_point');
            $table->string('comment',1000);
            $table->string('leader_comment',1000);
            $table->primary(['result_id', 'question_id'],'pk_cp_detail');
            $table->index('question_id');
            $table->foreign('result_id','fr_cp_result')
                ->references('id')
                ->on('checkpoint_result');
            $table->foreign('question_id','fr_cp_question')
                ->references('id')
                ->on('checkpoint_question');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('checkpoint_result_detail');
    }
}
