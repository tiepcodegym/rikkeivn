<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableTestResults extends Migration
{
    protected $tbl = 'ntest_results';
    protected $tblDetail = 'ntest_result_detail';
    
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
            if (!Schema::hasColumn($this->tbl, 'random_labels')) {
                $table->text('random_labels')->nullable();
            }
        });
        
        if (!Schema::hasTable($this->tblDetail)) {
            return;
        }
        Schema::table($this->tblDetail, function (Blueprint $table) {
            if (Schema::hasColumn($this->tblDetail, 'employee_id')) {
                $table->dropForeign('ntest_test_results_employee_id_foreign');
                $table->dropColumn('employee_id');
            }
            if (Schema::hasColumn($this->tblDetail, 'employee_name')) {
                $table->dropColumn('employee_name');
            }
            if (Schema::hasColumn($this->tblDetail, 'employee_email')) {
                $table->dropColumn('employee_email');
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
