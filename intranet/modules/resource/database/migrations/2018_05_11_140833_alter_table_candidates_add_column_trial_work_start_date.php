<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableCandidatesAddColumnTrialWorkStartDate extends Migration
{
    protected $tbl = 'candidates';
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl) || Schema::hasColumn($this->tbl, 'trial_work_start_date')) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->datetime('trial_work_start_date')->nullable()->after('start_working_date');
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
