<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRequestAssetTeams extends Migration
{
    private $table = 'request_asset_teams';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->table)) {
            return;
        }
        Schema::create($this->table, function (Blueprint $table) {
            $table->unsignedInteger('request_id');
            $table->unsignedInteger('team_id');

            $table->primary(['request_id', 'team_id']);

            $table->foreign('request_id')->references('id')->on('request_assets');
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
        Schema::drop($this->table);
    }
}
