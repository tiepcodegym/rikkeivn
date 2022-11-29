<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaskTeams extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_teams', function (Blueprint $table) {
            $table->unsignedInteger('task_id');
            $table->unsignedInteger('team_id');
            
            $table->primary(['task_id', 'team_id']);
            $table->index('team_id');
            $table->foreign('task_id')
                  ->references('id')
                  ->on('tasks');
            $table->foreign('team_id')
                  ->references('id')
                  ->on('teams');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_teams');
    }
}
