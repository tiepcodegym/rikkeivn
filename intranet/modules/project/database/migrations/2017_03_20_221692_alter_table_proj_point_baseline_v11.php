<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableProjPointBaselineV11 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('proj_point_baselines')) {
            return true;
        }
        if (!Schema::hasColumn('proj_point_baselines', 'cost_productivity')) {
            return true;
        }
        Schema::table('proj_point_baselines', function (Blueprint $table) {
            $table->text('cost_productivity')->nullable()->change();
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
