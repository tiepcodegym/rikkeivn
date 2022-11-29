<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableNtestAssignee extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('ntest_assignee')) {
            Schema::create('ntest_assignee', function(Blueprint $table) {
               $table->bigIncrements('id');
               $table->unsignedInteger('test_id');
               $table->unsignedInteger('team_id')->nullable();
               $table->unsignedInteger('employee_id')->nullable();
               $table->dateTime('time_from');
               $table->dateTime('time_to');
               $table->foreign('test_id')->references('id')->on('ntest_tests')->onDelete('cascade');
               $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
               $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
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
        Schema::dropIfExists('ntest_assignee');
    }
}
