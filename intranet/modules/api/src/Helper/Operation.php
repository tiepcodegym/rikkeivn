<?php

namespace Rikkei\Api\Helper;

use Carbon\Carbon;
use DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Rikkei\Api\Helper\Base as BaseHelper;
use Rikkei\Core\Http\Requests\Request;
use Rikkei\Project\Model\OperationOverview as ModelOverview;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjectAdditional;
use Rikkei\Project\Model\ProjectApprovedProductionCost;
use Rikkei\Project\View\OperationOverview as HelperOverview;
use Rikkei\Project\View\OperationOverview;
use Rikkei\Project\View\OperationProject;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\Permission;
use Rikkei\Team\View\TeamList;

class Operation extends BaseHelper
{
    const TYPE_PROJECT = 'projects';
    const TYPE_OVERVIEW = 'overview';

    const TYPE_OSDC = 1;
    const TYPE_BASE = 2;
    const TYPE_ONSITE = 5;

    const KIND_OFFSHORE_VN = 1;
    const KIND_OFFSHORE_JP = 2;
    const KIND_OFFSHORE_EN = 3;
    const KIND_ONSITE_JP = 4;
    const KIND_INTERNAL = 5;
    const KIND_ONSITE_VN = 7;
    const KIND_OTHER = 6;

    /** Swich get data  */
    public static function swichViewManin($request)
    {
        switch ($request->typeViewMain) {
            case (self::TYPE_PROJECT):
                return self::getProjectByMonth($request);
            case (self::TYPE_OVERVIEW) :
                return self::getOperationOverview($request);
            default:
                return false;
        }
    }

    /** Check Permission is Company
     * @param String $email
     * @return boolean
     */
    public static function isCheckPermissionCompany($email)
    {
        $employee = Employee::where('email', $email)->first();
        $permission = Permission::getInstance($employee);

        if ($permission->isScopeCompany()) {
            return true;
        }

        return false;
    }

