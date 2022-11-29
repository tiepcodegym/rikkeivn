<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableNtestResultsAddIndexResult extends Migration
{
    protected $tbl = 'ntest_results';

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
        try {
            Schema::table($this->tbl, function (Blueprint $table) {
                //avoid insert duplicate same time
                $table->unique(['employee_email', 'test_id', 'tester_type', 'candidate_id', 'created_at'], 'index_result_unique');
            });
        } catch (Exception $ex) {
            \Log::info($this->tbl . ' already had index');
        }
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
