<?php

namespace Rikkei\Project\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\CookieCore;
use Rikkei\Core\View\View;
use Rikkei\Project\Http\Requests\CreateOperationRequest;
use Rikkei\Project\Model\EmployeeContractMember;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjectAdditional;
use Rikkei\Project\Model\ProjectApprovedProductionCost;
use Rikkei\Project\Model\ProjectKind;
use Rikkei\Project\View\OperationMember;
use Rikkei\Project\View\OperationOverview;
use Rikkei\Project\View\OperationProject;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\CheckpointPermission;
use Rikkei\Team\View\Permission;
use Rikkei\Team\View\TeamList;
use URL;
use Validator;

class OperationController extends Controller
{
    const TYPE_PROJECT = 'projects';
    const TYPE_MEMBER = 'members';
    const TYPE_OVERVIEW = 'overview';

    public function saveCookiesFilter($request, $type)
    {
        // get data request
        $picker['monthFrom'] = $request->monthFrom;
        $picker['monthTo'] = $request->monthTo;
        $picker['teamId'] = $request->teamId;
        $picker['project_type'] = json_decode($request->selectedType);
        $picker['project_state'] = json_decode($request->selectedState);
        $picker['page_limit'] = $request->limit;
        // save filter to cookie
        CookieCore::setRaw('filter.project.' . $type, $picker);
    }

    public function indexOverview()
    {
        $type = self::TYPE_OVERVIEW;
        $route = 'project::operation.overview';
        return $this->index($type, $route);
    }

    public function indexMember()
    {
        $type = self::TYPE_MEMBER;
        $route = 'project::operation.members';
        return $this->index($type, $route);
    }

    public function indexProjects()
    {
        $type = self::TYPE_PROJECT;
        $route = 'project::operation.projects';
        return $this->index($type, $route);
    }

    public function index($type, $route)
    {
        $typesView = [
            self::TYPE_OVERVIEW => 'Overview',
            self::TYPE_MEMBER => 'Members Report',
            self::TYPE_PROJECT => 'Projects Report'
        ];

        $teamIdsAvailable = null;
        $teamTreeAvailable = [];
        $teamIdCurrent = false;
        self::getTeamTreeAvailable($route, $teamTreeAvailable, $teamIdsAvailable, $teamIdCurrent, true);
        Breadcrumb::add(Lang::get('project::view.Operation project'));
        $labelTypeProject = Project::labelTypeProjectFull();
        $curEmp = Permission::getInstance()->getEmployee();
        $teamsOfEmp = CheckpointPermission::getArrTeamIdByEmployee($curEmp->id);
        $labelKindProject = ProjectKind::orderBy('is_other_type')->orderBy('id')->pluck('kind_name','id')->toArray();

        $teamPQAIds = Team::select('id')->where('type', '=', Team::TEAM_TYPE_PQA)->pluck('id')->toArray();

        $checkPerOverview = Permission::getInstance()->isAllow('project::operation.overview');
        $checkPerMember = Permission::getInstance()->isAllow('project::operation.members');
        $checkPerProject = Permission::getInstance()->isAllow('project::operation.projects');

        return view('project::operation.index', [
            'teamPQAIds' => $teamPQAIds,
            'typeViewMain' => $type,
            'typesView' => $typesView,
            'labelTypeProject' => $labelTypeProject,
            'teamIdsAvailable' => $teamIdsAvailable,
            'teamsOfEmp' => $teamsOfEmp,
            'teamTreeAvailable' => $teamTreeAvailable,
            'teamIdCurrent' => $teamIdCurrent,
            'labelKindProject' => $labelKindProject,
            'checkPerOverview' => $checkPerOverview,
            'checkPerMember' => $checkPerMember,
            'checkPerProject' => $checkPerProject,
        ]);
    }

