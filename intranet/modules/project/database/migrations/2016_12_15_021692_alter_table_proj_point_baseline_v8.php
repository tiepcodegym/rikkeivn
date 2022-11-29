<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableProjPointBaselineV8 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('proj_point_baselines')) {
            if (!Schema::hasColumn('proj_point_baselines', 'raise')) {
                Schema::table('proj_point_baselines', function (Blueprint $table) {
                    $table->tinyInteger('raise')->default(2);
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
