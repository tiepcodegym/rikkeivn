<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterProjectMetasV3 extends Migration
{
    private $table = 'project_metas';
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $column = 'is_show_reward_budget';
        if (!Schema::hasTable($this->table) ||
            !Schema::hasColumn($this->table, $column)) {
            return;
        }
        Schema::table($this->table, function (Blueprint $table) use ($column) {
            $table->smallInteger($column)->nullable()->change();
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
