<?php

namespace Rikkei\Tag\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\TeamConst;
use Rikkei\Team\Model\Employee;
use Rikkei\Project\Model\Project;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Rikkei\Resource\Model\Programs;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Lang;
use Rikkei\Tag\Model\ProjectTag;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Http\Requests\AddProjectMemberRequest;
use Rikkei\Project\Model\ProjectMemberProgramLang;
use Rikkei\Tag\Model\Tag;
use Rikkei\Tag\View\TagConst;
use Rikkei\Tag\Model\Field;
use Rikkei\Tag\Model\TagValue;
use Rikkei\Team\View\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Tag\View\TagGeneral;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\Model\Permission as PermissionModel;
use Rikkei\Core\View\Menu;
use Rikkei\Project\Model\ProjectMeta;
use Illuminate\Support\Facades\Session;

class ProjectController extends Controller 
{
    
    protected $scope;
    
    /**
     * 
     */
    public function _construct() {
        $this->scope = Permission::getInstance();
    }

    /**
     * project list
     */
    public function index()
    {
        if (!$this->scope->isAllow(TagConst::ROUTE_VIEW_PROJ_TAG)) {
            CoreView::viewErrorPermission();
        }
        Menu::setActive('project', 'tag/field/manage');
        Breadcrumb::add(trans('tag::view.Project tag'));
        return view('tag::object.project.index', [
            'fieldsPath' => Field::getFieldPath(
                    TagConst::SET_TAG_PROJECT, 
                    [TagConst::FIELD_TYPE_TAG]
                ),
            'permissSubmit' => $this->scope
                ->isAllow(TagConst::ROUTE_SUBMIT_PROJ_TAG) ? 1 : 0,
            'permissApprove' => $this->scope
                ->isAllow(TagConst::ROUTE_APPROVE_PROJ_TAG) ? 1 : 0,
            'permissViewDetail' => $this->scope
                ->isAllow(TagConst::ROUTE_VIEW_PROJ_DETAIL) ? 1 : 0,
            'permissProjOldEdit' => $this->scope
                ->isAllow(TagConst::RA_PROJ_OLD_EDIT) ? 1 : 0,
            'isReview' => 0
        ]);
    }
    
    /**
     * get data normal to create project
     *    pm, group, leader...
     */
    public function dataNormal()
    {
        if (!$this->scope->isAllow(TagConst::ROUTE_VIEW_PROJ_DETAIL)) {
            return CoreView::viewErrorPermission();
        }
        
        $response = [];
        $response['success'] = 1;
        $response['team'] = Team::getTeamPathTree();
        $response['pm'] = Employee::getAllPM();
        $response['project_type'] = Project::labelTypeProject();
        $response['lang'] = Programs::getListOption();
        $response['member_types'] = ProjectMember::getTypeMember();
        $response['member_type_avai_lang'] = 
            ProjectMemberProgramLang::getTypeMemberAvaiLang();
        if ($this->scope->isScopeCompany(null, TagConst::RA_PROJ_OLD_EDIT)) {
            $response['scope_proj_old_edit'] = PermissionModel::SCOPE_COMPANY;
        } elseif ($this->scope->isScopeTeam(null, TagConst::RA_PROJ_OLD_EDIT)) {
            $response['scope_proj_old_edit'] = PermissionModel::SCOPE_TEAM;
            $response['my_team'] = $this->scope->getTeams();
        } else {
            $response['scope_proj_old_edit'] = PermissionModel::SCOPE_NONE;
        }
        return response()->json($response);
    }
    
    /**
     * create post project
     */
    public function create()
    {
        if (!$this->scope->isAllow(TagConst::RA_PROJ_OLD_EDIT)) {
            return CoreView::viewErrorPermission();
        }
        
        $response = [];
        $validator = Validator::make(
            Input::get(), $this->getRuleProjectValidate(Input::get())
        );
        if ($validator->fails()) {
            $response['success'] = 0;
            $response['message'] = $validator->errors()->first();
            return response()->json($response);
        }
        try {
            $dataProject = array_filter(Input::get('base'));
            $dataProject['status'] = Project::STATUS_OLD;
            $dataProject['state'] = Project::STATE_CLOSED;
            $dataProject['description'] = 'old';
            $dataProject['tag_status'] = TagConst::TAG_STATUS_ASSIGNED;
            $dataProject['tag_assignee'] = $dataProject['manager_id'];
            unset($dataProject['project_code_auto']);
            $project = ProjectTag::createProj([
                'base' => $dataProject,
                'team_id' => Input::get('team.ids'),
                'sale_id' => Input::get('sale.ids'),
                'prog_langs' => Input::get('lang.ids'),
                'quality' => Input::get('quality')
            ]);
            $response['id'] = $project->id;
            $response['success'] = 1;
            $response['project'] = [
                'id' => $project->id, 
                'name' => $project->name, 
                'tag_status' => TagConst::TAG_STATUS_ASSIGNED,
                'status' => $project->status
            ];
            $response['message'] = Lang::get('tag::message.Save data success');
            return response()->json($response);
        } catch (Exception $ex) {
            $response['success'] = 0;
            $response['message'] = Lang::get('core::message.Error system');
            Log::info($ex);
            return response()->json($response);
        }
    }
    
