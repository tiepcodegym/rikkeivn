<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMusicOrderVoteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('music_order_vote')) {
            return false;
        }
        Schema::create('music_order_vote', function (Blueprint $table) {
            $table->integer('music_order_id')->unsigned();
            $table->foreign('music_order_id')->references('id')->on('music_orders')->onDelete('cascade');
            $table->integer('employee_id')->unsigned();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');;
            $table->primary(['music_order_id', 'employee_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('music_order_vote');
    }
}
