<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMonthRewardColumnToProjRewardMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('proj_reward_metas', function (Blueprint $table) {
           $table->dateTime('month_reward')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('proj_reward_metas', function (Blueprint $table) {
           $table->dropColumn('month_reward'); 
        });
    }
}