    /**
     * check exists data of project
     */
    public function checkexists()
    {
        $arrayName = [
            'name',
            'project_code'
        ];
        if (!in_array(Input::get('col'), $arrayName)) {
            return 0;
        }
        $tableProject = Project::getTableName();
        // col , name, id
        $validator = Validator::make(Input::get(), [
            'value' => 'required|max:255|unique:'.$tableProject.
                ','.Input::get('col').','.Input::get('id').',id,deleted_at,NULL',
            'col' => 'required|max:255',
        ]);
        if ($validator->fails()) {
            return 0;
        }
        return 'true';
    }
    
    /**
     * save single field project
     */
    protected function saveInput()
    {
        $response = [];
        $project = $this->getProject();
        if (is_array($project) && 
            isset($project['success']) && 
            !$project['success']
        ) {
            return response()->json($project);
        }
        if (!TagGeneral::isAllowEditOldProj($project)) {
            Session::flash(
                'messages', [
                        'errors'=> [
                            Lang::get('core::message.You don\'t have access'),
                        ]
                    ]
            );
            return response()->json([
                'message' => Lang::get('core::message.You don\'t have access'),
                'success' => 0,
                'reload' => 1
            ]);
        }
        $colGroup = Input::get('colGroup');
        $colName = Input::get('colName');
        $value = Input::get('value');
        if (!$colGroup || !$colName) {
            $response['success'] = 0;
            $response['message'] = Lang::get('core::message.Not found item');
            return response()->json($response);
        }
        //validator
        $dataValidator = [
            $colGroup => [
                $colName => $value
            ]
        ];
        if ($colGroup === 'team' && $colName === 'ids') {
            $leaderId = Input::get('relate.leader_id');
            $dataValidator['base']['leader_id'] = $leaderId;
        } else {
            $leaderId = null;
        }
        $validator = Validator::make(
            $dataValidator, 
            $this->getRuleProjectValidate($dataValidator)
        );
        if ($validator->fails()) {
            $response['success'] = 0;
            $response['message'] = $validator->errors()->first();
            return response()->json($response);
        }
        try {
            switch ($colGroup) {
                case 'base':
                    if ($colName === 'type_mm') {
                        $option = ['type_resource' => true];
                    } else {
                        $option = [];
                    }
                    ProjectTag::saveBase($project, [
                        $colName => $value
                    ], $option);
                    break;
                case 'team':
                    ProjectTag::saveTeam($project, $value, $leaderId);
                    break;
                case 'sale':
                    ProjectTag::saveSale($project, $value);
                    break;
                case 'lang':
                    $return = ProjectTag::saveLang($project, $value);
                    if (isset($return['status']) &&
                        $return['status'] === false
                    ) {
                        $response['success'] = 0;
                        $response['message'] = $return['message_error']['prog_langs'];
                        return response()->json($response);
                    }
                    break;
                case 'quality':
                    ProjectTag::saveQuality($project, [
                        $colName => $value
                    ]);
                    break;
                case 'scope':
                    ProjectMeta::saveFromProject($project, [
                        $colName => $value
                    ]);
                    break;
                default:
                    break;
            }
            $response['success'] = 1;
            $response['message'] = Lang::get('tag::message.Save data success');
            return response()->json($response);
        } catch (Exception $ex) {
            $response['success'] = 0;
            $response['message'] = Lang::get('core::message.Error system');
            Log::info($ex);
            return response()->json($response);
        }        
    }

    
    /**
     * get rule validate
     * 
     * @return array
     */
    protected function getRuleProjectValidate($data)
    {
        $tableProject = Project::getTableName();
        $rules = [];
        if (isset($data['base']['name'])) {
            $rules['base.name'] = 'required|max:255|unique:'.$tableProject.
                ',name,'.Input::get('id').',id,deleted_at,NULL';
        }
        if (isset($data['base']['project_code'])) {
            $rules['base.project_code'] = 'required|max:255|unique:'.$tableProject.
                ',name,'.Input::get('id').',id,deleted_at,NULL';
        }
        if (isset($data['base']['leader_id'])) {
            $rules['base.leader_id'] = 'required';
        }
        if (isset($data['base']['manager_id'])) {
            $rules['base.manager_id'] = 'required';
        }
        if (isset($data['base']['type'])) {
            $rules['base.type'] = 'required';
        }
        if (isset($data['base']['type_mm'])) {
            $rules['base.type_mm'] = 'required';
        }
        if (isset($data['base']['end_at'])) {
            $rules['base.end_at'] = 'required|date_format:Y-m-d';
        }
        if (isset($data['base']['start_at'])) {
            $rules['base.start_at'] = 'required|date_format:Y-m-d';
            if (isset($rules['base.end_at'])) {
                $rules['base.end_at'] .= '|after:base.start_at';
            }
        }
        if (isset($data['team']['ids'])) {
            $rules['team.ids'] = 'required';
        }
        if (isset($data['lang']['ids'])) {
            $rules['lang.ids'] = 'required';
        }
        if (isset($data['quality']['billable_effort'])) {
            $rules['quality.billable_effort'] = 'required|numeric|min:0';
        }
        if (isset($data['quality']['plan_effort'])) {
            $rules['quality.plan_effort'] = 'required|numeric|min:0';
        }
        return $rules;
    }
    
