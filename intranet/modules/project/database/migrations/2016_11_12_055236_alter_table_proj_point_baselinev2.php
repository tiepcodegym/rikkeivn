<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableProjPointBaselinev2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('proj_point_baselines') || 
            Schema::hasColumn('proj_point_baselines', 'project_evaluation')
        ){
            return;
        }
        Schema::table('proj_point_baselines', function (Blueprint $table) {
            $table->smallInteger('project_evaluation')->default(0);
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
