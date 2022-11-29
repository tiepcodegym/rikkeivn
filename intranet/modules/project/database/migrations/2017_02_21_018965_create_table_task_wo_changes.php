<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTaskWoChanges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('task_wo_changes')) {
            return;
        }
        Schema::create('task_wo_changes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('task_id');
            $table->text('content');
            $table->integer('created_by')->nullable();
            $table->dateTime('created_at')->nullable();
            
            $table->index('task_id');
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
        Schema::drop('task_wo_changes');
    }
}