    /** Get Team Lead
     * @param String $email
     * @return array team | null
     */
    public static function getTeamIsSoftDev($request)
    {
        $employee = Employee::where('email', $request->email)->first();
        $permission = Permission::getInstance($employee);
        $selectedFields = ['name', 'id', 'leader_id', 'is_soft_dev', 'code', 'parent_id', 'is_function'];
        $collections = Team::select($selectedFields);

        if ($permission->isScopeCompany()) {
            $collections = $collections->orderBy('sort_order', 'asc');
            $scope = \Rikkei\Team\Model\Permission::SCOPE_COMPANY;
        } elseif ($ownedTeamIds = $permission->isScopeTeam()) {
            $collections = $collections->whereIn('id', $ownedTeamIds);
            $scope = \Rikkei\Team\Model\Permission::SCOPE_TEAM;
        } elseif ($permission->isScopeSelf()) {
            $scope = \Rikkei\Team\Model\Permission::SCOPE_SELF;
        } else {
            return [];
        }
        $ownerTeamIds = $collections->get();

        return static::getListOption($ownerTeamIds, $request, $employee, $scope);
    }
    /**
     * Return List data for operation overview
     *
     * @param $result
     * @param $teamProjectCost
     * @param $filters
     *
     * @return array
     */
    public static function getOverviewData($result, $teamProjectCost, $filters)
    {
        $rangeSelectedMonth = self::initContinuousMonthV2($filters);

        if (count($filters['team_id']) == 1) {
            $arrNew = [];
            foreach ($rangeSelectedMonth as $key => $value) {
                if (empty($result[$key]['month'])) {
                    $index = (count($result) - 1) > 0 ?  (count($result) - 1) : 0;
                    $arrNew[] = [
                        "base" => "0",
                        "members" => empty($result[$index]['members']) ? "0" : $result[$index]['members'],
                        "month" => $value,
                        "onsite" => "0",
                        "osdc" => "0",
                        "project" => "0",
                        "team_id" => $filters['team_id'][0],
                        "branch_code" => empty($result[$index]['branch_code']) ? "" : $result[$index]['branch_code'],
                        "is_collapse" => empty($result[$index]['is_collapse']) ? "0" : $result[$index]['is_collapse'],
                        "offshore_vn" => "0",
                        "offshore_jp" => "0",
                        "offshore_en" => "0",
                        "onsite_jp" => "0",
                        "onsite_vn" => "0",
                        "internal" => "0",
                        "other" => "0"
                    ];
                }
            }
            $data = array_merge($result, $arrNew);
        } else {
            $data = [];
            if (!count($result)) {
                foreach ($filters['team_id'] as $value) {
                    foreach ($rangeSelectedMonth as $key => $v) {
                        $data[] = [
                            "base" => "0",
                            "members" =>  "0",
                            "month" => $v,
                            "onsite" => "0",
                            "osdc" => "0",
                            "project" => "0",
                            "team_id" => $value,
                            "branch_code" => "" ,
                            "is_collapse" => "0",
                            "offshore_vn" => "0",
                            "offshore_jp" => "0",
                            "offshore_en" => "0",
                            "onsite_jp" => "0",
                            "onsite_vn" => "0",
                            "internal" => "0",
                            "other" => "0"
                        ];
                    }
                }
            } else {
                // filter arrTeam project
                $arrData = [];
                foreach ($teamProjectCost as $key => $value) {
                    $arrItem = [];
                    foreach ($result as $item) {
                        if ($value == $item['team_id']) {
                            $arrItem[] = $item;
                        }
                    }
                    if (count($arrItem)) {
                        $arrData[] = $arrItem;
                    }
                }

                foreach ($arrData as $k => $value) {
                    foreach ($rangeSelectedMonth as $key => $v) {
                        if (empty($value[$key]['month'])) {
                            $index = (count($value) - 1) > 0 ?  (count($value) - 1) : 0;
                            $arrNewItem = [
                                "base" => "0",
                                "members" => empty($value[$index]['members']) ? "0" : $value[$index]['members'],
                                "month" => $v,
                                "onsite" => "0",
                                "osdc" => "0",
                                "project" => "0",
                                "team_id" => empty($value[$index]['team_id']) ? "" : $value[$index]['team_id'],
                                "branch_code" => empty($value[$index]['branch_code']) ? "" : $value[$index]['branch_code'],
                                "is_collapse" => empty($value[$index]['is_collapse']) ? "0" : $value[$index]['is_collapse'],
                                "offshore_vn" => "0",
                                "offshore_jp" => "0",
                                "offshore_en" => "0",
                                "onsite_jp" => "0",
                                "onsite_vn" => "0",
                                "internal" => "0",
                                "other" => "0"
                            ];
                            array_push($arrData[$k], $arrNewItem);
                        }
                    }
                    $data = array_merge($data, $arrData[$k]);
                }
            }
        }

        return $data;
    }
    /** List data operation overview with condition teamId and month
     * @param Request $request
     * @return mixed
     */
    public static function getOperationOverview($request)
    {
        $filters = [
            'monthFrom' => date('Y-m', strtotime($request->monthFrom)),
            'monthTo' => date('Y-m', strtotime($request->monthTo)),
            'team_id' => $request->teamId,
            'email' => $request->email,
        ];
        $totalMember = [];
        $teamProjectCost = null;
        if ($request->isCompany) {
            $teamProjectCost = OperationOverview::getTeamProjectCost($request->teamId);
        }
        $result = ModelOverview::where(function ($q) use ($filters) {
            return $q->where("month", ">=" , $filters['monthFrom'])->where("month", "<=" , $filters['monthTo']);
        })->whereIn('team_id', $filters['team_id'])->get()->toArray();

        foreach ($filters['team_id'] as $value) {
            $datafilter = [
                'monthFrom' => date('Y-m-d', strtotime($request->monthFrom)),
                'monthTo' => date('Y-m-t', strtotime($request->monthTo)),
                'team_id' => $value,
                'is_internal_project' => $request->isInternalProject
            ];

            $overviewData = OperationOverview::getProjectGridDataApi($datafilter);
            $resultTotal= self::getDetailTotalPointEachMonth($result, $overviewData, $value);
            $totalMember =array_merge($totalMember, $resultTotal);//  merge array in one array under
        }

        $data = self::getOverviewData($totalMember, $teamProjectCost, $filters);

        if (self::isCheckPermissionCompany($filters['email']) == true) {
            return [
                'overview' => $data,
                'teamProjectCost' => $teamProjectCost,
                'isCompany' => true,
                'team_id' => null
            ];
        }

        return [
            'overview' => $data,
            'teamProjectCost' => $teamProjectCost,
            'isCompany' => false,
            'team_id' => static::getTeamIsSoftDev($request)
        ];
    }

