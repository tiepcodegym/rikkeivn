<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableProjRewardEmployees extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('proj_reward_employees')) {
            return;
        }
        Schema::create('proj_reward_employees', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('task_id');
            $table->unsignedInteger('employee_id');
            $table->smallInteger('type')->nullable();
            $table->text('effort_resource')->nullable();
            $table->double('reward_default', 15, 2)->nullable();
            $table->double('reward_submit', 15, 2)->nullable();
            $table->double('reward_confirm', 15, 2)->nullable();
            $table->double('reward_approve', 15, 2)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            
            $table->index('task_id');
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
        Schema::drop('proj_reward_employees');
    }
}
