<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHomeMessageBannerTeamTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('home_message_banner_team')) {
            return;
        }

        Schema::create('home_message_banner_team', function (Blueprint $table) {
            $table->unsignedInteger('banner_id');
            $table->unsignedInteger('team_id');
            $table->foreign('banner_id')->references('id')->on('home_message_banners')->onDelete('cascade');
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('home_message_banner_team');
    }
}
