<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApproveRewardColumnToProjRewardMetasTable extends Migration
{
    protected $tableName = 'proj_reward_metas';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tableName)) {
            return;
        }
        if (Schema::hasColumn($this->tableName, 'approve_date')) {
            return;
        }
        Schema::table($this->tableName, function (Blueprint $table) {
           $table->dateTime('approve_date')->nullable()->comment('approved reward date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable($this->tableName)) {
            return;
        }
        if (!Schema::hasColumn($this->tableName, 'approve_date')) {
            return;
        }
        Schema::table($this->tableName, function (Blueprint $table) {
           $table->dropColumn('approve_date'); 
        });
    }
}
