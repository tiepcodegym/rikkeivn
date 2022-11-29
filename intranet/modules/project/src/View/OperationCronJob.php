<?php

namespace Rikkei\Project\View;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjectWOBase;
use Rikkei\Project\Model\ProjQuality;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\Model\Team;
use Rikkei\Project\Model\OperationOverview;
use Rikkei\Project\View\OperationOverview as HelperOverview;

class OperationCronJob
{
    const TYPE_OSDC = 1;
    const TYPE_BASE = 2;
    const TYPE_ONSITE = 5;
    const MONTH = 12;

    const CODE_PREFIX_DN = 'danang';
    const CODE_PREFIX_JP = 'japan';
    const CODE_PREFIX_HCM = 'hcm';

    const KIND_OFFSHORE_VN = 1;
    const KIND_OFFSHORE_JP = 2;
    const KIND_OFFSHORE_EN = 3;
    const KIND_ONSITE_JP = 4;
    const KIND_INTERNAL = 5;
    const KIND_OTHER = 6;

    public static function cronJobOperationOverview()
    {
        DB::beginTransaction();
        try {
            $arrInsert = [];
            if(self::checkExitDB()) {
                $currentMonth = Carbon::now()->format("m");
                $currentYear = Carbon::now()->format("Y");
                if ($currentMonth == 12) $currentYear++;

                $monthFrom = Carbon::now()->subMonths(self::MONTH)->format("Y-m");
                $monthTo = $currentYear . "-12";
                self::deleteDataBD($monthFrom, $monthTo);
                $arrInsert = self::arrayInsertDB($monthFrom, $monthTo);
            } else {
                $monthFrom = "2012-01";
                $monthTo = Carbon::now()->format("Y") . "-12";
                $arrInsert = self::arrayInsertDB($monthFrom, $monthTo);
            }
            OperationOverview::insert($arrInsert);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    public static function checkExitDB()
    {
        $isCheck = OperationOverview::all()->count();
        if ($isCheck) {
            return true;
        }

        return false;
    }

    public static function deleteDataBD($monthFrom, $monthTo)
    {
        return  OperationOverview::where(function ($q) use ($monthFrom, $monthTo) {
            return $q->where("month", ">=", $monthFrom)->where("month", "<=", $monthTo);
        })->delete();
    }

    public static function arrayInsertDB($monthFrom, $monthTo)
    {
        $arrTeamId = self::getTeamProjectCost();

        $arrInsert = [];
        foreach ($arrTeamId as $key => $value) {
            $filters = [
                'monthFrom' => date('Y-m-d', strtotime($monthFrom)),
                'monthTo' => date('Y-m-t', strtotime($monthTo)),
                'team_id' => $value['team_id']
            ];
            $employeePoints = OperationMember::getDataByMonth($filters);
            $employeePointsDimension = OperationMember::convertDataMemberToDimension($employeePoints);

            $collectionMaternity = OperationMember::getMaternityLeaveDayCollection($filters);
            $maternityDataDimension = OperationMember::transformMaternityLeaveDayCollectionToDimension($collectionMaternity);

            $projectCost = self::getProjectGridData($filters);
            $totalMember = self::getTotalMemberPointEachMonth(
                $filters,
                $employeePointsDimension,
                $maternityDataDimension
            );

            $data = self::getDetailTotalPointEachMonth($totalMember, $projectCost, $value);
            $arrInsert = array_merge($arrInsert, $data);//  merge array in one array under
        }

        return $arrInsert;
    }

    public static function getTeamProjectCost()
    {
        $arrNew = [];
        $teamConditions = [
            'is_soft_dev' => Team::IS_SOFT_DEVELOPMENT,
        ];
        $teamIsDev = Team::getTeamList($teamConditions, ['id', 'name', 'branch_code', 'parent_id'])->toArray();
        $parent = self::getParentByTeam($teamIsDev);
        // Get teamId có is_soft_dev = 1.
        foreach ($teamIsDev as $value) {
            $arrNew[$value ['id']] = [
                'team_id' => $value ['id'],
                'branch_code' => $value ['branch_code']
            ];
        }
        // Parent id team.
        foreach ($parent as $val) {
            if (!array_key_exists($val, $arrNew)) {
                $arrNew[$val] = [
                    'team_id' => $val,
                    'branch_code' => ''
                ];
            }
        }
        // Danh sach chi nhanh.
        $listPrefixBranch = Team::listPrefixBranch();
        $teamIsbranh = Team::select('id', 'name', 'branch_code')
            ->whereIn('branch_code', array_keys($listPrefixBranch))
            ->where('is_branch', 1)
            ->get()->toArray();

        foreach ($teamIsbranh as $value) {
            if (!array_key_exists($value ['id'], $arrNew)) {
                $arrNew[$value ['id']] = [
                    'team_id' => $value ['id'],
                    'branch_code' => $value ['branch_code']
                ];
            }
        }

        return $arrNew;
    }

    /**
     * Lấy all team cha theo team hiện tai
     *
     * @param $teamIds
     * @return array
     */
    public static function getParentByTeam($teamIds)
    {
        $litsTeam = Team::getTeamPathTree();
        $teamParent = [];
        foreach ($teamIds as $value) {
            if (array_key_exists($value['id'], $litsTeam)) {
                $teamParent = array_merge($teamParent, $litsTeam[$value['id']]['parent']);
            }
        }
        return array_unique($teamParent);
    }

    public static function getTotalMemberPointEachMonth($filters, $employeePointsDimension, $maternityDataDimension)
    {
        $employeePoints = $employeePointsDimension;
        $employeeMaternity = $maternityDataDimension;
        $result = [];
        $monthFrom = date('Y-m', strtotime($filters['monthFrom']));
        while ($monthFrom <= date('Y-m', strtotime($filters['monthTo']))) {
            $totalPoint = 0;
            $totalPointMain = 0;
            $totalPointPartTime = 0;
            foreach ($employeePoints as $key => $value) {
                if (isset($employeePoints[$key])) {
                    foreach ($employeePoints[$key] as $k_time => $v_data) {
                        $listTimeline = $employeePoints[$key][$k_time];
                        if(!self::checkValidContractDateEmployee($monthFrom, $listTimeline)) {
                            continue;
                        }
                        $employeeInforMappingMonth = self::getDataMappingMonth($employeePoints, $key, $k_time, $monthFrom);
                        if($employeeInforMappingMonth) {
                            $point = floatval($employeeInforMappingMonth['point']);
                            if ($employeeInforMappingMonth['join_date'] > $monthFrom) {
                                continue;
                            }
                            if ($employeeInforMappingMonth['join_date'] === $monthFrom) {
                                $point = floatval($employeeInforMappingMonth['actual_point_first_month']);
                            }
                            if($employeeInforMappingMonth['leave_date'] === $monthFrom) {
                                $point = floatval($employeeInforMappingMonth['actual_point_last_month']);
                            }
                            $detailEmployeeId = explode("-", $key)[1];

                            $percentNotWorking = self::getPointForMaternity($monthFrom, $detailEmployeeId, $employeeMaternity);
                            $point = ($point - ($percentNotWorking*$point));
                            $currentPoint = floatval($point >= 0 ? $point : 0);
                            $totalPoint += $currentPoint;
                            if ($employeeInforMappingMonth['contract_type'] == getOptions::WORKING_PARTTIME) {
                                $totalPointPartTime += $currentPoint;
                            } else {
                                $totalPointMain += $currentPoint;
                            }
                        }
                    }
                }
            }
            $result[$monthFrom]['total_point'] = $totalPoint;
            $result[$monthFrom]['total_point_main'] = $totalPointMain;
            $result[$monthFrom]['total_point_part_time'] = $totalPointPartTime;
            $monthFrom = Carbon::parse($monthFrom)->addMonths(1)->format('Y-m');
        }
        return $result;
    }

    public static function checkValidContractDateEmployee($monthFrom, $listTimeline)
    {
        $months = array_keys($listTimeline);
        $flag = false;
        foreach ($months as $key => $value) {
            if ($monthFrom >= $value) $flag = true;
        }

        return $flag;
    }

    public static function getDataMappingMonth($employeePoints, $employeeId, $startTeam, $scopeMonthFrom)
    {
        if (!isset($employeePoints[$employeeId][$startTeam][$scopeMonthFrom])) {
            if ($scopeMonthFrom > '2012-01') {
                $arrayKeyContractStartDates = array_keys($employeePoints[$employeeId][$startTeam]);
                sort($arrayKeyContractStartDates);
                $reverseArray = array_reverse($arrayKeyContractStartDates);
                foreach ($reverseArray as $key => $value) {
                    if ($scopeMonthFrom > $reverseArray[$key]) {
                        $result = $employeePoints[$employeeId][$startTeam][$reverseArray[$key]];
                        if ($result && $result['leave_date'] && $scopeMonthFrom > $result['leave_date']) {
                            return null;
                        }

                        return $result;
                    }
                }
            }
        }

        return $employeePoints[$employeeId][$startTeam][$scopeMonthFrom];
    }

    public static function getPointForMaternity($monthFrom, $detailEmployeeId, $employeeMaternity)
    {
        if(!isset($employeeMaternity[$detailEmployeeId])) {
            return 0;
        }

        foreach ($employeeMaternity[$detailEmployeeId] as $key => $value)
        {
            $currentData = $employeeMaternity[$detailEmployeeId][$key];
            if ($monthFrom == $currentData['leave_start']) {
                return $currentData['percent_not_working_from_leave_start'];
            } else if ($monthFrom == $currentData['leave_end']) {
                return  $currentData['percent_not_working_until_leave_end'];
            } else if ($monthFrom > $currentData['leave_start'] && $monthFrom < $currentData['leave_end']) {
                return 1;
            }
        }

        return 0;
    }

    public static function getDetailTotalPointEachMonth($totalMember, $projectCost, $item)
    {
        $totalMemeberPointEachMonth = $totalMember;
        $totalProjectPointEachMonth = self::tranformData($projectCost);
        $countProjectKindEachMonth = self::tranformDataProjectKind($projectCost);
        $result = [];
        $osdc = 0;
        $base = 0;
        $onsite = 0;
        $offVN = 0;
        $offJP = 0;
        $offEN = 0;
        $onstieJP = 0;
        $internal = 0;
        $other = 0;
        $collapse = self::checkBrannCode($item['branch_code']);
        foreach ($totalMemeberPointEachMonth as $key => $value) {
            $osdc = (isset($totalProjectPointEachMonth[$key]) && isset($totalProjectPointEachMonth[$key][self::TYPE_OSDC])) ? $totalProjectPointEachMonth[$key][self::TYPE_OSDC]['cost'] : 0;
            $base = (isset($totalProjectPointEachMonth[$key]) && isset($totalProjectPointEachMonth[$key][self::TYPE_BASE])) ? $totalProjectPointEachMonth[$key][self::TYPE_BASE]['cost'] : 0;
            $onsite = (isset($totalProjectPointEachMonth[$key]) && isset($totalProjectPointEachMonth[$key][self::TYPE_ONSITE])) ? $totalProjectPointEachMonth[$key][self::TYPE_ONSITE]['cost'] : 0;

            $offVN = (isset($countProjectKindEachMonth[$key]) && isset($countProjectKindEachMonth[$key][self::KIND_OFFSHORE_VN])) ? $countProjectKindEachMonth[$key][self::KIND_OFFSHORE_VN]['cost'] : 0;
            $offJP = (isset($countProjectKindEachMonth[$key]) && isset($countProjectKindEachMonth[$key][self::KIND_OFFSHORE_JP])) ? $countProjectKindEachMonth[$key][self::KIND_OFFSHORE_JP]['cost'] : 0;
            $offEN = (isset($countProjectKindEachMonth[$key]) && isset($countProjectKindEachMonth[$key][self::KIND_OFFSHORE_EN])) ? $countProjectKindEachMonth[$key][self::KIND_OFFSHORE_EN]['cost'] : 0;
            $onstieJP = (isset($countProjectKindEachMonth[$key]) && isset($countProjectKindEachMonth[$key][self::KIND_ONSITE_JP])) ? $countProjectKindEachMonth[$key][self::KIND_ONSITE_JP]['cost'] : 0;
            $internal = (isset($countProjectKindEachMonth[$key]) && isset($countProjectKindEachMonth[$key][self::KIND_INTERNAL])) ? $countProjectKindEachMonth[$key][self::KIND_INTERNAL]['cost'] : 0;
            $other = (isset($countProjectKindEachMonth[$key]) && isset($countProjectKindEachMonth[$key][self::KIND_OTHER])) ? $countProjectKindEachMonth[$key][self::KIND_OTHER]['cost'] : 0;

            $result[] = [
                'month' => $key,
                'members' => $totalMemeberPointEachMonth[$key]['total_point'],
                'member_main' => $totalMemeberPointEachMonth[$key]['total_point_main'],
                'member_part_time' => $totalMemeberPointEachMonth[$key]['total_point_part_time'],
                'osdc' => $osdc,
                'base' => $base,
                'onsite' => $onsite,
                'project' => floatval($osdc) + floatval($base) + floatval($onsite),
                'team_id' => $item['team_id'],
                'branch_code' => $item['branch_code'],
                'is_collapse' => $collapse,
                'offshore_vn' => $offVN,
                'offshore_jp' => $offJP,
                'offshore_en' => $offEN,
                'onsite_jp' => $onstieJP,
                'internal' => $internal,
                'other' => $other,
            ];
        }

        return $result;
    }

    public static function checkBrannCode($name)
    {
        if (!$name) {
            return '0';
        }

        switch ($name) {
            case self::CODE_PREFIX_DN:
            case self::CODE_PREFIX_JP:
            case self::CODE_PREFIX_HCM:
                return '1';
            default:
                return '0';
        }
    }

    /**
     * get project grid data
     *
     * @param  array $filter
     *
     * @return array
     */
    public static function getProjectGridData($filter)
    {
        $projectApprovedProductionCostTable = 'project_approved_production_cost';
        $projectAdditionalTable = 'projs_additional';
        $teamsTable = Team::getTableName();
        $projectsTable = Project::getTableName();
        $yearMonthAs = 'yearMonth';
        $concatMonthYear = "CONCAT({$projectApprovedProductionCostTable}.year, '-', LPAD({$projectApprovedProductionCostTable}.month, 2, 0))";
        $concatMonthYearProjAdditional = "CONCAT({$projectAdditionalTable}.year, '-', LPAD({$projectAdditionalTable}.month, 2, 0))";
        $from = date($filter['monthFrom']);
        $to = date($filter['monthTo']);
        $projectStatusApprove = Project::STATUS_APPROVED;
        $projectQualityTable = ProjQuality::getTableName();

        $teamId = null;
        if ($filter['team_id']) {
            $teamId = $filter['team_id'];
            $teamModel = Team::find($teamId);
            $team = Team::getTeamPath($withTrashed = true);

            if (isset($team[$teamId]['child']) && ($teamModel )) { //get all team childs except BOD
                $teamId = '(' . $teamId . ', ' . implode(", ", $team[$teamId]['child']) . ')';
            } else {
                $teamId = '(' . $teamId . ')';
            }
        }
        $projectCost = DB::table($projectsTable)
            ->select(
                "{$projectsTable}.type",
                DB::raw("{$concatMonthYear} as {$yearMonthAs}"),
                DB::raw("sum({$projectApprovedProductionCostTable}.approved_production_cost) as cost"),
                DB::raw("COALESCE({$projectsTable}.kind_id, 6) as kind")
            )
            ->join($projectApprovedProductionCostTable, "{$projectsTable}.id", '=', "{$projectApprovedProductionCostTable}.project_id")
            ->leftJoin($projectQualityTable, "{$projectQualityTable}.project_id", '=', "{$projectsTable}.id")
            ->whereIn("{$projectsTable}.type", [Project::TYPE_OSDC, Project::TYPE_BASE, Project::TYPE_ONSITE])
            ->whereRaw("{$projectsTable}.status = {$projectStatusApprove}")
            ->whereNull("{$projectsTable}.deleted_at")
            ->whereNull("{$projectApprovedProductionCostTable}.deleted_at")
            ->whereIn("{$projectQualityTable}.id", function ($q) use ($projectQualityTable, $projectsTable) {
                $q->select(DB::raw("max(id)"))->from($projectQualityTable)
                    ->whereNull("{$projectQualityTable}.deleted_at")
                    ->where(function ($where) use ($projectQualityTable) {
                        $where->where(function ($where) use ($projectQualityTable) {
                            $where->where("{$projectQualityTable}.status", '!=', ProjectWOBase::STATUS_APPROVED)
                                ->whereNull('billable_effort')
                                ->whereNull('plan_effort');
                        })->orWhere("{$projectQualityTable}.status", '=', ProjectWOBase::STATUS_APPROVED);
                    })
                    ->groupBy("{$projectQualityTable}.project_id");;
            })
            ->whereNull("{$projectsTable}.deleted_at")
            ->whereRaw(
                "{$projectsTable}.parent_id IS NULL"
            )
            ->join($teamsTable, "{$teamsTable}.id", "=", "{$projectApprovedProductionCostTable}.team_id");

        if ($teamId) {
            $projectCost = $projectCost->whereRaw(
                "{$teamsTable}.id IN {$teamId}"
            );
        }
        $projectCost = $projectCost->whereBetween(DB::raw("str_to_date(CONCAT({$concatMonthYear}, '-01'), '%Y-%m-%d')"), [$from, $to])
            ->groupBy($yearMonthAs)
            ->groupBy('type')
            ->groupBy('kind');

        $projectFutureCost = DB::table($projectAdditionalTable)
            ->select(
                "type",
                DB::raw("{$concatMonthYearProjAdditional} as {$yearMonthAs}"),
                DB::raw("sum({$projectAdditionalTable}.approved_production_cost) as cost"),
                DB::raw("COALESCE(kind_id, 6) as kind")
            )
            ->whereNull("{$projectAdditionalTable}.deleted_at")
            ->whereIn("type", [Project::TYPE_OSDC, Project::TYPE_BASE, Project::TYPE_ONSITE]);
        if ($teamId) {
            $projectFutureCost = $projectFutureCost->whereRaw(
                "team_id IN {$teamId}"
            );
        }
        $projectFutureCost = $projectFutureCost->whereBetween(DB::raw("str_to_date(CONCAT({$concatMonthYearProjAdditional}, '-01'), '%Y-%m-%d')"), [$from, $to])
            ->groupBy($yearMonthAs)
            ->groupBy('type')
            ->groupBy('kind');

        $query = $projectFutureCost->union($projectCost);
        $querySql = $query->toSql();
        $query = DB::table(DB::raw("($querySql) as a"))
            ->select(
                'type',
                $yearMonthAs,
                DB::raw("sum(cost) as cost"),
                'kind'
            )
            ->groupBy($yearMonthAs)
            ->groupBy('type')
            ->groupBy('kind')
            ->mergeBindings($query);

        return collect($query->get())->groupBy($yearMonthAs, true)->map(function ($pb) { return $pb->keyBy('type'); })->toArray();
    }

    /**
     * tranformData
     *
     * @return array
     */
    public static function tranformData($projectCost)
    {
        $arrNew = [];
        foreach ($projectCost as $key => $value) {
            $res = [];
            foreach ($value as $item) {
                if(array_key_exists($item->type,$res)) {
                    $res[$item->type]['type'] = $item->type;
                    $res[$item->type]['yearMonth'] = $item->yearMonth;
                    $res[$item->type]['cost'] += $item->cost;
                } else {
                    $res[$item->type]['type'] = $item->type;
                    $res[$item->type]['yearMonth'] = $item->yearMonth;
                    $res[$item->type]['cost'] = $item->cost;
                }
            }
            $arrNew[$key] = $res;
        }

        return $arrNew;
    }

    public static function tranformDataProjectKind($projectCost)
    {
        $arrNew = [];
        foreach ($projectCost as $key => $value) {
            $res = [];
            foreach ($value as $item) {
                if(array_key_exists($item->kind,$res)) {
                    $res[$item->kind]['kind'] = $item->kind;
                    $res[$item->kind]['yearMonth'] = $item->yearMonth;
                    $res[$item->kind]['cost'] += $item->cost;
                } else {
                    $res[$item->kind]['kind'] = $item->kind;
                    $res[$item->kind]['yearMonth'] = $item->yearMonth;
                    $res[$item->kind]['cost'] = $item->cost;
                }
            }
            $arrNew[$key] = $res;
        }

        return $arrNew;
    }
}
