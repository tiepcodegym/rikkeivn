<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComeLateTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('come_late_teams')) {
            return;
        }

        Schema::create('come_late_teams', function(Blueprint $table) {
            $table->unsignedInteger('come_late_id');
            $table->unsignedInteger('team_id');

            $table->primary(['come_late_id', 'team_id']);

            $table->foreign('come_late_id')->references('id')->on('come_late_registers');
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
        Schema::drop('come_late_teams');
    }
}
