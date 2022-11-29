<?php

namespace Rikkei\Api\Helper;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\Model\Languages;
use Rikkei\Resource\Model\Programs;
use Rikkei\Resource\Model\RequestTeam;
use Rikkei\Resource\Model\ResourceRequest;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;

/**
 * Description of Hrm Recruitment
 *
 * @author duydv
 */
class HrmCandidate extends HrmRecruitment
{
    /**
     * Get Common Candidate column to indicate kind of status
     *
     * @return array
     */
    private function _getCommonCandidateStateColumn()
    {
        $candidateTbl = Candidate::getTableName();
        $employeeTbl = Employee::getTableName();

        return [
            "{$candidateTbl}.contact_note",
            "{$candidateTbl}.contact_result",
            "{$candidateTbl}.test_plan as cv_date",
            "{$candidateTbl}.test_note as cv_note",
            "{$candidateTbl}.test_result as cv_result",
            "{$candidateTbl}.interview2_plan",
            "{$candidateTbl}.interview_plan",
            "{$candidateTbl}.interview_note",
            "{$candidateTbl}.interview_result",
            "{$candidateTbl}.offer_date",
            "{$candidateTbl}.offer_note",
            "{$candidateTbl}.offer_result",
            "{$candidateTbl}.working_type",
            "{$employeeTbl}.join_date",
            "{$employeeTbl}.trial_date",
            "{$employeeTbl}.trial_end_date",
            "{$employeeTbl}.offcial_date as official_date",
            "{$employeeTbl}.leave_date"
        ];
    }

    /**
     * @param $type
     * @return string
     */
    private function _getQueryCaseWhenStatus($type)
    {
        $query = $this->_generateQueryStatusCondition($type);

        if ($query) {
            return "WHEN {$query} THEN {$type}";
        }
    }

    /**
     * @return string
     */
    private function _getStatusCaseQuery()
    {
        $query = "CASE ";
        foreach ($this->getStatus() as $type => $name) {
            $query .= $this->_getQueryCaseWhenStatus($type);
            $query .= ' ';
        }

        $query .= "ELSE 0
            END as candidate_status";

        return $query;
    }

    /**
     * Main function to get candidates
     *
     * @param Request $request
     * @return mixed
     */
    public function getCandidates(Request $request)
    {
        $limitQuery = $request->limit ? $request->limit : 15;

        $candidateTbl = Candidate::getTableName();
        $requestTbl = ResourceRequest::getTableName();
        $teamTbl = Team::getTableName();
        $employeeTbl = Employee::getTableName();
        $programmingLanguageTbl = Programs::getTableName();

        $queryCaseStatus = $this->_getStatusCaseQuery();

        $commonCandidateStateColumn = $this->_getCommonCandidateStateColumn();

        $selectFields = [
            "{$candidateTbl}.id",
            "{$candidateTbl}.email",
            "{$candidateTbl}.mobile",
            "{$candidateTbl}.birthday",
            "{$candidateTbl}.gender",
            "{$candidateTbl}.skype",
            "{$candidateTbl}.other_contact",
            "{$candidateTbl}.fullname",
            "{$candidateTbl}.position_apply",
            "{$candidateTbl}.note",
            "{$candidateTbl}.cv",
            DB::raw($queryCaseStatus),
            "{$candidateTbl}.university",
            "{$candidateTbl}.certificate",
            "{$candidateTbl}.old_company",
            "{$candidateTbl}.experience",
        ];

        $selectFields = array_merge($selectFields, $commonCandidateStateColumn);

        $collections = Candidate::select($selectFields)
            ->with([
                'candidateProgramming' => function ($q) use ($programmingLanguageTbl) {
                    $q->select("{$programmingLanguageTbl}.id", "{$programmingLanguageTbl}.name")->withPivot('exp_year');
                },
                'candidateTeam' => function ($q) use ($teamTbl) {
                    $q->select("{$teamTbl}.name", "{$teamTbl}.branch_code");
                },
                'candidateLang' => function ($q) {
                    $langTblName = Languages::getTableName();
                    $q->select("{$langTblName}.name", "{$langTblName}.id");
                },
                'candidateRequest' => function ($q) use ($requestTbl, $programmingLanguageTbl, $teamTbl) {
                    $q->select("{$requestTbl}.title", "{$requestTbl}.id")
                        ->with([
                            'requestProgramming' => function ($q) use ($programmingLanguageTbl) {
                                $q->select("{$programmingLanguageTbl}.id", "{$programmingLanguageTbl}.name");
                            },
                            'requestTeam' => function ($q) use ($teamTbl) {
                                $q->select("{$teamTbl}.name", "{$teamTbl}.branch_code")->withPivot('position_apply', 'number_resource');
                            }
                        ]);
                },
            ])
            ->leftJoin($employeeTbl, "{$employeeTbl}.id", '=', "{$candidateTbl}.employee_id");

        $collections->groupBy("{$candidateTbl}.id");

        return $collections->paginate(intval($limitQuery));
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getCandidateRequests(Request $request)
    {
        $limitQuery = $request->limit ? $request->limit : 15;

        $requestTbl = ResourceRequest::getTableName();
        $programmingLanguageTbl = Programs::getTableName();
        $languageTbl = Languages::getTableName();
        $teamTbl = Team::getTableName();

        $selectedQuery = [
            "{$requestTbl}.id",
            "{$requestTbl}.title",
            "{$requestTbl}.recruiter",
            "{$requestTbl}.deadline",
            "{$requestTbl}.onsite",
            "{$requestTbl}.status",
            "{$requestTbl}.customer",
            "{$requestTbl}.request_date",
            "{$requestTbl}.description",
            "{$requestTbl}.effort",
            "{$requestTbl}.benefits",
            "{$requestTbl}.job_qualifi",
            "{$requestTbl}.interviewer",
        ];

        $collections = ResourceRequest::select($selectedQuery)
            ->with([
                'requestProgramming' => function ($q) use ($programmingLanguageTbl) {
                    $q->select("{$programmingLanguageTbl}.id", "{$programmingLanguageTbl}.name");
                },
                'requestLang' => function ($q) use ($languageTbl) {
                    $q->select("{$languageTbl}.id", "{$languageTbl}.name");
                },
                'requestTeam' => function ($q) use ($teamTbl) {
                    $candidateTbl = Candidate::getTableName();
                    $requestTeam = RequestTeam::getTableName();
                    $statusIn = $this->_generateQueryIn([getOptions::WORKING, getOptions::END]);

                    $q->select("{$teamTbl}.name", "{$teamTbl}.branch_code", "{$requestTeam}.position_apply","{$requestTeam}.number_resource", DB::raw(
                        "(SELECT COUNT(*) FROM {$candidateTbl} where request_id = {$requestTeam}.request_id
                          AND position_apply = {$requestTeam}.position_apply AND team_id = {$requestTeam}.team_id 
                          AND status IN {$statusIn}) as actual_number_resource"
                    ))->withTrashed();
                },
                'requestCandidates' => function ($q) {
                    $statusCaseQuery = $this->_getStatusCaseQuery();
                    $q->select([DB::raw($statusCaseQuery), DB::raw('count(*) as total')])
                        ->groupBy('candidate_request.request_id')
                        ->groupBy('candidate_status');

//                    $commonCandidateStateColumn = $this->_getCommonCandidateStateColumn();
//                    $q->select($commonCandidateStateColumn)->leftJoin($employeeTbl, "{$employeeTbl}.id", '=', "{$candidateTbl}.employee_id");
                }
            ]);

        return $collections->paginate($limitQuery)->makeVisible('interviewer_email');
    }
}
