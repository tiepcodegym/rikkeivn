<?php

namespace Rikkei\Project\Model;

use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\TeamProject;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Config;
use Carbon\Carbon;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\Form;
use DB;
use Illuminate\Support\Facades\URL;
use Rikkei\Core\View\View as CoreView;

class MeEvaluated extends MeEvaluation {

    /**
     * collect evaluated
     * @param type $userId
     * @param type $data
     * @return type
     */
    public static function collectEvaluated($userId = null, $data = []) {
        $scope = Permission::getInstance();
        if (!$userId) {
            $userId = $scope->getEmployee()->id;
        }

        $projectMemberTbl = ProjectMember::getTableName();
        $projectTbl = Project::getTableName();
        $evalTbl = self::getTableName();
        $empTbl = Employee::getTableName();
        $teamProjTbl = TeamProject::getTableName();
        $teamMbTbl = TeamMember::getTableName();

        //collection
        $collection = self::leftJoin($projectMemberTbl.' as pjm', function ($join) use ($evalTbl) {
                $join->on($evalTbl.'.project_id', '=', 'pjm.project_id')
                        ->where('pjm.status', '=', ProjectMember::STATUS_APPROVED);
            })
            ->leftJoin($projectTbl.' as proj', function ($join) use ($evalTbl) {
                $join->on($evalTbl.'.project_id', '=', 'proj.id')
                        ->where('proj.status', '=', Project::STATUS_APPROVED)
                        ->whereNull('proj.deleted_at');
            })
            ->join($empTbl.' as emp', $evalTbl.'.employee_id', '=', 'emp.id')
            ->leftJoin(Team::getTableName() . ' as team', 'team.id', '=', $evalTbl . '.team_id')
            ->where($evalTbl.'.status', '!=', self::STT_REWARD);

        // check scope
        if ($scope->isScopeCompany(null, 'project::me.view.evaluated')) {
            $collection->where($evalTbl.'.status', '!=', self::STT_DRAFT);
        } elseif ($teamIds = $scope->isScopeTeam(null, 'project::me.view.evaluated')) {
            $teamIds = is_array($teamIds) ? $teamIds : [];
            $collection->leftJoin($teamProjTbl . ' as teamproj', 'teamproj.project_id', '=', 'proj.id')
                ->leftJoin($teamMbTbl . ' as tmb', 'tmb.employee_id', '=', $evalTbl . '.employee_id')
                ->where(function ($query) use ($teamIds, $evalTbl) {
                    $query->whereIn('teamproj.team_id', $teamIds)
                        ->orWhereIn('tmb.team_id', $teamIds)
                        ->orWhereIn($evalTbl . '.team_id', $teamIds);
                })
                ->where($evalTbl.'.status', '!=', self::STT_DRAFT);
        } else {
            CoreView::viewErrorPermission();
        }

        /*bad code*/
        $filterProjects = clone $collection;
        $filterMonths = clone $collection;
        $filterEmployees = clone $collection;
        /*bad code*/
        
        // filter month
        $filterMonth = (isset($data['eval_time']) && $data['eval_time']) ? $data['eval_time'] : Form::getFilterData('month', 'eval_time');
        if (!$filterMonth) {
            $filterMonth = Carbon::now()->subMonthNoOverflow()->startOfMonth();
        }
        if ($filterMonth != '_all_') {
            if (!$filterMonth instanceof Carbon) {
                $filterMonth = Carbon::parse($filterMonth)->startOfMonth();
            }
            $collection->where($evalTbl.'.eval_time', $filterMonth->toDateTimeString());
        }
        
        // filter team
        $teamFilter = (isset($data['teams']) && $data['teams']) ? $data['teams'] : Form::getFilterData('team_filter', 'team_id');
        if ($teamFilter) {
            $teamFilter = Team::teamChildIds($teamFilter);
            $collection->leftJoin($teamProjTbl . ' as ft_teamproj', 'ft_teamproj.project_id', '=', 'proj.id')
                ->leftJoin($teamMbTbl . ' as ft_tmb', 'ft_tmb.employee_id', '=', $evalTbl . '.employee_id')
                // ->where(function ($query) use ($teamFilter, $evalTbl) {
                //     $query->whereIn('ft_teamproj.team_id', $teamFilter)
                //         ->orWhereIn('ft_tmb.team_id', $teamFilter)
                //         ->orWhereIn($evalTbl . '.team_id', $teamFilter);
                // });
                ->whereIn('ft_tmb.team_id', $teamFilter);
        }
        
        // filter attributes
        $collection = self::filterGrid($collection);
        $filterAvgs = clone $collection;
        
        // filter and pager
        $urlSubmitFilter = trim(URL::route('project::me.view.evaluated'), '/') . '/';
        $pager = Config::getPagerData($urlSubmitFilter, ['limit' => self::PAGER_LIMIT_IN_LEADER_REVIEW]);
        if (Form::getFilterPagerData('order')) {
            $collection = $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection = $collection->orderBy('eval_time', 'desc')
                    ->orderBy($evalTbl.'.project_id', 'desc');
        }

        /*bad code will fix*/
        $filterProjects = $filterProjects->select('proj.id', 'proj.name')
                                            ->groupBy('proj.id')
                                            ->orderBy('proj.created_at', 'desc')
                                            ->get();
        $filterMonths = $filterMonths->select($evalTbl.'.eval_time')
                                            ->groupBy($evalTbl.'.eval_time')
                                            ->orderBy($evalTbl.'.eval_time', 'desc')
                                            ->get();
        $filterEmployees = $filterEmployees->select('emp.id as employee_id', 'emp.email')
                                            ->groupBy('emp.id')
                                            ->orderBy('emp.email', 'asc')
                                            ->get();
        /*bad code will fix*/

        //$filterAvgs = self::countContributeLabel($filterAvgs);
        
        $collection->select(
            $evalTbl.'.id',
            $evalTbl.'.project_id',
            $evalTbl.'.team_id',
            $evalTbl.'.eval_time',
            $evalTbl.'.avg_point',
            $evalTbl.'.status',
            'team.name as team_name',
            'proj.start_at',
            'proj.end_at', 
            'proj.project_code_auto',
            'proj.name as project_name',
            'proj.type as project_type', 
            'emp.email',
            'emp.employee_code'
        )
        ->groupBy($evalTbl.'.id');
        self::pagerCollection($collection, $pager['limit'], $pager['page']);

        return [
            'collectionModel' => $collection,
            'filterMonths' => $filterMonths,
            'filterProjects' => $filterProjects,
            //'filterAvgs' => $filterAvgs,
            'filterEmployees' => json_encode($filterEmployees)
        ];
    }
    
