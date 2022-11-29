<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableProjPointBaselineV3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('proj_point_baselines')) {
            if (!Schema::hasColumn('proj_point_baselines', 'cost_busy_rate')) {
                Schema::table('proj_point_baselines', function (Blueprint $table) {
                    $table->float('cost_busy_rate')->nullable();
                });
            }
            if (!Schema::hasColumn('proj_point_baselines', 'cost_busy_rate_point')) {
                Schema::table('proj_point_baselines', function (Blueprint $table) {
                    $table->float('cost_busy_rate_point')->nullable();
                });
            }
            if (!Schema::hasColumn('proj_point_baselines', 'cost_effort_efficiency2_point')) {
                Schema::table('proj_point_baselines', function (Blueprint $table) {
                    $table->float('cost_effort_efficiency2_point')->nullable();
                });
            }
        }
        
        if (Schema::hasTable('proj_point')) {
            if (!Schema::hasColumn('proj_point', 'cost_busy_rate_note')) {
                Schema::table('proj_point', function (Blueprint $table) {
                    $table->text('cost_busy_rate_note')->nullable();
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