    /**
     * get data project
     * 
     * format json
     *  base: {},
        sale: {
            ids: []
        },
        team: {
            ids: []
        },
        lang: {
            ids: []
        },
        quality: {}
     */
    public function getDataItem()
    {
        if (!$this->scope->isAllow(TagConst::ROUTE_VIEW_PROJ_DETAIL)) {
            return CoreView::viewErrorPermission();
        }
        
        $response = [];
        $project = $this->getProject('id', false);
        if (is_array($project) && 
            isset($project['success']) && 
            !$project['success']
        ) {
            return response()->json($project);
        }
        $id = (int) $project->id;
        $response['project']['id'] = $id;
        // get base
        $response['project']['base'] = [
            'cust_contact_id' => (int) $project->cust_contact_id,
            'manager_id' => (int) $project->manager_id,
            'leader_id' => (int) $project->leader_id,
            'status' => (int) $project->status,
            'type_mm' => (int) $project->type_mm,
            'name' => $project->name,
            'project_code' => $project->project_code,
            'start_at' => preg_replace('/\s.*/', '', $project->start_at),
            'end_at' => preg_replace('/\s.*/', '', $project->end_at),
            'type' => (int) $project->type,
            'state' => (int) $project->state,
        ];
        /*
         * info old project and new project
         */
        $response['customer_name'] = ProjectTag::getCustomerName($project);
        $response['leader_pm'] = ProjectTag::getLeaderPMName($project);
        // get sale
        $response['sales'] = ProjectTag::getSales($project);
        $response['project']['sale']['ids'] = $response['sales'] ? 
                array_keys($response['sales']) : [];
        // get team
        $response['project']['team']['ids'] = ProjectTag::getTeamIdsOfProj($project);
        // get lang
        $response['project']['lang']['ids'] = ProjectTag::getLangIds($project);
        // get billable, plan of quality
        $response['project']['quality'] = ProjectTag::getQuaEffort($project);
        
        $response['success'] = 1;
        return response()->json($response);
    }
    
    /**
     * save member
     */
    public function memberSave()
    {
        $project = $this->getProject('project_id');
        if (is_array($project) && 
            isset($project['success']) && 
            !$project['success']
        ) {
            return response()->json($project);
        }
        if (!TagGeneral::isAllowEditOldProj($project)) {
            return CoreView::viewErrorPermission();
        }
        $data = Input::get();
        if (!Input::get('id')) {
            $data['isAddNew'] = true;
        }
        $validator = AddProjectMemberRequest::validateData($data, $project);
        if ($validator->fails()) {
            $response['success'] = 0;
            $response['message'] = $validator->errors()->first();
            return response()->json($response);
        }
        try {
            $member = ProjectTag::saveMember($project, (array) $data);
            $response['member'] = [
                'flat_resource' => $member->flat_resource,
                'id' => $member->id
            ];
            $response['success'] = 1;
            $response['message'] = Lang::get('tag::message.Save data success');
            return response()->json($response);
        } catch (Exception $ex) {
            $response['success'] = 0;
            $response['message'] = Lang::get('core::message.Error system');
            Log::info($ex);
            return response()->json($response);
        }
        return response()->json($response);
    }
    
