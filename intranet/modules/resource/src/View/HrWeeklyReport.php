<?php

namespace Rikkei\Resource\View;

use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Employee;
use Carbon\Carbon;
use Rikkei\Resource\Model\Week;
use Illuminate\Support\Facades\DB;
use Rikkei\Resource\Model\CandidateProgramming;
use Rikkei\Resource\Model\Programs;
use Rikkei\Resource\Model\CandidatePosition;
use Rikkei\Test\Models\Result;
use Rikkei\Test\Models\Test;
use Rikkei\Test\Models\Type;
use Rikkei\Core\Model\CoreConfigData;

class HrWeeklyReport
{
    const LIMIT = 200;

    public static function getGridData(&$paramWiths = [])
    {
        $pager = Config::getPagerData(null, ['limit' => 20]);
        //filter recruiter
        if ($filterRecruiter = CoreForm::getFilterData('excerpt', 'recruiter')) {
            $paramWiths['recruiter'] = $filterRecruiter;
        }
        //permission
        $scope = Permission::getInstance();
        if ($scope->isScopeCompany()) {
            $paramWiths['scope_copany'] = true;
        } elseif ($scope->isScopeTeam()) {
            $paramWiths['scope_team'] = true;
            $currUser = $scope->getEmployee();
            $empTbl = Employee::getTableName();
            $paramWiths['team_ids'] = TeamMember::where('employee_id', $currUser->id)
                    ->lists('team_id')
                    ->toArray();
            $paramWiths['employee_emails'] = Employee::select($empTbl.'.email')
                    ->join(TeamMember::getTableName() . ' as tmb', $empTbl . '.id', '=', 'tmb.employee_id')
                    ->whereIn('tmb.team_id', $paramWiths['team_ids'])
                    ->groupBy($empTbl . '.email')
                    ->lists($empTbl . '.email')
                    ->toArray();
        } elseif ($scope->isScopeSelf()) {
            $paramWiths['scope_self'] = true;
            $paramWiths['current_email'] = $scope->getEmployee()->email;
        } else {
            CoreView::viewErrorPermission();
        }

        $collection = Week::select('wk.*', 'numcv.recruiter')
                ->from(Week::getTableName(). ' as wk')
                ->leftJoin(Candidate::getTableName() . ' as numcv', 'wk.week', '=', DB::raw('DATE_FORMAT(numcv.received_cv_date, "%X-%v")'))
                ->groupBy('wk.week');

        //permission
        if (isset($paramWiths['scope_copany'])) {
            //view all
        } elseif (isset($paramWiths['scope_team'])) {
            $collection->join(Employee::getTableName() . ' as emp', 'numcv.recruiter', '=', 'emp.email')
                    ->join(TeamMember::getTableName() . ' as tmb', 'emp.id', '=', 'tmb.employee_id')
                    ->whereIn('tmb.team_id', $paramWiths['team_ids']);
        } elseif (isset($paramWiths['scope_self'])) {
            $collection->where('numcv.recruiter', '=', $scope->getEmployee()->email);
        } else {
            //view error
        }

        //filter date
        if ($filterDateFrom = CoreForm::getFilterData('excerpt', 'date_from')) {
            $collection->where('wk.week', '>=', Carbon::parse($filterDateFrom)->format('Y-W'));
        }
        if ($filterDateTo = CoreForm::getFilterData('excerpt', 'date_to')) {
            $collection->where('wk.week', '<=', Carbon::parse($filterDateTo)->format('Y-W'));
        }
        //filter recruiter
        if (isset($paramWiths['recruiter'])) {
            $collection->where(function ($query) use ($filterRecruiter) {
                $query->orWhere('number_cvs', 'LIKE', '%"recruiter":"' . $filterRecruiter . '%')
                    ->orWhere('tests', 'LIKE', '%"recruiter":"' . $filterRecruiter . '%')
                    ->orWhere('tests_pass', 'LIKE', '%"recruiter":"' . $filterRecruiter . '%')
                    ->orWhere('gmats_8', 'LIKE', '%"recruiter":"' . $filterRecruiter . '%')
                    ->orWhere('interviews', 'LIKE', '%"recruiter":"' . $filterRecruiter . '%')
                    ->orWhere('interviews_pass', 'LIKE', '%"recruiter":"' . $filterRecruiter . '%')
                    ->orWhere('offers', 'LIKE', '%"recruiter":"' . $filterRecruiter . '%')
                    ->orWhere('offers_pass', 'LIKE', '%"recruiter":"' . $filterRecruiter . '%')
                    ->orWhere('workings', 'LIKE', '%"recruiter":"' . $filterRecruiter . '%');
            });
        }

        if (CoreForm::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('week', 'desc');
        }
        Candidate::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
     * get start, end of week
     * @param type $strWeek
     * @return array
     */
    public static function getArrDateByWeek($strWeek)
    {
        $now = Carbon::now();
        $arrWeek = explode('-', $strWeek);
        if (count($arrWeek) !== 2) {
            return [
                $now->modify('monday this week')->format('Y-m-d'),
                $now->modify('sunday this week')->format('Y-m-d'),
                $now->format('W')
            ];
        }
        $now->setISODate($arrWeek[0], $arrWeek[1]); //monday of week
        return [
            $now->startOfWeek()->format('Y-m-d'),
            $now->endOfWeek()->format('Y-m-d'),
            $now->format('W')
        ];
    }

    /**
     * count cv with program languages
     * @param type $strCv
     * @param type $programs
     * @return string
     */
    public static function countCvPrograms($collection, $programs, $position = [])
    {
        if ($collection->isEmpty()) {
            return;
        }
        $html = '<ul class="list-cv">';
        foreach ($collection->groupBy('prog_id') as $progId => $collect) {
            if (!$progId) {
                $progId = -1;
            }
            if (is_numeric($progId)) {
                $progPosName = isset($programs[$progId]) ? ' ' . $programs[$progId] : '';
            } else {
                $posId = explode('_', $progId)[1];
                $progPosName = isset($position[$posId]) ? ' ' . $position[$posId] : '';
            }
            $html .= '<li>'. $collect->count() . e($progPosName) .'</li>';
        }
        return $html .= '</ul>';
    }

    /**
     * get candidate name with program language name
     * @param type $strCdd
     * @param type $programs
     * @return string
     */
    public static function getCandidateProgram($collection, $programs, $position = [])
    {
        if ($collection->isEmpty()) {
            return null;
        }
        $html = '<ul class="list-cv">';
        $otherHtml = '';
        foreach ($collection as $item) {
            $progId = $item->prog_id;
            if (!$progId) {
                $progId = -1;
            }
            if (is_numeric($progId)) {
                $progName = isset($programs[$progId]) ? $programs[$progId] : '';
            } else {
                $posId = explode('_', $progId)[1];
                $progName = isset($position[$posId]) ? $position[$posId] : '';
            }
            $itemHtml = '<li><a target="_blank" href="'. route('resource::candidate.detail', $item->id) .'">'. $item->fullname .'</a> ('. $progName .')</li>';
            if ($progId != -1) {
                $html .= $itemHtml;
            } else {
                $otherHtml .= $itemHtml;
            }
        }
        $html .= $otherHtml;
        return $html .= '</ul>';
    }

    /**
     * get candidate name with start working date
     * @param type $strCdd
     * @return string
     */
    public static function getCandidateWorking($collection)
    {
        if ($collection->isEmpty()) {
            return;
        }
        $html = '<ul class="list-cv">';
        foreach ($collection as $item) {
            $html .= '<li>'. $item->start_working_date .': <a target="_blank" href="'. route('resource::candidate.detail', $item->id) .'">'. $item->fullname .'</a></li>';
        }
        return $html .= '</ul>';
    }

    /**
     * render list select options
     * @param type $programs
     * @param type $selected
     * @return string
     */
    public static function renderOptions($programs, $selected)
    {
        if (!$programs) {
            return null;
        }
        $option = '';
        foreach ($programs as $value => $label) {
            $option .= '<option value="'. $value .'" '.($value == $selected ? 'selected' : '').'>'. e($label) .'</option>';
        }
        return $option;
    }

    /*
     * cronjob collect data
     */
    public static function cronData()
    {
        $arrayDates = ['received_cv_date', 'test_plan', 'interview_plan', 'offer_date', 'start_working_date'];
        $zeroDate = '2014-01-01';
        $isFirstRun = CoreConfigData::getValueDb('hr_weekly_report_cron_lists');
        if ($isFirstRun) {
            $zeroDate = Carbon::now()->subMonths(3)->toDateString();
        } else {
            CoreConfigData::saveItem('hr_weekly_report_cron_lists', 1);
        }

        $cddTbl = Candidate::getTableName();
        $strMaxField = implode(',', array_map(function ($iDate) {
            return 'MAX('. $iDate .')';
        }, $arrayDates));
        $strMinField = implode(',', array_map(function ($iDate) {
            return 'MIN('. $iDate .')';
        }, $arrayDates));

        $queryMaxDate = 'SELECT DATE(GREATEST('. $strMaxField .')) AS max_date FROM ' . $cddTbl . ' WHERE deleted_at IS NULL';
        $queryMinDate = 'SELECT DATE(LEAST('. $strMinField .')) AS min_date FROM ' . $cddTbl . ' WHERE deleted_at IS NULL AND ';
        $queryField = [];
        foreach ($arrayDates as $field) {
            $queryField[] = $field . ' >= ' . '"'. $zeroDate .'"';
        }
        $queryMinDate .= implode(' AND ', $queryField);

        $strMaxDate = DB::select($queryMaxDate)[0]->max_date;
        $strMinDate = DB::select($queryMinDate)[0]->min_date;

        $maxDate = Carbon::parse($strMaxDate);
        $minDate = Carbon::parse($strMinDate);
        $dateCount = clone $minDate;

        while ($dateCount->lte($maxDate)) {
            $itemWeek = $dateCount->format('Y-W');
            $dataSave = [
                'number_cvs' => json_encode(self::getDataNumCv($itemWeek)),
                'tests' => json_encode(self::getDataTest($itemWeek)),
                'tests_pass' => json_encode(self::getDataTest($itemWeek, getOptions::RESULT_PASS)),
                'gmats_8' => json_encode(self::getDataTest($itemWeek, null, 8)),
                'interviews' => json_encode(self::getDataInterview($itemWeek)),
                'interviews_pass' => json_encode(self::getDataInterview($itemWeek, getOptions::RESULT_PASS)),
                'offers' => json_encode(self::getDataOffer($itemWeek)),
                'offers_pass' => json_encode(self::getDataOffer($itemWeek, getOptions::RESULT_PASS)),
                'workings' => json_encode(self::getDataWorking($itemWeek))
            ];
            $weekModel = Week::where('week', $itemWeek)->first();
            if ($weekModel) {
                $weekModel->update($dataSave);
            } else {
                $isSave = false;
                foreach ($dataSave as $colData) {
                    if ($colData && $colData != '[]') {
                        $isSave = true;
                        break;
                    }
                }
                if ($isSave) {
                    $dataSave['week'] = $itemWeek;
                    $weekModel = Week::create($dataSave);
                }
            }
            $dateCount->addWeek();
        }
    }

    /*
     * query general candidate
     */
    public static function queryData($andWhere = '', $groupBy = 'cdd.id', $joinTest = false)
    {
        $queryJoinTest = $joinTest ? 'LEFT JOIN ' . Result::getTableName() . ' as trs '
                    . 'INNER JOIN '. Test::getTableName() .' as test ON trs.test_id = test.id '
                    . 'INNER JOIN '. Type::getTableName() .' as ttype ON test.type_id = ttype.id '
                    . 'AND ttype.id = '. Test::getGMATId() . ' '
                . ' ON cdd.email = trs.employee_email ' : '';
        return ' FROM ' . Candidate::getTableName() . ' AS cdd '
                . 'LEFT JOIN ' . CandidateProgramming::getTableName() . ' AS cddprog ON cdd.id = cddprog.candidate_id '
                . 'LEFT JOIN ' . Programs::getTableName() . ' AS pl ON cddprog.programming_id = pl.id '
                . 'LEFT JOIN ' . CandidatePosition::getTableName() . ' AS cddpos ON cdd.id = cddpos.candidate_id '
                . $queryJoinTest
                . 'WHERE cdd.deleted_at IS NULL '
                . $andWhere . ' '
                . 'GROUP BY ' . $groupBy . ' '
                . 'LIMIT ' . self::LIMIT;
    }

    /*
     * query get candidate reciever cv date in week
     */
    public static function getDataNumCv($week)
    {
        return DB::select(
            'SELECT cdd.id, '
            . 'cdd.fullname, '
            . 'cdd.email, '
            . 'cdd.recruiter, '
            . 'GROUP_CONCAT(DISTINCT(IFNULL(pl.id, IF(cddpos.position_apply IS NULL, -1, CONCAT("p_", cddpos.position_apply))))) AS prog_id'
            . self::queryData('AND DATE_FORMAT(cdd.received_cv_date, "%X-%v") = "'. $week .'"')
        );
    }

    /*
     * query get candidate test plan in week
     */
    public static function getDataTest($week, $result = null, $gmatPoit = null)
    {
        $queryResult = $result ? ' AND cdd.test_result = ' . $result : '';
        return DB::select(
            'SELECT cdd.id, '
            . 'cdd.fullname, '
            . 'cdd.email, '
            . 'cdd.recruiter, '
            . ($gmatPoit ? 'IFNULL(MAX(trs.total_corrects / trs.total_question * 10), cdd.test_gmat_point) AS gmat_point, ' : '')
            . 'GROUP_CONCAT(DISTINCT(IFNULL(pl.id, IF(cddpos.position_apply IS NULL, -1, CONCAT("p_", cddpos.position_apply))))) AS prog_id'
            . self::queryData(
                'AND DATE_FORMAT(cdd.test_plan, "%X-%v") = "'. $week .'"' . $queryResult,
                'cdd.id' . ($gmatPoit ? ' HAVING gmat_point >= 8' : ''),
                $gmatPoit
            )
        );
    }

    /*
     * query get candidate interview plan in week
     */
    public static function getDataInterview($week, $result = null)
    {
        $queryResult = $result ? ' AND cdd.interview_result = ' . $result : '';
        return DB::select(
            'SELECT cdd.id, '
            . 'cdd.fullname, '
            . 'cdd.email, '
            . 'cdd.recruiter, '
            . 'GROUP_CONCAT(DISTINCT(IFNULL(pl.id, IF(cddpos.position_apply IS NULL, -1, CONCAT("p_", cddpos.position_apply))))) AS prog_id'
            . self::queryData('AND DATE_FORMAT(cdd.interview_plan, "%X-%v") = "'. $week .'"' . $queryResult)
        );
    }

    /*
     * query get candidate offer date in week
     */
    public static function getDataOffer($week, $result = null)
    {
        $queryResult = $result ? ' AND cdd.offer_result = ' . $result : '';
        return DB::select(
            'SELECT cdd.id, '
            . 'cdd.fullname, '
            . 'cdd.email, '
            . 'cdd.recruiter, '
            . 'GROUP_CONCAT(DISTINCT(IFNULL(pl.id, IF(cddpos.position_apply IS NULL, -1, CONCAT("p_", cddpos.position_apply))))) AS prog_id'
            . self::queryData('AND DATE_FORMAT(cdd.offer_date, "%X-%v") = "'. $week .'"' . $queryResult)
        );
    }

    /*
     * query get candidate start working date in week
     */
    public static function getDataWorking($week)
    {
        $querySql = 'SELECT cdd.id, '
            . 'cdd.fullname, '
            . 'cdd.employee_id, '
            . 'cdd.email, '
            . 'cdd.recruiter, '
            . 'cdd.team_id, '
            . 'DATE(cdd.start_working_date) as start_working_date, '
            . 'GROUP_CONCAT(DISTINCT(IFNULL(pl.id, IF(cddpos.position_apply IS NULL, -1, CONCAT("p_", cddpos.position_apply))))) AS prog_id'
            . self::queryData(
                'AND DATE_FORMAT(cdd.start_working_date, "%X-%v") = "'. $week .'" '
                . 'AND cdd.status IN (' . getOptions::WORKING . ', '. getOptions::PREPARING . ', '. getOptions::LEAVED_OFF .')',
                'cdd.id ORDER BY cdd.start_working_date DESC');

        return DB::table(DB::raw("({$querySql}) as cdd"))
            ->leftJoin('teams', 'teams.id', '=', 'cdd.team_id')
            ->select('cdd.*', 'teams.name')
            ->groupBy('cdd.id')
            ->limit(self::LIMIT)
            ->get();
    }
}
