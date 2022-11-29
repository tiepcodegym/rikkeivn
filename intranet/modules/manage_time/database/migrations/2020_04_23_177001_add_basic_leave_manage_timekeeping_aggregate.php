<?php

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rikkei\Resource\View\getOptions;

class AddBasicLeaveManageTimekeepingAggregate extends Migration
{
    private $tbl = 'manage_time_timekeeping_aggregates';
    private $col1 = 'total_official_leave_basic_salary';
    private $col2 = 'total_trial_leave_basic_salary';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { 
        if (!Schema::hasTable($this->tbl)
            || Schema::hasColumn($this->tbl, $this->col1)
            || Schema::hasColumn($this->tbl, $this->col2)) {
            return;
        }
        Schema::table($this->tbl, function(Blueprint $table) {
            $table->double($this->col1, 8, 2)->default(0);
            $table->double($this->col2, 8, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable($this->tbl) 
            || !Schema::hasColumn($this->tbl, $this->col1)
            || !Schema::hasColumn($this->tbl, $this->col2)) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->dropColumn($this->col1);
            $table->dropColumn($this->col2);
        });
    }
}
