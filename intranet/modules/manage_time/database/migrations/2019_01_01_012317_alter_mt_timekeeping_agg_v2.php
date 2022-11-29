<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterMtTimekeepingAggV2 extends Migration
{
    protected $tbl = 'manage_time_timekeeping_aggregates';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl)) {
            return true;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            if (!Schema::hasColumn($this->tbl, 'number_com_off')) {
                $table->double('number_com_off', 8, 2)->nullable()
                    ->comment('number days compensation in month with official');
            }
            if (!Schema::hasColumn($this->tbl, 'number_com_tri')) {
                $table->double('number_com_tri', 8, 2)->nullable()
                    ->comment('number days compensation in month with trial');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {}
}
