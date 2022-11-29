<?php

namespace Rikkei\Ot\Http\Controllers;

use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Lang;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\View;
use Rikkei\ManageTime\Model\ManageTimeComment;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\ManageTime\View\ManageTimeConst;
use Rikkei\ManageTime\View\View as ManageTimeView;
use Rikkei\ManageTime\View\ViewTimeKeeping;
use Rikkei\ManageTime\View\WorkingTime as WorkingTimeView;
use Rikkei\Ot\Model\OtBreakTime;
use Rikkei\Ot\Model\OtEmployee;
use Rikkei\Ot\Model\OtRegister;
use Rikkei\Ot\Model\OtTeam;
use Rikkei\Ot\View\OtEmailManagement;
use Rikkei\Ot\View\OtPermission;
use Rikkei\Ot\View\OtView;
use Rikkei\Resource\View\View as ResourceView;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\View\Permission;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Ot\Model\OtRelater;
use Rikkei\Project\Model\Project;
use Rikkei\ManageTime\Model\SupplementRegister;

class OtRegisterController extends Controller
{

    /**
     * add bread crumb
     */
    protected function _construct()
    {
    }

    /**
     * show OT register form
     * @return view
     */
    public function showRegisterForm()
    {

        Breadcrumb::add('Profile');
        Breadcrumb::add('OT');
        Menu::setActive('Profile');
        $userCurrent = Permission::getInstance()->getEmployee();
        $registrantInformation = ManageTimeCommon::getRegistrantInformation($userCurrent->id);
        $pageType = 'create';
        $empProjects = OtRegister::getProjectsbyEmployee($userCurrent->id);
        $arrEmpPro = [];
        if (count($empProjects)) {
            foreach ($empProjects as $item) {
                $arrEmpPro[$item->project_id][] = $item;
            }
        }
        $empRoles = OtRegister::getRoleandTeam($userCurrent->id);
        $registerInfo = new OtRegister();

        $tagEmployeeInfo = new OtEmployee();
        $totalRegister = OtRegister::countTotalRegister($userCurrent->id, OtRegister::REGISTER);
        $totalApproval = OtRegister::countTotalRegister($userCurrent->id, OtRegister::APPROVER);
        $listTotalRelateTo = OtRegister::countTotalRegister($userCurrent->id,  OtRegister::RELATETO);
        $isEditable = (($userCurrent->id == $registerInfo->employee_id || $userCurrent->id == $registerInfo->approver
                            || $pageType == 'create'|| OtPermission::isScopeApproveOfCompany())
                        && $registerInfo->status != OtRegister::DONE);
        $approverForNotSoftDev = OtRegister::getApproverForNotSoftDev($userCurrent->id);

        // Get working time this month of employee logged
        $objWTView = new WorkingTimeView();
        $teamCodePre = Team::getOnlyOneTeamCodePrefix($userCurrent);
        $workingTime = $objWTView->getWorkingTimeByEmployeeBetween($userCurrent->id, $teamCodePre);
        $timeSetting = $workingTime['timeSetting'];

        $keyDateInit = date('Y-m-d');
        $timeRegisterDefault = OtView::getTimeRegisterDefault($timeSetting[$userCurrent->id][$keyDateInit]);
        $registerInfo->start_at = $timeRegisterDefault['start_date'];
        $registerInfo->end_at = $timeRegisterDefault['end_date'];


        $roleIds = [Team::ROLE_TEAM_LEADER, Team::ROLE_SUB_LEADER];
        $obIsLeader = TeamMember::whereIn('role_id',$roleIds)->first();
        $isLeader = false;
        if ($obIsLeader) {
            $isLeader = true;
        }
        return view('ot::ot.register', [
            'applicant' => $userCurrent,
            'applicantRole' => $empRoles,
            'empProjects' => $empProjects,
            'approverForNotSoftDev' => $approverForNotSoftDev,
            'registerInfo' => $registerInfo,
            'tagEmployeeInfo' => $tagEmployeeInfo,
            'totalRegister' => $totalRegister,
            'totalApproval' => $totalApproval,
            'listTotalRelateTo' => $listTotalRelateTo,
            'empType' => OtRegister::REGISTER,
            'leaderList' => '',
            'pageType' => $pageType,
            'isEditable' => $isEditable,
            'commentsList' => [],
            'registrantInformation' => $registrantInformation,
            'suggestApprover' => ManageTimeCommon::suggestApprover(ManageTimeConst::TYPE_OT, $userCurrent),
            'projectAllowedOT18Key' => unserialize(CoreConfigData::getValueDb('project.ot.18h')),
            'timeSetting' => $timeSetting,
            'keyDateInit' => $keyDateInit,
            'compensationDays' => CoreConfigData::getCompensatoryDays($teamCodePre),
            'teamCodePreOfEmp' => $teamCodePre,
            'isLeader' => $isLeader,
            'arrEmpPro' => $arrEmpPro,
        ]);
    }

    /**
     * view form register for permission
     * @return [view]
     */
    public function adminRegister()
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('OT');
        Menu::setActive('Profile');

        if (!OtPermission::allowCreateEditOther()) {
            View::viewErrorPermission();
        }
        // Get working time this month of employee logged
        $objWTView = new WorkingTimeView();
        $userCurrent = Permission::getInstance()->getEmployee();
        $teamCodePre = Team::getOnlyOneTeamCodePrefix($userCurrent);
        $workingTime = $objWTView->getWorkingTimeByEmployeeBetween($userCurrent->id, $teamCodePre);
        $timeSetting = $workingTime['timeSetting'];
        $keyDateInit = date('Y-m-d');