    public static function getProjectByMonth($requests)
    {

        $filter = [
            'monthFrom' => $requests->monthFrom,
            'monthTo' => $requests->monthTo,
            'url' => $requests->url,
            'path' => $requests->path,
            'team_id' => $requests->teamId[0],
            'order_by' => $requests->currentSortName,
            'order_dir' => $requests->currentDir,
            'types' => $requests->selectedType,
            'state' => $requests->selectedState,
        ];

        $result = self::getOperationInformation($filter);
        $paginator = $result['projectItems'];

        return [
            'totalMMEachMonth' => $result['totalMMEachMonth'],
            'paginator' => $paginator->toArray(),
        ];
    }

    /**
     * get Operation Information
     * @param array
     * @return array
     */
    public static function getOperationInformation($filter)
    {
        $returnOfTransformerData = self::transformerData($filter);
        $projectsReportArr = $returnOfTransformerData['projectsReportArr'];
        if ($filter['order_by'] && $filter['order_dir']) {
            $projectsReportArr = collect($projectsReportArr);
            if ($filter['order_dir'] == 'asc') {
                $projectsReportArr = $projectsReportArr->sortBy($filter['order_by']);
            } else {
                $projectsReportArr = $projectsReportArr->sortByDesc($filter['order_by']);
            }
            $projectsReportArr = $projectsReportArr->toArray();
        }
        $paginatorConfig = Config::getPagerDataQuery();
        $slice = array_slice($projectsReportArr, $paginatorConfig['limit'] * ($paginatorConfig['page'] - 1), $paginatorConfig['limit']);

        return [
            'projectItems' =>new LengthAwarePaginator(
                $slice,
                count($projectsReportArr),
                $paginatorConfig['limit'],
                Paginator::resolveCurrentPage(),
                ['path' => $filter['url']]
            ),
            'totalMMEachMonth' => $returnOfTransformerData['totalMMEachMonth'],
        ];
    }

    /**
     * transformer data
     * @param startMont, endMont
     */
    public static function transformerData($filter)
    {
        $projectsReportArr = [];
        $rangeSelectedMonth = self::initContinuousMonth($filter);
        $items = OperationProject::getDataSQL($filter);
        $states = Project::lablelState() + [ProjectAdditional::STATE_FUTURE => 'Future'];
        $previousItem = null;
        $initialItem = [];
        $totalMMEachMonth = [];
        foreach ($items as $item) {
            if ($previousItem) {
                if ($previousItem->name == $item->name && $previousItem->type == $item->type && $previousItem->team_name == $item->team_name) {
                    foreach ($initialItem['months'] as $key => $value) {
                        if ($value['month'] == $item->yearMonth) {
                            if (!isset($totalMMEachMonth[$value['month']])) {
                                $totalMMEachMonth[$value['month']] = 0;
                            }
                            $initialItem['months'][$key] = [
                                'month' => $value['month'],
                                'id' => $item->id,
                                'cost' =>$item->cost
                            ];
                            $totalMMEachMonth[$value['month']] += $item->cost;
                        }
                    }
                    $previousItem = $item;
                    continue;
                } else {
                    $projectsReportArr[] = $initialItem;
                }
            }
            $initialItem = [
                'id' => $item->id,
                'project_id' => $item->project_id,
                'cost_approved_production' => $item->cost_approved_production,
                'company_name' => $item->company_name,
                'type_mm' => Project::arrayTypeMM()[$item->type_mm],
                'name' => $item->name,
                'type' => Project::labelChartTypeProject()[$item->type],
                'type_id' => $item->type,
                'team' => $item->team_name,
                'team_id' => $item->team_id,
                'status' => $states[$item->state],
                'start_date' => $item->start_data,
                'end_date' => $item->end_data,
                'kind_id' => $item->kind_id,
            ];

            $initialItem = array_merge($initialItem, $rangeSelectedMonth);
            foreach ($initialItem['months'] as $key => $value) {
                if ($value['month'] == $item->yearMonth) {
                    $initialItem['months'][$key] = [
                        'month' => $value['month'],
                        'id' => $item->id,
                        'cost' =>$item->cost
                    ];
                    if (!isset($totalMMEachMonth[$value['month']])) {
                        $totalMMEachMonth[$value['month']] = 0;
                    }
                    $totalMMEachMonth[$value['month']] += $item->cost;
                }
            }
            $previousItem = $item;
        }
        $projectsReportArr[] = $initialItem;

        return [
            'totalMMEachMonth' => $totalMMEachMonth,
            'projectsReportArr' => $projectsReportArr,
        ];
    }

