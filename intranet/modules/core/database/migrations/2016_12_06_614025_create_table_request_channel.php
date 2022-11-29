<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRequestChannel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('request_channel')) {
            return;
        }
        Schema::create('request_channel', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('request_id');
            $table->unsignedInteger('channel_id');
            $table->text('url');
            
            $table->foreign('request_id')->references('id')->on('requests');
            $table->foreign('channel_id')->references('id')->on('recruit_channel');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('request_channel');
    }
}
