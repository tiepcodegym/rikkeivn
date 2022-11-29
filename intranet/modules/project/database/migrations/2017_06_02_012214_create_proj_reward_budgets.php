<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjRewardBudgets extends Migration
{
    private $table = 'proj_reward_budgets';
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
            $table->unsignedInteger('project_id');
            $table->integer('level');
            $table->double('reward', 15, 2);
            
            $table->primary(['project_id', 'level']);
            $table->index('level');
            $table->foreign('project_id')
                  ->references('id')
                  ->on('projs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->table);
    }
}
