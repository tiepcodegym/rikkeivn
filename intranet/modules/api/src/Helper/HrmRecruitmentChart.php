<?php

namespace Rikkei\Api\Helper;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\Model\CandidateRequest;
use Rikkei\Resource\Model\CandidateTeam;
use Rikkei\Resource\Model\RequestTeam;
use Rikkei\Resource\Model\ResourceRequest;
use Rikkei\Resource\View\getOptions;

/**
 * Description of Hrm Recruitment
 *
 * @author duydv
 */
class HrmRecruitmentChart extends HrmRecruitment
{
    /**
     * @param $request
     * @return array
     */
    private function _initFilter($request)
    {
        $firstMonthOfYear = Carbon::createFromFormat('Y', $request->year)->firstOfYear();
        $lastMonthOfYear = Carbon::createFromFormat('Y', $request->year)->lastOfYear();

        return [
            'firstMonthOfYear' => $firstMonthOfYear,
            'lastMonthOfYear' => $lastMonthOfYear,
        ];
    }

    /**
     * @param $monthNumber
     * @param $monthFormatYM
     * @param $teamIds
     * @param $candidateIds
     * @return mixed
     */
    private function _initSingleSqlRecruitmentTargetEachMonth($monthNumber, $monthFormatYM, $teamIds, $candidateIds)
    {
        $requestTbl = ResourceRequest::getTableName();
        $candidateTbl = Candidate::getTableName();
        $requestTeamTbl = RequestTeam::getTableName();

        $additionConditionTotalTarget = '';
        $additionConditionTotalPass = '';
        if ($teamIds) {
            $additionConditionTotalTarget = "AND {$requestTeamTbl}.team_id in {$teamIds}";
            $additionConditionTotalPass = "AND {$candidateTbl}.id in {$candidateIds}";
        }

        $candidateWorkingStatus = getOptions::WORKING;
        //_Lọc ra các ứng viên candidates có cột status = 8 (working status) để tính tổng ứng viên đã gia nhập
        $selectRawTotalPass = "SUM(COALESCE((SELECT 
                    COUNT({$candidateTbl}.id)
                FROM
                    {$candidateTbl}
                WHERE
                    status = {$candidateWorkingStatus} AND request_id = {$requestTbl}.id $additionConditionTotalPass),
            0)) AS totalPass";

        //_Lấy tổng số lượng yêu cầu ở các requests để tính tổng chỉ tiêu
        $selectRawTotalTarget = "SUM(COALESCE((SELECT 
                    SUM({$requestTeamTbl}.number_resource)
                FROM
                    {$requestTeamTbl}
                WHERE
                    {$requestTeamTbl}.request_id = {$requestTbl}.id {$additionConditionTotalTarget}),
            0)) AS totalTarget";

        $collections = DB::table($requestTbl)
            ->select(
                DB::raw("{$monthNumber} as month"),
                DB::raw($selectRawTotalPass),
                DB::raw($selectRawTotalTarget)
            )
            ->where(DB::raw("DATE_FORMAT(request_date, '%Y-%m')"), '<=', $monthFormatYM)
            ->where(DB::raw("DATE_FORMAT(deadline, '%Y-%m')"), '>=', $monthFormatYM)
            ->where('approve', getOptions::APPROVE_ON);

        return $collections;
    }

    /**
     * Chart Report
     *
     * @param $request
     * @return mixed
     */
    public function getReport($request)
    {
        $selectedDateFrom = $request->date_from;
        $selectedDateTo = $request->date_to;
        $selectedBranchCode = $request->branch_code;
        $selectedTeamId = $request->team_id;
        $campaignId = $request->campaign_id;

        $additionConditionForCampaignId = '';

        $candidateTbl = Candidate::getTableName();
        $candidateTeamTbl = CandidateTeam::getTableName();
        $teamTbl = \Rikkei\Team\Model\Team::getTableName();
        $employeeTbl = \Rikkei\Team\Model\Employee::getTableName();
        $requestTbl = ResourceRequest::getTableName();
        $candidateRequestTbl = CandidateRequest::getTableName();

        $conditionPassCv = $this->_generateQueryStatusCondition(self::PASS_CV);
        $conditionPassInterview = $this->_generateQueryStatusCondition(self::PASS_INTERVIEW);
        $conditionPassOffer = $this->_generateQueryStatusCondition(self::PASS_OFFER);
        $conditionProbation = $this->_generateQueryStatusCondition(self::PROBATION_TIME);
        $conditionInternship = $this->_generateQueryStatusCondition(self::INTERNSHIP_TIME);
        $conditionOfficialEmployee = $this->_generateQueryStatusCondition(self::OFFICIAL_EMPLOYEE);

        $selectRawSumTotalPassCV = "COALESCE(SUM(CASE
                            WHEN DATE(test_plan) BETWEEN '{$selectedDateFrom}' AND '{$selectedDateTo}' AND {$conditionPassCv} THEN 1
                            ELSE 0
                        END), 0) AS total_pass_cv";
        $selectRawSumTotalPassInterview = "COALESCE(SUM(CASE
                            WHEN
                                (DATE(interview_plan) BETWEEN '{$selectedDateFrom}' AND '{$selectedDateTo}'
                                    AND {$conditionPassInterview})
                            THEN
                                1
                            ELSE 0
                        END), 0) AS total_pass_interview";
        if ($campaignId) {
            $additionConditionForCampaignId = "AND {$candidateTbl}.request_id = {$campaignId}";
        }
        $selectRawSumTotalPassOffer = "COALESCE(SUM(CASE
                            WHEN
                                (DATE(offer_date) BETWEEN '{$selectedDateFrom}' AND '{$selectedDateTo}'
                                    AND {$conditionPassOffer} {$additionConditionForCampaignId})
                            THEN
                                1
                            ELSE 0
                        END), 0) AS total_pass_offer";
        $selectRawSumTotalProbation = "COALESCE(SUM(CASE
                            WHEN
                                (DATE(employees.join_date) BETWEEN '{$selectedDateFrom}' AND '{$selectedDateTo}'
                                    AND ({$conditionProbation} OR {$conditionInternship})
                                    {$additionConditionForCampaignId} 
                                    AND(DATE(employees.join_date) < DATE(employees.offcial_date) OR DATE(employees.trial_date) < DATE(employees.offcial_date)
                                    OR employees.offcial_date IS NULL))
                            THEN
                                1
                            ELSE 0
                        END), 0) AS total_probation";
        $selectRawSumTotalOfficial = "COALESCE(SUM(CASE
                            WHEN DATE(employees.offcial_date) BETWEEN '{$selectedDateFrom}' AND '{$selectedDateTo}' AND {$conditionOfficialEmployee} {$additionConditionForCampaignId} THEN 1
                            ELSE 0
                        END), 0) AS total_official";

        $collections = DB::table($candidateTbl)
            ->select(
                DB::raw($selectRawSumTotalPassCV),
                DB::raw($selectRawSumTotalPassInterview),
                DB::raw($selectRawSumTotalPassOffer),
                DB::raw($selectRawSumTotalProbation),
                DB::raw($selectRawSumTotalOfficial)
            )
            ->join($candidateTeamTbl, "{$candidateTeamTbl}.candidate_id", '=', "{$candidateTbl}.id")
            ->join($teamTbl, "{$candidateTeamTbl}.team_id", '=', "{$teamTbl}.id")
            ->join($candidateRequestTbl, "{$candidateRequestTbl}.candidate_id", '=', "{$candidateTbl}.id")
            ->join($requestTbl, "{$candidateRequestTbl}.request_id", '=', "{$requestTbl}.id")
            ->leftJoin($employeeTbl, "{$employeeTbl}.id", '=', "{$candidateTbl}.employee_id")
            ->where("{$requestTbl}.approve", getOptions::APPROVE_ON);

        if ($selectedBranchCode) {
            $collections->where('branch_code', $selectedBranchCode);
        }
        if ($selectedTeamId) {
            $collections->where("{$teamTbl}.id", $selectedTeamId);
        }

        //Tính chỉ tiêu tuyển dụng của Campaign
        if ($campaignId) {
            $requestTeamTbl = RequestTeam::getTableName();
            $collections->where("{$requestTbl}.id", $campaignId);
            $collections->join($requestTeamTbl, "{$requestTeamTbl}.request_id", '=', "{$requestTbl}.id")
                ->addSelect(DB::raw("
                        (select SUM(request_team.number_resource) from request_team where 
                        {$requestTeamTbl}.request_id = {$campaignId}) as target"));
        }

        return $collections->get();
    }

    /**
     * Get List Campaign
     *
     * @param $request
     * @return mixed
     */
    public function getCampaign($request)
    {
        $selectedDateFrom = $request->date_from;
        $selectedDateTo = $request->date_to;

        $candidateRequestTbl = 'requests';

        $collections = DB::table($candidateRequestTbl)
            ->select(['id', 'title'])
            ->whereDate('request_date', '<=', $selectedDateTo)
            ->whereDate('deadline', '>=', $selectedDateFrom)
            ->where('approve', getOptions::APPROVE_ON)
            ->get();

        return $collections;
    }

    /**
     * @param $request
     * @return array
     */
    public function getTarget($request)
    {
        $filter = $this->_initFilter($request);

        $firstOfYearClone = clone $filter['firstMonthOfYear'];
        $hrmTotal = (object) array();

        $teamIds = [];
        $candidateIds = [];

        if ($request->branch_code) {
            if ($request->team_id) {
                $teamIds[] = $request->team_id;
            } else {
                $teamTbl = \Rikkei\Team\Model\Team::getTableName();
                $teamIds = DB::table($teamTbl)->select('id');
                $teamIds = $teamIds->where('branch_code', $request->branch_code)->pluck('id');
            }
        }

        //_Nếu có truyền lên Chi nhánh hoặc bộ phận thì phải lọc ra các Candidates tương ứng theo bộ phận
        if (count($teamIds)) {
            $candidateTeamTbl = CandidateTeam::getTableName();
            $candidateIds = DB::table($candidateTeamTbl)->select('candidate_id')->whereIn('team_id', $teamIds)->pluck('candidate_id');
            $teamIds = '(' . implode($teamIds, ',') . ')';
            $candidateIds = '(' . implode($candidateIds, ',') . ')';
        }

        while ($firstOfYearClone->lte($filter['lastMonthOfYear'])) {
            $currentSelectedMonth = clone $firstOfYearClone;

            $monthFormatYM = $currentSelectedMonth->lastOfMonth()->format('Y-m');
            $monthNumber = $currentSelectedMonth->month;

            $sql = $this->_initSingleSqlRecruitmentTargetEachMonth($monthNumber, $monthFormatYM, $teamIds, $candidateIds);

            if ($firstOfYearClone->eq($filter['firstMonthOfYear'])) {
                $hrmTotal = $sql;
            } else {
                $collections = $hrmTotal->union($sql);
            }

            $firstOfYearClone = $firstOfYearClone->addMonth();
        }

        if (isset($collections)) {
            return $collections->get();
        }

        return [];
    }
}
