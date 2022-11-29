<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamProjs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('team_projs')) {
            return;
        }
        Schema::create('team_projs', function (Blueprint $table) {
            $table->unsignedInteger('team_id');
            $table->unsignedInteger('project_id');
            $table->dateTime('created_at');
            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            
            $table->primary(['team_id', 'project_id']);
            $table->index('project_id');
            $table->foreign('team_id')
                ->references('id')
                ->on('teams');
            $table->foreign('project_id')
                ->references('id')
                ->on('projs');
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
