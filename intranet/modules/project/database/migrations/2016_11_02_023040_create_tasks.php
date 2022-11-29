<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id');
            $table->smallInteger('type')->nullable();
            $table->string('title', 255);
            $table->smallInteger('status');
            $table->smallInteger('priority')->default(1);
            $table->dateTime('duedate')->nullable();
            $table->dateTime('actual_date')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->text('content');
            $table->unsignedInteger('created_by')->nullable();
            
            $table->index('project_id');
            $table->foreign('project_id')
                  ->references('id')
                  ->on('projs');
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
        Schema::drop('tasks');
    }
}
