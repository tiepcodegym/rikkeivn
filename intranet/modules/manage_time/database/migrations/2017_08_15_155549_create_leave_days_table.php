<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaveDaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('leave_days')) {
            return;
        }
        
        Schema::create('leave_days', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id');
            $table->float('day_last_year')->default(0);
            $table->float('day_last_transfer')->default(0);
            $table->float('day_current_year')->default(0);
            $table->float('day_seniority')->default(0);
            $table->float('day_ot')->default(0);
            $table->float('day_used')->default(0); 
            $table->text('note')->nullable();
            
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

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
        Schema::drop('leave_days');
    }
}
