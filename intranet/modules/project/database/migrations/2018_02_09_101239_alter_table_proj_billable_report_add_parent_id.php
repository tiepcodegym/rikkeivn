<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableProjBillableReportAddParentId extends Migration
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
            if (!Schema::hasColumn($this->tbl, 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('effort');
                $table->foreign('parent_id')
                        ->references('id')
                        ->on($this->tbl)
                        ->onDelete('set null')
                        ->onUpdate('cascade');
            }
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
