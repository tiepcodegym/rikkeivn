<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableProjPointBaselineV4 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('proj_point_baselines')) {
            if (!Schema::hasColumn('proj_point_baselines', 'cost_plan_effort_total_point')) {
                Schema::table('proj_point_baselines', function (Blueprint $table) {
                    $table->float('cost_plan_effort_total_point')->nullable();
                });
            }
        }
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