    /**
     * get data item member
     */
    public function getDataItemMember()
    {
        if (!$this->scope->isAllow(TagConst::ROUTE_VIEW_PROJ_DETAIL)) {
            return CoreView::viewErrorPermission();
        }
        
        $response = [];
        $project = $this->getProject('id', false);
        if (is_array($project) && 
            isset($project['success']) && 
            !$project['success']
        ) {
            return response()->json($project);
        }
        $response['relate']['member'] = ProjectTag::getMembersOfProj($project);
        $response['success'] = 1;
        return response()->json($response);
    }
    
    /**
     * save member
     */
    public function memberDelete()
    {
        $project = $this->getProject('project_id');
        if (is_array($project) && 
            isset($project['success']) && 
            !$project['success']
        ) {
            return response()->json($project);
        }
        if (!TagGeneral::isAllowEditOldProj($project)) {
            return CoreView::viewErrorPermission();
        }
        $id = Input::get('id');
        if (!$id || !is_numeric($id)) {
            $response['success'] = 0;
            $response['message'] = Lang::get('core::message.Not found item');
            return response()->json($response);
        }
        try {
            ProjectTag::deleteMember($project, $id);
            $response['success'] = 1;
            $response['message'] = Lang::get('tag::message.Delete member success');
            return response()->json($response);
        } catch (Exception $ex) {
            $response['success'] = 0;
            $response['message'] = Lang::get('core::message.Error system');
            Log::info($ex);
            return response()->json($response);
        }
    }
    
    /**
     * get project item
     * 
     * @param string $key primary key
     * @param boolean $isCheckStatus check status old? 
     * @return array|model
     */
    protected function getProject($key = 'id', $isCheckStatus = true)
    {
        $response = [];
        $id = Input::get($key);
        if (!$id || !is_numeric($id)) {
            $response['success'] = 0;
            $response['message'] = Lang::get('core::message.Not found item');
            return $response;
        }
        $project = Project::find($id);
        if (!$project) {
            $response['success'] = 0;
            $response['message'] = Lang::get('core::message.Not found item');
            return $response;
        }
        if ($isCheckStatus && !in_array($project->status, [
            Project::STATUS_OLD
        ])) {
            $response['success'] = 0;
            $response['message'] = Lang::get('core::message.Not found item');
            return $response;
        }
        return $project;
    }
    
       
    /**
     * get list projects
     */
    public function dataList (Request $request) {
        if (!$this->scope->isAllow(TagConst::ROUTE_VIEW_PROJ_TAG)) {
            return CoreView::viewErrorPermission();
        }
        return ProjectTag::getDataList($request->all(), TagConst::ROUTE_VIEW_PROJ_TAG);
    }
    
    /**
     * get project to edit tags
     * @param Request $request
     * @return type
     */
    public function getEditTags (Request $request) {
        if (!$this->scope->isAllow(TagConst::ROUTE_VIEW_PROJ_DETAIL)) {
            return CoreView::viewErrorPermission($request);
        }
        
        $valid = Validator::make($request->all(), [
            'proj_id' => 'required|numeric',
            'field_id' => 'required|numeric'
        ]);
        $projectId = $request->get('proj_id');
        $fieldId = $request->get('field_id');
        if ($valid->fails()) {
            return response()->json(['message' => trans('tag::message.Not found item')], 422);
        }
        $result = ProjectTag::getTagsFromId($projectId, [$fieldId]);
        return response()->json([
            'tags' => $result,
            'permissUpdateTag' => (TagGeneral::canDoProject($projectId, TagConst::ROUTE_SUBMIT_PROJ_TAG, TagConst::ACTION_SUBMIT)
                || TagGeneral::canDoProject($projectId, TagConst::ROUTE_APPROVE_PROJ_TAG, TagConst::ACTION_APPROVE)) 
        ]);
    }
    
