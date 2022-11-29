<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaveDayRelatersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('leave_day_relaters')) {
            return;
        }

        Schema::create('leave_day_relaters', function(Blueprint $table) {
            $table->unsignedInteger('register_id');
            $table->unsignedInteger('relater_id');

            $table->primary(['register_id', 'relater_id']);

            $table->foreign('register_id')->references('id')->on('leave_day_registers');
            $table->foreign('relater_id')->references('id')->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('leave_day_relaters');
    }
}
