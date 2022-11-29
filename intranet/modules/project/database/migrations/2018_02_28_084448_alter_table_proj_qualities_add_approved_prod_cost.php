<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableProjQualitiesAddApprovedProdCost extends Migration
{
    protected $tbl = 'proj_qualities';
    protected $tbl2 = 'proj_point_baselines';
    protected $tbl3 = 'proj_point';
    protected $col = 'cost_approved_production';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->tbl)
                && !Schema::hasColumn($this->tbl, $this->col)) {
            Schema::table($this->tbl, function (Blueprint $table) {
                $table->float($this->col)->nullable()->after('plan_effort');
            });
        }
        if (Schema::hasTable($this->tbl2)) {
            Schema::table($this->tbl2, function (Blueprint $table) {
                if (!Schema::hasColumn($this->tbl2, $this->col)) {
                    $table->float($this->col)->nullable()->after('cost_actual_effort');
                }
                if (!Schema::hasColumn($this->tbl2, $this->col . '_note')) {
                    $table->text($this->col . '_note')->nullable()->after('cost_actual_effort_note');
                }
            });
        }
        if (Schema::hasTable($this->tbl3)) {
            Schema::table($this->tbl3, function (Blueprint $table) {
                if (!Schema::hasColumn($this->tbl3, $this->col . '_note')) {
                    $table->text($this->col . '_note')->nullable()->after('cost_billable_note');
                }
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
        //
    }
}
