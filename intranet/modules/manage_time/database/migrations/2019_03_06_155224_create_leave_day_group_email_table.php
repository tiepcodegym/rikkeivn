<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaveDayGroupEmailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('leave_day_group_email')) {
            return;
        }

        Schema::create('leave_day_group_email', function(Blueprint $table) {
            $table->unsignedInteger('register_id');
            $table->string('group_email');

            $table->foreign('register_id')->references('id')->on('leave_day_registers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('leave_day_group_email');
    }
}