    /**
     * separate avg point
     * @param type $collection
     * @return type
     */
    public static function countContributeLabel($collect) {
        $evalTbl = self::getTableName();
        $collect->select($evalTbl.'.id', $evalTbl.'.avg_point')
                ->groupBy($evalTbl.'.id');
        
        $collection = DB::table(DB::raw("({$collect->toSql()}) as $evalTbl"))
                ->mergeBindings($collect->getQuery());
        //count range point
        $collection->select(
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN '. $evalTbl .'.avg_point >= '. self::TH_EXCELLENT .' THEN 1 ELSE 0 END) as count_excellent'),
                DB::raw('SUM(CASE WHEN '. $evalTbl .'.avg_point >= '. self::TH_GOOD .' AND '. $evalTbl .'.avg_point < '. self::TH_EXCELLENT .' THEN 1 ELSE 0 END) as count_good'),
                DB::raw('SUM(CASE WHEN '. $evalTbl .'.avg_point >= '. self::TH_FAIR .' AND '. $evalTbl .'.avg_point < '. self::TH_GOOD .' THEN 1 ELSE 0 END) as count_fair'),
                DB::raw('SUM(CASE WHEN '. $evalTbl .'.avg_point >= '. self::TH_SATIS .' AND '. $evalTbl .'.avg_point < '. self::TH_FAIR .' THEN 1 ELSE 0 END) as count_satis'),
                DB::raw('SUM(CASE WHEN '. $evalTbl .'.avg_point >= '. self::TH_UNSATIS .' AND '. $evalTbl .'.avg_point < '. self::TH_SATIS .' THEN 1 ELSE 0 END) as count_unsatis')
            );
        $summary = $collection->first();
        
