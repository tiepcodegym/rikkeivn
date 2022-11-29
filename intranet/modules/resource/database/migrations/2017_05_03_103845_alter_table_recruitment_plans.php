<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterTableRecruitmentPlans extends Migration
{
    protected $tbl = 'recruitment_plans';
    
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
        if (!Schema::hasColumn($this->tbl, 'team_id')) {
            return;
        }
        if (!Schema::hasTable('teams_feature')) {
            return;
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table($this->tbl)->truncate();
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->dropForeign('recruitment_plans_team_id_foreign');
            $table->foreign('team_id')->references('id')->on('teams_feature')->onDelete('cascade');
        });
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
