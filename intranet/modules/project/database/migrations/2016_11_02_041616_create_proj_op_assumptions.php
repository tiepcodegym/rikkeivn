<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjOpAssumptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proj_op_assumptions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id');
            $table->text('content');
            $table->text('note')->nullable();
            $table->smallInteger('status');
            $table->unsignedInteger('parent_id')->nullable();
            $table->unsignedInteger('task_id')->nullable();
            $table->integer('created_by')->nullable();
            $table->index('parent_id');
            $table->index('project_id');
            $table->index('task_id');
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('proj_op_assumptions');
            $table->foreign('project_id')
                  ->references('id')
                  ->on('projs');
            $table->foreign('task_id')
                  ->references('id')
                  ->on('tasks');
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
        Schema::drop('proj_op_assumptions');
    }
}
