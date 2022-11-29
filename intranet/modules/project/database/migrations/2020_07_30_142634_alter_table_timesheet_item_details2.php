<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlterTableTimesheetItemDetails2 extends Migration
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

        Schema::table($this->table, function (Blueprint $table) {
            $table->dropForeign('timesheet_item_details_timesheet_item_id_foreign');

            $table->index('timesheet_item_id');
            $table->foreign('timesheet_item_id')->references('id')->on('timesheet_items')->onDelete('cascade');
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
