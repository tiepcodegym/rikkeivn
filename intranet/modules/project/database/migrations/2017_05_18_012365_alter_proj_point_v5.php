<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterProjPointV5 extends Migration
{
    private $table = 'proj_point';
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }
        if (Schema::hasColumn($this->table, 'qua_defect_reward_errors')) {
            return true;
        }
        Schema::table($this->table, function (Blueprint $table) {
            $table->integer('qua_defect_reward_errors')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
