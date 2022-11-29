<?php

namespace Rikkei\Resource\Model;

use Rikkei\Resource\View\getOptions;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Team\View\Permission;
use Rikkei\Team\View\CheckpointPermission;
use Illuminate\Support\Facades\DB;
use Rikkei\Resource\Model\CandidateTeam;
use Rikkei\Team\Model\Team;
use Carbon\Carbon;

class TestSchedule extends Candidate
{
    /*
     * define filter val
     */
    const NULL_VAL = 'NULL';
    const YES_VAL = 1;
    const NO_VAL = 2;
    
    /**
     * get data
     * @param type $data
     * @return type
     */
    public static function getGridData($data) {
        $pager = Config::getPagerData();
        $candidateTbl = self::getTableName();
        $currentUser = Permission::getInstance()->getEmployee();
        
        $collection = self::select(
                $candidateTbl.'.id', $candidateTbl.'.fullname', $candidateTbl.'.email', $candidateTbl.'.status', $candidateTbl.'.test_result', 
                $candidateTbl.'.interview_result', $candidateTbl.'.mobile', $candidateTbl.'.position_apply', $candidateTbl.'.test_note',
                DB::raw($candidateTbl.'.test_plan AS test_time')
            );
                        
        //check permisison
        if (Permission::getInstance()->isScopeCompany()) {
            //do nothing
        } else if (Permission::getInstance()->isScopeTeam()) {
            $teamIds = CheckpointPermission::getArrTeamIdByEmployee($currentUser->id);
            $cddTeamTbl = CandidateTeam::getTableName();
            $teamTbl = Team::getTableName();
            $collection->leftJoin($cddTeamTbl.' as ccdTeam', $candidateTbl.'.id', '=', 'ccdTeam.candidate_id')
                    ->leftJoin($teamTbl.' as team', 'ccdTeam.team_id', '=', 'team.id')
                    ->where(function ($query) use ($candidateTbl, $teamIds, $currentUser) {
                            $query->whereIn('ccdTeam.team_id', $teamIds)
                                ->orWhere($candidateTbl.'.created_by', $currentUser->id)
                                ->orWhere($candidateTbl.'.recruiter', $currentUser->email )
                                ->orWhereRaw($candidateTbl.'.interviewer IS NOT NULL AND FIND_IN_SET('. $currentUser->id .',candidates.interviewer)'); 
                        });
        } else if (Permission::getInstance()->isScopeSelf()) {
            $collection->where(function ($query) use ($candidateTbl, $currentUser) {
                            $query->where($candidateTbl.'.created_by', $currentUser->id)
                                ->orWhere($candidateTbl.'.recruiter', $currentUser->email )
                                ->orWhereRaw('('. $candidateTbl .'.interviewer IS NOT NULL AND FIND_IN_SET('. $currentUser->id .','. $candidateTbl .'.interviewer))');
                        });
        }

        $collection->groupBy($candidateTbl.'.id');
        //time now
        $currTime = Carbon::now();
        //filter year
        $testYear = Form::getFilterData('spec_data', 'test_year');
        if (!$testYear) {
            $testYear = $currTime->year;
        }
        $collection->where(DB::raw('YEAR('. $candidateTbl .'.test_plan)'), $testYear);
        //filter month
        $testMonth = Form::getFilterData('spec_data', 'test_month');
        if (!$testMonth && $testMonth != self::NULL_VAL) {
            $testMonth = $currTime->month;
        }
        if ($testMonth != self::NULL_VAL) {
            $collection->where(DB::raw('MONTH('. $candidateTbl .'.test_plan)'), $testMonth);
        }
        
        //filter had tested
        $hadTested = Form::getFilterData('spec_data', 'had_test');
        if ($hadTested) {
            if ($hadTested == self::YES_VAL) {
                $collection->where($candidateTbl.'.test_result', '!=', getOptions::RESULT_DEFAULT);
            } else if ($hadTested == self::NO_VAL) {
                $collection->where($candidateTbl.'.test_result', '=', getOptions::RESULT_DEFAULT);
            }
        }
        //filter had inteviewed
        $hadInterviewed = Form::getFilterData('spec_data', 'had_inteview');
        if ($hadInterviewed) {
            if ($hadInterviewed == self::YES_VAL) {
                $collection->where($candidateTbl.'.interview_result', '!=', getOptions::RESULT_DEFAULT);
            } else if ($hadInterviewed == self::NO_VAL) {
                $collection->where($candidateTbl.'.interview_result', '=', getOptions::RESULT_DEFAULT);
            }
        }
        //filter test result
        $testResult = Form::getFilterData('spec_data', 'test_result');
        if ($testResult !== null && $testResult != -1) {
            $testResult = intval($testResult);
            $collection->where($candidateTbl.'.test_result', $testResult);
        }
        
        //filter grid
        $collection = self::filterGrid($collection);
        
        if (Form::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy($candidateTbl.'.test_plan', 'desc');
        }
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;
    }
    
    /**
     * get label status as attribute
     * @return type
     */
    public function getStatusLabelAttribute() {
        return getOptions::getInstance()->getCandidateStatus($this->status);
    }
    
}
