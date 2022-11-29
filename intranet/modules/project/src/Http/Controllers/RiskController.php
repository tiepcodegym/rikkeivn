<?php

namespace Rikkei\Project\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\View\CookieCore;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\View;
use Rikkei\Project\Model\Project;
use Lang;
use Rikkei\SubscriberNotify\Model\EmailQueue;
use Rikkei\Team\Model\PqaResponsibleTeam;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Config;
use Rikkei\Project\View\RiskPermission;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Project\Model\Risk;
use Rikkei\Team\View\Permission;
use Rikkei\Project\Model\RiskAction;
use Rikkei\Project\Model\RiskAttach;
use Illuminate\Http\Request;
use Rikkei\Project\Model\RiskComment;
use DB;
use Rikkei\Project\Model\Task;

class RiskController extends Controller
{
    /**
     * construct more
     */
    protected function _construct()
    {
        Menu::setActive('Report statistic');
        Breadcrumb::add(Lang::get('project::view.Report'));
        Breadcrumb::add(Lang::get('project::view.Risk') , route('project::report.risk'));
    }

    /**
     * Risk list page
     */
    public function risk()
    {
        Breadcrumb::add(Lang::get('project::view.List'));
        $pager = Config::getPagerData(null, ['order' => 'proj_op_ricks.id', 'dir' => 'desc']);
        $columns = ['proj_op_ricks.id', 'proj_op_ricks.type', 'content', 'level_important', 'owner', 'employees.email as owner_email',
            'teams.name as team_name', 'proj_op_ricks.status', 'proj_op_ricks.due_date', 'proj_op_ricks.updated_at', 'proj_op_ricks.created_at'];
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];
        $conditions = [];
        $conditions = array_merge($dataFilter, $conditions);
        $per = new Permission();
        $urlFilter = route('project::report.risk') . '/';
        $teamIds = [];
        $teamIdsAvailable = null;
        $teamTreeAvailable = [];
        $route = 'project::report.risk';
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
                $flagNoCheck = true;
            }
            if (is_array($teamIdsAvailable) && count($teamIdsAvailable) == 1) {
                $teamIdsAvailable = Team::select('name')
                    ->find($teamIds);
            }
        }
        $list = RiskPermission::getList($columns, $conditions, $pager['order'], $pager['dir'], $teamIdsAvailable);
        $list = Risk::filterRisk($list, $conditions);
        if (count($list) > 0) {
            $list = CoreModel::filterGrid($list);
            $list = CoreModel::pagerCollection($list, $pager['limit'], $pager['page']);
        }
        $projFilter = null;
        if (isset($dataFilter['projs.id'])) {
            $projFilter = Project::getProjectById($dataFilter['projs.id']);
        }
        return View('project::risk.list', [
            'collectionModel' => $list,
            'projFilter' => $projFilter,
            'teamIdCurrent' => $teamIds,
            'teamIdsAvailable' => $teamIdsAvailable,
            'teamTreeAvailable' => $teamTreeAvailable]);
    }
    
    /**
     * Risk detail page
     *
     * @param int $riskId
     * @return view
     */
    public function detail($riskId)
    {
        Breadcrumb::add(Lang::get('project::view.Detail'));
        $riskInfo = Risk::getById($riskId);
        if (!$riskInfo) {
            return redirect()->back()->with('messages', ['errors' => [trans('project::message.Risk not found')]]);
        }
        $projectId = $riskInfo->project_id;
        $methods = Risk::getMethods();
        $results = Risk::getResults();
        // check permission
        $scope = Permission::getInstance();
        $route = \Request::route()->getName();
        $currentUser = Permission::getInstance()->getEmployee();
        return View('project::risk.detail', 
            [
                'riskInfo' => $riskInfo,
                'methods'  => $methods,
                'results'  => $results,
                'projectId' => $projectId,
                'permissionEdit' => true,
                'riskMitigation' => isset($riskId) ? RiskAction::getByType(RiskAction::TYPE_RISK_MITIGATION, $riskId) : null,
                'riskContigency' => isset($riskId) ? RiskAction::getByType(RiskAction::TYPE_RISK_CONTIGENCY, $riskId) : null,
                'attachs' => isset($riskId) ? RiskAttach::getAttachs($riskId, RiskAttach::TYPE_RISK) : null,
                'comments' => RiskComment::getComments($riskId, RiskComment::TYPE_RISK),
                'project' => Project::getTeamInChargeOfProject($projectId)
            ]);
    }

    public function saveComment(Request $request)
    {
        $data = $request->all();
        DB::beginTransaction();
        try {
            if (!$data['content']) {
                $validator = Validator::make($data, [
                    'content' => 'required',
                ]);

                if ($validator->fails()) {
                    return redirect()->back()->withErrors($validator)->withInput();
                }
            }

            $dataContent = RiskComment::getMentions($data["content"]);
            $linkPattern = "/\[\:.+\:\]/";
            $content = trim(preg_replace($linkPattern, "", $dataContent));
            $riskComment = new RiskComment();
            $riskComment->obj_id = $data['risk_id'];
            $riskComment->content = $content;
            $riskComment->type = RiskComment::TYPE_RISK;
            $riskComment->created_by = Auth::user()->employee_id;
            $riskComment->save();
            if (!empty($data['attach_risk_comment'][0]) && count($data['attach_risk_comment'])) {
                if (isset($data['attach_risk_comment'])) {
                    $valid = Validator::make($data, [
                        'attach_risk_comment.*' => 'file|mimes:doc,docx,xlsx,pdf,png,jpg,gif,jpeg|max:5120',
                    ]);
                    if ($valid->fails()) {
                        return redirect()->back()->withErrors($valid)->withInput();
                    }
                    $messagesError = [
                        'success' => [
                            Lang::get('project::message.Error max size file'),
                        ]
                    ];
                    foreach ($data['attach_risk_comment'] as $attach) {
                        if (in_array($attach->getClientOriginalExtension(), ['jpg', 'jpeg', 'png'])) {
                            if ($attach->getSize() >= 2048*1000) {
                                return redirect()->route('project::report.risk.detail', ['id' => $data['risk_id']])->with('messages', $messagesError);
                            }
                        } else {
                            if ($attach->getSize() >= 5120*1000) {
                                return redirect()->route('project::report.risk.detail', ['id' => $data['risk_id']])->with('messages', $messagesError);
                            }
                        }
                    }
                    RiskAttach::uploadFiles($riskComment->id, $data['attach_risk_comment'], RiskAttach::TYPE_COMMENT);
                }
            }
            $project = Project::find($data['project_id']);
            $teamPqa = Team::getTeamPQAByType();
            if (isset($teamPqa)) {
                foreach ($teamPqa as $team) {
                    if (isset($team->mail_group)) {
                        if (empty($team->mail_group)) {
                            continue;
                        } else {
                            $this->sendMailRelatersForComment($team->mail_group, $team->name, $project, $data, Auth::user()->name);
                        }
                    }
                }
            }
            //send mail & noti to the person mentioned
            if (!empty($data["emp_mention"])) {
                Task::notiToPersonMentioned($data["emp_mention"], RiskComment::TYPE_RISK, $data['risk_id'], $content);
            }
            DB::commit();
            return redirect()->route('project::report.risk.detail', ['id' => $data['risk_id']])->with('messages', ['success' => [trans('project::message.Add comment successful.')]]);
        } catch (Exception $ex) {
            \Log::info($ex);
            DB::rollBack();
            return redirect()->route('project::report.risk.detail', ['id' => $data['risk_id']])->with('messages', ['error' => [trans('project::message.Add comment error.')]]);
        }
    }

    public function sendMailRelatersForComment($email, $name, $project, $data, $member)
    {
        $dataComment = [
            'email' => $email,
            'name' => $name,
            'url' => route("project::issue.detail", ['id' => $data['risk_id']]),
            'projectName' => $project->name,
            'issueContent' => $data['content'],
            'creator' => $member
        ];
        $subject = Lang::get("project::view.[Workoder] A issue has had a new comment");
        $emailQueue = new EmailQueue();
        $emailQueue->setTo($email, $name)
            ->setSubject($subject)
            ->setTemplate("project::emails.issue_relater_comment", $dataComment)
            ->save();
    }

    public function exportRisk()
    {
        $urlFilter = trim(URL::route('project::report.risk'), '/') . '/';
        $columns = ['proj_op_ricks.id', 'level_important', 'impact_using', 'owner', 'proj_op_ricks.status', 'proj_op_ricks.due_date', 'proj_op_ricks.type', 'probability_backup',
            'proj_op_ricks.description', 'proj_op_ricks.status', 'source', 'solution_using', 'proj_op_ricks.content', 'proj_op_ricks.updated_at', 'proj_op_ricks.created_at', 'impact_backup'];
        $filter = Form::getFilterData(null, null, $urlFilter);
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];
        $conditions = [];
        $conditions = array_merge($dataFilter, $conditions);
        $urlFilter = route('project::report.risk') . '/';
        $teamIds = [];
        $teamIdsAvailable = null;
        $teamTreeAvailable = [];
        $route = 'project::report.risk';
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
            if ($idFilters = Form::getFilterData('except', 'team_ids', $urlFilter)) {
                $teamIds = implode($idFilters, ', ') . ', ' . $teamIds;
                $teamIds = array_intersect(array_map('intval', explode(',', $teamIds)), $teamIdsAvailable);
                if (!array_intersect($teamIds, $teamIdsAvailable)) {
                    $checkReturn = CookieCore::get(Team::CACHE_TEAM_MEMBER_LIST);
                    if ($checkReturn < 1 || Permission::getInstance()->isScopeTeam($teamIds, $route)) {
                        Form::forgetFilter($urlFilter);
                        CookieCore::set(Team::CACHE_TEAM_MEMBER_LIST, 1);
                        return redirect()->route($route);
                    }
                    View::viewErrorPermission();
                }
                $teamIds = implode($teamIds, ', ');
            }
            if (!$teamIds) {
                $teamIds = null;
                $flagNoCheck = true;
            }
            if (is_array($teamIdsAvailable) && count($teamIdsAvailable) == 1) {
                $teamIdsAvailable = Team::select('name')
                    ->find($teamIds);
            }
        }
        $projFilter = null;
        if (isset($dataFilter['projs.id'])) {
            $projFilter = Project::getProjectById($dataFilter['projs.id']);
        }
        $dataRisk = Risk::getAllRiskExport($columns, $conditions, $teamIdsAvailable);
        $dataRisk = Risk::filterRisk($dataRisk, $conditions);
        if (count($dataRisk) > 0) {
            $dataRisk = CoreModel::filterGrid($dataRisk, [], $urlFilter, 'LIKE');
            $dataRisk = $dataRisk->get();
        }
        if (!$dataRisk) {
            return back()->with('messages', [
                'errors' => [
                    trans('project::view.There are no risk currently ongoing  to now'),
                ]
            ]);
        }
        Excel::create('Danh sách risk', function ($excel) use ($dataRisk) {
            $excel->sheet('sheet1', function ($sheet) use ($dataRisk) {
                $sheet->loadView('project::task.include.export-risk', [
                    'dataRisk' => $dataRisk
                ]);
            });
        })->export('xlsx');
    }

    public function riskCancel(Request $request)
    {
        $risk = Risk::cancelRisk($request);
        return response()->json($risk);
    }
}
