<?php

namespace Rikkei\Resource\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Resource\Model\RequestPriority;
use Rikkei\Resource\Model\WorkPlace;
use Rikkei\Team\Model\Permission as TeamPermission;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Permission;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\Breadcrumb;
use Lang;
use Rikkei\Team\View\TeamList;
use Rikkei\Resource\View\CandidatePermission;
use Rikkei\Resource\Model\RequestTeam;
use Rikkei\Resource\Model\Languages;
use Rikkei\Resource\Model\Programs;
use Rikkei\Team\Model\Employee;
use Illuminate\Http\Request;
use Rikkei\Resource\Model\ResourceRequest;
use Rikkei\Core\View\CookieCore;
use Mail;
use Rikkei\Resource\View\RequestPermission;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Resource\Model\Channels;
use Rikkei\Resource\Model\RequestChannel;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Team\Model\PqaResponsibleTeam;
use Rikkei\Resource\Http\Requests\AddResourceRequest;
use Rikkei\Resource\View\View;
use Rikkei\Test\Models\Test;
use Yajra\Datatables\Datatables;
use Rikkei\Test\Models\Result;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RequestController extends \Rikkei\Core\Http\Controllers\Controller
{
    /**
     * construct more
     */
    protected function _construct()
    {
        Menu::setActive('resource');
        Breadcrumb::add('Resource' , route('resource::request.list'));
    }

    /**
     * Create request
     *
     * @return View
     */
    public function create()
    {
        Breadcrumb::add(Lang::get('resource::view.Request.Create.Create request'));
        $langs = Languages::getInstance()->getList();
        $programs = Programs::getInstance()->getList();
        $roles = getOptions::getInstance()->getRoles();
        $effort = getOptions::getInstance()->getEffort();
        $statusOption = getOptions::getInstance()->getStatusOption();
        $onsiteOption = getOptions::getInstance()->getOnsiteOption();
        $priorityOption = RequestPriority::getPriorityOption();
        $places = WorkPlace::getInstance()->getList();
        $canEdit = true;
        $create = true;
        $teamPermission = new TeamPermission();
        $hrAccounts = $teamPermission->getEmployeeByActionName('sendMailToCandidate.candidate')->pluck('email')->toArray();
        $typeOptions = Candidate::getTypeOptions();
        return view('resource::request.create', compact([
            'langs', 'roles', 'programs', 'create', 'hrAccounts',
            'effort', 'onsiteOption', 'statusOption', 'canEdit', 'places',
            'typeOptions', 'priorityOption', 'skill'
        ]));
    }

    /**
     * Update request
     *
     * @param type $id
     * @return View
     */
    public function edit($id)
    {
        $request = ResourceRequest::find($id);
        if (!$request) {
            return view('core::errors.exception');
        }
        //Check permission
        $curEmp = Permission::getInstance()->getEmployee();
        $teamIds = Permission::getInstance()->isScopeTeam();

        if (!ResourceRequest::checkCanEdit($request)) {
            return view('core::errors.permission_denied');
        }
        //End check permission

        Breadcrumb::add(Lang::get('resource::view.Request.Create.Create request'));
        $langs = Languages::getInstance()->getList();
        $programs = Programs::getInstance()->getList();
        $roles = getOptions::getInstance()->getRoles();
        $effort = getOptions::getInstance()->getEffort();
        $onsiteOption = getOptions::getInstance()->getOnsiteOption();
        $priorityOption = RequestPriority::getPriorityOption();
        $places = WorkPlace::getInstance()->getList();
        //If approved
        $statusOption = getOptions::getInstance()->getStatusApproveOption();

        $allLangs = ResourceRequest::getAllLangOfRequest($request);
        $allProgrammingLangs = ResourceRequest::getAllProgramOfRequest($request);
        $cooOption = getOptions::getInstance()->getApproveOption();
        $typeOption = getOptions::getInstance()->getTypeOption();
        $canEdit = ResourceRequest::checkCanEdit($request);

        $teams = RequestTeam::getTeamByRequest($request->id);
        $teamsSelected = RequestTeam::getTeamSelectedLabel($request->id);
        $teamPermission = new TeamPermission();
        $hrAccounts = $teamPermission->getEmployeeByActionName('sendMailToCandidate.candidate')->pluck('email')->toArray();
        $typeOptions = Candidate::getTypeOptions();
        $typeOfRq = ResourceRequest::getAllTypeOfRequest($request);

        $interviewers = empty($request->interviewer) ? null : Employee::getEmpByIds(explode(',', $request->interviewer));
        $saler = empty($request->saler) ? null : Employee::getEmpById($request->saler);

        return view('resource::request.create', compact([
            'langs', 'roles', 'programs', 'teams', 'teamsSelected',
            'effort', 'onsiteOption', 'request', 'statusOption',
            'allLangs', 'allProgrammingLangs', 'cooOption', 'places',
            'typeOption', 'canEdit', 'hrAccounts', 'typeOptions',
            'typeOfRq', 'saler', 'interviewers', 'priorityOption'
        ]));
    }

    /**
     * Detail request page
     *
     * @param int $id
     * @return View
     */
    public function detail($id)
    {
        $request = ResourceRequest::getRequestById($id);
        if (!$request) {
            return view('core::errors.exception');
        }

        Breadcrumb::add(Lang::get('resource::view.Request.Create.Request detail'));

        //Request information
        $langs = Languages::getInstance()->getNamesByIds(ResourceRequest::getAllLangOfRequest($request));
        $programIds = ResourceRequest::getAllProgramOfRequest($request);
        $programs = Programs::getInstance()->getNamesByIds($programIds);
        $role = ProjectMember::getType($request->role);
        $effort = getOptions::getInstance()->getEffortByKey($request->effort);
        $saler = Employee::getEmpById($request->saler);
        $onsite = getOptions::getInstance()->getOnsiteByKey($request->onsite);
        $interviewerIds = explode(',', $request->interviewer);
        $interviewers = Employee::getEmpByIds($interviewerIds);
        $createdBy = Employee::getEmpById($request->created_by);

        //Candidate information
        $totalCan = Candidate::getInstance()->getCountCandidateOfRequest($id);
        $channelsCan = Channels::getInstance()->getChannelsCandidate($request->id);

        //Approve options
        $recruiter = $request->type == getOptions::TYPE_RECRUIT ? Employee::getEmpById($request->recruiter) : null;
        $teamPermission = new TeamPermission();
        $hrAccounts = $teamPermission->getEmployeeByActionName('sendMailToCandidate.candidate')->pluck('email')->toArray();
        $approveOptions = getOptions::getInstance()->getApproveOption();
        $typeOption = getOptions::getInstance()->getTypeOption();

        //Recruiment cost
        $channels = Channels::getInstance()->getList();
        $channelsOfRequest = Channels::getInstance()->getChannels($request->id);

        //Teams detail
        $teams = RequestTeam::getTeamByRequest($request->id);
        $roles = getOptions::getInstance()->getRoles();
        $teamsOptionAll = TeamList::toOption(null, true, false);
        $priorityOption = RequestPriority::getPriorityOption();
        $places = WorkPlace::getInstance()->getList();
        $checkFull = Candidate::checkFull($request);
        $checkOverload = Candidate::checkOverload($request);
        // deadline
        $today = strtotime(date("Y-m-d"));
        $deadline = strtotime($request->deadline);
        $warning = (int)($deadline < $today && $request->status !== getOptions::STATUS_CLOSE);
        $candidateTypeOfRequest = \Rikkei\Resource\Model\RequestType::getTypeOfRequest($id);
        $arrCandidateType = [];
        $arrCandidateTypeId = [];
        if (!empty($candidateTypeOfRequest)) {
            foreach ($candidateTypeOfRequest as $candidateType) {
                $arrCandidateTypeId[] = $candidateType->type;
                $arrCandidateType[] = Candidate::getType($candidateType->type);
            }
        }
        $strCandidateType = empty($arrCandidateType) ? '' : implode(', ', $arrCandidateType);
        return view('resource::request.detail', compact([
                'langs', 'role', 'programs', 'hrAccounts', 'channels',
                'effort', 'saler', 'onsite', 'request', 'interviewers',
                'createdBy', 'recruiter', 'approveOptions', 'totalCan',
                'channelsCan', 'typeOption', 'channelsOfRequest', 'teams',
                'teamsOptionAll', 'roles', 'checkOverload', 'checkFull', 'warning', 'places',
                'programIds', 'typeOptions', 'strCandidateType', 'arrCandidateTypeId', 'priorityOption'
            ]));
    }

    /**
     * Save request
     *
     * @param Request $request
     * @return void
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $data['is_hot'] = isset($request['is_hot']) ? 1 : 0;

        $requestId = Input::get('request_id');
        if ($requestId) {
            $resourceRequest = ResourceRequest::find($requestId);
            if (!$resourceRequest) {
                return redirect()->route('resource:request.list')
                    ->withErrors(Lang::get('resource::message.Not found item.'));
            } else if (!ResourceRequest::checkCanEdit($resourceRequest)) {
                return view('core::errors.exception');
            }
        }
        if (!isset($data['recruiter'])) {
            $teamHr = Team::getTeamByType(Team::TEAM_TYPE_HR);
            $leaderHr = $teamHr ? $teamHr->getLeader() : null;
            if ($leaderHr === null) {
                return redirect()->back()
                    ->withErrors(Lang::get('resource::message.Leader HR not found.'));
            }
        }

        $requestId = ResourceRequest::getInstance()->insertOrUpdateRequest($data);
        if ($requestId) {
            if (isset($data['request_id']) && $data['request_id']) {
                $mgs = Lang::get('resource::message.Update request success');
            } else {
                $mgs = Lang::get('resource::message.Create request success');
            }

            $messages = [
                'success'=> [
                    $mgs,
                ]
            ];
            return redirect()->route('resource::request.edit', ['id' => $requestId])->with('messages', $messages);
        } else {
            if (isset($data['request_id']) && $data['request_id']) {
                $mgs = Lang::get('resource::message.Update request error');
            } else {
                $mgs = Lang::get('resource::message.Create request error');
            }
            $messages = [
                'errors'=> [
                    $mgs,
                ]
            ];
            return redirect()->route('resource::request.edit', ['id' => $requestId])->with('messages', $messages);
        }
    }

    /**
     * Approve request
     *
     * @param Request $request
     * @return void
     */
    public function approved(Request $request)
    {
        $approve = $request->input('approve');
        $data['_token'] = $request->input('_token');
        $data['request_id'] = $request->input('request_id');
        $resourceRequest = ResourceRequest::find($data['request_id']);

        //if this request closed then don't anything
        if ($resourceRequest->status == getOptions::STATUS_CLOSE) {
            return redirect()->route('resource::request.detail', ['id' => $data['request_id']]);
        }

        if ($resourceRequest->approve == getOptions::APPROVE_YET) {
            $data['approve'] = $approve;
            if ($approve == getOptions::APPROVE_ON) {
                $data['type'] = $request->input('type');
                if ($data['type'] == getOptions::TYPE_RECRUIT) {
                    $data['recruiter'] = $request->input('recruiter');
                    $data['priority_id'] = $request->input('priority');
                }
            }
            if ($approve == getOptions::APPROVE_OFF
                || $approve == getOptions::APPROVE_ON && $data['type'] == getOptions::TYPE_UTILIZE_RESOURCE) {
                //Change request to close when disapproved or approved and type is UTILIZE_RESOURCE
                $data['status'] = getOptions::STATUS_CLOSE;
            }
        } elseif ($resourceRequest->approve == getOptions::APPROVE_ON) {
            if ($resourceRequest->type == getOptions::TYPE_RECRUIT) {
                $data['recruiter'] = $request->input('recruiter');
                $data['priority_id'] = $request->input('priority');
            }
        } else {

        }

        //save data
        $requestId = ResourceRequest::getInstance()->approve($data);

        $mgs = Lang::get('resource::message.Update request success');
        $messages = [
            'success'=> [
                $mgs,
            ]
        ];
        return redirect()->route('resource::request.detail', ['id' => $requestId])->with('messages', $messages);
    }

    public function assignee(Request $request)
    {
        $data['_token'] = $request->input('_token');
        $data['request_id'] = $request->input('request_id');
        $resourceRequest = ResourceRequest::find($data['request_id']);

        if ($resourceRequest->approve == getOptions::APPROVE_ON) {
            if ($resourceRequest->type == getOptions::TYPE_RECRUIT) {
                $data['recruiter'] = $request->input('recruiter');
            }
        }

        //save data
        $requestId = ResourceRequest::getInstance()->approve($data);

        $messages = [
            'success'=> [
                Lang::get('resource::message.Update request success'),
            ]
        ];
        return redirect()->route('resource::request.detail', ['id' => $requestId])->with('messages', $messages);
    }

    /**
     * Send mail to recruiter
     *
     * @param Request $request
     */
    public function sendMailRecruiter(Request $request)
    {
        if($request->input('requestId')) {
            $id = $request->input('requestId');
            $rq = ResourceRequest::find($id);
            if ($rq && $rq->recruiter) {
                $recruiter = Employee::getEmpById($rq->recruiter);
                if ($recruiter) {
                    $data = [
                        'emailTo' => $recruiter->email,
                        'href' => route('resource::request.detail',['id' => $id])
                    ];

                    Mail::send('resource::request.sendMail', $data, function ($message) use($data) {
                        $message->from('sales@rikkeisoft.com', 'Rikkeisoft');
                        $message->to($data['emailTo'])
                                ->subject(Lang::get('resource::message.Mail subject'));
                    });
                    //set notify
                    \RkNotify::put(
                        $recruiter->id,
                        Lang::get('resource::message.Mail subject'),
                        $data['href'],
                        ['category_id', RkNotify::CATEGORY_HUMAN_RESOURCE]
                    );
                }
            }
        }
    }

    public function index()
    {
        Breadcrumb::add(Lang::get('resource::view.Request.List.Request list'));
        $pager = Config::getPagerData(null, ['order' => 'requests.id', 'dir' => 'desc']);
        $filterTeam = Form::getFilterData('exception', 'request_team.team_id');
        $filterProLangs = Form::getFilterData('exception', 'pro_lang_ids');
        $per = new Permission();
        $urlFilter = route('resource::request.list') . '/';
        $teamIds = [];
        $teamIdsAvailable = null;
        $teamTreeAvailable = [];
        $filterTitle = Form::getFilterData('except', 'requests.title');
        $filterRecruiter = Form::getFilterData('except', 'requests.recruiter');
        $route = 'resource::request.list';
        //scope company => view all team
        if (Permission::getInstance()->isScopeCompany(null, $route)) {
            $teamIds = Form::getFilterData('except', 'team_ids', $urlFilter);
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
            if ($idFilters = Form::getFilterData('except', 'team_ids', $urlFilter)) {
                $teamIds = implode($idFilters, ', ');
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
                $teamIds = implode($teamIdsAvailable, ', ');
                $flagNoCheck = true;
            }
            if (is_array($teamIdsAvailable) && count($teamIdsAvailable) == 1) {
                $teamIdsAvailable = Team::select('name')
                    ->find($teamIds);
            }
        }

        $list = RequestPermission::getInstance()->getList($pager['order'], $pager['dir'], $teamIds, $filterProLangs, $filterTitle, $filterRecruiter);
        // sum of all requesting resource having inprogress status
        $sumAllResourceRequest = RequestPermission::getInstance()->countAllResourceRequest($teamIds, $filterProLangs);
        if (count($sumAllResourceRequest) > 0) {
            $sumAllResourceRequest = ResourceRequest::getInstance()->getCollection($sumAllResourceRequest)->sum('number_resource');
        } else {
            $sumAllResourceRequest = '';
        }
        if (count($list) > 0) {
            $list = CoreModel::filterGrid($list);
            $list = CoreModel::pagerCollection($list, $pager['limit'], $pager['page']);
        }

        return view('resource::request.list', [
                'collectionModel' => $list,
                'sum' => $sumAllResourceRequest,
                'proLangs' => Programs::getInstance()->getList(),
                'isRoot' => $per->isRoot(),
                'teamIdCurrent' => $teamIds,
                'teamIdsAvailable' => $teamIdsAvailable,
                'teamTreeAvailable' => $teamTreeAvailable,
            ]);
    }

    public function saveChannel(Request $request)
    {
        $data = $request->all();
        $rq = ResourceRequest::find($data['request_id']);
        if (isset($data['isDelete'])) {
            $status = RequestChannel::deleteChannelRequest($data['channel_id'], $data['request_id']);
            if ($status) {
                $json['content'] = Channels::getContentTable($rq);
                $json['status'] = true;
                return response()->json($json);
            }
            $json['status'] = false;
            return response()->json($json);
        }
        $validateAdd = AddResourceRequest::validateData($data);
        if ($validateAdd->fails()) {
            $json['message_error'] = $validateAdd->errors();
            $json['status'] = false;
            return response()->json($json);
        }
        if (isset($data['cost'])) {
            $data['cost'] = str_replace(Channels::PRICE, '', $data['cost']);
        }
        $id = RequestChannel::getInstance()->store($data);
        if ($id) {
            $json['status'] = true;
            $json['content'] = Channels::getContentTable($rq);
        } else {
            $json['status'] = false;
        }

       return response()->json($json);
    }

    public function generate(Request $request)
    {
        if($request->input('id')) {
            $id = $request->input('id');
            $rq = ResourceRequest::find($id);
            if (count($rq)) {
                $data = [
                    'title' => $this->generateTitle($rq),
                    'workType' => $this->generateWorktype($rq),
                ];
            }
        }
    }

    public function generateTitle($rq)
    {
        switch ($rq->role) {
            case getOptions::ROLE_DEV :
                $programs = ResourceRequest::getProgramByRequest($rq);
                foreach ($programs as $program) {
                   $arr[] = $program->name;
                }
                $name = implode(', ', $arr);
                return Lang::get('resource::view.Request.Detail.Title job dev', ['program'=>$name]);
            case getOptions::ROLE_QA :
                return Lang::get('resource::view.Request.Detail.Title job QA');
            case getOptions::ROLE_BRSE :
                return Lang::get('resource::view.Request.Detail.Title job BrSE');
        }
    }

    public function generateWorktype($rq)
    {
        switch ($rq->effort) {
            case getOptions::EFFORT_FULL :
                return Lang::get('resource::view.Request.Detail.Work type fulltime');
            case getOptions::EFFORT_PART :
                return Lang::get('resource::view.Request.Detail.Work type parttime');
        }
    }

    /**
     * Get data table candidate list by request
     * @param int $requestId
     * @param Datatables $datatables
     * @return html
     */
    public function candidateList($requestId, Datatables $datatables)
    {
        $candidates = CandidatePermission::getInstance()->getList(null, null, false);
        $candidates = $candidates->where("candidate_request.request_id", $requestId)->get();
        $arrPassValues = [getOptions::END, getOptions::WORKING];
        if (count($candidates)) {
            foreach ($candidates as &$can) {
                if (in_array($can->status, $arrPassValues)) {
                    if ((int)$can->request_id != (int)$requestId) {
                        $can->status = getOptions::PASS_OTHER_REQUEST;
                    }
                }
                if (!empty($can->positions)) {
                    $strPos = [];
                    $positions = explode(',', $can->positions);
                    if (is_array($positions) && count($positions)) {
                        foreach ($positions as $pos) :
                            $strPos[] = getOptions::getInstance()->getRole($pos);
                        endforeach;
                    }
                    $can->positions = implode(', ', $strPos);
                } else {
                    $can->positions = '';
                }
                $can->position_apply = getOptions::getInstance()->getRole($can->position_apply);
                if ((int) $can->status === getOptions::WORKING){
                    $can->status = getOptions::getInstance()->getCandidateStatus($can->status, $can)
                               .' ('.getOptions::getInstance()->getWorkingTypeLabel($can->working_type).')';
                }
                else {
                    $can->status = getOptions::getInstance()->getCandidateStatus($can->status, $can);
                }
                $can->type = Candidate::getType($can->type);
                $results = Result::getByEmailType($can->email, Test::IS_NOT_AUTH, $can->id);
                if ($results->count() > 0) {
                    $can->specialize_score = '';
                    foreach ($results as $result) {
                        if ($result['name'] == 'GMAT') {
                            $can->test_mark = $result->total_corrects.'/'.$result->total_questions;
                        } else {
                            $can->specialize_score = $can->specialize_score.$result->name.' : '.$result->total_corrects.'/'.$result->total_questions.'<br />';
                        }
                    }
                } else {
                    $can->specialize_score = $can->test_mark_specialize;
                }
            }
        }
        return $datatables->of($candidates)->make(true);

    }

    /**
     * search team by ajax
     */
    public function searchAjax()
    {
        if (!app('request')->ajax()) {
            //return redirect('/');
        }
        return response()->json(
            ResourceRequest::searchAjax(Input::get('q'), Input::except(['q']))
        );
    }

    /**
     * Send data to webvn
     * @param Request $request
     * @return mixed
     */
    public function postRequest(Request $request)
    {
        $url = Config('services.curl');
        $data = $request->all();
        $response = [];
        DB::beginTransaction();
        try {
            //Update field `published`
            ResourceRequest::where('id', $data['request_id'])->update(['published' => ResourceRequest::PUBLISHED]);

            //Publish to webvn
            $token = CoreConfigData::getApiToken();
            $data = [
                'data' => $data,
                'token' => $token,
            ];
            $url = $url['server_webvn'].'/recruitment/insert';
            $checkError = View::postData($data, $url);
            DB::commit();
            return $checkError;
        } catch (\Exception $ex) {
            $response['error'] = 'Error system';
            Log::info($ex);
            DB::rollback();
            return response()->json($response, 402);
        }
    }

    /**
     * Send data to webvn
     * @param Request $request
     * @return mixed
     */
    public function postRequestRecruitment(Request $request)
    {
        $url = Config('services.curl');
        $data = $request->all();
        $response = [];
        DB::beginTransaction();
        try {
            //Update field `published`
            ResourceRequest::where('id', $data['request_id'])->update(['published' => ResourceRequest::PUBLISHED]);

            //Publish to webvn
            $token = CoreConfigData::getApiToken();

            $data['name'] = $data['title'];
            $data['short_description'] = str_limit(strip_tags($data['description']), 300);
            $data['slug'] = str_slug($data['title']) . time();
            $data['end_date'] = $data['expired'];
            $data['start_date'] = isset($data['request_date']) ? $data['request_date'] : date('Y-m-d');
            $data['programs'] = isset($data['programs']) ? explode(',', $data['programs']) : [];
            $data['types'] = isset($data['types']) ? explode(',', $data['types']) : [];
            $positions = isset($data['positions']) ? $data['positions'] : [];
            $data['positions'] = [];
            $data['positions']['id'] = !empty(array_keys($positions)) ? array_keys($positions)[0] : '';
            $data['positions']['number'] = !empty(array_values($positions)) ? array_values($positions)[0] : '';
            $data['_token'] = $token;

            $url = $url['server_recruitment'].'/api/admin/recruitment/insert';
            $checkError = View::postData($data, $url);
            DB::commit();
            return $checkError;
        } catch (\Exception $ex) {
            $response['error'] = 'Error system';
            Log::info($ex);
            DB::rollback();
            return response()->json($response, 402);
        }
    }

    /**
     * Send data to webvn
     */
    public function sendDataWebVn($key = null)
    {
        $token = CoreConfigData::getApiToken();
        if ($key == $token) {
            $programs = Programs::getInstance()->getList();
            $roles = getOptions::getInstance()->getRoles();
            $places = WorkPlace::getInstance()->getList();
            $langs = Languages::getInstance()->getListWithLevel();
            $typeOptions = Candidate::getTypeOptions();
            $statusRequest = getOptions::getInstance()->getStatusApproveOption();
            return [
                'programs' => $programs,
                'roles' => $roles,
                'places' => $places,
                'token' => $token,
                'langs' => $langs,
                'type' => $typeOptions,
                'statusRequest' => $statusRequest,
            ];
        } else {
            return 'Token invalid';
        }
    }

    /**
     * save employee assign request
     */
    public function saveAssignRequest(Request $request) {

        $json = Employee::getEmailEmpById($request->assign);
        if(!$json) {
            $json = 'Not assign';
        }
        $requestAssign = ResourceRequest::find($request->id);
        $requestAssign->employees_assign = $request->assign;
        $requestAssign->save();
        return response()->json($json);

    }
}
