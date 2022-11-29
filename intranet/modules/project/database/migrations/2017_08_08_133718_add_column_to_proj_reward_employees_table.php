<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToProjRewardEmployeesTable extends Migration
{
    protected $tableName = 'proj_reward_employees';
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
        if (Schema::hasColumn($this->tableName, 'comment')) {
            return;
        }
        Schema::table($this->tableName, function (Blueprint $table) {
           $table->text('comment')->nullable();
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
        if (!Schema::hasColumn($this->tableName, 'comment')) {
            return;
        }
        Schema::table($this->tableName, function (Blueprint $table) {
           $table->dropColumn('comment'); 
        });
    }
}
