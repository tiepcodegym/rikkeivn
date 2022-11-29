<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBusinessTripTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('business_trip_teams')) {
            return;
        }

        Schema::create('business_trip_teams', function(Blueprint $table) {
            $table->unsignedInteger('register_id');
            $table->unsignedInteger('team_id');

            $table->primary(['register_id', 'team_id']);

            $table->foreign('register_id')->references('id')->on('business_trip_registers');
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
        Schema::drop('business_trip_teams');
    }
}
