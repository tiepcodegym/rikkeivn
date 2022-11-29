<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveColumnIdTableCronjobProjectAllocations extends Migration
{
    protected $tbl = 'cronjob_project_allocations';
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
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->dropColumn('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable($this->tbl)) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->increments('id');
        });
    }
}
