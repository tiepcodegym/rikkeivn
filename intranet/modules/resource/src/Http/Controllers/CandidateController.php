<?php

namespace Rikkei\Resource\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\Breadcrumb;
use Lang;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Resource\Http\Requests\RecommendPost;
use Rikkei\Resource\Model\CandidateRequest;
use Rikkei\Resource\Model\ChannelFee;
use Rikkei\Resource\Model\Languages;
use Rikkei\Resource\Model\Programs;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\Model\User;
use Illuminate\Http\Request;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Resource\Model\Channels;
use Rikkei\Resource\Model\ResourceRequest;
use Rikkei\Resource\View\CandidatePermission;
use Rikkei\Team\View\Permission;
use Rikkei\Resource\Model\RecruitProcess;
use Rikkei\Core\View\View;
use Illuminate\Support\Facades\Config as SupportConfig;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Test\Models\Result;
use Maatwebsite\Excel\Facades\Excel;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Resource\View\View as Rview;
use Rikkei\Resource\Model\CandidateMail;
use PDF;
use Rikkei\Team\Model\Team;
use Rikkei\Test\Models\Test;
use Rikkei\Resource\Model\CandidatePosition;
use Illuminate\Support\Facades\DB;
use Rikkei\Resource\Model\RequestTeam;
use Yajra\Datatables\Datatables;
use Rikkei\Resource\Model\LanguageLevel;
use Rikkei\Resource\Model\CandidateLanguages;
use Rikkei\Team\View\TeamList;
use Rikkei\Test\Models\Type;
use Auth;
use Illuminate\Support\Facades\Log;
use Rikkei\ManageTime\Model\LeaveDay;
use Carbon\Carbon;
use Rikkei\Core\View\CookieCore;
use Rikkei\Resource\View\RequestPermission;
use Google_Service_Calendar;
use Rikkei\Resource\View\GoogleCalendarHelp;
use Rikkei\Resource\Model\CandidateMailInterviewer;
use Rikkei\Resource\Model\CandidateProgramming;
use Rikkei\Resource\Model\CandidateComment;
use Illuminate\Support\Facades\View as ViewLaravel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Rikkei\Team\Model\EmployeeContact;
use GuzzleHttp\Client;
use Rikkei\Resource\Model\RicodeTest;
use Rikkei\Team\Model\Permission as TeamPermission;

class CandidateController extends \Rikkei\Core\Http\Controllers\Controller
{
    /**
     * construct more
     */
    protected function _construct()
    {
        Menu::setActive('resource');
        Breadcrumb::add('Candidate' , route('resource::candidate.list'));
    }

    /**
     * Create request
     *
     */
    public function create()
    {
        $where = [
            ['status', getOptions::STATUS_INPROGRESS],
            ['approve', getOptions::APPROVE_ON],
            ['requests.type', getOptions::TYPE_RECRUIT],
        ];
        $listRequest = ResourceRequest::getInstance()->getAllList($where);
        Breadcrumb::add(Lang::get('resource::view.Candidate.Create.Create candidate'));
        $langs = Languages::getInstance()->getListWithLevel();
        $programs = Programs::getInstance()->getList();
        $roles = getOptions::getInstance()->getRoles();
        $statusOption = getOptions::getInstance()->getStatusOption();
        $channels = Channels::getInstance()->getList();
        $teamPermission = new TeamPermission();
        $hrAccounts = $teamPermission->getEmployeeByActionName('sendMailToCandidate.candidate')->pluck('email')->toArray();
        $typeOptions = Candidate::getTypeOptions();
        $requestArray = $this->requestTeamPos($listRequest);
        $langArray = Candidate::langWithLevel($langs);
        $channelChange = Channels::where('type', Channels::COST_CHANGE)->pluck('id')->toArray();
        $programmingLangs = null;
        $allProgrammingLangs = null;
        return view('resource::candidate.create', compact([
            'langs', 'roles', 'programs', 'listRequest', 'typeOptions',
            'statusOption', 'canEdit', 'channels', 'hrAccounts',
            'requestArray', 'langArray', 'allProgrammingLangs', 'programmingLangs', 'channelChange'
        ]));
    }

    public function edit($id)
    {
        $candidate = Candidate::leftjoin('recruit_channel', 'recruit_channel.id', '=', 'candidates.channel_id')
            ->select('candidates.*', 'recruit_channel.type as channel_type')
            ->find($id);
        if (!$candidate) {
            return view('core::errors.exception');
        }

        $curEmp = Permission::getInstance()->getEmployee();
        if (!CandidatePermission::editPermission($candidate, $curEmp)) {
            return view('core::errors.permission_denied');
        }

        Breadcrumb::add(Lang::get('resource::view.Candidate.Create.Edit candidate'));
        $langs = Languages::getInstance()->getListWithLevel();
        $programs = Programs::getInstance()->getList();
        $roles = getOptions::getInstance()->getRoles();
        $orWhere = [['requests.id', $candidate->request_id]];
        $listRequest = RequestPermission::getInstance()->getRequestsProgress($candidate);
        $statusOption = getOptions::getInstance()->getCandidateResultOptions($candidate);
        $allLangs = CandidateLanguages::getListByCandidate($candidate->id);
        $allProgrammingLangs = Candidate::getAllProgramOfCandidate($candidate);
        $allTeams = Candidate::getAllTeamOfCandidate($candidate);
        $allRequests = Candidate::getAllRequestOfCandidate($candidate);
        $channels = Channels::getInstance()->getList();
        $teamPermission = new TeamPermission();
        $hrAccounts = $teamPermission->getEmployeeByActionName('sendMailToCandidate.candidate')->pluck('email')->toArray();
        $positionSelected = CandidatePosition::getPositions($id);
        $positions = [];
        foreach ($positionSelected as $pos) {
            $positions[] = $pos->position_apply;
        }
        $typeOptions = Candidate::getTypeOptions();
        $teamsOfRequests = RequestTeam::getTeamsByRequests($allRequests);
        $posesOfTeams = RequestTeam::getPosesByTeamsAndRequests($allRequests, $allTeams);
        $langSelected = CandidateLanguages::getLangSelectedLabel($candidate->id);
        $requestArray = $this->requestTeamPos($listRequest);
        $langArray = Candidate::langWithLevel($langs);
        //path folder storage candidate cv
        $pathFolder = url(SupportConfig::get('general.upload_folder') . '/' . Candidate::UPLOAD_CV_FOLDER);

        //get list apply history
        $applyHistory = Candidate::getCandidates(['email' => $candidate->email], ['id', 'received_cv_date']);

        //Get presenter if exist
        $founder = empty($candidate->found_by) ? null : Employee::getEmpById($candidate->found_by);

        //Get pre if exist
        $presenter = empty($candidate->presenter_id) ? null : Employee::getEmpById($candidate->presenter_id);
        //Get list programming language
        $programmingLangs = Candidate::getCandidateProgrammingById($id);
        $channelChange = Channels::where('type', Channels::COST_CHANGE)->pluck('id')->toArray();

        return view('resource::candidate.create', compact([
            'langs', 'roles', 'programs',  'listRequest', 'resultOptions',
            'candidate', 'statusOption', 'channels', 'hrAccounts', 'founder', 'presenter',
            'allLangs', 'allProgrammingLangs', 'positions', 'requestArray',
            'typeOptions', 'allTeams', 'allRequests', 'teamsOfRequests', 'posesOfTeams',
            'langSelected', 'langArray', 'pathFolder', 'applyHistory', 'curEmp', 'programmingLangs', 'channelChange'
        ]));

    }

    /**
     * List request with team and position
     *
     * @param ResourceRequest join RequestTeam collection $listRequest
     * @return array
     */
    function requestTeamPos($listRequest)
    {
        $requestArray = [];
        foreach ($listRequest as $item) {
            $tempTeam = [];
            $arrayTeamPos = explode(';', $item->team_pos);
            if (is_array($arrayTeamPos)) {
                foreach ($arrayTeamPos as $teamPosItem) {
                    $teamPos = explode(',', $teamPosItem);
                    if (is_array($teamPos)) {
                        $teamId = $teamPos[0];
                        if (isset($teamPos[1]) && $teamPos[1]) {
                            $positionId = $teamPos[1];
                            $tempTeam[$teamId][] = $positionId;
                        }
                    }
                }
                $requestArray[$item->id] = $tempTeam;
            }
        }
        return $requestArray;
    }

