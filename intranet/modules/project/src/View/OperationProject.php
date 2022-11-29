<?php

namespace Rikkei\Project\View;

use Carbon\Carbon;
use DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjectAdditional;
use Rikkei\Project\Model\ProjectApprovedProductionCost;
use Rikkei\Project\Model\ProjectWOBase;
use Rikkei\Project\Model\ProjQuality;
use Rikkei\Project\Model\TeamProject;
use Rikkei\Sales\Model\Company;
use Rikkei\Sales\Model\Customer;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Config;

class OperationProject
{
    /**
     * flag allow number max leader of a team
     */
    const CODE_BOD = 'bod';

    /**
     * get Operation Information
     * @param array
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
        $items = self::getDataSQL($filter);
        $states = Project::lablelState() + [ProjectAdditional::STATE_FUTURE => 'Future'];
        $previousItem = null;
        $initialItem = [];
        $totalMMEachMonth = [];

        foreach ($items as $item) {
            if ($previousItem) {
                if ($previousItem->name == $item->name && $previousItem->type == $item->type && $previousItem->team_name == $item->team_name) {
                    foreach ($initialItem as $key => $value) {
                        if ($key == $item->yearMonth) {
                            if (!isset($totalMMEachMonth[$key])) {
                                $totalMMEachMonth[$key] = 0;
                            }
                            $initialItem[$key] = [
                                'id' => $item->id,
                                'cost' => $item->cost
                            ];
                            $totalMMEachMonth[$key] += $item->cost;
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
            foreach ($initialItem as $key => $value) {
                if ($key == $item->yearMonth) {
                    $initialItem[$key] = [
                        'id' => $item->id,
                        'cost' =>$item->cost
                    ];
                    if (!isset($totalMMEachMonth[$key])) {
                        $totalMMEachMonth[$key] = 0;
                    }
                    $totalMMEachMonth[$key] += $item->cost;
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
     * get Data SQl
     * @param startMont, endMont
     */
    public static function getDataSQL($filter)
    {
        $projectApprovedProductionCostTable = ProjectApprovedProductionCost::getTableName();
        $teamTable = Team::getTableName();
        $projsTable = Project::getTableName();
        $projectAdditionalTable = ProjectAdditional::getTableName();
        $projectTeamTable = TeamProject::getTableName();
        $projectQualityTable = ProjQuality::getTableName();
        $fieldYearMonthAs = 'yearMonth';
        $fieldCostAs = 'cost';
        $defaultValueFuture = ProjectAdditional::STATE_FUTURE;
        $cusContactTable = Customer::getTableName();
        $companyTable = Company::getTableName();
        $projectStatusApprove = Project::STATUS_APPROVED;

        $teamId = '';
        if ($filter['team_id']) {
            $teamId = $filter['team_id'];
            $team = Team::getTeamPath($withTrashed = true);
            if (isset($team[$teamId]['child'])) { //get all team childs except BOD
                $teamId = '(' . $teamId . ', ' . implode(", ", $team[$teamId]['child']) . ')';
            } else {
                $teamId = '(' . $teamId . ')';
            }
        }
        $conditionForProjTeamWithoutProjApprovedCost = "(select count(*) from {$projectApprovedProductionCostTable} where  {$projectApprovedProductionCostTable}.project_id = {$projsTable}.id "
            . ($teamId ? "and team_id IN {$teamId} )" : ')')
            . " = 0" ;

        $dataProjsTable = DB::table($projsTable)
            ->select(
                DB::raw("'0' AS id"),
                "{$projsTable}.id as project_id",
                "{$teamTable}.id as team_id",
                "{$projsTable}.name",
                "{$projsTable}.type",
                "{$teamTable}.name as team_name",
                DB::raw("0.00 AS cost"),
                DB::raw("'0000-00-00' AS yearMonth"),
                "{$projsTable}.state",
                "{$projsTable}.start_at",
                "{$projsTable}.end_at",
                "{$projsTable}.created_at",
                "{$projsTable}.type_mm",
                "{$projectQualityTable}.cost_approved_production",
                DB::raw("{$companyTable}.company as company_name"),
                "{$projsTable}.kind_id",
                DB::raw("CONCAT({$projsTable}.created_at, '-', {$projsTable}.name) as Order_created_at_with_name")
            )
            ->join($cusContactTable, "{$cusContactTable}.id", '=', "{$projsTable}.cust_contact_id")
            ->join($companyTable, "{$companyTable}.id", '=', "{$cusContactTable}.company_id")
            ->leftJoin($projectQualityTable, "{$projectQualityTable}.project_id", '=', "{$projsTable}.id")
            ->whereIn("{$projectQualityTable}.id", function ($q) use ($projectQualityTable, $projsTable) {
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
            ->join($projectTeamTable, "{$projectTeamTable}.project_id", '=', "{$projsTable}.id")
            ->join($teamTable, "{$teamTable}.id", '=', "{$projectTeamTable}.team_id")
            ->where(function ($query) use ($projsTable, $filter) {
                $query->where(DB::raw("DATE_FORMAT({$projsTable}.start_at, '%Y-%m')"), '<=', $filter['monthTo'])
                    ->where(DB::raw("DATE_FORMAT({$projsTable}.end_at, '%Y-%m')"), '>=', $filter['monthFrom']);
            })
            ->whereNull("{$projsTable}.deleted_at")
//            ->whereRaw(
//                $conditionForProjTeamWithoutProjApprovedCost
//            )
            ->whereRaw("{$projsTable}.status = {$projectStatusApprove}")
            ->whereRaw(
                "{$projsTable}.parent_id IS NULL"
            );

        if ($teamId) {
            $dataProjsTable = $dataProjsTable->whereRaw(
                "{$projectTeamTable}.team_id IN {$teamId}"
            );
        }

        $dataTableProjectCost = DB::table($projectApprovedProductionCostTable)
            ->select(
                "{$projectApprovedProductionCostTable}.id",
                "{$projsTable}.id as project_id",
                "{$projectApprovedProductionCostTable}.team_id",
                "{$projsTable}.name",
                "{$projsTable}.type",
                "{$teamTable}.name as team_name",
                DB::raw("SUM({$projectApprovedProductionCostTable}.approved_production_cost) AS {$fieldCostAs}"),
                DB::raw("CONCAT({$projectApprovedProductionCostTable}.year, '-', LPAD({$projectApprovedProductionCostTable}.month,2,0)) AS {$fieldYearMonthAs}"),
                "{$projsTable}.state",
                DB::raw("coalesce(projs2.start_at, projs.start_at) as start_at"),
                DB::raw("coalesce(projs2.end_at, projs.end_at) as end_at"),
                "{$projsTable}.created_at",
                "{$projsTable}.type_mm",
                "{$projectQualityTable}.cost_approved_production",
                DB::raw("{$companyTable}.company as company_name"),
                "{$projsTable}.kind_id",
                DB::raw("CONCAT({$projsTable}.created_at, '-', {$projsTable}.name) as Order_created_at_with_name")
            )
            ->join($projsTable, "{$projectApprovedProductionCostTable}.project_id", "=", "{$projsTable}.id")
            ->join($cusContactTable, "{$cusContactTable}.id", '=', "{$projsTable}.cust_contact_id")
            ->join($companyTable, "{$companyTable}.id", '=', "{$cusContactTable}.company_id")
            ->leftJoin($projectQualityTable, "{$projectQualityTable}.project_id", '=', "{$projsTable}.id")
            ->whereIn("{$projectQualityTable}.id", function ($q) use ($projectQualityTable, $projsTable) {
                $q->select(DB::raw("max(id)"))->from($projectQualityTable)
                    ->whereNull("{$projectQualityTable}.deleted_at")
                    ->where(function ($where) use ($projectQualityTable) {
                        $where->where(function ($where) use ($projectQualityTable) {
                            $where->where("{$projectQualityTable}.status", '!=', ProjectWOBase::STATUS_APPROVED)
                                ->whereNull('billable_effort')
                                ->whereNull('plan_effort');
                        })->orWhere("{$projectQualityTable}.status", '=', ProjectWOBase::STATUS_APPROVED);
                    })
                    ->groupBy("{$projectQualityTable}.project_id");
            })
            ->leftJoin("{$projsTable} AS projs2", "{$projsTable}.id", "=", "projs2.parent_id")
            ->join($teamTable, "{$projectApprovedProductionCostTable}.team_id", "=", "{$teamTable}.id")
            ->whereRaw(
                "CONCAT({$projectApprovedProductionCostTable}.year , '-', LPAD({$projectApprovedProductionCostTable}.month,2,0)) BETWEEN '{$filter['monthFrom']}' AND '{$filter['monthTo']}'"
            )
            ->whereRaw("{$projsTable}.status = {$projectStatusApprove}")
            ->whereNull("{$projectApprovedProductionCostTable}.deleted_at")
            ->whereNull("{$projsTable}.deleted_at");

        if ($teamId) {
            $dataTableProjectCost = $dataTableProjectCost->whereRaw(
                "{$projectApprovedProductionCostTable}.team_id IN {$teamId}"
            );
        }

        $dataTableProjectCost = $dataTableProjectCost->groupBy(DB::raw("{$projsTable}.name, {$projectApprovedProductionCostTable}.team_id, {$fieldYearMonthAs}"));

        $dataTableProjectAdditional = DB::table($projectAdditionalTable)
            ->select(
                "{$projectAdditionalTable}.id",
                DB::raw("'0' AS project_id"),
                "{$projectAdditionalTable}.team_id",
                "{$projectAdditionalTable}.name",
                "{$projectAdditionalTable}.type",
                "{$teamTable}.name as team_name",
                DB::raw("SUM({$projectAdditionalTable}.approved_production_cost) AS {$fieldCostAs}"),
                DB::raw("CONCAT({$projectAdditionalTable}.year, '-', LPAD({$projectAdditionalTable}.month,2,0)) AS {$fieldYearMonthAs}"),
                DB::raw("{$defaultValueFuture} AS state"),
                DB::raw("'0000-00-00' AS start_data"),
                DB::raw("'0000-00-00' AS end_data"),
                "{$projectAdditionalTable}.created_at",
                DB::raw("1 AS type_mm"),
                DB::raw("'0' AS cost_approved_production"),
                DB::raw("'' as company_name"),
                "{$projectAdditionalTable}.kind_id",
                DB::raw("CONCAT(coalesce({$projectAdditionalTable}.created_at, ''), '-', {$projectAdditionalTable}.name) as Order_created_at_with_name")
            )
            ->join($teamTable, "{$projectAdditionalTable}.team_id", "=", "{$teamTable}.id")
            ->whereRaw(
                "CONCAT({$projectAdditionalTable}.year, '-', LPAD({$projectAdditionalTable}.month,2,0)) BETWEEN '{$filter['monthFrom']}' AND '{$filter['monthTo']}'"
            )
            ->whereNull("{$projectAdditionalTable}.deleted_at");

        if ($teamId) {
            $dataTableProjectAdditional = $dataTableProjectAdditional->whereRaw(
                "{$projectAdditionalTable}.team_id IN {$teamId}"
            );
        }

        $query = $dataTableProjectAdditional->groupBy(DB::raw("{$projectAdditionalTable}.name, {$projectAdditionalTable}.team_id, {$fieldYearMonthAs}"))
            ->union($dataTableProjectCost)->union($dataProjsTable);

        $querySql = $query->toSql();
        $query = DB::table(DB::raw("($querySql) as a"))->mergeBindings($query);


        if ($filter['types']) {
            $types = '(' . implode(',', array_map('intval', $filter['types'])) . ')';
            $query = $query->whereRaw("kind_id IN {$types}");
        }
        if ($filter['state']) {
            $states = '(' . implode(',', array_map('intval', $filter['state'])) . ')';
            $query = $query->whereRaw("state IN {$states}");
        }
        $query->orderByRaw("state desc, Order_created_at_with_name asc, type asc, team_name asc");

        return $query->get();
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
            $data[$monthFrom->format('Y-m')] = '';
            $monthFrom->addMonth(1);
        }

        return $data;
    }
}
