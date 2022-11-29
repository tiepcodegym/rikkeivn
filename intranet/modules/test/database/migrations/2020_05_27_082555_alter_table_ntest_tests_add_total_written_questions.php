<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableNtestTestsAddTotalWrittenQuestions extends Migration
{
    protected $tbl = 'ntest_tests';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn($this->tbl, 'total_written_question')) {
            Schema::table($this->tbl, function (Blueprint $table) {
                $table->integer('total_written_question')->nullable();
            });
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