    public function detail($id, Request $request)
    {
        if (isset($request->needLogOut) && $request->needLogOut) {
            return User::forceLogOut();
        }
        $candidate = Candidate::getCandidateById($id);

        if (!$candidate) {
            return view('core::errors.exception');
        }
        $curEmp = Permission::getInstance()->getEmployee();
        if (!CandidatePermission::detailPermission($candidate, $curEmp)) {
            return view('core::errors.permission_denied');
        }

        $rq = ResourceRequest::find($candidate->request_id);
        $channel = Channels::getChannelById($candidate->channel_id);
        $resultOptions = getOptions::getInstance()->getResultOption();

        if ($candidate->presenter_id) {
            $presenter = Employee::getEmpById( $candidate->presenter_id );
        } else {
            $presenter = null;
        }
        $founder = Employee::getEmpById($candidate->found_by);

        $resultTest = Result::getByEmailType($candidate->email, Test::IS_NOT_AUTH, $candidate->id);
        $ricodeTest = RicodeTest::where('candidate_id', $candidate->id)->get();
        if(!$ricodeTest->isEmpty() && isset($ricodeTest->first()->title)) {
            $ricodeTest->map(function($item) {
                $item->total_corrects = $item->total_correct_answers;
                $item->total_answers = null;
                $item->type = null;
                $item->name = 'Rikkei Code';
                $item->total_questions = (int)($item->level_easy) + (int)($item->level_medium) + (int)($item->level_hard);
                return $item;
            });
            $resultTest->push($ricodeTest->first());
        }

        $recruiter = Employee::getEmpByEmail($candidate->recruiter);
        $contact = EmployeeContact::getByEmp($recruiter['id']);
        if ($contact) {
            $recruiter['mobile_phone'] = $contact['mobile_phone'];
            $recruiter['skype'] = $contact['skype'];
        }
        $team = Team::find($candidate->team_id);
        if ($team) {
            $leader = $team->getLeader();
            $teamName = $team->name;
        } else {
            $leader = null;
            $teamName = null;
        }
        //test option
        $testTypes = Type::getList();
        $testTypeId = $candidate->test_option_type_ids;
        $rikkeiCode = false;
        $testTypes->map(function ($item) use ($testTypeId, &$rikkeiCode) {
            if($item->code == 'rikkei-code' && in_array($item->id, $testTypeId)) {
                $rikkeiCode = true;
            }
            return $item;
        });

        //path folder storage candidate cv
        $pathFolder = url(SupportConfig::get('general.upload_folder') . '/' . Candidate::UPLOAD_CV_FOLDER);
        //path folder storage candidate attach file
        $pathFolderAttach = url(SupportConfig::get('general.upload_folder') . '/' . Candidate::ATTACH_FOLDER);
        $allPos = getOptions::getInstance()->getRoles();
        $teamsOptionAll = TeamList::toOption(null, true, false);
        $employee = $candidate->employee;
        $workingtypeOptions = getOptions::getInstance()->getWorkingType();
        $contractLength = $this->getCandidateContractLength($candidate, $employee);
        $contractType = $this->getCandidateContractType($candidate, $employee);
        $empCodePrefix = Employee::getCodePrefix($candidate->team_id, $candidate->working_type);
        $maxEmployeeCardId = Employee::genSuggestCardId($candidate->team_id, $empCodePrefix, $candidate->working_type);

        //get interviewers of candidate
        if (!empty($candidate->interviewer) && is_array(explode(',', $candidate->interviewer))) {
            $interviewers = Employee::getEmpByIds(explode(',', $candidate->interviewer));
        } else {
            $interviewers = null;
        }
        //get content comment tab interview
        $collectionModel = CandidateComment::getGridData($id);
        $listRequest = RequestPermission::getInstance()->getRequestsProgress($candidate, true);
        $allRequests = Candidate::getAllRequestOfCandidate($candidate);

        //Check if team of current user at Da Nang
        $teamsOfEmployee = Team::getTeamOfEmployee($curEmp->id);
        $isDn = false;
        if ($teamsOfEmployee) {
            foreach ($teamsOfEmployee as $team) {
                if (substr($team->code, 0, 6) === 'danang') {
                    $isDn = true;
                    break;
                }
            }
        }
        //get list apply history
        $applyHistory = Candidate::getCandidates(['email' => $candidate->email], ['id', 'received_cv_date']);
        $programs = Programs::getInstance()->getList();
        $idPosDev = getOptions::ROLE_DEV;

        $candidate->ricodeTest = RicodeTest::where('candidate_id', $candidate->id)->first();
        $getRequests = CandidateRequest::getRequest($candidate->id);
        return view('resource::candidate.detail', compact(
            'candidate', 'rq', 'channel',
            'resultOptions', 'presenter', 'founder', 'resultTest',
            'recruiter', 'team', 'teamName', 'leader', 'listRequest', 'allRequests',
            'testTypes', 'applyHistory',
            'teamsOptionAll',
            'pathFolder', 'pathFolderAttach', 'allPos',
            'workingtypeOptions', 'contractLength', 'employee', 'contractType',
            'maxEmployeeCardId', 'empCodePrefix', 'curEmp', 'interviewers', 'isDn', 'collectionModel',
            'programs', 'idPosDev', 'rikkeiCode', 'getRequests'
        ));
    }

    /**
     * Get contract length of candidate
     *
     * @param Candidate $candidate
     * @param Employee $employee
     * @return string
     */
    public function getCandidateContractLength($candidate, $employee)
    {
        if ($employee) {
            return $employee->contract_length;
        } elseif (!empty($candidate->contract_length)) {
            return $candidate->contract_length;
        } else {
            return '';
        }
    }

    /**
     * Get contract type of candidate
     *
     * @param Candidate $candidate
     * @param Employee $employee
     * @return string
     */
    public function getCandidateContractType($candidate, $employee)
    {
        if ($employee) {
            $employeeWork = $employee->getItemRelate('work');
            if ($employeeWork) {
                return $employeeWork->contract_type;
            }
        } elseif (!empty($candidate->working_type)) {
            return $candidate->working_type;
        } else {
            return '';
        }
    }

