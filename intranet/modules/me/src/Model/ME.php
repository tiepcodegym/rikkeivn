<?php

namespace Rikkei\Me\Model;

use Rikkei\Project\Model\MeEvaluation;
use Rikkei\Team\View\Permission;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Project\Model\Project;
use Rikkei\Team\Model\Employee;
use Rikkei\Me\Model\Comment as MeComment;
use Rikkei\Me\Model\History as MeHistory;
use Rikkei\Me\Model\Point as MePoint;
use Rikkei\Team\Model\Team;
use Rikkei\ManageTime\Model\TimekeepingAggregate;
use Rikkei\ManageTime\Model\TimekeepingTable;
use Rikkei\Project\Model\TeamProject;
use Rikkei\Me\View\View as MeView;
use Rikkei\Team\View\TeamList;
use Rikkei\Core\View\Form;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ME extends MeEvaluation
{
    private static $instance = null;

    const MAX_POINT_NEW = 10;

    /**
     * get instance of this class
     * @return object
     */
    public static function getInstance()
    {
        if (self::$instance !== null) {
            return self::$instance;
        }
        self::$instance = new self();
        return self::$instance;
    }

    /**
     * search projects of PM
     * @param integer $managerId
     * @param string $search
     * @param array $config
     * @return array
     */
    public function getProjectsOfPM($managerId = null, $search = '', $config = [])
    {
        $arrayDefault = [
            'page' => 1,
            'limit' => 20,
        ];
        $config = array_merge($arrayDefault, $config);
        $tblProj = Project::getTableName();

        $projects = parent::getProjectsOfCurrentManager($managerId, true);
        $projects->select($tblProj.'.id', $tblProj.'.name as text', DB::raw('1 as loading'));

        if ($search) {
            $projects->where($tblProj . '.name', 'like', '%' . $search . '%');
        }

        self::pagerCollection($projects, $config['limit'], $config['page']);

        return [
            'incomplete_results' => true,
            'items' => $projects->items(),
            'total_count' => $projects->total()
        ];
    }

    /**
     * list array months of project
     */
    public function listMonthsOfProject($projId)
    {
        return parent::listProjectMonths($projId);
    }

    /**
     * list member in project to evaluate
     */
    public function getMembersOfProject($project, $time)
    {
        if (!$time instanceof Carbon) {
            $time = Carbon::parse($time);
        }
        $empTbl = Employee::getTableName();
        $pjmTbl = ProjectMember::getTableName();
        $rangeTime = MeView::getBaselineRangeTime($time);

        $collection = Employee::join($pjmTbl . ' as pjm', $empTbl . '.id', '=', 'pjm.employee_id')
            ->join(Project::getTableName() . ' as proj', function ($join) {
                $join->on('proj.id', '=', 'pjm.project_id')
                        ->whereNull('proj.deleted_at');
            })
            ->where('pjm.project_id', $project->id)
            ->whereDate('pjm.start_at', '<=', $rangeTime['end']->toDateString())
            ->whereDate('pjm.end_at', '>=', $rangeTime['start']->toDateString())
            ->whereNotIn('pjm.type', [ProjectMember::TYPE_PQA, ProjectMember::TYPE_COO])
            ->where('pjm.status', ProjectMember::STATUS_APPROVED)
            ->where(function ($query) use ($empTbl, $time) {
                $query->whereNull($empTbl.'.leave_date')
                    ->orWhereDate($empTbl.'.leave_date', '>=', $time->startOfMonth()->toDateString());
            });

        //join timekeepingsheet
        $prevTime = clone $time;
        //$prevTime->subMonthNoOverflow();
        $collection->leftJoin(
            DB::raw('(SELECT tkavg.employee_id, SUM(tkavg.total_number_late_in) as late_time '
                . 'FROM ' . TimekeepingAggregate::getTableName() . ' as tkavg '
                . 'INNER JOIN ' . TimekeepingTable::getTableName() . ' as tktbl ON tkavg.timekeeping_table_id = tktbl.id '
                . 'WHERE tktbl.month = ' . $prevTime->month . ' AND tktbl.year = ' . $prevTime->year . ' '
                . 'AND tktbl.deleted_at IS NULL AND tkavg.deleted_at IS NULL '
                . 'GROUP BY tkavg.employee_id) as tkp'),
            $empTbl . '.id',
            '=',
            'tkp.employee_id'
        );

        $collection->select(
            $empTbl . '.id as employee_id',
            $empTbl . '.employee_card_id',
            $empTbl . '.employee_code',
            'pjm.effort',
            $empTbl . '.name',
            $empTbl . '.email',
            'pjm.start_at',
            'pjm.end_at',
            'proj.name as proj_name',
            DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(pjm.id, "|", pjm.start_at)) SEPARATOR ",") AS arr_start_at'),
            DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(pjm.id, "|", pjm.end_at)) SEPARATOR ",") AS arr_end_at'),
            DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(pjm.id, "|", pjm.effort)) SEPARATOR ",") AS arr_effort'),
            'tkp.late_time'
        )
        ->groupBy($empTbl.'.id')
        ->orderBy('pjm.start_at', 'asc');

        return [
            'members' => $collection->get(),
            'range_time' => $rangeTime
        ];
    }

    /**
     * insert or update ME project item
     * @param collection $projMembers
     * @param object $project
     * @param string|object $time
     * @return boolean
     */
    public function insertOrUpdateProjMembers($projMembers, $project, $time)
    {
        if ($projMembers->isEmpty()) {
            return false;
        }
        if (!$time instanceof Carbon) {
            $time = Carbon::parse($time);
        }
        $currUserId = auth()->id();
        $projId = $project->id;
        $projectPoint = $this->getProjPointLastMonth($projId, $time);
        $employeeIds = $projMembers->pluck('employee_id')->toArray();
        $existsMeItems = self::whereIn('employee_id', $employeeIds)
                ->where('project_id', $projId)
                ->where(DB::raw('DATE_FORMAT(eval_time, "%Y-%m")'), $time->format('Y-m'))
                ->get()
                ->groupBy('employee_id');

        $timeDate = $time->toDateString();
        $items = [];
        $meValidIds = [];
        DB::beginTransaction();
        try {
            foreach ($projMembers as $member) {
                $workDates = [];
                $arrStartAt = explode(',', $member->arr_start_at);
                $arrEndAt = explode(',', $member->arr_end_at);
                $arrEffort = explode(',', $member->arr_effort);
                $length = count($arrStartAt);

                for ($i = 0; $i < $length; $i++) {
                    array_push($workDates, [
                        explode('|', $arrStartAt[$i])[1],
                        explode('|', $arrEndAt[$i])[1],
                        explode('|', $arrEffort[$i])[1]
                    ]);
                }
                $item = isset($existsMeItems[$member->employee_id]) ? $existsMeItems[$member->employee_id][0] : null;
                $updateData = [
                    'proj_point' => $projectPoint,
                    'proj_index' => self::factorOfProjType($projId, $project->type),
                    'effort' => self::calMemberDaysInMonth($workDates, $time),
                ];
                if (!$item) {
                    $evalData = [
                        'employee_id' => $member->employee_id,
                        'project_id' => $projId,
                        'eval_time' => $timeDate,
                        'proj_point' => $projectPoint,
                        'avg_point' => static::defaultAvgPoint(),
                        'manager_id' => $currUserId,
                        'assignee' => $currUserId,
                        'status' => self::STT_DRAFT,
                    ];
                    $item = self::create(array_merge($evalData, $updateData));
                } else {
                    $item->update($updateData);
                }
                //insert or update activities comment
                MeComment::insertActivities($item);
                MeComment::getInstance()->insertLateTime($item, $member->late_time);

                $item->proj_name = $member->proj_name;
                $item->emp_card_id = $member->employee_card_id;
                $item->emp_code = $member->employee_code;
                $item->emp_name = $member->name;
                $item->emp_email = $member->email;
                $item->can_change_point = $item->canChangePoint();
                $items[] = $item;
                $meValidIds[] = $item->id;
            }
            $this->removeInvalidItems($projId, $timeDate, $meValidIds);

            DB::commit();
            return $items;
        } catch (\Excpetion $ex) {
            DB::rollback();
            \Log::info($ex);
            return false;
        }
    }

    /**
     * get last project point of project in month
     * @param object $project
     * @param string $time
     * @return integer
     */
    public function getProjPointLastMonth($project, $time)
    {
        return parent::getProjectPointLastMonth($project, $time);
    }

    /**
     * insert or update ME item
     * @param object $member project member
     * @param object $project
     * @param string $time evaluate month
     * @param object $currUser
     * @param integer $projectPoint
     * @param bool $existsMonth
     * @return object
     */
    public function insertOrUpdate($member, $project, $time, $currUser, $projectPoint, $existsMonth)
    {
        return parent::createOrFindItem($member, $project, $time, $currUser, $projectPoint, $existsMonth, false);
    }

    public static function defaultAvgPoint()
    {
        return 0;
    }

    /**
     * remove invalid ME evaluation data
     * @param int $projectId
     * @param string $time
     * @param array $meValidIds
     * @return void
     */
    public function removeInvalidItems($projectId, $time, $meValidIds, $type = 'project')
    {
        return parent::delInvalidItems($projectId, $time, $meValidIds, $type);
    }

    /*
     * save ME list point
     */
    public function savePoints($evalPoints)
    {
        $items = [];
        foreach ($evalPoints as $evalId => $dataEval) {
            $sumaryPoint = $dataEval['sumary'] ? $dataEval['sumary'] : null;
            $attrPoints = $dataEval['attr_points'];
            if (count($attrPoints) < 1) {
                continue;
            }
            $item = self::find($evalId);
            if (!$item) {
                continue;
            }
            //save sumary point
            if ($sumaryPoint) {
                $item->avg_point = $sumaryPoint;
                $item->save();
            }
            //add history feedback
            if ($item->status == self::STT_FEEDBACK) {
                MeHistory::create([
                    'eval_id' => $item->id,
                    'employee_id' => auth()->id(),
                    'version' => $item->version,
                    'action_type' => MeHistory::AC_CHANGE_POINT,
                    'type_id' => $item->id
                ]);
            }
            $this->saveAttrPoint($item, $attrPoints);
            $items[] = $item;
        }
        return $items;
    }

    /**
     * save attribute item point
     */
    public function saveAttrPoint($evalItem, $attrPoints)
    {
        if (!$evalItem->canChangePoint()) {
            return false;
        }

        foreach ($attrPoints as $attrId => $point) {
            MePoint::getInstance()->savePoint($evalItem->id, $attrId, $point);
        }
    }

    /*
     * get reviews items
     */
    public function getReviewItems($data = [], $collectType = 'list')
    {
        $collection = self::collectByLeader($data['filter']);
        return $this->collectReviewItems($collection, $data, $collectType);
    }

    /*
     * list review item by leader
     */
    public function collectReviewItems($collection, $data = [], $collectType = 'list')
    {
        $evalTbl = self::getTableName();
        $opts = [
            'page' => 1,
            'per_page' => 50,
            'orderby' => [
                'eval_time' => 'desc',
                'project_id' => 'desc',
            ],
            'filter' => [],
        ];
        $data = array_merge($opts, $data);
        $dataFilter = $data['filter'];
        $sepMonth = config("project.me_sep_month");
        if ($sepMonth) {
            $collection->where(DB::raw('DATE_FORMAT(' . $evalTbl . '.eval_time, "%Y-%m")'), '>', $sepMonth);
        }

        //filter month
        $filterMonth = Form::getFilterData('excerpt', 'month', $dataFilter);
        if ($filterMonth) {
            $collection->where(DB::raw('DATE_FORMAT('. $evalTbl . '.eval_time, "%Y-%m")'), $filterMonth);
        }

        // if isset param project_id
        $projId = Form::getFilterData('excerpt', 'project_id', $dataFilter);
        if ($projId) {
            if (is_numeric($projId)) {
                $collection->where($evalTbl.'.project_id', $projId);
            } else {
                //filter team: team_<id>
                $arrIds = explode('_', $projId);
                if (count($arrIds) == 2) {
                    $collection->where($evalTbl.'.team_id', $arrIds[1]);
                }
            }
        }
        //collect total evaluated
        if ($collectType == 'totalEvaluated') {
            $collectEvaluated = $collection->select(
                    DB::raw('CONCAT(' . $evalTbl . '.employee_id, "|", '
                            . 'COUNT(DISTINCT('. $evalTbl .'.project_id))) as emp_count_proj')
                )
                ->whereNotNull($evalTbl.'.project_id')
                ->groupBy($evalTbl.'.employee_id')
                ->pluck('emp_count_proj')
                ->toArray();
            $collectNeedEval = $this->collectNeedEval(null, $dataFilter)
                ->pluck('emp_count_proj')
                ->toArray();
            //except employee join multi-projects not evaluate yet
            $realEvaluated = array_intersect($collectEvaluated, $collectNeedEval);
            return count($realEvaluated) . '/' . count($collectNeedEval);
        }

        // filter avg_point
        $avgRange = Form::getFilterData('excerpt', 'avg_point', $dataFilter);
        $arrAvg = explode('-', $avgRange);
        if (count($arrAvg) > 1) {
            $collection->where($evalTbl.'.avg_point', '>=', $arrAvg[0])
                            ->where($evalTbl.'.avg_point', '<', $arrAvg[1]);
        }
        //filter project type
        $projectType = Form::getFilterData('excerpt', 'proj_type', $dataFilter);
        if ($projectType) {
            if ($projectType == '_team_') {
                $collection->whereNull($evalTbl.'.project_id');
            } else {
                $collection->where('proj.type', $projectType);
            }
        }
        self::filterGrid($collection, [], $dataFilter);

        //return statistic, not paginate
        if ($collectType == 'statistic') {
            $collection->select($evalTbl.'.id', $evalTbl.'.avg_point')
                                ->groupBy($evalTbl.'.id');
            $collection = DB::table(DB::raw("({$collection->toSql()}) as $evalTbl"))		        
                            ->mergeBindings($collection->getQuery());
            //count range point
            $collection->select(
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN avg_point >= '. MeView::TYPE_S .' THEN 1 ELSE 0 END) as count_s'),
                DB::raw('SUM(CASE WHEN avg_point >= '. MeView::TYPE_A .' AND avg_point < '. MeView::TYPE_S .' THEN 1 ELSE 0 END) as count_a'),
                DB::raw('SUM(CASE WHEN avg_point >= '. MeView::TYPE_B .' AND avg_point < '. MeView::TYPE_A .' THEN 1 ELSE 0 END) as count_b'),
                DB::raw('SUM(CASE WHEN avg_point >= '. MeView::TYPE_C .' AND avg_point < '. MeView::TYPE_B .' THEN 1 ELSE 0 END) as count_c')
            );
            $summary = $collection->first();
            return $summary;
        }
        //get list per page
        $collection->select(
                $evalTbl . '.id',
                $evalTbl . '.employee_id',
                $evalTbl . '.project_id',
                $evalTbl . '.team_id',
                DB::raw('DATE(' . $evalTbl . '.eval_time) as eval_time'),
                $evalTbl . '.proj_point',
                $evalTbl . '.proj_index',
                $evalTbl . '.effort',
                $evalTbl . '.avg_point',
                $evalTbl . '.status',
                $evalTbl . '.created_at',
                $evalTbl . '.is_leader_updated',
                DB::raw('DATE_FORMAT('. $evalTbl . '.eval_time, "%Y-%m") as eval_month'),
                'proj.project_code_auto',
                'proj.name as project_name',
                'proj.type as project_type',
                'emp.email',
                'emp.employee_code',
                'team.name as team_name',
                DB::raw('DATE_FORMAT('.$evalTbl.'.eval_time, "%Y-%m") as eval_month')
            )
            ->groupBy($evalTbl.'.id');

        if (count($data['orderby']) > 0) {
            foreach ($data['orderby'] as $orderby => $dir) {
                $collection->orderBy($orderby, $dir);
            }
        } else {
            $collection->orderBy('eval_time', 'desc')
                    ->orderBy('project_id', 'desc');
        }

        return $collection->paginate($data['per_page'], ['*'], 'page', $data['page']);
    }

    /**
     * get list employee need evaluate
     * 
     * @param string $scopeRoute route permission
     * @param array $dataFilter filter params
     * @return collection
     */
    public function collectNeedEval(
        $scopeRoute = 'me::review.list',
        $dataFilter = [],
        $collectType = 'employee'
    )
    {
        $filterMonth = Form::getFilterData('excerpt', 'month', $dataFilter);
        $scope = Permission::getInstance();

        $projTbl = Project::getTableName();
        $projMbTbl = ProjectMember::getTableName();
        $teamProjTbl = TeamProject::getTableName();
        $teamMbTbl = TeamMember::getTableName();
        $empTbl = Employee::getTableName();
        $teamTbl = Team::getTableName();

        if (!$filterMonth) {
            return collect();
        }

        $filterMonth = MeView::parseDateFromFormat($filterMonth, 'Y-m');
        $rangeTime = MeView::getBaselineRangeTime($filterMonth);
        $timeTwoMonthsPrevious = clone $filterMonth;
        $timeTwoMonthsPrevious->subMonth(2);

        //cal all member join project
        $collection = ProjectMember::from($projMbTbl . ' as pjm')
            ->join($projTbl.' as proj', function ($join) {
                $join->on('pjm.project_id', '=', 'proj.id');
            })
            ->join($empTbl . ' as emp', 'pjm.employee_id', '=', 'emp.id')
            ->whereNull('emp.deleted_at')
            ->where('pjm.status', ProjectMember::STATUS_APPROVED)
            ->where('pjm.is_disabled', '!=', ProjectMember::STATUS_DISABLED)
            ->whereNotIn('pjm.type', [ProjectMember::TYPE_PQA, ProjectMember::TYPE_COO])
            //project processing or closed 2 months lately
            ->where(function ($query) use ($timeTwoMonthsPrevious) {
                $query->where('proj.state', Project::STATE_CLOSED)
                    ->where('proj.end_at', '>=', $timeTwoMonthsPrevious->toDateString())
                    ->orWhere('proj.state', Project::STATE_PROCESSING);
            })
            ->where('pjm.start_at', '<=', $rangeTime['end'])
            ->where('pjm.end_at', '>=', $rangeTime['start']);

        //fillter project
        $projectId = Form::getFilterData('excerpt', 'project_id', $dataFilter);
        if ($projectId && is_numeric($projectId)) {
             $collection->where('proj.id', $projectId);
        }

        // filter team project
        $teamFilter = Form::getFilterData('team_filter', 'team_id', $dataFilter);
        if ($teamFilter) {
            $teamFilter = Team::teamChildIds($teamFilter);
            $collection->join($teamProjTbl.' as ft_teamproj', function ($join) use ($teamFilter) {
                    $join->on('proj.id', '=', 'ft_teamproj.project_id')
                        ->whereIn('ft_teamproj.team_id', $teamFilter);
                });
        }
        //filter team member
        $teamMember = Form::getFilterData('team_filter', 'team_member', $dataFilter);
        if ($teamMember) {
            $teamMember = Team::teamChildIds($teamMember);
            $collection->join($teamMbTbl . ' as ft_tmb', function ($join) use ($teamMember) {
                $join->on('pjm.employee_id', '=', 'ft_tmb.employee_id')
                        ->whereIn('ft_tmb.team_id', $teamMember);
            });
        }

        $joinedTeamProj = false;
        if ($scope->isScopeCompany(null, $scopeRoute)) {
            //do nothing
        } elseif ($teamIds = $scope->isScopeTeam(null, $scopeRoute)) {
            $joinedTeamProj = true;
            $teamIds = is_array($teamIds) ? $teamIds : [];
            $collection->leftJoin($teamProjTbl . ' as teamproj', 'proj.id', '=', 'teamproj.project_id')
                ->leftJoin($teamMbTbl . ' as tmb', 'tmb.employee_id', '=', 'pjm.employee_id')
                ->where(function ($query) use ($teamIds, $teamMbTbl) {
                    $query->whereIn('proj.leader_id', function ($subQuery) use ($teamIds, $teamMbTbl) {
                        $subQuery->select('employee_id')
                            ->from($teamMbTbl)
                            ->whereIn('team_id', $teamIds)
                            ->whereIn('role_id', [Team::ROLE_SUB_LEADER, Team::ROLE_TEAM_LEADER]);
                    })
                    ->orWhereIn('tmb.team_id', $teamIds);
            });
        } else {
            //
        }

        if ($collectType == 'employee') {
            $collection->select(DB::raw('CONCAT(pjm.employee_id, "|", COUNT(DISTINCT(pjm.project_id))) as emp_count_proj'))
                ->groupBy('pjm.employee_id');
        } elseif ($collectType == 'notEvaluate') {
            $collection->select(
                    'pjm.employee_id',
                    'pjm.project_id',
                    'emp.name as emp_name',
                    'emp.email as emp_email',
                    'proj.project_code_auto',
                    'proj.name as proj_name',
                    'pm.email as pm_email',
                    DB::raw('GROUP_CONCAT(DISTINCT(group.name) SEPARATOR ", ") as group_names')
                )
                ->leftJoin($empTbl . ' as pm', 'pm.id', '=', 'proj.manager_id')
                ->whereNotIn('pjm.project_id', function ($query) use ($filterMonth) {
                    $query->select('project_id')
                        ->from(self::getTableName())
                        ->whereNotNull('project_id')
                        ->whereNotIn('status', [self::STT_DRAFT, self::STT_REWARD])
                        ->where(DB::raw('DATE_FORMAT(eval_time, "%Y-%m")'), $filterMonth->format('Y-m'))
                        ->groupBy('project_id');
                })
                ->groupBy('pjm.employee_id', 'pjm.project_id');
            if (!$joinedTeamProj) {
                $collection->leftJoin($teamProjTbl . ' as teamproj', 'proj.id', '=', 'teamproj.project_id');
            }
            $collection->leftJoin($teamTbl . ' as group', 'group.id', '=', 'teamproj.team_id');
        } else {
            //
        }

        return $collection->get();
    }

    /*
     * get team list option by scope route permission
     */
    public function getTeamPermissOptions($scopeRoute = '')
    {
        if (Permission::getInstance()->isScopeCompany(null, $scopeRoute)) {
            return TeamList::toOption(null, false, false);
        }
        if (($teamIds = Permission::getInstance()->isScopeTeam(null, $scopeRoute))) {
            $teamIds = is_array($teamIds) ? $teamIds : [];
        } elseif (Permission::getInstance()->isScopeSelf(null, $scopeRoute)) {
            $teamIds = TeamMember::getTeamMembersByEmployees(auth()->id());
        } else {
            return [];
        }
        if (count($teamIds) < 1) {
            return [];
        }
        return Team::select('id as value', 'name as label')
                ->whereIn('id', $teamIds)
                ->get()
                ->toArray();
    }

    /*
     * get project or team
     */
    public function getProjectOrTeam($objectId)
    {
        if (!$objectId) {
            return null;
        }
        if (is_numeric($objectId)) {
            return Project::find($objectId, ['id', 'name']);
        }
        $aryStr = explode('_', $objectId);
        if (count($aryStr) < 2) {
            return null;
        }
        $team = Team::find($aryStr[1], ['id', 'name']);
        if (!$team) {
            return null;
        }
        $team->id = 'Team_' . $team->id;
        return $team;
    }

    public function getTotalEvalMember($dataFilter = [])
    {
        $data = [
            'month' => Form::getFilterData('excerpt', 'month', $dataFilter),
            'project_id' => Form::getFilterData('excerpt', 'project_id', $dataFilter),
        ];
        return self::getTotalMemberOfLeader($data, null, $dataFilter);
    }

    public function getProjsNotEval($dataFilter = [])
    {
        $data = [
            'month' => Form::getFilterData('excerpt', 'month', $dataFilter),
            'project_id' => Form::getFilterData('excerpt', 'project_id', $dataFilter),
        ];
        return self::getProjectNotEval($data, $dataFilter);
    }


    //ME team
    public function getMembersOfEvalTeam($teamId, $time, $excerptEmployees = [])
    {
        return static::getMembersOfTeam($teamId, $time, $excerptEmployees);
    }

    /*
     * insert or update eval team item
     */
    public function insertOrUpdateEvalTeam($members, $team, $time)
    {
        if (!$time instanceof Carbon) {
            $time = Carbon::parse($time);
        }
        $currUserId = auth()->id();
        $teamId = $team->id;
        $employeeIds = collect($members)->pluck('employee_id')->toArray();
        $existsMeItems = self::whereIn('employee_id', $employeeIds)
                ->where('team_id', $teamId)
                ->where(DB::raw('DATE_FORMAT(eval_time, "%Y-%m")'), $time->format('Y-m'))
                ->get()
                ->groupBy('employee_id');

        $timeDate = $time->toDateString();
        $items = [];
        $meValidIds = [];
        DB::beginTransaction();
        try {
            foreach ($members as $member) {
                $limit = 15;
                $arrStartAt = array_slice(explode(',', $member->arr_start_at), 0, $limit);
                $arrEndAt = array_slice(explode(',', $member->arr_end_at), 0, $limit);
                $arrEffort = array_slice(explode(',', $member->arr_effort), 0, $limit);
                $arrProjId = array_slice(explode(',', $member->arr_proj_id), 0, $limit);
                $arrProjType = array_slice(explode(',', $member->arr_proj_type), 0, $limit);
                $arrPointTotal = array_slice(explode(',', $member->arr_point_total), 0, $limit);

                $workDates = [];
                $projPoint = 0;
                $projIndex = null;
                //calculate work date
                if (count($arrStartAt) > 0) {
                    for ($i = 0; $i < count($arrStartAt); $i++) {
                        array_push($workDates, [
                            explode('|', $arrStartAt[$i])[1],
                            explode('|', $arrEndAt[$i])[1],
                            explode('|', $arrEffort[$i])[1]
                        ]);
                    }
                }
                //caculate project averate project point
                $uniqueLength = count($arrProjId);
                if ($uniqueLength > 0) {
                    for ($i = 0; $i < $uniqueLength; $i++) {
                        $type = explode('|', $arrProjType[$i])[1];
                        $pointIndex = self::FT_OSDC;
                        if ($type == self::PJ_BASE) {
                            $pointIndex = self::FT_BASE;
                        }
                        $pointItem = explode('|', $arrPointTotal[$i])[1];
                        if ($type == Project::TYPE_TRAINING) {
                            $pointItem = Project::POINT_PROJECT_TYPE_TRANING;
                        } else if ($type == Project::TYPE_RD) {
                            $pointItem = Project::POINT_PROJECT_TYPE_RD;
                        } else if ($type == Project::TYPE_ONSITE){
                            $pointItem = Project::POINT_PROJECT_TYPE_ONSITE;
                        }
                        $projPoint += $pointItem * $pointIndex;
                    }
                    $projPoint = number_format($projPoint / $uniqueLength, 1, '.', ',');
                }

                $item = isset($existsMeItems[$member->employee_id]) ? $existsMeItems[$member->employee_id][0] : null;
                $updateData = [
                    'proj_point' => $projPoint,
                    'proj_index' => $projIndex,
                    'effort' => self::calMemberDaysInMonth($workDates, $time),
                ];
                if (!$item) {
                    $evalData = [
                        'employee_id' => $member->employee_id,
                        'eval_time' => $time,
                        'team_id' => $member->team_id,
                        'avg_point' => static::defaultAvgPoint(),
                        'manager_id' => $currUserId,
                        'assignee' => $currUserId,
                        'status' => self::STT_DRAFT,
                    ];
                    $item = self::create(array_merge($evalData, $updateData));
                } else {
                    $item->update($updateData);
                }
                //insert or update activities comment
                MeComment::insertActivities($item);
                MeComment::getInstance()->insertLateTime($item, $member->late_time);

                $item->emp_card_id = $member->employee_card_id;
                $item->emp_code = $member->employee_code;
                $item->emp_name = $member->name;
                $item->emp_email = $member->email;
                $item->arr_proj_name = $member->arr_proj_name;
                $item->can_change_point = $item->canChangePoint();
                $items[] = $item;
                $meValidIds[] = $item->id;
            }
            $this->removeInvalidItems($teamId, $timeDate, $meValidIds, 'team');

            DB::commit();
            return $items;
        } catch (\Excpetion $ex) {
            DB::rollback();
            \Log::info($ex);
            return false;
        }
    }

    /*
     * list project of employees
     */
    public function getProjectsOfEmployee($empId = null)
    {
        if (!$empId) {
            $empId = auth()->id();
        }
        return ProjectMember::getProjsOfEmployee($empId, ['proj.id', 'proj.name']);
    }

    public function getTeamsOfEmployee($empId = null)
    {
        if (!$empId) {
            $empId = auth()->id();
        }
        return TeamMember::select('team.id', 'team.name')
            ->from(TeamMember::getTableName() . ' as tmb')
            ->join(Team::getTableName() . ' as team', function ($join) {
                $join->on('team.id', '=', 'tmb.team_id')
                        ->whereNull('team.deleted_at');
            })
            ->groupBy('team.id')
            ->get();
    }

    /*
     * get ME items by current employee
     */
    public function getConfirmItems($data = [])
    {
        $collection = self::collectByStaft(auth()->id());
        return $this->collectConfirmItems($collection, $data);
    }

    /*
     * get ME items by current employees
     */
    public function collectConfirmItems($collection, $data = [])
    {
        $evalTbl = self::getTableName();
        $opts = [
            'page' => 1,
            'per_page' => 50,
            'orderby' => [
                'eval_time' => 'desc',
                'project_id' => 'desc',
            ],
            'filter' => [],
        ];
        $data = array_merge($opts, $data);
        $dataFilter = $data['filter'];
        $sepMonth = config("project.me_sep_month");
        if ($sepMonth) {
            $collection->where(DB::raw('DATE_FORMAT(' . $evalTbl . '.eval_time, "%Y-%m")'), '>', $sepMonth);
        }

        // if isset param project_id
        $projId = Form::getFilterData('excerpt', 'project_id', $dataFilter);
        if ($projId) {
            if (is_numeric($projId)) {
                $collection->where($evalTbl.'.project_id', $projId);
            } elseif ($projId == '_TEAM_') {
                $collection->whereNotNull($evalTbl.'.team_id');
            } else {
                //
            }
        }

        //filter month
        $filterMonth = Form::getFilterData('excerpt', 'month', $dataFilter);
        if ($filterMonth) {
            $collection->where(DB::raw('DATE_FORMAT(' . $evalTbl . '.eval_time, "%Y-%m")'), $filterMonth);
        }

        self::filterGrid($collection, [], $dataFilter);

        $collection->select(
            $evalTbl . '.id',
            $evalTbl . '.employee_id',
            $evalTbl . '.project_id',
            $evalTbl . '.team_id',
            DB::raw('DATE(' . $evalTbl . '.eval_time) as eval_time'),
            $evalTbl . '.proj_point',
            $evalTbl . '.proj_index',
            $evalTbl . '.effort',
            $evalTbl . '.avg_point',
            $evalTbl . '.status',
            $evalTbl . '.created_at',
            $evalTbl . '.is_leader_updated',
            DB::raw('DATE_FORMAT('. $evalTbl . '.eval_time, "%Y-%m") as eval_month'),
            'proj.project_code_auto',
            'proj.name as project_name',
            'proj.type as project_type',
            'team.name as team_name',
            DB::raw('DATE_FORMAT('.$evalTbl.'.eval_time, "%Y-%m") as eval_month')
        )
            ->groupBy($evalTbl.'.id');

        if (count($data['orderby']) > 0) {
            foreach ($data['orderby'] as $orderby => $dir) {
                $collection->orderBy($orderby, $dir);
            }
        } else {
            $collection->orderBy('eval_time', 'desc')
                    ->orderBy('project_id', 'desc');
        }

        return $collection->paginate($data['per_page'], ['*'], 'page', $data['page']);
    }

    /*
     * collect view member items
     */
    public function getViewMemberItems($data = [])
    {
        $meEvalTbl = self::getTableName();
        $tmbTbl = TeamMember::getTableName();
        $opts = [
            'page' => 1,
            'per_page' => 50,
            'orderby' => [
                'eval_time' => 'desc',
                'project_id' => 'desc',
            ],
            'filter' => [],
        ];
        $data = array_merge($opts, $data);
        $dataFilter = $data['filter'];
        $sepMonth = config("project.me_sep_month");

        $collection = self::leftJoin(ProjectMember::getTableName() . ' as pmt', function ($join) use ($meEvalTbl) {
                $join->on($meEvalTbl. '.project_id', '=', 'pmt.project_id')
                    ->where('pmt.status', '=', ProjectMember::STATUS_APPROVED);
            })
            ->where(function ($query) use ($meEvalTbl){
                $query->where($meEvalTbl.'.employee_id', DB::raw('pmt.employee_id'))
                        ->whereNotNull($meEvalTbl.'.project_id')
                        ->orWhereNull($meEvalTbl.'.project_id');
            })
            ->leftJoin(Project::getTableName() . ' as proj', function ($join) use ($meEvalTbl) {
                $join->on($meEvalTbl. '.project_id', '=', 'proj.id')
                        ->where('proj.status', '=', Project::STATUS_APPROVED);
            })
            ->whereNull('proj.deleted_at')
            ->leftJoin(Team::getTableName() . ' as team', $meEvalTbl.'.team_id', '=', 'team.id')
            ->join(Employee::getTableName() . ' as emp', $meEvalTbl.'.employee_id', '=', 'emp.id')
            ->whereNotIn($meEvalTbl.'.status', [self::STT_DRAFT, self::STT_NEW, self::STT_REWARD]);

        if ($sepMonth) {
            $collection->where(DB::raw('DATE_FORMAT(' . $meEvalTbl . '.eval_time, "%Y-%m")'), '>', $sepMonth);
        }

        if (Permission::getInstance()->isScopeCompany(null, 'me::view.member.index')) {
            //do nothing
        } elseif (($teamIds = Permission::getInstance()->isScopeTeam(null, 'me::view.member.index'))) {
            $teamIds = is_array($teamIds) ? $teamIds : [];

            $collection->join($tmbTbl . ' as tmb', $meEvalTbl.'.employee_id', '=', 'tmb.employee_id')
                    ->whereIn('tmb.team_id', $teamIds);
        }

        //filter teams
        $filterTeamId = Form::getFilterData('excerpt', 'team_id', $dataFilter);
        if ($filterTeamId) {
            $collection->join($tmbTbl . ' as ft_tmb', function ($join) use ($meEvalTbl, $filterTeamId) {
                            $join->on($meEvalTbl.'.employee_id', '=', 'ft_tmb.employee_id')
                                ->where('ft_tmb.team_id', '=', $filterTeamId);
                        });
        }

        $filterProjId = Form::getFilterData('excerpt', 'project_id', $dataFilter);
        if ($filterProjId) {
            if (is_numeric($filterProjId)) {
                $collection->where($meEvalTbl.'.project_id', $filterProjId);
            } else {
                $collection->where($meEvalTbl.'.team_id', explode('_', $filterProjId)[1]);
            }
        }
        //filter month
        $filterMonth = Form::getFilterData('excerpt', 'month', $dataFilter);
        if ($filterMonth) {
            $collection->where(DB::raw('DATE_FORMAT(' . $meEvalTbl . '.eval_time, "%Y-%m")'), $filterMonth);
        }

        //join employee
        $collection->select(
                $meEvalTbl . '.id',
                $meEvalTbl . '.employee_id',
                $meEvalTbl . '.project_id',
                $meEvalTbl . '.team_id',
                DB::raw('DATE(' . $meEvalTbl . '.eval_time) as eval_time'),
                $meEvalTbl . '.proj_point',
                $meEvalTbl . '.proj_index',
                $meEvalTbl . '.effort',
                $meEvalTbl . '.avg_point',
                $meEvalTbl . '.status',
                $meEvalTbl . '.created_at',
                $meEvalTbl . '.is_leader_updated',
                DB::raw('DATE_FORMAT('. $meEvalTbl . '.eval_time, "%Y-%m") as eval_month'),
                'proj.project_code_auto',
                'proj.name as project_name',
                'proj.type as project_type',
                'emp.email',
                'emp.employee_code',
                'team.name as team_name',
                DB::raw('DATE_FORMAT('.$meEvalTbl.'.eval_time, "%Y-%m") as eval_month')
            )
            ->groupBy($meEvalTbl.'.id');

        if (count($data['orderby']) > 0) {
            foreach ($data['orderby'] as $orderby => $dir) {
                $collection->orderBy($orderby, $dir);
            }
        } else {
            $collection->orderBy('eval_time', 'desc')
                    ->orderBy('project_id', 'desc');
        }

        self::filterGrid($collection, ['excerpt'], $dataFilter);

        return $collection->paginate($data['per_page'], ['*'], 'page', $data['page']);
    }

    /**
     * check evaluator must be comment when ME in type S, A, C
     * @param array $evalIds ME ids
     * @param string $employeeId employee ID evaluator
     * @return boolean
     */
    public function checkEvalRequireComment($evalIds, $employeeId = null)
    {
        if (!$employeeId) {
            $employeeId = auth()->id();
        }
        $evalItems = self::whereIn('id', $evalIds)
            ->where(function ($query) { //avg_point in type S, A and C
                $query->where('avg_point', '>=', MeView::TYPE_A)
                    ->orWhere('avg_point', '<', MeView::TYPE_B);
            })
            ->get();
        if ($evalItems->isEmpty()) {
            return [
                'check' => true
            ];
        }
        $filterEvalIds = $evalItems->pluck('id')->toArray();
        $commentEvalIds = MeComment::whereIn('eval_id', $filterEvalIds)
            ->where('employee_id', $employeeId)
            ->groupBy('eval_id')
            ->pluck('eval_id')
            ->toArray();
        //none eval ids has comment
        if (count($commentEvalIds) < 1) {
            return [
                'check' => false,
                'eval_ids' => $filterEvalIds,
            ];
        }
        $diffEvalIds = array_diff($filterEvalIds, $commentEvalIds);
        //all eval ids has comment
        if (count($diffEvalIds) < 1) {
            return [
                'check' => true
            ];
        }
        return [
            'check' => false,
            'eval_ids' => $diffEvalIds
        ];
    }
}
