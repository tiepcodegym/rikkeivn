<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateTableTaskAssigns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('task_assigns')) {
            return;
        }
        Schema::create('task_assigns', function (Blueprint $table) {
            $table->unsignedInteger('task_id');
            $table->unsignedInteger('employee_id');
            $table->dateTime('created_at')->nullable();
            
            $table->primary(['task_id', 'employee_id']);
            $table->foreign('task_id')
                  ->references('id')
                  ->on('tasks');
            $table->foreign('employee_id')
                  ->references('id')
                  ->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('task_assigns');
    }
}
