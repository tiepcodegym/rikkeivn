<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\Model\Programs;
use Rikkei\Resource\Model\CandidateProgramming;
use Rikkei\Resource\Model\CandidatePosition;
use Rikkei\Test\Models\Result;
use Rikkei\Test\Models\Test;
use Rikkei\Test\Models\Type;

class CreateCandidateProgramViewTable extends Migration
{
    protected $tbl = 'view_candidate_report';
    protected $tblWeek = 'candidate_weeks';
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
        $query = 'CREATE ALGORITHM = MERGE VIEW ' . $this->tbl . ' AS (SELECT '
                . 'cdd.id , cdd.fullname, cdd.test_result, cdd.interview_result, cdd.offer_result, cdd.status, '
                . 'DATE_FORMAT(cdd.received_cv_date, "%X-%v") AS week_create, '
                . 'DATE_FORMAT(cdd.test_plan, "%X-%v") AS week_test, '
                . 'DATE_FORMAT(cdd.interview_plan, "%X-%v") AS week_interview, '
                . 'DATE_FORMAT(cdd.offer_date, "%X-%v") AS week_offer, '
                . 'DATE_FORMAT(cdd.start_working_date, "%X-%v") AS week_working, '
                . 'DATE(cdd.start_working_date) AS working_date, '
                . 'IFNULL(pl.id, CONCAT("p_", cddpos.position_apply)) AS prog_id, '
                . 'IFNULL(MAX(trs.total_corrects / trs.total_question * 10), cdd.test_gmat_point) AS gmat_point, '
                . 'cdd.recruiter '
                . 'FROM ' . Candidate::getTableName() . ' AS cdd '
                . 'LEFT JOIN ' . CandidateProgramming::getTableName() . ' AS cddprog ON cdd.id = cddprog.candidate_id '
                . 'LEFT JOIN ' . Programs::getTableName() . ' AS pl ON cddprog.programming_id = pl.id '
                . 'LEFT JOIN ' . CandidatePosition::getTableName() . ' AS cddpos ON cdd.id = cddpos.candidate_id '
                . 'LEFT JOIN ' . Result::getTableName() . ' as trs '
                    . 'INNER JOIN '. Test::getTableName() .' as test ON trs.test_id = test.id '
                    . 'INNER JOIN '. Type::getTableName() .' as ttype ON test.type_id = ttype.id '
                    . 'AND ttype.id = '. Test::getGMATId() . ' '
                . ' ON cdd.email = trs.employee_email '
                . 'WHERE cdd.deleted_at IS NULL '
                . 'GROUP BY cdd.id, prog_id)';
        DB::statement($query);

        //candidate weeks
        if (Schema::hasTable($this->tblWeek)) {
            return;
        }
        Schema::create($this->tblWeek, function (Blueprint $table) {
            $table->string('week', 8)->unique();
            $table->primary('week');
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