    /**
     * search tags
     * @param Request $request
     */
    public function suggestTags (Request $request) {
        if (!$this->scope->isAllow(TagConst::ROUTE_SUBMIT_PROJ_TAG) 
                && !$this->scope->isAllow(TagConst::ROUTE_APPROVE_PROJ_TAG)) {
            return CoreView::viewErrorPermission();
        }
        
        $fieldId = $request->get('field_id');
        if ($fieldId) {
            $fieldIds = [$fieldId];
        } else {
            $fieldIds = Field::getChildIds(TagConst::SET_TAG_PROJECT);
        }
        $excerptIds = $request->get('excerpt_ids');
        $listTags = Tag::searchTags($fieldIds, $request->get('term'), [
            'excerpt' => $excerptIds
        ]);
        return $listTags->lists('value')->toArray();
    }

    /**
     * save assignee
     * @param Request $request
     * @return type
     */
    public function saveAssignee(Request $request) {
	$valid = Validator::make($request->all(), [
		'project_id' => 'required',
		'assignee_id' => 'required'
	]);
	if ($valid->fails()) {
		return response()->json(['message' => trans('tag::message.Not found item')], 422);
	}
	$projectId = $request->get('project_id');
        if (!is_array($projectId)) {
            $projectId = [$projectId];
        }
        foreach ($projectId as $pId) {
            if (!TagGeneral::canDoProject($pId, TagConst::ROUTE_APPROVE_PROJ_TAG, TagConst::ACTION_ASSIGN)) {
                return CoreView::viewErrorPermission();
            }
        }
        
	$assigneeId = $request->get('assignee_id');
        $eplAssignee = explode(':', $assigneeId);
        if (count($eplAssignee) > 1) {
            $assigneeId = $eplAssignee[1];
        }
        $projects = ProjectTag::getWithAssignee($projectId);
	$employee = Employee::find($assigneeId, ['id', 'email', 'name']);
	if ($projects->isEmpty() || !$employee) {
		return response()->json(['message' => trans('tag::message.Not found item')], 422);
	}
        
        DB::beginTransaction();
        try {
            foreach ($projects as $project) {
                //change assignee
                $project->tag_status = TagConst::TAG_STATUS_ASSIGNED;
                $project->tag_assignee = $assigneeId;
                $project->save();

                //send mail assignee
                if ($project->assignee_id != $employee->id) {
                    $dataMail = [
                        'dear_name' => $employee->name,
                        'project_name' => $project->name,
                        'project_id' => $project->id,
                        'submit_name' => Permission::getInstance()->getEmployee()->name,
                        'old_assignee' => $project->assignee_name,
                        'new_assignee' => $employee->name
                    ];
                    $mailAssignee = new EmailQueue();
                    $mailAssignee->setTo($employee->email);
                    if ($project->assignee_email) {
                        $mailAssignee->addCc($project->assignee_email)
                                ->addCcNotify($project->assignee_id);
                    }
                    $mailAssignee->setTemplate('tag::mail.tag_assignee', $dataMail)
                            ->setSubject(trans('tag::view.Mail subject assignee', ['project' => $project->name]))
                            ->setNotify(
                                $employee->id,
                                null,
                                route('tag::object.project.index', ['project_ids' => $project->id]).'#show'.$project->id,
                                ['category_id' => RkNotify::CATEGORY_PROJECT]
                            )
                            ->save();
                }
            }
            DB::commit();
            
            return response()->json([
                    'assignee_id' => $assigneeId,
                    'assignee_name' => $employee->email,
                    'tag_status' => $projects->first()->tag_status,
                    'message' => trans('tag::message.Save data success')
                ]);
        } catch (\Exception $ex) {
            Log::info($ex);
            DB::rollback();
            return response()->json([
               'message' => trans('core::message.Error system')
            ], 500);
        }
    }
    