    /**
     * Array Month
     * @param $filter
     *
     * @return mixed
     */
    public static function initContinuousMonth($filter)
    {
        $monthFrom = Carbon::parse($filter['monthFrom']);
        $monthTo = Carbon::parse($filter['monthTo']);
        $data = [];
        while ($monthFrom <= $monthTo) {
            $moth = [
                'month' => $monthFrom->format('Y-m')
            ];
            $data[$monthFrom->format('Y-m')] = $moth;
            $monthFrom->addMonth(1);
        }
        $result['months'] = $data;

        return $result;
    }
    /**
     * Array Month
     * @param $filter
     *
     * @return mixed
     */
    public static function initContinuousMonthV2($filter)
    {
        $monthFrom = Carbon::parse($filter['monthFrom']);
        $monthTo = Carbon::parse($filter['monthTo']);
        $data = [];
        while ($monthFrom <= $monthTo) {
            $data[] = $monthFrom->format('Y-m');
            $monthFrom->addMonth(1);
        }

        return $data;
    }

    /**
     * Delete project addition
     * @param $request
     *
     * @return mixed
     */
    public static function deleteProjectAddition($request)
    {
        return ProjectAdditional::deleteProjectAdditional($request->id);
    }

    /**
     * create project addition
     * @param $request
     *
     * @return mixed
     */
    public static function createProject($request, $isFlagCreate = false)
    {
        DB::beginTransaction();
            if (isset($request->nameOld) && isset($request->typeOld)) {
                ProjectAdditional::where([
                    ['name', $request->nameOld],
                    ['type', $request->typeOld],
                ])->delete();
            }
            if($isFlagCreate) {
                $insertArray = [] ;
                foreach ($request->detail as $value) {
                    $insertArray[] = [
                        'name' => $request->name,
                        'type' => $request->type,
                        'kind_id' => $request->kindId,
                        'team_id' => $value['teamId'],
                        'approved_production_cost' => $value['costApprovedProduction'],
                        'month' => explode('-', $value['month'])[1],
                        'year' => explode('-', $value['month'])[0],
                        'note' =>isset($value['approveCostNote']) ? $value['approveCostNote'] : null,
                        'price' => $value['price'],
                        'unit_price' => $value['unitPriceSelected'],
                    ];
                    if(isset($value['projectChild'])) {
                        foreach ($value['projectChild'] as $item) {
                            $insertArray[] = [
                                'name' => $request->name,
                                'type' => $request->type,
                                'kind_id' => $request->kindId,
                                'team_id' => $item['teamId'],
                                'approved_production_cost' => $item['costApprovedProduction'],
                                'month' => explode('-', $value['month'])[1],
                                'year' => explode('-', $value['month'])[0],
                                'note' =>isset($item['approveCostNote']) ? $item['approveCostNote'] : null,
                                'price' => $item['price'],
                                'unit_price' => $item['unitPriceSelected'],
                            ];
                        }
                    }
                }

                ProjectAdditional::insert($insertArray);
            }
        DB::commit();

        return true;
    }

    public static function getPorjectFuture($request) {
        $data = ProjectAdditional::getProjectFutureDetail($request);
        $dataUsingForView = ProjectAdditional::renderDataToDisplayView($data);

        return $dataUsingForView;
    }

    public static function updateProjectCost($request) {
        if ($request->isFuture == 1) {
            $response = self::updateProjectAdditional($request);
        } else {
            $response = self::updatePointProjectApprovedCost($request);
        }

        return $response;
    }

    public static function updateProjectAdditional($attribute)
    {
        $item = ProjectAdditional::find($attribute->id);
        $item->approved_production_cost = $attribute->approvedProjectCost;
        $item->save();

        return true;
    }

