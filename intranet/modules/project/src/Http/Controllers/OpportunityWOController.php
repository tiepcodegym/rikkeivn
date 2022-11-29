<?php

namespace Rikkei\Project\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\Task;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\View\Permission;
use Rikkei\Project\Model\TaskAssign;
use Exception;
use Illuminate\Support\Facades\DB;
use Rikkei\Project\Model\TaskTeam;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Project\Model\TaskComment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Rikkei\Core\View\View;
use Rikkei\Team\Model\Team;
use Rikkei\Core\View\Form;
use Rikkei\Team\Model\PqaResponsibleTeam;
use Rikkei\Team\View\Config;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Core\Model\CoreModel;
use Maatwebsite\Excel\Facades\Excel;
use Session;

class OpportunityWOController extends Controller
{
    public function index()
    {
        Breadcrumb::add('Opportunity', URL::route('project::report.opportunity'));

        $pager = Config::getPagerData(null, ['order' => 'tasks.id', 'dir' => 'desc']);
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];
        $conditions = [];
        $conditions = array_merge($dataFilter, $conditions);
        $urlFilter = route('project::report.opportunity') . '/';
        $teamIds = [];
        $teamIdsAvailable = null;
        $teamTreeAvailable = [];
        $route = 'project::report.opportunity';
        //scope company => view all team
        if (Permission::getInstance()->isScopeCompany(null, $route)) {
            $teamIds = Form::getFilterData('except', 'teams.id', $urlFilter);
            if (is_array($teamIds)) {
                $teamIds = array_filter(array_values($teamIds));
                $teamIds = implode($teamIds, ', ');
            }
            $teamIdsAvailable = true;
        } else {// permission team or self profile.
            $teamIdsAvailable = [];
            if (($scopeTeamIds = Permission::getInstance()->isScopeTeam(null, $route))) {
                $teamIdsAvailable = is_array($scopeTeamIds) ? $scopeTeamIds : [];
            }
            // get list team_id responsible by pqa.
            $curEmp = Permission::getInstance()->getEmployee();
            $teamIdsResponsibleByPqa = PqaResponsibleTeam::getListTeamIdResponsibleTeam($curEmp->id);
            if (!$teamIdsResponsibleByPqa->isEmpty()) {
                $teamIdsAvailable = array_merge($teamIdsAvailable, $teamIdsResponsibleByPqa->pluck('team_id')->toArray());
            }
            if (!$teamIdsAvailable || !Permission::getInstance()->isAllow($route)) {
                View::viewErrorPermission();
            }
            $teamIdsAvailable = array_unique($teamIdsAvailable);
            //ignore team childs
            //get team and all child avaliable
            $teamIdsChildAvailable = [];
            if (is_array($teamIdsAvailable) && count($teamIdsAvailable)) {
                $teamPathTree = Team::getTeamPath();
                foreach ($teamIdsAvailable as $teamId) {
                    if (isset($teamPathTree[$teamId]) && $teamPathTree[$teamId]) {
                        if (isset($teamPathTree[$teamId]['child'])) {
                            $teamTreeAvailable = array_merge($teamTreeAvailable, $teamPathTree[$teamId]['child']);
                            $teamIdsChildAvailable = array_merge($teamIdsChildAvailable, $teamPathTree[$teamId]['child']);
                            unset($teamPathTree[$teamId]['child']);
                        }
                        $teamTreeAvailable = array_merge($teamTreeAvailable, $teamPathTree[$teamId]);
                    }
                    $teamTreeAvailable = array_merge($teamTreeAvailable, [$teamId]);
                }
                $teamIdsAvailable = array_merge($teamIdsAvailable, $teamIdsChildAvailable);
            }
            if ($teamIds = Form::getFilterData('except', 'teams.id', $urlFilter)) {
                $teamIds = implode($teamIds, ', ');
            }
            if (!$teamIds) {
                $teamIds = null;
            }
        }

        $list = $this->getList($pager['order'], $pager['dir'], $teamIdsAvailable);
        $list = $this->filterList($list, $conditions);
        if (count($list) > 0) {
            $list = CoreModel::filterGrid($list);
            $list = CoreModel::pagerCollection($list, $pager['limit'], $pager['page']);
        }
        