    /**
     * submit tags
     * @param Request $request
     * @return type
     */
    public function submitTags (Request $request) {
        $projectId = $request->get('project_id');
        if (!$projectId) {
            return response()->json(['message' => trans('tag::message.Not found item')], 422);
        }
        if (!is_array($projectId)) {
            $projectId = [$projectId];
        }
        //check permission
        foreach ($projectId as $pId) {
            if (!TagGeneral::canDoProject($pId, TagConst::ROUTE_SUBMIT_PROJ_TAG, TagConst::ACTION_SUBMIT)) {
                Session::flash(
                    'messages', [
                            'errors'=> [
                                Lang::get('core::message.You don\'t have access'),
                            ]
                        ]
                );
                return response()->json([
                    'message' => trans('core::message.You don\'t have access'),
                    'success' => 0,
                    'reload' => 1
                ]);
            }
        }
        
        //find projects
        $projects = ProjectTag::getWithLeader($projectId);
        if ($projects->isEmpty()) {
            return response()->json(['message' => trans('tag::message.Not found item or project not have leader')], 422);
        }
        //check permiss approve
        $permissApprove = $this->scope->isAllow(TagConst::ROUTE_APPROVE_PROJ_TAG);
        
        DB::beginTransaction();
        try {
            $currentUser = Permission::getInstance()->getEmployee();
            $projectsLeader = $projects->groupBy('leader_id');
            $fieldIds = Field::getChildIds(TagConst::SET_TAG_PROJECT);
            foreach ($projectsLeader as $arrProjects) {
                $projectNames = [];
                $projectIds = [];
                $isSubmited = false;
                foreach ($arrProjects as $project) {
                    if ($project->tag_status == TagConst::TAG_STATUS_APPROVE && !$permissApprove) {
                        continue;
                    }
                    if ($project->tag_status == TagConst::TAG_STATUS_REVIEW && !$isSubmited) {
                        $isSubmited = true;
                    }
                    //if has permission approve set status approve
                    if ($permissApprove) {
                        $project->tag_status = TagConst::TAG_STATUS_APPROVE;
                        $project->tag_assignee = null;
                    } else {
                        $project->tag_status = TagConst::TAG_STATUS_REVIEW;
                    }
                    //submit not assign to leader?
                    $project->save();
                    $projectNames[] = $project->name;
                    $projectIds[] = $project->id;
                    //set tag status
                    $project->submitDraftTags($fieldIds);
                }
                //send mail to leader.
                $leaderInfo = $arrProjects[0];
                
                if ($leaderInfo->leader_email) {
                    $projectNames = implode(', ', $projectNames);
                    $dataMail = [
                        'dear_name' => $leaderInfo->leader_name,
                        'project_names' => $projectNames,
                        'project_ids' => $projectIds,
                        'is_submited' => $isSubmited,
                        'submit_name' => $currentUser->name
                    ];
                    $emailLeader = new EmailQueue();
                    $emailLeader->setTo($leaderInfo->leader_email)
                            ->setTemplate('tag::mail.tag_submitted', $dataMail)
                            ->setSubject(trans('tag::view.Mail submit subject', ['projects' => $projectNames]))
                            ->setNotify($leaderInfo->leader_id, null, route('tag::object.project.index', ['project_ids' => $projectIds]), ['category_id' => RkNotify::CATEGORY_PROJECT])
                            ->save();
                }
            }
            
            DB::commit();
            $firstProject = $projects->first();
            return response()->json([
                    'tag_status' => $firstProject->tag_status,
                    'assignee_id' => $firstProject->leader_id,
                    'assignee_name' => $firstProject->leader_email,
                    'message' => trans('tag::message.Save data success')
                ]);
        } catch (\Exception $ex) {
            Log::info($ex);
            DB::rollback();
            return response()->json([
               'message' => trans('core::message.Error system')
            ], 500);
        }
    }
    
