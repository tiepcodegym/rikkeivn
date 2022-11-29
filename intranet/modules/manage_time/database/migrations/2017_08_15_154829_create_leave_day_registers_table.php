<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaveDayRegistersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('leave_day_registers')) {
            return;
        }

        Schema::create('leave_day_registers', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('creator_id');
            $table->unsignedInteger('approver_id');
            $table->unsignedInteger('substitute_id')->nullable();
            $table->unsignedInteger('reason_id');
            $table->datetime('date_start');
            $table->datetime('date_end');
            $table->double('number_days_off', 8, 2);
            $table->text('note');
            $table->tinyInteger('status')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->foreign('creator_id')->references('id')->on('employees');
            $table->foreign('approver_id')->references('id')->on('employees');
            $table->foreign('substitute_id')->references('id')->on('employees');
            $table->foreign('reason_id')->references('id')->on('leave_day_reasons');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('leave_day_registers');
    }
}
