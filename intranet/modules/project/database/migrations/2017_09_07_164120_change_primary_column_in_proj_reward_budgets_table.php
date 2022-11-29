<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangePrimaryColumnInProjRewardBudgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('proj_reward_budgets', function (Blueprint $table) {
            $table->dropForeign('proj_reward_budgets_project_id_foreign');
            $table->dropPrimary();
            $table->foreign('project_id')->references('id')->on('projs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('proj_reward_budgets', function (Blueprint $table) {
           $table->primary(['project_id', 'level']);
        });
    }
}