    /**
     * approve tag
     * 
     * @param Request $request
     * @return type
     */
    public function approveTags (Request $request) {
        $projectId = $request->get('project_id');
        if (!$projectId) {
            return response()->json(['message' => trans('tag::message.Not found item')], 422);
        }
        if (!is_array($projectId)) {
            $projectId = [$projectId];
        }
        //check permission
        foreach ($projectId as $pId) {
            if (!TagGeneral::canDoProject($pId, TagConst::ROUTE_APPROVE_PROJ_TAG, TagConst::ACTION_APPROVE)) {
                Session::flash(
                    'messages', [
                            'errors'=> [
                                Lang::get('core::message.You don\'t have access'),
                            ]
                        ]
                );
                return response()->json([
                    'message' => trans('core::message.You don\'t have access'),
                    'success' => 0,
                    'reload' => 1
                ]);
            }
        }
        
        $projects = ProjectTag::getWithAssignee($projectId);
        if ($projects->isEmpty()) {
            return response()->json(['message' => trans('tag::message.Not found item')], 422);
        }
        
        DB::beginTransaction();
        $currentUserName = Permission::getInstance()->getEmployee()->name;
        try {
            $projectAssignees = $projects->groupBy('assignee_id');
            $fieldIds = Field::getChildIds(TagConst::SET_TAG_PROJECT);
            foreach ($projectAssignees as $arrProjects) {
                $isApproved = false;
                $projectNames = [];
                $projectIds = [];
                foreach ($arrProjects as $project) {
                    if ($project->tag_status == TagConst::TAG_STATUS_APPROVE && !$isApproved) {
                        $isApproved = true;
                    }
                    $project->tag_status = TagConst::TAG_STATUS_APPROVE;
                    $project->tag_assignee = null;
                    $project->save();
                    $projectNames[] = $project->name;
                    $projectIds[] = $project->id;
                    //set tag status
                    $project->submitDraftTags($fieldIds);
                }
                //send mail to assignee.
                $assignee = $arrProjects[0];
                if (!$assignee->assignee_email) {
                    continue;
                }
                $projectNames = implode(', ', $projectNames);
                $dataMail = [
                    'dear_name' => $assignee->assignee_name,
                    'project_name' => $projectNames,
                    'project_id' => $projectIds,
                    'submit_name' => $currentUserName,
                    'is_approved' => $isApproved
                ];
                $pmMail = new EmailQueue();
                $pmMail->setTo($assignee->assignee_email)
                        ->setTemplate('tag::mail.tag_approved', $dataMail)
                        ->setSubject(trans('tag::view.Mail subject approve', ['project' => $projectNames]))
                        ->setNotify($assignee->assignee_id, null, route('tag::object.project.index', ['project_ids' => $projectIds]), ['category_id' => RkNotify::CATEGORY_PROJECT])
                        ->save();
            }
            
            DB::commit();
            $projectFirst = $projects->first();
            return response()->json([
                    'tag_status' => $projectFirst->tag_status,
                    'assignee_id' => null,
                    'assignee_name' => null,
                    'message' => trans('tag::message.Save data success')
                ]);
        } catch (\Exception $ex) {
            Log::info($ex);
            DB::rollback();
            return response()->json([
                'message' => trans('core::message.Error system')
            ], 500);
        }
    }
    
    /**
     * get tags count
     * @param Request $request
     * @return type
     */
    public function getFieldsTagCount(Request $request) {
        if (!$this->scope->isAllow(TagConst::ROUTE_VIEW_PROJ_DETAIL)) {
            return CoreView::viewErrorPermission();
        }
        
        $projectId = $request->get('project_id');
        if (!$projectId) {
            return response()->json(['message' => trans('tag::message.Not found item')], 422);
        }
        return TagValue::countTagOfFieldsInProject($projectId);
    }
    
    /**
     * bulk action
     * @param Request $request
     */
    public function bulkActions (Request $request) {
        if (!$this->scope->isAllow(TagConst::ROUTE_SUBMIT_PROJ_TAG)
                && !$this->scope->isAllow(TagConst::ROUTE_APPROVE_PROJ_TAG)) {
            return CoreView::viewErrorPermission();
        }
        
        $action = $request->get('action');
        if (!$action) {
            return response()->json(['message' => trans('tag::message.Not found item')], 422);
        }
        switch ($action) {
            case TagConst::ACTION_SUBMIT:
                return $this->submitTags($request);
            case TagConst::ACTION_APPROVE:
                return $this->approveTags($request);
            default :
                return response()->json(['message' => trans('tag::message.Not found item')], 422);
        }
    }
    
