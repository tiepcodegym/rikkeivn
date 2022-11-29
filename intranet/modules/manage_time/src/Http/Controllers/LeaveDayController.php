<?php

namespace Rikkei\ManageTime\Http\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Lang;
use Log;
use Response;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\CacheHelper as Cache;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\View;
use Rikkei\ManageTime\Model\LeaveDay;
use Rikkei\ManageTime\Model\LeaveDayGroupEmail;
use Rikkei\ManageTime\Model\LeaveDayHistories;
use Rikkei\ManageTime\Model\LeaveDayReason;
use Rikkei\ManageTime\Model\LeaveDayRegister;
use Rikkei\ManageTime\Model\LeaveDayRelater;
use Rikkei\ManageTime\Model\LeaveDayTeam;
use Rikkei\ManageTime\Model\ManageTimeAttachment;
use Rikkei\ManageTime\Model\ManageTimeComment;
use Rikkei\ManageTime\View\LeaveDayPermission;
use Rikkei\ManageTime\View\ManageLeaveDay;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\ManageTime\View\ManageTimeConst;
use Rikkei\ManageTime\View\View as ManageTimeView;
use Rikkei\ManageTime\View\WorkingTime as WorkingTimeView;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Resource\View\View as ResourceView;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\EmployeeRelationship;
use Rikkei\Team\Model\LeaveDayRelationMember;
use Rikkei\Team\Model\RelationNames;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Permission;
use Rikkei\ManageTime\Model\SupplementRegister;
use Rikkei\Team\View\TeamList;
use DateTime, DateInterval, DatePeriod;
use Rikkei\ManageTime\Model\LeaveDayBaseline;
use Maatwebsite\Excel\Facades\Excel;

class LeaveDayController extends Controller
{
    /**
     * [register: view form register]
     * @return [view]
     */
    public function register()
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('Leave day');
        Menu::setActive('Profile');

