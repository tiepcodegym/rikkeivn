<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHomeMessageDayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('home_message_day')) {
            return;
        }
        Schema::create('home_message_day', function (Blueprint $table) {
            $table->unsignedInteger('message_id');
            $table->unsignedInteger('day_id');
            $table->foreign('day_id')->references('id')->on('week_days')->onDelete('cascade');
            $table->foreign('message_id')->references('id')->on('home_messages')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('home_message_day');
    }
}
