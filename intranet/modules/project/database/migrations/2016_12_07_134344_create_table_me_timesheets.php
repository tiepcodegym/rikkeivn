<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMeTimesheets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('me_timesheets')) {
            Schema::create('me_timesheets', function (Blueprint $table) {
               $table->increments('id');
               $table->integer('employee_code');
               $table->date('date');
               $table->time('late_time');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('me_timesheets');
    }
}
