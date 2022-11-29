<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateManageTimeTimekeepingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('manage_time_timekeepings')) {
            return;
        }
        
        Schema::create('manage_time_timekeepings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id');
            $table->date('timekeeping_date');
            $table->string('start_time_morning_shift')->nullable();
            $table->string('end_time_morning_shift')->nullable();
            $table->string('start_time_afternoon_shift')->nullable();
            $table->string('end_time_afternoon_shift')->nullable();
            $table->unsignedInteger('late_start_shift')->nullable()->default(0);
            $table->unsignedInteger('early_mid_shift')->nullable()->default(0);
            $table->unsignedInteger('late_mid_shift')->nullable()->default(0);
            $table->unsignedInteger('early_end_shift')->nullable()->default(0);
            $table->tinyInteger('register_business_trip')->nullable()->default(0);
            $table->double('register_business_trip_number', 8, 2)->nullable()->default(0);
            $table->tinyInteger('register_leave')->nullable()->default(0);
            $table->double('register_leave_number', 8, 2)->nullable()->default(0);
            $table->tinyInteger('register_supplement')->nullable()->default(0);
            $table->double('register_supplement_number', 8, 2)->nullable()->default(0);
            $table->tinyInteger('register_ot')->nullable()->default(0);
            $table->double('register_ot_number', 8, 2)->nullable()->default(0);
            $table->tinyInteger('timekeeping')->nullable()->default(0);
            $table->double('timekeeping_number', 8, 2)->nullable()->default(0);
            $table->double('leave_day_added', 8, 2)->nullable()->default(0);
            
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
        Schema::drop('manage_time_timekeepings');
    }
}
