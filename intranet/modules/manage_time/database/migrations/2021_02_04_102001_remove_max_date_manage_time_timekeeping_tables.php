<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\Resource\View\getOptions;

class removeMaxDateManageTimeTimekeepingTables extends Migration
{
    private $tbl = 'manage_time_timekeeping_tables';
    private $col1 = 'date_max_import';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { 
        if (!Schema::hasTable($this->tbl) || !Schema::hasColumn($this->tbl, $this->col1)) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->dropColumn($this->col1);
        });
    }
}