    public function getTeamTreeAvailable($route, &$teamTreeAvailable, &$teamIdsAvailable, &$teamIdCurrent, $checkTeam = false)
    {
        //scope company => view all team
        if (Permission::getInstance()->isScopeCompany(null, $route)) {
            $teamIdsAvailable = true;
        } else {// permission team or self profile.
            $perTeamIds = Permission::getInstance()->isScopeTeam(null, $route);
            if ($perTeamIds) {
                $teamIdsAvailable = (array)Permission::getInstance()->getTeams();
            }
            if (!$teamIdsAvailable || !Permission::getInstance()->isAllow($route)) {
                View::viewErrorPermission();
            }
            if (!$checkTeam) {
                $teamIdsAvailable = array_unique($teamIdsAvailable);
            } else {
                $teamIdsAvailable = array_unique(array_merge($perTeamIds, $teamIdsAvailable));
            }
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
                        if (!$checkTeam) {
                            $teamTreeAvailable = array_merge($teamTreeAvailable, $teamPathTree[$teamId]);
                        }
                    }
                    $teamTreeAvailable = array_merge($teamTreeAvailable, [$teamId]);
                }
                $teamIdsAvailable = array_merge($teamIdsAvailable, $teamIdsChildAvailable);
            }
            if (is_array($teamIdsAvailable) && count($teamIdsAvailable)) {
                $teamIdCurrent = $teamIdsAvailable[0];
                if (count($teamIdsAvailable) == 1) {
                    $teamIdsAvailable = Team::find($teamIdCurrent);
                }
            }
        }
    }

    public function getOperationReports(Request $request)
    {
        switch ($request->typeViewMain) {
            case (self::TYPE_PROJECT):
                return $this->getProjectByMonth($request);
            case (self::TYPE_MEMBER) :
                return $this->getOperationMembers($request);
            case (self::TYPE_OVERVIEW) :
                return $this->getOperationOverview($request);
            default:
                return response()->json([
                    'success' => false
                ]);
        }
    }

    /**
     * project getOperationMembers
     * @param Request $request
     */
    public function getOperationMembers(Request $request)
    {
        $this->saveCookiesFilter($request, self::TYPE_MEMBER);

        $filters = [
            'monthFrom' => date('Y-m-d', strtotime($request->monthFrom)),
            'monthTo' => date('Y-m-t', strtotime($request->monthTo)),
            'url' => $request->url,
            'path' => $request->path,
            'team_id' => $request->teamId
        ];

        $employeePoints = OperationMember::getDataByMonth($filters);
        $employeePointsDimension = OperationMember::convertDataMemberToDimension($employeePoints);

        $collectionMaternity = OperationMember::getMaternityLeaveDayCollection($filters);
        $maternityDataDimension = OperationMember::transformMaternityLeaveDayCollectionToDimension($collectionMaternity);

        return response()->json([
            'employee_points' => $employeePointsDimension,
            'maternity_data' => $maternityDataDimension,
        ]);
    }

    /**
     * project getOperationOverview
     * @param Request $request
     */
    public function getOperationOverview(Request $request)
    {
        $this->saveCookiesFilter($request, self::TYPE_OVERVIEW);

        $filters = [
            'monthFrom' => date('Y-m-d', strtotime($request->monthFrom)),
            'monthTo' => date('Y-m-t', strtotime($request->monthTo)),
            'url' => $request->url,
            'path' => $request->path,
            'team_id' => $request->teamId
        ];

        $employeePoints = OperationMember::getDataByMonth($filters);
        $employeePointsDimension = OperationMember::convertDataMemberToDimension($employeePoints);

        $collectionMaternity = OperationMember::getMaternityLeaveDayCollection($filters);
        $maternityDataDimension = OperationMember::transformMaternityLeaveDayCollectionToDimension($collectionMaternity);

        $projectCost = OperationOverview::getProjectGridData($filters);

        return response()->json([
            'data'  => [
                'member_points' => $employeePointsDimension,
                'project_points' => $projectCost,
                'maternity_data' => $maternityDataDimension,
            ]
        ]);
    }

    /**
     * project getPointUpdateUrl
     * @param Request $request
     */
    public function getPointUpdateUrl(Request $request)
    {
        $dataProcess = EmployeeContractMember::getDataPointUpdate($request);

        return response()->json([
            'result' => $dataProcess
        ]);
    }

    /**
     * project Operation By Month
     */
    public function getProjectByMonth(Request $requests)
    {
        $this->saveCookiesFilter($requests, self::TYPE_PROJECT);

        $filter = [
            'monthFrom' => $requests->monthFrom,
            'monthTo' => $requests->monthTo,
            'url' => $requests->url,
            'path' => $requests->path,
            'team_id' => $requests->teamId,
            'order_by' => $requests->currentSortName,
            'order_dir' => $requests->currentDir,
            'types' => json_decode($requests->selectedType),
            'state' => json_decode($requests->selectedState),
        ];

        try {
            $result = OperationProject::getOperationInformation($filter);
            $paginator = $result['projectItems'];
            $type = self::TYPE_PROJECT;
            $html = view('project::operation.includes.component.pagination', compact('paginator', 'type'))->render();

            return response()->json([
                'message' => trans('project::me.Accepted successful'),
                'status' => true, 'totalMMEachMonth' => $result['totalMMEachMonth'],
                'data' => $paginator->toArray(),
                'html' => $html]);
        } catch (Exception $ex) {
            return response()->json(['errors' => $ex->getMessage(), 'status' => false]);
        }
    }

    /**
     * Create Operation
     */
    public function storeProjectAddition(CreateOperationRequest $request)
    {
        $data = $request->all();
        try {
            $response = ProjectAdditional::insertProjectAdditional($data, true);

            return response()->json(['message' => trans('project::me.Accepted successful'), 'status' => $response]);
        } catch (Exception $ex) {
            return response()->json(['errors' => $ex->getMessage(), 'status' => false]);
        }
    }

    /**
     * Create Operation
     */
    public function deleteProjectAddition(Request $request)
    {
        try {
            $response = ProjectAdditional::deleteProjectAdditional($request->id);

            return response()->json(['message' => trans('project::me.Delete success'), 'status' => $response]);
        } catch (Exception $ex) {
            Log::info($ex);
            return response()->json(['errors' => $ex->getMessage(), 'status' => false]);
        }
    }

    /**
     * Delete item table project_approved_production_cost
     */
    public function deleteOperaionProductionCost(Request $request)
    {
        try {
            $response = ProjectApprovedProductionCost::deleteProjectApprovedCost($request->id);

            return response()->json(['message' => trans('project::me.Delete success'), 'status' => $response]);
        } catch (Exception $ex) {
            Log::info($ex);
            return response()->json(['errors' => $ex->getMessage(), 'status' => false]);
        }
    }

    public function updateProjectCost(Request $request)
    {
        try {
            if ($request->is_future) {
                $response = ProjectAdditional::updateProjectAdditional($request);
            } else {
                $response = ProjectApprovedProductionCost::updatePointProjectApprovedCost($request);
            }

            return response()->json(['message' => trans('project::me.Update success'), 'status' => $response['status'], 'data' => $response['data']]);
        } catch (Exception $ex) {
            Log::info($ex);
            return response()->json(['errors' => $ex->getMessage(), 'status' => false]);
        }
    }

    public function getProjectFuture(Request $request)
    {
        $data = ProjectAdditional::getProjectFutureDetail($request);
        $dataUsingForView = ProjectAdditional::renderDataToDisplayView($data);
        $projectName = $request->name;
        $labelTypeProject = Project::labelTypeProjectFull();
        $curEmp = Permission::getInstance()->getEmployee();
        $teamsOfEmp = CheckpointPermission::getArrTeamIdByEmployee($curEmp->id);
        $typeProject = $request->type;
        $teamsOptionAll = TeamList::toOption(null, true, false);
        $labelKindProject = ProjectKind::orderBy('is_other_type')->orderBy('id')->pluck('kind_name','id')->toArray();
        $kindProject = $request->kind_id;

        $teamIdsAvailable = null;
        $teamTreeAvailable = [];
        $teamIdCurrent = false;
        $route = 'project::operation.projects';
        self::getTeamTreeAvailable($route, $teamTreeAvailable, $teamIdsAvailable, $teamIdCurrent);
        $unitPrices = ProjectApprovedProductionCost::getUnitPrices();

        $html =  view('project::operation.includes.component.operation-project-detail', compact(
            'dataUsingForView', 'projectName', 'labelTypeProject', 'teamsOfEmp', 'typeProject', 'teamsOptionAll',
            'teamIdsAvailable', 'teamTreeAvailable', 'labelKindProject', 'kindProject', 'unitPrices'
        ));

        $data = $html->render();

        return response()->json(['message' => trans('project::me.Update success'), 'data' => $data, 'status' => 200]);
    }
}
