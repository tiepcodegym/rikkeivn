<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableProjPointBaseline extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('proj_point_baselines') && 
            Schema::hasColumn('proj_point_baselines', 'point_total')
        ) {
            Schema::table('proj_point_baselines', function (Blueprint $table) {
                $table->float('point_total')->change();
            });
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
