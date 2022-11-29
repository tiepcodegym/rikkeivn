<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCssTeamTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('css_team', function (Blueprint $table) {
            $table->unsignedInteger('css_id');
            $table->unsignedInteger('team_id');
            $table->primary(['css_id', 'team_id']);
            $table->index('css_id');
            $table->foreign('css_id')
                ->references('id')
                ->on('css');
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
        Schema::drop('css_team');
    }
}
