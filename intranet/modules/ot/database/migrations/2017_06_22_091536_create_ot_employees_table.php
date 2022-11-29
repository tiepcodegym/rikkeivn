<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOtEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ot_employees', function (Blueprint $table) {
            $table->integer('ot_register_id')->unsigned();
            $table->integer('employee_id')->unsigned();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->double('time_break', 11,2)->nullable();
            $table->text('note')->nullable();

            $table->foreign('ot_register_id')->references('id')->on('ot_registers');
            $table->foreign('employee_id')->references('id')->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ot_employees');
    }
}
