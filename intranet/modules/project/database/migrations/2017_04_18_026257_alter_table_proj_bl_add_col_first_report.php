<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableProjBlAddColFirstReport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tbl = 'proj_point_baselines';
        if (!Schema::hasTable($tbl) || Schema::hasColumn($tbl, 'first_report')) {
            return;
        }
        Schema::table($tbl, function (Blueprint $table) {
           $table->dateTime('first_report')->nullable();
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
