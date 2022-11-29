<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\Test\Models\Test;
use Rikkei\Test\Models\Result;
use Rikkei\Test\View\ViewTest;

class CreateTestsCountViewTable extends Migration
{
    protected $tbl = 'tests_statistics';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->tbl)) {
            DB::statement('DROP VIEW ' . $this->tbl);
        }

        $query = 'CREATE ALGORITHM=MERGE VIEW ' . $this->tbl . ' AS ( '
            . 'SELECT test.id, '
                . 'COUNT('
                    . 'DISTINCT('
                        . 'CASE WHEN result.tester_type = '. ViewTest::TESTER_PRIVATE .' THEN result.employee_email END'
                    . ')'
                . ') AS count_result, '
                . 'COUNT('
                    . 'DISTINCT('
                        . 'CASE WHEN result.tester_type = '. ViewTest::TESTER_PUBLISH .' THEN result.employee_email END'
                    . ')'
                . ') AS count_result_pl, '
                . 'COUNT(DISTINCT(tq.question_id)) AS count_questions, '
                . 'CASE '
                    . 'WHEN (test.limit_question = 0 OR test.limit_question IS NULL) '
                    . 'THEN COUNT(DISTINCT(tq.question_id)) '
                    . 'ELSE test.total_question '
                . 'END AS display_question '
            . 'FROM ' . Test::getTableName() . ' AS test '
            . 'LEFT JOIN ' . Result::getTableName() . ' AS result ON result.test_id = test.id '
            . 'LEFT JOIN ntest_test_question AS tq ON tq.test_id = test.id '
            . 'GROUP BY test.id'
        . ')';
        DB::statement($query);
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
