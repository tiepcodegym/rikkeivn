<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRequestTeam extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('request_team')) {
            return;
        }
        Schema::create('request_team', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('request_id');
            $table->unsignedInteger('team_id');
            $table->tinyInteger('position_apply');
            $table->smallInteger('number_resource');
            
            $table->foreign('request_id')->references('id')->on('requests');
            $table->foreign('team_id')->references('id')->on('teams');
            $table->unique(['request_id', 'team_id', 'position_apply']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('request_team');
    }
}
