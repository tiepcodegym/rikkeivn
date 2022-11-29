<?php

namespace Rikkei\ManageTime\Http\Controllers;

use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Lang;
use Log;
use Maatwebsite\Excel\Facades\Excel;
use Response;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\View;
use Rikkei\ManageTime\Model\BusinessTripEmployee;
use Rikkei\ManageTime\Model\BusinessTripRegister;
use Rikkei\ManageTime\Model\BusinessTripRelater;
use Rikkei\ManageTime\Model\BusinessTripTeam;
use Rikkei\ManageTime\Model\GratefulEmployeeOnsite;
use Rikkei\ManageTime\Model\ManageTimeAttachment;
use Rikkei\ManageTime\Model\ManageTimeComment;
use Rikkei\ManageTime\View\ApplicationHelper;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\ManageTime\View\ManageTimeConst;
use Rikkei\ManageTime\View\MissionPermission;
use Rikkei\ManageTime\View\ReportOnsite;
use Rikkei\ManageTime\View\ReportPermission;
use Rikkei\ManageTime\View\SupplementPermission;
use Rikkei\ManageTime\View\View as ManageTimeView;
use Rikkei\ManageTime\View\WorkingTime as WorkingTimeView;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Resource\View\View as ResourceView;
use Rikkei\Team\Model\Country;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Province;
use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Permission;
use Rikkei\Team\View\TeamList;
use Rikkei\ManageTime\Model\SupplementRegister;

class MissionController extends Controller
{
    /**
     * [missionRegister: view form register]
     * @return [view]
     */
    public function missionRegister()
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('Business trip');
        Menu::setActive('Profile');

        $userCurrent = Permission::getInstance()->getEmployee();
        $approversList = ManageTimeCommon::getApproversForEmployee($userCurrent->id);
        $registrantInformation = ManageTimeCommon::getRegistrantInformation($userCurrent->id);

        // Get working time this month of employee logged
        $teamCodePre = Team::getOnlyOneTeamCodePrefixChange($userCurrent);
        $objWTView = new WorkingTimeView();
        $workingTime = $objWTView->getWorkingTimeByEmployeeBetween($userCurrent->id, $teamCodePre);
        $timeSetting = $workingTime['timeSetting'];
        $keyDateInit = date('Y-m-d');