       $params = self::sharedRegister();
        return view('manage_time::leave.leave_register', $params);
    }

    public function sharedRegister()
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        $teams = Team::getTeamOfEmployee($userCurrent->id);
        $registrantInformation = ManageTimeCommon::getRegistrantInformation($userCurrent->id);
        $teamCodePre = Team::getOnlyOneTeamCodePrefixChange($userCurrent, $teams);
        $listLeaveDayReasons = LeaveDayReason::getLeaveDayReasons($teamCodePre);

        // Get working time this month of employee logged
        $objWTView = new WorkingTimeView();
        $workingTime = $objWTView->getWorkingTimeByEmployeeBetween($userCurrent->id, $teamCodePre);
        $timeSetting = $workingTime['timeSetting'];
        $timeWorkingQuater = $workingTime['timeWorkingQuater'];
        $groupEmail = CoreConfigData::getGroupEmailRegisterLeave();
        $registerBranch = CoreConfigData::checkBranchRegister(false, $teamCodePre);
        $annualHolidays = CoreConfigData::getAnnualHolidays(2);
        $specialHolidays = CoreConfigData::getSpecialHolidays(2, $teamCodePre);
        $leaveDay = LeaveDay::getLeaveDayById($userCurrent->id);
        $getRelation = EmployeeRelationship::getListRelationEmpNotDie($userCurrent->id);
        $creator = Employee::getEmpById($userCurrent->id);
        // if employee type japan
        if ($teamCodePre == Team::CODE_PREFIX_JP) {
            $grantDate = self::setGrantDateEmployeeJp($creator);
        } else {
            $grantDate = [
                'last_grant_date' => '',
                'next_grant_date' => ''
            ];
        }
        $params = [
            'registrantInformation' => $registrantInformation,
            'listLeaveDayReasons' => $listLeaveDayReasons,
            'specialHolidays' => $specialHolidays,
            'annualHolidays' => $annualHolidays,
            'suggestApprover' => ManageTimeCommon::suggestApprover(ManageTimeConst::TYPE_LEAVE_DAY, $userCurrent),
            'curEmp' => $userCurrent,
            'regsUnapporve' => ManageTimeView::dayOffUnapprove($userCurrent->id),
            'timeSetting' => $timeSetting,
            'keyDateInit' => date('Y-m-d'),
            'compensationDays' => CoreConfigData::getCompensatoryDays($teamCodePre),
            'teamCodePreOfEmp' => $teamCodePre,
            'groupEmail' => $groupEmail,
            'registerBranch' => $registerBranch,
            'weekends' => ManageTimeCommon::getAllWeekend(),
            'timeWorkingQuater' => $timeWorkingQuater,
            'leaveDay' => $leaveDay,
            'getRelation' => $getRelation,
            'grantDate' => $grantDate,
        ];
        return $params;
    }

    public function getLeaveRegister(Request $request)
    {
        $params = self::sharedRegister();
        $params['dateStart'] = date('d-m-Y 8:00', strtotime($request->date));
        $params['dateEnd'] = date('d-m-Y 17:30', strtotime($request->date));
        $getLeaveRegister = view('manage_time::timekeeping.personal.modal_leave_register', $params)->render();
        return \Response::json([
            'renderHtml' => $getLeaveRegister,
        ]);
    }

    /**
     * view form register for permission
     * @return [view]
     */
    public function adminRegister()
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('Leave day');
        Menu::setActive('Profile');

        if (!LeaveDayPermission::allowCreateEditOther()) {
            View::viewErrorPermission();
        }

        $userCurrent = Permission::getInstance()->getEmployee();
        // Get working time this month of employee logged
        $teamCodePre = Team::getOnlyOneTeamCodePrefixChange($userCurrent->id);
        $objWTView = new WorkingTimeView();
        $workingTime = $objWTView->getWorkingTimeByEmployeeBetween($userCurrent->id, $teamCodePre);
        $timeSetting = $workingTime['timeSetting'];
        $timeWorkingQuater = $workingTime['timeWorkingQuater'];
        
        $registerBranch = CoreConfigData::checkBranchRegister();
        $groupEmail = CoreConfigData::getGroupEmailRegisterLeave();
        $annualHolidays = CoreConfigData::getAnnualHolidays(2);
        $specialHolidays = CoreConfigData::getSpecialHolidays(2, $teamCodePre);

        $params = [
            'listLeaveDayReasons' => LeaveDayReason::getLeaveDayReasons($teamCodePre),
            'timeSetting' => $timeSetting,
            'keyDateInit' => date('Y-m-d'),
            'userCurrent' => $userCurrent,
            'compensationDays' => CoreConfigData::getCompensatoryDays($teamCodePre),
            'teamCodePreOfEmp' => $teamCodePre,
            'registerBranch' => $registerBranch,
            'specialHolidays' => $specialHolidays,
            'annualHolidays' => $annualHolidays,
            'weekends' => ManageTimeCommon::getAllWeekend(),
            'groupEmail' => $groupEmail,
            'timeWorkingQuater' => $timeWorkingQuater,
        ];
        return view('manage_time::leave.leave_admin_register', $params);
    }

    /**
     * Applicantions created by employee
     *
     * @param int|null $status status of application
     *
     * @return Response View
     */
    public function getRegisterList($status = null)
    {
        $params = $this->applications($status);
        return view('manage_time::leave.leave_register_list', $params);
    }

    /**
     * Applicantions related of employee
     *
     * @param int|null $status status of application
     *
     * @return Response View
     */
    public function getRelatedList($status = null)
    {
        $params = $this->applications($status, true);
        return view('manage_time::leave.leave_register_list', $params);
    }

    /**
     * This function calculate for display applications in register page or relater page
     *
     * @param int|null $status
     * @param boolean $isRelaterPage true if page get relater applications
     *
     * @return array
     */
    public function applications($status, $isRelaterPage = false)
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];

        if ($isRelaterPage) {
            $relaterId = $userCurrent->id;
            $createdBy = null;
        } else {
            $createdBy = $userCurrent->id;
            $relaterId = null;
        }
        $collectionModel = LeaveDayRegister::getListRegisters($createdBy, null, $status, $dataFilter, $relaterId);
        $teamCodePre = Team::getOnlyOneTeamCodePrefix($userCurrent);
        $listLeaveDayReasons = LeaveDayReason::getLeaveDayReasons($teamCodePre);

        return [
            'collectionModel' => $collectionModel,
            'listLeaveDayReasons' => $listLeaveDayReasons,
            'status' => $status,
            'isRelaterPage' => $isRelaterPage,
            'teamCodePre' => $teamCodePre
        ];
    }

    /**
     * [getApproveList: view approves list of approver]
     * @param  [int|null] $status
     * @return [view]
     */
    public function getApproveList($status = null)
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('Leave day');
        Menu::setActive('Profile');

        $userCurrent = Permission::getInstance()->getEmployee();
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];

        $isScopeApproveOfSelf = LeaveDayPermission::isScopeApproveOfSelf();
        $isScopeApproveOfTeam = LeaveDayPermission::isScopeApproveOfTeam();
        $isScopeApproveOfCompany = LeaveDayPermission::isScopeApproveOfCompany();
        if ($isScopeApproveOfSelf || $isScopeApproveOfTeam || $isScopeApproveOfCompany) {
            $collectionModel = LeaveDayRegister::getListRegisters(null, $userCurrent->id, $status, $dataFilter);
            $teamCodePre = Team::getOnlyOneTeamCodePrefix($userCurrent);
            $listLeaveDayReasons = LeaveDayReason::getLeaveDayReasons($teamCodePre);

        } else {
            View::viewErrorPermission();
        }

        $params = [
            'collectionModel' => $collectionModel,
            'listLeaveDayReasons' => $listLeaveDayReasons,
            'status' => $status
        ];

        return view('manage_time::leave.leave_approve_list', $params);
    }

    /**
     * [getManageList: view register list of manager]
     * @param  [int] $id
     * @return [type]
     */
    public function getManageList($id = null)
    {
        Breadcrumb::add('HR');
        Breadcrumb::add('Manage time');
        Breadcrumb::add('Leave day');
        Menu::setActive('HR');

        $userCurrent = Permission::getInstance()->getEmployee();
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];

        $teamIdsAvailable = null;
        $teamTreeAvailable = [];

        $isScopeManageOfTeam = LeaveDayPermission::isScopeManageOfTeam();
        $isScopeManageOfCompany = LeaveDayPermission::isScopeManageOfCompany();

        if ($isScopeManageOfCompany) {
            $teamIdsAvailable = true;
        } elseif ($isScopeManageOfTeam) {
            $teamIdsAvailable = Permission::getInstance()->isScopeTeam(null, 'manage_time::manage-time.manage.view');
            if (!$teamIdsAvailable) {
                View::viewErrorPermission();
            }

            if (!$id) {
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
            if (!in_array($id, $teamIdsAvailable)) {
                View::viewErrorPermission();
            }
            if (is_array($teamIdsAvailable) && count($teamIdsAvailable) == 1) {
                $teamIdsAvailable = Team::find($id);
            }
        } else {
            View::viewErrorPermission();
        }

        $collectionModel = LeaveDayRegister::getListManageRegisters($id, $dataFilter);
        $teamCodePre = Team::getOnlyOneTeamCodePrefix($userCurrent);
        $listLeaveDayReasons = LeaveDayReason::getLeaveDayReasons($teamCodePre);

        $params = [
            'collectionModel' => $collectionModel,
            'listLeaveDayReasons' => $listLeaveDayReasons,
            'teamIdCurrent' => $id,
            'teamIdsAvailable' => $teamIdsAvailable,
            'teamTreeAvailable' => $teamTreeAvailable,
        ];

        return view('manage_time::leave.leave_manage_list', $params);
    }

    /**
     * [editRegister: view form edit register]
     * @param  [int] $registerId
     * @return [view]
     */
    public function editRegister($registerId)
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('Leave day');
        Menu::setActive('Profile');

        $userCurrent = Permission::getInstance()->getEmployee();
        $registerRecord = LeaveDayRegister::getInformationRegister($registerId);
        $getRelationMember = EmployeeRelationship::getRelationEmpByRegister($registerId);
        if (!$registerRecord) {
            return redirect()->route('manage_time::profile.leave.register-list')->withErrors(Lang::get('team::messages.Not found item.'));
        }

        if ($userCurrent->id != $registerRecord->creator_id && !LeaveDayPermission::allowCreateEditOther()) {
            View::viewErrorPermission();
        }

        $relatedPersonsList = LeaveDayRelater::getRelatedPersons($registerId);
        $commentsList = ManageTimeComment::getReasonDisapprove($registerId, ManageTimeConst::TYPE_LEAVE_DAY);
        $isAllowEdit = false;
        if ($registerRecord->status != LeaveDayRegister::STATUS_APPROVED && $registerRecord->status != LeaveDayRegister::STATUS_CANCEL) {
            $isAllowEdit = true;
        }
        $creator = Employee::getEmpById($registerRecord->creator_id);
        $teamCodePre = Team::getOnlyOneTeamCodePrefixChange($creator);
        $listLeaveDayReasons = LeaveDayReason::getLeaveDayReasons($teamCodePre);

        // Get working time this month of employee logged
        $objWTView = new WorkingTimeView();
        $period = [
            'start_date' => Carbon::parse($registerRecord->date_start)->toDateString(),
            'end_date' => Carbon::parse($registerRecord->date_end)->toDateString(),
        ];
        $workingTime = $objWTView->getWorkingTimeByEmployeeBetween($registerRecord->creator_id, $teamCodePre, $period);
        $timeSetting = $workingTime['timeSetting'];
        $timeWorkingQuater = $workingTime['timeWorkingQuater'];

        $groupEmail = LeaveDayGroupEmail::getGroupEmail($registerId);
        $groupEmailRegister = CoreConfigData::getGroupEmailRegisterLeave();
        $registerBranch = CoreConfigData::checkBranchRegister($creator, $teamCodePre);
        if (!empty($groupEmail)) {
            $groupEmailRegister = array_diff($groupEmailRegister, $groupEmail);
        }

        // Get attach files
        $attachmentsList = ManageTimeAttachment::getAttachments($registerId, ManageTimeConst::TYPE_LEAVE_DAY);
        $appendedFiles = [];
        foreach ($attachmentsList as $file) {
            $appendedFiles[] = [
                'name' => $file->file_name,
                'type' => $file->mime_type,
                'size' => $file->size,
                'file' => url($file->path),
                'data' => ['url' => url($file->path)]
            ];
        }

        $registerRecord->number_days_off = $registerRecord->used_leave_day == ManageTimeConst:: NOT_USED_LEAVE_DAY ? 0 : $registerRecord->number_days_off;
        $leaveDay = LeaveDay::getLeaveDayById($creator->id);
        $getRelation = EmployeeRelationship::getListRelationEmpNotDie($creator->id);
        // if employee type japan
        if ($teamCodePre == Team::CODE_PREFIX_JP) {
            $grantDate = self::setGrantDateEmployeeJp($creator);
        } else {
            $grantDate = [
                'last_grant_date' => '',
                'next_grant_date' => ''
            ];
        }

        $params = [
            'registerRecord' => $registerRecord,
            'relatedPersonsList' => $relatedPersonsList,
            'commentsList' => $commentsList,
            'listLeaveDayReasons' => $listLeaveDayReasons,
            'isAllowEdit' => $isAllowEdit,
            'curEmp' => $userCurrent,
            'regsUnapporve' => ManageTimeView::dayOffUnapprove($registerRecord->creator_id),
            'timeSetting' => $timeSetting,
            'compensationDays' => CoreConfigData::getCompensatoryDays($teamCodePre),
            'teamCodePreOfEmp' => $teamCodePre,
            'groupEmail' => $groupEmail,
            'groupEmailRegister' => $groupEmailRegister,
            'registerBranch' => $registerBranch,
            'weekends' => ManageTimeCommon::getAllWeekend(),
            'appendedFiles' => json_encode($appendedFiles),
            'attachmentsList' => $attachmentsList,
            'timeWorkingQuater' => $timeWorkingQuater,
            'leaveDay' => $leaveDay,
            'getRelation' => $getRelation,
            'getRelationMember' => $getRelationMember,
            'grantDate' => $grantDate,
        ];
        return view('manage_time::leave.leave_register_edit', $params);
    }

    /**
     * [showDetailRegister: view page detail of register]
     * @param  [int] $registerId
     * @return [view]
     */
    public function showDetailRegister($registerId)
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('Leave day');
        Menu::setActive('Profile');

        $userCurrent = Permission::getInstance()->getEmployee();

        $registerRecord = LeaveDayRegister::getInformationRegister($registerId);
        if (!$registerRecord) {
            return redirect()->route('manage_time::profile.leave.approve-list')->withErrors(Lang::get('team::messages.Not found item.'));
        }

        $isAllowView = LeaveDayPermission::isAllowView($registerId, $userCurrent->id, $registerRecord);
        if ($isAllowView) {
            $relatedPersonsList = LeaveDayRelater::getRelatedPersons($registerId);
            $commentsList = ManageTimeComment::getReasonDisapprove($registerId, ManageTimeConst::TYPE_LEAVE_DAY);
        } else {
            View::viewErrorPermission();
        }

        $isAllowApprove = false;
        if (LeaveDayPermission::isAllowApprove($registerRecord, $userCurrent->id) && $registerRecord->status != LeaveDayRegister::STATUS_CANCEL) {
            $isAllowApprove = true;
        }
        $creator = Employee::getEmpById($registerRecord->creator_id);
        $teamCodePre = Team::getOnlyOneTeamCodePrefixChange($creator);
        $listLeaveDayReasons = LeaveDayReason::getLeaveDayReasons($teamCodePre);

        // Get working time this month of employee logged
        $objWTView = new WorkingTimeView();
        $period = [
            'start_date' => Carbon::parse($registerRecord->date_start)->toDateString(),
            'end_date' => Carbon::parse($registerRecord->date_end)->toDateString(),
        ];
        $workingTime = $objWTView->getWorkingTimeByEmployeeBetween($registerRecord->creator_id, $teamCodePre, $period);
        $timeSetting = $workingTime['timeSetting'];
        $timeWorkingQuater = $workingTime['timeWorkingQuater'];

        $groupEmail = LeaveDayGroupEmail::getGroupEmail($registerId);
        $groupEmailRegister = CoreConfigData::getGroupEmailRegisterLeave();
        $registerBranch = CoreConfigData::checkBranchRegister(Employee::getEmpById($registerRecord->creator_id));
        if (!empty($groupEmail)) {
            $groupEmailRegister = array_diff($groupEmailRegister, $groupEmail);
        }
        $leaveDay = LeaveDay::getLeaveDayById($registerRecord->creator_id);
        $getRelationMember = EmployeeRelationship::getRelationEmpByRegister($registerRecord->id);

        // if employee type japan
        if ($teamCodePre == Team::CODE_PREFIX_JP) {
            $grantDate = self::setGrantDateEmployeeJp($creator);
        } else {
            $grantDate = [
                'last_grant_date' => '',
                'next_grant_date' => ''
            ];
        }

        $params = [
            'registerRecord' => $registerRecord,
            'relatedPersonsList' => $relatedPersonsList,
            'commentsList' => $commentsList,
            'listLeaveDayReasons' => $listLeaveDayReasons,
            'isAllowApprove' => $isAllowApprove,
            'curEmp' => \Rikkei\Team\Model\Employee::getEmpById($registerRecord->creator_id),
            'regsUnapporve' => ManageTimeView::dayOffUnapprove($registerRecord->creator_id),
            'timeSetting' => $timeSetting,
            'compensationDays' => CoreConfigData::getCompensatoryDays($teamCodePre),
            'teamCodePreOfEmp' => $teamCodePre,
            'groupEmail' => $groupEmail,
            'groupEmailRegister' => $groupEmailRegister,
            'registerBranch' => $registerBranch,
            'weekends' => ManageTimeCommon::getAllWeekend(),
            'attachmentsList' => ManageTimeAttachment::getAttachments($registerId, ManageTimeConst::TYPE_LEAVE_DAY),
            'timeWorkingQuater' => $timeWorkingQuater,
            'leaveDay' => $leaveDay,
            'getRelationMember' => $getRelationMember,
            'grantDate' => $grantDate,
        ];
        return view('manage_time::leave.leave_register_edit', $params);
    }

    /**
     * [showPopupDetailRegister: show information of register]
     * @return show detail register to modal
     */
    public function showPopupDetailRegister(Request $request)
    {
        $registerId = $request->registerId;
        $registerRecord = LeaveDayRegister::getInformationRegister($registerId);
        $relatedPersonsList = LeaveDayRelater::getRelatedPersons($registerId);

        $params = [
            'registerRecord' => $registerRecord,
            'relatedPersonsList' => $relatedPersonsList,
        ];

        echo view('manage_time::include.modal.modal_view_leave', $params);
    }

    /**
     * Save register by admin
     * @param  Request $request
     * @return [type]
     */
    public function saveAdminRegister(Request $request)
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        $usedLeaveDay = false;
        if (!LeaveDayPermission::allowCreateEditOther()) {
            View::viewErrorPermission();
        }
        $rules = [
            'employee_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'reason' => 'required',
            'note' => 'required',
        ];
        $messages = [
            'employee_id.required' => Lang::get('manage_time::view.The registrant field is required'),
            'start_date.required' => Lang::get('manage_time::view.The start date field is required'),
            'end_date.required' => Lang::get('manage_time::view.The end date field is required'),
            'reason.required' => Lang::get('manage_time::view.The leave day type field is required'),
            'note.required' => Lang::get('manage_time::view.The leave day reason field is required'),
        ];

        if (LeaveDayReason::checkReasonTeamType($request->reason)) {
            $rules['employee_relationship'] = 'required';
            $messages['employee_relationship.required'] = Lang::get('manage_time::view.Please choose relationship member');
        }

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        $reasonLeaveDay = LeaveDayReason::find($request->reason);
        if ($reasonLeaveDay) {
            if ($reasonLeaveDay->used_leave_day == ManageTimeConst::USED_LEAVE_DAY) {
                $usedLeaveDay = true;
            }
        }

        if ($request->employee_id) {
            $employee = Employee::getEmpById($request->employee_id);
        } else {
            $employee = $userCurrent;
        }
        $checkFullDay = with(new ManageTimeView())->isFullDayHoliday($request);
        if ($checkFullDay) {
            return redirect()->back()->withErrors(Lang::get('manage_time::message.Register leave day overlap holiday need full day'));
        }

        // check again time register leaveday
        if (!self::checkTimeRegister($request, $employee)) {
            $rules['min_time_leave_day'] = 'required';
            $messages['min_time_leave_day.required'] = Lang::get('manage_time::view.Total leave diff day register leave day');
        }

        if ($request->start_date && $request->end_date) {
            if ($usedLeaveDay) {
                $numberDaysOff = $request->number_validate;
                $informationLeaveDay = LeaveDay::getInformationLeaveDayOfEmp($request->employee_id);
                $numberDaysRemain = $informationLeaveDay ? $informationLeaveDay->remain_day : 0;
                if ($numberDaysOff > $numberDaysRemain) {
                    $rules['min_time_leave_day'] = 'required';
                    $messages['min_time_leave_day.required'] = Lang::get('manage_time::view.The day of leave must be smaller day of remain');
                }
            }

            // Validate special type
            $isValidSpecialType = $reasonLeaveDay->isValidSpecialType($request, $request->employee_id);
            if (!$isValidSpecialType['valid']) {
                $rules['special_type'] = 'required';
                $messages['special_type.required'] = $isValidSpecialType['message'];
            }
        }
        DB::beginTransaction();
        try {
            $employeeId = $request->employee_id;
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $checkRegisterExist = LeaveDayRegister::checkRegisterExist($employeeId, Carbon::parse($startDate), Carbon::parse($endDate));
            if ($checkRegisterExist) {
                return redirect()->back()->withErrors(Lang::get('manage_time::message.Registration time has been identical'));
            }
            $checkLeaveReason = $this->checkLeaveReason($request, $employeeId);
            if (count($checkLeaveReason)) {
                return redirect()->back()->withErrors($checkLeaveReason['message'])->withInput();
            }
            $registerRecord = new LeaveDayRegister;
            $registerRecord->creator_id = $employeeId;
            $registerRecord->approver_id = $userCurrent->id;
            $registerRecord->substitute_id = $request->substitute;
            $registerRecord->reason_id = $request->reason;
            $registerRecord->date_start = Carbon::parse($startDate);
            $registerRecord->date_end = Carbon::parse($endDate);
            $registerRecord->number_days_off = $request->number_days_off;
            $registerRecord->note = $request->note;
            $registerRecord->status = LeaveDayRegister::STATUS_APPROVED;
            $registerRecord->company_name = isset($request->company_name) ? $request->company_name : null;
            $registerRecord->customer_name = isset($request->customer_name) ? $request->customer_name : null;
            $registerRecord->approved_at = Carbon::now();
            if (!empty($request->substitute)) {
                $registerRecord->substitute_id = $request->substitute;
            }
            $data = [];
            $dataLeaveDayTK = [];
            if ($registerRecord->save()) {
                if ($usedLeaveDay) {
                    $leaveDayRecord = LeaveDay::getInformationLeaveDayOfEmp($employeeId);
                    if ($leaveDayRecord) {
                        if ($request->number_days_off > $leaveDayRecord->remain_day) {
                            return redirect()->back()->withErrors(Lang::get('manage_time::view.The day of leave must be smaller day of remain'));
                        } else {
                            $dayUsed = $leaveDayRecord->day_used + $request->number_days_off;
                            $leaveDayRecord->day_used = $dayUsed;
                            $leaveDayRecord->save();
                        }
                    } else {
                        return redirect()->back()->withErrors(Lang::get('manage_time::message.No exist employee in leave day table'));
                    }
                }

                if (count($request->employee_relationship)) {
                    foreach($request->employee_relationship as $key => $item) {
                        $dataRela[] = [
                            'leave_day_registers_id' => $registerRecord->id,
                            'employee_relationship_id' => $item,
                            'status' => LeaveDayRegister::STATUS_APPROVED,
                        ];
                    }
                    LeaveDayRelationMember::insert($dataRela);

                    foreach($request->employee_relationship as $key => $val) {
                        EmployeeRelationship::where('id', $val)->update([
                            'is_die' => EmployeeRelationship::STATUS_IS_DIE
                        ]);
                    }
                    DB::commit();
                }

                $registerTeam = [];
                $teamsOfRegistrant = ManageTimeCommon::getTeamsOfEmployee($employeeId);
                foreach ($teamsOfRegistrant as $team) {
                    $registerTeam[] = array('register_id' => $registerRecord->id, 'team_id' => $team->id, 'role_id' => $team->role_id);
                }
                LeaveDayTeam::insert($registerTeam);
                // Save attachment
                $notificationData = [
                    'category_id' => RkNotify::CATEGORY_TIMEKEEPING
                ];
                ComeLateController::inserstFile($registerRecord, ManageTimeConst::FOLDER_ATTACH_LEAVE_DAY, ManageTimeConst::TYPE_LEAVE_DAY);
                $registerRecordNew = LeaveDayRegister::getInformationRegister($registerRecord->id);
                $data['position_appover'] = (new Employee)->getRoleAndTeams($registerRecordNew->approver_id);
                $data['user_mail'] = $userCurrent->email;
                $data['mail_to'] = $registerRecordNew->creator_email;
                $data['mail_title'] = Lang::get('manage_time::view.[Leave day] :name register leave day, from date :start_date to date :end_date', ['name' => $registerRecordNew->creator_name, 'start_date' => Carbon::parse($registerRecordNew->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecordNew->date_end)->format('d/m/Y')]);
                $data['status'] = Lang::get('manage_time::view.Approved');
                $data['registrant_name'] = $registerRecordNew->creator_name;
                $data['approver_name'] = $registerRecordNew->approver_name;
                $data['team_name'] = $registerRecordNew->role_name;
                $data['start_date'] = Carbon::parse($registerRecordNew->date_start)->format('d/m/Y');
                $data['start_time'] = Carbon::parse($registerRecordNew->date_start)->format('H:i');
                $data['end_date'] = Carbon::parse($registerRecordNew->date_end)->format('d/m/Y');
                $data['end_time'] = Carbon::parse($registerRecordNew->date_end)->format('H:i');
                $data['number_days_off'] = $registerRecordNew->number_days_off;
                $data['reason'] = View::nl2br(ManageTimeCommon::limitText($registerRecordNew->reason, 50));
                $data['note'] = View::nl2br(ManageTimeCommon::limitText($registerRecordNew->note, 50));
                $data['link'] = route('manage_time::profile.leave.detail', ['id' => $registerRecordNew->register_id]);
                $data['to_id'] = $registerRecord->creator_id;
                $data['noti_content'] = $data['mail_title'];
                $template = 'manage_time::template.leave.mail_register.mail_admin_to_employee_register';
                ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData);
                if ($registerRecordNew->substitute_id) {
                    $data['mail_to'] = $registerRecordNew->substitute_email;
                    $data['mail_title'] = Lang::get('manage_time::view.[Notification][Leave day] :name register leave day, from date :start_date to date :end_date', ['name' => $registerRecordNew->creator_name, 'start_date' => Carbon::parse($registerRecordNew->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecordNew->date_end)->format('d/m/Y')]);
                    $data['substitute_name'] = $registerRecordNew->substitute_name;
                    $data['to_id'] = $registerRecordNew->substitute_id;
                    $data['noti_content'] = $data['mail_title'];
                    $template = 'manage_time::template.leave.mail_register.mail_register_to_substitute';
                    ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData);
                }
                $relatedPersonsId = $request->related_persons_list;
                if (!empty($relatedPersonsId)) {
                    $relatePersons = \Rikkei\Team\Model\Employee::getEmpByIds($relatedPersonsId);
                    foreach ($relatePersons as $person) {
                        $registerRelaters [] = array('register_id' => $registerRecord->id, 'relater_id' => $person->id);
                        //Send mail to relaters
                        $data['mail_to'] = $person->email;
                        $data['mail_title'] = Lang::get('manage_time::view.[Notification][Leave day] :name register leave day, from date :start_date to date :end_date', ['name' => $registerRecordNew->creator_name, 'start_date' => Carbon::parse($registerRecordNew->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecordNew->date_end)->format('d/m/Y')]);
                        $data['substitute_name'] = $registerRecordNew->substitute_name;
                        $data['to_id'] = $person->id;
                        $data['noti_content'] = $data['mail_title'];
                        $data['related_person_name'] = $person->name;
                        $template = 'manage_time::template.leave.mail_register.mail_register_to_related_person';
                        ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData);
                    }
                    LeaveDayRelater::insert($registerRelaters);
                }
                $groupEmail = $request->group_email;
                $groupEmailRegister = CoreConfigData::getGroupEmailRegisterLeave();
                if (!empty($groupEmail)) {
                    $emailGroup = '';
                    $data['mail_title'] = Lang::get('manage_time::view.[Notification][Leave day] :name register leave day, from date :start_date to date :end_date', ['name' => $registerRecordNew->creator_name, 'start_date' => Carbon::parse($registerRecordNew->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecordNew->date_end)->format('d/m/Y')]);
                    $data['substitute_name'] = $registerRecordNew->substitute_name;
                    $data['noti_content'] = $data['mail_title'];
                    $template = 'manage_time::template.leave.mail_register.mail_register_to_group_email';
                    foreach ($groupEmail as $value) {
                        if (in_array($value, $groupEmailRegister)) {
                            $emailGroup = $emailGroup . $value . ';';
                            $data['mail_to'] = $value;
                            ManageTimeCommon::pushEmailToQueue($data, $template);
                        }
                    }
                    $leaveDayGroupEmail = [
                        'register_id' => $registerRecord->id,
                        'group_email' => rtrim($emailGroup, ";")
                    ];
                    LeaveDayGroupEmail::insert($leaveDayGroupEmail);
                }
                $dataLeaveDayTK[] = $registerRecord;
            }
            // ==== save timekeeping ===
            $this->insertTimekeeping($userCurrent, $dataLeaveDayTK);
            DB::commit();

            $messages = [
                'success' => [
                    Lang::get('manage_time::message.Register success'),
                ]
            ];

            return redirect()->route('manage_time::timekeeping.manage.leave')->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex->getMessage());
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('manage_time::message.An error occurred')]]);
        }
    }

    /**
     * [saveRegister: save register]
     * @param  Request $request
     * @return [view]
     */
    public function saveRegister(Request $request)
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        $rules = self::rules();
        $messages = self::messages();
        $teams = Team::getTeamOfEmployee($userCurrent->id);
        $teamCodePre = Team::getOnlyOneTeamCodePrefixChange($userCurrent, $teams);

        // if employee type japan
        if ($teamCodePre == Team::CODE_PREFIX_JP) {
            // if reason type is not 許可休暇
            if (!LeaveDayReason::checkReasonTeamTypeJpPaidLeave($request->reason)) {
                $rules['note'] = 'required';
                $messages['note.required'] = Lang::get('manage_time::view.The leave day reason field is required');
            }
            $grantDate = self::setGrantDateEmployeeJp($userCurrent, $request->start_date);
            // Compare the period of leave application time
            if (Carbon::parse($request->end_date)->format('Y-m-d') > Carbon::parse($grantDate['next_grant_date'])->format('Y-m-d')) {
                // Applications cannot be made across the annual paid leave grant date
                if(!empty($request->isAjax) && $request->isAjax == 1) {
                    return array('status' => false, 'message' => Lang::get('manage_time::message.Please enter separately before and after the grant date :date'));
                }else{
                    return redirect()->back()->withErrors(Lang::get('manage_time::message.Please enter separately before and after the grant date :date', ['date' => Carbon::parse($grantDate['next_grant_date'])->format('Y-m-d')]));
                }
            }
        } else {
            $rules['note'] = 'required';
            $messages['note.required'] = Lang::get('manage_time::view.The leave day reason field is required');
        }

        //check reason leave day
        if (LeaveDayReason::checkReasonTeamType($request->reason)) {
            $rules['employee_relationship'] = 'required';
            $messages['employee_relationship.required'] = Lang::get('manage_time::view.Please choose relationship member');
        }

        // check again time register leaveday
        if (!self::checkTimeRegister($request, $userCurrent)) {
            $rules['min_time_leave_day'] = 'required';
            $messages['min_time_leave_day.required'] = Lang::get('manage_time::view.Total leave diff day register leave day');
        }
        if (!CoreConfigData::checkBranchRegister()) {
            $request->number_validate = round($request->number_validate, 1);
        }
        if ($request->start_date && $request->end_date) {
            $reasonLeaveDay = LeaveDayReason::find($request->reason);
            if ($reasonLeaveDay && $reasonLeaveDay->used_leave_day == ManageTimeConst::USED_LEAVE_DAY) {
                $numberDaysOff = $request->number_validate;
                if ($numberDaysOff <= 0) {
                    $rules['min_time_leave_day'] = 'required';
                    $messages['min_time_leave_day.required'] = Lang::get('manage_time::view.The number day off must be than 0');
                }
                $informationLeaveDay = LeaveDay::getInformationLeaveDayOfEmp($userCurrent->id);
                $numberDaysRemain = $informationLeaveDay ? $informationLeaveDay->remain_day : 0;
                $numberDaysUnapprove = ManageTimeView::dayOffUnapprove($userCurrent->id);
                if ($numberDaysOff > $numberDaysRemain - $numberDaysUnapprove) {
                    $rules['min_time_leave_day'] = 'required';
                    $messages['min_time_leave_day.required'] = Lang::get('manage_time::view.The day of leave must be smaller day of remain');
                }
            }

            // Validate special type
            $isValidSpecialType = $reasonLeaveDay->isValidSpecialType($request, $userCurrent->id);
            if (!$isValidSpecialType['valid']) {
                $rules['special_type'] = 'required';
                $messages['special_type.required'] = $isValidSpecialType['message'];
            }
        }
        if (!empty($request->admin)) {
            $rules['employee_id'] = 'required';
            $messages['employee_id.required'] = Lang::get('manage_time::view.The registrant field is required');
        }
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            if(!empty($request->isAjax) && $request->isAjax == 1){
                $strMessage = '';
                 //dd($validator->errors());
                foreach ($validator->errors()->all() as $message) {
                    $strMessage .= $message . ' ';
                }
                return array('status' => false, 'message' => $strMessage);
            }else{
                return redirect()->back()->withErrors($validator)->withInput();
            }
        }
        $datacheck = [
            [
                "empId" => $userCurrent->id,
                "startAt" => $request->start_date
            ]
        ];
        $datacheck = json_decode(json_encode($datacheck));
        $checkLockUp = SupplementRegister::checkCloseAllTimekeeping($datacheck);
        if ($checkLockUp) {
            if(!empty($request->isAjax) && $request->isAjax == 1) {
                return array('status' => false, 'message' => 'Không thể tạo, sửa, duyệt đơn sau khi bảng công đã bị khóa!');
            }else{
                return redirect()->back()->withErrors('Không thể tạo, sửa, duyệt đơn sau khi bảng công đã bị khóa!');
            }
        }
        DB::beginTransaction();

        try {
            if (!empty($request->employee_id)) {
                $employeeId = $request->employee_id;
            } else {
                $employeeId = $userCurrent->id;
            }
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $checkRegisterExist = LeaveDayRegister::checkRegisterExist($employeeId, Carbon::parse($startDate), Carbon::parse($endDate));
            if ($checkRegisterExist) {
                if(!empty($request->isAjax) && $request->isAjax == 1) {
                    return array('status' => false, 'message' => Lang::get('manage_time::message.Registration time has been identical'));
                }else{
                    return redirect()->back()->withErrors(Lang::get('manage_time::message.Registration time has been identical'));
                }
            }
            $checkFullDay = with(new ManageTimeView())->isFullDayHoliday($request);
            if ($checkFullDay) {
                if(!empty($request->isAjax) && $request->isAjax == 1) {
                    return array('status' => false, 'message' => Lang::get('manage_time::message.Register leave day overlap holiday need full day'));
                }else{
                    return redirect()->back()->withErrors(Lang::get('manage_time::message.Register leave day overlap holiday need full day'));
                }
            }
            $checkLeaveReason = $this->checkLeaveReason($request, $employeeId);
            if (count($checkLeaveReason)) {
                if(!empty($request->isAjax) && $request->isAjax == 1) {
                    return array('status' => false, 'message' => $checkLeaveReason['message']);
                }else{
                    return redirect()->back()->withErrors($checkLeaveReason['message'])->withInput();
                }
            }
            $registerRecord = new LeaveDayRegister;
            $registerRecord->creator_id = $employeeId;
            $registerRecord->approver_id = $request->approver;
            $registerRecord->substitute_id = $request->substitute;
            $registerRecord->reason_id = $request->reason;
            $registerRecord->date_start = Carbon::parse($startDate);
            $registerRecord->date_end = Carbon::parse($endDate);
            $registerRecord->number_days_off = $request->number_validate;
            $registerRecord->note = $request->note;
            $registerRecord->status = LeaveDayRegister::STATUS_UNAPPROVE;
            $registerRecord->company_name = $request->company_name;
            $registerRecord->customer_name = $request->customer_name;

            $data = [];

            if ($registerRecord->save()) {
                $registerTeam = [];
                $teamsOfRegistrant = ManageTimeCommon::getTeamsOfEmployee($employeeId);
                foreach ($teamsOfRegistrant as $team) {
                    $registerTeam[] = array('register_id' => $registerRecord->id, 'team_id' => $team->id, 'role_id' => $team->role_id);
                }
                LeaveDayTeam::insert($registerTeam);

                // Save attachment
                ComeLateController::inserstFile($registerRecord, ManageTimeConst::FOLDER_ATTACH_LEAVE_DAY, ManageTimeConst::TYPE_LEAVE_DAY);

                $registerRecordNew = LeaveDayRegister::getInformationRegister($registerRecord->id);
                $data['user_mail'] = $userCurrent->email;
                $data['mail_to'] = $registerRecordNew->approver_email;
                $data['mail_title'] = Lang::get('manage_time::view.[Leave day] :name register leave day, from date :start_date to date :end_date', ['name' => $registerRecordNew->creator_name, 'start_date' => Carbon::parse($registerRecordNew->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecordNew->date_end)->format('d/m/Y')]);
                $data['status'] = Lang::get('manage_time::view.Unapprove');
                $data['registrant_name'] = $registerRecordNew->creator_name;
                $data['approver_name'] = $registerRecordNew->approver_name;
                $data['team_name'] = $registerRecordNew->role_name;
                $data['start_date'] = Carbon::parse($registerRecordNew->date_start)->format('d/m/Y');
                $data['start_time'] = Carbon::parse($registerRecordNew->date_start)->format('H:i');
                $data['end_date'] = Carbon::parse($registerRecordNew->date_end)->format('d/m/Y');
                $data['end_time'] = Carbon::parse($registerRecordNew->date_end)->format('H:i');
                $data['number_days_off'] = $registerRecordNew->number_days_off;
                $data['reason']          = View::nl2br(ManageTimeCommon::limitText($registerRecordNew->reason, 50));
                $data['note']            = View::nl2br(ManageTimeCommon::limitText($registerRecordNew->note, 50));
                $data['link']            = route('manage_time::profile.leave.detail', ['id' => $registerRecordNew->register_id]);
                $data['to_id']           = $registerRecord->approver_id;
                $data['noti_content']    = $data['mail_title'];
                $notificationData = [
                    'category_id' => RkNotify::CATEGORY_TIMEKEEPING
                ];
                $template = 'manage_time::template.leave.mail_register.mail_register_to_approver';
                ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData);

                if ($registerRecordNew->substitute_id) {
                    $data['mail_to'] = $registerRecordNew->substitute_email;
                    $data['mail_title'] = Lang::get('manage_time::view.[Notification][Leave day] :name register leave day, from date :start_date to date :end_date', ['name' => $registerRecordNew->creator_name, 'start_date' => Carbon::parse($registerRecordNew->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecordNew->date_end)->format('d/m/Y')]);
                    $data['substitute_name'] = $registerRecordNew->substitute_name;
                    $data['to_id'] = $registerRecordNew->substitute_id;
                    $data['noti_content'] = $data['mail_title'];
                    $template = 'manage_time::template.leave.mail_register.mail_register_to_substitute';
                    ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData);
                }

                if (count($request->employee_relationship)) {
                    foreach($request->employee_relationship as $key => $item) {
                        $dataRela[] = [
                            'leave_day_registers_id' => $registerRecord->id,
                            'employee_relationship_id' => $item,
                            'status' => LeaveDayRegister::STATUS_UNAPPROVE,
                        ];
                    }
                    LeaveDayRelationMember::insert($dataRela);
                }

                $relatedPersonsId = $request->related_persons_list;
                if (!empty($relatedPersonsId)) {
                    $relatePersons = \Rikkei\Team\Model\Employee::getEmpByIds($relatedPersonsId);
                    foreach ($relatePersons as $person) {
                        $registerRelaters [] = array('register_id' => $registerRecord->id, 'relater_id' => $person->id);

                        //Send mail to relaters
                        $data['mail_to'] = $person->email;
                        $data['mail_title'] = Lang::get('manage_time::view.[Notification][Leave day] :name register leave day, from date :start_date to date :end_date', ['name' => $registerRecordNew->creator_name, 'start_date' => Carbon::parse($registerRecordNew->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecordNew->date_end)->format('d/m/Y')]);
                        $data['substitute_name'] = $registerRecordNew->substitute_name;
                        $data['to_id'] = $person->id;
                        $data['noti_content'] = $data['mail_title'];
                        $data['related_person_name'] = $person->name;
                        $template = 'manage_time::template.leave.mail_register.mail_register_to_related_person';
                        ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData);
                    }
                    LeaveDayRelater::insert($registerRelaters);
                }
                $groupEmail = $request->group_email;
                $groupEmailRegister = CoreConfigData::getGroupEmailRegisterLeave();
                if (!empty($groupEmail)) {
                    $emailGroup = '';
                    $data['mail_title'] = Lang::get('manage_time::view.[Notification][Leave day] :name register leave day, from date :start_date to date :end_date', ['name' => $registerRecordNew->creator_name, 'start_date' => Carbon::parse($registerRecordNew->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecordNew->date_end)->format('d/m/Y')]);
                    $data['substitute_name'] = $registerRecordNew->substitute_name;
                    $data['noti_content'] = $data['mail_title'];
                    $template = 'manage_time::template.leave.mail_register.mail_register_to_group_email';
                    foreach ($groupEmail as $value) {
                        if (in_array($value, $groupEmailRegister)) {
                            $emailGroup = $emailGroup . $value . ';';
                            $data['mail_to'] = $value;
                            ManageTimeCommon::pushEmailToQueue($data, $template);
                        }
                    }
                    $leaveDayGroupEmail = [
                        'register_id' => $registerRecord->id,
                        'group_email' => rtrim($emailGroup, ";")
                    ];
                    LeaveDayGroupEmail::insert($leaveDayGroupEmail);
                }
            }

            DB::commit();

            $messages = [
                'success' => [
                    Lang::get('manage_time::message.Register success'),
                ]
            ];

            if(!empty($request->isAjax) && $request->isAjax == 1) {
                return array('status' => true, 'message' => 'Thành công');
            }else{
                return redirect()->route('manage_time::profile.leave.edit', ['id' => $registerRecord->id])->with('messages', $messages);
            }
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex->getMessage());
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('manage_time::message.An error occurred')]]);
        }
    }

    public function leaveRegister(Request $request)
    {
        dd($request->all());
    }

    /**
     * [updateRegister: update register]
     * @param  Request $request
     * @return [view]
     */
    public function updateRegister(Request $request)
    {
        $rules = self::rules();
        $messages = self::messages();
        $leaveDayRegister = LeaveDayRegister::find($request->register_id);
        $userCurrent = Permission::getInstance()->getEmployee();
        $userCreate = $userCurrent;
        $teams = Team::getTeamOfEmployee($userCurrent->id);
        $teamCodePre = Team::getOnlyOneTeamCodePrefixChange($userCurrent, $teams);
        
        // if employee type japan
        if ($teamCodePre == Team::CODE_PREFIX_JP) {
            // if reason type is not 許可休暇
            if (!LeaveDayReason::checkReasonTeamTypeJpPaidLeave($request->reason)) {
                $rules['note'] = 'required';
                $messages['note.required'] = Lang::get('manage_time::view.The leave day reason field is required');
            }
            $grantDate = self::setGrantDateEmployeeJp($userCurrent, $request->start_date);
            // Compare the period of leave application time
            if (Carbon::parse($request->end_date)->format('Y-m-d') > Carbon::parse($grantDate['next_grant_date'])->format('Y-m-d')) {
                // Applications cannot be made across the annual paid leave grant date
                return redirect()->back()->withErrors(Lang::get('manage_time::message.Please enter separately before and after the grant date :date', ['date' => Carbon::parse($grantDate['next_grant_date'])->format('Y-m-d')]));
            }
        } else {
            $rules['note'] = 'required';
            $messages['note.required'] = Lang::get('manage_time::view.The leave day reason field is required');
        }

        if ($leaveDayRegister->creator_id != $userCurrent->id) {
            $userCreate = Employee::find($leaveDayRegister->creator_id);
        }
        //check reason leave day
        if (LeaveDayReason::checkReasonTeamType($request->reason)) {
            $rules['employee_relationship'] = 'required';
            $messages['employee_relationship.required'] = Lang::get('manage_time::view.Please choose relationship member');
        }

        // check again time register leaveday
        if (!self::checkTimeRegister($request, $userCreate)) {
            $rules['min_time_leave_day'] = 'required';
            $messages['min_time_leave_day.required'] = Lang::get('manage_time::view.Total leave diff day register leave day');
        }
        $reasonLeaveDay = LeaveDayReason::find($request->reason);
        $salaryRate = explode(' ', $request->salary_rate);

        if ((int)$reasonLeaveDay->salary_rate - (int)$salaryRate[0]) {
            $rules['information_register_false'] = 'required';
            $messages['information_register_false.required'] = Lang::get('manage_time::message.Wrong registration information. please register again!');
        }
        if (!CoreConfigData::checkBranchRegister()) {
            $request->number_validate = round($request->number_validate, 1);
        }

        $registerRecord = LeaveDayRegister::getInformationRegister($request->register_id);

        $registerId = $request->register_id;
        if (!$registerRecord) {
            return redirect()->route('manage_time::profile.leave.register-list')->withErrors(Lang::get('team::messages.Not found item.'));
        }

        if ($userCurrent->id != $registerRecord->creator_id && !LeaveDayPermission::allowCreateEditOther()) {
            View::viewErrorPermission();
        }

        if ($registerRecord->status == LeaveDayRegister::STATUS_APPROVED) {
            return redirect()->route('manage_time::profile.leave.edit', ['id' => $registerId])->withErrors(Lang::get('manage_time::message.The register of leave day has been approved can not edit'));
        }

        if ($registerRecord->status == LeaveDayRegister::STATUS_CANCEL) {
            return redirect()->route('manage_time::profile.leave.edit', ['id' => $registerId])->withErrors(Lang::get('manage_time::message.The register of leave day has been canceled can not edit'));
        }

        if ($request->start_date && $request->end_date) {
            if ($reasonLeaveDay) {
                if ($reasonLeaveDay->used_leave_day == ManageTimeConst::USED_LEAVE_DAY) {
                    $numberDaysOff = $request->number_validate;
                    $informationLeaveDay = LeaveDay::getInformationLeaveDayOfEmp($registerRecord->creator_id);
                    $numberDaysRemain = $informationLeaveDay ? $informationLeaveDay->remain_day : 0;
                    $numberDaysUnapprove = ManageTimeView::dayOffUnapprove($registerRecord->creator_id);

                    $oldNumberDayOff = $registerRecord->used_leave_day == ManageTimeConst:: NOT_USED_LEAVE_DAY ? 0 : $registerRecord->number_days_off;

                    if ($numberDaysOff - $oldNumberDayOff > $numberDaysRemain - $numberDaysUnapprove) {
                        $rules['min_time_leave_day'] = 'required';
                        $messages['min_time_leave_day.required'] = Lang::get('manage_time::view.The day of leave must be smaller day of remain');
                    }
                }
            }

            // Validate special type
            $isValidSpecialType = $reasonLeaveDay->isValidSpecialType($request, $registerRecord->creator_id);
            if (!$isValidSpecialType['valid']) {
                $rules['special_type'] = 'required';
                $messages['special_type.required'] = $isValidSpecialType['message'];
            }
        }
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return redirect()->route('manage_time::profile.leave.edit', ['id' => $registerId])
                ->withErrors($validator)
                ->withInput();
        }
        $datacheck = [
            [
                "empId" => $registerRecord->creator_id,
                "startAt" => $request->start_date
            ]
        ];
        $datacheck = json_decode(json_encode($datacheck));
        $checkLockUp = SupplementRegister::checkCloseAllTimekeeping($datacheck);
        if ($checkLockUp) {
            return redirect()->back()->withErrors("Không thể tạo, sửa, duyệt đơn sau khi bảng công đã bị khóa!");
        }

        DB::beginTransaction();
        try {
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $checkRegisterExist = LeaveDayRegister::checkRegisterExist($registerRecord->creator_id, Carbon::parse($startDate), Carbon::parse($endDate), $registerId);
            if ($checkRegisterExist) {
                return redirect()->back()->withErrors(Lang::get('manage_time::message.Registration time has been identical'));
            }

            $checkFullDay = with(new ManageTimeView())->isFullDayHoliday($request);
            if ($checkFullDay) {
                return redirect()->back()->withErrors(Lang::get('manage_time::message.Register leave day overlap holiday need full day'));
            }
            $checkLeaveReason = $this->checkLeaveReason($request, $registerRecord->creator_id);
            if (count($checkLeaveReason)) {
                return redirect()->back()->withErrors($checkLeaveReason['message'])->withInput();
            }
            $registerRecord->approver_id = $request->approver;
            $registerRecord->substitute_id = $request->substitute;
            $registerRecord->reason_id = $request->reason;
            $registerRecord->date_start = Carbon::parse($startDate);
            $registerRecord->date_end = Carbon::parse($endDate);
            $registerRecord->number_days_off = $request->number_validate;
            $registerRecord->note = $request->note;
            $registerRecord->status = LeaveDayRegister::STATUS_UNAPPROVE;
            $registerRecord->company_name = $request->company_name;
            $registerRecord->customer_name = $request->customer_name;

            $data = [];

            if ($registerRecord->save()) {
                LeaveDayTeam::where('register_id', $registerId)->delete();
                $registerTeam = [];
                $teamsOfRegistrant = ManageTimeCommon::getTeamsOfEmployee($registerRecord->creator_id);
                foreach ($teamsOfRegistrant as $team) {
                    $registerTeam[] = array('register_id' => $registerRecord->id, 'team_id' => $team->id, 'role_id' => $team->role_id);
                }
                LeaveDayTeam::insert($registerTeam);

                LeaveDayRelationMember::where('leave_day_registers_id', $registerId)->delete();
                if (count($request->employee_relationship)) {
                    foreach($request->employee_relationship as $key => $item) {
                        $dataRela[] = [
                            'leave_day_registers_id' => $registerRecord->id,
                            'employee_relationship_id' => $item,
                            'status' => LeaveDayRegister::STATUS_UNAPPROVE,
                        ];
                    }
                    LeaveDayRelationMember::insert($dataRela);
                }

                // Save attachment
                ComeLateController::updateFile($registerRecord, $registerId, ManageTimeConst::FOLDER_ATTACH_LEAVE_DAY, ManageTimeConst::TYPE_LEAVE_DAY);

                $registerRecordNew = LeaveDayRegister::getInformationRegister($registerRecord->id);
                $data['user_mail'] = $userCurrent->email;
                $data['mail_to'] = $registerRecordNew->approver_email;
                $data['mail_title'] = Lang::get('manage_time::view.[Leave day] :name register leave day, from date :start_date to date :end_date', ['name' => $registerRecordNew->creator_name, 'start_date' => Carbon::parse($registerRecordNew->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecordNew->date_end)->format('d/m/Y')]);
                $data['status'] = Lang::get('manage_time::view.Unapprove');
                $data['registrant_name'] = $registerRecordNew->creator_name;
                $data['approver_name'] = $registerRecordNew->approver_name;
                $data['team_name'] = $registerRecordNew->role_name;
                $data['start_date'] = Carbon::parse($registerRecordNew->date_start)->format('d/m/Y');
                $data['start_time'] = Carbon::parse($registerRecordNew->date_start)->format('H:i');
                $data['end_date'] = Carbon::parse($registerRecordNew->date_end)->format('d/m/Y');
                $data['end_time'] = Carbon::parse($registerRecordNew->date_end)->format('H:i');
                $data['number_days_off'] = $registerRecordNew->number_days_off;
                $data['reason'] = View::nl2br(ManageTimeCommon::limitText($registerRecordNew->reason, 50));
                $data['note'] = View::nl2br(ManageTimeCommon::limitText($registerRecordNew->note, 50));
                $data['link'] = route('manage_time::profile.leave.detail', ['id' => $registerRecordNew->register_id]);
                $data['to_id'] = $registerRecordNew->approver_id;
                $data['noti_content'] = $data['mail_title'];

                $notificationData = [
                    'category_id' => RkNotify::CATEGORY_TIMEKEEPING
                ];
                $template = 'manage_time::template.leave.mail_register.mail_register_to_approver';
                ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData);

                if ($registerRecordNew->substitute_id) {
                    $data['mail_to'] = $registerRecordNew->substitute_email;
                    $data['mail_title'] = Lang::get('manage_time::view.[Notification][Leave day] :name register leave day, from date :start_date to date :end_date', ['name' => $registerRecordNew->creator_name, 'start_date' => Carbon::parse($registerRecordNew->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecordNew->date_end)->format('d/m/Y')]);
                    $data['substitute_name'] = $registerRecordNew->substitute_name;
                    $data['to_id'] = $registerRecordNew->substitute_id;
                    $data['noti_content'] = $data['mail_title'];
                    $template = 'manage_time::template.leave.mail_register.mail_register_to_substitute';
                    ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData);
                }

                LeaveDayRelater::where('register_id', $registerId)->delete();
                $relatedPersons = $request->related_persons_list;
                if (!empty($relatedPersons)) {
                    foreach ($relatedPersons as $key => $value) {
                        $registerRelaters[] = array('register_id' => $registerRecord->id, 'relater_id' => $value);
                    }
                    LeaveDayRelater::insert($registerRelaters);
                }
                $groupEmail = $request->group_email;
                $groupEmailRegister = CoreConfigData::getGroupEmailRegisterLeave();
                LeaveDayGroupEmail::where('register_id', $registerId)->delete();
                if (!empty($groupEmail)) {
                    $emailGroup = '';
                    $data['mail_title'] = Lang::get('manage_time::view.[Notification][Leave day] :name register leave day, from date :start_date to date :end_date', ['name' => $registerRecordNew->creator_name, 'start_date' => Carbon::parse($registerRecordNew->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecordNew->date_end)->format('d/m/Y')]);
                    $data['substitute_name'] = $registerRecordNew->substitute_name;
                    $data['noti_content'] = $data['mail_title'];
                    $template = 'manage_time::template.leave.mail_register.mail_register_to_group_email';
                    foreach ($groupEmail as $value) {
                        if (in_array($value, $groupEmailRegister)) {
                            $emailGroup = $emailGroup . $value . ';';
                            $data['mail_to'] = $value;
                            ManageTimeCommon::pushEmailToQueue($data, $template);
                        }
                    }
                    $leaveDayGroupEmail = [
                        'register_id' => $registerRecord->id,
                        'group_email' => rtrim($emailGroup, ";")
                    ];
                    LeaveDayGroupEmail::insert($leaveDayGroupEmail);
                }
            }
            DB::commit();

            $messages = [
                'success' => [
                    Lang::get('manage_time::message.Update success'),
                ]
            ];

            return redirect()->route('manage_time::profile.leave.edit', ['id' => $registerId])->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex->getMessage());
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('manage_time::message.An error occurred')]]);
        }
    }

    /**
     * [deleteRegister: delete register]
     * @param  Request $request
     * @return [json]
     */
    public function deleteRegister(Request $request)
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        $urlCurrent = $request->urlCurrent;
        $registerId = $request->registerId;
        $registerRecord = LeaveDayRegister::find($registerId);
        if (!$registerRecord) {
            $messages = [
                'errors' => [
                    Lang::get('team::messages.Not found item.'),
                ]
            ];

            $request->session()->flash('messages', $messages);
            echo json_encode(['url' => $urlCurrent]);
            return;
        }

        if ($userCurrent->id != $registerRecord->creator_id && !LeaveDayPermission::allowCreateEditOther()) {
            $messages = [
                'errors' => [
                    Lang::get('manage_time::message.You do not have permission to delete this object'),
                ]
            ];

            $request->session()->flash('messages', $messages);
            echo json_encode(['url' => $urlCurrent]);
            return;
        }

        if ($registerRecord->status == LeaveDayRegister::STATUS_APPROVED) {
            $messages = [
                'errors' => [
                    Lang::get('manage_time::message.The register of leave day has been approved cannot delete'),
                ]
            ];

            $request->session()->flash('messages', $messages);
            echo json_encode(['url' => $urlCurrent]);
            return;
        }

        $registerRecord->status = LeaveDayRegister::STATUS_CANCEL;
        $registerRecord->save();
        $registerRecord->delete();
        LeaveDayRelationMember::where('leave_day_registers_id', $registerRecord->id)->delete();

        $messages = [
            'success' => [
                Lang::get('manage_time::message.The register of leave day has delete success'),
            ]
        ];

        $request->session()->flash('messages', $messages);
        echo json_encode(['url' => $urlCurrent]);
    }

    /**
     * [approveRegister: approve register]
     * @param  Request $request
     * @return [json]
     */
    public function approveRegister(Request $request)
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        $urlCurrent = $request->urlCurrent;
        $arrRegisterId = explode(',', $request->registerId);
        $listRegisterIdApprove = LeaveDayRegister::getRegisterByStatus($arrRegisterId, LeaveDayRegister::STATUS_APPROVED);

        // check exits
        $messages = with(new ManageTimeView())->checkOverlap($arrRegisterId, ManageTimeView::STR_LEAVE_DAYS);
        if (count($messages)) {
            $request->session()->flash('messages', $messages);
            echo json_encode(['url' => $urlCurrent]);
            return;
        }
        $errorsOverdueLeaveDay = [];
        $dataLeaveDayTK = [];
        if (count($listRegisterIdApprove)) {
            foreach ($listRegisterIdApprove as $registerId) {
                $registerRecord = LeaveDayRegister::getInformationRegister($registerId);
                if ($registerRecord) {
                    // Check Permission
                    if (!LeaveDayPermission::isAllowApprove($registerRecord, $userCurrent->id) || $registerRecord->status == LeaveDayRegister::STATUS_CANCEL) {
                        $errors['errors']  = trans('manage_time::message.Permission denied');
                        $request->session()->flash('messages', ['errors' => $errors]);
                        return json_encode(['url' => $urlCurrent]);
                    }
                    $datacheck = [
                        [
                            "empId" => $registerRecord->creator_id,
                            "startAt" => $registerRecord->date_start
                        ]
                    ];
                    $datacheck = json_decode(json_encode($datacheck));
                    $checkLockUp = SupplementRegister::checkCloseAllTimekeeping($datacheck);
                    if ($checkLockUp) {
                        $messages = 'Không thể tạo, sửa, duyệt đơn sau khi bảng công đã bị khóa!';
                        $request->session()->flash('messages', ['errors' => [$messages]]);
                        return json_encode(['url' => $urlCurrent]);
                    }

                    $startDate = Carbon::parse($registerRecord->date_start)->format('d/m/Y');
                    $startTime = Carbon::parse($registerRecord->date_start)->format('H:i');
                    $endDate = Carbon::parse($registerRecord->date_end)->format('d/m/Y');
                    $endTime = Carbon::parse($registerRecord->date_end)->format('H:i');
                    //$changes: store leave day changes
                    $changes = [];
                    $leaveDayPermission = new LeaveDayPermission();
                    if ($registerRecord->used_leave_day == ManageTimeConst::USED_LEAVE_DAY) {
                        $leaveDayRecord = LeaveDay::getInformationLeaveDayOfEmp($registerRecord->creator_id);
                        if ($leaveDayRecord) {
                            if ($registerRecord->number_days_off > $leaveDayRecord->remain_day) {
                                $errorsOverdueLeaveDay[] = Lang::get('manage_time::view.The register of leave day of :registrant_name, from :start_time of date :start_date to :end_time of :end_date, number days off :number_days_off', ['registrant_name' => $registerRecord->creator_name, 'start_time' => $startTime, 'start_date' => $startDate, 'end_time' => $endTime, 'end_date' => $endDate, 'number_days_off' => $registerRecord->number_days_off]);
                                continue;
                            } else {
                                $dayUsed = $leaveDayRecord->day_used + $registerRecord->number_days_off;
                                // Find leave day changes
                                $newData['day_used'] = $dayUsed;

                                $changes = $leaveDayPermission->findChanges($leaveDayRecord, $newData);

                                $leaveDayRecord->day_used = $dayUsed;
                                $leaveDayRecord->save();
                            }
                        } else {
                            $errorsOverdueLeaveDay[] = Lang::get('manage_time::view.The register of leave day of :registrant_name, from :start_time of date :start_date to :end_time of :end_date, number days off :number_days_off', ['registrant_name' => $registerRecord->creator_name, 'start_time' => $startTime, 'start_date' => $startDate, 'end_time' => $endTime, 'end_date' => $endDate, 'number_days_off' => $registerRecord->number_days_off]);
                            continue;
                        }
                    }
                    $registerRecord->status = LeaveDayRegister::STATUS_APPROVED;
                    $registerRecord->approver_id = $userCurrent->id;
                    $registerRecord->approved_at = Carbon::now();
                    
                    $data = [];
                    if ($registerRecord->save()) {
                        // Save leave day history
                        if (count($changes)) {
                            $leaveDayPermission->saveHistory($registerRecord->creator_id, $changes, LeaveDayHistories::TYPE_APPROVE);
                        }

                        if (LeaveDayReason::checkReasonTeamType($registerRecord->reason_id)) {
                            $getRelationMember = EmployeeRelationship::getRelationEmpByRegister($registerRecord->id);
                            LeaveDayRelationMember::where('leave_day_registers_id', $registerRecord->id)->delete();
                            if (count($getRelationMember)) {
                                foreach($getRelationMember as $key => $item) {
                                    $dataRela[] = [
                                        'leave_day_registers_id' => $registerRecord->id,
                                        'employee_relationship_id' => $item["employee_relationship_id"],
                                        'status' => LeaveDayRegister::STATUS_APPROVED,
                                    ];
                                }
                                LeaveDayRelationMember::insert($dataRela);

                                foreach($getRelationMember as $key => $val) {
                                    EmployeeRelationship::where('id', $val["employee_relationship_id"])->update([
                                        'is_die' => EmployeeRelationship::STATUS_IS_DIE
                                    ]);
                                }
                                DB::commit();
                            }
                        }

                        // Push email and notification
                        $data['user_mail'] = $userCurrent->email;
                        $data['mail_to'] = $registerRecord->creator_email;
                        $data['mail_title'] = Lang::get('manage_time::view.[Approved][Leave day] :name register leave day, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
                        $data['status'] = Lang::get('manage_time::view.Approved');
                        $data['registrant_name'] = $registerRecord->creator_name;
                        $data['team_name'] = $registerRecord->role_name;
                        $data['start_date'] = $startDate;
                        $data['start_time'] = $startTime;
                        $data['end_date'] = $endDate;
                        $data['end_time'] = $endTime;
                        $data['number_days_off'] = $registerRecord->number_days_off;
                        $data['reason'] = View::nl2br(ManageTimeCommon::limitText($registerRecord->reason, 50));
                        $data['note'] = View::nl2br(ManageTimeCommon::limitText($registerRecord->note, 50));
                        $data['link'] = route('manage_time::profile.leave.detail', ['id' => $registerRecord->register_id]);
                        $data['approver_name'] = $registerRecord->approver_name;
                        $data['approver_position'] = '';
                        $approver = $registerRecord->getApproverInformation();
                        if ($approver) {
                            $data['approver_position'] = $approver->approver_position;
                        }
                        $data['to_id'] = $registerRecord->creator_id;
                        $data['noti_content'] = trans('manage_time::view.The register of leave day has been considered:') . ' ' . $data['status'];

                        $template = 'manage_time::template.leave.mail_approve.mail_approve_to_registrant';
                        $notificationData = [
                            'category_id' => RkNotify::CATEGORY_TIMEKEEPING
                        ];
                        if (ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData)) {
                            if ($registerRecord->substitute_id) {
                                $data['mail_title'] = Lang::get('manage_time::view.[Notification][Leave day] :name register leave day, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
                                $data['mail_to'] = $registerRecord->substitute_email;
                                $data['substitute_name'] = $registerRecord->substitute_name;
                                $data['to_id'] = $registerRecord->substitute_id;
                                $data['noti_content'] = trans(
                                        'manage_time::view.The register of leave day of :registrant_name, :team_name which you replace job is considered:',
                                        $data
                                    ) . ' ' . $data['status'];
                                $template = 'manage_time::template.leave.mail_approve.mail_approve_to_substitute';
                                ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData);
                            }

                            $relatedPersons = LeaveDayRelater::getRelatedPersons($registerRecord->id);
                            if (count($relatedPersons)) {
                                $data['mail_title'] = Lang::get('manage_time::view.[Notification][Leave day] :name register leave day, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
                                foreach ($relatedPersons as $item) {
                                    $data['mail_to'] = $item->relater_email;
                                    $data['related_person_name'] = $item->relater_name;
                                    $template = 'manage_time::template.leave.mail_approve.mail_approve_to_related_person';
                                    ManageTimeCommon::pushEmailToQueue($data, $template);
                                }
                                \RkNotify::put(
                                    $relatedPersons->lists('relater_id')->toArray(),
                                    trans('manage_time::view.The register of leave day of :registrant_name, :team_name related to you is considered:', $data).' '.$data['status'],
                                    $data['link'], ['category_id' => RkNotify::CATEGORY_TIMEKEEPING]
                                );
                            }

                            $groupEmail = LeaveDayGroupEmail::getGroupEmail($registerRecord->id);
                            if (count($groupEmail)) {
                                $data['mail_title'] = Lang::get('manage_time::view.[Notification][Leave day] :name register leave day, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
                                $template = 'manage_time::template.leave.mail_approve.mail_approve_to_group_email';
                                foreach ($groupEmail as $item) {
                                    $data['mail_to'] = $item;
                                    ManageTimeCommon::pushEmailToQueue($data, $template);
                                }
                            }
                        }
                    }
                    $dataLeaveDayTK[] = $registerRecord;
                }
            }
            // ==== save timekeeping ===
            $this->insertTimekeeping($userCurrent, $dataLeaveDayTK);
        }
        if (count($errorsOverdueLeaveDay)) {
            $messages = [
                'errors' => array_merge([Lang::get('manage_time::message.Registers of leave day can not edit because the day of remain than day of leave:'),], $errorsOverdueLeaveDay)
            ];
        } else {
            $messages = [
                'success' => [
                    Lang::get('manage_time::message.The register of leave day has approve success'),
                ]
            ];
        }

        $request->session()->flash('messages', $messages);
        echo json_encode(['url' => $urlCurrent]);
    }

    /**
     * [disapproveRegister: approve register]
     * @param  Request $request
     * @return [json]
     */
    public function disapproveRegister(Request $request)
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        $urlCurrent = $request->urlCurrent;
        $arrRegisterId = explode(',', $request->registerId);
        $reasonDisapprove = $request->reasonDisapprove;
        if (empty($reasonDisapprove)) {
            $messages = [
                'errors' => [
                    Lang::get('manage_time::message.You have not entered a reason not approve'),
                ]
            ];

            $request->session()->flash('messages', $messages);
            echo json_encode(['url' => $urlCurrent]);
            return;
        }

        $listRegisterIdDisapprove = LeaveDayRegister::getRegisterByStatus($arrRegisterId, LeaveDayRegister::STATUS_DISAPPROVE);

        if (count($listRegisterIdDisapprove)) {
            $dataLeaveDayTK = [];
            foreach ($listRegisterIdDisapprove as $registerId) {
                $registerRecord = LeaveDayRegister::getInformationRegister($registerId);
                if ($registerRecord) {
                    // Check Permission
                    if (!LeaveDayPermission::isAllowApprove($registerRecord, $userCurrent->id) || $registerRecord->status == LeaveDayRegister::STATUS_CANCEL) {
                        $errors['errors']  = trans('manage_time::message.Permission denied');
                        $request->session()->flash('messages', ['errors' => $errors]);
                        return json_encode(['url' => $urlCurrent]);
                    }

                    //$changes: store leave day changes
                    $changes = [];
                    $leaveDayPermission = new LeaveDayPermission();
                    if ($registerRecord->used_leave_day == ManageTimeConst::USED_LEAVE_DAY && $registerRecord->status == LeaveDayRegister::STATUS_APPROVED) {
                        $leaveDayRecord = LeaveDay::getInformationLeaveDayOfEmp($registerRecord->creator_id);
                        if ($leaveDayRecord) {
                            $dayUsed = $leaveDayRecord->day_used - $registerRecord->number_days_off;
                            if ($dayUsed < 0) {
                                $dayUsed = 0;
                            }
                            // Find leave day changes
                            $newData['day_used'] = $dayUsed;
                            $changes = $leaveDayPermission->findChanges($leaveDayRecord, $newData);

                            $leaveDayRecord->day_used = $dayUsed;
                            $leaveDayRecord->save();
                        }
                    }

                    $registerRecord->status = LeaveDayRegister::STATUS_DISAPPROVE;
                    $registerRecord->approver_id = $userCurrent->id;
                    $registerRecord->approved_at = Carbon::now();

                    $data = [];
                    if ($registerRecord->save()) {
                        // Save leave day history
                        if (count($changes)) {
                            $leaveDayPermission->saveHistory($registerRecord->creator_id, $changes, LeaveDayHistories::TYPE_CANCEL_APPROVE);
                        }

                        if (LeaveDayReason::checkReasonTeamType($registerRecord->reason_id)) {
                            $getRelationMember = EmployeeRelationship::getRelationEmpByRegister($registerRecord->id);
                            LeaveDayRelationMember::where('leave_day_registers_id', $registerRecord->id)->delete();
                            if (count($getRelationMember)) {
                                foreach($getRelationMember as $key => $item) {
                                    $dataRela[] = [
                                        'leave_day_registers_id' => $registerRecord->id,
                                        'employee_relationship_id' => $item["employee_relationship_id"],
                                        'status' => LeaveDayRegister::STATUS_DISAPPROVE,
                                    ];
                                }
                                LeaveDayRelationMember::insert($dataRela);

                                foreach($getRelationMember as $key => $val) {
                                    EmployeeRelationship::where('id', $val["employee_relationship_id"])->update([
                                        'is_die' => EmployeeRelationship::STATUS_IS_NOT_DIE
                                    ]);
                                }
                                DB::commit();
                            }
                        }

                        $registerComment = new ManageTimeComment;
                        $registerComment->register_id = $registerId;
                        $registerComment->comment = $reasonDisapprove;
                        $registerComment->type = ManageTimeConst::TYPE_LEAVE_DAY;
                        $registerComment->created_by = $userCurrent->id;
                        $registerComment->save();

                        $data['user_mail'] = $userCurrent->email;
                        $data['mail_to'] = $registerRecord->creator_email;
                        $data['mail_title'] = Lang::get('manage_time::view.[Unapproved][Leave day] :name register leave day, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
                        $data['status'] = Lang::get('manage_time::view.Unapprove');
                        $data['registrant_name'] = $registerRecord->creator_name;
                        $data['team_name'] = $registerRecord->role_name;
                        $data['start_date'] = Carbon::parse($registerRecord->date_start)->format('d/m/Y');
                        $data['start_time'] = Carbon::parse($registerRecord->date_start)->format('H:i');
                        $data['end_date'] = Carbon::parse($registerRecord->date_end)->format('d/m/Y');
                        $data['end_time'] = Carbon::parse($registerRecord->date_end)->format('H:i');
                        $data['number_days_off'] = $registerRecord->number_days_off;
                        $data['reason'] = View::nl2br(ManageTimeCommon::limitText($registerRecord->reason, 50));
                        $data['reason_disapprove'] = View::nl2br(ManageTimeCommon::limitText($reasonDisapprove, 50));
                        $data['note'] = View::nl2br(ManageTimeCommon::limitText($registerRecord->note, 50));
                        $data['link'] = route('manage_time::profile.leave.detail', ['id' => $registerRecord->register_id]);
                        $data['to_id'] = $registerRecord->creator_id;
                        $data['noti_content'] = trans('manage_time::view.The register of leave day has been considered:') . ' ' . $data['status'];

                        $template = 'manage_time::template.leave.mail_disapprove.mail_disapprove_to_registrant';
                        $notificationData = [
                            'category_id' => RkNotify::CATEGORY_TIMEKEEPING
                        ];
                        if (ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData)) {
                            if ($registerRecord->substitute_id) {
                                $data['mail_to'] = $registerRecord->substitute_email;
                                $data['mail_title'] = Lang::get('manage_time::view.[Notification][Leave day] :name register leave day, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
                                $data['substitute_name'] = $registerRecord->substitute_name;
                                $template = 'manage_time::template.leave.mail_disapprove.mail_disapprove_to_substitute';
                                $data['to_id'] = $registerRecord->substitute_id;
                                $data['noti_content'] = $data['mail_title'];
                                ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData);
                            }

                            $groupEmail = LeaveDayGroupEmail::getGroupEmail($registerRecord->id);
                            if (count($groupEmail)) {
                                $data['mail_title'] = Lang::get('manage_time::view.[Notification][Leave day] :name register leave day, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
                                foreach ($groupEmail as $item) {
                                    $data['mail_to'] = $item;
                                    $data['status'] = Lang::get('manage_time::view.Unapprove');
                                    $data['reason_disapprove'] = View::nl2br(ManageTimeCommon::limitText($reasonDisapprove, 50));
                                    $template = 'manage_time::template.leave.mail_disapprove.mail_disapprove_to_group_email';
                                    ManageTimeCommon::pushEmailToQueue($data, $template);
                                }
                            }
                        }
                        $dataLeaveDayTK[] = $registerRecord;
                    }
                }
            }
            // ==== save timekeeping ===
            $this->insertTimekeeping($userCurrent, $dataLeaveDayTK);
        }

        $messages = [
            'success' => [
                Lang::get('manage_time::message.The register of leave day has disapprove success'),
            ]
        ];

        $request->session()->flash('messages', $messages);
        echo json_encode(['url' => $urlCurrent]);
    }

    /**
     * [findEmployee: search related persons]
     * @param  Request $request
     * @return [json]
     */
    public function findEmployee(Request $request)
    {
        $keySearch = trim($request->q);

        if (empty($keySearch)) {
            return Response::json([]);
        }

        $employees = ManageTimeCommon::searchEmployee($keySearch);

        $formattedEmployees = [];

        foreach ($employees as $employee) {
            $formattedEmployees[] = ['id' => $employee->id, 'text' => $employee->name . ' (' . preg_replace('/@.*/', '', $employee->email) . ')'];
        }

        return Response::json($formattedEmployees);
    }

    /**
     * Check Leave day application exist
     *
     * @param  Request $request
     *
     * @return Response json
     */
    public function checkRegisterExist(Request $request)
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        $employeeId = $request->employeeId;
        if (!$employeeId) {
            $employeeId = $userCurrent->id;
        }
        $startDate = $request->startDate;
        $endDate = $request->endDate;
        $registerId = $request->registerId;
        $checkRegisterExist = LeaveDayRegister::checkRegisterExist($employeeId, Carbon::parse($startDate), Carbon::parse($endDate), $registerId);

        return Response::json($checkRegisterExist);
    }

    /**
     * Check leave applicant exist by special type
     *
     * @param Request $request
     *
     * @return Response json
     */
    public function checkRegisterTypeExist(Request $request)
    {
        $employeeId = $request->employeeId;
        if (!$employeeId) {
            $userCurrent = Permission::getInstance()->getEmployee();
            $employeeId = $userCurrent->id;
        }
        $startDate = $request->startDate;
        $registerId = $request->registerId;
        $reasonLeaveDay = LeaveDayReason::find($request->reasonId);

        $count = $reasonLeaveDay->countByUnit($employeeId, $reasonLeaveDay->id, $startDate, $reasonLeaveDay->repeated, $reasonLeaveDay->unit, $registerId);
        return Response::json([
            'exist' => $count ? true : false,
            'message' => Lang::get('manage_time::message.With the application type :name, you can only apply for one off in :repeated :unit', [
                'name' => $reasonLeaveDay->name,
                'repeated' => $reasonLeaveDay->repeated,
                'unit' => Lang::get('manage_time::view.' . $reasonLeaveDay->unit),
            ]),
        ]);
    }

    /**
     * Get the rules for the defined validation.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'approver' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'reason' => 'required'
        ];
        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        $messages = [
            'approver.required' => Lang::get('manage_time::view.The approver field is required'),
            'start_date.required' => Lang::get('manage_time::view.The start date field is required'),
            'end_date.required' => Lang::get('manage_time::view.The end date field is required'),
            'reason.required' => Lang::get('manage_time::view.The leave day type field is required'),
        ];

        return $messages;
    }

    /**
     * Get approver for register
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function ajaxGetApprover(Request $request)
    {
        $employeeId = $request->get('employeeId');
        $params = [
            'approversList' => ManageTimeCommon::getApproversForEmployee($employeeId),
        ];
        $view = 'manage_time::supplement.opption_approver';
        $html = view($view)->with($params)->render();
        $htmlLeaveDay = view('manage_time::leave.leave_day_information')->with(['informationLeaveDay' => LeaveDay::getInformationLeaveDayOfEmp($employeeId)])->render();
        $teamCodePre = Team::getOnlyOneTeamCodePrefix($employeeId);
        $objWTView = new WorkingTimeView();
        $period = [
            'start_date' => Carbon::parse($request->get('start_at'))->toDateString(),
            'end_date' => Carbon::parse($request->get('end_at'))->toDateString(),
        ];
        $workingTime = $objWTView->getWorkingTimeByEmployeeBetween($employeeId, $teamCodePre, $period);
        $timeSetting = $workingTime['timeSetting'];
        $timeWorkingQuater = $workingTime['timeWorkingQuater'];

        return response()->json([
            'html' => $html,
            'htmlLeaveDay' => $htmlLeaveDay,
            'empSelected' => \Rikkei\Team\Model\Employee::getEmpById($employeeId),
            'regsUnapporve' => ManageTimeView::dayOffUnapprove($employeeId),
            'timeSettingNew' => $timeSetting,
            'timeWorkingQuater' => $timeWorkingQuater,
        ]);
    }

    /**
     * get time setting ajax
     * register leave day
     * @param  Request $request
     * @return [type]
     */
    public function getTimeSetting(Request $request)
    {
        $dateKey = $request->get('dateKey');
        $period = $request->get('period');
        $empId = $request->get('empId');

        $resourceView = new ResourceView();
        $objWTView = new WorkingTimeView();
        $teamCodePre = Team::getOnlyOneTeamCodePrefixChange($empId);

        /* valid param period */
        if (isset($period['start_date']) && isset($period['end_date']) && $period['start_date'] <= $period['end_date']
            && $resourceView->checkIsDate($period['start_date']) && $resourceView->checkIsDate($period['end_date'])) {
            list ($timeSetting, $timeWorkingQuarter) = $objWTView->getEmpWorkingTimeInPeriod($empId, $teamCodePre, $period);
            return response()->json(['timeWorking' => $timeSetting, 'timeQuater' => $timeWorkingQuarter]);
        }

        $workingTime = $objWTView->getWorkingTimeByEmployeeDate($empId, $teamCodePre, $dateKey);
        return Response()->json([
            'timeWorking' => $workingTime['timeSetting'],
            'timeQuater' => $workingTime['timeWorkingQuater'],
        ]);
    }

    /**
     * calculation again time register leave
     * @param  [string||carbon] $startDate
     * @param  [string||carbon] $endDate
     * @param  [collection] $userCurrent
     * @param  [float] $numberValidate
     * @return [boolean]
     */
    public static function checkTimeRegister($request, $userCurrent)
    {
        //calculate_full_day luôn trả về 1 lỗi cần fix - return để fix nhanh
        return true;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $numberValidate = $request->number_validate;
        $calculateFullDay = $request->calculate_full_day;

        $number = ManageLeaveDay::getTimeLeaveDay($startDate, $endDate, $userCurrent, null, $calculateFullDay);

        if ($numberValidate != $number) {
            return false;
        }
        return true;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxGetLeaveDayReason(Request $request)
    {
        $employeeId = $request->input('employee_id');

        $teamCodePre = Team::getOnlyOneTeamCodePrefixChange($employeeId);
        $leaveDayReason = LeaveDayReason::getLeaveDayReasons($teamCodePre);

        return response()->json($leaveDayReason->toArray());
    }

    public function ajaxGetLeaveDayRelation(Request $request)
    {
        $employeeId = $request->input('employee_id');
        $getRelation = EmployeeRelationship::getListRelationEmpNotDie($employeeId);

        return response()->json($getRelation);
    }

    /**
     * @param $timeWorking
     * @param $date
     * @return mixed
     */
    private function getTimeWorkingOfMonth($timeWorking, $date)
    {
        return array_first($timeWorking, function ($key, $value) use ($date) {
            return ($value->from_month <= $date && $value->to_month >= $date);
        });
    }

    /**
     * Check the type of leave reason for the limited number of registration times
     * @param $request
     * @param $emId
     * @return array
     */
    public function checkLeaveReason($request, $emId)
    {
        $data = [];
        $idReason = $request->reason;
        $objLReason = new LeaveDayReason();
        $only1Time = $objLReason->getLeaveReasonOnlyOnetime();
        if (count($only1Time)) {
            $idOnly1Time = $only1Time->lists('id')->toArray();
            if (in_array($idReason, $idOnly1Time)) {
                $build = LeaveDayRegister::where('creator_id', $emId)->where('reason_id', $idReason);
                if (isset($request->register_id)) {
                    $build->where('id', '!=', $request->register_id);
                }
                $leave = $build->first();
                if ($leave) {
                    foreach ($only1Time as $item) {
                        if ($idReason == $item->id) {
                           $data['message'] = Lang::get('manage_time::message.Type of leave :type register only one time. Duplicate id :id', [
                               'type' => $item->name,
                               'id' => $leave->id,
                           ]);
                           break;
                        }
                    }
                    return $data;
                }
            }
        }
        $only1TimeMonth = $objLReason->getLeaveReasonOnlyOnetimeMonth();
        if (count($only1TimeMonth)) {
            $idOnly1TimeMonth = $only1TimeMonth->lists('id')->toArray();
            if (in_array($idReason, $idOnly1TimeMonth)) {
                $startDate =  Carbon::parse($request->start_date)->firstOfMonth()->format('Y-m-d');
                $endDate =  Carbon::parse($request->end_date)->lastOfMonth()->format('Y-m-d');
                $build = LeaveDayRegister::where('creator_id', $emId)
                    ->where('reason_id', $idReason)
                    ->where(function ($query) use ($startDate, $endDate) {
                        $query->where(function ($query1) use ($startDate, $endDate) {
                            $query1->whereDate('date_start', '>=', $startDate)
                                ->whereDate('date_start', '<=', $endDate);
                        });
                        $query->orWhere(function ($query2) use ($startDate, $endDate) {
                            $query2->whereDate('date_end', '>=', $startDate)
                                ->whereDate('date_end', '<=', $endDate);
                        });
                    });
                if (isset($request->register_id)) {
                    $build->where('id', '!=', $request->register_id);
                }
                $leave = $build->first();
            }
        }

        if (LeaveDayReason::checkReasonTeamType($request->reason)) {
            $getLeaveDayMemberRela = LeaveDayRelationMember::getMemberRela($request->employee_relationship);
            if ($getLeaveDayMemberRela) {
                foreach($getLeaveDayMemberRela as $val) {
                    if ($val->status == LeaveDayRegister::STATUS_UNAPPROVE) {
                        $data['message'] = Lang::get('manage_time::message.Relation member :memberId register only one time. Duplicate id :id', [
                            'memberId' => $val->employee_relationship_id,
                            'id' => $val->leave_day_registers_id,
                        ]);
                        break;
                    }
                }
                return $data;
            }
        }

        //check leave day obon - japan
        if (!count($data)) {
            $data = $this->checkLeaveDayObon($request, $emId);
        }

        return $data;
    }

    /**
     * checkLeaveDayObon
     *
     * @param  request $request
     * @param  int $emId
     * @return array
     */
    public function checkLeaveDayObon($request, $emId)
    {
        $idReason = $request->reason;
        $objLReason = new LeaveDayReason();
        $data = [];
        $idRegister = $request->register_id;
        $employee = Employee::getEmpById($emId);

        $obons = $objLReason->getLeaveReasonOnlyByName(LeaveDayReason::OBON);
        if (count($obons)) {
            $obon = $obons->first();
            if ($idReason == $obon->id) {
                $startDate =  Carbon::parse($request->start_date)->firstOfYear()->format('Y-m-d');
                $endDate =  Carbon::parse($request->end_date)->lastOfYear()->format('Y-m-d');

                $objLDRegister = new LeaveDayRegister();
                $regOnbon = $objLDRegister->getListRegisterByReasonEmpId($obon->id, [$emId], $startDate, $endDate);
                $arrObon = [];
                $arrRegId = [];
                foreach($regOnbon as $item) {
                    if (!empty($idRegister) && $idRegister == $item->id) {
                        continue;
                    }
                    $array = $this->calculateObon($item->date_start, $item->date_end, $employee);
                    foreach($array as $key => $value) {
                        $arrRegId[$key][] = $item->id;
                        if (isset($arrObon[$key])) {
                            $arrObon[$key] += $value;
                        } else {
                            $arrObon[$key] = $value;
                        }
                    }
                }
                $array = $this->calculateObon($request->start_date, $request->end_date, $employee);
                foreach($array as $key => $value) {
                    if (isset($arrObon[$key])) {
                        $arrObon[$key] += $value;
                    } else {
                        $arrObon[$key] = $value;
                    }
                }
                if ($arrObon) {
                    $str = '';
                    foreach($arrObon as $key => $value) {
                        if ($value > $obon->value) {
                            foreach($arrRegId[$key] as $vId) {
                                $str .= "<a href=" . route('manage_time::profile.leave.edit', ['id' => $vId]) . "> ". $vId ." </a> ";
                            }
                            $data['message'] =  Lang::get('manage_time::message.Total number of Obon types in year : year. Current application and applications registered: list have a larger number of days off :max', [
                                'list' => $str,
                                'year' => $key,
                                'max' => $obon->value,
                            ]);
                            break;
                        }
                    }
                }
            }
        }
        return $data;
    }

    /**
     * tinh ngay phep cua obon - japan
     *
     * @param  string $startDate
     * @param  string $endDate
     * @param  object $employee
     * @return array
     */
    public function calculateObon($startDate, $endDate, $employee = null)
    {
        $arrObon = [];
        $startDate =  Carbon::parse($startDate);
        $endDate =  Carbon::parse($endDate);

        if (!$employee) {
            $employee = Permission::getInstance()->getEmployee();
        }
        $workTimeStart = Employee::getTimeWorkEmployeeDate($startDate->format('Y-m-d'), $employee);
        $workTimeEnd = Employee::getTimeWorkEmployeeDate($endDate->format('Y-m-d'), $employee);
        $teamCodePrefix = Team::getOnlyOneTeamCodePrefix($employee);
        $time = 0;
        $dateStart = clone $startDate;
        $compensationDays = CoreConfigData::getCompensatoryDays($teamCodePrefix);

        while (strtotime($startDate->toDateString()) <= strtotime($endDate->toDateString())) {
            $isWeekend = ManageTimeCommon::isWeekend($startDate, $compensationDays);
            $isHoliday = ManageTimeCommon::isHoliday($startDate, null, null, $teamCodePrefix);

            if ((!$isWeekend && !$isHoliday)) {
                $time = 0;
                if ($dateStart->toDateString() == $startDate->toDateString() && $startDate->toDateString() == $endDate->toDateString()) {
                    if ($startDate->hour == $workTimeStart["morningInSetting"]->hour && $endDate->hour == $workTimeEnd["afternoonOutSetting"]->hour) {
                        $time = $time + 1;
                    } else {
                        $time = $time + 0.5;
                    }
                } elseif ($dateStart->toDateString() == $startDate->toDateString() && $startDate->toDateString() < $endDate->toDateString()) {
                    if ($startDate->hour > $workTimeStart["morningInSetting"]->hour) {
                        $time = $time + 0.5;
                    } else {
                        $time = $time + 1;
                    }
                } elseif ($dateStart->toDateString() < $startDate->toDateString() && $startDate->toDateString() < $endDate->toDateString()) {
                    $time = $time + 1;
                } else {
                    if ($endDate->hour == $workTimeEnd["afternoonOutSetting"]->hour) {
                        $time = $time + 1;
                    } else {
                        $time = $time + 0.5;
                    }
                }

                if (isset($arrObon[$startDate->year])) {
                    $arrObon[$startDate->year] += $time;
                } else {
                    $arrObon[$startDate->year] = $time;
                }
            }
            $startDate->addDay();
        }
        return $arrObon;
    }
    
    /**
     * insertTimekeeping
     *
     * @param  collection $userCurrent
     * @param  array $dataLeaveDayTK
     * @return void
     */
    public function insertTimekeeping($userCurrent, $dataLeaveDayTK)
    {
        $objView = new ManageTimeView();
        return $objView->insertTimekeeping($userCurrent, $dataLeaveDayTK);
    }

    /**
     * Compare the period of leave application time
     * @param  $joinDate join date
     * @param  $startDate start date
     * @param  $endDate end date
     * @return boolean
     */
    private function checkDifferentNendo($joinDate, $startDate, $endDate)
    {
        $joinDate = strtotime($joinDate);
        $yearChangeDate = [date('m', $joinDate), date('d', $joinDate)];
        $startNendoYear = self::getNendoYear($yearChangeDate, strtotime($startDate));
        $endNendoYear = self::getNendoYear($yearChangeDate, strtotime($endDate));

        // Applications cannot be made across the annual paid leave grant date.
        if ($startNendoYear != $endNendoYear) {
            return false;
        }
        return true;
    }

    /**
     * get nendo datetime
     * @param  $joinDate join date
     * @param  $startDate start date
     * @param  $endDate end date
     */
    private function getNendoDateTime($joinDate, $endDate) 
    {
        
        $yearChangeDate = [date('m', strtotime($joinDate)), date('d', strtotime($joinDate))];
        $endNendoYear = self::getNendoYear($yearChangeDate, strtotime($endDate));
        $nendoDate = $endNendoYear . '/' . date('m', strtotime($joinDate)) . '/' . date('d', strtotime($joinDate));
        return $nendoDate;
    }

    /**
     * 指定時刻からの年度取得
     * @param array $yearChangeDate 配列形式の月日
     * @param type $time 時刻（指定なしの場合、現在時刻）
     * @return type
     */
    private static function getNendoYear($yearChangeDate = null, $time = null)
    {
        if (empty($time)) {
            $time = time();
        }
        if (empty($yearChangeDate) || !is_array($yearChangeDate)) {
            $yearChangeDate = static::yearChangeDateArr();
        }
        $now_year = date('Y', $time);
        $now_month = date('n', $time);
        $now_day = date('j', $time);

        if($now_month < (int) $yearChangeDate[0]) {
            return ($now_year - 1);
        }

        if($now_month == (int) $yearChangeDate[0] && $now_day < (int) $yearChangeDate[1]) {
            return ($now_year - 1);
        }

        return $now_year;
    }

    /**
     * set grant date for employee japan
     * @param employee employee
     * @param compareDate
     * @return Array lastGrantDate nextGrantDate
     */
    public static function setGrantDateEmployeeJp($employee, $compareDate = null)
    {
        $joinDate = self::getPaidLeaveDateTimeEmployeeJapan($employee);

        $grantDate = LeaveDay::getGrantDateEmployeeJp($joinDate, $compareDate);
        $grantDate = [
            'last_grant_date' => $grantDate[0],
            'next_grant_date' => $grantDate[1]
        ];

        return $grantDate;
    }

    /**
     * set grant date for employee japan by nendo
     * @param employee employee
     * @param compareDate
     * @return Array lastGrantDate nextGrantDate
     */
    private static function setGrantDateEmployeeJpByNendo($employee, $compareDate = null)
    {
        $joinDate = self::getPaidLeaveDateTimeEmployeeJapan($employee);

        $grantDate = LeaveDay::getGrantDateEmployeeJpByNendo($joinDate, $compareDate);
        $grantDate = [
            'last_grant_date' => $grantDate[0],
            'next_grant_date' => $grantDate[1]
        ];

        return $grantDate;
    }

    /**
     * lấy ngày để bắt đầu tính thời gian cấp theo cho nhân viên Nhật Bản
     * @param $employee employee
     * @return $joinDate
     */
    private static function getPaidLeaveDateTimeEmployeeJapan($employee)
    {
        $joinDate = $employee->join_date;
        // Kiễm tra nhân viên có ở japan liên tục hay không ?  và lấy ngày join team japan đầu tiên và ở japan liên tục.
        list($flgJapan, $firstTeamStartAt) = LeaveDay::getJoinFistTeamInJapan($employee->id);

        // Nếu nhân viên từ VN sang hoặc có sự chuyển giữa VN-JP thì lấy ngày vào team japan và ở japan liên tục để tính phép.
        if( (!$flgJapan && $firstTeamStartAt != '') || !$employee->join_date){
            $joinDate = $firstTeamStartAt;
        }

        return $joinDate;
    }

    /**
     * [年次有給休暇取得状況一覧: view form 年次有給休暇取得状況一覧]
     * @return [view]
     */
    public function getAcquisitionStatus() 
    {
        $route = 'manage_time::profile.leave.acquisition-status';
        $urlFilter = route('manage_time::profile.leave.acquisition-status') . '/';
        $teamIds = Form::getFilterData('search', 'team_ids', $urlFilter);
        if (is_array($teamIds)) {
            $teamIds = array_filter(array_values($teamIds));
            $teamIds = implode($teamIds, ', ');
        }

        $filterSearchDate = Form::getFilterData('search', 'search_date', $urlFilter);
        $filterSearchYear = Form::getFilterData('search', 'search_year', $urlFilter);
        $filterSearchLessThan5Day = Form::getFilterData('search', 'is_less_than_5_day', $urlFilter);

        $today = new DateTime();
        $userCurrent = Permission::getInstance()->getEmployee();
        $teamIdsAvailable = null;
        $teamTreeAvailable = [];
        $teamIdCurrent = false;
        self::getTeamTreeAvailable($route, $teamTreeAvailable, $teamIdsAvailable, $teamIdCurrent, true);
        $teams = Team::getTeamOfEmployee($userCurrent->id);
        $teamsOptionAll = TeamList::toOption(null, true, false);
        // Tìm kiếm theo bộ phận
        $teamId = $teams[0]->id;
        if (count($teamIds) == 0) {
            $teamIds = $teamId;
        }

        $searchPattern = 0;
        if (!$filterSearchDate && !$filterSearchYear) 
        {
            $searchPattern = 1;
        } else if ($filterSearchDate) { // Nếu nhập ngày
            $searchPattern = 1;
        } else {
            $searchPattern = 2;
        }
        $employeeCode = Form::getFilterData('search', 'employee_code', $urlFilter);
        $employeeName = Form::getFilterData('search', 'employee_name', $urlFilter);
        $searchGrantDate = Form::getFilterData('search', 'search_grant_date', $urlFilter);

        $searchRequest = [
            'employee_code' => $employeeCode,
            'employee_name' => $employeeName,
            'is_less_than_5_day' => $filterSearchLessThan5Day,
            'search_grant_date' => $searchGrantDate,
            'search_pattern' => $searchPattern
        ];

        list($listEmployee, $pattern, $filterSearchDate, $filterSearchYear, $all_total_num_day_off_approved, $all_total_day)
            = self::getAnnualPaidLeave($filterSearchDate, $filterSearchYear, $teamIds, null, $searchRequest);

        $params = [
            'teamsOptionAll' => $teamsOptionAll,
            'teamIdCurrent' => $teamIds,
            'teamIdsAvailable' => $teamIdsAvailable,
            'teamTreeAvailable' => $teamTreeAvailable,
            'listEmployee' => $listEmployee,
            'searchDate' => $filterSearchDate,
            'searchYear' => $filterSearchYear,
            'filterSearchLessThan5Day' => $filterSearchLessThan5Day,
            'allTotalNumDayOffApproved' => $all_total_num_day_off_approved,
            'allTotalDay' => $all_total_day,
            'pattern' => $pattern,
            'employeeCode' => $employeeCode,
            'employeeName' => $employeeName,
            'searchGrantDate' => $searchGrantDate
        ];

        return view('manage_time::leave.acquisition_status', $params);
    }

    private function getTeamTreeAvailable($route, &$teamTreeAvailable, &$teamIdsAvailable, &$teamIdCurrent, $checkTeam = false)
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

    /**
     * [年次有給休暇取得状況一覧: view detail form 年次有給休暇取得状況一覧]
     * @param Request
     * * @return [view]
     */
    public function showPopupDetailAcquisitionStatus(Request $request)
    {
        $teamIds = $request->teamId;
        $filterSearchDate = $request->searchDate;
        $filterSearchYear = $request->searchYear;
        $arrEmployeeId = [$request->employeeId];

        list($listEmployee, $pattern, $filterSearchDate, $filterSearchYear, $all_total_num_day_off_approved, $all_total_day)
            = self::getAnnualPaidLeave($filterSearchDate, $filterSearchYear, $teamIds, $arrEmployeeId, false, true);

        $params = [
            'employee' => $listEmployee ? $listEmployee[0] : null,
            'searchDate' => $filterSearchDate,
            'searchYear' => $filterSearchYear,
            'allTotalNumDayOffApproved' => $all_total_num_day_off_approved,
            'allTotalDay' => $all_total_day,
        ];

        echo view('manage_time::include.modal.modal_view_leave_day_acquisition', $params);
    }

    /**
     * [年次有給休暇取得状況一覧: view detail form 年次有給休暇取得状況一覧]
     * @param Request
     * * @return [view]
     */
    public function getAcquisitionStatusDetail(Request $request)
    {
        // lấy dữ liệu truyền lên từ request
        $teamIds = $request->teamId;
        $filterSearchDate = $request->date;
        $filterSearchYear = null;
        $arrEmployeeId = [$request->id];
        $listEmployee = null;
        $all_total_num_day_off_approved = 0;
        $all_total_day = 0;
        if (strtotime($filterSearchDate)) {
            $searchRequest = [
                'employee_code' => '',
                'employee_name' => '',
                'is_less_than_5_day' => false,
                'search_grant_date' => '',
                'search_pattern' => ''
            ];
    
            list($listEmployee, $pattern, $filterSearchDate, $filterSearchYear, $all_total_num_day_off_approved, $all_total_day)
                = self::getAnnualPaidLeave($filterSearchDate, $filterSearchYear, $teamIds, $arrEmployeeId, $searchRequest, true);
        }
        
        $params = [
            'employee' => $listEmployee ? $listEmployee[0] : null,
            'searchDate' => $filterSearchDate,
            'searchYear' => $filterSearchYear,
            'allTotalNumDayOffApproved' => $all_total_num_day_off_approved,
            'allTotalDay' => $all_total_day,
        ];

        return view('manage_time::leave.acquisition_status_detail', $params);
    }

    /**
     * Lấy thông tin nghỉ phép của danh sách nhân viên theo thời gian tìm kiếm
     * @param filterSearchDate Đầu vào ngày tìm kiếm
     * @param filterSearchYear Đầu vào năm tìm kiếm
     * @param teamIds Mảng danh sách teams.id
     * @param arrEmployeeId Mảng danh sách employee.id
     * @param searchRequest cờ đánh dấu chỉ hiển thị nhân nghỉ phép dưới 5 ngày trong kỳ hay không
     * @param isGetComment cờ đánh lấy lý do từ chối duyệt đơn hay không
     * @return Array danh sách nhân viên sau cùng thu được khi xử lý dữ liệu
     *               tổng số ngày nghỉ có phép của nhân viên trong cả kỳ cấp phép
     */
    private static function getAnnualPaidLeave($filterSearchDate, $filterSearchYear, $teamIds, $arrEmployeeId, $searchRequest, $isGetComment = null)
    {
        $pattern = 0;
        // Tìm kiếm theo thời gian cụ thể
        $searchDate = new DateTime();
        $searchDate = $searchDate->format('Y/m/d');
        $today = new DateTime();
        // Hiển thị mặc định sẽ là tìm theo ngày hiện tại
        if (!$filterSearchDate && !$filterSearchYear) 
        {
            $filterSearchDate = $searchDate;
            $pattern = 1;
            $filterSearchYear = $today->format('Y');
        } else if ($filterSearchDate) { // Nếu nhập ngày
            $pattern = 1;
            $searchDate = $filterSearchDate; 
            $filterSearchYear = $today->format('Y');
        } else { // Nếu nhập năm để tìm kiếm
            // Khi thực hiện tìm kiếm theo năm, thì ngày tìm kiếm sẽ là ngày cuối cùng của năm
            $filterSearchDate = $searchDate;
            $tmpSearchDate = $filterSearchYear . '/12/31';
            $searchDate = $tmpSearchDate;
            $pattern = 2;
        }

        // Do ngày cấp phép có khoảng thời gian lớn nhất ( trước và sau ngày tìm kiếm ) là 1 năm 6 tháng
        // Để tránh query theo từng nhân viên thì sẽ thực hiện query trong vòng 3 năm ( 1 năm 6 tháng trước ngày tìm kiếm và 1 năm 6 tháng sau ngày tìm kiếm )
        // Ex: ngày tìm kiếm là 2020-09-06 thì sẽ lấy tất cả dữ liệu trong khoảng thời gian 2019-09-06 đến 2021-09-06
        // Sau khi lấy được danh sách nhân viên trong vòng 3 năm thì sẽ tiến hành xóa bỏ những nhân viên không thuộc phạm vi tại thời điểm tìm kiếm
        // Với nhân viên làm việc trên 1 năm 6 tháng thì tìm kiếm trong khoảng thời gian 1 năm 6 tháng trước sẽ đúng
        // Với nhân viên chưa làm việc đủ 1 năm ( 0, 2, 4, 6 tháng ) so với ngày tìm kiếm, thì vẫn thực hiện tìm kiếm 1 năm trước
            // Nhưng kết quả chỉ ra của 6 tháng gần nhất. Do đó kết quả khi thực hiện so sánh để hiển thị ra vẫn sẽ đúng
  
        $searchStartDate = $searchDate;
        $searchEndDate = $searchDate;
        // Thêm 1 năm 6 tháng kể từ ngày tìm kiếm
        $endDate = Carbon::parse($searchEndDate)->addYear(+1)->addMonth(6)->format('Y/m/d');
        // Trừ 1 năm 6 tháng kể từ ngày tìm kiếm
        $startDate = Carbon::parse($searchStartDate)->addYear(-2)->addMonth(6)->format('Y/m/d');
        // Khởi tạo mảng trung gian danh sách nhân viên lấy được trong khoảng thời gian tìm kiếm
        $tmpListEmployee = LeaveDay::getAllEmployeeJapanWorkingByDateTime($searchDate, $teamIds, $arrEmployeeId, $searchRequest);

        // Khởi tạo mảng employee.id để query lấy dữ liệu ở bảng leave_day_baseline
        $arrEmployeeId = [];
        forEach($tmpListEmployee as $key => $employee) 
        {
            array_push($arrEmployeeId, $employee->id);
            // Lấy ra ngày cấp phép gần nhất và ngày cấp phép sắp tới tương ứng với ngày thực hiện tìm kiếm
            $grantDate = self::setGrantDateEmployeeJpByNendo($employee, $searchDate);
            $tmpListEmployee[$key]['last_grant_date'] = $grantDate['last_grant_date'];
            $tmpListEmployee[$key]['next_grant_date'] = $grantDate['next_grant_date'];
            $tmpListEmployee[$key]['manage_time_comments'] = [];
        }
        // Khởi tạo mảng để lấy ra danh sách các tháng trong khoảng giữa thời gian 3 năm từ ngày bắt đầu đến ngày kết thúc tìm kiếm
        $arrMonth = [];
        $arrMonth = self::getListOfMonthBetweenTwoDate($startDate, $endDate);

        // Thực hiện lấy dữ liệu ở bảng leave_day_baseline tương ứng với mảng nhân viên và mảng các tháng cần thu thập dữ liệu
        $leaveDayBaseLine = LeaveDayBaseline::getLeaveDayBaseLineByFilterData($arrEmployeeId, $arrMonth);

        // Khởi tạo mảng nhân viên để hiển thị ra màn hình
        $listEmployee = [];
        // Tổng số ngày được nghỉ của tất cả nhân viên
        $all_total_day = 0;
        // Tổng số ngày đã nghỉcủa tất cả nhân viên
        $all_total_num_day_off_approved = 0;

        // Bắt đầu gán dữ liệu của bảng leave_day_baseline cho từng nhân viên
        forEach($tmpListEmployee as $key => $employee) 
        {
            $tmpListEmployee[$key]['leave_day_baseline'] = [];
            // Tổng số ngày nghỉ của tất cả nhân viên trong kỳ
            $tmpListEmployee[$key]['all_total_day'] = 0;
            $tmpLeaveDayBaseLine = [];
            // Lấy ra danh sách các tháng trong khoảng thời gian cấp ngày nghỉ
            $arrSearchMonth = self::getListOfMonthBetweenTwoDate($tmpListEmployee[$key]['last_grant_date'], $tmpListEmployee[$key]['next_grant_date']);
            // Lặp dữ liệu của bảng leave_day_baseline để xóa đi các tháng không nằm trong phạm vi thời gian cấp ngày nghỉ 
            forEach($leaveDayBaseLine as $keyLeaveDayBaseLine => $valLeaveDayBaseLine) 
            {
                // Chỉ gán dữ liệu leave_day_baseline tìm được nếu leave_day_baseline.month là các tháng nằm trong phạm vi thời gian cấp ngày nghỉ
                if ($valLeaveDayBaseLine->employee_id == $employee->id && in_array($valLeaveDayBaseLine->month, $arrSearchMonth)) 
                {
                    // Nếu tìm kiếm theo ngày
                    if ($pattern == 1) {
                        // Chỉ lấy ra những nhân viên có ngày tháng tìm kiếm trong khoảng thời gian cấp thêm ngày phép và chưa nghỉ việc
                        if (in_array(Carbon::parse($searchStartDate)->format('Y-m'), $arrSearchMonth)
                            && Carbon::parse($searchStartDate) <= Carbon::parse($employee->leave_date)
                            && Carbon::parse($searchStartDate) >= $employee->last_grant_date) {
                            array_push($tmpLeaveDayBaseLine, $valLeaveDayBaseLine);
                        } else if (Carbon::parse($searchStartDate)->format('m-d') == '12-31') { // TH ngày nhập là ngày cuối cùng của năm
                            // Lấy ra tất cả nhân viên có tháng cấp thêm ngày phép nằm trong năm tìm kiếm
                            if (in_array(Carbon::parse($searchStartDate)->format('Y-m'), $arrSearchMonth)) {
                                array_push($tmpLeaveDayBaseLine, $valLeaveDayBaseLine);
                            }
                        }
                    } else { // Nếu tìm kiếm theo năm
                        // TH tìm theo năm thì chỉ xuất ra những nhân viên làm việc trên 6 tháng từ ngày vào công ty đến ngày cuối cùng của năm tìm kiếm
                        // dữ liệu ở TH dưới 6 tháng sẽ tính là của năm tiếp theo
                        // Ex : nhân viên vào ngày 2019/07/15 thì search năm 2019 sẽ không hiển thị, search năm 2020 sẽ hiển thị dữ liệu của cả năm 2019
                        $startAt = Carbon::parse($employee->join_date);
                        $nextAt = clone $startAt;
                        $workingMonths = Carbon::parse($searchStartDate)->diffInMonths($startAt);
                        $next6Month = $startAt->addMonth(6)->format('Y-m-d');
                        if ($workingMonths == 6) {
                            if (Carbon::parse($nextAt)->format('Y') == Carbon::parse($next6Month)->format('Y')) {
                                if (in_array(Carbon::parse($searchStartDate)->format('Y-m'), $arrSearchMonth)) {
                                    array_push($tmpLeaveDayBaseLine, $valLeaveDayBaseLine);
                                }
                            }
                        } else if ($workingMonths > 6) {
                            if (in_array(Carbon::parse($searchStartDate)->format('Y-m'), $arrSearchMonth)) {
                                array_push($tmpLeaveDayBaseLine, $valLeaveDayBaseLine);
                            }
                        }
                    }
                }
            }

            // TH mà không có dữ liệu trong bảng leave_day_baseline thì có nghĩa là nhân viên đó sẽ không ở trong kỳ cấp ngày nghỉ phép 
            // Nên chỉ thêm vào mảng danh sách nhân viên hiển thị ra màn hình đối với những nhân viên mà có dữ liệu trong bảng leave_day_baseline
            if (count($tmpLeaveDayBaseLine) > 0)
            {
                // Do dữ liệu ở bảng leave_day_baseline lưu theo tháng, nên nếu lấy theo tháng gần nhất sẽ có TH không đúng khi NV vào làm việc giữa tháng
                // Nên sẽ lấy dữ liệu theo tháng năm trong khoảng thời gian tìm kiếm
                if (count($tmpLeaveDayBaseLine) > 1) {
                    $tmpListEmployee[$key]['leave_day_baseline'] = $tmpLeaveDayBaseLine[count($tmpLeaveDayBaseLine) - 2];
                } else {
                    $tmpListEmployee[$key]['leave_day_baseline'] = $tmpLeaveDayBaseLine[count($tmpLeaveDayBaseLine) - 1];
                }

                list($listEmployee) = self::calcNumDay($employee, $tmpListEmployee, $key, $listEmployee, $grantDate, $searchRequest, $isGetComment);
            }
            else 
            {
                $today = new DateTime();
                $currentYearMonth = $today->format("Y-m");
                // Đối với những nhân viên mới vào làm chưa được 1 tháng thì dữ liệu ở bảng leave_day_baseline sẽ chưa có
                // Nên sẽ tìm kiếm thông tin ngày nghỉ theo bảng leave_days
                if (in_array($currentYearMonth, $arrSearchMonth))
                {
                    $leaveDayInfo = LeaveDay::getLeaveDayById($employee->id);
                    if ($leaveDayInfo) 
                    {
                        $tmpListEmployee[$key]['leave_day_baseline'] = [
                            'day_last_year' => $leaveDayInfo->day_last_year,
                            'day_last_transfer' => $leaveDayInfo->day_last_transfer,
                            'day_current_year' => $leaveDayInfo->day_current_year,
                            'day_seniority' => $leaveDayInfo->day_seniority,
                            'day_ot' => $leaveDayInfo->day_ot,
                        ];
                        list($listEmployee) = self::calcNumDay($employee, $tmpListEmployee, $key, $listEmployee, $grantDate, $searchRequest, $isGetComment);
                    } else {
                        if (Carbon::parse($employee->join_date)->format('Y-m') == $currentYearMonth && 
                            Carbon::parse($searchStartDate) <= Carbon::parse($employee->leave_date)) {
                            $tmpListEmployee[$key]['leave_day_baseline'] = [
                                'day_last_year' => 0,
                                'day_last_transfer' => 0,
                                'day_current_year' => 0,
                                'day_seniority' => 0,
                                'day_ot' => 0,
                            ];
                            list($listEmployee) = self::calcNumDay($employee, $tmpListEmployee, $key, $listEmployee, $grantDate, $searchRequest, $isGetComment);
                        }
                    }
                }
            }
        }

        forEach($listEmployee as $key => $employee) {
            $all_total_day += ($employee['leave_day_baseline']['day_current_year'] + $employee['leave_day_baseline']['day_seniority']);
            $all_total_num_day_off_approved += $employee['total_num_day_off_approved'];
        }

        return [$listEmployee, $pattern, $filterSearchDate, $filterSearchYear, $all_total_num_day_off_approved, $all_total_day];
    }

    /**
     * tính toán dữ liệu liên quan đến ngày nghỉ của nhân viên
     * @param employee nhân viên cần xử lý dữ liệu hiển thị
     * @param tmpListEmployee danh sách nhân viên tạm để xử lý dữ liệu 
     * @param index chỉ mục danh sách nhân viên
     * @param listEmployee danh sách nhân viên sau cùng thu được khi xử lý dữ liệu
     * @param grantDate giới hạn thời gian cấp nghỉ phép trong kỳ của nhân viên tương ứng
     * @param Array searchRequest  điều kiện tìm kiếm
     * @param isGetComment cờ đánh lấy lý do từ chối duyệt đơn hay không
     * @return Array danh sách nhân viên sau cùng thu được khi xử lý dữ liệu
     *               tổng số ngày nghỉ có phép của nhân viên trong cả kỳ cấp phép
     */   
    private static function calcNumDay($employee, $tmpListEmployee, $index, $listEmployee, $grantDate, $searchRequest, $isGetComment)
    {
        $leaveDayRegister = new LeaveDayRegister();
        // Lấy dữ liệu ở bảng leave_day_registers dựa theo thời gian cấp phép
        $leaveDayRegister = $leaveDayRegister->getListRegisterByReasonEmpId(LeaveDayReason::REASON_PAID_LEAVE_JA,
            [$employee->id], $employee['last_grant_date'], $employee['next_grant_date'],
            [LeaveDayRegister::STATUS_APPROVED, LeaveDayRegister::STATUS_UNAPPROVE, LeaveDayRegister::STATUS_DISAPPROVE]);
        $total_num_day_off_approved = 0;
        $total_num_day_off_unapproved = 0;
        // Đối với dữ liệu trong quá khứ thì việc đăng ký xin nghỉ trong cùng 1 năm quản lý vẫn thành công
        // Nên sẽ đánh dấu để hiển thị ra chữ màu đỏ ở màn hình danh sách
        $isIncludeOtherYear = false;
        $manage_time_comments = [];
        $total_num_day_off = 0;
        forEach($leaveDayRegister as $keyLeaveDayRegister => $valLeaveDayRegister)
        {
            if ($valLeaveDayRegister->status == LeaveDayRegister::STATUS_APPROVED)
            {
                $total_num_day_off_approved += $valLeaveDayRegister->number_days_off;
                $isInFuture = false;
                if (Carbon::now()->format('Y-m-d') < Carbon::parse($valLeaveDayRegister->date_start)->format('Y-m-d'))
                {
                    $isInFuture = true;
                }
                if (!$isInFuture) {
                    $total_num_day_off += $valLeaveDayRegister->number_days_off;
                }
            }
            else if ($valLeaveDayRegister->status == LeaveDayRegister::STATUS_UNAPPROVE)
            {
                $total_num_day_off_unapproved += $valLeaveDayRegister->number_days_off;
            }
            $grantDate = [
                'last_grant_date' => $tmpListEmployee[$index]['last_grant_date'],
                'next_grant_date' => $tmpListEmployee[$index]['next_grant_date']
            ];
            // Tính toán xem dữ liệu ngày nghỉ phép có bao gồm cả ở trong kỳ cấp phép khác hay không
            $isIncludeOtherYear = LeaveDayRegister::checkLeaveDayRegisterGrantDateOtherYear(Carbon::parse($valLeaveDayRegister->date_start)->format('Y-m-d'), Carbon::parse($valLeaveDayRegister->date_end)->format('Y-m-d'), $grantDate);

            if ($isGetComment) 
            {
                $commentsList = ManageTimeComment::getReasonDisapprove($valLeaveDayRegister->id, ManageTimeConst::TYPE_LEAVE_DAY);
                if (count($commentsList)) {
                    array_push($manage_time_comments, $commentsList[0]);
                }
            }
        }
        $tmpListEmployee[$index]['manage_time_comments'] = $manage_time_comments;
        $tmpListEmployee[$index]['leave_day_registers'] = $leaveDayRegister;  
        $tmpListEmployee[$index]['total_num_day_off_approved'] = $total_num_day_off_approved;
        $tmpListEmployee[$index]['total_num_day_off_unapproved'] = $total_num_day_off_unapproved;
        $tmpListEmployee[$index]['total_num_day_off'] = $total_num_day_off;
        $tmpListEmployee[$index]['is_include_other_year'] = $isIncludeOtherYear;
        $tmpListEmployee[$index]['total_day'] = $tmpListEmployee[$index]['leave_day_baseline']['day_last_transfer']
            + $tmpListEmployee[$index]['leave_day_baseline']['day_current_year'] + $tmpListEmployee[$index]['leave_day_baseline']['day_seniority'];
        $tmpListEmployee[$index]['remain_day'] = $tmpListEmployee[$index]['total_day'] - $total_num_day_off_approved;
        // TH tìm kiếm theo 次回付与⽇
        if ($searchRequest['search_grant_date']) {
            $search_grant_date = strtotime($searchRequest['search_grant_date']);
            if ($search_grant_date) {
                $startAt = Carbon::parse($tmpListEmployee[$index]['next_grant_date']);
                $nextGrantDate = $startAt->addDays(1)->format('Y-m-d');
                if(Carbon::parse($nextGrantDate) <= Carbon::parse($searchRequest['search_grant_date'])) {
                    // Trường hợp chọn chỉ hiển thị những nhân viên nghỉ phép ít hơn 5 ngày
                    $filterSearchLessThan5Day = $searchRequest['is_less_than_5_day'];
                    if ($filterSearchLessThan5Day)
                    {
                        if ($total_num_day_off_approved < 5) 
                        {
                            array_push($listEmployee, $tmpListEmployee[$index]);
                        }
                    }
                    else 
                    {
                        array_push($listEmployee, $tmpListEmployee[$index]);
                    }
                }
            }
        } else {
            // Trường hợp chọn chỉ hiển thị những nhân viên nghỉ phép ít hơn 5 ngày
            $filterSearchLessThan5Day = $searchRequest['is_less_than_5_day'];
            if ($filterSearchLessThan5Day)
            {
                if ($total_num_day_off_approved < 5) 
                {
                    array_push($listEmployee, $tmpListEmployee[$index]);
                } else {
                    $isAddEmployee = false;
                }
            }
            else 
            {
                array_push($listEmployee, $tmpListEmployee[$index]);
            }
        }
        return [$listEmployee];
    }

    /**
     * get list of year-month between two date
     * @param startDate
     * @param endDate
     * @return Array list of year-month
     */
    private static function getListOfMonthBetweenTwoDate($startdate, $endDate)
    {
        $today = new DateTime();
        $currentYearMonth = $today->format("Y-m");
        $start    = new DateTime($startdate);
        $start->modify('first day of this month');
        $end      = new DateTime($endDate);
        $end->modify('first day of next month');
        $interval = DateInterval::createFromDateString('1 month');
        $period   = new DatePeriod($start, $interval, $end);
        $arrMonth = [];
        foreach ($period as $dt) {
            // Chỉ lấy ra những tháng nhỏ hơn hoặc bằng so với tháng hiện tại
            $yearMonth = $dt->format("Y-m");
            if ($yearMonth <= $currentYearMonth)
            {
                array_push($arrMonth, $dt->format("Y-m"));
            }
        }

        return $arrMonth;
    }

    /**
     * comment do tạm thời không sử dụng đến
     * export excel 年次有給休暇取得状況一覧
     * @param Request request
     */
    // public function exportExcelAcquisitionStatus(Request $request)
    // {
    //     // lấy dữ liệu truyền lên từ request
    //     $teamIds = $request->teamId;
    //     $filterSearchDate = $request->searchDate;
    //     $filterSearchYear = $request->searchYear;
    //     $arrEmployeeId = [$request->employeeId];
    //     $pattern = $request->pattern;
    //     if ($pattern == 1) {
    //         $filterSearchYear = null;
    //     } else {
    //         $filterSearchDate = null;
    //     }
    //     list($listEmployee, $pattern, $filterSearchDate, $filterSearchYear, $all_total_num_day_off_approved, $all_total_day)
    //         = self::getAnnualPaidLeave($filterSearchDate, $filterSearchYear, $teamIds, $arrEmployeeId, false, true);
    //     $employee = $listEmployee ? $listEmployee[0] : null;
    //     if ($employee)
    //     {
    //         $params = [
    //             'employee' => $listEmployee ? $listEmployee[0] : null,
    //             'searchDate' => $filterSearchDate,
    //             'searchYear' => $filterSearchYear,
    //             'allTotalNumDayOffApproved' => $all_total_num_day_off_approved,
    //             'allTotalDay' => $all_total_day,
    //         ];
    
    //         $fileName = $employee->employee_code . '_' . $employee->name . '_'
    //             . Carbon::parse($employee->last_grant_date)->format('Y-m-d') . '_'
    //             . Carbon::parse($employee->next_grant_date)->format('Y-m-d');
    //         //create excel file
    //         Excel::create($fileName, function ($excel) use ($employee) {
    //             $excel->setTitle($employee->employee_code . '_' . $employee->name . '_'
    //                 . Carbon::parse($employee->last_grant_date)->format('Y-m-d') . '_'
    //                 . Carbon::parse($employee->next_grant_date)->format('Y-m-d'));
    //             $excel->sheet($employee->employee_code ? $employee->employee_code : $employee->name, function ($sheet) use ($employee) {
    //                 $styleCell = array(
    //                     'borders' => array(
    //                         'allborders' => array(
    //                             'style' => \PHPExcel_Style_Border::BORDER_THIN
    //                         )
    //                     )
    //                 );
    //                 $styleCellRed = array(
    //                     'borders' => array(
    //                         'allborders' => array(
    //                             'style' => \PHPExcel_Style_Border::BORDER_THIN
    //                         )
    //                         ),
    //                     'font'  => array(
    //                         'color' => array('rgb' => 'FF0000'),
    //                     )
    //                 );
    //                 // Đặt width từ cột A đến cột CO trong file excel
    //                 for( $index = 0 ; $index <= 92 ; $index++) {
    //                     $col = \PHPExcel_Cell::stringFromColumnIndex($index);
    //                     $sheet->setWidth($col, 2.9);
    //                 }
    //                 $sheet->cells('A1', function ($cells) {
    //                     $cells->setValue('');
    //                 });
    //                 $sheet->mergeCells('B2:G2');
    //                 $sheet->getStyle("B2:G2")->applyFromArray($styleCell);
    //                 $sheet->cells('B2', function ($cells) {
    //                     $cells->setValue('従業員コード');
    //                     $cells->setFontWeight('bold');
    //                     $cells->setAlignment('left');
    //                     $cells->setValignment('center');
    //                 });

    //                 $sheet->mergeCells('H2:M2');
    //                 $sheet->getStyle("H2:M2")->applyFromArray($styleCell);
    //                 $sheet->cells('H2', function ($cells) use ($employee) {
    //                     $cells->setValue($employee->employee_code);
    //                     $cells->setAlignment('left');
    //                     $cells->setValignment('center');
    //                 });

    //                 $sheet->mergeCells('S2:U2');
    //                 $sheet->getStyle("S2:U2")->applyFromArray($styleCell);
    //                 $sheet->cells('S2', function ($cells) {
    //                     $cells->setValue('部⾨');
    //                     $cells->setFontWeight('bold');
    //                     $cells->setAlignment('left');
    //                     $cells->setValignment('center');
    //                 });

    //                 $sheet->mergeCells('V2:AC2');
    //                 $sheet->getStyle("V2:AC2")->applyFromArray($styleCell);
    //                 $sheet->cells('V2', function ($cells) use ($employee) {
    //                     $cells->setValue($employee->team_name);
    //                     $cells->setAlignment('left');
    //                     $cells->setValignment('center');
    //                 });

    //                 $sheet->mergeCells('AI2:AJ2');
    //                 $sheet->getStyle("AI2:AJ2")->applyFromArray($styleCell);
    //                 $sheet->cells('AI2', function ($cells) {
    //                     $cells->setValue('⽒名');
    //                     $cells->setFontWeight('bold');
    //                     $cells->setAlignment('left');
    //                     $cells->setValignment('center');
    //                 });

    //                 $sheet->mergeCells('AK2:AT2');
    //                 $sheet->getStyle("AK2:AT2")->applyFromArray($styleCell);
    //                 $sheet->cells('AK2', function ($cells) use ($employee) {
    //                     $cells->setValue($employee->last_grant_date ? Carbon::parse($employee->last_grant_date)->format('Y') : null);
    //                     $cells->setAlignment('left');
    //                     $cells->setValignment('center');
    //                 });

    //                 $sheet->mergeCells('B5:D5');
    //                 $sheet->getStyle("B5:D5")->applyFromArray($styleCell);
    //                 $sheet->cells('B5', function ($cells) {
    //                     $cells->setValue('⼊社⽇');
    //                     $cells->setFontWeight('bold');
    //                     $cells->setAlignment('left');
    //                     $cells->setValignment('center');
    //                 });

    //                 $sheet->mergeCells('E5:I5');
    //                 $sheet->getStyle("E5:I5")->applyFromArray($styleCell);
    //                 $sheet->cells('E5', function ($cells) use ($employee) {
    //                     $cells->setValue($employee->join_date ? Carbon::parse($employee->join_date)->format('Y-m-d') : null);
    //                     $cells->setAlignment('left');
    //                     $cells->setValignment('center');
    //                 });

    //                 $sheet->mergeCells('S5:AA5');
    //                 $sheet->getStyle("S5:AA5")->applyFromArray($styleCell);
    //                 $sheet->cells('S5', function ($cells) {
    //                     $cells->setValue('前期繰越⽇数');
    //                     $cells->setFontWeight('bold');
    //                     $cells->setAlignment('left');
    //                     $cells->setValignment('center');
    //                 });

    //                 $sheet->mergeCells('AB5:AC5');
    //                 $sheet->getStyle("AB5:AC5")->applyFromArray($styleCell);
    //                 $sheet->cells('AB5', function ($cells) {
    //                     $cells->setAlignment('left');
    //                     $cells->setValignment('center');
    //                 });
    //                 $sheet->setCellValueExplicit('AB5', number_format($employee->leave_day_baseline['day_last_transfer'], 2), \PHPExcel_Cell_DataType::TYPE_STRING);

    //                 $sheet->mergeCells('AI5:AR5');
    //                 $sheet->getStyle("AI5:AR5")->applyFromArray($styleCell);
    //                 $sheet->cells('AI5', function ($cells) {
    //                     $cells->setValue('今期取得合計⽇数');
    //                     $cells->setFontWeight('bold');
    //                     $cells->setAlignment('left');
    //                     $cells->setValignment('center');
    //                 });

    //                 $sheet->mergeCells('AS5:AT5');
    //                 $sheet->getStyle("AS5:AT5")->applyFromArray($styleCell);
    //                 $sheet->cells('AS5', function ($cells) {
    //                     $cells->setAlignment('left');
    //                     $cells->setValignment('center');
    //                 });
    //                 $sheet->setCellValueExplicit('AS5', number_format($employee->total_num_day_off_approved, 2), \PHPExcel_Cell_DataType::TYPE_STRING);
                    
    //                 $sheet->mergeCells('B6:D6');
    //                 $sheet->getStyle("B6:D6")->applyFromArray($styleCell);
    //                 $sheet->cells('B6', function ($cells) {
    //                     $cells->setValue('基準⽇');
    //                     $cells->setFontWeight('bold');
    //                     $cells->setAlignment('left');
    //                     $cells->setValignment('center');
    //                 });

    //                 $sheet->mergeCells('E6:I6');
    //                 $sheet->getStyle("E6:I6")->applyFromArray($styleCell);
    //                 $sheet->cells('E6', function ($cells) use ($employee) {
    //                     $cells->setValue($employee->join_date ? Carbon::parse($employee->last_grant_date)->format('Y-m-d') : null);
    //                     $cells->setAlignment('left');
    //                     $cells->setValignment('center');
    //                 });

    //                 $sheet->mergeCells('S6:AA6');
    //                 $sheet->getStyle('S6:AA6')->applyFromArray($styleCell);
    //                 $sheet->cells('S6', function ($cells) {
    //                     $cells->setValue('今期付与⽇数');
    //                     $cells->setFontWeight('bold');
    //                     $cells->setAlignment('left');
    //                     $cells->setValignment('center');
    //                 });

    //                 $sheet->mergeCells('AB6:AC6');
    //                 $sheet->getStyle("AB6:AC6")->applyFromArray($styleCell);
    //                 $sheet->cells('AB6', function ($cells) {
    //                     $cells->setAlignment('left');
    //                     $cells->setValignment('center');
    //                 });
    //                 $sheet->setCellValueExplicit('AB6', number_format($employee->leave_day_baseline['day_current_year'] + $employee->leave_day_baseline['day_seniority'], 2), \PHPExcel_Cell_DataType::TYPE_STRING);

    //                 $sheet->mergeCells('AI6:AR6');
    //                 $sheet->getStyle('AI6:AR6')->applyFromArray($styleCell);
    //                 $sheet->cells('AI6', function ($cells) {
    //                     $cells->setValue('年5⽇の時季指定残⽇数');
    //                     $cells->setFontWeight('bold');
    //                     $cells->setAlignment('left');
    //                     $cells->setValignment('center');
    //                 });

    //                 $minusNumDayOff = 0;
    //                 if ($employee->total_num_day_off_approved < 5) {
    //                     $minusNumDayOff = 5 - $employee->total_num_day_off_approved;
    //                 }
    //                 $sheet->mergeCells('AS6:AT6');
    //                 $sheet->getStyle("AS6:AT6")->applyFromArray($styleCell);
    //                 $sheet->cells('AS6', function ($cells) {
    //                     $cells->setAlignment('left');
    //                     $cells->setValignment('center');
    //                 });
    //                 $sheet->setCellValueExplicit('AS6', number_format($minusNumDayOff, 2), \PHPExcel_Cell_DataType::TYPE_STRING);

    //                 $sheet->mergeCells('S7:AA7');
    //                 $sheet->getStyle('S7:AA7')->applyFromArray($styleCell);
    //                 $sheet->cells('S7', function ($cells) {
    //                     $cells->setValue('合計⽇数');
    //                     $cells->setFontWeight('bold');
    //                     $cells->setAlignment('left');
    //                     $cells->setValignment('center');
    //                 });

    //                 $sheet->mergeCells('AB7:AC7');
    //                 $sheet->getStyle("AB7:AC7")->applyFromArray($styleCell);
    //                 $sheet->cells('AB7', function ($cells) {
    //                     $cells->setAlignment('left');
    //                     $cells->setValignment('center');
    //                 });
    //                 $sheet->setCellValueExplicit('AB7', number_format($employee->total_day, 2), \PHPExcel_Cell_DataType::TYPE_STRING);

    //                 $sheet->mergeCells('B10:C10');
    //                 $sheet->getStyle('B10:C10')->applyFromArray($styleCell);
    //                 $sheet->cells('B10', function ($cells) {
    //                     $cells->setValue('No');
    //                     $cells->setFontWeight('bold');
    //                     $cells->setAlignment('left');
    //                     $cells->setValignment('center');
    //                 });

    //                 $sheet->mergeCells('D10:J10');
    //                 $sheet->getStyle('D10:J10')->applyFromArray($styleCell);
    //                 $sheet->cells('D10', function ($cells) {
    //                     $cells->setValue('申請⽇');
    //                     $cells->setFontWeight('bold');
    //                     $cells->setAlignment('left');
    //                     $cells->setValignment('center');
    //                 });

    //                 $sheet->mergeCells('K10:Q10');
    //                 $sheet->getStyle('K10:Q10')->applyFromArray($styleCell);
    //                 $sheet->cells('K10', function ($cells) {
    //                     $cells->setValue('開始⽇時');
    //                     $cells->setFontWeight('bold');
    //                     $cells->setAlignment('left');
    //                     $cells->setValignment('center');
    //                 });

    //                 $sheet->mergeCells('R10:X10');
    //                 $sheet->getStyle('R10:X10')->applyFromArray($styleCell);
    //                 $sheet->cells('R10', function ($cells) {
    //                     $cells->setValue('終了⽇時');
    //                     $cells->setFontWeight('bold');
    //                     $cells->setAlignment('left');
    //                     $cells->setValignment('center');
    //                 });

    //                 $sheet->mergeCells('Y10:AE10');
    //                 $sheet->getStyle('Y10:AE10')->applyFromArray($styleCell);
    //                 $sheet->cells('Y10', function ($cells) {
    //                     $cells->setValue('有給休暇申請⽇数');
    //                     $cells->setFontWeight('bold');
    //                     $cells->setAlignment('left');
    //                     $cells->setValignment('center');
    //                 });

    //                 $sheet->mergeCells('AF10:AM10');
    //                 $sheet->getStyle('AF10:AM10')->applyFromArray($styleCell);
    //                 $sheet->cells('AF10', function ($cells) {
    //                     $cells->setValue('有給休暇取得⽇数');
    //                     $cells->setFontWeight('bold');
    //                     $cells->setAlignment('left');
    //                     $cells->setValignment('center');
    //                 });

    //                 $sheet->mergeCells('AN10:AS10');
    //                 $sheet->getStyle('AN10:AS10')->applyFromArray($styleCell);
    //                 $sheet->cells('AN10', function ($cells) {
    //                     $cells->setValue('有給休暇残⽇数');
    //                     $cells->setFontWeight('bold');
    //                     $cells->setAlignment('left');
    //                     $cells->setValignment('center');
    //                 });

    //                 $sheet->mergeCells('AT10:AX10');
    //                 $sheet->getStyle('AT10:AX10')->applyFromArray($styleCell);
    //                 $sheet->cells('AT10', function ($cells) {
    //                     $cells->setValue('状態');
    //                     $cells->setFontWeight('bold');
    //                     $cells->setAlignment('left');
    //                     $cells->setValignment('center');
    //                 });

    //                 $sheet->mergeCells('AY10:CE10');
    //                 $sheet->getStyle('AY10:CE10')->applyFromArray($styleCell);
    //                 $sheet->cells('AY10', function ($cells) {
    //                     $cells->setValue('時季変更理由');
    //                     $cells->setFontWeight('bold');
    //                     $cells->setAlignment('left');
    //                     $cells->setValignment('center');
    //                 });

    //                 if ($employee && $employee->leave_day_registers && count($employee->leave_day_registers) > 0) {
    //                     $rowIndex = 11;
    //                     $i = 0;
    //                     $remain_day = $employee->total_day;
    //                     $status = '';
    //                     $isCellRed = false;
    //                     $totalDayOff = 0;
    //                     foreach($employee->leave_day_registers as $leave_day_register) {
    //                         if ($leave_day_register->status == LeaveDayRegister::STATUS_APPROVED) {
    //                             $status = '承認済み';
    //                         } elseif ($leave_day_register->status == LeaveDayRegister::STATUS_DISAPPROVE) {
    //                             $status = '時季変更';
    //                         } else {
    //                             $status = '未承認';
    //                         }
    //                         $isIncludeOtherYear = false;
    //                         $grantDate = [
    //                             'last_grant_date' => $employee['last_grant_date'],
    //                             'next_grant_date' => $employee['next_grant_date']
    //                         ];
    //                         // Tính toán xem dữ liệu ngày nghỉ phép có bao gồm cả ở trong kỳ cấp phép khác hay không
    //                         $isIncludeOtherYear = LeaveDayRegister::checkLeaveDayRegisterGrantDateOtherYear($leave_day_register->date_start, $leave_day_register->date_end, $grantDate);

    //                         if ($isIncludeOtherYear)
    //                         {
    //                             $isCellRed = true;
    //                         }
    //                         $isInFuture = false;
    //                         if (Carbon::now()->format('Y-m-d') < Carbon::parse($leave_day_register->date_start)->format('Y-m-d'))
    //                         {
    //                             $isInFuture = true;
    //                         }
    //                         if ($leave_day_register->status == LeaveDayRegister::STATUS_APPROVED && !$isInFuture) 
    //                         {
    //                             $remain_day -= $leave_day_register->number_days_off;
    //                         }
    //                         $sheet->mergeCells('B' .$rowIndex .':C' . $rowIndex .'');
    //                         $sheet->getStyle('B' .$rowIndex .':C' . $rowIndex .'')->applyFromArray($isCellRed ? $styleCellRed : $styleCell);
    //                         $sheet->cells('B' .$rowIndex, function ($cells) use ($i) {
    //                             $cells->setValue($i + 1);
    //                             $cells->setAlignment('left');
    //                             $cells->setValignment('center');
    //                         });

    //                         $sheet->mergeCells('D' .$rowIndex .':J' . $rowIndex .'');
    //                         $sheet->getStyle('D' .$rowIndex .':J' . $rowIndex .'')->applyFromArray($isCellRed ? $styleCellRed : $styleCell);
    //                         $sheet->cells('D' .$rowIndex, function ($cells) use ($leave_day_register) {
    //                             $cells->setValue($leave_day_register->created_at ? Carbon::parse($leave_day_register->created_at)->format('Y-m-d H:i') : '');
    //                             $cells->setAlignment('left');
    //                             $cells->setValignment('center');
    //                         });

    //                         $sheet->mergeCells('K' .$rowIndex .':Q' . $rowIndex .'');
    //                         $sheet->getStyle('K' .$rowIndex .':Q' . $rowIndex .'')->applyFromArray($isCellRed ? $styleCellRed : $styleCell);
    //                         $sheet->cells('K' .$rowIndex, function ($cells) use ($leave_day_register) {
    //                             $cells->setValue($leave_day_register->date_start ? Carbon::parse($leave_day_register->date_start)->format('Y-m-d H:i') : '');
    //                             $cells->setAlignment('left');
    //                             $cells->setValignment('center');
    //                         });

    //                         $sheet->mergeCells('R' .$rowIndex .':X' . $rowIndex .'');
    //                         $sheet->getStyle('R' .$rowIndex .':X' . $rowIndex .'')->applyFromArray($isCellRed ? $styleCellRed : $styleCell);
    //                         $sheet->cells('R' .$rowIndex, function ($cells) use ($leave_day_register) {
    //                             $cells->setValue($leave_day_register->date_end ? Carbon::parse($leave_day_register->date_end)->format('Y-m-d H:i') : '');
    //                             $cells->setAlignment('left');
    //                             $cells->setValignment('center');
    //                         });

    //                         $sheet->mergeCells('Y' .$rowIndex .':AE' . $rowIndex .'');
    //                         $sheet->getStyle('Y' .$rowIndex .':AE' . $rowIndex .'')->applyFromArray($isCellRed ? $styleCellRed : $styleCell);
    //                         $sheet->cells('Y' .$rowIndex, function ($cells) {
    //                             $cells->setAlignment('left');
    //                             $cells->setValignment('center');
    //                         });
    //                         $sheet->setCellValueExplicit('Y' . $rowIndex, number_format($leave_day_register->number_days_off, 2), \PHPExcel_Cell_DataType::TYPE_STRING);

    //                         $sheet->mergeCells('AF' .$rowIndex .':AM' . $rowIndex .'');
    //                         $sheet->getStyle('AF' .$rowIndex .':AM' . $rowIndex .'')->applyFromArray($isCellRed ? $styleCellRed : $styleCell);
    //                         $sheet->cells('AF' .$rowIndex, function ($cells) {
    //                             $cells->setAlignment('left');
    //                             $cells->setValignment('center');
    //                         });
    //                         if ($leave_day_register->status == LeaveDayRegister::STATUS_APPROVED && !$isInFuture) {
    //                             $totalDayOff += $leave_day_register->number_days_off;
    //                         }
    //                         $sheet->setCellValueExplicit('AF' . $rowIndex, number_format($totalDayOff, 2), \PHPExcel_Cell_DataType::TYPE_STRING);
                            
    //                         $sheet->mergeCells('AN' .$rowIndex .':AS' . $rowIndex .'');
    //                         $sheet->getStyle('AN' .$rowIndex .':AS' . $rowIndex .'')->applyFromArray($isCellRed ? $styleCellRed : $styleCell);
    //                         $sheet->cells('AN' .$rowIndex, function ($cells) {
    //                             $cells->setAlignment('left');
    //                             $cells->setValignment('center');
    //                         });
    //                         $sheet->setCellValueExplicit('AN' . $rowIndex, number_format($remain_day, 2), \PHPExcel_Cell_DataType::TYPE_STRING);


    //                         $sheet->mergeCells('AT' .$rowIndex .':AX' . $rowIndex .'');
    //                         $sheet->getStyle('AT' .$rowIndex .':AX' . $rowIndex .'')->applyFromArray($isCellRed ? $styleCellRed : $styleCell);
    //                         $sheet->cells('AT' .$rowIndex, function ($cells) use ($status) {
    //                             $cells->setValue($status);
    //                             $cells->setAlignment('left');
    //                             $cells->setValignment('center');
    //                         });

    //                         $sheet->mergeCells('AY' .$rowIndex .':CE' . $rowIndex .'');
    //                         $sheet->getStyle('AY' .$rowIndex .':CE' . $rowIndex .'')->applyFromArray($isCellRed ? $styleCellRed : $styleCell);
    //                         if(isset($employee->manage_time_comments) && count($employee->manage_time_comments) && $employee->manage_time_comments[0]->register_id == $leave_day_register->id) {
    //                             $sheet->cells('AY' .$rowIndex, function ($cells) use ($employee) {
    //                                 $cells->setValue(nl2br($employee->manage_time_comments[0]->comment));
    //                                 $cells->setAlignment('left');
    //                                 $cells->setValignment('center');
    //                             });
    //                         }

    //                         $rowIndex++;
    //                         $i++;
    //                     }
    //                 } else {
    //                     $sheet->mergeCells('B11:CE11');
    //                     $sheet->getStyle('B11:CE11')->applyFromArray($styleCell);
    //                     $sheet->cells('B11', function ($cells) use ($employee) {
    //                         $cells->setValue('データなし');
    //                         $cells->setFontWeight('bold');
    //                         $cells->setAlignment('center');
    //                         $cells->setValignment('left');
    //                     });
    //                 }
    //             });
    //         })->export('xlsx');
    //     }
    // }

    /**
     * [editRegister: view form edit register]
     * @param  [int] $registerId
     * @return [view]
     */
    public function reapplyRegister($registerId)
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('Leave day');
        Menu::setActive('Profile');

        $userCurrent = Permission::getInstance()->getEmployee();
        $registerRecord = LeaveDayRegister::getInformationRegister($registerId);
        $getRelationMember = EmployeeRelationship::getRelationEmpByRegister($registerId);
        if (!$registerRecord) {
            return redirect()->route('manage_time::profile.leave.register-list')->withErrors(Lang::get('team::messages.Not found item.'));
        }

        if ($userCurrent->id != $registerRecord->creator_id && !LeaveDayPermission::allowCreateEditOther()) {
            View::viewErrorPermission();
        }

        $relatedPersonsList = LeaveDayRelater::getRelatedPersons($registerId);
        $commentsList = ManageTimeComment::getReasonDisapprove($registerId, ManageTimeConst::TYPE_LEAVE_DAY);
        $isAllowEdit = false;
        if ($registerRecord->status != LeaveDayRegister::STATUS_APPROVED && $registerRecord->status != LeaveDayRegister::STATUS_CANCEL) {
            $isAllowEdit = true;
        }
        $creator = Employee::getEmpById($registerRecord->creator_id);
        $teamCodePre = Team::getOnlyOneTeamCodePrefixChange($creator);
        $listLeaveDayReasons = LeaveDayReason::getLeaveDayReasons($teamCodePre);

        // Get working time this month of employee logged
        $objWTView = new WorkingTimeView();
        $period = [
            'start_date' => Carbon::parse($registerRecord->date_start)->toDateString(),
            'end_date' => Carbon::parse($registerRecord->date_end)->toDateString(),
        ];
        $workingTime = $objWTView->getWorkingTimeByEmployeeBetween($registerRecord->creator_id, $teamCodePre, $period);
        $timeSetting = $workingTime['timeSetting'];
        $timeWorkingQuater = $workingTime['timeWorkingQuater'];

        $groupEmail = LeaveDayGroupEmail::getGroupEmail($registerId);
        $groupEmailRegister = CoreConfigData::getGroupEmailRegisterLeave();
        $registerBranch = CoreConfigData::checkBranchRegister($creator, $teamCodePre);
        if (!empty($groupEmail)) {
            $groupEmailRegister = array_diff($groupEmailRegister, $groupEmail);
        }

        // Get attach files
        $attachmentsList = ManageTimeAttachment::getAttachments($registerId, ManageTimeConst::TYPE_LEAVE_DAY);
        $appendedFiles = [];
        foreach ($attachmentsList as $file) {
            $appendedFiles[] = [
                'name' => $file->file_name,
                'type' => $file->mime_type,
                'size' => $file->size,
                'file' => url($file->path),
                'data' => ['url' => url($file->path)]
            ];
        }

        $registerRecord->number_days_off = $registerRecord->used_leave_day == ManageTimeConst:: NOT_USED_LEAVE_DAY ? 0 : $registerRecord->number_days_off;
        $leaveDay = LeaveDay::getLeaveDayById($creator->id);
        $getRelation = EmployeeRelationship::getListRelationEmpNotDie($creator->id);
        // if employee type japan
        if ($teamCodePre == Team::CODE_PREFIX_JP) {
            $grantDate = self::setGrantDateEmployeeJp($creator);
        } else {
            $grantDate = [
                'last_grant_date' => '',
                'next_grant_date' => ''
            ];
        }

        $params = [
            'registerRecord' => $registerRecord,
            'relatedPersonsList' => $relatedPersonsList,
            'commentsList' => $commentsList,
            'listLeaveDayReasons' => $listLeaveDayReasons,
            'isAllowEdit' => $isAllowEdit,
            'curEmp' => $userCurrent,
            'regsUnapporve' => ManageTimeView::dayOffUnapprove($registerRecord->creator_id),
            'timeSetting' => $timeSetting,
            'compensationDays' => CoreConfigData::getCompensatoryDays($teamCodePre),
            'teamCodePreOfEmp' => $teamCodePre,
            'groupEmail' => $groupEmail,
            'groupEmailRegister' => $groupEmailRegister,
            'registerBranch' => $registerBranch,
            'weekends' => ManageTimeCommon::getAllWeekend(),
            'appendedFiles' => json_encode($appendedFiles),
            'attachmentsList' => $attachmentsList,
            'timeWorkingQuater' => $timeWorkingQuater,
            'leaveDay' => $leaveDay,
            'getRelation' => $getRelation,
            'getRelationMember' => $getRelationMember,
            'grantDate' => $grantDate
        ];
        return view('manage_time::leave.leave_register_reapply', $params);
    }
}