        return view('project::opportunity_wo.list', [
            'collectionModel' => $list,
            'teamIdCurrent' => $teamIds,
            'teamIdsAvailable' => $teamIdsAvailable,
            'teamTreeAvailable' => $teamTreeAvailable
        ]);
    }

    public function save(Request $request)
    {
        $data = $request->all();
        $curEmp = Permission::getInstance()->getEmployee();
        $requiredArray = ['opportunity_source', 'content', 'cost', 'expected_benefit', 'status',
                            'action_plan', 'employee_oop_assignee', 'duedate', 'action_status', 'project_id', 'team'];
        $rule = array_fill_keys($requiredArray, 'required');
        $validator = Validator::make($data, $rule);
        if ($validator->fails()) {
            return back()->with('messages', [
                'errors' => [
                    Lang::get('project::message.Error input data!'),
                ]
            ]);
        }

        if (empty($data['isEdit'])) {
            $data['created_by'] = $curEmp->id;
        }
        $data['type'] = Task::TYPE_OPPORTUNITY;
        $data['priority'] = Task::fillPriority($data['cost'], $data['expected_benefit']);
        DB::beginTransaction();
        try {
            $issue = Task::store($data);

            TaskAssign::delByIssue($issue->id);
            $taskAssign = [
                [
                    'task_id' => $issue->id,
                    'employee_id' => $data['employee_oop_assignee'],
                    'role' => TaskAssign::ROLE_ASSIGNEE,
                    'status' => TaskAssign::STATUS_NO,
                ],
            ];
            TaskAssign::insert($taskAssign);

            TaskTeam::delByIssue($issue->id);
            $taskTeam = [
                'task_id' => $issue->id,
                'team_id' => $data['team'],
            ];
            TaskTeam::insert($taskTeam);

            $cmt = TaskComment::where('task_id', $issue->id)->first();
            if (!$cmt || $cmt->content != $data['comment']) {
                TaskComment::delByTaskId($issue->id);
                if (!empty($data['comment'])) {
                    TaskComment::create([
                        'task_id' => $issue->id,
                        'content' => $data['comment'],
                        'created_by' => $curEmp->id,
                    ]);
                }
            }

            $messages = [
                'success' => [
                    'Save opportunity success'
                ]
            ];
            
            DB::commit();
            if ($redirectUrl = $request->get('redirectUrl')) {
                return redirect()->to($redirectUrl)->with('messages', $messages);
            }
            return redirect()->route('project::report.opportunity.detail', ['id' => $issue->id])->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollBack();
            \Log::info($ex);
            $messages = [
                'errors' => [
                    'Save opportunity error'
                ]
            ];
            return redirect()->back()->with('messages', $messages);
        }
    }

    public function detail($id)
    {
        Breadcrumb::add(Lang::get('project::view.Detail'));
        $oopInfo = Task::getById($id);
        if (!$oopInfo) {
            return redirect()->back()->with('messages', ['errors' => [trans('project::message.Opportunity not found')]]);
        }
        $projectId = $oopInfo->project_id;
        $curEmp = Permission::getInstance()->getEmployee();
        if (isset($_SERVER['HTTP_REFERER']) && in_array($_SERVER['HTTP_REFERER'], [route('project::report.opportunity'), route('project::project.edit', ['id' => $projectId])])) {
            Session::put('opportunity_url_back', $_SERVER['HTTP_REFERER']);
        }
        return View('project::opportunity_wo.detail',
        [
            'id' => $id,
            'curEmp' => $curEmp,
            'oopInfo' => $oopInfo,
            'projectId' => $projectId,
            'projectData' => Project::find($projectId),
            'project' => Project::getTeamInChargeOfProject($projectId),
            'comments' => TaskComment::getCommentOfTask($id),
            'isEdit' => true,
            'urlBack' => Session::get('opportunity_url_back')
        ]);
    }

    public function export()
    {
        $pager = Config::getPagerData(null, ['order' => 'tasks.id', 'dir' => 'desc']);
        $filter = Form::getFilterData(null, null, route("project::report.opportunity")."/");
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];
        $conditions = [];
        $conditions = array_merge($dataFilter, $conditions);
        $urlFilter = route('project::report.opportunity') . '/';
        $teamIds = [];
        $teamIdsAvailable = null;
        $teamTreeAvailable = [];
        $route = 'project::report.opportunity';
        
        //scope company => view all team
        if (Permission::getInstance()->isScopeCompany(null, $route)) {
            $teamIds = Form::getFilterData('except', 'teams.id', $urlFilter);
            if (is_array($teamIds)) {
                $teamIds = array_filter(array_values($teamIds));
                $teamIds = implode($teamIds, ', ');
            }
            $teamIdsAvailable = true;
        } else {// permission team or self profile.
            $teamIdsAvailable = [];
            if (($scopeTeamIds = Permission::getInstance()->isScopeTeam(null, $route))) {
                $teamIdsAvailable = is_array($scopeTeamIds) ? $scopeTeamIds : [];
            }
            // get list team_id responsible by pqa.
            $curEmp = Permission::getInstance()->getEmployee();
            $teamIdsResponsibleByPqa = PqaResponsibleTeam::getListTeamIdResponsibleTeam($curEmp->id);
            if (!$teamIdsResponsibleByPqa->isEmpty()) {
                $teamIdsAvailable = array_merge($teamIdsAvailable, $teamIdsResponsibleByPqa->pluck('team_id')->toArray());
            }
            if (!$teamIdsAvailable || !Permission::getInstance()->isAllow($route)) {
                View::viewErrorPermission();
            }
            $teamIdsAvailable = array_unique($teamIdsAvailable);
            //ignore team childs
            //get team and all child avaliable
            $teamIdsChildAvailable = [];
            if (is_array($teamIdsAvailable) && count($teamIdsAvailable)) {
                $teamPathTree = Team::getTeamPath();
                foreach ($teamIdsAvailable as $teamId) {
                    if (isset($teamPathTree[$teamId]) && $teamPathTree[$teamId]) {
                        if (isset($teamPathTree[$teamId]['child'])) {
                            $teamTreeAvailable = array_merge($teamTreeAvailable, $teamPathTree[$teamId]['child']);
                            $teamIdsChildAvailable = array_merge($teamIdsChildAvailable, $teamPathTree[$teamId]['child']);
                            unset($teamPathTree[$teamId]['child']);
                        }
                        $teamTreeAvailable = array_merge($teamTreeAvailable, $teamPathTree[$teamId]);
                    }
                    $teamTreeAvailable = array_merge($teamTreeAvailable, [$teamId]);
                }
                $teamIdsAvailable = array_merge($teamIdsAvailable, $teamIdsChildAvailable);
            }
            if ($teamIds = Form::getFilterData('except', 'teams.id', $urlFilter)) {
                $teamIds = implode($teamIds, ', ');
            }
            if (!$teamIds) {
                $teamIds = null;
            }
            if (is_array($teamIdsAvailable) && count($teamIdsAvailable) == 1) {
                $teamIdsAvailable = Team::select('name')
                    ->find($teamIds);
            }
        }

        $collectionModel = $this->getList($pager['order'], $pager['dir'], $teamIdsAvailable);
        $collectionModel = $this->filterList($collectionModel, $conditions);
        if (count($collectionModel) > 0) {
            $collectionModel = CoreModel::filterGrid($collectionModel, [], route("project::report.opportunity")."/");
            $collectionModel = $collectionModel->get();
        }
        Excel::create('Opportunity list', function ($excel) use ($collectionModel) {
            $excel->sheet('sheet1', function ($sheet) use ($collectionModel) {
                $sheet->loadView('project::opportunity_wo.export', [
                    'collectionModel' => $collectionModel
                ]);
            });
        })->export('xlsx');
    }

    private function getList($order = 'tasks.id', $dir = 'desc', $teamIdsAvailable)
    {
        $typeOpp = Task::TYPE_OPPORTUNITY;
        $tableTask = Task::getTableName();
        $tableEmployee = Employee::getTableName();
        $tableTaskAssigns = TaskAssign::getTableName();

        $risks = Task::select('tasks.id',
            'tasks.project_id', 
            'tasks.type', 
            'tasks.opportunity_source',
            'tasks.content',
            'tasks.duedate',
            'tasks.actual_date',
            'tasks.cost',
            'tasks.expected_benefit',
            'tasks.priority',
            'tasks.status',
            'tasks.action_plan',
            'tasks.action_status',
            'tasks.created_at',
            'tasks.updated_at',
            "projs.name as projs_name",
            "projs.leader_id",
            "teams.id as team_id",
            "teams.name as team_name",
            "tblEmpAssign.id as assign_empId",
            DB::raw("SUBSTRING_INDEX(tblEmpAssign.email, ". "'@', 1) as assign_email")
        );

        $risks->leftJoin("$tableTaskAssigns as assign", function ($join) use ($tableTask) {
                $join->on('assign.task_id', '=', "{$tableTask}.id")
                ->where('assign.role', '=', TaskAssign::ROLE_ASSIGNEE);
            })
            ->leftJoin("{$tableEmployee} as tblEmpAssign", "tblEmpAssign.id", '=', "assign.employee_id")
            ->join("projs", "projs.id", "=", "{$tableTask}.project_id")
            ->leftJoin('project_members', "project_members.project_id", "=", "projs.id")
            ->leftJoin('team_members', 'team_members.employee_id', '=', 'projs.leader_id')
            ->leftJoin("teams", "teams.id", "=", "team_members.team_id");
        $risks->orderBy($order, $dir);
        $risks->groupBy('tasks.id');
        $risks->where("tasks.type", $typeOpp);

        $emp = Permission::getInstance()->getEmployee();
        if (Permission::getInstance()->isScopeCompany(null, 'project::report.opportunity')) {
            return $risks;
        } else {
            if (!empty($teamIdsAvailable)) {
                $risks->where(function ($p) use ($teamIdsAvailable, $emp) {
                    $p->orWhereIn('teams.id', $teamIdsAvailable)
                        ->orWhere(function ($p) use ($emp) {
                            $p->where('project_members.employee_id', $emp->id)
                                ->whereDate('project_members.start_at', '<=', Carbon::now()->format('Y-m-d'))
                                ->whereDate('project_members.end_at', '>=', Carbon::now()->format('Y-m-d'))
                                ->where('project_members.status', ProjectMember::STATUS_APPROVED);
                        });
                });
            } else {
                $risks->where('project_members.employee_id', $emp->id)
                    ->whereDate('project_members.start_at', '<=', Carbon::now()->format('Y-m-d'))
                    ->whereDate('project_members.end_at', '>=', Carbon::now()->format('Y-m-d'))
                    ->where('project_members.status', ProjectMember::STATUS_APPROVED);
            }
        }
        return $risks;
    }
    
    private function filterList($collection, $conditions)
    {
        if (isset($conditions['teams.id'])) {
            $collection->whereIn("team_members.team_id", $conditions['teams.id']);
        }
        if (isset($conditions['tasks.deadline_from'])) {
            $collection->whereDate('tasks.duedate', '>=', $conditions['tasks.deadline_from']);
        }
        if (isset($conditions['tasks.deadline_to'])) {
            $collection->whereDate('tasks.duedate', '<=', $conditions['tasks.deadline_to']);
        }

        if (isset($conditions['tasks.created_from'])) {
            $collection->whereDate('tasks.created_at', '>=', $conditions['tasks.created_from']);
        }
        if (isset($conditions['tasks.created_to'])) {
            $collection->whereDate('tasks.created_at', '<=', $conditions['tasks.created_to']);
        }
        
        if (isset($conditions['tasks.updated_form'])) {
            $collection->whereDate('tasks.updated_at', '>=', $conditions['tasks.updated_form']);
        }
        if (isset($conditions['tasks.updated_to'])) {
            $collection->whereDate('tasks.updated_at', '<=', $conditions['tasks.updated_to']);
        }
        return $collection;
    }
}