        return view('manage_time::mission.mission_register', [
            'approversList' => $approversList,
            'registrantInformation' => $registrantInformation,
            'suggestApprover' => ManageTimeCommon::suggestApprover(ManageTimeConst::TYPE_MISSION, $userCurrent),
            'tagEmployeeInfo' => new BusinessTripEmployee(),
            'registerRecord' => new BusinessTripRegister(),
            'isAllowEdit' => true,
            'pageType' => "create",
            'userCurrent' => $userCurrent,
            'timeSetting' => $timeSetting,
            'keyDateInit' => $keyDateInit,
            'compensationDays' => CoreConfigData::getCompensatoryDays($teamCodePre),
            'teamCodePreOfEmp' => $teamCodePre,
            'provinces' => Province::getProvinceList(),
            'country' => Country::listCountry(),
            'vn' => Country::VN,
            'jp' => Country::JP
        ]);
    }

    /**
     * [missionEditRegister: view form edit register]
     * @param  [int] $registerId
     * @return [view]
     */
    public function missionEditRegister($registerId)
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('Business trip');
        Menu::setActive('Profile');

        $userCurrent = Permission::getInstance()->getEmployee();

        $registerRecord = BusinessTripRegister::getInformationRegister($registerId);
        if (!$registerRecord) {
            return redirect()->route('manage_time::profile.mission.register-list')->withErrors(Lang::get('team::messages.Not found item.'));
        }

        $tagEmployeeInfo = BusinessTripEmployee::getEmployees($registerId);

        if (!SupplementPermission::isAllowViewDetail($registerRecord, $tagEmployeeInfo, $userCurrent->id)) {
            View::viewErrorPermission();
        }

        $approversList = ManageTimeCommon::getApproversForEmployee($registerRecord->creator_id);
        $relatedPersonsList = BusinessTripRelater::getRelatedPersons($registerId);
        $attachmentsList = ManageTimeAttachment::getAttachments($registerId, ManageTimeConst::TYPE_MISSION);
        $commentsList = ManageTimeComment::getReasonDisapprove($registerId, ManageTimeConst::TYPE_MISSION);

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

        // Get working time setting of employee in
        $timeSetting = (new WorkingTimeView)->getEmpWorkingTimeSettingInRegistration($tagEmployeeInfo, $registerRecord);
        $teamCodePre = Team::getOnlyOneTeamCodePrefixChange($userCurrent);
        return view('manage_time::mission.mission_register_edit', [
            'approversList'      => $approversList,
            'registerRecord'     => $registerRecord,
            'relatedPersonsList' => $relatedPersonsList,
            'attachmentsList'    => $attachmentsList,
            'commentsList'       => $commentsList,
            'appendedFiles'      => json_encode($appendedFiles),
            'isAllowEdit'        => SupplementPermission::isAllowEditDetail($registerRecord, $userCurrent->id),
            'tagEmployeeInfo'    => BusinessTripEmployee::getEmployees($registerId),
            'userCurrent'        => $userCurrent,
            'timeSetting'        => $timeSetting,
            'compensationDays' => CoreConfigData::getCompensatoryDays($teamCodePre),
            'teamCodePreOfEmp' => $teamCodePre,
            'provinces'          => Province::getProvinceList(),
            'country'            => Country::listCountry(),
            'currentEmpId'     => $registerRecord->creator_id,
            'vn' => Country::VN,
            'jp' => Country::JP
        ]);
    }

    /**
     * view form register for permission
     * @return [view]
     */
    public function adminRegister()
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('Business trip');
        Menu::setActive('Profile');

        if (!MissionPermission::allowCreateEditOther()) {
            View::viewErrorPermission();
        }
        // Get working time this month of employee logged
        $userCurrent = Permission::getInstance()->getEmployee();
        $teamCodePre = Team::getOnlyOneTeamCodePrefixChange($userCurrent);
        $objWTView = new WorkingTimeView();
        $workingTime = $objWTView->getWorkingTimeByEmployeeBetween($userCurrent->id, $teamCodePre);
        $timeSetting = $workingTime['timeSetting'];

        return view('manage_time::mission.mission_admin_register', [
            'timeSetting' => $timeSetting,
            'keyDateInit' => date('Y-m-d'),
            'userCurrent' => $userCurrent,
            'provinces' => Province::getProvinceList(),
            'country' => Country::listCountry(),
            'registerRecord' => new BusinessTripRegister(),
            'compensationDays' => CoreConfigData::getCompensatoryDays($teamCodePre),
            'vn' => Country::VN,
            'jp' => Country::JP,
            'teamCodePre' => $teamCodePre,
        ]);
    }

    /**
     * [missionRegisterList: view registers list of registrant]
     * @param  [int|null] $status
     * @return [view]
     */
    public function missionRegisterList($status = null)
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('Business trip');
        Menu::setActive('Profile');

        $userCurrent = Permission::getInstance()->getEmployee();
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];

        $collectionModel = BusinessTripRegister::getListRegisters($userCurrent->id, null, $status, $dataFilter);
        
        $params = [
            'collectionModel' => $collectionModel,
            'status'          => $status,
            'userCurrent'     => $userCurrent,
        ];

        return view('manage_time::mission.mission_register_list', $params);
    }

    /**
     * [missionApproveList: view approves list of approver]
     * @param  [int|null] $status
     * @return [view]
     */
    public function missionApproveList($status = null)
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('Business trip');
        Menu::setActive('Profile');

        $userCurrent = Permission::getInstance()->getEmployee();
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];

        $isScopeApproveOfSelf = MissionPermission::isScopeApproveOfSelf();
        $isScopeApproveOfTeam = MissionPermission::isScopeApproveOfTeam();
        $isScopeApproveOfCompany = MissionPermission::isScopeApproveOfCompany();
        if ($isScopeApproveOfSelf || $isScopeApproveOfTeam || $isScopeApproveOfCompany) {
            $collectionModel = BusinessTripRegister::getListRegisters(null, $userCurrent->id, $status, $dataFilter);
        } else {
            View::viewErrorPermission();
        }
        
        $params = [
            'collectionModel' => $collectionModel,
            'status'          => $status,
            'isApprove' => true
        ];

        return view('manage_time::mission.mission_approve_list', $params);
    }

    /**
     * [missionManageList: view register list of manager]
     * @param  [int] $id
     * @return [type]
     */
    public function missionManageList($id = null)
    {
        Breadcrumb::add('HR');
        Breadcrumb::add('Manage time');
        Breadcrumb::add('Business trip');
        Menu::setActive('HR');

        $userCurrent = Permission::getInstance()->getEmployee();
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];

        $teamIdsAvailable = null;
        $teamTreeAvailable = [];

        $isScopeManageOfTeam = MissionPermission::isScopeManageOfTeam();
        $isScopeManageOfCompany = MissionPermission::isScopeManageOfCompany();

        if ($isScopeManageOfCompany) {
            $teamIdsAvailable = true;
        } elseif ($isScopeManageOfTeam) {
            $teamIdsAvailable = Permission::getInstance()->isScopeTeam(null, 'manage_time::manage-time.manage.view');
            if (! $teamIdsAvailable) {
                View::viewErrorPermission();
            }
            
            foreach ($teamIdsAvailable as $key => $teamId) {
                if (! MissionPermission::isScopeManageOfTeam($teamId)) {
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

        $collectionModel = BusinessTripRegister::getListManageRegisters($id, $dataFilter);
        
        $params = [
            'collectionModel'   => $collectionModel,
            'teamIdCurrent'     => $id,
            'teamIdsAvailable'  => $teamIdsAvailable,
            'teamTreeAvailable' => $teamTreeAvailable,
            'isViewReportBusinessTrip'=>self::isViewReportBusinessTrip(),
            'roles' => Role::getAllPosition(),
        ];

        return view('manage_time::mission.mission_manage_list', $params);
    }

    /**
     * Check permission view report business trip
     * @return boolean TRUE is allow or FALSE not allow
     */
    public static function isViewReportBusinessTrip()
    {
        return ReportPermission::isScopeManageOfCompany() || ReportPermission::isScopeManageOfTeam();
    }

    
    /**
     * [missionDetailRegister: view page detail of register]
     * @param  [int] $registerId
     * @return [view]
     */
    public function missionDetailRegister($registerId)
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('Business trip');
        Menu::setActive('Profile');

        $userCurrent = Permission::getInstance()->getEmployee();

        $registerRecord = BusinessTripRegister::getInformationRegister($registerId);
        if (!$registerRecord) {
            return redirect()->route('manage_time::profile.mission.approve-list')->withErrors(Lang::get('team::messages.Not found item.'));
        }

        $tagEmployeeInfo = BusinessTripEmployee::getEmployees($registerId);
        $isAllowView = MissionPermission::isAllowView($registerId, $tagEmployeeInfo, $userCurrent->id);

        if ($isAllowView) {
            $approversList = ManageTimeCommon::getApproversForEmployee($registerRecord->creator_id);
            $relatedPersonsList = BusinessTripRelater::getRelatedPersons($registerId);
            $attachmentsList = ManageTimeAttachment::getAttachments($registerId, ManageTimeConst::TYPE_MISSION);
            $commentsList = ManageTimeComment::getReasonDisapprove($registerId, ManageTimeConst::TYPE_MISSION);
        } else {
            View::viewErrorPermission();
        }

        $isAllowApprove = false;
        if (MissionPermission::isAllowApprove($registerRecord, $userCurrent->id) && $registerRecord->status != BusinessTripRegister::STATUS_CANCEL) {
            $isAllowApprove = true;
        }

        // Get working time setting of employee in
        $timeSetting = (new WorkingTimeView)->getEmpWorkingTimeSettingInRegistration($tagEmployeeInfo, $registerRecord);
        $teamCodePre = Team::getOnlyOneTeamCodePrefix($userCurrent);
        return view('manage_time::mission.mission_register_edit', [
            'approversList'      => $approversList,
            'registerRecord'     => $registerRecord,
            'relatedPersonsList' => $relatedPersonsList,
            'attachmentsList'    => $attachmentsList,
            'commentsList'       => $commentsList,
            'isAllowApprove'     => $isAllowApprove,
            'tagEmployeeInfo'    => $tagEmployeeInfo,
            'userCurrent'        => $userCurrent,
            'timeSetting' => $timeSetting,
            'compensationDays' => CoreConfigData::getCompensatoryDays($teamCodePre),
            'teamCodePreOfEmp' => $teamCodePre,
            'provinces' => Province::getProvinceList(),
            'country' => Country::getCountryList(),
            'currentEmpId' => $registerRecord->creator_id,
            'vn' => Country::VN,
            'jp' => Country::JP
        ]);
    }

    /**
     * [missionRegisterViewPopup: show information of register]
     * @return show detail register to modal
     */
    public function missionRegisterViewPopup(Request $request)
    {      
        $registerId = $request->registerId;

        $registerRecord = BusinessTripRegister::getInformationRegister($registerId);
        $relatedPersonsList = BusinessTripRelater::getRelatedPersons($registerId);

        $params = [
            'registerRecord'   => $registerRecord,
            'relatedPersonsList'   => $relatedPersonsList,
        ];

        echo view('manage_time::include.modal.modal_view_mission', $params);
    }

    /**
     * Save register by admin
     * @param  Request $request
     * @return [type]
     */
    public function saveAdminRegister(Request $request)
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        if (!MissionPermission::allowCreateEditOther()) {
            View::viewErrorPermission();
        }
        $rules = [
            'employee_id' => 'required',
            'start_date'  => 'required',
            'end_date'    => 'required|after:start_date',
            'reason'      => 'required',
            'country_id'      => 'required',
        ];
        $messages = [
            'employee_id.required' => Lang::get('manage_time::view.The registrant field is required'),
            'start_date.required'  => Lang::get('manage_time::view.The out date field is required'),
            'end_date.required'    => Lang::get('manage_time::view.The on date field is required'),
            'end_date.after'       => Lang::get('manage_time::view.The on date at must be after out date'),
            'location.required'    => Lang::get('manage_time::view.The location field is required'),
            'reason.required'      => Lang::get('manage_time::view.The purpose field is required'),
            'country_id.required'  => Lang::get('manage_time::view.The country field is required'),
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }
        DB::beginTransaction();
        try {
            $dataMissionTK = [];
            $employeeId = $request->employee_id;
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $checkRegisterExist = BusinessTripRegister::checkRegisterExist($employeeId, Carbon::parse($startDate), Carbon::parse($endDate));
            if ($checkRegisterExist) {
                return redirect()->back()->withErrors(Lang::get('manage_time::message.Registration time has been identical'));
            }
            $registerRecord = new BusinessTripRegister;
            $registerRecord->creator_id                = $employeeId;
            $registerRecord->approver_id               = $userCurrent->id;
            $registerRecord->date_start                = Carbon::parse($startDate);
            $registerRecord->date_end                  = Carbon::parse($endDate);
            $registerRecord->number_days_business_trip = $request->number_days_off;
            $registerRecord->company_customer          = $request->company_customer;
            $registerRecord->location                  = $request->location;
            $registerRecord->purpose                   = $request->reason;
            $registerRecord->status                    = BusinessTripRegister::STATUS_APPROVED;
            $registerRecord->country_id = $request->country_id;
            $registerRecord->province_id = $request->province_id;
            $registerRecord->is_long = $request->is_long ? 1 : null;
            $registerRecord->approved_at = Carbon::now();

            if ($registerRecord->save()) {
                $registerTeam = [];
                $teamsOfRegistrant = ManageTimeCommon::getTeamsOfEmployee($employeeId);
                foreach ($teamsOfRegistrant as $team) {
                    $registerTeam[] = array('register_id' => $registerRecord->id, 'team_id'=> $team->id, 'role_id' => $team->role_id);
                }
                BusinessTripTeam::insert($registerTeam);

                $businessEmployee = new BusinessTripEmployee();
                $businessEmployee->register_id = $registerRecord->id;
                $businessEmployee->employee_id = $registerRecord->creator_id;
                $businessEmployee->start_at = $registerRecord->date_start;
                $businessEmployee->end_at = $registerRecord->date_end;
                $businessEmployee->save();
                BusinessTripEmployee::insertTeamId($registerRecord->creator_id, $registerRecord->id);
                $dataMissionTK[] = $registerRecord;
            }
            // ==== save timekeeping ===
            $this->insertTimekeeping($userCurrent, $dataMissionTK);
            DB::commit();

            $messages = [
                'success'=> [
                    Lang::get('manage_time::message.Register success'),
                ]
            ];

            return redirect()->route('manage_time::profile.mission.edit', ['id' => $registerRecord->id])->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex->getMessage());
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('manage_time::message.An error occurred')]]);
        }
    }

    /**
     * [missionSaveRegister: save register]
     * @param  Request $request
     * @return [view]
     */
    public function missionSaveRegister(Request $request)
    {
        $userCurrent = Permission::getInstance()->getEmployee();

        $rules = self::rules();
        $messages = self::messages();
        if (!empty($request->admin)) {
            $rules['employee_id'] = 'required';
            $messages['employee_id.required'] = Lang::get('manage_time::view.The registrant field is required');
        }
        if (empty(trim($request->location))) {
             $rules['location'] = 'required';
             $messages['location.required'] = trans('manage_time::message.Message empty location');
        }
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return redirect()->route('manage_time::profile.mission.register')
                        ->withErrors($validator)
                        ->withInput();
        }
        $country = Country::find($request->country_id);
        if ($country) {
            if (Country::VN === $country->country_code && empty($request->province_id)) {
                return redirect()->route('manage_time::profile.mission.register')
                        ->withErrors(trans('manage_time::message.Message empty provices'))
                        ->withInput();
            }
        } else {
            return redirect()->route('manage_time::profile.mission.register')
                    ->withErrors(trans('manage_time::message.Message not exists country'))
                    ->withInput();
        }
        $togethers = json_decode($request->table_data_emps);
        $checkLockUp = SupplementRegister::checkCloseAllTimekeeping($togethers);
        if ($checkLockUp) {
            return redirect()->back()->withErrors('Không thể tạo, sửa, duyệt đơn sau khi bảng công đã bị khóa đối với nhân viên: '.$checkLockUp);
        }

        DB::beginTransaction();
        try {
            if (!empty($request->employee_id)) {
                $employeeId = $request->employee_id;
            } else {
                $employeeId = $userCurrent->id;
            }
            $startDate = $request->date_start;
            $endDate = $request->date_end;

            $registerRecord = new BusinessTripRegister;
            $registerRecord->creator_id                = $employeeId;
            $registerRecord->approver_id               = $request->approver;
            $registerRecord->date_start                = Carbon::parse($startDate);
            $registerRecord->date_end                  = Carbon::parse($endDate);
            $registerRecord->number_days_business_trip = $request->number_days_off;
            $registerRecord->company_customer          = $request->company_customer;
            $registerRecord->location                  = $request->location;
            $registerRecord->purpose                   = $request->purpose;
            $registerRecord->status                    = BusinessTripRegister::STATUS_UNAPPROVE;
            $registerRecord->country_id                = $request->country_id;
            $registerRecord->province_id               = $request->province_id;
            $registerRecord->is_long                   = $request->is_long ? 1 : null;

            if ($registerRecord->save()) {
                $applicationHelper = new ApplicationHelper();
                $applicationHelper->saveRelate($registerRecord, $request, $userCurrent);
            }
            DB::commit();

            $messages = [
                'success'=> [
                    Lang::get('manage_time::message.Register success'),
                ]
            ];

            return redirect()->route('manage_time::profile.mission.edit', ['id' => $registerRecord->id])->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex->getMessage());
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('manage_time::message.An error occurred')]]);
        }
    }

    /**
     * [missionUpdateRegister: update register]
     * @param  Request $request
     * @return [view]
     */
    public function missionUpdateRegister(Request $request)
    {
        $userCurrent = Permission::getInstance()->getEmployee();

        $registerRecord = BusinessTripRegister::getInformationRegister($request->register_id);
        $registerId = $request->register_id;
        if (!$registerRecord) {
            return redirect()->route('manage_time::profile.mission.register-list')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        
        if (!SupplementPermission::isAllowEditDetail($registerRecord, $userCurrent->id)) {
            View::viewErrorPermission();
        }

        $rules = self::rules();
        $messages = self::messages();
        if (empty(trim($request->location))) {
             $rules['location'] = 'required';
             $messages['location.required'] = trans('manage_time::message.Message empty location');
        }
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return redirect()->route('manage_time::profile.mission.edit', ['id' => $registerId])
                        ->withErrors($validator)
                        ->withInput();
        }
        $country = Country::find($request->country_id);
        if ($country) {
            if (Country::VN === $country->country_code && empty($request->province_id)) {
                return redirect()->route('manage_time::profile.mission.register')
                        ->withErrors(trans('manage_time::message.Message empty provices'))
                        ->withInput();
            }
        } else {
            return redirect()->route('manage_time::profile.mission.register')
                    ->withErrors(trans('manage_time::message.Message not exists country'))
                    ->withInput();
        }
        $togethers = json_decode($request->table_data_emps);
        $checkLockUp = SupplementRegister::checkCloseAllTimekeeping($togethers);
        if ($checkLockUp) {
            return redirect()->back()->withErrors('Không thể tạo, sửa, duyệt đơn sau khi bảng công đã bị khóa đối với nhân viên: '.$checkLockUp);
        }
        DB::beginTransaction();
        try {
            $applicationHelper = new ApplicationHelper();
            //$fieldsChanged = $applicationHelper->applicationFieldsChanged($registerRecord, $request->except('_token', 'add_time_start', 'add_time_end'));
            $idRedirect = $registerRecord->id;
            //if (count($fieldsChanged)) {
                $startDate = $request->date_start;
                $endDate = $request->date_end;

                if ($registerRecord->status == BusinessTripRegister::STATUS_APPROVED) {
                    $childReg = new BusinessTripRegister();
                    $childReg->parent_id = $registerRecord->id;
                    $childReg->creator_id = $registerRecord->creator_id;
                    $childReg->approver_id = $request->approver;
                    $childReg->date_start = Carbon::parse($startDate);
                    $childReg->date_end = Carbon::parse($endDate);
                    $childReg->number_days_business_trip = $request->number_days_business_trip;
                    $childReg->location = $request->location;
                    $childReg->company_customer = $request->company_customer;
                    $childReg->purpose = $request->purpose;
                    $childReg->status = BusinessTripRegister::STATUS_UNAPPROVE;
                    $childReg->country_id = $request->country_id;
                    $childReg->province_id = $request->province_id;
                    $childReg->is_long = $request->is_long ? 1 : null;
                    if ($childReg->save()) {
                        $applicationHelper->saveRelate($childReg, $request, $userCurrent);
                        $idRedirect = $childReg->id;
                    }
                } else {
                    $registerRecord->approver_id = $request->approver;
                    $registerRecord->date_start = Carbon::parse($startDate);
                    $registerRecord->date_end = Carbon::parse($endDate);
                    $registerRecord->number_days_business_trip = $request->number_days_business_trip;
                    $registerRecord->location = $request->location;
                    $registerRecord->company_customer = $request->company_customer;
                    $registerRecord->purpose = $request->purpose;
                    $registerRecord->status = BusinessTripRegister::STATUS_UNAPPROVE;
                    $registerRecord->country_id = $request->country_id;
                    $registerRecord->province_id = $request->province_id;
                    $registerRecord->is_long = $request->is_long ? 1 : null;
                    if ($registerRecord->save()) {
                        $applicationHelper->saveRelate($registerRecord, $request, $userCurrent);
                    }
                }
                DB::commit();
            //}

            $messages = [
                'success'=> [
                    Lang::get('manage_time::message.Update success'),
                ]
            ];

            return redirect()->route('manage_time::profile.mission.edit', ['id' => $idRedirect])->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('manage_time::message.An error occurred')]]);
        }
    }

    /**
     * [missionDeleteRegister: delete register]
     * @param  Request $request
     * @return [json]
     */
    public function missionDeleteRegister(Request $request)
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        $urlCurrent = $request->urlCurrent;

        $registerId = $request->registerId;
        $registerRecord = BusinessTripRegister::find($registerId);
        if (!$registerRecord) {
            $messages = [
                'errors'=> [
                    Lang::get('team::messages.Not found item.'),
                ]
            ];

            $request->session()->flash('messages', $messages);
            echo json_encode(['url' => $urlCurrent]);
            return;
        }

        if ($userCurrent->id != $registerRecord->creator_id && !MissionPermission::allowCreateEditOther()) {
            $messages = [
                'errors'=> [
                    Lang::get('manage_time::message.You do not have permission to delete this object'),
                ]
            ];

            $request->session()->flash('messages', $messages);
            echo json_encode(['url' => $urlCurrent]);
            return;
        }
        
        if ($registerRecord->status == BusinessTripRegister::STATUS_APPROVED) {
            $messages = [
                'errors'=> [
                    Lang::get('manage_time::message.The register of business trip has been approved cannot delete'),
                ]
            ];

            $request->session()->flash('messages', $messages);
            echo json_encode(['url' => $urlCurrent]);
            return;
        }

        $registerRecord->status = BusinessTripRegister::STATUS_CANCEL;
        $registerRecord->save();
        $registerRecord->delete();
        
        $messages = [
            'success'=> [
                Lang::get('manage_time::message.The register of business trip has delete success'),
            ]
        ];

        $request->session()->flash('messages', $messages);
        echo json_encode(['url' => $urlCurrent]);
    }

    /**
     * [missionApproveRegister: approve register]
     * @param  Request $request
     * @return [json]
     */
    public function missionApproveRegister(Request $request)
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        $urlCurrent = $request->urlCurrent;

        $arrRegisterId = explode(',', $request->registerId);
        $listRegisterIdApprove = BusinessTripRegister::getRegisterByStatus($arrRegisterId, null);
        $messages = with(new ManageTimeView())->checkOverlap($arrRegisterId, ManageTimeView::STR_MISSION);

        if (count($listRegisterIdApprove) && !count($messages)) {
            DB::beginTransaction();
            $teamCodePre = Team::getOnlyOneTeamCodePrefix($userCurrent);
            try {
                $dataMissionTK = [];
                foreach ($listRegisterIdApprove as $registerId) {
                    $registerRecord = BusinessTripRegister::getInformationRegister($registerId);
                    $otEmps = BusinessTripEmployee::where("register_id", $registerId)->get();
                    $checkLockUp = SupplementRegister::checkCloseAllTimekeeping($otEmps, $registerRecord->date_start);
                    if ($checkLockUp) {
                        $messages = 'Không thể tạo, sửa, duyệt đơn sau khi bảng công đã bị khóa đối với nhân viên: '.$checkLockUp;
                        $request->session()->flash('messages', ['errors' => [$messages]]);
                        return json_encode(['url' => $urlCurrent]);
                    }
                    if (!empty($registerRecord->parent_id) && $teamCodePre != Team::CODE_PREFIX_JP) {
                       $this->getEmployeeMissionByRegister($registerRecord->parent_id, $dataMissionTK);
                    }
                    $registerRecord->status = BusinessTripRegister::STATUS_APPROVED;
                    $registerRecord->approver_id = $userCurrent->id;
                    $registerRecord->approved_at = Carbon::now();
                    
                    if (!empty($registerRecord->parent_id)) {
                        BusinessTripRegister::deleteById($registerRecord->parent_id);
                        $registerRecord->parent_id = null;
                    }

                    $data = [];
                    if ($registerRecord->save()) {
                        $data['user_mail']       = $userCurrent->email;
                        $data['mail_to']         = $registerRecord->creator_email;
                        $data['mail_title']      = Lang::get('manage_time::view.[Approved][Business trip] :name register business trip, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
                        $data['status']          = Lang::get('manage_time::view.Approved');
                        $data['registrant_name'] = $registerRecord->creator_name;
                        $data['team_name']       = $registerRecord->role_name;
                        $data['start_date']      = Carbon::parse($registerRecord->date_start)->format('d/m/Y');
                        $data['start_time']      = Carbon::parse($registerRecord->date_start)->format('H:i');
                        $data['end_date']        = Carbon::parse($registerRecord->date_end)->format('d/m/Y');
                        $data['end_time']        = Carbon::parse($registerRecord->date_end)->format('H:i');
                        $data['location']        = $registerRecord->location;
                        $data['purpose']         = View::nl2br(ManageTimeCommon::limitText($registerRecord->purpose, 50));
                        $data['link']            = route('manage_time::profile.mission.detail', ['id' => $registerRecord->register_id]);
                        $data['approver_name']   = $registerRecord->approver_name;
                        $data['approver_position'] = '';
                        $approver = $registerRecord->getApproverInformation();
                        if ($approver) {
                            $data['approver_position'] = $approver->approver_position;
                        }
                        $data['to_id']           = $registerRecord->creator_id;
                        $data['noti_content']    = trans('manage_time::view.The register of business trip has been considered:').' '.$data['status'];

                        $template = 'manage_time::template.mission.mail_approve.mail_approve_to_registrant';
                        $notificationData = [
                            'category_id' => RkNotify::CATEGORY_TIMEKEEPING
                        ];
                        if (ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData)) {
                            $relatedPersons = BusinessTripRelater::getRelatedPersons($registerRecord->id);
                            if (count($relatedPersons)) {
                                $data['mail_title'] = Lang::get('manage_time::view.[Notification][Business trip] :name register business trip, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
                                foreach ($relatedPersons as $item) {
                                    $data['mail_to'] = $item->relater_email;
                                    $data['related_person_name'] = $item->relater_name;
                                    $template = 'manage_time::template.mission.mail_approve.mail_approve_to_related_person';
                                    ManageTimeCommon::pushEmailToQueue($data, $template);
                                }
                                \RkNotify::put(
                                    $relatedPersons->lists('relater_id')->toArray(),
                                    trans('manage_time::view.The register of business trip of :registrant_name, :team_name related to you is considered:', $data).' '.$data['status'],
                                    $data['link'], ['category_id' => RkNotify::CATEGORY_TIMEKEEPING]
                                );
                            }
                        }
                        if ($teamCodePre != Team::CODE_PREFIX_JP) {
                            $this->getEmployeeMissionByRegister($registerRecord->register_id, $dataMissionTK);
                        }
                    }
                }
                $this->insertTimekeeping($userCurrent, $dataMissionTK);
                DB::commit();
            } catch (\Exception $ex) {
                DB::rollback();
                \Log::info($ex);
                $request->session()->flash('messages', ['errors' => trans('manage_time::message.An error occurred')]);
            }
            $messages = [
                'success'=> [
                    Lang::get('manage_time::message.The register of business trip has approve success'),
                ]
            ];
        }
        $request->session()->flash('messages', $messages);
        echo json_encode(['url' => $urlCurrent]);
    }

    /**
     * [missionApproveRegister: approve register]
     * @param  Request $request
     * @return [json]
     */
    public function missionDisapproveRegister(Request $request)
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        $urlCurrent = $request->urlCurrent;
        $arrRegisterId = explode(',', $request->registerId);
        $reasonDisapprove = $request->reasonDisapprove;

        if (empty($reasonDisapprove)) {
            $messages = [
                'errors'=> [
                    Lang::get('manage_time::message.You have not entered a reason not approve'),
                ]
            ];

            $request->session()->flash('messages', $messages);
            echo json_encode(['url' => $urlCurrent]);
            return;
        }

        $listRegisterIdDisapprove = BusinessTripRegister::getRegisterByStatus($arrRegisterId, null);
        $teamCodePre = Team::getOnlyOneTeamCodePrefix($userCurrent);
        if (count($listRegisterIdDisapprove)) {
            $dataLeaveDayTK = [];
            foreach ($listRegisterIdDisapprove as $registerId) {
                $registerRecord = BusinessTripRegister::getInformationRegister($registerId);
                $registerRecord->status = BusinessTripRegister::STATUS_DISAPPROVE;
                $registerRecord->approver_id = $userCurrent->id;
                $registerRecord->approved_at = Carbon::now();
                
                $data = [];
                if ($registerRecord->save()) {
                    $registerComment = new ManageTimeComment;
                    $registerComment->register_id = $registerId;
                    $registerComment->comment = $reasonDisapprove;
                    $registerComment->type = ManageTimeConst::TYPE_MISSION;
                    $registerComment->created_by = $userCurrent->id;
                    $registerComment->save();

                    $data['user_mail']         = $userCurrent->email;
                    $data['mail_to']           = $registerRecord->creator_email;
                    $data['mail_title']        = Lang::get('manage_time::view.[Approved][Business trip] :name register business trip, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
                    $data['status']            = Lang::get('manage_time::view.Unapprove');
                    $data['registrant_name']   = $registerRecord->creator_name;
                    $data['team_name']         = $registerRecord->role_name;
                    $data['start_date']        = Carbon::parse($registerRecord->date_start)->format('d/m/Y');
                    $data['start_time']        = Carbon::parse($registerRecord->date_start)->format('H:i');
                    $data['end_date']          = Carbon::parse($registerRecord->date_end)->format('d/m/Y');
                    $data['end_time']          = Carbon::parse($registerRecord->date_end)->format('H:i');
                    $data['location']          = $registerRecord->location;
                    $data['purpose']           = View::nl2br(ManageTimeCommon::limitText($registerRecord->purpose, 50));
                    $data['reason_disapprove'] = View::nl2br(ManageTimeCommon::limitText($reasonDisapprove, 50));
                    $data['link']              = route('manage_time::profile.mission.detail', ['id' => $registerRecord->register_id]);
                    $data['to_id']             = $registerRecord->creator_id;
                    $data['noti_content']      = trans('manage_time::view.The register of business trip has been considered:').' '.$data['status'];

                    $template = 'manage_time::template.mission.mail_disapprove.mail_disapprove_to_registrant';
                    $notificationData = [
                        'category_id' => RkNotify::CATEGORY_TIMEKEEPING
                    ];
                    ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData);
                    if ($teamCodePre != Team::CODE_PREFIX_JP) {
                        $this->getEmployeeMissionByRegister($registerRecord->register_id, $dataMissionTK);
                    }
                }
            }
            // ==== save timekeeping ===
            $this->insertTimekeeping($userCurrent, $dataLeaveDayTK);
        }

        $messages = [
            'success'=> [
                Lang::get('manage_time::message.The register of business trip has disapprove success'),
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
            $formattedEmployees[] = ['id' => $employee->id, 'text' => $employee->name . ' ('. preg_replace('/@.*/', '',$employee->email) . ')'];
        }

        return Response::json($formattedEmployees);
    }

    /**
     * [checkRegisterExist: check time register exist]
     * @param  Request $request
     * @return [boolean]
     */
    public function checkRegisterExist(Request $request)
    {
        $empList = json_decode($request->empList, true);
        $registerId = $request->registerId;
        $arrEmpExist = [];
        if (count($empList)) {
            foreach ($empList as $emp) {
                $checkRegisterExist = BusinessTripRegister::checkRegisterExist($emp['empId'], Carbon::parse($emp['startAt']), Carbon::parse($emp['endAt']), $registerId);
                if (count($checkRegisterExist)) {
                    $arrEmpExist[] = [
                        'empId' => $emp['empId'],
                        'empCode' => $checkRegisterExist->employee_code,
                        'empName' => $checkRegisterExist->name,
                        'url' => route('manage_time::profile.mission.edit', ['id' => $checkRegisterExist->register_id]),
                    ];
                }
            }
        }

       return Response()->json($arrEmpExist);
    }

    /**
     * Get the rules for the defined validation.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'approver'   => 'required',
            'date_start' => 'required',
            'date_end'   => 'required|after:date_start',
            'purpose'     => 'required',
            'country_id'      => 'required',
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
            'approver.required'   => Lang::get('manage_time::view.The approver field is required'),
            'date_start.required' => Lang::get('manage_time::view.The out date field is required'),
            'date_end.required'   => Lang::get('manage_time::view.The on date field is required'),
            'date_end.after'      => Lang::get('manage_time::view.The on date at must be after out date'),
            'location.required'   => Lang::get('manage_time::view.The location field is required'),
            'purpose.required'     => Lang::get('manage_time::view.The purpose field is required'),
            'country_id.required'     => Lang::get('manage_time::view.The country field is required'),
        ];

        return $messages;
    }

    /**
     * Get working time of employees list
     *
     * @param Request $request
     *
     * @return Response json
     */
    public function getWorkingTimeEmployees(Request $request)
    {
        $empIds = $request->get('empIds');
        if (!count($empIds)) {
            return null;
        }

        $objWTView = new WorkingTimeView();
        $period = [
            'start_date' => Carbon::parse($request->get('startDate'))->toDateString(),
            'end_date' => Carbon::parse($request->get('endDate'))->toDateString(),
        ];
        $employees = Employee::getEmpByIds($empIds, ['id']);
        $timeSetting = [];
        foreach ($employees as $emp) {
            $teamCode = Team::getOnlyOneTeamCodePrefixChange($emp->id);
            $workingTime = $objWTView->getWorkingTimeByEmployeeBetween($emp->id, $teamCode, $period);
            $timeSetting[$emp->id] = $workingTime['timeSetting'][$emp->id];
        }
        return response()->json([$timeSetting]);
    }

    /**
     * report business trip status approved
     * @param  [type] $id
     * @return [type]
     */
    public static function reportApproved($id = null)
    {
        if (!MissionPermission::isAllowReport()) {
           View::viewErrorPermission();
        }

        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];

        $teamIdsAvailable = true;
        $teamTreeAvailable = [];

        $collectionModel = BusinessTripRegister::getListManageRegistersApproved($dataFilter, BusinessTripRegister::STATUS_APPROVED, $id);

        $params = [
            'collectionModel'   => $collectionModel,
            'teamIdCurrent'     => $id,
            'teamIdsAvailable'  => $teamIdsAvailable,
            'teamTreeAvailable' => $teamTreeAvailable,
            'isViewReportBusinessTrip'=> false,
            'roles' => Role::getAllPosition(),
        ];

        return view('manage_time::mission.mission_manage_list', $params);
    }

    /**
     * export Report bussiness trip approved
     * @param  [int] $id
     * @return [type]
     */
    public static function reportApprovedExport($id = null)
    {
        if (!MissionPermission::isAllowReport()) {
           View::viewErrorPermission();
        }

        $userCurrent = Permission::getInstance()->getEmployee();
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];
        $collectionModel = BusinessTripRegister::exportListManageRegistersApproved($dataFilter, BusinessTripRegister::STATUS_APPROVED, $id)->get()->toArray();

        Excel::create('Business_Trip_' . \Carbon\Carbon::now()->now()->format('Y_m_d'), function ($excel) use ($collectionModel) {
            $excel->sheet('Sheet 1', function ($sheet) use ($collectionModel) {
                $data = [];
                $data[0] = [Lang::get('manage_time::view.Employee code'),
                            Lang::get('manage_time::view.Employee name'),
                            Lang::get('manage_time::view.Position'),
                            Lang::get('manage_time::view.Location'),
                            Lang::get('manage_time::view.Purpose'),
                            Lang::get('manage_time::view.From date'),
                            Lang::get('manage_time::view.End date'),
                            Lang::get('manage_time::view.Number of days business trip'),
                            Lang::get('manage_time::view.Skype'),
                            Lang::get('manage_time::view.Telephone allowance'),
                            Lang::get('manage_time::view.Email')
                        ];

                foreach ($collectionModel as $key => $item) {
                    $start = Carbon::createFromFormat('Y-m-d H:i:s', $item['start_at']);
                    $end = Carbon::createFromFormat('Y-m-d H:i:s', $item['end_at']);
                    if ($item["creator_id"] == $item["employee_id"]) {
                        $positon = $item["postion_reg"];
                        $number = $item["number_days_business_trip"];
                    } else {
                        $positon = $item["postion_emp"];
                        $startAt = clone $start;
                        $endAt = clone $end;
                        $number = BusinessTripRegister::getNumberBusinessTrip($startAt, $endAt, $item["employee_id"]);
                    }

                    $data[] = [
                        $item['employee_code'],
                        $item['name'],
                        $positon,
                        $item['location'],
                        $item['purpose'],
                        $start->format('d-m-Y H:i'),
                        $end->format('d-m-Y H:i'),
                        number_format($number, 1),
                        $item['skype'],
                        $item['mobile_phone'],
                        $item['email']
                    ];
                }
                $sheet->fromArray($data, null, 'A1', true, false);
                $sheet->cells('A1:K1', function ($cells) {
                    $cells->setFontWeight('bold');
                    $cells->setBackground('#D3D3D3');
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });

                $countData = count($data);
                $sheet->cells("A2:K{$countData}", function ($cells) {
                    $cells->setAlignment('left');
                    $cells->setValignment('center');
                });

                $sheet->setHeight([
                    1     =>  50,
                    2     =>  25
                ]);

                $sheet->setBorder('A1:K1', 'thin');
            });
            $excel->getActiveSheet()->getDefaultStyle()->getAlignment()->setWrapText(true);
        })->download('xlsx');
    }

    /**
     * Business trip relates to me
     * @param null $status
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function missionRelatesList($status = null)
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];
        $collectionModel = BusinessTripRegister::getListRegisters(null, null, $status, $dataFilter, $userCurrent->id);

        $params = [
            'collectionModel' => $collectionModel,
            'status' => $status,
            'isRelates' => true
        ];

        return view('manage_time::mission.mission_approve_list', $params);
    }
        
    /**
     * reportOnsiteWithYear
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function reportOnsiteWithYear()
    {
        if (!Permission::getInstance()->isAllow()) {
            View::viewErrorPermission();
        }

        Breadcrumb::add('Hr');
        Breadcrumb::add('report onsite');
        $objView = new ManageTimeView();
        $objGrateful = new GratefulEmployeeOnsite();
        $objReport = new ReportOnsite();

        $dataOnsite = $objReport->getEmployeesOnsite();
        $emloyeeOnsite = $dataOnsite['dataEmp'];
        $timeStart = $dataOnsite['timeStart'];
        $timeEnd = $dataOnsite['timeEnd'];
        $diffDay = $timeEnd->diffInDays($timeStart) + 1;
        $arrYear = $objView->getDayYear($diffDay);
        
        $empIds= [];
        $dataEmpGrateful = [];
        if (count($emloyeeOnsite['data'])) {
            foreach ($emloyeeOnsite['data'] as $item) {
                $empIds[] = $item->employee_id;
            }
        }
        if ($year = Form::getFilterData('except', "tbl.year")) {
            $dataYear = [$year];
        } else {
            $dataYear = array_values($arrYear);
        }

        $employeeGratefuled = $objGrateful->getEmployeeGratefulEmployee($empIds, $dataYear);
        if (count($employeeGratefuled)) {
            foreach($employeeGratefuled as $item) {
                $dataEmpGrateful[$item->employee_id][$item->number] = $item;
            }
        }

        $params = [
           'emloyeeOnsite' => $emloyeeOnsite,
           'teamsOptionAll' => TeamList::toOption(null, true, false),
           'arrYear' => $arrYear,
           'year' => $year,
           'startDateFilter' => Form::getFilterData('except', "tbl.date_start"),
           'endDateFilter' => Form::getFilterData('except', "tbl.date_end"),
           'dataEmpGrateful' => $dataEmpGrateful,
           'diffDay' => $diffDay,
        ];

        return view('manage_time::mission.report_onsite_hr', $params);
    }

    /**
     * Export report onsite
     *
     * @return void
     */
    public function exportOnsite()
    {
        if (!Permission::getInstance()->isAllow()) {
            return;
        }

        $objReport = new ReportOnsite();
        $dataOnsite = $objReport->getEmployeesOnsite(true);
        $emloyeeOnsite = $dataOnsite['dataEmp'];
        $timeStart = $dataOnsite['timeStart'];
        $timeEnd = $dataOnsite['timeEnd'];
        $diffDay = $timeEnd->diffInDays($timeStart) + 1;
        $objView = new ManageTimeView();
        $arrYear = $objView->getDayYear($diffDay);

        Excel::create('Business_Trip_' . \Carbon\Carbon::now()->now()->format('Y_m_d'), function ($excel) use ($emloyeeOnsite, $arrYear) {
            $excel->sheet('Sheet 1', function ($sheet) use ($emloyeeOnsite, $arrYear) {
                $data = [];
                $data[0] = [
                    'STT',
                    'Mã NV',
                    'Tên NV',
                    'Email NV',
                    'Bộ phận',
                    'Từ ngày',
                    'Đến ngày',
                    'Địa chỉ',
                    'Công ty onsite',
                    'Khách hàng đại diện',
                    'Sale',
                    'Số ngày onsite',
                    'Số năm',
                ];
                $i = 0;
                foreach ($emloyeeOnsite as $item) {
                    $objView = new ManageTimeView();
                    $viewYear = $objView->getYearByDay($item->onsite_days, $arrYear);
                    $data[] = [
                        ++$i,
                        $item->employee_code,
                        $item->employee_name,
                        $item->employee_email,
                        $item->team_name,
                        $item->start_at,
                        $item->end_at_now,
                        $item->location,
                        $item->company_name,
                        $item->contacts_name,
                        $item->sale_employee,
                        $item->onsite_days,
                        $viewYear,
                    ];
                }
                $sheet->fromArray($data, null, 'A1', true, false);
                $sheet->cells('A1:M1', function ($cells) {
                    $cells->setFontWeight('bold');
                    $cells->setBackground('#D3D3D3');
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });

                $countData = count($data);
                $sheet->cells("A2:M{$countData}", function ($cells) {
                    $cells->setAlignment('left');
                    $cells->setValignment('center');
                });

                $sheet->setHeight([
                    1     =>  50,
                    2     =>  25
                ]);

                $sheet->setBorder('A1:M1', 'thin');
            });
            $excel->getActiveSheet()->getDefaultStyle()->getAlignment()->setWrapText(true);
        })->download('xlsx');
    }
        
    /**
     * getEmployeeMissionByRegister
     *
     * @param  int $id
     * @return void
     */
    public function getEmployeeMissionByRegister($id, &$dataMissionTK)
    {
        $arrEmpBusiness = BusinessTripEmployee::getEmployees($id);
        if (count($arrEmpBusiness)) {
            foreach($arrEmpBusiness as $item) {
                $dataMissionTK[] = $item;
            }
        }
    }
    
    /**
     * insertTimekeeping
     *
     * @param  collection $userCurrent
     * @param  array $dataMissionTK
     * @return void
     */
    public function insertTimekeeping($userCurrent, $dataMissionTK)
    {
        $objView = new ManageTimeView();
        return $objView->insertTimekeeping($userCurrent, $dataMissionTK);
    }
}
