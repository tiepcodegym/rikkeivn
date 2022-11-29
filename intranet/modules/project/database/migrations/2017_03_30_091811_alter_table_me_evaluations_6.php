<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableMeEvaluations6 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tbl = 'me_evaluations';
        if (!Schema::hasTable($tbl)) {
            return;
        }
        if (Schema::hasColumn($tbl, 'proj_point')) {
            return;
        }
        Schema::table($tbl, function (Blueprint $table) {
           $table->float('proj_point')->default(0)->after('eval_time'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tbl = 'me_evaluations';
        if (!Schema::hasTable($tbl)) {
            return;
        }
        if (!Schema::hasColumn($tbl, 'proj_point')) {
            return;
        }
        Schema::table($tbl, function (Blueprint $table) {
           $table->dropColumn('proj_point');
        });
    }
}
