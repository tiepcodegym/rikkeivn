<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableEmployeeContractHistoryAddDatesColumns extends Migration
{
    protected $tbl = 'employee_contract_histories';
    protected $cols = ['leave_date', 'join_date', 'official_date'];

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
            if (!Schema::hasColumn($this->tbl, 'team_name')) {
                $table->string('team_name')->nullable()->after('end_date');
            }
            foreach ($this->cols as $col) {
                if (!Schema::hasColumn($this->tbl, $col)) {
                    $table->date($col)->nullable()->after('end_date');
                }
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
