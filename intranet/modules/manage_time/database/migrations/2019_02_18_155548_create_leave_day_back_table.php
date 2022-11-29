<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaveDayBackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('leave_day_back')) {
            return;
        }
        
        Schema::create('leave_day_back', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('leave_day_id');
            $table->float('day_last_year')->default(0);
            $table->float('day_last_transfer')->default(0);
            $table->float('day_current_year')->default(0);
            $table->float('day_seniority')->default(0);
            $table->float('day_ot')->default(0);
            $table->float('day_used')->default(0); 
            $table->text('note')->nullable();
            $table->timestamps();
            $table->foreign('leave_day_id')->references('id')->on('leave_days')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('leave_day_back');
    }
}
