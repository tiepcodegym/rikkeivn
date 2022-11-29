<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\Employee;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Core\View\Form;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Rikkei\Project\View\MeView;
use Rikkei\Team\Model\EmployeeWork;
use Rikkei\Resource\View\getOptions;

class MeReward extends CoreModel {

    protected $table = 'me_reward';
    protected $fillable = ['eval_id', 'reward_submit', 'reward_approve', 'status'];
    protected $primaryKey = 'eval_id';
    public $incrementing = false;

    const STT_DRAFT = 1;
    const STT_SUBMIT = 7;
    const STT_APPROVE = 9;

    /**
     * list reward status
     */
    public static function listStatuses () {
        $statuses = [
            self::STT_DRAFT => trans('project::me.Draft'),
            self::STT_SUBMIT => trans('project::me.Submited'),
            self::STT_APPROVE => trans('project::me.Approved')
        ];
        return $statuses;
    }

    /**
     * get me item to create reward
     *
     * @param  string $scopeRoute
     * @param  array $data
     * @param  string $urlFilter
     * @param  string $collectType
     * @param  boolean $refreshFiter
     * @return collection
     */
    public static function getMERewardData(
        $scopeRoute = 'project::me.reward.update',
        $data = [],
        $urlFilter = null,
        $collectType = 'list',
        $refreshFiter = true
    )
    {
        $pager = Config::getPagerData($urlFilter);
        if (isset($data['team_id']) && isset($data['time']) && $refreshFiter) {
            Form::forgetFilter($urlFilter);
        }

        $scope = Permission::getInstance();
        $currUser = $scope->getEmployee();

        $rewardTbl = self::getTableName();
        $empTbl = Employee::getTableName();
        $meTbl = MeEvaluation::getTableName();
        $projTbl = Project::getTableName();
        $projMbTbl = ProjectMember::getTableName();
        $teamTbl = Team::getTableName();
        $teamMbTbl = TeamMember::getTableName();

        //get filter month
        $filterMonth = isset($data['filter_month']) ? $data['filter_month'] : null;
        //collection
        $collection = MeEvaluation::from($meTbl.' as me')
                //join reward
                ->leftJoin($rewardTbl.' as reward', 'me.id', '=', 'reward.eval_id')
                //join employee
                ->join($empTbl.' as emp', function ($join) {
                    $join->on('me.employee_id', '=', 'emp.id')
                            ->whereNull('emp.deleted_at');
                })
                //join team member filter permission
                ->join($teamMbTbl.' as tmb', function ($join) {
                    $join->on('emp.id', '=', 'tmb.employee_id');
                })
                ->join($teamTbl.' as teamld', function ($join) {
                    $join->on('tmb.team_id', '=', 'teamld.id');
                })
                //join project member
                ->leftJoin($projMbTbl.' as pjm', function ($join) {
                    $join->on('me.project_id', '=', 'pjm.project_id')
                            ->on('me.employee_id', '=', 'pjm.employee_id')
                            ->on(DB::raw('DATE(pjm.start_at)'), '<=', DB::raw('LAST_DAY(me.eval_time)'))
                            ->on(DB::raw('DATE(pjm.end_at)'), '>=', DB::raw('DATE(me.eval_time)'))
                            ->where('pjm.is_disabled', '!=', ProjectMember::STATUS_DISABLED);
                })
                //check project_id
                ->where(function ($query) {
                    $query->whereNotNull('me.project_id')
                            ->where('pjm.status', ProjectMember::STATUS_APPROVED)
                            ->orWhereNull('me.project_id');
                })
                //join projs
                ->leftJoin($projTbl.' as proj', function ($join) {
                    $join->on('me.project_id', '=', 'proj.id')
                            ->where('proj.status', '=', Project::STATUS_APPROVED);
                })
                //join ME team
                ->leftJoin($teamTbl.' as team', function ($join) {
                    $join->on('me.team_id', '=', 'team.id')
                            ->whereNull('team.deleted_at');
                })
                ->groupBy('me.id');

        //filter project_id
        if (isset($data['project_id'])) {
            if ($refreshFiter) {
                Form::forgetFilter($urlFilter);
            }
            $collection->where('proj.id', $data['project_id']);
        }
        //filter project type
        $projType = $data['project_type'];

        // $projType = Project::TYPE_OSDC;
        //$queryProjType ='AND proj2.type <> '. Project::TYPE_BASE;

        if ($projType && $projType != '_all_') {
            $collection->where(function ($query) use ($projType) {
                $query->whereNotNull('me.project_id')
                        ->where('proj.type', $projType)
                        ->orWhere(function ($subQuery) use ($projType) {
                            $subQuery->where('me.status', MeEvaluation::STT_REWARD)
                                ->whereNull('reward.project_type')
                                ->orWhere('reward.project_type', $projType);
                        });
            });
        } else {
            /*$collection->where(function ($query) {
                $query->whereNotNull('me.project_id')
                    //->where('proj.type', '<>', Project::TYPE_BASE) not ignore base
                    ->orWhereNull('me.project_id');
            });*/
        }
        //join ME project member get all effort join projects
        /*$collection->leftJoin(DB::raw('(SELECT pjm2.id, pjm2.employee_id, pjm2.start_at, pjm2.end_at, pjm2.effort, pjm2.status, pjm2.is_disabled, pjm2.type '
                            . 'FROM '. $projMbTbl .' AS pjm2 '
                            . 'INNER JOIN '. $projTbl .' AS proj2 '
                            . 'ON pjm2.project_id = proj2.id '
                            //. $queryProjType not ignore base
                            .') as pjmteam'),
                function ($join) {
                    $join->on('me.employee_id', '=', 'pjmteam.employee_id')
                            ->whereNotNull('me.team_id')
                            ->on(DB::raw('DATE(pjmteam.start_at)'), '<=', DB::raw('LAST_DAY(me.eval_time)'))
                            ->on(DB::raw('DATE(pjmteam.end_at)'), '>=', DB::raw('DATE(me.eval_time)'))
                            ->where('pjmteam.status', '=', ProjectMember::STATUS_APPROVED)
                            ->where('pjmteam.is_disabled', '!=', ProjectMember::STATUS_DISABLED);
                            //->where('pjmteam.type', '=', ProjectMember::TYPE_PQA);
                })
                ->whereNull('proj.deleted_at')
                ->where('me.status', '!=', MeEvaluation::STT_DRAFT);*/

        $collection->whereNull('proj.deleted_at')
                ->where('me.status', '!=', MeEvaluation::STT_DRAFT);
        //check condition $data
        if (isset($data['reward_status'])) {
            $rewardStatuses = $data['reward_status'];
            $collection->whereIn('reward.status', $rewardStatuses);
        }

        $filterTeam = Form::getFilterData('excerpt', 'team_id', $urlFilter);
        if (!$filterTeam) {
            $filterTeam = $data['default_team_id'];
        }
        //check scope permision
        if ($scope->isScopeCompany(null, $scopeRoute)) {
            //do nothing
        } elseif ($teamIds = $scope->isScopeTeam(null, $scopeRoute)) {
            //list by team
            $teamIds = is_array($teamIds) ? $teamIds : [];
            $collection->whereIn('tmb.team_id', $teamIds);
        } else {
            $collection->where('teamld.leader_id', $currUser->id);
        }

        //filter team
        if (isset($data['team_id']) && $data['team_id']) {
            $filterTeam = $data['team_id'];
        }
        if (!isset($data['project_id']) && $filterTeam && $filterTeam != MeEvaluation::VAL_ALL) {
            //list by team
            $filterTeam = Team::teamChildIds($filterTeam);
            $collection->whereIn('tmb.team_id', $filterTeam);
        }
        //filter month
        if ($filterMonth != MeEvaluation::VAL_ALL) {
            $startMonth = $filterMonth->startOfMonth()->toDateString();
            $collection->where(DB::raw('DATE(me.eval_time)'), $startMonth);
            $collection->where(function ($query) use ($startMonth) {
                $query->whereNull('emp.leave_date')
                    ->orWhere(DB::raw('DATE(emp.leave_date)'), '>=', $startMonth);
            });
        }

        //filter range point
        $avgRange = Form::getFilterData('excerpt', 'avg_point', $urlFilter);
        $arrAvg = explode('-', $avgRange);
        if (count($arrAvg) > 1) {
            $collection->where('me.avg_point', '>=', $arrAvg[0])
                            ->where('me.avg_point', '<', $arrAvg[1]);
        }
        //filter status
        $filterStatus = Form::getFilterData('excerpt', 'reward_status', $urlFilter);
        if ($filterStatus) {
            if ($filterStatus == self::STT_DRAFT) {
                $collection->whereNull('reward.status');
            } else {
                $collection->where('reward.status', $filterStatus);
            }
        }
        //sort order
        if (Form::getFilterPagerData('order', $urlFilter)) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('me.eval_time', 'desc')
                    ->orderBy('me.project_id', 'desc');
        }

