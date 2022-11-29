<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnTeamOwnerTableProjOpRicks extends Migration
{
    protected $tbl = 'proj_op_ricks';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl)) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->unsignedInteger("team_owner")->nullable()->comment("team owner of risk");
            $table->foreign('team_owner')->references('id')->on('teams');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable($this->tbl)) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->dropForeign('proj_op_ricks_team_owner_foreign');
            $table->dropColumn('team_owner'); 
        });
    }
}
