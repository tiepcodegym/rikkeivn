<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlterTableTimesheetItemDetails extends Migration
{
    private $table = 'timesheet_item_details';

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

        \DB::statement("ALTER TABLE `{$this->table}` CHANGE COLUMN `working_hour` `working_hour` FLOAT NULL DEFAULT NULL");
        \DB::statement("ALTER TABLE `{$this->table}` CHANGE COLUMN `ot_hour` `ot_hour`FLOAT NULL DEFAULT NULL");
        \DB::statement("ALTER TABLE `{$this->table}` CHANGE COLUMN `overnight` `overnight`FLOAT NULL DEFAULT NULL");
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
