<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableCandidateWeeksCronData extends Migration
{
    protected $tbl = 'candidate_weeks';
    protected $cols = ['number_cvs', 'tests', 'tests_pass', 'gmats_8', 'interviews', 'interviews_pass', 'offers', 'offers_pass', 'workings'];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('view_candidate_report')) {
            DB::statement('DROP VIEW view_candidate_report');
        }
        if (!Schema::hasTable($this->tbl)) {
            return false;
        }
        foreach ($this->cols as $col) {
            if (Schema::hasColumn($this->tbl, $col)) {
                return false;
            }
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            foreach ($this->cols as $col) {
                $table->text($col)->nullable();
            }
        });
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
