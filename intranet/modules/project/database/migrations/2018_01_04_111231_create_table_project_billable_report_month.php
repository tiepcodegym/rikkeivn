<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableProjectBillableReportMonth extends Migration
{
    protected $tbl = 'proj_billable_report_time';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->tbl)) {
            return;
        }
        Schema::create($this->tbl, function (Blueprint $table) {
            $table->unsignedBigInteger('report_id');
            $table->date('time');
            $table->float('billable')->nullable();
            $table->primary(['report_id', 'time']);
            $table->foreign('report_id')
                    ->references('id')
                    ->on('proj_billable_report')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tbl);
    }
}
