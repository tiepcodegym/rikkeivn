<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaveDayTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('leave_day_teams')) {
            return;
        }

        Schema::create('leave_day_teams', function(Blueprint $table) {
            $table->unsignedInteger('register_id');
            $table->unsignedInteger('team_id');

            $table->primary(['register_id', 'team_id']);

            $table->foreign('register_id')->references('id')->on('leave_day_registers');
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
        Schema::drop('leave_day_teams');
    }
}
