<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableNewTestResults extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('ntest_test_results')) {
            if (Schema::hasColumn('ntest_test_results', 'question_id')) {
                Schema::table('ntest_test_results', function ($table) {
                   $table->unsignedInteger('question_id')->nullable()->change(); 
                });
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
