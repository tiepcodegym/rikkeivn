<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\Resource\View\getOptions;

class AddLateEarlyShiftColumnsTableManageTimeTimekeepingAggregates extends Migration
{
    private $tbl = 'manage_time_timekeeping_aggregates';
    private $col1 = 'total_late_mid_shift';
    private $col2 = 'total_early_mid_shift';
    private $col3 = 'total_early_end_shift';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl)
                || Schema::hasColumn($this->tbl, $this->col1)
                || Schema::hasColumn($this->tbl, $this->col2)
                || Schema::hasColumn($this->tbl, $this->col3)) {
            return;
        }
        Schema::table($this->tbl, function(Blueprint $table) {
            $table->float($this->col1);
            $table->float($this->col2);
            $table->float($this->col3);
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
                || !Schema::hasColumn($this->tbl, $this->col2)
                || !Schema::hasColumn($this->tbl, $this->col3)) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->dropColumn($this->col1); 
            $table->dropColumn($this->col2);
            $table->dropColumn($this->col3); 
        });
    }
}
