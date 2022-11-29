<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddColumnSummaryNoteInProjPointTable extends Migration
{
    protected $table = 'proj_point';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }
        Schema::table($this->table, function (Blueprint $table) {
            $table->text('css_summary_note')->nullable();
            $table->text('qua_leakage_summary_note')->nullable();
            $table->text('qua_defect_summary_note')->nullable();
            $table->text('proc_compliance_summary_note')->nullable();
            $table->text('deliver_summary_note')->nullable();
            $table->text('correction_cost_summary_note')->nullable();
            $table->text('effort_efficiency_summary_note')->nullable();
            $table->text('correction_cost_note')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }
        Schema::table($this->table, function (Blueprint $table) {
            $table->dropColumn('css_summary_note');
            $table->dropColumn('qua_leakage_summary_note');
            $table->dropColumn('qua_defect_summary_note');
            $table->dropColumn('proc_compliance_summary_note');
            $table->dropColumn('deliver_summary_note');
            $table->dropColumn('correction_cost_summary_note');
            $table->dropColumn('effort_efficiency_summary_note');
            $table->dropColumn('correction_cost_note');
        });
    }
}
