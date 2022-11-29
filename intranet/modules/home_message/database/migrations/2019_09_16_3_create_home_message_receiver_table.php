<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHomeMessageReceiverTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('home_message_receivers')) {
            return;
        }

        Schema::create('home_message_receivers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('home_message_id');
            $table->unsignedInteger('employee_id')->nullable();
            $table->unsignedInteger('team_id')->nullable();
            $table->datetime('send_at')->nullable();;
            $table->foreign('employee_id')->references('id')->on('employees');
            $table->foreign('home_message_id')->references('id')->on('home_messages');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('home_message_receivers');
    }
}
