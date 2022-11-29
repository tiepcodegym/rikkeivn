<?php

namespace Rikkei\Me\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rikkei\Me\Model\ME;
use Rikkei\Project\Model\Project;
use Rikkei\Me\Model\Attribute;
use Rikkei\Me\Model\Point as MePoint;
use Rikkei\Me\Model\Comment as MeComment;
use Rikkei\Me\View\View as MeView;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\Employee;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Validator;

class MeController extends Controller
{
    public function __construct() {
        parent::__construct();
        Breadcrumb::add(trans('me::view.Monthly Evaluation'), route('me::proj.edit'));
        if (request() && request()->route()) {
            $routeName = request()->route()->getName();
        } else {
            $routeName = null;
        }
        if ($routeName == 'me::profile.confirm') {
            Menu::setActive('profile');
        } else {
            Menu::setActive('team');
        }
    }

    /*
     * render edit view
     */
    public function edit()
    {
        Breadcrumb::add(trans('me::view.Create'));
        return view('me::edit');
    }

    /**
     * loading projects of PM
     * @param Request $request
     * @return array
     */
    public function getProjectsOfPM(Request $request)
    {
        $search = $request->get('q');
        $config = $request->except('q');
        return ME::getInstance()->getProjectsOfPM(null, $search, $config);
    }

    /**
     * get Months of project
     * @param Request $request
     * @return string|array
     */
    public function getMonthsOfProject(Request $request)
    {
        $projId = $request->get('project_id');
        $response = [
            'months' => [],
            'teams' => '',
        ];
        if (!$projId) {
            return $response;
        }
        $project = Project::find($projId);
        if (!$project) {
            return $response;
        }

        $response['months'] = ME::getInstance()->listMonthsOfProject($project);
        $projTeams = $project->teamProject;
        if ($projTeams->isEmpty()) {
            return $response;
        }
        $response['teams'] = trans('me::view.Team') . ': ' . $projTeams->implode('name', ', ');
        return $response;
    }

