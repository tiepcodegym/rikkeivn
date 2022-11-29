<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\Project\Model\ProjectPoint;

class AlterTableProjPointBaselineV10 extends Migration {

    protected $tbl = 'proj_point_baselines';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        $tbl = $this->tbl;
        if (!Schema::hasTable($tbl)) {
            return;
        }
        $columns = ProjectPoint::getAttrNote();
        Schema::table($tbl, function (Blueprint $table) use ($columns, $tbl) {
            foreach ($columns as $col) {
                if (!Schema::hasColumn($tbl, $col)) {
                    $table->text($col)->nullable();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        $tbl = $this->tbl;
        if (!Schema::hasTable($tbl)) {
            return;
        }
        $columns =  ProjectPoint::getAttrNote();
        Schema::table($this->tbl, function (Blueprint $table) use ($columns, $tbl) {
            foreach ($columns as $col) {
                if (Schema::hasColumn($tbl, $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

}
