<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableLogUserSessions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('log_user_sessions')) {
            return;
        }
        Schema::create('log_user_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('ip')->nullable();
            $table->string('browser', 255)->nullable();
            $table->text('agent');
            $table->dateTime('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('log_user_sessions');
    }
}
