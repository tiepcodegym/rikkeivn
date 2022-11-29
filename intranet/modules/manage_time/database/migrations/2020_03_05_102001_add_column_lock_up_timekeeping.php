<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnLockUpTimekeeping extends Migration
{
    private $tbl = 'manage_time_timekeeping_tables';
    private $col1 = 'lock_up';
    private $col2 = 'lock_up_time';

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
            $table->tinyInteger($this->col1)->nullable()->default(1)->comment('1: mở, 2:khóa');
            $table->datetime($this->col2)->nullable();
        });
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