    /**
     * Save candidate
     * @param Request $request
     * @return type
     */
    public function store(Request $request)
    {
        $needLogOut = false;
        $data = $request->except('employee');

        if (isset($data['interview_note'])) {
            $data['interview_note'] = htmlentities(Input::get('interview_note'));
        }
        if (isset($data['trainee_start_date']) && !$data['trainee_start_date']) {
            $data['trainee_start_date'] = null;
        }
        if (isset($data['trainee_end_date']) && !$data['trainee_end_date']) {
            $data['trainee_end_date'] = null;
        }
        if (isset($data['status']) && intval($data['status']) === getOptions::PREPARING ) {
            $contractTeamID = isset($data['contract_team_id']) ? $data['contract_team_id'] : 0;
            $contractTeamInfo = Team::getTeamById($contractTeamID);
            if (!$contractTeamInfo) {
                return redirect()->back()->withInput()->with('messages', ['errors' => [trans('resource::message.An error occurred')]]);
            }
        }

        $employeeData = $request->only('employee');
        $candidateId = Input::get('candidate_id');
        $curEmp = Permission::getInstance()->getEmployee();
        if ($candidateId) {
            $candidate = Candidate::find($candidateId);
            if (!$candidate) {
                return redirect()->route('resource:candidate.list')
                    ->withErrors(Lang::get('resource::message.Not found item.'));
            } else {
                if (!$request->input('detail') && !CandidatePermission::editPermission($candidate, $curEmp)) {
                    return view('core::errors.permission_denied');
                }
                //Store old candidate before save to compare changes after save
                $oldCandidate = $candidate;
            }
        } else {
            $data['status'] = getOptions::CONTACTING;
            $data['created_by'] = $curEmp->id;
        }

        //Upload cv
        if (isset($data['cv']) && $data['cv']) {
            $pathFolder = SupportConfig::get('general.upload_folder') . '/' . Candidate::UPLOAD_CV_FOLDER;
            $cv = View::uploadFile(
                $data['cv'],
                SupportConfig::get('general.upload_storage_public_folder') .
                    '/' . Candidate::UPLOAD_CV_FOLDER,
                SupportConfig::get('services.file.cv_allow'),
                SupportConfig::get('services.file.cv_max')
            );
            $data['cv'] = trim($pathFolder, '/').'/'.$cv;
        }
        DB::beginTransaction();
        $candidateOld =  Candidate::getCandidateById($candidateId);
        $candidateStatusOld = isset($candidateOld) && $candidateOld->status ? $candidateOld->status : null;
        $dataStatus = $request->get('status');
        try {
            //gửi mail cho người giới thiệu khi chuyển sang trạng thái chuẩn bị
            if ($candidateStatusOld && $dataStatus && $dataStatus == getOptions::PREPARING && $dataStatus != $candidateStatusOld && $candidate->found_by) {
                $empFoundBy = Employee::find($candidate->found_by);
                if ($empFoundBy) {
                    $emailQueue = new EmailQueue();
                    $emailQueue->setFrom(config('mail.username'), config('mail.name'))
                        ->setTo($empFoundBy->email)
                        ->setSubject('【Rikkeisoft】 Ứng viên '.$candidate->fullname.' vừa được cập nhật sang trạng thái chuẩn bị')
                        ->setTemplate('resource::candidate.mail.preparing_status_to_foundby', [
                            'foundbyName' => $empFoundBy->name,
                            'candidateName' => $candidate->fullname,
                            'candidateEmail' => $candidate->email,
                            'link' => route('resource::candidate.recommend'),
                        ]);
                    $emailQueue->save();
                }
            }
            //Update status tab detail page
            if ($request->input('detail')) {
                //check candidate had worked
                if ($candidateId && isset($data['had_worked']) && $data['had_worked']) {
                    $oldEmployeeId = $request->get('old_employee_id');
                    $employeeData['employee']['old_employee_id'] = $oldEmployeeId;
                    $employeeData['employee']['status'] = $request->get('status');
                    $employeeValid = Rview::validCandidateEmployee($candidate, $employeeData);
                    if ($employeeValid->fails()) {
                        return redirect()->back()->withInput()->withErrors($employeeValid->errors())->with('employee_errors', true);
                    }
                    if (in_array($request->get('status'), [getOptions::WORKING, getOptions::PREPARING])) {
                        //check exists card id -> employee_code
                        $employeeCode = Employee::getCodeFromCardId($employeeData['employee']['employee_card_id'], $candidate->team_id, $candidate->working_type);
                        $empId = $oldEmployeeId ? $oldEmployeeId : ($candidate->employee ? $candidate->employee->id : null);
                        $exitsCddEmp = Employee::checkExistsEmpCode($employeeCode, $empId);
                        if ($exitsCddEmp > 0) {
                            return redirect()
                                    ->back()
                                    ->withInput()
                                    ->withErrors([
                                        'employee.employee_card_id' => trans('validation.unique', ['attribute' => 'Employee card ID'])
                                    ])
                                    ->with('employee_errors', true);
                        }

                        // check exist employee card id in a branch
                        $existsEmpCardId = Employee::checkExistsEmpCardId($candidate->team_id, $employeeData['employee']['employee_card_id'], $empId);
                        if ($existsEmpCardId) {
                            $msg = Lang::get('validation.unique', ['attribute' => 'Employee card ID']);
                            return redirect()->back()->withInput()
                                ->withErrors(['employee.employee_card_id' => $msg])
                                ->with('employee_errors', true);
                        }
                    }
                }

                if (isset($data['offer_result']) || isset($data['update_contract_working'])) {
                    $offerResult = isset($data['offer_result']) ? $data['offer_result'] : $candidate->offer_result;
                    if ($offerResult == getOptions::RESULT_PASS) {
                        $data['status'] = getOptions::END;
                        //update employee contract
                        $candidate->updateEmployee($data);
                    } elseif ($offerResult == getOptions::RESULT_FAIL) {
                        $data['status'] = getOptions::FAIL;
                    } elseif ($offerResult == getOptions::RESULT_DEFAULT) {
                        $data['status'] = getOptions::OFFERING;
                    } else {
                        //else continue
                    }
                } elseif (isset($data['interview_result'])) {
                    if ($data['interview_result'] == getOptions::RESULT_DEFAULT) {
                        $data['status'] = getOptions::INTERVIEWING;
                    } elseif ($data['interview_result'] == getOptions::RESULT_PASS && $candidate->status != getOptions::OFFERING) {
                        $data['status'] = getOptions::OFFERING;
                    } elseif ($data['interview_result'] == getOptions::RESULT_FAIL) {
                        $data['contract_length'] = '';
                        $data['start_working_date'] = '';
                        $data['working_type'] = '';
                        $data['status'] = getOptions::FAIL;
                    }
                } elseif (isset($data['test_result'])) {
                    if ($data['test_result'] == getOptions::RESULT_DEFAULT) {
                        $data['status'] = getOptions::ENTRY_TEST;
                    } elseif ($data['test_result'] == getOptions::RESULT_PASS && $candidate->status != getOptions::INTERVIEWING && $candidate->status != getOptions::OFFERING) {
                        $data['status'] = getOptions::INTERVIEWING;
                    } elseif ($data['test_result'] == getOptions::RESULT_FAIL) {
                        $data['status'] = getOptions::FAIL;
                    }
                    //check test option
                    if (!isset($data['test_option_type_ids']) || !$data['test_option_type_ids']) {
                        $data['test_option_type_ids'] = null;
                    }
                } elseif (isset($data['contact_result'])) {
                    if ($data['contact_result'] == getOptions::RESULT_DEFAULT) {
                        $data['status'] = getOptions::CONTACTING;
                    } elseif ($data['contact_result'] == getOptions::RESULT_PASS && $candidate->status != getOptions::INTERVIEWING && $candidate->status != getOptions::OFFERING && $candidate->status != getOptions::ENTRY_TEST) {
                        $data['status'] = getOptions::ENTRY_TEST;
                    } elseif ($data['contact_result'] == getOptions::RESULT_FAIL) {
                        $data['status'] = getOptions::FAIL;
                    }
                }
            }

            if ($candidateId) { //if update then save diffrences before update
                //save history action
                RecruitProcess::getInstance()->saveProcess($data);
                //save candidate employee
                $candidate = Candidate::getInstance()->insertOrUpdate($data);
                if ($candidate && isset($data['had_worked']) && $data['had_worked']) {
                    $employeeData = $request->get('employee');
                    if (in_array($candidate->status, getOptions::statusEmpUpdateable())) {
                        $employee = Candidate::createOrUpdateEmployee($employeeData, $candidate, $data);
                        if (!empty($employee['employee']) && count($employee['employee'])) {
                            $data['employee_id'] = $employee['employee']->id;
                            $leaveDay = LeaveDay::where('employee_id', $data['employee_id'])->withTrashed()->first();
                            $now = Carbon::now();
                            if (!$leaveDay) {
                                $leaveDay = new LeaveDay();
                                $leaveDay->employee_id = $data['employee_id'];
                                $leaveDay->created_at = $now;
                            }
                            $leaveDay->updated_at = $now;
                            $leaveDay->deleted_at = null;
                            $leaveDay->save();

                            // save email for user if changing and delete that user session
                            User::saveEmail($employee['employee']);
                            $needLogOut = $employee['logout'];
                        }
                        //create or update request asset
                        //$candidate->updateRequestAsset($employee['employee']); //not create auto
                    }
                }
            } else { //if create then create then save history
                $candidate = Candidate::getInstance()->insertOrUpdate($data);
                $candidateId = $candidate->id;
                $data['candidate_id'] = $candidateId;
                $data['create'] = true;
                RecruitProcess::getInstance()->saveProcess($data);

                //Send mail to recruiter
                if (!empty($candidate->recruiter)) {
                    $recruiter = Employee::getEmpByEmail($candidate->recruiter);
                    $emailQueue = new EmailQueue();
                    $emailQueue->setTo($candidate->recruiter)
                        ->setFrom(Config('mail.username'), Config('mail.name'))
                        ->setSubject(Lang::get('resource::view.【Rikkeisoft】 The candidate :name has just been created then assign to you', ['name' => $candidate->fullname]))
                        ->setTemplate('resource::candidate.mail.reapply', [
                            'recruiterName' => View::getNickName($candidate->recruiter),
                            'candidateName' => $candidate->fullname,
                            'urlToCandidate' => route('resource::candidate.detail', $candidate->id),
                        ])
                        ->setNotify(
                            $recruiter->id,
                            Lang::get('resource::view.The candidate :name has just been created then assign to you', ['name' => $candidate->fullname]),
                            route('resource::candidate.detail', $candidate->id), ['category_id' => RkNotify::CATEGORY_HUMAN_RESOURCE]
                        );
                    $emailQueue->save();
                }
            }

            //Send mail if update information of candidate in detail page
            if ($request->input('detail')) {
                $data['employee_code'] = $request->input('employee_code');
                CandidatePermission::sendMailWhenCandidateChange($oldCandidate, $data);
            }
            //Update contract team id
            if(isset($contractTeamInfo)) {
                $candidate->updateContractTeamId($contractTeamInfo);
            }
            //commit database change;
            DB::commit();

            if ($candidateId) {
                if (isset($data['candidate_id']) && $data['candidate_id']) {
                    $mgs = Lang::get('resource::message.Update candidate success');
                } else {
                    $mgs = Lang::get('resource::message.Create candidate success');
                }

                $messages = [
                    'success'=> [
                        $mgs,
                    ]
                ];
                if ($request->input('detail')) {
                    return redirect()->route('resource::candidate.detail',
                            ['id' => $candidateId, 'needLogOut' => $needLogOut])->with('messages', $messages);
                } else {
                    return redirect()->route('resource::candidate.edit', ['id' => $candidateId])->with('messages', $messages);
                }

            } else {
                if (isset($data['candidate_id']) && $data['candidate_id']) {
                    $mgs = Lang::get('resource::message.Update candidate error');
                } else {
                    $mgs = Lang::get('resource::message.Create candidate error');
                }
                $messages = [
                    'errors'=> [
                        $mgs,
                    ]
                ];

                return redirect()->route('resource::candidate.create')->with('messages', $messages);
            }
        } catch (\Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('resource::message.An error occurred')]]);
        }
    }

    /**
     * Page candidate list
     */
    public function index()
    {
        Breadcrumb::add(Lang::get('resource::view.Candidate.List.Candidate list'));
        //get all candidates
        $pager = Config::getPagerData(null, ['order' => 'candidates.id', 'dir' => 'desc']);
        $list = CandidatePermission::getInstance()->getList($pager['order'], $pager['dir']);
        //filter and paginate
        CoreModel::filterGrid($list, [], null, 'LIKE');
        //$listAll = clone $list; //get filter options
        CoreModel::pagerCollection($list, $pager['limit'], $pager['page']);

        $positionOptions = getOptions::getInstance()->getRoles();
        $programList = Programs::all();
        $teamPermission = new TeamPermission();
        $recruiters = $teamPermission->getEmployeeByActionName('sendMailToCandidate.candidate')->pluck('email')->toArray();

        return view('resource::candidate.list', [
            //'listAll' => $listAll->get(),
            'collectionModel' => $list,
            'positionOptions' => $positionOptions,
            'programList' => $programList,
            'recruiters' => $recruiters
        ]);
    }

    public function history()
    {
        Breadcrumb::add(Lang::get('resource::view.Candidate.History.Candidate history action list'));
        $pager = Config::getPagerData();
        $pagerFilter = (array) Form::getFilterPagerData();
        $pagerFilter = array_filter($pagerFilter);
        $order = 'id';
        $dir = 'desc';
        if ($pagerFilter) {
            $order = $pager['order'];
            $dir = $pager['dir'];
        }
        $list = RecruitProcess::getInstance()->getList($order, $dir);
        if (count($list) > 0) {
            $list = CoreModel::filterGrid($list);
            $list = CoreModel::pagerCollection($list, $pager['limit'], $pager['page']);
        }

        return view('resource::candidate.history', [
                'collectionModel' => $list,
            ]);
    }

    /**
     * Download file cv of candidate
     * @param string $filename
     * @return type
     */
    public function downloadcv($filename)
    {
        $myFile = storage_path("app/".SupportConfig::get('general.upload_storage_public_folder')."/".Candidate::UPLOAD_CV_FOLDER.$filename);
        if (!file_exists($myFile)) {
           return redirect()->back()->with('messages', ['errors' => [trans('resource::message.File does not exist')]]);
        }
        $headers = ['Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        return response()->download($myFile, null, $headers);
    }
    
    /**
     * View file cv of candidate
     * @param string $filename
     * @return type
     */
    public function viewcv($id,$filename)
    {
        $candidate = Candidate::getCandidateById($id);
        $curEmp = Permission::getInstance()->getEmployee();
        if (!CandidatePermission::detailPermission($candidate, $curEmp)) {
            return redirect()->back()->with('messages', ['errors' => [trans('resource::message.File does not exist')]]);
        }
        $myFile = storage_path("app/".SupportConfig::get('general.upload_storage_public_folder')."/".Candidate::UPLOAD_CV_FOLDER.$filename);
        $headers = ['Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword,application/pdf'];
        return response()->file($myFile, $headers);
    }


    /**
     * delete candidate
     */
    public function deleteCandidate()
    {
        $id = Input::get('id');
        $model = Candidate::find($id);
        if (! $model) {
            return redirect()->route('resource::candidate.list')->withErrors(Lang::get('resource::messages.Not found item.'));
        }
        $model->delete();
        $messages = [
                'success'=> [
                    Lang::get('resource::message.Delete item success!'),
                ]
        ];
        return redirect()->route('resource::candidate.list')->with('messages', $messages);
    }

    /**
     * Check exists attach file
     * @param string $filename
     */
    public function checkAttachFile($filename)
    {
        $myFile = storage_path("app/".SupportConfig::get('general.upload_storage_public_folder')."/".Candidate::ATTACH_FOLDER.$filename);
        if (file_exists($myFile)) {
            echo '1'; //File exists
            return;
        }
        echo '0';
        return;
    }

    /**
     * Check unique candidate email
     * @return json
     */
    public function checkCandidateMail()
    {
        $valid = false;
        if (Input::get('email'))
        {
            $id = Input::get('id');
            $email = Input::get('email');
            if (!Candidate::checkExist($email, $id)) {
                $valid = true;
            }
        }
        return response()->json($valid);
    }

    /**
     * import cv
     */
    public function importcv()
    {
        Breadcrumb::add(Lang::get('resource::view.Candidate.Importcv.Candidate Import Cv'));
        return view('resource::candidate.importcv');
    }

    /**
     * save import cv
     */
    public function postImportcv(Request $requests)
    {
        $data = $requests->file;
        $destinationPath = SupportConfig::get('general.upload_folder') . '/' . Candidate::UPLOAD_CV_FOLDER;
        if (empty($data)) {
            return redirect()->route('resource::candidate.importcv')
                ->withErrors(Lang::get('resource::message.Not found item'));
        } else {
            $nameFileType = $data->getClientOriginalExtension();
            $nameFile = time().str_random(5).'_'.$data->getClientOriginalName();
            if (in_array($nameFileType, SupportConfig::get('services.file.cv_import'))) {
                $data = Excel::load($data->getRealPath(), function($reader) {
                    $reader->formatDates(false);
                    })->get();
                if (Candidate::checkFormatEcxel($data)) {
                    foreach ($data as $key => $value1) {
                        Candidate::insertCv($value1);
                    }
                    $messages = [
                            'success'=> [
                                Lang::get('resource::message.Import CV Success!'),
                            ]
                    ];
                    return redirect()->route('resource::candidate.importcv')->with('messages',$messages);
                } else {
                    return redirect()->route('resource::candidate.importcv')
                    ->withErrors(Lang::get('resource::message.File is not formatted correctly!'));
                }
            } else {
                return redirect()->route('resource::candidate.importcv')
                    ->withErrors(Lang::get('resource::message.File is not formatted correctly!'));
            }
        }
    }

    /**
     * Get team of request
     * @return json
     */
    public function getTeamByRequest()
    {
        $requestId = Input::get('request_id');
        if ($requestId) {
            $teamIds = Rview::getTeamByRequest($requestId);
            return response()->json($teamIds);
        }
    }

    /**
     * Get postion by team of request
     * @return json
     */
    public function getPositionByTeam()
    {
        $requestId = Input::get('request_id');
        $teamId = Input::get('team_id');
        if ($requestId && $teamId) {
            $positions = Rview::getPositionByTeam($requestId, $teamId);
            return response()->json($positions);
        }
    }

    /**
     * Get view check exist
     * @return view
     */
    public function checkExist()
    {
        Breadcrumb::add(Lang::get('resource::view.CheckExist.checkexist'));
        return view('resource::candidate.check_exist',['data' => Input::old()]);
    }

    /**
    * Check exist candidate
    * @param $value candidate
    * @return true fasle
    */
    public function postCheckExist(Request $request)
    {
        $data = $request->all();
        if (empty($data['fullname']) && empty($data['email'])) {
            return redirect()->route('resource::candidate.checkExist')
                ->withErrors(Lang::get('resource::message.Name candidate is unique field'));
        } else {
            $getCandidate = Candidate::select('id');
            if(!empty($data['fullname'])) {
                $getCandidate->where('fullname',trim(preg_replace('!\s+!', ' ', $data['fullname'])));
            }
            if(!empty($data['email'])) {
                $getCandidate->where('email',trim(preg_replace('!\s+!', ' ', $data['email'])));
            }
            if(!empty($data['birthday'])) {
                $getCandidate->where('birthday',$data['birthday']);
            }
            if(!empty($data['mobile'])) {
                $getCandidate->where('mobile',trim(preg_replace('!\s+!', ' ', $data['mobile'])));
            }
            $checkCandidate = $getCandidate->get();
        }
        if (!count($checkCandidate)) {
            return redirect()->route('resource::candidate.checkExist')
                ->withInput()
                ->with('messages',['success' => [Lang::get('resource::message.CheckExist does not exist')]]);
        } else {
            return redirect()->route('resource::candidate.checkExist')
                ->withInput()
                ->withErrors(Lang::get('resource::message.CheckExist Already exists'));
        }
    }

    /**
     * Create invite letter to send candidate
     * Button create in candidate detail page, tab offer
     *
     * @param int $candidateId
     */
    public function pdfSave($candidateId)
    {
        //Check exists and create path storage/fonts
        if (!file_exists(storage_path("fonts"))) {
            mkdir(storage_path("fonts"), 0777);
        }
        //Check exists and create attach path
        $attachPath = storage_path("app/" . SupportConfig::get('general.upload_storage_public_folder') . "/" . Candidate::ATTACH_FOLDER);
        if (!file_exists($attachPath)) {
            mkdir($attachPath, 0777, true);
        }

        $content = Input::get('content');
        $html = view('resource::candidate.invite_letter', compact('content'));
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");
        $pdf = PDF::loadHTML($html);
        $candidate = Candidate::find($candidateId);
        $filename = Rview::getInviteLeterName($candidate->email);
        $pdf->save($attachPath . $filename);
        chmod($attachPath . $filename, 0777);
        echo '1';
        return ;
    }

    /**
     * Send mail offer
     */
    public function sendMailOffer()
    {
        $data = Input::get();
        if (Rview::checkArrayEmptyValue($data)) {
            echo '-1'; //don't save mail
        } else {
            $curEmp = Permission::getInstance()->getEmployee();
            $leaderHr = CandidatePermission::getLeaderHr();

            //Save mail to queue
            $subject = $this->getMailSubject($data);
            $emailQueue = new EmailQueue();
            $emailQueue->setTo($data['candidate_email'])
                ->setSubject($subject);
            $emailQueue->addBcc($curEmp->email);
            $data['content'] = $this->replaceContentMail($curEmp,$data['content']);
            if ((int)$data['type'] == (int)Candidate::MAIL_RECRUITER) {
                $template = 'resource::candidate.mail.recruit';
                $emailQueue->setFrom(config('mail.username'), config('mail.name'))
                    ->setTemplate($template, $data);
            } else {
                $template = 'resource::candidate.mail.content';
                $emailQueue->setFrom(config('mail.username'), $curEmp->name)
                    ->addReply($curEmp->email)
                    ->addCc($curEmp->email)
                    ->setTemplate($template, $data);
            }

            //========= send email cho người giới thiệu khi thư mời phỏng vấn và thư cảm ơn ==============
            $arrTypeInterview = [
                (int)Candidate::MAIL_INTERVIEW_TEST_HH3,
                (int)Candidate::MAIL_INTERVIEW_TEST_HH4,
                (int)Candidate::MAIL_INTERVIEW_TEST_DN,
                (int)Candidate::MAIL_INTERVIEW_TEST_HCM,
                (int)Candidate::MAIL_INTERVIEW_TEST_JP,
                (int)Candidate::MAIL_INTERVIEW_TEST_HANDICO,
                (int)Candidate::MAIL_INTERVIEW_CONFIRM_JP,

                (int)Candidate::MAIL_INTERVIEW_FAIL_JP,
                (int)Candidate::MAIL_INTERVIEW_FAIL_HN,
                (int)Candidate::MAIL_INTERVIEW_FAIL_DN,
                (int)Candidate::MAIL_INTERVIEW_FAIL_HCM,
            ];
            if (in_array(((int)$data['type']), $arrTypeInterview)) {
                $dataCan = Candidate::find($data['candidate_id']);
                if ($dataCan && $dataCan->found_by) {
                    $foundBy = Employee::find($dataCan->found_by);
                    if ($foundBy) {
                        $emailQueue->addCc($foundBy->email);
                        $emailQueue->addCcNotify($foundBy->id);
                    }
                }
            }

            //get related
            $toEmpId = Employee::getIdByEmail($data['candidate_email']);
            $relatedIds = isset($data['related_ids']) ? $data['related_ids'] : [];
            //remove to employee id sent before
            if (($key = array_search($toEmpId, $relatedIds)) !== false) {
                unset($relatedIds[$key]);
            }
            $relateds = count($relatedIds) > 0 ?  Employee::getEmpByIds($relatedIds, ['id', 'name', 'email']) : null;
            $emailQueue->addCcRelated($relateds);
            $emailToCc = $relateds ? $relateds->pluck('email', 'email')->toArray() : [];

            //check type recruiter set notify
            if ((int) $data['type'] == Candidate::MAIL_RECRUITER) {
                $emailQueue->setNotify(
                    $toEmpId, null, route('resource::candidate.detail', $data['candidate_id']), ['category_id' => RkNotify::CATEGORY_HUMAN_RESOURCE]
                );
            }
            if ($leaderHr) {
                if ((int) $data['type'] == Candidate::MAIL_RECRUITER) {
                    $emailQueue->addCc($leaderHr->email);
                    $emailQueue->addCcNotify($leaderHr->id);
                    $emailToCc[$leaderHr->email] = $leaderHr->email;
                } else {
                    $emailQueue->addBcc($leaderHr->email);
                }
            }
            //If send mail to recruiter then cc to bod
            if ((int) $data['type'] == Candidate::MAIL_RECRUITER) {
                $bod = CoreConfigData::getBodEmail();
                if (!empty($bod)) {
                    $emailQueue->addCc($bod)
                            ->addCcNotify(Employee::getIdByEmail($bod));
                    $emailToCc[$bod] = $bod;
                }

                /* to Cc to leader and sub leader teams request */
                if (isset($data['candidate_id']) && ($candidate = Candidate::find($data['candidate_id']))) {
                    // ignore received email and current employee
                    $emailToCc[$data['candidate_email']] = $data['candidate_email'];
                    $emailToCc[$curEmp->email] = $curEmp->email;

                    $requestTeamIds = Candidate::getAllTeamOfCandidate($candidate);
                    $empTeamIds = Team::getTeamOfEmployee($curEmp->id)->pluck('id')->toArray();
                    $teamIds = array_intersect($requestTeamIds, $empTeamIds);
                    $leaderAndSubLeaders = TeamMember::getListLeaderByTeamIds($teamIds);
                    foreach ($leaderAndSubLeaders as $employee) {
                        $email = $employee->email;
                        if (!isset($emailToCc[$email])) {
                            $emailQueue->addCc($email);
                            $emailQueue->addCcNotify($employee->id);
                        }
                    }
                }
            }
            //Add attach file if mail type is offer
            $candidateModel = new Candidate();
            if (in_array((int)$data['type'], $candidateModel->getTypeOffer())) {
                $myFile = storage_path("app/".SupportConfig::get('general.upload_storage_public_folder')."/".Candidate::ATTACH_FOLDER.Candidate::FILE_NAME_TUTORIAL);
                $emailQueue->addAttachment($myFile, false);
                $myFile = storage_path("app/".SupportConfig::get('general.upload_storage_public_folder')."/".Candidate::ATTACH_FOLDER.Rview::getInviteLeterName($data['candidate_email']));
                $emailQueue->addAttachment($myFile, true);
            }
            $emailQueue->save();

            //Save mail to table candidate_mail
            $data['created_by'] = $curEmp->id;
            $data['relateds'] = $relateds;
            CandidateMail::saveData($data);
            $response['success'] = 1;

            //save comment to table candidate_comments
            if ((int) $data['type'] == Candidate::MAIL_RECRUITER) {
                $candidateComment = new CandidateComment();
                $candidateComment->candidate_id = $data['candidate_id'];
                $candidateComment->content =' Đã gửi mail offer tới ' . View::getNickName($data['candidate_email']);
                $candidateComment->save();
                $response['nameUserCurrent'] = $curEmp->name;
                $response['emailUserCurrent'] = $curEmp->email;
                $response['content'] = $candidateComment->content;
                $response['created_at'] = $candidateComment->created_at->format('Y-m-d h:i:s');
            }
            return response()->json($response);
        }
    }

    public static function sendMailThanks(Request $request)
    {
        $data = $request->all();
        $curEmp = Permission::getInstance()->getEmployee();
        $teamHr = Team::getTeamByType(Team::TEAM_TYPE_HR);
        $leaderHr = null;
        if ($teamHr) {
            $leaderHr = $teamHr->getLeader();
        }
        $template = 'resource::candidate.mail.thanks';
        $dataTemplate = [
            'name' => $curEmp->name,
            'email' => $curEmp->email,
            'skype' => $curEmp->skype,
            'phone' => $curEmp->mobile_phone,
            'candidateName' => $data['toName'],
        ];
        $emailQueue = new EmailQueue();
        $emailQueue->setTo($data['toEmail'])
            ->setFrom($curEmp->email, $curEmp->name)
            ->setSubject('[RIKKEISOFT] THƯ CẢM ƠN')
            ->setTemplate($template, $dataTemplate);
        $emailQueue->addBcc($curEmp->email);
        if ($leaderHr) {
            $emailQueue->addBcc($leaderHr->email);
        }
        $emailQueue->save();
        $dataCandidateMail = [
            'candidate_id' => $data['candidateId'],
            'candidate_email' => $data['toEmail'],
            'created_by' => $curEmp->id,
            'type' => Candidate::MAIL_THANKS,
        ];
        CandidateMail::saveData($dataCandidateMail);
    }

    /**
     * Get mail subject by type
     * @param int $type
     * @return string
     */
    public function getMailSubject($data)
    {
        switch ($data['type']) {
            case Candidate::MAIL_OFFER:
            case Candidate::MAIL_THANKS:
                return Lang::get('resource::view.Mail thanks.Subject');
            case Candidate::MAIL_OFFER_HH3:
            case Candidate::MAIL_OFFER_HH4:
            case Candidate::MAIL_OFFER_DN:
            case Candidate::MAIL_OFFER_HCM:
            case Candidate::MAIL_OFFER_HANDICO:
                return Lang::get('resource::view.Mail offer.Subject', ['name' => $data['candidate_fullname']]);
            case Candidate::MAIL_OFFER_JP:
                return Lang::get('resource::view.Mail offer.Subject Japan', ['name' => $data['candidate_fullname']]);
            case Candidate::MAIL_TEST:
                return Lang::get('resource::view.Mail test subject', ['name' => $data['candidate_fullname']]);
            case Candidate::MAIL_INTERVIEW:
            case Candidate::MAIL_INTERVIEW_TEST_HH3:
            case Candidate::MAIL_INTERVIEW_TEST_HH4:
            case Candidate::MAIL_INTERVIEW_TEST_DN:
            case Candidate::MAIL_INTERVIEW_TEST_HCM:
            case Candidate::MAIL_INTERVIEW_TEST_HANDICO:
                return Lang::get('resource::view.Mail interview subject' , ['name' => $data['candidate_fullname']]);
            case Candidate::MAIL_INTERVIEW_TEST_JP:
                return Lang::get('resource::view.Mail interview subject Japan');
            case Candidate::MAIL_INTERVIEW_CONFIRM_JP:
                return Lang::get('resource::view.Mail interview confirm subject Japan');
            case Candidate::MAIL_INTERVIEW_FAIL:
            case Candidate::MAIL_INTERVIEW_FAIL_HN:
            case Candidate::MAIL_INTERVIEW_FAIL_DN:
            case Candidate::MAIL_INTERVIEW_FAIL_HCM:
                return Lang::get('resource::view.Mail interview fail subject');
            case Candidate::MAIL_INTERVIEW_FAIL_JP:
                return Lang::get('resource::view.Mail interview fail Japan subject');
            case Candidate::MAIL_RECRUITER:
                return isset($data['mail_title']) ? $data['mail_title'] : '';
            default: return '';
        }
    }

    /**
     * Test history of candidate
     *
     * @param string $email
     * @param Datatables $datatables
     * @return json
     */
    function testHistory($email, $candidateId, Datatables $datatables)
    {
        $rikkeiCode = 'Rikkei Code';
        $results = Result::getListByEmail($email, Test::IS_NOT_AUTH, $candidateId);
        $ricodeTest = RicodeTest::where('candidate_id', $candidateId)->get();
        $ricodeDataFirst = $ricodeTest->first();

        if(!$ricodeTest->isEmpty() && $ricodeDataFirst && $ricodeDataFirst->title) {
            $ricodeTest->map(function($item) use($rikkeiCode) {
                $item->total_corrects = $item->total_correct_answers;
                $item->name = $rikkeiCode;
                $item->total_questions = (int)($item->level_easy) + (int)($item->level_medium) + (int)($item->level_hard);
                $item->total_answers = null;
                return $item;
            });
            $results = $results->merge($ricodeTest);
        }

        return $datatables
                ->of($results)
                ->addColumn('link', function ($model) use($rikkeiCode, $ricodeDataFirst) {
                    if($model->name == $rikkeiCode && $ricodeDataFirst) {
                        return '<a class="btn-edit" target="_blank" href="'.config('app.ricode_app_url'). $ricodeDataFirst->url_view_source.'" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i> </a>';
                    }
                    return '<a class="btn-edit" target="_blank" href="'.route('test::result', ['id' => $model->id]).'" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i> </a>';
                })
                ->make(true);
    }

    /** Get level of language
     *
     * @param Request $request
     * @return json
     */
    public function getLevelByLang(Request $request)
    {
        $data = $request->all();
        if ($data) {
            $languageId = $data['languageId'];
            $langLevel = LanguageLevel::getLevelByLanguage($languageId);
            return response()->json($langLevel);
        }
        return response()->json([]);
    }

     /**
     * replace content email
     */
    public function replaceContentMail($employee,$content)
    {
        $patterns = [
            '/\{\{ Name \}\}/',
            '/\{\{ Japanese Name \}\}/',
            '/\{\{ Phone \}\}/',
            '/\{\{ Email \}\}/',
            '/\{\{ Skype \}\}/',
        ];
        $replaces = [
            $employee->name,
            $employee->japanese_name,
            $employee->getFieldVal('contact', 'mobile_phone'),
            $employee->email,
            $employee->getFieldVal('contact', 'skype'),
        ];
        $result = preg_replace($patterns, $replaces, $content);
        return $result;
    }

    /**
     * update recruiter of selected candidates
     * @param Request $request
     * @return redirect to Candidate list page
     */
    public function updateRecruiter (Request $request)
    {
        $recruiterEmail = $request->get('recruiterList');

        try {
            $list = CandidatePermission::getInstance()->getList(null, null);
            CoreModel::filterGrid($list, [], route('resource::candidate.list'). '/', 'LIKE');
            $candidateId = $list->lists('id')->toArray();
            if ($candidateId) {
                Candidate::whereIn('id', $candidateId)->update(['recruiter' => $recruiterEmail]);
                $messages = [
                    'success'=> [
                        Lang::get('core::message.Update success'),
                    ]
                ];
            } else {
                $messages = [
                    'errors'=> [
                        Lang::get('core::message.Not found item'),
                    ]
                ];
            }

            return redirect()->route('resource::candidate.list')->with('messages', $messages);
        }
        catch (\Exception $ex) {
            Log::info($ex);
            DB::rollback();
            return redirect()->route('resource::candidate.list')->with('messages', ['errors' => [trans('core::message.Error system, please try later!')]]);
        }
    }

    public function checkExistEmpPropertyValue (Request $request)
    {
        $candidateid = $request->get('candidateid');
        $valueid = $request->get('valueid');
        $candidate = Candidate::find($candidateid);
        if (!$candidate) {
            return response()->json([
                'status' => 1,
            ]);
        }
        $emp = $candidate->employee;
        $field = $request->get('field');
        $oldEmpId = $request->get('old_employee_id');
        $cddEmpId = $oldEmpId ? $oldEmpId : ($emp ? $emp->id : null);
        if ($field == "card_id") {
            $empCode = Employee::getCodeFromCardId($valueid, $candidate->team_id, $candidate->working_type);
            // $existCardId = Employee::checkExistsEmpCode($empCode, $cddEmpId);
            $existCardId = Employee::getSuggestCardId($empCode, $cddEmpId);
            if ($existCardId > 0) {
                return response()->json([
                    'status' => 0,
                    'card_id' => $existCardId,
                ]);
            }
        } else {
            $existEmail = Employee::getSuggestEmail($valueid, $cddEmpId);
            if (strlen($existEmail) > 0) {
                return response()->json([
                    'status' => 0,
                    'email' => $existEmail,
                ]);
            }
        }

        return response()->json([
            'status' => 1,
        ]);
    }


    /**
     * Search candidate page
     * @return View
     */
    public function search()
    {
        $columns = Candidate::getColumns();

        //Get column selected
        if ($columnsSelected = CookieCore::get(Candidate::COOKIE_COLUMN_SEARCH_ADVANCE)) {
            $columnsSelected = json_decode($columnsSelected, true);
        } else {
            $columnsSelected = [];
            foreach ($columns as $column) {
                if (isset($column['default'])) {
                    $columnsSelected[] = $column;
                }
            }
        }

        $teamsOptionAll = TeamList::toOption(null, true, false);
        $positionOptions = getOptions::getInstance()->getRoles();
        $channelOptions = Channels::getInstance()->getList();
        $testType = Type::getList();
        $resultOptions = getOptions::getInstance()->getResultOption();
        $statusOptions = getOptions::getInstance()->getCandidateStatusOptionsAll();
        $teamPermission = new TeamPermission();
        $hrAccounts = $teamPermission->getEmployeeByActionName('sendMailToCandidate.candidate')->pluck('email')->toArray();
        $typeOptions = Candidate::getTypeOptions();
        $langs = Languages::getInstance()->getListWithLevel();
        $programs = Programs::getInstance()->getList();
        $langArray = Candidate::langWithLevel($langs);
        $workingtypeOptions = getOptions::getInstance()->getWorkingType();
        $allTypeCandidate = Candidate::getAllTypeCandidate();
        $permissExport = Permission::getInstance()->isAllow(Candidate::ROUTE_EXPORT_SEARCH);
        return view('resource::candidate.search', compact([
            'columnsSelected', 'teamsOptionAll', 'positionOptions',
            'channelOptions', 'testType', 'resultOptions',
            'statusOptions', 'hrAccounts', 'typeOptions', 'permissExport',
            'langs', 'programs', 'langArray', 'columns', 'workingtypeOptions', 'allTypeCandidate'
        ]));
    }

    /**
     * Search candidate click event
     * @param Datatables $datatables
     * @return Datatables
     */
    public function searchAdvance(Datatables $datatables)
    {
        $filter = Input::get('filter');
        $columnsSelected = Input::get('columnsField');
        $searchType = Input::get('type');

        //store selected column into cookie to load at next time
        $allColumns = Candidate::getColumns();
        $columns = [];
        foreach ($columnsSelected as $col) {
            if ($getColumn = View::getArrayInArrayTwodimensional($allColumns, 'field', $col)) {
                $columns[] = $getColumn;
            }
        }
        CookieCore::set(Candidate::COOKIE_COLUMN_SEARCH_ADVANCE, json_encode($columns));

        $isExport = ($searchType == 'export');
        //Search from db
        $collection = Candidate::advanceSearch($columnsSelected, $filter, $isExport);
        //check if export
        if ($isExport) {
            return $this->exportSearch($collection, $columns, $columnsSelected);
        }

        return $datatables
                ->of($collection)
                ->editColumn('status', function ($model) {
                    return getOptions::getInstance()->getCandidateStatus($model->status, $model);
                })
                ->editColumn('position_apply', function ($model) {
                    $positions = explode(',', $model->position_apply);
                    $posName = [];
                    if (is_array($positions) && count($positions)) {
                        foreach ($positions as $positionId) {
                            $posName [] = getOptions::getInstance()->getRole($positionId);
                        }
                        return implode(', ', $posName);
                    }
                    return null;
                })
                ->editColumn('university', function ($model) {
                    return str_replace("\n", '<br>', $model->university);
                })
                ->editColumn('certificate', function ($model) {
                    return str_replace("\n", '<br>', $model->certificate);
                })
                ->editColumn('old_company', function ($model) {
                    return str_replace("\n", '<br>', $model->old_company);
                })
                ->editColumn('contact_result', function ($model) {
                    if ($model->contact_result == getOptions::RESULT_DEFAULT) {
                        return Lang::get('resource::view.Contacting');
                    } else {
                        return getOptions::getInstance()->getResult($model->contact_result);
                    }
                })
                ->editColumn('test_result', function ($model) {
                    if ($model->test_result == getOptions::RESULT_DEFAULT) {
                        return Lang::get('resource::view.Testing');
                    } else {
                        return getOptions::getInstance()->getResult($model->test_result);
                    }
                })
                ->editColumn('interview_result', function ($model) {
                    if ($model->interview_result == getOptions::RESULT_DEFAULT) {
                        return Lang::get('resource::view.Interviewing');
                    } else {
                        return getOptions::getInstance()->getResult($model->interview_result);
                    }
                })
                ->editColumn('offer_result', function ($model) {
                    if ($model->offer_result == getOptions::RESULT_DEFAULT) {
                        return Lang::get('resource::view.Offering');
                    } elseif ($model->offer_result == getOptions::RESULT_WORKING) {
                        return Lang::get('resource::view.Candidate.Detail.Working');
                    }else {
                        return getOptions::getInstance()->getResult($model->offer_result);
                    }
                })
                ->editColumn('gender', function ($model) {
                    return getOptions::getInstance()->getGender($model->gender);
                })
                ->editColumn('type', function ($model) {
                    return Candidate::getType($model->type);
                })
                ->editColumn('type_candidate', function ($model) {
                    return Candidate::getTypeCandidate($model->type_candidate);
                })
                ->editColumn('working_type', function ($model) {
                    return getOptions::getInstance()->getContractTypeByType($model->working_type);
                })
                ->addColumn('', function ($model) {
                    return '<a class="btn-edit" target="_blank" href="'.route('resource::candidate.detail', ['id' => $model->id]).'" class="btn btn-xs btn-primary"><i class="fa fa-info-circle"></i> </a>';
                })
                ->make(true);
    }

    /*
     * export search
     */
    public function exportSearch($collection, $columns, $colSelected)
    {
        $routePermiss = Candidate::ROUTE_EXPORT_SEARCH;
        if (!Permission::getInstance()->isAllow($routePermiss)) {
            return View::viewErrorPermission();
        }
        $colHead = [];
        foreach ($columns as $col) {
            $len = strlen($col['label']);
            $colHead[$col['data']] = [
                'tt' => $col['label'],
                'wch' => $len > 10 ? $len : 10,
            ];
        }
        $orders = Input::get('order');
        if (!$orders || !is_array($orders) || !isset($colSelected[$orders[0]])) {
            $orders = [0, 'asc'];
        }
        $collection->orderBy($colSelected[$orders[0]], $orders[1]);
        return [
            'colsHead' => $colHead,
            'sheetsData' => [
                'Candidates' => $collection->get()
            ],
            'fileName' => 'Candidate_' . Carbon::now()->now()->format('Y_m_d')
        ];
    }

    /**
     * check exists employee email
     */
    public function checkEmployeeEmail()
    {
        $email = Input::get('email');
        $employeeId = Input::get('employee_id');
        if (!$email) {
            return response()->json(['exists' => 0]);
        }
        $exists = Employee::where('email', $email);
        if ($employeeId && is_numeric($employeeId)) {
            $exists->where('id', '!=', $employeeId);
        }
        return response()->json([
            'exists' => $exists->first() ? 1 : 0,
            'message' => trans('resource::message.Email candidate has been taken, are you sure want to continue?', [
                'email' => $email
            ])
        ]);
    }

    /**
     * Get data from webvn insert to intranet
     * @param Request $request
     * @return string
     * @throws \Rikkei\Resource\Model\Exception
     */
    public function insertIntranet(Request $request)
    {
        if ($request->isMethod('post')) {
            //check access token
            $bearerToken = $request->bearerToken();
            if ($bearerToken != CoreConfigData::getApiToken()) {
                return ['success' => trans('resource::message.Access token invalid')];
            }

            //check required value
            if (!$request->get('email') || !$request->get('fullname')) {
                return ['success' => trans('resource::message.Email and Fullname are required')];
            }
            $request = $request->except('_token');
            if (!empty($request['position_apply'])) {
                $positions[] = $request['position_apply'];
            }
            /*if (isset($request['g-recaptcha-response']) && $request['g-recaptcha-response']) {
                $verified = $this->verifyCaptcha($request['g-recaptcha-response']);
                if ($verified) {*/
                    if (isset($request['cvfile']) && $request['cvfile'] && $request['cvfile']['data']) {
                        try {
                            $decodedFile = base64_decode($request['cvfile']['data']);
                        } catch (Exception $ex) {
                            $decodedFile = null;
                        }
                        $pathFolder = SupportConfig::get('general.upload_storage_public_folder') . '/' . Candidate::UPLOAD_CV_FOLDER;
                        if ($decodedFile) {
                            $request['cv'] = trim($pathFolder, '/') . '/'
                                . str_random(5) . '_' . time() . '.'
                                . (isset($request['cvfile']['ext']) ? $request['cvfile']['ext'] : '');
                            Storage::put(
                                $request['cv'],
                                $decodedFile
                            );
                        }
                        $request['received_cv_date'] = Carbon::now()->format('Y-m-d');
                    }
                    $candidate = Candidate::where('email', $request['email'])->orderBy('id', 'desc')->first();
                    if (!$candidate) {
                        $request['status'] = getOptions::DRAFT;
                        $request['received_cv_date'] = date('Y-m-d');
                        $request['type_candidate'] = isset($request['type_recruitment']) ? $request['type_recruitment'] : Candidate::TYPE_FROM_WEBVN;
                        $candidate = Candidate::create($request);

                        if (!empty($request['requests'])) {
                            $candidate->candidateRequest()->attach($request['requests']);
                        }
                        if (!empty($request['programs'])) {
                            $candidate->candidateProgramming()->attach($request['programs']);
                        }
                        if (!empty($request['languages'])) {
                            $dataLang = [];
                            foreach ($request['languages'] as $lang) {
                                $dataLang[] = [
                                    'candidate_id' => $candidate->id,
                                    'lang_id' => $lang,
                                ];
                            }
                            CandidateLanguages::insert($dataLang);
                        }
                        if (isset($positions)) {
                            Candidate::insertPostions($candidate, $positions);
                        }
                    } else {
                        if (!CandidatePermission::canReApply($candidate->status)) {
                            if ($candidate->status == getOptions::DRAFT) {
                                $request['status'] = getOptions::DRAFT;
                            }
                            if (!empty($request['programs'])) {
                                CandidateProgramming::where('candidate_id', $candidate->id)->delete();
                                $candidate->candidateProgramming()->attach($request['programs']);
                            }
                            if (!empty($request['languages'])) {
                                CandidateLanguages::where('candidate_id', $candidate->id)->delete();
                                $dataLang = [];
                                foreach ($request['languages'] as $lang) {
                                    $dataLang[] = [
                                        'candidate_id' => $candidate->id,
                                        'lang_id' => $lang,
                                    ];
                                }
                                CandidateLanguages::insert($dataLang);
                            }
                            if (isset($positions)) {
                                Candidate::insertPostions($candidate, $positions);
                            }
                            if (!empty($request['requests'])) {
                                $requestOld = Candidate::getAllRequestOfCandidate($candidate);
                                if (!count($requestOld) || !in_array($request['requests'], $requestOld)) {
                                    $candidate->candidateRequest()->attach($request['requests']);
                                }
                            }
                            $candidate = $candidate->fill($request);
                            $candidate->save();
                        } else {
                            $request['received_cv_date'] = date('Y-m-d');
                            $request['type_candidate'] = isset($request['type_recruitment']) ? $request['type_recruitment'] : Candidate::TYPE_FROM_WEBVN;
                            $request['status'] = getOptions::CONTACTING;
                            if ($candidate->parent_id) {
                                $request['parent_id'] = $candidate->parent_id;
                            } else {
                                $request['parent_id'] = $candidate->id;
                            }
                            $candidate = Candidate::create($request);

                            if (!empty($request['requests'])) {
                                $candidate->candidateRequest()->attach($request['requests']);
                            }
                            if (!empty($request['programs'])) {
                                $candidate->candidateProgramming()->attach($request['programs']);
                            }
                            if (!empty($request['languages'])) {
                                $dataLang = [];
                                foreach ($request['languages'] as $lang) {
                                    $dataLang[] = [
                                        'candidate_id' => $candidate->id,
                                        'lang_id' => $lang,
                                    ];
                                }
                                CandidateLanguages::insert($dataLang);
                            }
                            if (isset($positions)) {
                                Candidate::insertPostions($candidate, $positions);
                            }
                            if (!empty($candidate->recruiter)) {
                                //Send mail to recruiter
                                $recruiter = Employee::getEmpByEmail($candidate->recruiter);
                                $emailQueue = new EmailQueue();
                                $emailQueue->setTo($candidate->recruiter)
                                    ->setFrom(Config('mail.username'), Config('mail.name'))
                                    ->setSubject(Lang::get('resource::view.【Rikkeisoft】 The candidate :name has just been created then assign to you', ['name' => $candidate->fullname]))
                                    ->setTemplate('resource::candidate.mail.reapply', [
                                        'recruiterName' => View::getNickName($candidate->recruiter),
                                        'candidateName' => $candidate->fullname,
                                        'urlToCandidate' => route('resource::candidate.detail', $candidate->id),
                                    ])
                                    ->setNotify(
                                        $recruiter->id,
                                        Lang::get('resource::view.The candidate :name has just been created then assign to you', ['name' => $candidate->fullname]),
                                        route('resource::candidate.detail', $candidate->id), ['category_id' => RkNotify::CATEGORY_HUMAN_RESOURCE]
                                    );
                                $emailQueue->save();
                                $request['recruiter'] = $candidate->recruiter;
                            }
                        }

                    }
                    return $message['success'] = trans('resource::message.You have successfully applied');
                /*} else {
                    return $message['success'] = trans('resource::message.Captcha has expired. Please try again.');
                }
            } else {
                return $message['success'] = trans('resource::message.Captcha has expired. Please try again.');
            }*/
        }
    }

    /**
     * Get level by language
     * @return array
     */
    public function getLanguages($key)
    {
        $token = CoreConfigData::getApiToken();
        if ($key == $token) {
            $langs = Languages::getInstance()->getListWithLevel();
            return  Candidate::langWithLevel($langs);
        } else {
            return "Token invalid";
        }
    }

    /**
     * Check google calendar room available
     *
     * @param Request $request
     * @return Response View `resource::candidate.include.google_calendar`
     */
    public function checkRoomAvailable(Request $request)
    {
        if ($request->session()->has(GoogleCalendarHelp::TOKEN_NAME)) {
            $client = GoogleCalendarHelp::initClient();
            $client->setAccessToken($request->session()->get(GoogleCalendarHelp::TOKEN_NAME));

            $service = new Google_Service_Calendar($client);
            $calendarList = $service->calendarList->listCalendarList();

            $calendarListGroup = GoogleCalendarHelp::groupCalendar($calendarList->getItems());
            $roomUnavailable = GoogleCalendarHelp::getRoomUnavailable($service, $calendarList, $request->get('minDate'), $request->get('maxDate'));

            return view('resource::candidate.include.google_calendar', [
                'calendarList' => $calendarListGroup,
                'roomUnavailable' => $roomUnavailable,
            ]);
        }
    }

    /**
     * get data of calendar event form
     *
     * @param Request $request
     * @return response json
     */
    public function getFormCalendar(Request $request)
    {
        //Check if session access token has expire then flush session to get new token
        if (GoogleCalendarHelp::isTokenHasExpire($request)) {
            GoogleCalendarHelp::flushSession($request);
        }

        if ($request->session()->has(GoogleCalendarHelp::TOKEN_NAME)) {
            $client = GoogleCalendarHelp::initClient();
            $client->setAccessToken($request->session()->get(GoogleCalendarHelp::TOKEN_NAME));

            $service = new Google_Service_Calendar($client);
            $calendarList = $service->calendarList->listCalendarList();

            $calendarListGroup = GoogleCalendarHelp::groupCalendar($calendarList->getItems());

            $oldCalendarId = $request->get('calendarId');
            $oldEventId = $request->get('eventId');
            $notFound = false;
            if (empty($oldCalendarId) || empty($oldEventId)) {
                $dateDefault = GoogleCalendarHelp::getStartEndDateDefault();
                $event = null;
            } else {
                $event = $service->events->get($oldCalendarId, $oldEventId);

                //Not event creator
                if ($event && $event->getCreator() && $event->getCreator()->email !== Permission::getInstance()->getEmployee()->email) {
                    return response()->json([
                        'success' => 1,
                        'isCreator' => false,
                    ]);
                }

                //Not found event
                if (!$event || $event->getStatus() === GoogleCalendarHelp::EVENT_CANCELLED) {
                    $dateDefault = GoogleCalendarHelp::getStartEndDateDefault();
                    $event = null;
                    $notFound = true;
                }
            }
            if (!$event && $request->get('candidateId')) {
                $dateDefault =  GoogleCalendarHelp::getStartEndDateDefaultNew($request->get('candidateId'));
            }
            //get interviewers data
            if (!empty($request->get('interviewerIds'))) {
                $interviewers = Employee::getEmpByIds($request->get('interviewerIds'));
            } else {
                $interviewers = null;
            }

            return response()->json([
                'success' => 1,
                'data' => $calendarListGroup,
                'interviewers' => $interviewers,
                'title' => $event ? $event->getSummary() : Rview::defaultSubjectMailInterviewer(Candidate::find($request->get('candidateId')), false),
                'minDate' => $event ? GoogleCalendarHelp::formatDate($event->getStart()->dateTime, true) : $dateDefault['startDate'],
                'maxDate' => $event ? GoogleCalendarHelp::formatDate($event->getEnd()->dateTime, true) : $dateDefault['endDate'],
                'description' => $event ? $event->getDescription() : '',
                'roomId' => $event ? GoogleCalendarHelp::getRoomOfEvent($event) : 0,
                'notFound' => $notFound,
                'isCreator' => true,
            ]);
        } else {
            $redirect_uri = route('resource::candidate.oauth2callback');
            header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
        }
    }

    /**
     * Save Calendar event
     *
     * @param Request $request
     * @return response jon
     */
    public function saveCalendar(Request $request)
    {
        if (empty($request->get('title')) || empty($request->get('startDate'))
                || empty($request->get('endDate')) || empty($request->get('attendeesId'))
                || empty($request->get('roomId'))) {
            return;
        }

        $dataInsert = [
            'title' => $request->get('title'),
            'startDate' => $request->get('startDate'),
            'endDate' => $request->get('endDate'),
            'roomId' => $request->get('roomId'),
            'location' => $request->get('location'),
            'description' => $request->get('description'),
            'attendeesId' => $request->get('attendeesId'),
        ];
        if ($request->session()->has(GoogleCalendarHelp::TOKEN_NAME)) {
            $client = GoogleCalendarHelp::initClient();
            $client->setAccessToken($request->session()->get(GoogleCalendarHelp::TOKEN_NAME));

            //insert or update event
            $calendarId = $request->get('calendarId');
            $eventId = $request->get('eventId');
            $service = new Google_Service_Calendar($client);
            if (empty($calendarId) || empty($eventId)) {
                $calendarId = Auth::user()->email;
            } else {
                $event = $service->events->get($calendarId, $eventId);
                //check event has been deleted forever?
                $eventId = $event->getCreated() ? $eventId : null;
            }
            $event = GoogleCalendarHelp::saveEvent($client, $dataInsert, $calendarId, $eventId);

            if ($event) {
                Candidate::where('id', $request->get('candidateId'))
                        ->update([
                            'calendar_id' => $calendarId,
                            'event_id' => $event->getId(),
                        ]);

                $mailNewPerson = [];
                $interviewersSent = CandidateMailInterviewer::getInterviewerSent($request->get('candidateId'));
                $interviewersIdSent = CandidateMailInterviewer::getInterviewerIdSent($interviewersSent);

                //Get interviewers haven't sent
                foreach ($request->get('attendeesId') as $attendee) {
                    if (!in_array($attendee, $interviewersIdSent)) {
                        $mailNewPerson[] = $attendee;
                    }
                }

                //Get interviewers have sent but meeting information changed from last time
                $interviewersResendMail = CandidateMailInterviewer::getInterviewersResendMail($interviewersSent, $dataInsert);

                //Delete interviewers have been removed
                $removedPerson = [];
                foreach ($interviewersIdSent as $removed) {
                    if (!in_array($removed, $request->get('attendeesId'))) {
                        $removedPerson[] = $removed;
                        if (($key = array_search($removed, $interviewersResendMail)) !== false) {
                            unset($interviewersResendMail[$key]);
                        }
                    }
                }
                if (count($removedPerson)) {
                    CandidateMailInterviewer::deleteData($request->get('candidateId'), $removedPerson);
                }

                //all mail send
                $sendMailPerson = array_merge($mailNewPerson, $interviewersResendMail);

                //insert db
                if (count($sendMailPerson)) {
                    DB::beginTransaction();
                    try {
                        //delete old data
                        CandidateMailInterviewer::deleteData($request->get('candidateId'), $sendMailPerson);
                        //insert new data
                        CandidateMailInterviewer::insertData($request->get('candidateId'), $sendMailPerson, $dataInsert);
                        //Send mail
                        $curEmp = Permission::getInstance()->getEmployee();
                        $candidate = Candidate::find($request->get('candidateId'));
                        Rview::sendMailToInterviewer($sendMailPerson, $candidate, $curEmp, $dataInsert);
                        DB::commit();
                    } catch (Exception $ex) {
                        DB::rollback();
                        throw $ex;
                    }

                }

                return response()->json([
                    'success' => 1,
                    'calendar_id' => $calendarId,
                    'event_id' => $event->getId(),
                    'interviewers' => Employee::getEmpByIds($request->get('attendeesId')),
                ]);
            }
            return response()->json([
                'success' => 0,
            ]);
        }
    }

    /**
     * Authentication when open calendar event form
     *
     * @param Request $request
     */
    public function oauth2callback(Request $request)
    {
        $client = GoogleCalendarHelp::initClient();

        if (!isset($_GET['code'])) {
            $auth_url = $client->createAuthUrl();
            return response()->json([
                'success' => 0,
                'auth_url' => filter_var($auth_url, FILTER_SANITIZE_URL)
            ]);
        } else {
            $client->authenticate($_GET['code']);
            $request->session()->put(GoogleCalendarHelp::TOKEN_NAME, $client->getAccessToken());
            $request->session()->put(GoogleCalendarHelp::LAST_ACTIVITY, time());

            //Close window authenticate
            echo "<script>window.close();</script>";
        }
    }

    /**
     * Update status candidate
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request)
    {
        $id = $request->input('candidate_id');
        if ($id) {
            $candidate = Candidate::find($id);
            $candidate->status = getOptions::CONTACTING;
            $curEmp = Permission::getInstance()->getEmployee();
            $candidate->recruiter = $curEmp->email;
            $candidate->save();
            return redirect()->back()->with('message', 'Update status candidate success');
        }
    }

    /**
     * Validate captcha
     * @param string $response
     * @return bool
     */
    public function verifyCaptcha($response)
    {
        $googleUrl = 'https://www.google.com/recaptcha/api/siteverify';

        $captcha = Config('services.captcha');
        $url = $googleUrl."?secret=". $captcha['secret_key']. "&response=".$response;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        $curlData = curl_exec($curl);
        curl_close($curl);
        $res = json_decode($curlData, true);
        $check = false;
        if ($res['success'] == 'true') {
            $check = true;
        }
        return $check;
    }

    /**
     * Re-apply candidate action
     *
     * @param Request $request
     * @return type
     */
    public function reapply(Request $request)
    {
        $candidateId = $request->get('candidate_id');
        $candidate = Candidate::find($candidateId);
        $curEmp = Permission::getInstance()->getEmployee();
        //Check permission
        $messageError = null;
        if (!$messageError && (!CandidatePermission::canReApply($candidate->status) || $candidate->countActiveStatus())) {
            $messageError = [
                'errors' => [
                    Lang::get('resource::message.Can not re-apply this candidate')
                ]
            ];
        }
        if ($messageError) {
            return redirect()->route('resource::candidate.edit', $candidate->id)
                    ->with('messages', $messageError);
        }

        //Replicate candidate
        if ($candidate) {
            DB::beginTransaction();
            try {
                $objectPermission = new CandidatePermission();
                if ($copyCandidate = $objectPermission->copyCandidate($candidate, $objectPermission->exceptColumnsCopy())) {
                    //Send mail to recruiter
                    if (!empty($candidate->recruiter)) {
                        if (null === $recruiter = Employee::getEmpByEmail($candidate->recruiter)) {
                            $messages = [
                                'errors' => [
                                    Lang::get('resource::message.Recruiter does not exist'),
                                ],
                            ];
                            return redirect()->route('resource::candidate.edit', $candidate->id)
                                ->with('messages', $messages);
                        }
                        $emailQueue = new EmailQueue();
                        $emailQueue->setTo($candidate->recruiter)
                            ->setFrom(Config('mail.username'), Config('mail.name'))
                            ->setSubject(Lang::get('resource::view.【Rikkeisoft】 The candidate :name has just been re-apply', ['name' => $copyCandidate->fullname]))
                            ->setTemplate('resource::candidate.mail.reapply', [
                                'recruiterName' => View::getNickName($candidate->recruiter),
                                'candidateName' => $copyCandidate->fullname,
                                'urlToCandidate' => route('resource::candidate.edit', $copyCandidate->id),
                            ])
                            ->setNotify(
                                $recruiter->id,
                                Lang::get('resource::view.The candidate `:name` has just been re-apply', ['name' => $copyCandidate->fullname]),
                                route('resource::candidate.edit', $copyCandidate->id), ['category_id' => RkNotify::CATEGORY_HUMAN_RESOURCE]
                            );
                        $emailQueue->save();
                    }

                    DB::commit();
                    $messages = [
                        'success' => [
                            Lang::get('resource::message.Re-apply success'),
                        ],
                    ];
                    return redirect()->route('resource::candidate.edit', $copyCandidate->id)
                        ->with('messages', $messages);
                } else {
                    $messages = [
                        'errors' => [
                            Lang::get('resource::message.Re-apply error. Please try again'),
                        ],
                    ];
                    return redirect()->route('resource::candidate.edit', $candidate->id)
                        ->with('messages', $messages);
                }
            } catch (Exception $ex) {
                DB::rollback();
                Log::info($ex->getMessage());
            }
        }
    }

    /**
    *save, edit comment Candidate
    */
    public function saveCommentCandidate()
    {
        $candidate = Candidate::find(Input::get('id'));
        $dataComment = Input::get('candidate_comment');
        $dataComment['content'] = trim(htmlentities($dataComment['content']));
        $commentId = Input::get('comment_id');
        $response = [];
        if (!$candidate) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        //check validate
        $validator = Validator::make($dataComment, [
            'content' => 'required'
        ]);
        if ($validator->fails()) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error input data!');
            return response()->json($response);
        }
        //save comment candidate.
        DB::beginTransaction();
        try {
            if ($commentId) {
                $commentUpdate = CandidateComment::find($commentId);
                $commentUpdate->content = $dataComment['content'];
                $commentUpdate->save();
            } else {
                $candidateComment = new CandidateComment();
                $candidateComment->candidate_id = $candidate->id;
                $candidateComment->setData($dataComment)->save();
                $response['created_at'] = $candidateComment->created_at->format('Y-m-d H:i:s');
                //send mail related
                $candidateComment->sendMailRelated($candidate);
            }
            $candidate->updated_at = date('Y-m-d H:i:s');
            $candidate->save();
            $response['success'] = 1;
            $response['message'] = Lang::get('project::message.Save');
            $response['popup'] = 1;
            DB::commit();
            return response()->json($response);
        } catch (Exception $ex) {
            DB::rollBack();
            $response['error'] = 1;
            $response['message'] = Lang::get('resource::message.System Error!');
            return response()->json($response);
        }
        

        
    }

    /**
     * show list comment by ajax
     */
    public function commentListAjax($id)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $candidate = Candidate::find($id);
        if (!$candidate) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        $response['success'] = 1;
        $response['html'] = ViewLaravel::make('resource::candidate.include.comment.list_comment', [
                'collectionModel' => CandidateComment::getGridData($id)
            ])->render();
        return response()->json($response);
    }

    /**
    * delete comment candidate
    */
    public function deleteComment(Request $request)
    {
        $comment = CandidateComment::find($request->id);
        if (!$comment) {
            return response()->json([
                'status' => 0,
                'message' => trans('core::message.Not found item'),
            ], 500);
        }
        DB::beginTransaction();
        try {
            $comment->delete();
            DB::commit();
            return response()->json([
                'status' => 1,
            ]);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return response()->json([
                'status' => 0,
                'message' => trans('core::message.Error system'),
            ], 500);
        }
    }

    /**
     * get old employee information
     * @param Request $request
     * @return json
     */
    public function getEmployeeInfo(Request $request)
    {
        $employeeId = $request->get('employee_id');
        if (!$employeeId) {
            return response()->json('Not found item', 404);
        }
        $fields = [];
        $fieldsRq = $request->get('employee');
        if ($fieldsRq) {
            $empFillable = Employee::getFillableCols();
            $contactFillable = ['native_country', 'native_province', 'native_district', 'native_ward', 'native_addr'];
            $dateFields = ['id_card_date'];
            foreach ($fieldsRq as $field => $values) {
                if (is_array($values)) {
                    foreach ($values as $extraField => $extValues) {
                        if (in_array($extraField, $contactFillable)) {
                            $fields[] = $field.'.'.$extraField ;
                        }
                    }
                } else {
                    if (in_array($field, $empFillable)) {
                        $strField = 'employees.' . $field;
                        if (in_array($field, $dateFields)) {
                            $strField = DB::raw('(CASE WHEN '. $strField .' IS NULL OR DATE('. $strField .') = "0000-00-00" '
                                    . 'THEN NULL ELSE DATE('.$strField.') END) AS ' . $field);
                        }
                        $fields[] = $strField;
                    }
                }
            }
        }
        if (!$fields) {
            return response()->json('Not found item', 404);
        }
        return Employee::select($fields)
                ->leftJoin(EmployeeContact::getTableName() . ' as contact', 'employees.id', '=', 'contact.employee_id')
                ->where('employees.id', $employeeId)
                ->first();
    }

    public function dataSendRicode($candidate, $fieldCode) {
        $data = [
            "user_info" => [
                "candiate_id" => $candidate->id,
                "email"  => $candidate->email,
                "name"  => $candidate->fullname,
                "gender" => (int)($candidate->gender),
                "phone" => $candidate->mobile,
                "birthday" => $candidate->birthday
            ],
            "exam" => [
                "easy_problem" =>(int)$fieldCode['level_easy'],
                "medium_problem" =>(int)$fieldCode['level_medium'],
                "hard_problem" =>(int)$fieldCode['level_hard'],
                "duration" => (int)$fieldCode['duration']
            ]
        ];

        return $data;
    }

    public function dataUpdateRicode($data) {
        unset($data['user_info']['name']);
        unset($data['user_info']['email']);
        unset($data['user_info']['gender']);
        unset($data['user_info']['address']);
        unset($data['user_info']['phone']);
        unset($data['user_info']['birthday']);

        return $data;
    }

    public function updateRecordRikkei($fieldCode, $res_body) {
        return [
            'url' => $res_body->exam_url,
            'exam_id' => (int)$res_body->exam_id,
            'level_easy' => (int)$fieldCode['level_easy'],
            'level_medium' => (int)$fieldCode['level_medium'],
            'level_hard' => (int)$fieldCode['level_hard'],
            'duration' => (int)$fieldCode['duration'],
            'candidate_id' => (int)$fieldCode['candidate_id']
        ];
    }

    public function requestToRicode($fieldCode, $candidate) {
        try {
            $client = new Client();
            $url = config('app.ricode_url').'/exam/candiate';
            $header = [
                'Content-Type' => 'application/json',
                'AuthRicode' => base64_encode(config('app.auth_ricode'))
            ];
            $data = $this->dataSendRicode($candidate, $fieldCode);

            if($fieldCode['type'] == 'create') {
                $res = $client->post($url, [
                    'headers' => $header,
                    'body' => json_encode($data)
                ]);
            }

            if($fieldCode['type'] == 'update') {
                $dataUpdate = $this->dataUpdateRicode($data);
                $ricodeTest = RicodeTest::where('candidate_id', $fieldCode['candidate_id'])->first();
                $dataUpdate['exam']['exam_id'] = $ricodeTest->exam_id;

                $res = $client->put($url, [
                    'headers' => $header,
                    'body' => json_encode($dataUpdate)
                ]);
            }

            $res_status = $res->getStatusCode();
            if($res_status == 200) {
                $res_body = json_decode($res->getBody());
                if(isset($res_body) && isset($res_body->errors)) {
                    if(isset($res_body->errors->exam_info)) {
                        return $this->responseJson('error', $res_body->errors->exam_info);
                    } else {
                        throw new Exception("exam_info not found");
                    }
                }
                $dataCreate = $this->updateRecordRikkei($fieldCode, $res_body->data);

                if(RicodeTest::updateOrCreate(['candidate_id' => $fieldCode['candidate_id']], $dataCreate)) {
                    $password = isset($res_body->data->password) ? $res_body->data->password : null;
                    $this->updateTypeOptionCandidate($candidate);
                    return $this->responseJson('Success', 200, ['password' => $password]);
                }
            }
        } catch(Exception $e) {
            $this->responseJson('System error', 500);
            return false;
        }
    }

    public function createRicodeTest(Request $request) {
        $fieldCode = $request->only(
            'level_easy',
            'level_medium',
            'level_hard',
            'duration',
            'candidate_id',
            'type'
        );

        $riCodeValid = Validator::make($fieldCode, [
            'level_easy'=> 'numeric|min:0',
            'level_medium'=> 'numeric|min:0',
            'level_hard'=> 'numeric|min:0',
            'duration'=> 'required|numeric|min:1',
            'candidate_id'=> 'required|integer',
            'type' => 'required|string|max:255'
        ]);

        $candidate = Candidate::find($fieldCode['candidate_id']);
        if(!isset($candidate)) {
            return $this->responseJson('Candidate not found', 404);
        }

        $riCodeValid->after(function($riCodeValid) use($fieldCode){
            if((int)($fieldCode['level_easy']) + (int)($fieldCode['level_medium']) + (int)($fieldCode['level_hard']) < 1) {
                $riCodeValid->errors()->add('common','Total number of questions must be greater than 1');
            }
        });

        if($riCodeValid->fails()) {
            return $riCodeValid->errors();
        }

        return $this->requestToRiCode($fieldCode, $candidate);
    }

    public function updateTypeOptionCandidate($candidate) {
        $rikkeiCodeType = Type::where('code', 'rikkei-code')->first();
        $test_option_type_ids_current = $candidate->test_option_type_ids;
        if(!in_array($rikkeiCodeType->id, $test_option_type_ids_current)) {
            if(empty($test_option_type_ids_current)) {
                $candidate->test_option_type_ids = [$rikkeiCodeType->id];
            } else {
                $test_option_type_ids_current[] = $rikkeiCodeType->id;
                $candidate->test_option_type_ids = $test_option_type_ids_current;
            }
            $candidate->save();
        }
    }

    /*
     * get follow candidates list
     */
    public function follow(Candidate $candidateObj)
    {
        $type = Input::get('type');
        Breadcrumb::add(Lang::get('resource::view.Candidate.List.Follow candidate list'));

        $isScopeTeam = Permission::getInstance()->isScopeCompany() || Permission::getInstance()->isScopeTeam();
        $teamPermission = new TeamPermission();
        $recruiters = $isScopeTeam ? $teamPermission->getEmployeeByActionName('sendMailToCandidate.candidate')->pluck('email')->toArray() : null;
        $positionOptions = getOptions::getInstance()->getRoles();
        $programList = Programs::all();

        return view('resource::candidate.follow', [
            'collectionModel' => $candidateObj->getFollowList($type, $isScopeTeam),
            'positionOptions' => $positionOptions,
            'programList' => $programList,
            'recruiters' => $recruiters,
            'isScopeTeam' => $isScopeTeam,
            'type' => $type,
        ]);
    }

    public function changeStatus(Request $request)
    {
        $candidate = Candidate::changeStatusToFail($request);
        return response()->json($candidate);
    }
}
