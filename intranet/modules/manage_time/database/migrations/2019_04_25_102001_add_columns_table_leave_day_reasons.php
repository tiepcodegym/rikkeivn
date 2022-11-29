<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsTableLeaveDayReasons extends Migration
{
    private $tbl = 'leave_day_reasons';
    private $col1 = 'type';
    private $col2 = 'repeated';
    private $col3 = 'unit';
    private $col4 = 'value';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl)) {
            return;
        }
        if (!Schema::hasColumn($this->tbl, $this->col1)) {
            Schema::table($this->tbl, function(Blueprint $table) {
                $table->tinyInteger($this->col1)->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn($this->tbl, $this->col2)) {
            Schema::table($this->tbl, function(Blueprint $table) {
                $table->tinyInteger($this->col2)->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn($this->tbl, $this->col3)) {
            Schema::table($this->tbl, function(Blueprint $table) {
                $table->string($this->col3)->nullable()->default('');
            });
        }
        if (!Schema::hasColumn($this->tbl, $this->col4)) {
            Schema::table($this->tbl, function(Blueprint $table) {
                $table->tinyInteger($this->col4)->nullable()->default(0);
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

    }
}
