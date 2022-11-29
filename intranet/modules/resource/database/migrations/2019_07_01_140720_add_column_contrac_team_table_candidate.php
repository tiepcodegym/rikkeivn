<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnContracTeamTableCandidate extends Migration
{

    protected $tbl = 'candidates';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->unsignedInteger('contract_team_id')->nullable()->comment('ID team quan ly ho so');
            $table->foreign('contract_team_id')->references('id')->on('teams');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->dropColumn('contract_team_id');
        });
    }

}
