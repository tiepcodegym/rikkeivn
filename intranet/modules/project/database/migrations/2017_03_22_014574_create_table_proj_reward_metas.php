<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableProjRewardMetas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('proj_reward_metas')) {
            return;
        }
        Schema::create('proj_reward_metas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('task_id');
            $table->integer('evaluation')->nullable();
            $table->float('billable')->nullable();
            $table->double('reward_budget', 15, 2)->nullable();
            $table->unsignedInteger('count_defect')->nullable();
            $table->unsignedInteger('count_defect_pqa')->nullable();
            $table->unsignedInteger('count_leakage')->nullable();
            $table->double('unit_reward_leakage_actual', 15, 2)->nullable();
            $table->double('unit_reward_leakage_qa', 15, 2)->nullable();
            $table->double('unit_reward_defect', 15, 2)->nullable();
            $table->double('unit_reward_defect_pqa', 15, 2)->nullable();
            $table->float('factor_reward_pm')->nullable();
            $table->float('factor_reward_dev')->nullable();
            $table->float('factor_reward_brse')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            
            $table->index('task_id');
            $table->foreign('task_id')
                ->references('id')
                ->on('tasks');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('proj_reward_metas');
    }
}