    /**
     * add project tag
     * @param Request $request
     * @return type
     */
    public function addTag (Request $request) {
        $valid = Validator::make($request->all(), [
            'project_id' => 'required',
            'tag' => 'required',
            'field_id' => 'required'
        ]);
        if ($valid->fails()) {
            return response()->json(['message' => trans('tag::message.Not found item')], 422);
        }
        $projectId = $request->get('project_id');
        $tagName = $request->get('tag');
        $fieldId = $request->get('field_id');
        //check permission
        if (!TagGeneral::canDoProject($projectId, TagConst::ROUTE_SUBMIT_PROJ_TAG, TagConst::ACTION_UPDATE_TAG)
                && !TagGeneral::canDoProject($projectId, TagConst::ROUTE_APPROVE_PROJ_TAG, TagConst::ACTION_UPDATE_TAG)) {
            Session::flash(
                'messages', [
                        'errors'=> [
                            Lang::get('core::message.You don\'t have access'),
                        ]
                    ]
            );
            return response()->json([
                'message' => trans('core::message.You don\'t have access'),
                'success' => 0,
                'reload' => 1
            ]);
        }
        $field = Field::find($fieldId);
        $project = ProjectTag::find($projectId);
        if (!$project || !$field) {
            return response()->json([
                'success' => 0,
                'message' => trans('tag::message.Not found item')
            ]);
        }
        $tag = null;
        try {
            if (Input::get('multi')) {
                $tags = explode('-', $tagName);
                $count = TagValue::insertMulti($project, $field, $tags);
            } else {
                $tag = Tag::createOrFindTag($fieldId, $tagName, TagConst::TAG_STATUS_DRAFT);
                if (!$tag) {
                    return response()->json([
                        'success' => 0,
                        'message' => trans('tag::message.Not found item')
                    ]);
                }
                $project->tags([$fieldId])->attach($tag->id, ['field_id' => $fieldId]);
                $count = 1;
            }
            TagGeneral::incrementLDBVersion();
            return response()->json([
                'success' => 1,
                'tag_id' => $tag ? $tag->id : null,
                'message' => trans('tag::message.Save data success'),
                'count' => $count
            ]);
        } catch (Exception $ex) {
            Log::info($ex);
            return response()->json([
                'success' => 0,
                'message' => trans('core::message.Error system')
            ]);
        }
    }
    
    /**
     * delete project tag
     * @param Request $request
     * @return type
     */
    public function deleteTag (Request $request) {
        $valid = Validator::make($request->all(), [
            'project_id' => 'required',
            'tag_id' => 'required',
            'field_id' => 'required'
        ]);
        if ($valid->fails()) {
            return response()->json(['message' => trans('tag::message.Not found item')], 422);
        }
        $projectId = $request->get('project_id');
        $tagId = $request->get('tag_id');
        $fieldId = $request->get('field_id');
        //check permission
        if (!TagGeneral::canDoProject($projectId, TagConst::ROUTE_SUBMIT_PROJ_TAG, TagConst::ACTION_UPDATE_TAG)
                && !TagGeneral::canDoProject($projectId, TagConst::ROUTE_APPROVE_PROJ_TAG, TagConst::ACTION_UPDATE_TAG)) {
            Session::flash(
                'messages', [
                        'errors'=> [
                            Lang::get('core::message.You don\'t have access'),
                        ]
                    ]
            );
            return response()->json([
                'message' => trans('core::message.You don\'t have access'),
                'success' => 0,
                'reload' => 1
            ]);
        }
        
        $tag = Tag::find($tagId);
        if ($tag) {
            $project = ProjectTag::find($projectId);
            if ($project) {
                $project->tags([$fieldId])->detach($tag->id);
                TagGeneral::incrementLDBVersion();
            }
        }
        return response()->json([
            'message' => trans('tag::message.Save data success')
        ]);
    }
    
    /**
     * get tags by ids
     */
    public function tagsList (Request $request) {
        if (!$this->scope->isAllow(TagConst::ROUTE_VIEW_PROJ_DETAIL)
                && !$this->scope->isAllow(TagConst::ROUTE_VIEW_PROJ_SEARCH)
                && !$this->scope->isAllow(TagConst::ROUTE_VIEW_PROJ_TAG)) {
            return CoreView::viewErrorPermission();
        }
        
        $tagStr = $request->get('tag_str');
        $tagIds = explode('-', $tagStr);
        if (!$tagIds) {
            return response()->json(['message' => trans('tag::message.Not found item')], 422);
        }
        return Tag::getWithFieldByIds($tagIds);
    }
    
    /**
     * get scope of project
     */
    public function getScope()
    {
        if (!$this->scope->isAllow(TagConst::ROUTE_VIEW_PROJ_DETAIL)) {
            return CoreView::viewErrorPermission();
        }
        $response = [];
        $project = $this->getProject('id', false);
        if (is_array($project) && 
            isset($project['success']) && 
            !$project['success']
        ) {
            return response()->json($project);
        }
        $projectMeta = $project->getProjectMeta();
        $response['relate']['scope'] = [
            'scope_scope' => $projectMeta->scope_scope,
            'scope_desc' => $projectMeta->scope_desc,
            'scope_customer_provide' => $projectMeta->scope_customer_provide
        ];
        $response['success'] = 1;
        return response()->json($response);
    }
}
