<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblNtestResultDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('ntest_test_results')) {
            Schema::table('ntest_test_results', function ($table) {
                if (!Schema::hasColumn('ntest_test_results', 'test_result_id')) {
                    $table->unsignedInteger('test_result_id')->after('id');
                }
            });
            
            if (!Schema::hasTable('ntest_result_detail')) {
                Schema::rename('ntest_test_results', 'ntest_result_detail');
            }
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
