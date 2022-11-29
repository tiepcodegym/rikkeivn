<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTabelProjectBillableReportV2 extends Migration
{
    protected $tbl = 'proj_billable_report';
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
            if (!Schema::hasColumn($this->tbl, 'is_running')) {
                $table->boolean('is_running')->after('saleman');
            }
            $table->index(['project_code', 'member', 'role', 'start_at', 'end_at'], 'unique_billable');
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