        $result = null;
        if ($summary) {
            $result = [
                'total' => $summary->total,
                'excellent' => [
                    'num' => $summary->count_excellent,
                    'percent' => $summary->total ? round($summary->count_excellent / $summary->total * 100, 2) : 0
                ],
                'good' => [
                    'num' => $summary->count_good,
                    'percent' => $summary->total ? round($summary->count_good / $summary->total * 100, 2) : 0
                ],
                'fair' => [
                    'num' => $summary->count_fair,
                    'percent' => $summary->total ? round($summary->count_fair / $summary->total * 100, 2) : 0
                ],
                'satis' => [
                    'num' => $summary->count_satis,
                    'percent' => $summary->total ? round($summary->count_satis / $summary->total * 100, 2) : 0
                ],
                'unsatis' => [
                    'num' => $summary->count_unsatis,
                    'percent' => $summary->total ? round($summary->count_unsatis / $summary->total * 100, 2) : 0
                ]
            ];
        }
        return $result;
    }
    
    /**
     * collect not evaluate
     * @return type
     */
    public static function collectNotEvaluate ($data = []) {
        $scopeRoute = 'project::me.view.not_evaluate';
        $scope = Permission::getInstance();
        $userId = $scope->getEmployee()->id;

        $projectMemberTbl = ProjectMember::getTableName();
        $projectTbl = Project::getTableName();
        $evalTbl = self::getTableName();
        $empTbl = Employee::getTableName();
        $teamMemberTbl = TeamMember::getTableName();
        $projTeamTbl = TeamProject::getTableName();

        //collection
        $collection = ProjectMember::from($projectMemberTbl . ' as projmb')
            ->join($projectTbl . ' as proj', function ($join) {
                $join->on('projmb.project_id', '=', 'proj.id')
                        ->where('proj.status', '=', Project::STATUS_APPROVED)
                        ->whereNull('proj.deleted_at');
            })
            ->join($empTbl . ' as emp', function ($join) {
                $join->on('projmb.employee_id', '=', 'emp.id')
                        ->whereNull('emp.deleted_at');
            })
            ->leftJoin($evalTbl . ' as eval', function ($join) {
                $join->on('projmb.project_id', '=', 'eval.project_id')
                        ->on('projmb.employee_id', '=', 'eval.employee_id');
            })
            ->where('projmb.status', ProjectMember::STATUS_APPROVED)
            ->whereNotIn('proj.state', [Project::STATE_REJECT, Project::STATE_PENDING])
            ->where(function ($query) {
                $query->where(function ($query2) {
                    $query2->whereNotNull('eval.id')
                    ->where('eval.status', self::STT_DRAFT);
                })
                ->orWhereNull('eval.id');
            });

        // check scope
        if ($scope->isScopeCompany(null, $scopeRoute)) {
            //do nothing
        } elseif ($teamIds = $scope->isScopeTeam(null, $scopeRoute)) {
            $teamIds = is_array($teamIds) ? $teamIds : [];
            $collection->leftJoin($projTeamTbl . ' as projteam', 'projteam.project_id', '=', 'proj.id')
                ->leftJoin($teamMemberTbl . ' as tmb', 'tmb.employee_id', '=', 'eval.employee_id')
                ->where(function ($query) use ($teamIds) {
                    $query->whereIn('projteam.team_id', $teamIds)
                            ->orWhereIn('tmb.team_id', $teamIds);
                });
        } else {
            CoreView::viewErrorPermission();
        }

        /*bad code*/
        $filterProjects = clone $collection;
        $filterMonths = clone $collection;
        $filterEmployees = clone $collection;
        /*bad code*/
        
        // filter month
        $filterMonth = (isset($data['start_at']) && $data['start_at']) ? $data['start_at'] : Form::getFilterData('month', 'start_at');
        if (!$filterMonth) {
            $filterMonth = Carbon::now()->subMonthNoOverflow();
        }
        if ($filterMonth != '_all_') {
            if (!$filterMonth instanceof Carbon) {
                $filterMonth = Carbon::parse($filterMonth);
            }
            $collection->where('projmb.start_at', '<=', $filterMonth->lastOfMonth()->toDateTimeString());
                    // ->where('projmb.end_at', '>=', $filterMonth->startOfMonth()->toDateTimeString());
            //filter employee leaved
            $collection->where(function ($query) use ($filterMonth) {
                $query->whereNull('emp.leave_date')
                    ->orWhereDate('emp.leave_date', '>=', $filterMonth->startOfMonth()->toDateString());
            });
        }
        $filterMonthEnd = (isset($data['start_at']) && $data['start_at']) ? $data['start_at'] : Form::getFilterData('month', 'end_at');
        if ($filterMonthEnd) {
            if (!$filterMonthEnd instanceof Carbon) {
                $filterMonthEnd = Carbon::parse($filterMonthEnd);
            }
            $collection->where('projmb.end_at', '>=', $filterMonthEnd->startOfMonth()->toDateTimeString());
            // $collection->where(DB::raw('MONTH(projmb.end_at)'), '=', $filterMonthEnd->month)
            //         ->where(DB::raw('YEAR(projmb.end_at)'), '=', $filterMonthEnd->year);
        }
        // filter team
        $teamFilter = (isset($data['teams']) && $data['teams']) ? $data['teams'] : Form::getFilterData('team_filter', 'team_id');
        if ($teamFilter) {
            $teamFilter = Team::teamChildIds($teamFilter);
            $collection->leftJoin($projTeamTbl . ' as tpj', function ($join) use ($teamFilter) {
                    $join->on('projmb.project_id', '=', 'tpj.project_id');
                })
                // ->leftJoin($teamMemberTbl . ' as ft_tmb', 'ft_tmb.employee_id', '=', 'eval.employee_id')
                ->leftJoin($teamMemberTbl . ' as ft_tmb', 'ft_tmb.employee_id', '=', 'projmb.employee_id')
                // ->where(function ($query) use ($teamFilter) {
                //     $query->whereIn('tpj.team_id', $teamFilter)
                //             ->orWhereIn('ft_tmb.team_id', $teamFilter);
                // });
                ->whereIn('ft_tmb.team_id', $teamFilter);
        }
        
        // filter and pager
        self::filterGrid($collection);
        $urlSubmitFilter = trim(URL::route($scopeRoute), '/') . '/';
        $pager = Config::getPagerData($urlSubmitFilter, ['limit' => self::PAGER_LIMIT_IN_LEADER_REVIEW]);
        if (Form::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('projmb.start_at', 'desc')
                    ->orderBy('projmb.project_id', 'desc');
        }
        /*bad code*/
        $filterProjects = $filterProjects
                ->select('proj.id', 'proj.name')
                ->groupBy('proj.id')
                ->orderBy('proj.created_at', 'desc')
                ->get();
        
        $filterMonthsEnd = clone $filterMonths;
        
        $filterMonths = $filterMonths
                ->select('projmb.start_at', DB::raw('DATE_FORMAT(projmb.start_at, "%Y-%m") as month_start_at'))
                ->groupBy('month_start_at')
                ->orderBy('start_at', 'desc')
                ->get();
        
        $filterMonthsEnd = $filterMonthsEnd
                ->select('projmb.end_at', DB::raw('DATE_FORMAT(projmb.end_at, "%Y-%m") as month_end_at'))
                ->groupBy('month_end_at')
                ->orderBy('end_at', 'desc')
                ->get();
        
        $filterEmployees = $filterEmployees
                ->select('emp.id as employee_id', 'emp.email')
                ->groupBy('emp.id')
                ->orderBy('emp.email', 'asc')
                ->get();
        /*bad code*/
        
        $collection->groupBy('projmb.project_id', 'projmb.employee_id')
                ->select('emp.id as employee_id', 'emp.employee_code', 'emp.email',
                        'proj.id as project_id', 'proj.name as project_name', 'proj.project_code_auto',
                        'projmb.start_at', 'projmb.end_at');
        
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        
        return [
            'collectionModel' => $collection,
            'filterMonths' => $filterMonths,
            'filterMonthsEnd' => $filterMonthsEnd,
            'filterProjects' => $filterProjects,
            'filterEmployees' => json_encode($filterEmployees)
        ];
    }
    
}
