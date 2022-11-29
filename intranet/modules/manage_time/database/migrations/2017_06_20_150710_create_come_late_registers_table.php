<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComeLateRegistersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('come_late_registers')) {
            return;
        }

        Schema::create('come_late_registers', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('approver');
            $table->date('date_start');
            $table->date('date_end');
            $table->integer('late_start_shift')->nullable()->default(0);
            $table->integer('early_mid_shift')->nullable()->default(0);
            $table->integer('late_mid_shift')->nullable()->default(0);
            $table->integer('early_end_shift')->nullable()->default(0);
            $table->text('reason')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->foreign('employee_id')->references('id')->on('employees');
            $table->foreign('approver')->references('id')->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('come_late_registers');
    }
}