        return view('ot::ot.admin_register', [
            'timeSetting' => $timeSetting,
            'keyDateInit' => $keyDateInit,
            'userCurrent' => $userCurrent,
            'timeRegisterDefault' => OtView::getTimeRegisterDefault($timeSetting[$userCurrent->id][$keyDateInit]),
            'projectAllowedOT18Key' => unserialize(CoreConfigData::getValueDb('project.ot.18h')),
            'compensationDays' => CoreConfigData::getCompensatoryDays($teamCodePre),
        ]);
    }

    /**
     * edit Ot register
     * @param int $id id of register
     * @return view
     */
    public function editRegister($id)
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('OT');
        Menu::setActive('Profile');
        $userCurrent = Permission::getInstance()->getEmployee();
        $leaderList = array();
        $registerInfo = OtRegister::getRegisterInfo($id);
        if (!$registerInfo) {
            return redirect()->route('ot::ot.list', ['empType' => 1])->with('messages', ['errors' => [trans('core::message.Not found item')]]);
        }
        $registrantInformation = ManageTimeCommon::getRegistrantInformation($registerInfo->employee_id);
        $pageType = 'edit';

        $isAllowView = OtPermission::isAllowView($registerInfo, $userCurrent->id);
        if (!$isAllowView) {
            View::viewErrorPermission();
        }

        $empProjects = OtRegister::getProjectsbyEmployee($registerInfo->employee_id);
        $arrEmpPro = [];
        if (count($empProjects)) {
            foreach ($empProjects as $item) {
                $arrEmpPro[$item->project_id][] = $item;
            }
        }
        $empRoles = OtRegister::getRoleandTeam($registerInfo->employee_id);
        $tagEmployeeInfo = OtEmployee::getOTEmployees($registerInfo->id);
        $totalRegister = OtRegister::countTotalRegister($userCurrent->id, OtRegister::REGISTER);
        $totalApproval = OtRegister::countTotalRegister($userCurrent->id, OtRegister::APPROVER);
        $isEditable = (($userCurrent->id == $registerInfo->employee_id || $userCurrent->id == $registerInfo->approver
                            || $pageType == 'create'|| OtPermission::isScopeApproveOfCompany())
                        && ($registerInfo->status != OtRegister::DONE && $registerInfo->status != OtRegister::REMOVE));
        $approverByProject = OtEmployee::getProjectApprovers($registerInfo->projs_id, $registerInfo->employee_id);
        $approverForNotSoftDev = OtRegister::getApproverForNotSoftDev($registerInfo->employee_id);
        $commentList = OtRegister::getRejectReasons($registerInfo->id);

        // Get working time setting of employee in
        $registerInfo->date_start = $registerInfo->start_at;
        $registerInfo->date_end = $registerInfo->end_at;
        $timeSetting = (new WorkingTimeView)->getEmpWorkingTimeSettingInRegistration($tagEmployeeInfo, $registerInfo);
        $teamCodePre = Team::getOnlyOneTeamCodePrefix($userCurrent);
        $keyDateInit = date('Y-m-d');
        $listTotalRelateTo = OtRegister::countTotalRegister($userCurrent->id, OtRegister::RELATETO);
        $roleIds = [Team::ROLE_TEAM_LEADER, Team::ROLE_SUB_LEADER];
        $obIsLeader = TeamMember::whereIn('role_id', $roleIds)->first();
        $isLeader = false;
        if ($obIsLeader) {
            $isLeader = true;
        }
        $relatedPersonsList = OtRelater::getRelatedPersons($id);
        return view('ot::ot.register', [
            'applicant' => $userCurrent,
            'applicantRole' => $empRoles,
            'empProjects' => $empProjects,
            'approverByProject' => $approverByProject,
            'approverForNotSoftDev' => $approverForNotSoftDev,
            'registerInfo' => $registerInfo,
            'tagEmployeeInfo' => $tagEmployeeInfo,
            'totalRegister' => $totalRegister,
            'totalApproval' => $totalApproval,
            'empType' => OtRegister::REGISTER,
            'leaderList' => '',
            'pageType' => $pageType,
            'isEditable' => $isEditable,
            'commentsList' => $commentList,
            'registrantInformation' => $registrantInformation,
            'breakTimeByRegister' => OtBreakTime::getBreakTimesByRegister($registerInfo->id, $registerInfo->employee_id),
            'projectAllowedOT18Key' => unserialize(CoreConfigData::getValueDb('project.ot.18h')),
            'timeSetting' => $timeSetting,
            'keyDateInit' => $keyDateInit,
            'compensationDays' => CoreConfigData::getCompensatoryDays($teamCodePre),
            'teamCodePreOfEmp' => $teamCodePre,
            'isLeader' => $isLeader,
            'listTotalRelateTo' => $listTotalRelateTo,
            'arrEmpPro' => $arrEmpPro,
            'relatedPersonsList' => $relatedPersonsList,
        ]);
    }

    /**
     * edit Ot register
     * @param int $id id of register
     * @return view
     */
    public function detailRegister($id,Request $request)
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('OT');
        Menu::setActive('Profile');
        $emp = Permission::getInstance()->getEmployee();
        $leaderList = array();
        $registerInfo = OtRegister::getRegisterInfo($id);
        if (!$registerInfo) {
            return redirect()->route('ot::ot.list', ['empType' => 1])->with('messages', ['errors' => [trans('core::message.Not found item')]]);
        }
        $pageType = 'edit';
        $isAllowView = OtPermission::isAllowView($registerInfo, $emp->id);
        if (!$isAllowView) {
            View::viewErrorPermission();
        }
        $empProjects = OtRegister::getProjectsbyEmployee($registerInfo->employee_id);
        $arrEmpPro = [];
        if (count($empProjects)) {
            foreach ($empProjects as $item) {
                $arrEmpPro[$item->project_id][] = $item;
            }
        }
        $empRoles = OtRegister::getRoleandTeam($registerInfo->employee_id);
        $tagEmployeeInfo = OtEmployee::getOTEmployees($registerInfo->id);
        $totalRegister = OtRegister::countTotalRegister($emp->id, OtRegister::REGISTER);
        $totalApproval = OtRegister::countTotalRegister($emp->id, OtRegister::APPROVER);
        $listTotalRelateTo = OtRegister::countTotalRegister($emp->id,  OtRegister::RELATETO);
        $roleIds = [Team::ROLE_TEAM_LEADER, Team::ROLE_SUB_LEADER];
        $obIsLeader = TeamMember::whereIn('role_id',$roleIds)->first();
        $isLeader = false;
        if ($obIsLeader) {
            $isLeader = true;
        }
        $isEditable = (($emp->id == $registerInfo->employee_id || $emp->id == $registerInfo->approver
                            || $pageType == 'create'|| OtPermission::isScopeApproveOfCompany())
                        && ($registerInfo->status != OtRegister::DONE && $registerInfo->status != OtRegister::REMOVE));
        $approverByProject = OtEmployee::getProjectApprovers($registerInfo->projs_id, $registerInfo->employee_id);
        $approverForNotSoftDev = OtRegister::getApproverForNotSoftDev($registerInfo->employee_id);
        $commentList = OtRegister::getRejectReasons($registerInfo->id);
        $isApprove = true;
        if ($registerInfo->status == OtRegister::REMOVE) {
            $isApprove = false;
        }

        // Get working time setting of employee in
        $registerInfo->date_start = $registerInfo->start_at;
        $registerInfo->date_end = $registerInfo->end_at;
        $timeSetting = (new WorkingTimeView)->getEmpWorkingTimeSettingInRegistration($tagEmployeeInfo, $registerInfo);
        $teamCodePre = Team::getOnlyOneTeamCodePrefix($emp);
        $keyDateInit = date('Y-m-d');
        
        $timekeepings = $this->getTimekeeping($tagEmployeeInfo, $teamCodePre);
        $relatedPersonsList = OtRelater::getRelatedPersons($id);

        return view('ot::ot.register', [
            'applicant' => $emp,
            'applicantRole' => $empRoles,
            'empProjects' => $empProjects,
            'approverByProject' => $approverByProject,
            'approverForNotSoftDev' => $approverForNotSoftDev,
            'registerInfo' => $registerInfo,
            'tagEmployeeInfo' => $tagEmployeeInfo,
            'totalRegister' => $totalRegister,
            'totalApproval' => $totalApproval,
            'empType' => OtRegister::REGISTER,
            'leaderList' => '',
            'pageType' => $pageType,
            'isEditable' => $isEditable,
            'isApprove' => $isApprove,
            'commentsList' => $commentList,
            'breakTimeByRegister' => OtBreakTime::getBreakTimesByRegister($registerInfo->id, $registerInfo->employee_id),
            'projectAllowedOT18Key' => unserialize(CoreConfigData::getValueDb('project.ot.18h')),
            'timeSetting' => $timeSetting,
            'keyDateInit' => $keyDateInit,
            'compensationDays' => CoreConfigData::getCompensatoryDays($teamCodePre),
            'teamCodePreOfEmp' => $teamCodePre,
            'listTotalRelateTo' => $listTotalRelateTo,
            'isLeader' => $isLeader,
            'arrEmpPro' => $arrEmpPro,
            'timekeepings' => $timekeepings,
            'relatedPersonsList' => $relatedPersonsList,
        ]);
    }

    /**
     * show Ot register
     * @param int $id id of register
     * @return view
     */
    public function showPopupDetailRegister(Request $request)
    {
        $registerId = $request->registerId;
        $registerInfo = OtRegister::getRegisterInfo($registerId);

        $empProjects = OtRegister::getProjectsbyEmployee($registerInfo->employee_id);
        $empRoles = OtRegister::getRoleandTeam($registerInfo->employee_id);
        $tagEmployeeInfo = OtEmployee::getOTEmployees($registerInfo->id);

        $approverByProject = OtEmployee::getProjectApprovers($registerInfo->projs_id, $registerInfo->employee_id);
        $approverForNotSoftDev = OtRegister::getApproverForNotSoftDev($registerInfo->employee_id);

        $params = [
            'applicantRole' => $empRoles,
            'empProjects' => $empProjects,
            'approverByProject' => $approverByProject,
            'approverForNotSoftDev' => $approverForNotSoftDev,
            'registerInfo' => $registerInfo,
            'tagEmployeeInfo' => $tagEmployeeInfo,
        ];

        echo view('ot::include.modals.modal_view', $params);
    }

    /**
     * get list of ot register by status
     * @param type $listType register status
     * @return view
     */
    public function getRegisterList($empType, $listType = null)
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('OT');
        Menu::setActive('Profile');
        $emp = Permission::getInstance()->getEmployee();
        $optionApprover = OtRegister::getApproverList($emp->id, $listType);

        if ($empType == OtRegister::APPROVER && !OtPermission::isScopeApproveOfSelf() && !OtPermission::isScopeApproveOfTeam() && !OtPermission::isScopeApproveOfCompany()) {
            View::viewErrorPermission();
        }

        //filter start or end date
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];
        $regList = OtRegister::getRegisterList($emp->id, $empType, $listType, $dataFilter);

        //count total number of registers for registrationa and approval
        $totalRegister = OtRegister::countTotalRegister($emp->id, OtRegister::REGISTER);
        $totalApproval = OtRegister::countTotalRegister($emp->id,  OtRegister::APPROVER);
        $listTotalRelateTo = OtRegister::countTotalRegister($emp->id,  OtRegister::RELATETO);
        $roleIds = [Team::ROLE_TEAM_LEADER, Team::ROLE_SUB_LEADER];
        $obIsLeader = TeamMember::whereIn('role_id',$roleIds)->first();
        $isLeader = false;
        if ($obIsLeader) {
            $isLeader = true;
        }
        $arrEmpPro = [];
        return view('ot::ot.register', [
            'optionApprover' => $optionApprover,
            'collectionModel' => $regList,
            'totalRegister' => $totalRegister,
            'totalApproval' => $totalApproval,
            'empType' => $empType,
            'pageType' => $listType,
            'isEditable' => '',
            'isLeader' => $isLeader,
            'listTotalRelateTo' => $listTotalRelateTo,
            'arrEmpPro' => $arrEmpPro,
        ]);
    }

    /**
     * get list of ot register for admin
     * @param Request $request
     * @return view
     */
    public function getManageList($id = null)
    {
        Breadcrumb::add('HR');
        Breadcrumb::add('Manage time');
        Breadcrumb::add('OT');
        Menu::setActive('HR');
        $userCurrent = Permission::getInstance()->getEmployee();
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];

        $isScopeManageOfTeam = OtPermission::isScopeManageOfTeam();
        $isScopeManageOfCompany = OtPermission::isScopeManageOfCompany();

        $teamIdsAvailable = null;
        $teamTreeAvailable = [];
        if ($isScopeManageOfCompany) {
            $teamIdsAvailable = true;
        } elseif ($isScopeManageOfTeam) {
            $teamIdsAvailable = Permission::getInstance()->isScopeTeam(null, 'manage_time::manage-time.manage.view');
            if (! $teamIdsAvailable) {
                View::viewErrorPermission();
            }

            foreach ($teamIdsAvailable as $key => $teamId) {
                if (! OtPermission::isScopeManageOfTeam($teamId)) {
                    unset($teamIdsAvailable[$key]);
                }
            }
            if (! $teamIdsAvailable) {
                View::viewErrorPermission();
            }
            if (! $id) {
                $id = reset($teamIdsAvailable);
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
                        $teamTreeAvailable = array_merge($teamTreeAvailable, $teamPathTree[$teamId]);
                    }
                    $teamTreeAvailable = array_merge($teamTreeAvailable, [$teamId]);
                }
                $teamIdsAvailable = array_merge($teamIdsAvailable, $teamIdsChildAvailable);
            }
            if (! in_array($id, $teamIdsAvailable)) {
                View::viewErrorPermission();
            }
            if (is_array($teamIdsAvailable) && count($teamIdsAvailable) == 1) {
                $teamIdsAvailable = Team::find($id);
            }
        } else {
            View::viewErrorPermission();
        }

        $collectionModel = OtRegister::getListManageRegisters($userCurrent->id, $id, $dataFilter);
        $optionStatus = OtRegister::getStatusList(OtRegister::REGISTER);

        return view('ot::ot.manage', [
            'collectionModel' => $collectionModel,
            'optionStatus' => $optionStatus,
            'pageType' => 'company_list',
            'teamIdCurrent'     => $id,
            'teamIdsAvailable'  => $teamIdsAvailable,
            'teamTreeAvailable' => $teamTreeAvailable,
        ]);
    }

    /**
     * save overtime register
     * @param Request $request
     * @return json
     */
    public function saveOt(Request $request)
    {
        $data = $request->all();

        //Validate project require
        if (!empty($data['project_list']) && $data['project_list'] == OtRegister::KEY_NOTPROJECT_OT) {
            $data['project_list'] = "";
        }

        //get validate rules and message
        $rules = self::getRules();
        $messages = self::getMessages();

        //set ot empployees info
        $otemps = json_decode($data['table_data_emps']);
        if (!$otemps) {
            $rules['data_employee'] = 'required';
            $messages['data_employee.required'] = Lang::get('ot::message.The register OT list is required');
        }
        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $checkLockUp = SupplementRegister::checkCloseAllTimekeeping($otemps);
        if ($checkLockUp) {
            return redirect()->back()->withErrors('Không thể tạo, sửa, duyệt đơn sau khi bảng công đã bị khóa đối với nhân viên: '.$checkLockUp);
        }
        //find if register form exist
        $regId = $data['form_id'];
        DB::beginTransaction();
        try {
            if ($regId) {
                $register = OtRegister::find($regId);
                if (!$register) {
                    return redirect()->back()->with('messages', ['errors' => [trans('core::message.Not found item')]])->withInput();
                }
            } else {
                $register = new OtRegister();
            }

            $startTime = Carbon::createFromFormat('d-m-Y H:i', $data['time_start'])->format('Y-m-d H:i:s');
            $endTime = Carbon::createFromFormat('d-m-Y H:i', $data['time_end'])->format('Y-m-d H:i:s');

            //set register info
            $register->employee_id = $data['emp_id'];
            if ($data['project_list']) {
                $register->projs_id = $data['project_list'];
            } else {
                $register->projs_id = null;
            }
            $register->approver = $data['leader_input'];
            $register->start_at = $startTime;
            $register->end_at = $endTime;
            if ($data['total_time_break']) {
                $register->time_break = $data['total_time_break'];
            } else {
                $register->time_break = 0;
            }
            $register->reason = $data['reason'];
            $register->status = OtRegister::WAIT;
            $register->is_onsite = isset($data['is_onsite']) ? $data['is_onsite'] : OtRegister::IS_NOT_ONSITE;


            $errorsExist = [];
            // $errorsExist[] = Lang::get('ot::message.The following employees have registered the same registration');
            $errorsExist[] = Lang::get('ot::message.The following employees have registered the same registration');

            $hasErrorExist = false;

            $dataBreakTime = [];
            foreach ($otemps as $item) {
                $employeeIds[] = $item->empId;
                $registerExist = OtEmployee::getRegisterExist($item->empId, Carbon::createFromFormat('d-m-Y H:i', $item->startAt)->format('Y-m-d H:i:s'), Carbon::createFromFormat('d-m-Y H:i', $item->endAt)->format('Y-m-d H:i:s'), $regId);
                
                //check $otemps co duoc OT huong luong

                $getDisableOtExist = OtEmployee::getDisableOtExist($item->empId);
                if($item->isPaid){
                    if($getDisableOtExist){
                        $hasErrorExist = true;
                        $errorsExist[] = Lang::get('ot::message.Employee name: :employee_name, employee code: :employee_code', ['employee_name' => $item->empName, 'employee_code' => $item->empCode]);
                 }
                }

                // check if $otemps exist
                if ($registerExist) {
                    
                    $hasErrorExist = true;
                    $errorsExist[] = Lang::get('ot::message.Employee name: :employee_name, employee code: :employee_code', ['employee_name' => $registerExist->employee_name, 'employee_code' => $registerExist->employee_code]);
                }

                //register time now one day
                //check time
                if (strtotime($item->startAt) >= strtotime($item->endAt)) {
                    return redirect()->back()->with('messages', ['errors' => [Lang::get('ot::message.Time start less than time end.')]])->withInput();
                }
                if (!empty((float)$item->break)) {
                    $dataBreakTime[] = [
                        'ot_date' => Carbon::parse($item->startAt)->format('Y-m-d'),
                        'ot_register_id' => '',
                        'employee_id' => $item->empId,
                        'break_time' => $item->break,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ];
                }
            }
            if ($hasErrorExist) {
                return redirect()->back()->with('messages', ['errors' => $errorsExist])->withInput();
            }

            //save register form
            if ($register->save()) {
                $registerId = $register->id;
                if ($regId) {
                    OtTeam::where('register_id', $registerId)->delete();
                }
                $registerTeam = [];
                $teamsOfRegistrant = ManageTimeCommon::getTeamsOfEmployee($data['emp_id']);
                foreach ($teamsOfRegistrant as $team) {
                    $registerTeam[] = array('register_id' => $registerId, 'team_id'=> $team->id, 'role_id' => $team->role_id);
                }
                OtTeam::insert($registerTeam);
            }
            //save ot employees
            $otEmpsTbl = OtEmployee::getTableName();
            if ($regId) {
                DB::table($otEmpsTbl)->where('ot_register_id', $regId)->delete();
                OtBreakTime::where('ot_register_id', $regId)->delete();
            }
            OtEmployeeController::saveEmployees($register->id, $otemps);
            if (count($dataBreakTime)) {
                foreach ($dataBreakTime as $key => $value) {
                    $dataBreakTime[$key]['ot_register_id'] = $register->id;
                }
                OtBreakTime::insert($dataBreakTime);
            }
            $userCurrent = Permission::getInstance()->getEmployee();
            if ($userCurrent->id != $register->approver) {
                $template = 'ot::template.mail_register.mail_register_to_approver';
                OtEmailManagement::setEmailRegister($register, $template);
            }
            //save ot_related
            $relatedPersonsId = $request->related_persons_list;
            if (!empty($relatedPersonsId)) {
                $isCreate = empty($data['form_id']) ? true : false;
                $this->otRegisterSaveToRelated($isCreate, $register, $relatedPersonsId);
            }
            DB::commit();

            $messages = [
                'success' => [
                    Lang::get('core::message.Save success'),
                ]
            ];
            if ($regId) {
                return redirect()->back()->with('messages', $messages);
            }
            return redirect()->route('ot::ot.editot', ['id' => $register->id])->with('messages', $messages);
        } catch (Exception $ex) {
            Log::info($ex);
            DB::rollback();

            return redirect()->back()->with('messages', ['errors' => [trans('Error system, please try later!')]]);
        }
    }

    public function otRegisterSaveToRelated($isCreate, $register, $relatedPersonsId)
    {
        $relatePersons = \Rikkei\Team\Model\Employee::getEmpByIds($relatedPersonsId);
        if ($isCreate) {
            $curEmp = Permission::getInstance()->getEmployee();
            $timeStart = Carbon::parse($register->start_at)->format('d/m/Y H:s');
            $timeEnd = Carbon::parse($register->end_at)->format('d/m/Y H:s');
            $projsName = '';
            if ($register->projs_id) {
                $projs = Project::find($register->projs_id);
                if ($projs) {
                    $projsName = $projs->name;
                }
            }
            $otEmps = OtEmployee::getOTEmployees($register->id);
            $teamName = Team::getTeamNameOfEmployee($curEmp->id);

            $dataMail['start_time'] = Carbon::parse($register->start_at)->format('H:i');
            $dataMail['start_date'] = Carbon::parse($register->start_at)->format('d/m/Y');
            $dataMail['end_time'] = Carbon::parse($register->end_at)->format('H:i');
            $dataMail['end_date'] = Carbon::parse($register->end_at)->format('d/m/Y');
            $dataMail['registrant_name'] = $curEmp->name;
            $dataMail['team_name'] = $teamName->role_name;
            $dataMail['link'] = route('ot::ot.editot', ['id' => $register->id]);
            $dataMail['reason'] = $register->reason;
            $dataMail['projs_name'] = $projsName;
            $dataMail['is_onsite'] = $register->projs_id == 1 ? 'Có' : 'Không';
            $dataMail['otEmps'] = $otEmps;
            $notificationData = [
                'category_id' => RkNotify::CATEGORY_TIMEKEEPING
            ];
            
            foreach ($relatePersons as $person) {
                $registerRelaters [] = array('ot_register_id' => $register->id, 'relater_id' => $person->id);

                //Send mail to relaters
                $dataMail['mail_to'] = $person->email;
                $dataMail['mail_title'] = '[Đăng ký làm thêm] '.$curEmp->name.' đăng ký làm thêm từ '.$dataMail['start_time'].' ngày '.$timeStart.' đến '.$dataMail['end_time'].' ngày '.$timeEnd;
                $dataMail['to_id'] = $person->id;
                $dataMail['noti_content'] = $dataMail['mail_title'];
                $dataMail['related_person_name'] = $person->name;
                $template = 'ot::ot.mails.mail_ot_register_to_related_person';
                ManageTimeCommon::pushEmailToQueue($dataMail, $template, true, $notificationData);
            }
        } else {
            OtRelater::where('ot_register_id', $register->id)->delete();
            foreach ($relatePersons as $person) {
                $registerRelaters [] = array('ot_register_id' => $register->id, 'relater_id' => $person->id);
            }
        }
        OtRelater::insert($registerRelaters);
    }

    public function saveAdminRegister(Request $request)
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        if (!OtPermission::allowCreateEditOther()) {
            View::viewErrorPermission();
        }
        $data = $request->all();
        //Validate project required
        if (!empty($data['project_list']) && $data['project_list'] == OtRegister::KEY_NOTPROJECT_OT) {
            $data['project_list'] = "";
        }
        $rules = [
            'employee_id' => 'required',
            'time_start' => 'required',
            'time_end' => 'required',
            'reason' => 'required',
        ];
        $messages = [
            'employee_id.required' => Lang::get('ot::message.The registrant field is required'),
            'time_start.required' => Lang::get('ot::message.The time start field is required'),
            'time_end.required' => Lang::get('ot::message.The time end field is required'),
            'reason.required' => Lang::get('ot::message.The reason field is required'),
        ];
        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        DB::beginTransaction();
        try {
            $startTime = Carbon::createFromFormat('d-m-Y H:i', $data['time_start'])->format('Y-m-d H:i:s');
            $endTime = Carbon::createFromFormat('d-m-Y H:i', $data['time_end'])->format('Y-m-d H:i:s');
            $isFree = OtEmployee::checkRegisterExist($data['employee_id'], $startTime, $endTime);
            if ($isFree) {
                return redirect()->back()->with('messages', ['errors' => [trans('ot::message.Contains occupied time range')]]);
            }

            // Validate allow only register in a day
            if (Carbon::createFromFormat('d-m-Y H:i', $data['time_start'])->format('Y-m-d') !=
                Carbon::createFromFormat('d-m-Y H:i', $data['time_end'])->format('Y-m-d')) {
                return redirect()->back()->with('messages', ['errors' => [trans('ot::message.Register time OT same day')]]);
            }

            $register = new OtRegister();

            $register->employee_id = $data['employee_id'];
            $register->projs_id = null;
            $register->approver = $userCurrent->id;
            $register->start_at = $startTime;
            $register->end_at = $endTime;
            if ($data['total_time_break']) {
                $register->time_break = $data['total_time_break'];
            } else {
                $register->time_break = 0;
            }
            $register->reason = $data['reason'];
            $register->status = OtRegister::DONE;
            $register->approved_at = Carbon::now();

            // Save register
            if ($register->save()) {
                $registerId = $register->id;
                $registerTeam = [];
                $teamsOfRegistrant = ManageTimeCommon::getTeamsOfEmployee($data['employee_id']);
                foreach ($teamsOfRegistrant as $team) {
                    $registerTeam[] = array('register_id' => $registerId, 'team_id'=> $team->id, 'role_id' => $team->role_id);
                }
                OtTeam::insert($registerTeam);

                $isPaid = 0;
                if (!empty($data['is_paid'])) {
                    $isPaid = 1;
                }
                // Save ot employees
                $otEmp = new OtEmployee();
                $otEmp->ot_register_id = $registerId;
                $otEmp->employee_id = $register->employee_id;
                $otEmp->start_at = $register->start_at;
                $otEmp->end_at = $register->end_at;
                $otEmp->is_paid = $isPaid;
                $otEmp->time_break = $register->time_break;
                if ($otEmp->save()) {
                    OtBreakTime::where('ot_register_id', $registerId)->delete();
                    $timeBreaks = json_decode($data['time_breaks']);
                    if (count($timeBreaks)) {
                        foreach ($timeBreaks as $item) {
                            $otBreakTime = new OtBreakTime();
                            $otBreakTime->ot_register_id = $register->id;
                            $otBreakTime->employee_id = $register->employee_id;
                            $otBreakTime->ot_date = Carbon::createFromFormat('d/m/Y', $item->date)->format('Y-m-d');
                            $otBreakTime->break_time = $item->time_break;
                            $otBreakTime->save();
                        }
                    }
                }
                $this->insertTimekeeping(Employee::find($register->employee_id), $registerId);
            }

            DB::commit();
            $messages = [
                'success' => [
                    Lang::get('core::message.Save success'),
                ]
            ];

            return redirect()->route('ot::ot.editot', ['id' => $register->id])->with('messages', $messages);
        } catch (Exception $ex) {
            Log::info($ex);
            DB::rollback();

            return redirect()->back()->with('messages', ['errors' => [trans('Error system, please try later!')]]);
        }
    }

    /**
     * delete selected register
     * @param Request $request
     * @return type route
     */
    public function delete(Request $request)
    {
        $deleteId = $request->get('ot_id_delete');
        $reg = OtRegister::withTrashed()->find($deleteId);
        $pageType = $request->get('page_type');
        if (!$reg) {
            if ($pageType == 'edit') {
                return redirect()->route('ot::ot.editot', ['id' => $reg->id])
                                ->with('messages', ['errors' => [trans('core::message.Not found item')]]);
            } else {
                return redirect()->route('ot::ot.list', ['empType' => OtRegister::REGISTER, 'listType' => $pageType])
                                ->with('messages', ['errors' => [trans('core::message.Not found item')]]);
            }
        }
        try {
            $reg->update(['status' => OtRegister::REMOVE]);
            if (!$reg->trashed()) {
                $reg->ot_employees()->delete();
                $reg->delete();
            }

            $messages = [
                'success' => [
                    Lang::get('ot::message.Delete success'),
                ]
            ];

            return redirect()->back()->with('messages', $messages);
        } catch (Exception $ex) {
            Log::info($ex);
            DB::rollback();

            return redirect()->back()->with('messages', ['errors' => [trans('core::message.Error system, please try later!')]]);
        }
    }

    /**
     * approve selected register
     * @param Request $request
     * @return type route
     */
    public function approve(Request $request)
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        $approveId = $request->get('ot_approve_id');
        $pageType = $request->get('page_type');
        if ($pageType == 'edit') {
            $pageType = OtRegister::DONE;
        }
        try {
            $reg = OtRegister::find($approveId);

            if (!$reg) {
                if ($pageType != "company_list") {
                    return redirect()->route('ot::ot.list', ['empType' => OtRegister::APPROVER, 'listType' => $pageType])
                                    ->with('messages', ['errors' => [trans('core::message.Not found item')]]);
                } else {
                    return redirect()->route('ot::profile.manage.ot')->with('messages', ['errors' => [trans('core::message.Not found item')]]);
                }
            }
            $otEmps = OtEmployee::where("ot_register_id", $approveId)->get();
            $checkLockUp = SupplementRegister::checkCloseAllTimekeeping($otEmps);
            if ($checkLockUp) {
                $messages = 'Không thể tạo, sửa, duyệt đơn sau khi bảng công đã bị khóa đối với nhân viên: '.$checkLockUp;
                return redirect()->back()->with('messages', ['errors' => [$messages]]);
            }
            $reg->update(['approver' => $userCurrent->id, 'status' => OtRegister::DONE]);
            $reg->approved_at = Carbon::now()->toDateTimeString();
            $reg->save();

            $template = 'ot::template.mail_approve.mail_approve_to_register';
            OtEmailManagement::setEmailApproverAction($reg, $template, OtRegister::DONE);

            $messages = [
                'success' => [
                    Lang::get('ot::message.Approve success'),
                ]
            ];
            $this->insertTimekeeping($userCurrent, $approveId);
            return redirect()->back()->with('messages', $messages);
        } catch (Exception $ex) {
            Log::info($ex);
            DB::rollback();

            return redirect()->back()->with('messages', ['errors' => [trans('core::message.Error system, please try later!')]]);
        }
    }

    /**
     * mass approve ot registrations
     * @param Request $request
     * @return type route
     */
    public function massApprove(Request $request)
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        $approveIds = explode(",", $request->get('ot_approve_id'));
        $pageType = $request->get('page_type');
        try {
            $regs = OtRegister::whereIn('id', $approveIds);
            if (count($regs->get()) == 0) {
                if ($pageType != "company_list") {
                    return redirect()->route('ot::ot.list', ['empType' => OtRegister::APPROVER, 'listType' => $pageType])
                                    ->with('messages', ['errors' => [trans('core::message.Not found item')]]);
                } else {
                    return redirect()->route('ot::profile.manage.ot')->with('messages', ['errors' => [trans('core::message.Not found item')]]);
                }

            }
            OtRegister::whereIn('id', $approveIds)->update(['approver' => $userCurrent->id, 'status' => OtRegister::DONE, 'approved_at' => Carbon::now()]);

            $template = 'ot::template.mail_approve.mail_approve_to_register';
            $idRegs = [];
            foreach ($regs->get() as $reg) {
                OtEmailManagement::setEmailApproverAction($reg, $template, OtRegister::DONE);
                $idRegs[] = $reg->id;
            }
            $this->insertTimekeeping($userCurrent, $idRegs);
            $messages = [
                'success' => [
                    Lang::get('ot::message.Approve success'),
                ]
            ];
            return redirect()->back()->with('messages', $messages);
        } catch (Exception $ex) {
            Log::info($ex);
            DB::rollback();

            return redirect()->back()->with('messages', ['errors' => [trans('core::message.Error system, please try later!')]]);
        }
    }

    /**
     * reject selected register
     * @param Request $request
     * @return type route
     */
    public function reject(Request $request)
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        $rejectId = $request->get('ot_reject_id');
        $pageType = $request->get('page_type');
        if ($pageType == 'edit') {
            $pageType = OtRegister::REJECT;
        }
        $reason = $request->get('reject_reason');

        $validator = Validator::make(
                        ['reject_reason' => $reason],
                        ['reject_reason' => 'required'],
                        ['reject_reason.required' => Lang::get('ot::message.The field is required', ['field' => 'Lý do từ chối'])]
                    );

        if ($validator->fails()) {
            if ($pageType != "company_list") {
                return redirect()->route('ot::ot.list', ['empType' => OtRegister::REJECT, 'listType' => $pageType])
                                 ->withErrors($validator)
                                 ->withInput();
            } else {
                return redirect()->route('ot::profile.manage.ot')
                                 ->withErrors($validator)
                                 ->withInput();
            }
        }

        try {
            $reg = OtRegister::find($rejectId);
            // remove in timekeeping
            $obViewTk = new ViewTimeKeeping();
            $obViewTk->removeRegisterOT([$rejectId]);
            if (!$reg) {
                if ($pageType != "company_list") {
                    return redirect()->route('ot::ot.list', ['empType' => OtRegister::REJECT, 'listType' => $pageType])
                                    ->with('messages', ['errors' => [trans('core::message.Not found item')]]);
                } else {
                    return redirect()->route('ot::profile.manage.ot')->with('messages', ['errors' => [trans('core::message.Not found item')]]);
                }
            }
            $reg->update(['approver' => $userCurrent->id, 'status' => OtRegister::REJECT, 'approved_at' => Carbon::now()]);
            $reg->approved_at = Carbon::now()->toDateTimeString();
            $reg->save();
            $registerComment = new ManageTimeComment;
            $registerComment->register_id = $rejectId;
            $registerComment->comment = $reason;
            $registerComment->type = ManageTimeConst::TYPE_OT;
            $registerComment->created_by = $userCurrent->id;
            $registerComment->save();

            $template = 'ot::template.mail_disapprove.mail_disapprove_to_register';
            OtEmailManagement::setEmailApproverAction($reg, $template, OtRegister::REJECT);

            $messages = [
                'success' => [
                    Lang::get('ot::message.Reject success'),
                ]
            ];
            $this->insertTimekeeping(Employee::find($userCurrent), $rejectId);
            return redirect()->back()->with('messages', $messages);
        } catch (Exception $ex) {
            Log::info($ex);
            DB::rollback();

            return redirect()->back()->with('messages', ['errors' => [trans('core::message.Error system, please try later!')]]);
        }
    }

    /**
     * mass reject ot registration
     * @param Request $request
     * @return type route
     */
    public function massReject(Request $request)
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        $rejectIds = explode(",", $request->get('ot_reject_id'));
        $pageType = $request->get('page_type');
        $reason = $request->get('reject_reason');

        $validator = Validator::make(
                        ['reject_reason' => $reason],
                        ['reject_reason' => 'required'],
                        ['reject_reason.required' => Lang::get('ot::message.The field is required', ['field' => 'Lý do từ chối'])]
                    );

        if ($validator->fails()) {
            if ($pageType != "company_list") {
                return redirect()->route('ot::ot.list', ['empType' => OtRegister::REJECT, 'listType' => $pageType])
                                 ->withErrors($validator)
                                 ->withInput();
            } else {
                return redirect()->route('ot::profile.manage.ot')
                                 ->withErrors($validator)
                                 ->withInput();
            }
        }

        try {
            $regs = OtRegister::whereIn('id', $rejectIds);
            if (count($regs->get()) == 0) {
                if ($pageType != "company_list") {
                    return redirect()->route('ot::ot.list', ['empType' => OtRegister::REJECT, 'listType' => $pageType])
                                    ->with('messages', ['errors' => [trans('core::message.Not found item')]]);
                } else {
                    return redirect()->route('ot::profile.manage.ot')->with('messages', ['errors' => [trans('core::message.Not found item')]]);
                }
            }

            OtRegister::whereIn('id', $rejectIds)->update(['approver' => $userCurrent->id, 'status' => OtRegister::REJECT, 'approved_at' => Carbon::now()]);
            // remove in timekeeping
            $obViewTk = new ViewTimeKeeping();
            $obViewTk->removeRegisterOT($rejectIds);
            $idRegs = [];
            foreach ($regs->get() as $reg) {
                $registerComment = new ManageTimeComment;
                $registerComment->register_id = $reg->id;
                $registerComment->comment = $reason;
                $registerComment->type = ManageTimeConst::TYPE_OT;
                $registerComment->created_by = Permission::getInstance()->getEmployee()->id;
                $registerComment->save();
                $idRegs[] = $reg->id;
            }
            $this->insertTimekeeping($userCurrent, $idRegs);
            $template = 'ot::template.mail_disapprove.mail_disapprove_to_register';
            foreach ($regs->get() as $reg) {
                OtEmailManagement::setEmailApproverAction($reg, $template, OtRegister::REJECT);
            }

            $messages = [
                'success' => [
                    Lang::get('ot::message.Reject success'),
                ]
            ];
            return redirect()->back()->with('messages', $messages);
        } catch (Exception $ex) {
            Log::info($ex);
            DB::rollback();

            return redirect()->back()->with('messages', ['errors' => [trans('core::message.Error system, please try later!')]]);
        }
    }

    /**
     * get validation rules
     * @return array rules
     */
    public static function getRules()
    {
        return [
            'leader_input' => 'required',
            'time_end' => 'required|date_format:d-m-Y H:i',
            'time_start' => 'required|date_format:d-m-Y H:i',
            // 'project_list' => 'required',
            'relax' => 'sometimes|numeric|between:0,24',
            'reason' => 'required',
        ];
    }

    /**
     * get validation messages
     * @return array messages
     */
    public static function getMessages()
    {
        return [
            'leader_input.required' => Lang::get('ot::message.The field is required', ['field' => 'Người duyệt']),
            'time_start.required' => Lang::get('ot::message.The field is required', ['field' => 'Làm thêm từ']),
            'time_start.date_format' => Lang::get('ot::message.Invalid Date format'),
            'time_end.required' => Lang::get('ot::message.The field is required', ['field' => 'Làm thêm đến']),
            'time_end.date_format' => Lang::get('ot::message.Invalid Date format'),
            // 'project_list.required' => Lang::get('ot::message.The field is required', ['field' => 'Dự án cần OT']),
            'relax.numeric' => Lang::get('ot::message.Number'),
            'relax.between' => Lang::get('ot::message.Break time invalid'),
            'reason.required' => Lang::get('ot::message.The field is required', ['field' => 'Lý do']),
        ];
    }

    /**
     * get register info ajax
     * @param Request $request
     * @return json employee's info
     */
    public function getRegisterForSearch(Request $request)
    {
        $emp = Permission::getInstance()->getEmployee();
        $data = array();
        $searchId = $request->id;

        $registerInfo = OtRegister::getRegisterInfo($searchId);
        $empRoles = OtRegister::getRoleandTeam($emp->id);
        $roleStr = '';
        foreach ($empRoles as $role) {
            $roleStr .= $role->name . ': ' . $role->role . '  ';
        }
        $data['applicant'] = $emp;
        $data['register'] = $registerInfo;

        $data['otemployees'] = OtEmployee::getOTEmployees($searchId);
        $data['role'] = $roleStr;
        $data['register']->status = OtRegister::getStatusLabel(OtRegister::REGISTER, $registerInfo->status);
        return $data;
    }

    /**
     * Ajax get when change registrant
     * @param  Request $request
     * @return [json]
     */
    public function ajaxChangeRegistrant(Request $request)
    {
        $employeeId = $request->employeeId;
        $leaderIds = view('ot::include.option_approver')->with([
            'approvers' => OtRegister::getApproverForNotSoftDev($employeeId),
        ])->render();
        $projects = view('ot::include.option_project')->with([
            'empProjects' => OtRegister::getProjectsbyEmployee($employeeId),
        ])->render();

        return response()->json(['leaderIds' => $leaderIds, 'projects' => $projects]);
    }

    /**
     * Ajax get when change project
     * @param  Request $request
     * @return [json]
     */
    public function ajaxChangeProject(Request $request)
    {
        $projectId = $request->projectId;
        $employeeId = $request->employeeId;
        if ($projectId) {
            $approvers = OtEmployee::getProjectApprovers($projectId, $employeeId);
        } else {
            $approvers = OtRegister::getApproverForNotSoftDev($employeeId);
        }
        $leaderIds = view('ot::include.option_approver')->with([
            'approvers' => $approvers,
        ])->render();

        return response()->json(['leaderIds' => $leaderIds]);
    }

    /**
     * get projects list and working time setting of employee
     * @param Request $request
     * @return string json
     */
    public function getProjectOt(Request $request)
    {
        $empId = $request->empId;
        $otProject =  OtRegister::getProjectsbyEmployee($empId);

        $objWTView = new WorkingTimeView();
        $teamCode = Team::getOnlyOneTeamCodePrefix($empId);
        $period = [
            'start_date' => Carbon::parse($request->get('start_at'))->toDateString(),
            'end_date' => Carbon::parse($request->get('end_at'))->toDateString(),
        ];
        $workingTime = $objWTView->getWorkingTimeByEmployeeBetween($empId, $teamCode, $period);

        return response()->json([
            'projects' => $otProject,
            'timeSetting' => $workingTime['timeSetting'],
        ]);
    }
    
    /**
     * getTimekeeping
     *
     * @param  collection $employeeInfo
     * @param  string $teamCodePre
     * @return array
     */
    public function getTimekeeping($employeeInfo, $teamCodePre)
    {
        $empIds = $employeeInfo->lists('employee_id')->toArray();
        $startDate = '';
        $endDate = '';
        foreach($employeeInfo as $item) {
            $start = substr($item->start_at, 0, 10);
            $end = substr($item->end_at, 0, 10);
            if (!$startDate) {
                $startDate = $start;
                $endDate = substr($item->end_at, 0, 10);
            } else {
                if ($startDate > $start)
                    $startDate = $start;
                if ($endDate < $end)
                    $endDate = $end;
            }
        }
        $objViewTimeKeeping = new ViewTimeKeeping();
        $dataTk =  $objViewTimeKeeping->getTimekeeping($startDate, $endDate, $empIds, $teamCodePre, $isEmpIds = true);
        $timeKeepings = [];
        foreach($employeeInfo as $item) {
            if (isset($dataTk[$item->employee_id]) && isset($dataTk[$item->employee_id][substr($item->start_at, 0, 10)])) {
                $timeKeepings[] = $dataTk[$item->employee_id][substr($item->start_at, 0, 10)];
            }
        }
        return $timeKeepings;
    }

    /** insertTimekeeping
     *
     * @param  collection $userCurrent
     * @param  int|array $idReg
     * @return void
     */
    public function insertTimekeeping($userCurrent, $idReg)
    {
        if (is_array($idReg)) {
            $dataOT = [];
            $collOT = OtRegister::find($idReg);
            foreach($collOT as $item) {
                $emOTs = $item->employeeOTs()->get();
                if (count($emOTs)) {
                    foreach($emOTs as $empOT) {
                        $dataOT[] = $empOT;
                    }
                }
            }
        } else {
            $dataOT = OtRegister::find($idReg)->employeeOTs()->get();
        }
        $objView = new ManageTimeView();
        return $objView->insertTimekeeping($userCurrent, $dataOT);
    }
}
