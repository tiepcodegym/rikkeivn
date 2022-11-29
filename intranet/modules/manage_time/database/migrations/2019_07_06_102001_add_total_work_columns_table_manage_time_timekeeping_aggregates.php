<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\Resource\View\getOptions;

class AddTotalWorkColumnsTableManageTimeTimekeepingAggregates extends Migration
{
    private $tbl = 'manage_time_timekeeping_aggregates';
    private $col1 = 'total_working_officail';
    private $col2 = 'total_working_trial';

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
            $table->float($this->col1);
            $table->float($this->col2);
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
