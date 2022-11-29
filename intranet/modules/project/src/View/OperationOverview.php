<?php

namespace Rikkei\Project\View;

use Illuminate\Support\Facades\DB;
use Rikkei\Api\Helper\Operation;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjectWOBase;
use Rikkei\Project\Model\ProjQuality;
use Rikkei\Team\Model\Team;

class OperationOverview
{
    /**
     * get project cost month
     *
     * @param  array $items
     *
     * @return array
     */
    public static function getProjectCostPerMonth($items)
    {
        $resultsGridData = [];
        foreach ($items as $item) {
            $yearMonth = $item->yearMonth;
            if (!array_key_exists($yearMonth, $resultsGridData)) {
                $resultsGridData[$yearMonth] = [
                    'project' => 0,
                    'osdc' => 0
                ];
            }
            if ($item->type == Project::TYPE_OSDC) {
                $resultsGridData[$yearMonth]['osdc'] += $item->cost;
            }
            $resultsGridData[$yearMonth]['project'] += $item->cost;
        }

        return $resultsGridData;
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
                   DB::raw("sum({$projectApprovedProductionCostTable}.approved_production_cost) as cost")
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
            ->groupBy('type');

        $projectFutureCost = DB::table($projectAdditionalTable)
            ->select(
                "type",
                DB::raw("{$concatMonthYearProjAdditional} as {$yearMonthAs}"),
                DB::raw("sum({$projectAdditionalTable}.approved_production_cost) as cost")
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
            ->groupBy('type');

        $query = $projectFutureCost->union($projectCost);
        $querySql = $query->toSql();
        $query = DB::table(DB::raw("($querySql) as a"))
            ->select(
                'type',
                $yearMonthAs,
                DB::raw("sum(cost) as cost")
            )
            ->groupBy($yearMonthAs)
            ->groupBy('type')
            ->mergeBindings($query);

        return collect($query->get())->groupBy($yearMonthAs, true)->map(function ($pb) { return $pb->keyBy('type'); })->toArray();
    }

    /**
     * get project grid data
     *
     * @param  array $filter
     *
     * @return array
     */
    public static function getTeamProjectCost($teamIds)
    {
        $arrNew = [];
        $currentIds = [];
        $teamConditions = [
            'is_soft_dev' => Team::IS_SOFT_DEVELOPMENT
        ];
        $teamIsDevs = Team::getTeamList($teamConditions, ['id', 'name'])->toArray();

        foreach ($teamIsDevs as $teamIsDev) {
            if (in_array($teamIsDev["id"], $teamIds)) {
                $currentIds[] = [
                    "id" => $teamIsDev["id"],
                    "name" => $teamIsDev["name"]
                ];
            }
        }
        foreach ($currentIds as $value) {
            $arrNew[str_replace('-', '_', str_slug($value['name']))] = $value ['id']. "";
        }

        $teamIsbranh = Team::select('id', 'name')
            ->whereIn('code', [Team::CODE_PREFIX_DN, Team::CODE_PREFIX_JP])
            ->get()->toArray();

        foreach ($teamIsbranh as $value) {
            if (!array_key_exists($value['name'], $arrNew)) {
                $arrNew[str_replace('-', '_', str_slug($value['name']))] = $value ['id']. "";
            }
        }

        // Them team id BOD  = 1
        $arrNew["rikkei_soft"] = "1";

        return $arrNew;
    }

    public static function getProjectGridDataApi($filter)
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
                    ->groupBy("{$projectQualityTable}.project_id");
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
        if (!$filter['is_internal_project']) {
            $projectCost->where(function ($query) {
                $query->where('kind_id', '!=', Operation::KIND_INTERNAL)
                    ->orWhereNull('kind_id');
            });
        }
        $projectCost = $projectCost->whereBetween(DB::raw("str_to_date(CONCAT({$concatMonthYear}, '-01'), '%Y-%m-%d')"), [$from, $to])
            ->groupBy($yearMonthAs)
            ->groupBy('type');

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
        if (!$filter['is_internal_project']) {
            $projectFutureCost->where(function ($query) {
                $query->where('kind_id', '!=', Operation::KIND_INTERNAL)
                    ->orWhereNull('kind_id');
            });
        }
        $projectFutureCost = $projectFutureCost->whereBetween(DB::raw("str_to_date(CONCAT({$concatMonthYearProjAdditional}, '-01'), '%Y-%m-%d')"), [$from, $to])
            ->groupBy($yearMonthAs)
            ->groupBy('type')
            ->groupBy('kind');

        $query = $projectFutureCost->union($projectCost);

        $querySql = $query->toSql();
        $query2 = DB::table(DB::raw("($querySql) as a"))
            ->select(
                'type',
                $yearMonthAs,
                DB::raw("sum(cost) as cost"),
                'kind'
            );


        $query2 = $query2->groupBy($yearMonthAs)
            ->groupBy('type')
            ->groupBy('kind')
            ->mergeBindings($query);

        return collect($query2->get())->groupBy($yearMonthAs, true)->toArray();
    }
}
