<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCandidateTeam extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('candidate_team')) {
            return;
        }
        Schema::create('candidate_team', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('candidate_id');
            $table->unsignedInteger('team_id');
            $table->unique(['candidate_id', 'team_id']);
            $table->foreign('candidate_id')
                ->references('id')->on('candidates');
            $table->foreign('team_id')
                ->references('id')->on('teams');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('candidate_team');
    }
}