    /**
     * update project
     */
    public static function updatePointProjectApprovedCost($request)
    {
        DB::beginTransaction();
        if ($request->id != "") {
            $proCost = ProjectApprovedProductionCost::find($request->id);
            $proCost->approved_production_cost = $request->approvedProjectCost;
        } else {
            $proCost = new ProjectApprovedProductionCost;
            $proCost->project_id = $request->projectId;
            $proCost->approved_production_cost = $request->approvedProjectCost;
            $proCost->month = explode('-', $request->month)[1];
            $proCost->year = explode('-', $request->month)[0];
            $proCost->team_id = $request->teamId;
        }
        $proCost->save();
        DB::commit();

        return true;
    }

    public static function genDataTeam(&$options, $teamList, $parentId = null, $char = '', $hasPrefix = true)
    {
        if (empty($teamList)) {
            return;
        }

        foreach ($teamList as $key => $team) {
            if ($team['parent_id'] == $parentId) {
                $optionItem = [
                    'label' => $char . $team['name'],
                    'value' => $team['id'],
                    'leader_id' => $team['leader_id'],
                    'is_soft_dev' => $team['is_soft_dev'],
                    'code' => $team['code'],
                    'parent_id' => $team['parent_id'],
                ];

                if (!$hasPrefix) {
                    $optionItem['label'] = $team->name;
                    $optionItem['prefix'] = $char;
                }
                $options[] = $optionItem;
                unset($teamList[$key]);
                static::genDataTeam($options, $teamList, $team['id'], $char . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $hasPrefix);
            }
        }
    }

    public static function getListOption($ownerTeamIds, $request, $employee, $scope)
    {
        $options = [];
        $team = false;
        if ($request->team_id) {
            $team = $ownerTeamIds->where('id', (int)$request->team_id)->first();
            if (!$team) return [];
        } else {
            if ($scope == \Rikkei\Team\Model\Permission::SCOPE_TEAM || $scope == \Rikkei\Team\Model\Permission::SCOPE_SELF) {
                $team = static::getTeamIsWorking($employee);
            }
        }

        if ($team) {
            $options[] = [
                'label' => $team['name'],
                'value' => $team['id'],
                'leader_id' => $team['leader_id'],
                'is_soft_dev' => $team['is_soft_dev'],
                'code' => $team['code'],
                'parent_id' => $team['parent_id'],
            ];
            static::genDataTeam($options, $ownerTeamIds, $team['id'], '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', true);

            return $options;
        }

        static::genDataTeam($options, $ownerTeamIds, null, '', true);

        return $options;
    }

    public static function getTeamIsWorking($employee)
    {
        $teamHistoryTbl = EmployeeTeamHistory::getTableName();
        $teamsTbl = Team::getTableName();

        return Team::select(['name', 'teams.id', 'leader_id', 'is_soft_dev', 'code', 'parent_id', 'is_function'])->join($teamHistoryTbl, "{$teamsTbl}.id", '=', "{$teamHistoryTbl}.team_id")
            ->where("{$teamHistoryTbl}.employee_id", $employee->id)
            ->where("{$teamHistoryTbl}.is_working", true)
            ->orderBy('sort_order', 'asc')
            ->first();
    }