    /*
     * get members of project
     */
    public function getMembersOfProject(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'project_id' => 'required',
            'month' => 'required'
        ]);
        if ($valid->fails()) {
            return response()->json(trans('me::view.No result'), 422);
        }
        $getFields = $request->get('fields');
        $projectId = $request->get('project_id');
        $time = $request->get('month');
        $project = Project::find($projectId);
        if (!$project) {
            return response()->json(trans('me::view.No result'), 422);
        }
        $groupLeader = $project->groupLeader;
        if (!$groupLeader) {
            return response()->json(trans('me::view.Project not have group directory'), 422);
        }
        $dataMembers = ME::getInstance()->getMembersOfProject($project, $time);
        $rangeTime = $dataMembers['range_time'];
        $members = $dataMembers['members'];

        //return range time
        $response = [
            'range_time' => array_map(function ($element) {
                return $element->format('Y-m-d');
            }, $rangeTime)
        ];
        if (isset($getFields['attributes']) && $getFields['attributes']) {
            $response['attributes'] = Attribute::getInstance()->getAttrsByGroup([Attribute::GR_NEW_PERFORM, Attribute::GR_NEW_NORMAL]);
        }

        if ($members->isEmpty()) {
            return response()->json([
                'message' => trans('me::view.No result'),
                'status' => 0
            ], 404);
        }

        $items = ME::getInstance()->insertOrUpdateProjMembers($members, $project, $time);
        if (!$items) {
            return response()->json(trans('me::view.An error occurred'), 500);
        }

        $evalIds = collect($items)->pluck('id')->toArray();
        $response['items'] = $items;
        $response['attrPoints'] = MePoint::getInstance()->getPointByEvalIds($evalIds);
        $response['commentClasses'] = MeComment::getInstance()->getEvalCommentClass($evalIds);
        $response['attrsCommented'] = MeComment::getInstance()->listAttrsCommented($evalIds);
        $response['leaderId'] = $groupLeader->id;

        return $response;
    }

    /*
     * save attributes points
     */
    public function savePoint(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'eval_points' => 'required',
        ]);
        if ($valid->fails()) {
            return response()->json(trans('me::view.Not found data'), 422);
        }
        $evalPoints = $request->get('eval_points');;

        DB::beginTransaction();
        try {
            $results = ME::getInstance()->savePoints($evalPoints);
            DB::commit();
            return $results;
        } catch (\Exception $ex) {
            DB::rollback();
            $message = $ex->getMessage();
            if ($ex->getCode() != 422) {
                $message = trans('me::view.Save error, please try again laster');
            }
            return response()->json($message, 422);
        }
    }

    /*
     * submit items
     */
    public function submit(Request $request)
    {
        $evalIds = $request->get('eval_ids');
        if ($evalIds) {
            $checkRequireComment = ME::getInstance()->checkEvalRequireComment($evalIds);
            if (!$checkRequireComment['check']) {
                return response()->json([
                    'eval_require_comment' => $checkRequireComment['eval_ids']
                ]);
            }
        }
        return (new \Rikkei\Project\Http\Controllers\MeEvalController())->update($request);
    }

    /*
     * render list review page
     */
    public function listReview()
    {
        Breadcrumb::add(trans('me::view.Review'));
        return view('me::review');
    }

    /*
     * get review page data
     */
    public function getReviewData(Request $request)
    {
        $getFields = $request->get('fields');
        $data = $request->except(['_token', 'fields']);
        if (!isset($data['filter'])) {
            $data['filter'] = [];
        }
        $meTbl = 'me_evaluations';
        $collectionModel = ME::getInstance()->getReviewItems($data);
        //get baseline date each month
        $listRangeMonths = MeView::listRangeBaselineDate(array_keys($collectionModel->pluck('id', 'eval_month')->toArray()));
        $evalIds = $collectionModel->pluck('id')->toArray();
        $resData = [
            'items' => $collectionModel,
            'listRangeMonths' => $listRangeMonths,
            'attrPoints' => MePoint::getInstance()->getPointByEvalIds($evalIds),
            'commentClasses' => MeComment::getInstance()->getEvalCommentClass($evalIds),
            'attrsCommented' => MeComment::getInstance()->listAttrsCommented($evalIds),
            'statistics' => ME::getInstance()->getReviewItems($data, 'statistic'),
            'totalMember' => ME::getInstance()->getReviewItems($data, 'totalEvaluated'),
        ];
        if (isset($getFields['attributes']) && $getFields['attributes']) {
            $resData['attributes'] = Attribute::getInstance()->getAttrsByGroup([Attribute::GR_NEW_NORMAL, Attribute::GR_NEW_PERFORM]);
        }
        if (isset($getFields['filterTeams']) && $getFields['filterTeams']) {
            $resData['filterTeams'] = ME::getInstance()->getTeamPermissOptions('me::review.list');
        }
        if (isset($getFields['hasPermissDelete']) && $getFields['hasPermissDelete']) {
            $resData['hasPermissDelete'] = Permission::getInstance()->isAllow('project::me.delete_item');
        }
        $filterEmpId = CoreForm::getFilterData('number', $meTbl . '.employee_id', $data['filter']);
        if ($filterEmpId) {
            $resData['filterEmployee'] = Employee::find($filterEmpId, ['id', 'email']);
        }
        $filterProjId = CoreForm::getFilterData('excerpt', 'project_id', $data['filter']);
        if ($filterProjId) {
            $resData['filterProject'] = ME::getInstance()->getProjectOrTeam($filterProjId);
        }

        return $resData;
    }

    /*
     * get project not evaluate
     */
    public function getProjsNotEval(Request $request)
    {
        $data = $request->except('_token');
        if (!isset($data['filter'])) {
            $data['filter'] = [];
        }
        return [
            'projNotEval' => ME::getInstance()->collectNeedEval(
                null,
                $data['filter'],
                'notEvaluate'
            )->groupBy('project_id')->toArray()
        ];
    }

    /*
     * update status item
     */
    public function updateStatus(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required',
            'type' => 'required|in:leader,staff',
            'status' => 'required|in:' . ME::STT_FEEDBACK . ',' . ME::STT_APPROVED,
        ]);
        if ($valid->fails()) {
            return response()->json(trans('me::view.Invalid data'), 422);
        }
        $type = $request->get('type');
        $evalId = $request->get('id');
        $controller = new \Rikkei\Project\Http\Controllers\MeEvalController();

        if ($type == 'leader') {
            return $controller->leaderUpdate($evalId, $request);
        }
        if ($type == 'staff') {
            return $this->staffUpdate($evalId, $request);
        }
    }

    public function multiUpdateStatus(Request $request)
    {
        return (new \Rikkei\Project\Http\Controllers\MeEvalController())->multiActions($request);
    }

    /*
     * delete item
     */
    public function deleteItem(Request $request)
    {
        $id = $request->get('id');
        return (new \Rikkei\Project\Http\Controllers\MeEvalController())->delete($id, $request);
    }

    /*
     * render list ME for employees
     */
    public function listConfirm()
    {
        Breadcrumb::add(trans('me::view.Evaluation'));
        return view('me::confirm-list');
    }

    /*
     * load confirm data
     */
    public function getConfirmData(Request $request)
    {
        $getFields = $request->get('fields');
        $data = $request->except(['_token', 'fields']);
        if (!isset($data['filter'])) {
            $data['filter'] = [];
        }
        $collectionModel = ME::getInstance()->getConfirmItems($data);
        //get baseline date each month
        $listRangeMonths = MeView::listRangeBaselineDate(array_keys($collectionModel->pluck('id', 'eval_month')->toArray()));
        $evalIds = $collectionModel->pluck('id')->toArray();
        $resData = [
            'items' => $collectionModel,
            'listRangeMonths' => $listRangeMonths,
            'attrPoints' => MePoint::getInstance()->getPointByEvalIds($evalIds),
            'commentClasses' => MeComment::getInstance()->getEvalCommentClass($evalIds),
            'attrsCommented' => MeComment::getInstance()->listAttrsCommented($evalIds),
        ];
        if (isset($getFields['attributes']) && $getFields['attributes']) {
            $resData['attributes'] = Attribute::getInstance()->getAttrsByGroup([Attribute::GR_NEW_NORMAL, Attribute::GR_NEW_PERFORM]);
        }
        if (isset($getFields['listProjsOfEmp']) && $getFields['listProjsOfEmp']) {
            $resData['listProjsOfEmp'] = ME::getInstance()->getProjectsOfEmployee();
        }
        $filterProjId = CoreForm::getFilterData('excerpt', 'project_id', $data['filter']);
        if ($filterProjId) {
            $resData['filterProject'] = ME::getInstance()->getProjectOrTeam($filterProjId);
        }

        return $resData;
    }

    /*
     * staff update status
     */
    public function staffUpdate(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required',
            'status' => 'required|in:' . ME::STT_FEEDBACK . ',' . ME::STT_CLOSED,
        ]);
        if ($valid->fails()) {
            return response()->json(trans('me::view.Invalid data'), 422);
        }
        $evalId = $request->get('id');
        $status = $request->get('status');
        $scope = Permission::getInstance();
        $evalItem = ME::find($evalId, ['assignee', 'project_id', 'team_id']);
        if (!$evalItem) {
            return response()->json(trans('me::view.No data'), 404);
        }
        return (new \Rikkei\Project\Http\Controllers\MeEvalController())->updateStatus($evalId, $status, false, $scope);
    }

    public function viewMember()
    {
        Breadcrumb::add(trans('me::view.View member of team'));
        return view('me::view-member');
    }

    /*
     * load view member data
     */
    public function getViewMemberData(Request $request)
    {
        $getFields = $request->get('fields');
        $data = $request->except(['_token', 'fields']);
        if (!isset($data['filter'])) {
            $data['filter'] = [];
        }
        $collectionModel = ME::getInstance()->getViewMemberItems($data);
        //get baseline date each month
        $listRangeMonths = MeView::listRangeBaselineDate(array_keys($collectionModel->pluck('id', 'eval_month')->toArray()));
        $evalIds = $collectionModel->pluck('id')->toArray();
        $resData = [
            'items' => $collectionModel,
            'listRangeMonths' => $listRangeMonths,
            'attrPoints' => MePoint::getInstance()->getPointByEvalIds($evalIds),
            'commentClasses' => MeComment::getInstance()->getEvalCommentClass($evalIds),
            'attrsCommented' => MeComment::getInstance()->listAttrsCommented($evalIds),
            'isScopeCompany' => Permission::getInstance()->isScopeCompany(null, 'me::view.member.index'),
        ];
        if (isset($getFields['attributes']) && $getFields['attributes']) {
            $resData['attributes'] = Attribute::getInstance()->getAttrsByGroup([Attribute::GR_NEW_NORMAL, Attribute::GR_NEW_PERFORM]);
        }
        if (isset($getFields['filterTeams']) && $getFields['filterTeams']) {
            $resData['filterTeams'] = ME::getInstance()->getTeamPermissOptions('me::review.list');
        }
        $filterProjId = CoreForm::getFilterData('excerpt', 'project_id', $data['filter']);
        if ($filterProjId) {
            $project = Project::find($filterProjId);
            $projTeams = $project ? $project->teamProject : null;
            $resData['teamName'] = ($projTeams && !$projTeams->isEmpty()) ? trans('me::view.Team') . ': ' . $projTeams->implode('name', ', ') : null;
            $resData['filterProject'] = ME::getInstance()->getProjectOrTeam($filterProjId);
        }

        return $resData;
    }
}
