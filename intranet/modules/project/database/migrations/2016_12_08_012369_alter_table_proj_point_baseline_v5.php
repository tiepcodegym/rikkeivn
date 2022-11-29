<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableProjPointBaselineV5 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('proj_point_baselines')) {
            if (!Schema::hasColumn('proj_point_baselines', 'cost_busy_rate_ucl')) {
                Schema::table('proj_point_baselines', function (Blueprint $table) {
                    $table->float('cost_busy_rate_ucl')->nullable();
                });
            }
            if (!Schema::hasColumn('proj_point_baselines', 'cost_busy_rate_target')) {
                Schema::table('proj_point_baselines', function (Blueprint $table) {
                    $table->float('cost_busy_rate_target')->nullable();
                });
            }
            if (!Schema::hasColumn('proj_point_baselines', 'cost_busy_rate_lcl')) {
                Schema::table('proj_point_baselines', function (Blueprint $table) {
                    $table->float('cost_busy_rate_lcl')->nullable();
                });
            }
            if (!Schema::hasColumn('proj_point_baselines', 'cost_effort_efficiency_lcl')) {
                Schema::table('proj_point_baselines', function (Blueprint $table) {
                    $table->float('cost_effort_efficiency_lcl')->nullable();
                });
            }
            if (!Schema::hasColumn('proj_point_baselines', 'cost_effort_efficiency_target')) {
                Schema::table('proj_point_baselines', function (Blueprint $table) {
                    $table->float('cost_effort_efficiency_target')->nullable();
                });
            }
            if (!Schema::hasColumn('proj_point_baselines', 'cost_effort_efficiency_ucl')) {
                Schema::table('proj_point_baselines', function (Blueprint $table) {
                    $table->float('cost_effort_efficiency_ucl')->nullable();
                });
            }
        }
        
        if (Schema::hasTable('proj_point')) {
            if (!Schema::hasColumn('proj_point', 'cost_busy_rate_lcl')) {
                Schema::table('proj_point', function (Blueprint $table) {
                    $table->float('cost_busy_rate_lcl')->nullable();
                });
            }
            if (!Schema::hasColumn('proj_point', 'cost_busy_rate_target')) {
                Schema::table('proj_point', function (Blueprint $table) {
                    $table->float('cost_busy_rate_target')->nullable();
                });
            }
            if (!Schema::hasColumn('proj_point', 'cost_busy_rate_ucl')) {
                Schema::table('proj_point', function (Blueprint $table) {
                    $table->float('cost_busy_rate_ucl')->nullable();
                });
            }
            if (!Schema::hasColumn('proj_point', 'cost_effort_efficiency_lcl')) {
                Schema::table('proj_point', function (Blueprint $table) {
                    $table->float('cost_effort_efficiency_lcl')->nullable();
                });
            }
            if (!Schema::hasColumn('proj_point', 'cost_effort_efficiency_target')) {
                Schema::table('proj_point', function (Blueprint $table) {
                    $table->float('cost_effort_efficiency_target')->nullable();
                });
            }
            if (!Schema::hasColumn('proj_point', 'cost_effort_efficiency_ucl')) {
                Schema::table('proj_point', function (Blueprint $table) {
                    $table->float('cost_effort_efficiency_ucl')->nullable();
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
