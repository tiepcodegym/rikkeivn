<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlterTableTimesheetItems extends Migration
{
    private $table = 'timesheet_items';

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

        \DB::statement("ALTER TABLE `{$this->table}` CHANGE COLUMN `day_of_leave` `day_of_leave` FLOAT NULL DEFAULT NULL");
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
