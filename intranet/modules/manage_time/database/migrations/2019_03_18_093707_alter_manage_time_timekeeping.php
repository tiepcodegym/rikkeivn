<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterManageTimeTimekeeping extends Migration
{
    protected $tbl = 'manage_time_timekeepings';
    private $col1 = 'has_leave_day';
    private $col2 = 'has_leave_day_no_salary';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl)
            || !Schema::hasColumn($this->tbl, $this->col1)
            || !Schema::hasColumn($this->tbl, $this->col2)) {
            return;
        }
        Schema::table($this->tbl, function(Blueprint $table) {
            $table->float($this->col1, 8, 2)->default(0)->change();
            $table->float($this->col2, 8, 2)->default(0)->change();
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
