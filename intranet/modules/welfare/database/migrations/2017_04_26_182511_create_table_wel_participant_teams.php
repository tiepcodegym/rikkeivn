<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableWelParticipantTeams extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('wel_participant_teams')) {
            return;
        }
        Schema::create('wel_participant_teams', function (Blueprint $table) {
            $table->integer('wel_id')->unsigned();
            $table->integer('team_id')->unsigned();

            $table->foreign('wel_id')->references('id')->on('welfares');
            $table->foreign('team_id')->references('id')->on('teams');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wel_participant_teams');
    }
}