        if ($collectType == 'totalReward') {
            return $collection->select(
                'me.id', 'me.eval_time', 'me.avg_point', 'me.effort as day_effort',
                DB::raw('DATE_FORMAT(me.eval_time, "%Y-%m") AS eval_month'),
                'reward.reward_submit', 'reward.reward_approve',
                'emp.id as emp_id',
                DB::raw('IFNULL(proj.type, reward.project_type) as proj_type')
                )
                ->get();
        }

        //filter + pager
        self::filterGrid($collection, ['excerpt'], $urlFilter);
        //select
        $collection->select(
            'me.id', 'me.eval_time', 'me.avg_point', 'me.status', 'me.effort as day_effort',
            'reward.status_paid', 'reward.reward_submit', 'reward.comment as reward_comment', 'reward.reward_approve',
            'reward.approve_histories', 'reward.submit_histories',
            DB::raw('DATE_FORMAT(me.eval_time, "%Y-%m") as eval_month'),
            DB::raw('IFNULL(reward.status, '. self::STT_DRAFT .') as reward_status'),
            'emp.id as emp_id', 'emp.email', 'emp.employee_code', 'team.name as team_name',
            'proj.id as proj_id', 'proj.name as proj_name', 'proj.project_code_auto',
            DB::raw('IFNULL(proj.type, reward.project_type) as proj_type')
        );
        //check is export -> get all
        if ($collectType == 'export') {
            $collection->addSelect('emp.name as employee_name')
                ->addSelect(DB::raw('GROUP_CONCAT(DISTINCT(teamld.name)) as team_member_name'))
                ->addSelect(DB::raw(
                '(SELECT GROUP_CONCAT(CONCAT(empcm.name, " - ", mecm.content) SEPARATOR "\r\n") FROM '. MeComment::getTableName() .' AS mecm
                    INNER JOIN '. Employee::getTableName() .' as empcm ON empcm.id = mecm.employee_id
                    WHERE mecm.eval_id = me.id GROUP BY mecm.eval_id
                ) AS me_comments'
                ));
            if (isset($data['item_ids'])) {
                $collection->whereIn('me.id', $data['item_ids']);
            }
            return $collection->get();
        }
        self::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;
    }

    /**
     *
     * @param type $time
     * @param type $scopeRoute
     * @return type
     */
    public static function getDataExport($time, $scopeRoute = null) {
        $rewardTbl = self::getTableName();
        $meTbl = MeEvaluation::getTableName();
        $empTbl = Employee::getTableName();
        $teamTbl = Team::getTableName();
        $teamMbTbl = TeamMember::getTableName();
        $projectTbl = Project::getTableName();
        if (!$time instanceof Carbon) {
            $time = Carbon::parse($time);
        }

        $collection = self::select('emp.id as emp_id', 'proj.id as proj_id', 'emp.employee_code as emp_code',
                        'emp.name as emp_name', 'emp.email as emp_email',
                    DB::raw('GROUP_CONCAT(DISTINCT(teamld.name)) as team_name'),
                    'teamme.name as teamme_name',
                    DB::raw('IFNULL(teamld.name,teamme.name) as team'),
                    'reward.reward_approve', 'reward.comment',
                    DB::raw('NULL as member_type'),
                    'proj.name as proj_name', 'proj.type as proj_type', 'me.avg_point as me_point')
                ->from($rewardTbl.' as reward')
                ->join($meTbl.' as me', 'reward.eval_id', '=', 'me.id')
                ->join($empTbl.' as emp', function ($join) {
                    $join->on('me.employee_id', '=', 'emp.id')
                            ->whereNull('emp.deleted_at');
                })
                ->join($teamMbTbl.' as tmb', 'emp.id', '=', 'tmb.employee_id')
                ->join($teamTbl.' as teamld', 'tmb.team_id', '=', 'teamld.id')
                ->leftJoin($projectTbl.' as proj', function ($join) {
                    $join->on('me.project_id', '=', 'proj.id')
                            ->where('proj.status', '=', Project::STATUS_APPROVED);
                })
                ->leftJoin($teamTbl.' as teamme', 'me.team_id', '=', 'teamme.id')
                ->leftJoin(EmployeeWork::getTableName() . ' as empw', 'empw.employee_id', '=', 'emp.id')
                ->where(function ($query) { //except partner employee
                    $query->whereNull('empw.employee_id')
                            ->orWhere('empw.contract_type', '!=', getOptions::WORKING_BORROW);
                })
                ->whereNull('proj.deleted_at')
                ->where('reward.status', self::STT_APPROVE)
                ->where('me.eval_time', $time->startOfMonth()->toDateTimeString())
                //ignore reward null or zero(0)
                ->where(function ($query) {
                    $query->whereNotNull('reward.reward_approve')
                            ->where('reward.reward_approve', '>', 0);
                })
                ->groupBy('reward.eval_id');

        $currUser = Permission::getInstance()->getEmployee();
        if (Permission::getInstance()->isScopeCompany(null, $scopeRoute)) {
            //view all
        } elseif ($teamIds = Permission::getInstance()->isScopeTeam(null, $scopeRoute)) {
            $teamIds = is_array($teamIds) ? $teamIds : [];
            $collection->whereIn('tmb.team_id', $teamIds);
        } else {
            $collection->where('proj.leader_id', $currUser->id);
        }
        return $collection;
    }

    /**
     * save reward
     * @param int $itemReward
     * @param type $status
     * @return type
     */
    public static function saveReward ($itemReward, $status = null, $projType = null)
    {
        $isUpdate = false;
        $isSubmit = false;
        $evalId = $itemReward['eval_id'];
        $reward = MeReward::find($evalId);
        if (!$reward) {
            $reward = new MeReward();
            $reward->eval_id = $evalId;
        }
        if (!isset($itemReward['submit']) || !$itemReward['submit']) {
            $itemReward['submit'] = 0;
        }
        $rewardSubmit = preg_replace('/\,|\s/', '', $itemReward['submit']);
        if ($reward->submit_histories) {
            $isUpdate = true;
        }
        if ($status && $status != $reward->status) {
            $isSubmit = true;
        }
        $reward->submit_histories = $rewardSubmit;
        $reward->reward_submit = $rewardSubmit;
        $reward->comment = $itemReward['comment'];
        if ($status) {
            $reward->status = $status;
        }
        if (!$reward->project_type && $projType) {
            $reward->project_type = $projType;
        }
        $reward->save();
        return [
            'is_update' => $isUpdate,
            'is_submit' => $isSubmit
        ];
    }

    //set submit histories attribute
    public function setSubmitHistoriesAttribute ($value) {
        $submitHistories = [];
        if ($this->submit_histories) {
            $submitHistories = unserialize($this->submit_histories);
        }
        if ($this->reward_submit != $value) {
            $currUser = Permission::getInstance()->getEmployee();
            array_push($submitHistories, [
                'account' => $currUser->name . ' ('. ucfirst(strtolower(preg_replace('/@.*/', '', $currUser->email))) .')',
                'number' => $value,
                'time' => Carbon::now()->format('Y-m-d H:i')
            ]);
        }
        if (!$submitHistories) {
            $submitHistories = null;
        } else {
            $submitHistories = serialize($submitHistories);
        }
        $this->attributes['submit_histories'] = $submitHistories;
    }

    //set histories attribute
    public function setApproveHistoriesAttribute ($value) {
        $approveHistories = [];
        if ($this->approve_histories) {
            $approveHistories = unserialize($this->approve_histories);
        }
        if ($this->reward_approve != $value) {
            $currUser = Permission::getInstance()->getEmployee();
            array_push($approveHistories, [
                'account' => $currUser->name . ' ('. ucfirst(strtolower(preg_replace('/@.*/', '', $currUser->email))) .')',
                'number' => $value,
                'time' => Carbon::now()->format('Y-m-d H:i')
            ]);
        }
        if (!$approveHistories) {
            $approveHistories = null;
        } else {
            $approveHistories = serialize($approveHistories);
        }
        $this->attributes['approve_histories'] = $approveHistories;
    }

    /**
     * collect data from eval_ids
     * @param type $evalIds
     * @return type
     */
    public static function collectBySubmited ($evalIds = [], $rewardStatus = []) {
        $rewardTbl = self::getTableName();
        $meTbl = MeEvaluation::getTableName();
        $empTbl = Employee::getTableName();
        $projTbl = Project::getTableName();
        $teamTbl = Team::getTableName();

        $collection = MeEvaluation::from($meTbl . ' as me')
                ->leftJoin($rewardTbl . ' as reward', 'me.id', '=', 'reward.eval_id')
                ->join($empTbl . ' as emp', function ($join) {
                    $join->on('me.employee_id', '=', 'emp.id')
                            ->whereNull('emp.deleted_at');
                })
                ->leftJoin(DB::raw('(SELECT proj2.id, proj2.status, proj2.deleted_at, proj2.name, proj2.leader_id, '
                            . 'emp2.name as leader_name, emp2.email as leader_email '
                        . 'FROM '. $projTbl .' AS proj2 '
                        . 'INNER JOIN '. $empTbl .' AS emp2 '
                        . 'ON proj2.leader_id = emp2.id '
                        . 'AND emp2.deleted_at IS NULL) as proj'), function ($join) {
                            $join->on('me.project_id', '=', 'proj.id')
                                    ->where('proj.status', '=', Project::STATUS_APPROVED);
                })
                ->leftJoin($teamTbl. ' as team', 'me.team_id', '=', 'team.id')
                ->whereNull('proj.deleted_at')
                ->whereIn('me.id', $evalIds);

        if ($rewardStatus) {
            $collection->whereIn('reward.status', $rewardStatus);
        }

        return $collection->groupBy('me.id')
                ->select('me.id', 'me.avg_point',
                        'reward.eval_id', 'team.id as team_id', 'team.name as team_name',
                        'proj.id as proj_id', 'proj.name as proj_name',
                        'proj.leader_name', 'proj.leader_email', 'proj.leader_id',
                        'emp.name', 'emp.email')
                ->get();
    }

    /**
     * collect to union with project reward
     * @param type $scopeRoute
     * @return type
     */
    public static function collectOnProject ($scopeRoute = null) {
        $rewardTbl = self::getTableName();
        $meTbl = MeEvaluation::getTableName();
        $projTbl = Project::getTableName();
        $empTbl = Employee::getTableName();
        $teamProjTbl = TeamProject::getTableName();
        $teamTbl = Team::getTableName();
        $tableTeamMember = TeamMember::getTableName();
        $tblProjQuality = ProjQuality::getTableName();

        $isPaidQuery = 'CASE WHEN (COUNT(DISTINCT(me.id)) = '
                . 'COUNT(DISTINCT(CASE WHEN merw.status_paid = '. MeView::STATE_PAID .' THEN merw.eval_id END))) '
                . 'THEN ' . MeView::STATE_PAID . ' ELSE ' . MeView::STATE_UNPAID . ' END';
        $collection = self::select(['me.id',
            DB::raw('merw.status'),
            DB::raw($isPaidQuery . ' as bonus_money'),
            DB::raw('null as approve_date'),
            'proj.created_at',
            DB::raw('IFNULL(proj.name, meteam.name) as name'),
            'proj.email',
            DB::raw('proj.billable_effort as billable'),
            DB::raw('-1 as reward_budget'),
            DB::raw('IFNULL(proj.id, 0) as project_id'), 'proj.type as project_type',
            DB::raw('GROUP_CONCAT(DISTINCT(proj.proj_team_name)) as team_name'),
            DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(merw.eval_id, "|", merw.reward_approve))) as sum_reward_approve'),
            DB::raw('2 as rw_type'),
            "proj.leader_id",
            'me.team_id as meteam_id',
            'merw.created_at as month_reward'])
                ->from($rewardTbl . ' as merw')
                ->join($meTbl . ' as me', 'merw.eval_id', '=', 'me.id')
                ->leftJoin(DB::raw('(SELECT proj2.id, proj2.status, proj2.created_at, proj2.name, proj2.deleted_at, proj2.type, '
                        . 'pm.email, team2.name as proj_team_name, team2.id as proj_team_id, proj2.leader_id, proj_ql.billable_effort '
                        . 'FROM '. $projTbl .' AS proj2 '
                        . 'INNER JOIN '. $empTbl .' AS pm '
                        . 'ON proj2.manager_id = pm.id '
                        . 'INNER JOIN '. $teamProjTbl .' AS tpj '
                        . 'ON proj2.id = tpj.project_id '
                        . 'INNER JOIN '. $teamTbl .' AS team2 '
                        . 'ON tpj.team_id = team2.id '
                        . 'INNER JOIN '. $tblProjQuality .' AS proj_ql '
                        . 'ON proj2.id = proj_ql.project_id) as proj'),
                        function ($join) {
                            $join->on('me.project_id', '=', 'proj.id')
                                    ->where('proj.status', '=', Project::STATUS_APPROVED);
                })
                ->leftJoin($teamTbl . ' as meteam', 'me.team_id', '=', 'meteam.id')
                ->whereNull('proj.deleted_at')
                ->groupBy('name');

        //permisstion
        if (Permission::getInstance()->isScopeCompany(null, $scopeRoute)) {
            //view all
        } else if(Permission::getInstance()->isScopeTeam(null, $scopeRoute)) {
            $teamMbTbl = TeamMember::getTableName();
            $teamIds = TeamMember::where('employee_id', auth()->id())->lists('team_id')->toArray();
            $teamIds = Team::teamChildIds($teamIds);
            $collection->join($empTbl.' as emp', 'me.employee_id', '=', 'emp.id')
                ->join($teamMbTbl.' as tmb', 'emp.id', '=', 'tmb.employee_id')
                ->whereIn('tmb.team_id', $teamIds);
        } else {
            $collection->where('me.id', -1);
        }

        $filterStatus = Form::getFilterData('number', 'tasks.status');
        $filterEmail = Form::getFilterData($empTbl.'.email');
        $filterProjName = Form::getFilterData($projTbl.'.name');
        $filterTeam = Form::getFilterData('exception', 'team_id');

        if ($filterStatus) {
            $collection->where('merw.status', $filterStatus);
        }
        if ($filterEmail) {
            $collection->where('proj.email', 'like', '%'. $filterEmail .'%');
        }
        if ($filterProjName) {
            $collection->where('proj.name', 'like', '%'. $filterProjName .'%');
        }
        if ($filterTeam) {
            $teamFilter = (int) $filterTeam;
            $arrayTeamFilter = [$teamFilter];
            $teamPath = Team::getTeamPath();
            if (isset($teamPath[$teamFilter]) &&
                isset($teamPath[$teamFilter]['child'])
            ) {
                $arrayTeamFilter = array_merge($arrayTeamFilter,
                        (array) $teamPath[$teamFilter]['child']);
            }
            $collection->where(function ($query) use ($arrayTeamFilter, $tableTeamMember) {
                $query->whereNotNull('proj.proj_team_id')
                        ->whereIn('proj.proj_team_id', $arrayTeamFilter)
                        ->orWhereIn('me.team_id', $arrayTeamFilter)
                        ->orWhereIn('me.employee_id', function ($query)
                            use (
                                $arrayTeamFilter,
                                $tableTeamMember
                        ){
                            $query->select($tableTeamMember.'.employee_id')
                                ->from($tableTeamMember)
                                ->whereIn($tableTeamMember.'.team_id', $arrayTeamFilter);
                        });
            });
        }

        return $collection;
    }

    /**
     * group leader id data
     * @param type $collections
     * @return type
     */
    public static function collectLeaders($collectReward)
    {
        $collectLeaders = [];
        if (!$collectReward->isEmpty()) {
            foreach ($collectReward as $meReward) {
                if (isset($collectLeaders[$meReward->leader_id])) {
                    $collectLeaders[$meReward->leader_id]['projects'][$meReward->proj_id] = $meReward->proj_name;
                } else {
                    $collectLeaders[$meReward->leader_id] = [
                        'name' => $meReward->leader_name,
                        'email' => $meReward->leader_email,
                        'projects' => [
                            $meReward->proj_id => $meReward->proj_name
                        ]
                    ];
                }
            }
        }
        return $collectLeaders;
    }

    /**
     * update paid status
     * @param type $evalIds
     * @param type $status
     */
    public static function updatePaidStatus($evalIds, $status)
    {
        return self::whereIn('eval_id', $evalIds)
                ->where('status', self::STT_APPROVE)
                ->update(['status_paid' => $status]);
    }

    /**
     * create new ME
     * @param array $aryData
     * @param string $month
     * @return array
     */
    public static function createNewME($aryData, $month, $teamId)
    {
        $result = [];
        foreach ($aryData as $data) {
            $item = MeEvaluation::create([
                'employee_id' => $data['employee_id'],
                'eval_time' => $month,
                'team_id' => $teamId,
                'status' => MeEvaluation::STT_REWARD
            ]);
            $data['is_new'] = true;
            $result[$item->id] = $data;
        }
        return $result;
    }

}