    public static function getDetailTotalPointEachMonth($totalMember, $projectCost, $item)
    {
        $arrTeamId = [];
        foreach ($totalMember as $value) {
            if ($value['team_id'] == $item) {
                $arrTeamId[$value['month']] = $value['members'];
            }
        }
        $totalMemeberPointEachMonth = $arrTeamId;
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
        $onstieVN = 0;
        $internal = 0;
        $other = 0;
        foreach ($totalMemeberPointEachMonth as $key => $value) {
            $osdc = (isset($totalProjectPointEachMonth[$key]) && isset($totalProjectPointEachMonth[$key][self::TYPE_OSDC])) ? $totalProjectPointEachMonth[$key][self::TYPE_OSDC]['cost'] : 0;
            $base = (isset($totalProjectPointEachMonth[$key]) && isset($totalProjectPointEachMonth[$key][self::TYPE_BASE])) ? $totalProjectPointEachMonth[$key][self::TYPE_BASE]['cost'] : 0;
            $onsite = (isset($totalProjectPointEachMonth[$key]) && isset($totalProjectPointEachMonth[$key][self::TYPE_ONSITE])) ? $totalProjectPointEachMonth[$key][self::TYPE_ONSITE]['cost'] : 0;

            $offVN = (isset($countProjectKindEachMonth[$key]) && isset($countProjectKindEachMonth[$key][self::KIND_OFFSHORE_VN])) ? $countProjectKindEachMonth[$key][self::KIND_OFFSHORE_VN]['cost'] : 0;
            $offJP = (isset($countProjectKindEachMonth[$key]) && isset($countProjectKindEachMonth[$key][self::KIND_OFFSHORE_JP])) ? $countProjectKindEachMonth[$key][self::KIND_OFFSHORE_JP]['cost'] : 0;
            $offEN = (isset($countProjectKindEachMonth[$key]) && isset($countProjectKindEachMonth[$key][self::KIND_OFFSHORE_EN])) ? $countProjectKindEachMonth[$key][self::KIND_OFFSHORE_EN]['cost'] : 0;
            $onstieJP = (isset($countProjectKindEachMonth[$key]) && isset($countProjectKindEachMonth[$key][self::KIND_ONSITE_JP])) ? $countProjectKindEachMonth[$key][self::KIND_ONSITE_JP]['cost'] : 0;
            $onstieVN = (isset($countProjectKindEachMonth[$key]) && isset($countProjectKindEachMonth[$key][self::KIND_ONSITE_VN])) ? $countProjectKindEachMonth[$key][self::KIND_ONSITE_VN]['cost'] : 0;
            $internal = (isset($countProjectKindEachMonth[$key]) && isset($countProjectKindEachMonth[$key][self::KIND_INTERNAL])) ? $countProjectKindEachMonth[$key][self::KIND_INTERNAL]['cost'] : 0;
            $other = (isset($countProjectKindEachMonth[$key]) && isset($countProjectKindEachMonth[$key][self::KIND_OTHER])) ? $countProjectKindEachMonth[$key][self::KIND_OTHER]['cost'] : 0;

            $result[] = [
                'month' => $key,
                'members' => $totalMemeberPointEachMonth[$key],
                'osdc' => $osdc,
                'base' => $base,
                'onsite' => $onsite,
                'project' => floatval($osdc) + floatval($base) + floatval($onsite),
                'team_id' => $item,
                'offshore_vn' => $offVN,
                'offshore_jp' => $offJP,
                'offshore_en' => $offEN,
                'onsite_jp' => $onstieJP,
                'onsite_vn' => $onstieVN,
                'internal' => $internal,
                'other' => $other,
            ];
        }

        return $result;
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

    public function getOperationReportsTeam($monthStart, $monthEnd, $teamId = 0)
    {
        $objModelOverview = new ModelOverview();
        $operations = $objModelOverview->getOperationReportsTeam($monthStart, $monthEnd, [$teamId]);
        if (!count($operations)) {
            return [];
        }
        $operations = $operations->groupBy('month');
        $operationTeams = [];
        foreach($operations as $month => $values) {
            foreach($values as $item) {
                if(isset($operationTeams[$month])) {
                    $operationTeams[$month]['members'] += $item->members;
                    $operationTeams[$month]['project'] += $item->project;
                    $operationTeams[$month]['osdc'] += $item->osdc;
                    $operationTeams[$month]['base'] += $item->base;
                    $operationTeams[$month]['onsite'] += $item->onsite;
                    $operationTeams[$month]['team_name'][] = $item->team_name;
                } else {
                    $operationTeams[$month] = [
                        'members' => $item->members,
                        'project' => $item->project,
                        'osdc' => $item->osdc,
                        'base' => $item->base,
                        'onsite' => $item->onsite,
                        'low_busy_rate' => false,
                        'team_name' => [$item->team_name],
                    ];
                }
            }
           
        }
        foreach ($operationTeams as $month => $item) {
            $operationTeams[$month]['busy_rate'] = 0;
            if ($item['members']) {
                $operationTeams[$month]['busy_rate'] = round((float)($item['project'] / $item['members']) * 100, 2);
            }
            if ($operationTeams[$month]['busy_rate'] < 50) {
                $operationTeams[$month]['low_busy_rate'] = true;
            }
            $operationTeams[$month]['team_name'] = implode(',', array_unique($operationTeams[$month]['team_name']));
        }
        return $operationTeams;
    }
}
