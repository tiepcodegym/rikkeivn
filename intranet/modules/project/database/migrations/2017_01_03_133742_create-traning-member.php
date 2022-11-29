<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTraningMember extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proj_training_member', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('training_id');
            $table->index('employee_id');
            $table->index('training_id');
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees');
            $table->foreign('training_id')
                ->references('id')
                ->on('proj_op_trainings');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('proj_traning_member');
    }
}
