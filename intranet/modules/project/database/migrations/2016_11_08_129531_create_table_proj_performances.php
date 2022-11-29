<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableProjPerformances extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('proj_performances')) {
            return;
        }
        Schema::create('proj_performances', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id');
            $table->dateTime('end_at');
            $table->smallInteger('status');
            $table->unsignedInteger('parent_id')->nullable();
            $table->unsignedInteger('task_id')->nullable();
            $table->dateTime('created_at');
            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('proj_performances');
            $table->foreign('project_id')
                  ->references('id')
                  ->on('projs');
            $table->foreign('task_id')
                  ->references('id')
                  ->on('tasks');
            
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('team_projs');
    }
}
