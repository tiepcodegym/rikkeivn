<?php

namespace Rikkei\ManageTime\Http\Controllers;

use Carbon\Carbon;
use DB;
use Exception;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Lang;
use Log;
use Response;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\View;
use Rikkei\ManageTime\Model\LeaveDayRegister;
use Rikkei\ManageTime\Model\ManageTimeAttachment;
use Rikkei\ManageTime\Model\ManageTimeComment;
use Rikkei\ManageTime\Model\SupplementEmployee;
use Rikkei\ManageTime\Model\SupplementReasons;
use Rikkei\ManageTime\Model\SupplementRegister;
use Rikkei\ManageTime\Model\SupplementRelater;
use Rikkei\ManageTime\Model\SupplementTeam;
use Rikkei\ManageTime\View\FileUploader;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\ManageTime\View\ManageTimeConst;
use Rikkei\ManageTime\View\SupplementPermission;
use Rikkei\ManageTime\View\View as ManageTimeView;
use Rikkei\ManageTime\View\ViewTimeKeeping;
use Rikkei\ManageTime\View\WorkingTime as WorkingTimeView;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Ot\Model\OtRegister;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Permission;

class SupplementController extends Controller
{
    /**
     * [supplementRegister: view form register]
     * @return [view]
     */
    public function supplementRegister(Request $request)
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('Supplement');
        Menu::setActive('Profile');

        $params = self::supplementMain($request);

        return view('manage_time::supplement.supplement_register', $params);
    }

    public function modalSupplementRegister(Request $request)
    {
        $params = self::supplementMain($request);
        $modalSupplementRegister = view('manage_time::supplement.include.form_supplement_register', $params)->render();
        return \Response::json([
            'renderHtml' => $modalSupplementRegister,
        ]);
    }

    public function supplementMain($request)
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        $registrantInformation = ManageTimeCommon::getRegistrantInformation($userCurrent->id);

        // Get working time this month of employee logged
        $teamCodePre = Team::getOnlyOneTeamCodePrefixChange($userCurrent);
        $objWTView = new WorkingTimeView();
        $workingTime = $objWTView->getWorkingTimeByEmployeeBetween($userCurrent->id, $teamCodePre);
        $timeSetting = $workingTime['timeSetting'];

        $reasons = ManageTimeView::isWorkingInJapan($teamCodePre) ? SupplementReasons::all() : null;
        $empProjects = static::getEmpProject($userCurrent->id);
        $params = [
            'registrantInformation' => $registrantInformation,
            'suggestApprover' => ManageTimeCommon::suggestApprover(ManageTimeConst::TYPE_SUPPLEMENT, $userCurrent),
            'tagEmployeeInfo' => new SupplementEmployee(),
            'registerRecord' => new SupplementRegister(),
            'isAllowEdit' => true,
            'pageType' => "create",
            'userCurrent' => $userCurrent,
            'timeSetting' => $timeSetting,
            'keyDateInit' => date('Y-m-d'),
            'compensationDays' => CoreConfigData::getCompensatoryDays($teamCodePre),
            'teamCodePreOfEmp' => $teamCodePre,
            'reasons' => $reasons,
            'empProjects' => $empProjects,
        ];

        return $params;
    }

    /**
     * view form register for permission
     * @return [view]
     */
    public function adminRegister()
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('Supplement');
        Menu::setActive('Profile');

        if (!SupplementPermission::allowCreateEditOther()) {
            View::viewErrorPermission();
        }

        $userCurrent = Permission::getInstance()->getEmployee();
        // Get working time this month of employee logged
        $teamCodePre = Team::getOnlyOneTeamCodePrefixChange($userCurrent);
        $objWTView = new WorkingTimeView();
        $workingTime = $objWTView->getWorkingTimeByEmployeeBetween($userCurrent->id, $teamCodePre);
        $timeSetting = $workingTime['timeSetting'];

        $reasons = ManageTimeView::isWorkingInJapan($teamCodePre) ? SupplementReasons::all() : null;
        $empProjects = null;
        return view('manage_time::supplement.supplement_admin_register', [
            'timeSetting' => $timeSetting,
            'keyDateInit' => date("Y-m-d"),
            'userCurrent' => $userCurrent,
            'compensationDays' => CoreConfigData::getCompensatoryDays($teamCodePre),
            'teamCodePreOfEmp' => $teamCodePre,
            'reasons' => $reasons,
            'empProjects' => $empProjects,
        ]);
    }

    /**
     * [supplementRegisterList: view registers list of registrant]
     * @param  [int|null] $status
     * @return [view]
     */
    public function supplementRegisterList($status = null)
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('Supplement');
        Menu::setActive('Profile');

        $userCurrent = Permission::getInstance()->getEmployee();
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];

        $collectionModel = SupplementRegister::getListRegisters($userCurrent->id, null, $status, $dataFilter);

        $params = [
            'collectionModel' => $collectionModel,
            'status' => $status,
            'userCurrent' => $userCurrent,
        ];

        return view('manage_time::supplement.supplement_register_list', $params);
    }

    /**
     * [supplementApproveList: view approves list of approver]
     * @param  [int|null] $status
     * @return [view]
     */
    public function supplementApproveList($status = null)
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('Supplement');
        Menu::setActive('Profile');

        $userCurrent = Permission::getInstance()->getEmployee();
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];

        $isScopeApproveOfSelf = SupplementPermission::isScopeApproveOfSelf();
        $isScopeApproveOfTeam = SupplementPermission::isScopeApproveOfTeam();
        $isScopeApproveOfCompany = SupplementPermission::isScopeApproveOfCompany();

        $empIds = [];

        $teamCodes = [];

        $teamHolidays = Team::getHolidaysByBranches();

        if ($isScopeApproveOfSelf || $isScopeApproveOfTeam || $isScopeApproveOfCompany) {
            $collectionModel = SupplementRegister::getListRegisters(null, $userCurrent->id, $status, $dataFilter);
            foreach ($collectionModel as $emp) {
                $empIds[] = $emp->creator_id;
            }

            $empIds = array_unique($empIds); // bỏ trùng

            $getTeamOfEmployees = Team::getTeamOfEmployees($empIds);

            foreach ($getTeamOfEmployees as $teamCode) {
              $teamCodes[$teamCode->employee_id] = $teamCode->branch_code;

            }
            foreach ($collectionModel as &$emp) {
                $emp->team_code =  $teamCodes[$emp->creator_id];
                $emp->holidays = $teamHolidays[$emp->team_code];
            }
        } else {
            View::viewErrorPermission();
        }

        $params = [
            'collectionModel' => $collectionModel,
            'status' => $status,
            'isApprove' => true
        ];

        return view('manage_time::supplement.supplement_approve_list', $params);
    }

    /**
     * [supplementManageList: view register list of manager]
     * @param  [int] $id
     * @return [type]
     */
    public function supplementManageList($id = null)
    {
        Breadcrumb::add('HR');
        Breadcrumb::add('Manage time');
        Breadcrumb::add('Supplement');
        Menu::setActive('HR');

        $userCurrent = Permission::getInstance()->getEmployee();
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];

        $teamIdsAvailable = null;
        $teamTreeAvailable = [];

        $isScopeManageOfTeam = SupplementPermission::isScopeManageOfTeam();
        $isScopeManageOfCompany = SupplementPermission::isScopeManageOfCompany();

        if ($isScopeManageOfCompany) {
            $teamIdsAvailable = true;
        } elseif ($isScopeManageOfTeam) {
            $teamIdsAvailable = Permission::getInstance()->isScopeTeam(null, 'manage_time::manage-time.manage.view');
            if (!$teamIdsAvailable) {
                View::viewErrorPermission();
            }

            foreach ($teamIdsAvailable as $key => $teamId) {
                if (!SupplementPermission::isScopeManageOfTeam($teamId)) {
                    unset($teamIdsAvailable[$key]);
                }
            }
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

        $collectionModel = SupplementRegister::getListManageRegisters($id, $dataFilter);

        $params = [
            'collectionModel' => $collectionModel,
            'teamIdCurrent' => $id,
            'teamIdsAvailable' => $teamIdsAvailable,
            'teamTreeAvailable' => $teamTreeAvailable,
        ];

        return view('manage_time::supplement.supplement_manage_list', $params);
    }

    /**
     * [supplementEditRegister: view form edit register]
     * @param  [int] $registerId
     * @return [view]
     */
    public function supplementEditRegister($registerId)
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('Supplement');
        Menu::setActive('Profile');

        $userCurrent = Permission::getInstance()->getEmployee();

        $registerRecord = SupplementRegister::getInformationRegister($registerId);
        if (!$registerRecord) {
            return redirect()->route('manage_time::profile.supplement.register-list')->withErrors(Lang::get('team::messages.Not found item.'));
        }

        $tagEmployeeInfo = SupplementEmployee::getEmployees($registerId);

        if (!SupplementPermission::isAllowViewDetail($registerRecord, $tagEmployeeInfo, $userCurrent->id)) {
            View::viewErrorPermission();
        }

        $approversList = ManageTimeCommon::getApproversForEmployee($registerRecord->creator_id);
        $relatedPersonsList = SupplementRelater::getRelatedPersons($registerId);
        $attachmentsList = ManageTimeAttachment::getAttachments($registerId, ManageTimeConst::TYPE_SUPPLEMENT);
        $commentsList = ManageTimeComment::getReasonDisapprove($registerId, ManageTimeConst::TYPE_SUPPLEMENT);

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
        $objWTView = new WorkingTimeView();
        $timeSetting = [];
        foreach ($tagEmployeeInfo as $emp) {
            $teamCode = Team::getOnlyOneTeamCodePrefix($emp->employee_id);
            $period = [
                'start_date' => Carbon::parse($emp->start_at)->toDateString(),
                'end_date' => Carbon::parse($emp->end_at)->toDateString(),
            ];
            $workingTime = $objWTView->getWorkingTimeByEmployeeBetween($emp->employee_id, $teamCode, $period);
            $timeSetting[$emp->employee_id] = $workingTime['timeSetting'][$emp->employee_id];
        }

        $teamCodePre = Team::getOnlyOneTeamCodePrefixChange($userCurrent);
        $reasons = $registerRecord->reason_id ? SupplementReasons::all() : null;
        $reasonTypeOther = $registerRecord->reason_id ? SupplementReasons::getOtherType() : null;
        $empProjects = static::getEmpProject($userCurrent->id);

        $params = [
            'approversList' => $approversList,
            'registerRecord' => $registerRecord,
            'relatedPersonsList' => $relatedPersonsList,
            'commentsList' => $commentsList,
            'attachmentsList' => $attachmentsList,
            'appendedFiles' => json_encode($appendedFiles),
            'isAllowEdit' => SupplementPermission::isCanEditDetail($registerRecord, $userCurrent->id),
            'tagEmployeeInfo' => $tagEmployeeInfo,
            'pageType' => "edit",
            'userCurrent' => $userCurrent,
            'timeSetting' => $timeSetting,
            'compensationDays' => CoreConfigData::getCompensatoryDays($teamCodePre),
            'teamCodePreOfEmp' => $teamCodePre,
            'reasons' => $reasons,
            'reasonTypeOther' => $reasonTypeOther,
            'empProjects' => $empProjects,
        ];

        return view('manage_time::supplement.supplement_register_edit', $params);
    }

    /**
     * [supplementDetailRegister: view page detail of register]
     * @param  [int] $registerId
     * @return [view]
     */
    public function supplementDetailRegister($registerId)
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('Supplement');
        Menu::setActive('Profile');

        $userCurrent = Permission::getInstance()->getEmployee();

        $registerRecord = SupplementRegister::getInformationRegister($registerId);
        if (!$registerRecord) {
            return redirect()->route('manage_time::profile.supplement.approve-list')->withErrors(Lang::get('team::messages.Not found item.'));
        }

        $tagEmployeeInfo = SupplementEmployee::getEmployees($registerId);
        $isAllowView = SupplementPermission::isAllowView($registerId, $tagEmployeeInfo, $userCurrent->id);
        if ($isAllowView) {
            $approversList = ManageTimeCommon::getApproversForEmployee($registerRecord->creator_id);
            $relatedPersonsList = SupplementRelater::getRelatedPersons($registerId);
            $attachmentsList = ManageTimeAttachment::getAttachments($registerId, ManageTimeConst::TYPE_SUPPLEMENT);
            $commentsList = ManageTimeComment::getReasonDisapprove($registerId, ManageTimeConst::TYPE_SUPPLEMENT);
        } else {
            View::viewErrorPermission();
        }

        $isAllowApprove = false;
        if (SupplementPermission::isAllowApprove($registerRecord, $userCurrent->id) && $registerRecord->status != SupplementRegister::STATUS_CANCEL) {
            $isAllowApprove = true;
        }

        // Get working time setting of employee in
        $objWTView = new WorkingTimeView();
        $timeSetting = [];
        foreach ($tagEmployeeInfo as $emp) {
            $teamCode = Team::getOnlyOneTeamCodePrefix($emp->employee_id);
            $period = [
                'start_date' => Carbon::parse($emp->start_at)->toDateString(),
                'end_date' => Carbon::parse($emp->end_at)->toDateString(),
            ];
            $workingTime = $objWTView->getWorkingTimeByEmployeeBetween($emp->employee_id, $teamCode, $period);
            $timeSetting[$emp->employee_id] = $workingTime['timeSetting'][$emp->employee_id];
        }

        $teamCodePre = Team::getOnlyOneTeamCodePrefix($userCurrent);
        $reasons = $registerRecord->reason_id ? SupplementReasons::all() : null;
        $reasonTypeOther = $registerRecord->reason_id ? SupplementReasons::getOtherType() : null;
        $empProjects = static::getEmpProject($registerRecord->creator_id);
        
        $arrStart = explode(" ",  $registerRecord->date_start);
        $arrEnd = explode(" ", $registerRecord->date_end);
        $timekeeping = $this->getTimekeeping($arrStart[0], $arrEnd[0], [$registerRecord->creator_id], $teamCodePre );

        $params = [
            'approversList' => $approversList,
            'registerRecord' => $registerRecord,
            'relatedPersonsList' => $relatedPersonsList,
            'attachmentsList' => $attachmentsList,
            'commentsList' => $commentsList,
            'isAllowApprove' => $isAllowApprove,
            'tagEmployeeInfo' => $tagEmployeeInfo,
            'pageType' => 'detail',
            'userCurrent' => $userCurrent,
            'timeSetting' => $timeSetting,
            'compensationDays' => CoreConfigData::getCompensatoryDays($teamCodePre),
            'teamCodePreOfEmp' => $teamCodePre,
            'reasons' => $reasons,
            'reasonTypeOther' => $reasonTypeOther,
            'empProjects' => $empProjects,
            'timekeeping' => $timekeeping,
        ];

        return view('manage_time::supplement.supplement_register_edit', $params);
    }

    /**
     * [supplementRegisterViewPopup: show information of register]
     * @return show detail register to modal
     */
    public function supplementRegisterViewPopup(Request $request)
    {
        $registerId = $request->registerId;
        $registerRecord = SupplementRegister::getInformationRegister($registerId);
        $relatedPersonsList = SupplementRelater::getRelatedPersons($registerId);

        $params = [
            'registerRecord' => $registerRecord,
            'relatedPersonsList' => $relatedPersonsList,
        ];

        echo view('manage_time::include.modal.modal_view_supplement', $params);
    }

    /**
     * Save register by admin
     * @param  Request $request
     * @return [type]
     */
    public function saveAdminRegister(Request $request)
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        if (!SupplementPermission::allowCreateEditOther()) {
            View::viewErrorPermission();
        }
        $rules = [
            'employee_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required|after:start_date',
            'number_days_off' => 'not_in:0',
        ];
        $messages = [
            'employee_id.required' => Lang::get('manage_time::view.The registrant field is required'),
            'start_date.required' => Lang::get('manage_time::view.The start date field is required'),
            'end_date.required' => Lang::get('manage_time::view.The end date field is required'),
            'end_date.after' => Lang::get('manage_time::view.The end date at must be after start date'),
            'number_days_off.not_in' => Lang::get('manage_time::view.Number days :category off other 0', ['category' => 'bổ sung công']),
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        DB::beginTransaction();
        $employeeId = $request->employee_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $checkRegisterExist = SupplementRegister::getRegisterExist($employeeId, Carbon::parse($startDate), Carbon::parse($endDate), null, $request->is_ot);

        $cbStart = Carbon::parse($request->start_date);
        $cbEnd = Carbon::parse($request->end_date);
        // check phep
        $collections = with(new LeaveDayRegister())->getRegisterExistSupp($employeeId, $cbStart->format('Y-m-d'), $cbEnd->format('Y-m-d'));
        $checkLeaveDay = $this->checkLeaveDays($collections, $cbStart, $cbEnd);
        if ($checkLeaveDay) {
            return redirect()->back()->withErrors($checkLeaveDay);
        }
        if (count($checkRegisterExist)) {
            $arrEmpExist = [];
            $idRegister = '';
            foreach ($checkRegisterExist as $emp) {
                $idRegister = $idRegister . $emp->supplement_registers_id . ';';
            }
            return redirect()->back()->withInput()
                ->with('messages', ['errors' => [trans('manage_time::message.Registration time has been supplement identical: :id', ["id" => rtrim($idRegister, ";")])]]);
        }

        // check time supplement OT one day
        if ($request->is_ot && Carbon::parse($startDate)->format('Y-m-d') != Carbon::parse($endDate)->format('Y-m-d')) {
            return redirect()->back()->withErrors(Lang::get('manage_time::view.Register supplement OT must be in a day'));
        }

        try {
            $registerRecord = new SupplementRegister;
            $registerRecord->creator_id = $employeeId;
            $registerRecord->approver_id = $userCurrent->id;
            $registerRecord->date_start = Carbon::parse($startDate);
            $registerRecord->date_end = Carbon::parse($endDate);
            $registerRecord->reason = $request->reason;
            $registerRecord->number_days_supplement = $request->number_days_off;
            $registerRecord->status = SupplementRegister::STATUS_APPROVED;
            $registerRecord->is_ot = $request->is_ot ? $request->is_ot : 0;
            $registerRecord->reason_id = $request->reason_id;
            $registerRecord->reason = SupplementRegister::setReason($registerRecord, $request->reason);
            $registerRecord->approved_at = Carbon::now();
            $data = [];

            // check exits
            $checkSupp = SupplementRegister::checkRegisterExist($employeeId, Carbon::parse($startDate), Carbon::parse($endDate), null, $registerRecord->is_ot);
            if ($checkSupp) {
                return redirect()->back()->withErrors(Lang::get('manage_time::message.Registration time has been identical'));
            }

            if ($registerRecord->save()) {
                $registerTeam = [];
                $dataSuppTK = [];
                $teamsOfRegistrant = ManageTimeCommon::getTeamsOfEmployee($employeeId);
                foreach ($teamsOfRegistrant as $team) {
                    $registerTeam[] = array('register_id' => $registerRecord->id, 'team_id' => $team->id, 'role_id' => $team->role_id);
                }
                SupplementTeam::insert($registerTeam);

                // create folder
                $structure = base_path('public/storage/manage-time');
                File::makeDirectory($structure, 0777, true, true);

                $fileUploader = new FileUploader('files', array(
                    'uploadDir' => base_path('public/storage/manage-time/'),
                    'title' => 'name'
                ));
                // call to upload the files
                $data = $fileUploader->upload();
                // if uploaded and success
                if ($data['isSuccess'] && count($data['files']) > 0) {
                    $uploadedFiles = $data['files'];
                }
                // get the fileList
                $fileList = $fileUploader->getFileList();
                if (count($fileList) > 0) {
                    $attachments = [];
                    foreach ($fileList as $key) {
                        $attachments[] = ['register_id' => $registerRecord->id, 'file_name' => $key['title'] . '.' . $key['extension'], 'path' => $key['file'], 'size' => $key['size'], 'mime_type' => $key['type'], 'type' => ManageTimeConst::TYPE_SUPPLEMENT];
                    }
                    ManageTimeAttachment::insert($attachments);
                }

                $supplementEmployee = new SupplementEmployee();
                $supplementEmployee->supplement_registers_id = $registerRecord->id;
                $supplementEmployee->employee_id = $registerRecord->creator_id;
                $supplementEmployee->start_at = $registerRecord->date_start;
                $supplementEmployee->end_at = $registerRecord->date_end;
                $supplementEmployee->number_days = $registerRecord->number_days_supplement;
                $supplementEmployee->save();

                $dataSuppTK[] = $registerRecord;
            }
            // ==== save timekeeping ===
            $this->insertTimekeeping($userCurrent, $dataSuppTK);
            DB::commit();

            $messages = [
                'success' => [
                    Lang::get('manage_time::message.Register success'),
                ]
            ];
            return redirect()->route('manage_time::timekeeping.manage.supplement')->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex->getMessage());
            $messages = [
                'errors' => [
                    $ex->getMessage(),
                ]
            ];
            return redirect()->back()->withInput()->with('messages', $messages);
        }
    }

    /**
     * [supplementSaveRegister: save register]
     * @param  Request $request
     * @return [view]
     */
    public function supplementSaveRegister(Request $request)
    {
        $userCurrent = Permission::getInstance()->getEmployee();

        $rules = static::rules();
        $messages = static::messages();
        if (!empty($request->admin)) {
            $rules['employee_id'] = 'required';
            $messages['employee_id.required'] = Lang::get('manage_time::view.The registrant field is required');
        }
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            if(!empty($request->isAjax) && $request->isAjax == 1){
                $strMessage = '';
                foreach ($validator->errors()->all() as $message) {
                    $strMessage .= $message . ' ';
                }
                return array('status' => false, 'message' => $strMessage);
            }else{
                return redirect()->route('manage_time::profile.supplement.register')
                    ->withErrors($validator)
                    ->withInput();
            }
        }
        //Check due
        if (!$this->checkDuedate($request->start_date, $userCurrent)) {
            if(!empty($request->isAjax) && $request->isAjax == 1){
                return array('status' => false, 'message' => 'Đơn đăng ký không được muộn quá 3 ngày. (tính từ ngày nhỏ nhất được bổ sung công đến ngày tạo)');
            }else{
                return redirect()->route('manage_time::profile.supplement.register')->withErrors('Đơn đăng ký không được muộn quá 3 ngày. (tính từ ngày nhỏ nhất được bổ sung công đến ngày tạo)');
            }
        }

        if (!empty($request->employee_id)) {
            $employeeId = $request->employee_id;
        } else {
            $employeeId = $userCurrent->id;
        }
        $cbStart = Carbon::parse($request->start_date);
        $cbEnd = Carbon::parse($request->end_date);
        // check phep
        $collections = with(new LeaveDayRegister())->getRegisterExistSupp($employeeId, $cbStart->format('Y-m-d'), $cbEnd->format('Y-m-d'));
        $checkLeaveDay = $this->checkLeaveDays($collections, $cbStart, $cbEnd);
        if ($checkLeaveDay) {
            if(!empty($request->isAjax) && $request->isAjax == 1){
                return array('status' => false, 'message' => $checkLeaveDay);
            }else{
                return redirect()->route('manage_time::profile.supplement.register')->withErrors($checkLeaveDay);
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
            if(!empty($request->isAjax) && $request->isAjax == 1){
                return array('status' => false, 'message' => 'Không thể tạo, sửa, duyệt đơn sau khi bảng công đã bị khóa!');
            }else{
                return redirect()->route('manage_time::profile.supplement.register')->withErrors('Không thể tạo, sửa, duyệt đơn sau khi bảng công đã bị khóa!');
            }
        }
        DB::beginTransaction();
        try {
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $registerRecord = new SupplementRegister;
            $registerRecord->creator_id = $employeeId;
            $registerRecord->approver_id = $request->approver;
            $registerRecord->date_start = Carbon::parse($startDate);
            $registerRecord->date_end = Carbon::parse($endDate);
            $registerRecord->number_days_supplement = $request->number_days_off;
            $registerRecord->status = SupplementRegister::STATUS_UNAPPROVE;
            $registerRecord->is_ot = $request->is_ot ? $request->is_ot : 0;
            $registerRecord->reason_id = $request->reason_id;
            $registerRecord->reason = SupplementRegister::setReason($registerRecord, $request->reason);
            $data = [];

            if ($registerRecord->save()) {
                $registerTeam = [];
                $teamsOfRegistrant = ManageTimeCommon::getTeamsOfEmployee($employeeId);
                foreach ($teamsOfRegistrant as $team) {
                    $registerTeam[] = array('register_id' => $registerRecord->id, 'team_id' => $team->id, 'role_id' => $team->role_id);
                }
                SupplementTeam::insert($registerTeam);

                $registerRecordNew = SupplementRegister::getInformationRegister($registerRecord->id);
                $data['user_mail'] = $userCurrent->email;
                $data['mail_to'] = $registerRecordNew->approver_email;
                $data['mail_title'] = Lang::get('manage_time::view.[Supplement] :name register supplement, from date :start_date to date :end_date', ['name' => $registerRecordNew->creator_name, 'start_date' => Carbon::parse($registerRecordNew->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecordNew->date_end)->format('d/m/Y')]);
                $data['status'] = Lang::get('manage_time::view.Unapprove');
                $data['registrant_name'] = $registerRecordNew->creator_name;
                $data['approver_name'] = $registerRecordNew->approver_name;
                $data['team_name'] = $registerRecordNew->role_name;
                $data['start_date'] = Carbon::parse($registerRecordNew->date_start)->format('d/m/Y');
                $data['start_time'] = Carbon::parse($registerRecordNew->date_start)->format('H:i');
                $data['end_date'] = Carbon::parse($registerRecordNew->date_end)->format('d/m/Y');
                $data['end_time'] = Carbon::parse($registerRecordNew->date_end)->format('H:i');
                $data['reason'] = View::nl2br(ManageTimeCommon::limitText($registerRecordNew->reason, 50));
                $data['link'] = route('manage_time::profile.supplement.detail', ['id' => $registerRecordNew->register_id]);
                $data['to_id'] = $registerRecordNew->approver_id;
                $data['noti_content'] = $data['mail_title'];

                $template = 'manage_time::template.supplement.mail_register.mail_register_to_approver';

                $notificationData = [
                    'category_id' => RkNotify::CATEGORY_TIMEKEEPING
                ];
                ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData);

                $relatedPersons = $request->related_persons_list;

                if (!empty($relatedPersons)) {
                    $comeLateRelaters = [];
                    foreach ($relatedPersons as $key => $value) {
                        $registerRelaters [] = array('register_id' => $registerRecord->id, 'relater_id' => $value);
                    }
                    SupplementRelater::insert($registerRelaters);
                }

                //Insert supplement together
                //  =>>> Comment because supplement one employee
                // $togethers = json_decode($request->table_data_emps, true);
                // dd($togethers);
                // if (count($togethers)) {
                //     $insertTogether = [];
                //     foreach ($togethers as $together) {
                //         $insertTogether[] = [
                //             'supplement_registers_id' => $registerRecord->id,
                //             'employee_id' => $together['empId'],
                //             'start_at' => Carbon::createFromFormat('d-m-Y H:i', $together['startAt'])->format('Y-m-d H:i'),
                //             'end_at' => Carbon::createFromFormat('d-m-Y H:i', $together['endAt'])->format('Y-m-d H:i'),
                //             'number_days' => $request->number_days_off,
                //         ];
                //     }
                // }

                $insertTogether = [
                    "supplement_registers_id" => $registerRecord->id,
                    "employee_id" => $employeeId,
                    "start_at" => $registerRecord->date_start,
                    "end_at" => $registerRecord->date_end,
                    "number_days" => $registerRecord->number_days_supplement
                ];
                SupplementEmployee::insert($insertTogether);

                // create folder
                $structure = base_path('public/storage/manage-time');
                @mkdir($structure, 0777, true);

                $fileUploader = new FileUploader('files', array(
                    'uploadDir' => base_path('public/storage/manage-time/'),
                    'title' => 'name'
                ));
                // call to upload the files
                $data = $fileUploader->upload();
                // if uploaded and success
                if ($data['isSuccess'] && count($data['files']) > 0) {
                    $uploadedFiles = $data['files'];
                }
                // get the fileList
                $fileList = $fileUploader->getFileList();
                if (count($fileList) > 0) {
                    $attachments = [];
                    foreach ($fileList as $key) {
                        $attachments[] = ['register_id' => $registerRecord->id, 'file_name' => $key['title'] . '.' . $key['extension'], 'path' => $key['file'], 'size' => $key['size'], 'mime_type' => $key['type'], 'type' => ManageTimeConst::TYPE_SUPPLEMENT];
                    }
                    ManageTimeAttachment::insert($attachments);
                }
            }
            DB::commit();

            $messages = [
                'success' => [
                    Lang::get('manage_time::message.Register success'),
                ]
            ];

            if(!empty($request->isAjax) && $request->isAjax == 1){
                return array('status' => false, 'message' => $messages['success'][0]);
            }else{
                return redirect()->route('manage_time::profile.supplement.edit', ['id' => $registerRecord->id])->with('messages', $messages);
            }
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            $messages = [
                'errors' => [
                    $ex->getMessage(),
                ]
            ];
            return redirect()->back()->withInput()->with('messages', $messages);
        }
    }

    public function checkDuedate($startDate, $userCurrent)
    {
        $startDate = Carbon::parse($startDate)->format('Y-m-d');
        $now = Carbon::now()->format('Y-m-d');
        $objTeam = new Team();
        $empBranchCode = $objTeam->getBranchPrefixByEmpId($userCurrent->id);
        $holidays = CoreConfigData::getSpecialHolidays(2, $empBranchCode);
        $isDue = ManageTimeView::isDueDate($startDate, $now, $holidays);
        if ($isDue) {
            return false;
        }
        return true;
    }

    /**
     * [supplementUpdateRegister: update register]
     * @param  Request $request
     * @return [view]
     */
    public function supplementUpdateRegister(Request $request)
    {
        $userCurrent = Permission::getInstance()->getEmployee();

        $registerRecord = SupplementRegister::getInformationRegister($request->register_id);
        $registerId = $request->register_id;
        if (!$registerRecord) {
            return redirect()->route('manage_time::profile.supplement.register-list')->withErrors(Lang::get('team::messages.Not found item.'));
        }

        if ($userCurrent->id != $registerRecord->creator_id && !SupplementPermission::allowCreateEditOther()) {
            View::viewErrorPermission();
        }

        if ($registerRecord->status == SupplementRegister::STATUS_APPROVED) {
            return redirect()->route('manage_time::profile.supplement.edit', ['id' => $registerId])->withErrors(Lang::get('manage_time::message.The register of supplement has been approved can not edit'));
        }

        if ($registerRecord->status == SupplementRegister::STATUS_CANCEL) {
            return redirect()->route('manage_time::profile.supplement.edit', ['id' => $registerId])->withErrors(Lang::get('manage_time::message.The register of supplement has been canceled can not edit'));
        }

        $rules = self::rules();
        $messages = self::messages();
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return redirect()->route('manage_time::profile.supplement.edit', ['id' => $registerId])
                ->withErrors($validator)
                ->withInput();
        }
        // check edit time & duedate
        $oldStartDate = Carbon::parse($registerRecord->date_start)->format('d-m-Y H:i');
        if ($oldStartDate != $request->start_date) {
            if (!$this->checkDuedate($request->start_date, $userCurrent)) {
                return redirect()->back()->withErrors('Đơn đăng ký không được muộn quá 3 ngày. (tính từ ngày nhỏ nhất được bổ sung công đến ngày tạo)');
            }
        }

        $cbStart = Carbon::parse($request->start_date);
        $cbEnd = Carbon::parse($request->end_date);
        // check phep
        $collections = with(new LeaveDayRegister())->getRegisterExistSupp($registerRecord->creator_id, $cbStart->format('Y-m-d'), $cbEnd->format('Y-m-d'));
        $checkLeaveDay = $this->checkLeaveDays($collections, $cbStart, $cbEnd);
        if ($checkLeaveDay) {
            return redirect()->route('manage_time::profile.supplement.edit', ['id' => $registerId])->withErrors($checkLeaveDay);
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
            return redirect()->back()->withErrors("Không thể tạo, sửa, duyệt đơn sau khi bảng công đã bị khóa!");
        }
        DB::beginTransaction();
        try {
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $registerRecord->approver_id = $request->approver;
            $registerRecord->date_start = Carbon::parse($startDate);
            $registerRecord->date_end = Carbon::parse($endDate);
            $registerRecord->number_days_supplement = $request->number_days_off;
            $registerRecord->status = SupplementRegister::STATUS_UNAPPROVE;
            $registerRecord->is_ot = $request->is_ot ? $request->is_ot : 0;
            $registerRecord->reason_id = $request->reason_id;
            $registerRecord->reason = SupplementRegister::setReason($registerRecord, $request->reason);

            $data = [];

            if ($registerRecord->save()) {
                SupplementTeam::where('register_id', $registerId)->delete();
                $registerTeam = [];
                $teamsOfRegistrant = ManageTimeCommon::getTeamsOfEmployee($registerRecord->creator_id);
                foreach ($teamsOfRegistrant as $team) {
                    $registerTeam[] = array('register_id' => $registerId, 'team_id' => $team->id, 'role_id' => $team->role_id);
                }
                SupplementTeam::insert($registerTeam);

                $registerRecordNew = SupplementRegister::getInformationRegister($registerRecord->id);
                $data['user_mail'] = $userCurrent->email;
                $data['mail_to'] = $registerRecordNew->approver_email;
                $data['mail_title'] = Lang::get('manage_time::view.[Notification][Supplement] :name register supplement, from date :start_date to date :end_date', ['name' => $registerRecordNew->creator_name, 'start_date' => Carbon::parse($registerRecordNew->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecordNew->date_end)->format('d/m/Y')]);
                $data['status'] = Lang::get('manage_time::view.Unapprove');
                $data['registrant_name'] = $registerRecordNew->creator_name;
                $data['approver_name'] = $registerRecordNew->approver_name;
                $data['team_name'] = $registerRecordNew->role_name;
                $data['start_date'] = Carbon::parse($registerRecordNew->date_start)->format('d/m/Y');
                $data['start_time'] = Carbon::parse($registerRecordNew->date_start)->format('H:i');
                $data['end_date'] = Carbon::parse($registerRecordNew->date_end)->format('d/m/Y');
                $data['end_time'] = Carbon::parse($registerRecordNew->date_end)->format('H:i');
                $data['reason'] = View::nl2br(ManageTimeCommon::limitText($registerRecordNew->reason, 50));
                $data['link'] = route('manage_time::profile.supplement.detail', ['id' => $registerRecordNew->register_id]);
                $data['to_id'] = $registerRecordNew->approver_id;
                $data['noti_content'] = $data['mail_title'];

                $template = 'manage_time::template.supplement.mail_register.mail_register_to_approver';
                $notificationData = [
                    'category_id' => RkNotify::CATEGORY_TIMEKEEPING
                ];
                ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData);
                SupplementRelater::where('register_id', $registerId)->delete();
                $relatedPersons = $request->related_persons_list;
                if (!empty($relatedPersons)) {
                    $comeLateRelaters = [];
                    foreach ($relatedPersons as $key => $value) {
                        $registerRelaters [] = array('register_id' => $registerRecord->id, 'relater_id' => $value);
                    }
                    SupplementRelater::insert($registerRelaters);
                }

                //Insert supplement together
                SupplementEmployee::removeAllEmp($registerRecord->id);
                $togethers = json_decode($request->table_data_emps, true);
                if (count($togethers)) {
                    $insertTogether = [];
                    foreach ($togethers as $together) {
                        $insertTogether[] = [
                            'supplement_registers_id' => $registerRecord->id,
                            'employee_id' => $together['empId'],
                            'start_at' => Carbon::createFromFormat('d-m-Y H:i', $together['startAt'])->format('Y-m-d H:i'),
                            'end_at' => Carbon::createFromFormat('d-m-Y H:i', $together['endAt'])->format('Y-m-d H:i'),
                            'number_days' => $registerRecord->number_days_supplement,
                        ];
                    }
                    SupplementEmployee::insert($insertTogether);
                }

                //Creat folder ticket
                $structure = base_path('public/storage/manage-time');
                @mkdir($structure, 0777, true);

                $attachmentsList = ManageTimeAttachment::getAttachments($registerId, ManageTimeConst::TYPE_SUPPLEMENT);

                $appendedFiles = [];
                foreach ($attachmentsList as $file) {
                    $appendedFiles[] = [
                        'attachment_id' => $file->attachment_id,
                        'name' => $file->file_name,
                        'type' => $file->mime_type,
                        'size' => $file->size,
                        'file' => url($file->path),
                        'path' => $file->path,
                        'uploaded' => false,
                        'data' => ['url' => url($file->path)]
                    ];
                }

                $fileUploader = new FileUploader('files', array(
                    'uploadDir' => base_path('public/storage/manage-time/'),
                    'title' => 'name',
                    'files' => $appendedFiles
                ));

                // call to upload the files
                $data = $fileUploader->upload();

                // if uploaded and success
                if ($data['isSuccess'] && count($data['files']) > 0) {
                    $uploadedFiles = $data['files'];
                }

                // unlink the files
                // !important only for appended files
                // you will need to give the array with appendend files in 'files' option of the FileUploader
                foreach ($fileUploader->getRemovedFiles('file') as $key => $value) {
                    $attachment = ManageTimeAttachment::find($value['attachment_id']);
                    $attachment->delete();
                    unlink(base_path('public/') . $value['path']);
                }

                // get the fileList
                $fileList = $fileUploader->getFileList();
                if (count($fileList) > 0) {
                    $attachments = [];
                    foreach ($fileList as $key) {
                        if ($key['uploaded']) {
                            $attachments[] = ['register_id' => $registerRecord->id, 'file_name' => $key['title'] . '.' . $key['extension'], 'path' => $key['file'], 'size' => $key['size'], 'mime_type' => $key['type'], 'type' => ManageTimeConst::TYPE_SUPPLEMENT];
                        }
                    }
                    ManageTimeAttachment::insert($attachments);
                }
            }

            DB::commit();

            $messages = [
                'success' => [
                    Lang::get('manage_time::message.Update success'),
                ]
            ];

            return redirect()->route('manage_time::profile.supplement.edit', ['id' => $registerId])->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex->getMessage());
            $messages = [
                'errors' => [
                    $ex->getMessage(),
                ]
            ];
            return redirect()->back()->withInput()->with('messages', $messages);
        }
    }

    /**
     * [supplementDeleteRegister: delete register]
     * @param  Request $request
     * @return [json]
     */
    public function supplementDeleteRegister(Request $request)
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        $urlCurrent = $request->urlCurrent;
        $registerId = $request->registerId;
        $registerRecord = SupplementRegister::find($registerId);
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

        if ($userCurrent->id != $registerRecord->creator_id && !SupplementPermission::allowCreateEditOther()) {
            $messages = [
                'errors' => [
                    Lang::get('manage_time::message.You do not have permission to delete this object'),
                ]
            ];

            $request->session()->flash('messages', $messages);
            echo json_encode(['url' => $urlCurrent]);
            return;
        }

        if ($registerRecord->status == SupplementRegister::STATUS_APPROVED) {
            $messages = [
                'errors' => [
                    Lang::get('manage_time::message.The register of supplement has been approved cannot delete'),
                ]
            ];

            $request->session()->flash('messages', $messages);
            echo json_encode(['url' => $urlCurrent]);
            return;
        }

        $registerRecord->status = SupplementRegister::STATUS_CANCEL;
        $registerRecord->save();
        $registerRecord->delete();

        $messages = [
            'success' => [
                Lang::get('manage_time::message.The register of supplement has delete success'),
            ]
        ];

        $request->session()->flash('messages', $messages);
        echo json_encode(['url' => $urlCurrent]);
    }

    /**
     * [supplementApproveRegister: approve register]
     * @param  Request $request
     * @return [json]
     */
    public function supplementApproveRegister(Request $request)
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        $urlCurrent = $request->urlCurrent;
        $arrRegisterId = explode(',', $request->registerId);
        $listRegisterIdApprove = SupplementRegister::getRegisterByStatus($arrRegisterId, null);

        // check exits
        $messages = with(new ManageTimeView())->checkOverlap($arrRegisterId, ManageTimeView::STR_SUPPLEMENT);

        if (count($listRegisterIdApprove) && !count($messages)) {
            $dataSuppTK = [];
            foreach ($listRegisterIdApprove as $registerId) {
                $registerRecord = SupplementRegister::getInformationRegister($registerId);
                $registerRecord->status = SupplementRegister::STATUS_APPROVED;
                $registerRecord->approver_id = $userCurrent->id;
                $cbStart = Carbon::parse($registerRecord->date_start);
                $cbEnd = Carbon::parse($registerRecord->date_end);
                // check phep
                $collections = with(new LeaveDayRegister())->getRegisterExistSupp($registerRecord->creator_id, $cbStart->format('Y-m-d'), $cbEnd->format('Y-m-d'));
                $checkLeaveDay = $this->checkLeaveDays($collections, $cbStart, $cbEnd);
                if ($checkLeaveDay) {
                    $messages = Lang::get('manage_time::view.Registrant') . ' ' . $registerRecord->creator_name . ' ' . Lang::get('manage_time::view.From date') . ' ' . $cbStart->format('Y-m-d') . ' ' . Lang::get('manage_time::view.End date') . ' ' . $cbEnd->format('Y-m-d') . ' ';
                    $request->session()->flash('messages', ['errors' => [$messages . $checkLeaveDay]]);
                    echo json_encode(['url' => $urlCurrent]);
                    return ;
                }
                $registerRecord->approved_at = Carbon::now();
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
                    echo json_encode(['url' => $urlCurrent]);
                    return ;
                }

                $data = [];
                if ($registerRecord->save()) {
                    $data['user_mail'] = $userCurrent->email;
                    $data['mail_to'] = $registerRecord->creator_email;
                    $data['mail_title'] = Lang::get('manage_time::view.[Approved][Supplement] :name register supplement, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
                    $data['status'] = Lang::get('manage_time::view.Approved');
                    $data['registrant_name'] = $registerRecord->creator_name;
                    $data['team_name'] = $registerRecord->role_name;
                    $data['start_date'] = Carbon::parse($registerRecord->date_start)->format('d/m/Y');
                    $data['start_time'] = Carbon::parse($registerRecord->date_start)->format('H:i');
                    $data['end_date'] = Carbon::parse($registerRecord->date_end)->format('d/m/Y');
                    $data['end_time'] = Carbon::parse($registerRecord->date_end)->format('H:i');
                    $data['reason'] = View::nl2br(ManageTimeCommon::limitText($registerRecord->reason, 50));
                    $data['link'] = route('manage_time::profile.supplement.detail', ['id' => $registerRecord->register_id]);
                    $data['approver_name'] = $registerRecord->approver_name;
                    $data['approver_position'] = '';
                    $approver = $registerRecord->getApproverInformation();
                    if ($approver) {
                        $data['approver_position'] = $approver->approver_position;
                    }
                    $data['to_id'] = $registerRecord->creator_id;
                    $data['noti_content'] = trans('manage_time::view.The register of supplement has been considered:') . ' ' . $data['status'];

                    $template = 'manage_time::template.supplement.mail_approve.mail_approve_to_registrant';
                    $notificationData = [
                        'category_id' => RkNotify::CATEGORY_TIMEKEEPING
                    ];
                    if (ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData)) {
                       $relatedPersons = SupplementRelater::getRelatedPersons($registerRecord->id);
                       if (count($relatedPersons)) {
                           $data['mail_title'] = Lang::get('manage_time::view.[Notification][Supplement] :name register supplement, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
                           foreach ($relatedPersons as $item) {
                               $data['mail_to'] = $item->relater_email;
                               $data['related_person_name'] = $item->relater_name;
                               $template = 'manage_time::template.supplement.mail_approve.mail_approve_to_related_person';
                               ManageTimeCommon::pushEmailToQueue($data, $template);
                           }
                           \RkNotify::put(
                               $relatedPersons->lists('relater_id')->toArray(),
                               trans('manage_time::view.The register of supplement of :registrant_name, :team_name related to you is considered:', $data).' '.$data['status'],
                               $data['link'],
                               $notificationData
                           );
                       }
                    }
                    $dataSuppTK[] = $registerRecord;
                }
            }
            // ==== save timekeeping ===
            $this->insertTimekeeping($userCurrent, $dataSuppTK);
            $messages = [
                'success' => [
                    Lang::get('manage_time::message.The register of supplement has approve success'),
                ]
            ];
        }
        $request->session()->flash('messages', $messages);
        echo json_encode(['url' => $urlCurrent]);
    }

    /**
     * [supplementDisapproveRegister: approve register]
     * @param  Request $request
     * @return [json]
     */
    public function supplementDisapproveRegister(Request $request)
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

        $listRegisterIdDisapprove = SupplementRegister::getRegisterByStatus($arrRegisterId, null);

        if (count($listRegisterIdDisapprove)) {
            $dataSuppTK = [];
            foreach ($listRegisterIdDisapprove as $registerId) {
                $registerRecord = SupplementRegister::getInformationRegister($registerId);
                $registerRecord->status = SupplementRegister::STATUS_DISAPPROVE;
                $registerRecord->approver_id = $userCurrent->id;
                $registerRecord->approved_at = Carbon::now();

                $data = [];
                if ($registerRecord->save()) {
                    $registerComment = new ManageTimeComment;
                    $registerComment->register_id = $registerId;
                    $registerComment->comment = $reasonDisapprove;
                    $registerComment->type = ManageTimeConst::TYPE_SUPPLEMENT;
                    $registerComment->created_by = $userCurrent->id;
                    $registerComment->save();

                    $data['user_mail'] = $userCurrent->email;
                    $data['mail_to'] = $registerRecord->creator_email;
                    $data['mail_title'] = Lang::get('manage_time::view.[Unapproved][Supplement] :name register supplement, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
                    $data['status'] = Lang::get('manage_time::view.Unapprove');
                    $data['registrant_name'] = $registerRecord->creator_name;
                    $data['team_name'] = $registerRecord->role_name;
                    $data['start_date'] = Carbon::parse($registerRecord->date_start)->format('d/m/Y');
                    $data['start_time'] = Carbon::parse($registerRecord->date_start)->format('H:i');
                    $data['end_date'] = Carbon::parse($registerRecord->date_end)->format('d/m/Y');
                    $data['end_time'] = Carbon::parse($registerRecord->date_end)->format('H:i');
                    $data['reason'] = View::nl2br(ManageTimeCommon::limitText($registerRecord->reason, 50));
                    $data['reason_disapprove'] = View::nl2br(ManageTimeCommon::limitText($reasonDisapprove, 50));
                    $data['link'] = route('manage_time::profile.supplement.detail', ['id' => $registerRecord->register_id]);
                    $data['to_id'] = $registerRecord->creator_id;
                    $data['noti_content'] = trans('manage_time::view.The register of supplement has been considered:') . ' ' . $data['status'];

                    $template = 'manage_time::template.supplement.mail_disapprove.mail_disapprove_to_registrant';
                    $notificationData = [
                        'category_id' => RkNotify::CATEGORY_TIMEKEEPING
                    ];
                    ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData);
                    $dataSuppTK[] = $registerRecord;
                }
            }
            // ==== save timekeeping ===
            $this->insertTimekeeping($userCurrent, $dataSuppTK);
        }

        $messages = [
            'success' => [
                Lang::get('manage_time::message.The register of supplement has disapprove success'),
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
                $checkRegisterExist = SupplementRegister::checkRegisterExist($emp['empId'], Carbon::parse($emp['startAt']), Carbon::parse($emp['endAt']), $registerId, $request->isOt);
                if (count($checkRegisterExist)) {
                    $arrEmpExist[] = [
                        'empId' => $emp['empId'],
                        'empCode' => $checkRegisterExist->employee_code,
                        'empName' => $checkRegisterExist->name,
                        'url' => route('manage_time::profile.supplement.edit', ['id' => $checkRegisterExist->supplement_registers_id]),
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
            'approver' => 'required',
            'start_date' => 'required',
            'end_date' => 'required|after:start_date',
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
            'end_date.after' => Lang::get('manage_time::view.The end date at must be after start date'),
            'reason.required' => Lang::get('manage_time::view.The reason field is required'),
        ];

        return $messages;
    }

    /**
     * Get approver for register
     * @param  Request $request
     * @return [json]
     */
    public function ajaxGetApprover(Request $request)
    {
        $employeeId = $request->get('employeeId');
        $params = [
            'approversList' => ManageTimeCommon::getApproversForEmployee($employeeId),
        ];
        $view = 'manage_time::supplement.opption_approver';
        $html = view($view)->with($params)->render();

        $teamCode = Team::getOnlyOneTeamCodePrefix($employeeId);
        $objWTView = new WorkingTimeView();
        $period = [
            'start_date' => Carbon::parse($request->get('start_at'))->toDateString(),
            'end_date' => Carbon::parse($request->get('end_at'))->toDateString(),
        ];
        $workingTime = $objWTView->getWorkingTimeByEmployeeBetween($employeeId, $teamCode, $period);
        $timeSetting = $workingTime['timeSetting'];

        return response()->json([
            'html' => $html,
            'timeSettingNew' => $timeSetting,
            'empProjects' => static::getEmpProject($employeeId),
        ]);
    }

    /**
     * get information employee in project register time working 18h
     * @param  [int] $empId
     * @return [type]
     */
    public static function getEmpProject($empId)
    {
        $empProjects = OtRegister::getProjectsbyEmployee($empId, false);
        $projectAllowedOT18Key = unserialize(CoreConfigData::getValueDb('project.ot.18h'));

        foreach ($empProjects as $key => $emp) {
            if (!in_array($emp->project_id, $projectAllowedOT18Key)) {
                unset($empProjects[$key]);
            }
        }
        return array_values($empProjects);
    }

    /**
     * check BSC va phep trong truong hop dac biet
     * @param  [collections] $collections
     * @param  [carbon] $cbStart
     * @param  [carbon] $cbEnd
     * @return [string]
     */
    private function checkLeaveDays($collections, $cbStart, $cbEnd)
    {
        $teamCode = [];
        $objView = new ManageTimeView();
        foreach ($collections as $item) {
            if ($item->number_days_off < ManageTimeConst::TIME_MORE_HALF) {
                continue;
            }
            $cbDateStart = Carbon::parse($item->date_start);
            $cbDateEnd = Carbon::parse($item->date_end);

            if (!array_key_exists($item->creator_id, $teamCode)) {
                $teamCode[$item->creator_id] = Team::getOnlyOneTeamCodePrefix(Employee::find($item->creator_id));
            }
            $dateStart = $objView->timeSettingEmployee($item->creator_id, $cbDateStart->format('Y-m-d'), $teamCode[$item->creator_id]);
            $dateEnd = $objView->timeSettingEmployee($item->creator_id, $cbDateEnd->format('Y-m-d'), $teamCode[$item->creator_id]);

            if($cbDateStart->format('H') > $dateEnd['morningInSetting']['hour'] &&
                $cbDateStart->format('H') < $dateEnd['morningOutSetting']['hour']) {
                if ($cbStart->format('Y-m-d') == $cbDateStart->format('Y-m-d') &&
                    $cbStart->format('H') == $dateEnd['afternoonInSetting']['hour']) {
                    return Lang::get('manage_time::message.BSC day :day must is morning or full time', ['day' => $cbStart->format('Y-m-d')]);
                }
            }
            if ($cbDateEnd->format('H') < $dateEnd['afternoonOutSetting']['hour'] &&
                $cbDateEnd->format('H') > $dateEnd['afternoonInSetting']['hour']) {
                if ($cbEnd->format('Y-m-d') == $cbDateEnd->format('Y-m-d') &&
                    $cbEnd->format('H') == $dateEnd['morningOutSetting']['hour']) {
                    return Lang::get('manage_time::message.Supplement day :day must is afternoon or full time', ['day' => $cbEnd->format('Y-m-d')]);
                }
            }
        }
        return '';
    }

    public function supplementRelatesList($status = null)
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];

        $collectionModel = SupplementRegister::getListRegisters(null, null, $status, $dataFilter, $userCurrent->id);

        $params = [
            'collectionModel' => $collectionModel,
            'status' => $status,
            'isRelates' => true
        ];

        return view('manage_time::supplement.supplement_approve_list', $params);
    }

    /**
     * getTimekeeping
     *
     * @param  date $dateStart Y-m-d
     * @param  date $dateEnd Y-m-d
     * @param  array $empIds
     * @param  string $teamCodePrefix
     * @return array
     */
    public function getTimekeeping($dateStart, $dateEnd, $empIds, $teamCodePrefix)
    {
        $objViewTimeKeeping = new ViewTimeKeeping();
        return $objViewTimeKeeping->getTimekeeping($dateStart, $dateEnd, $empIds, $teamCodePrefix);
    }
        
    /**
     * insertTimekeeping
     *
     * @param  collection $userCurrent
     * @param  array $dataSuppTK
     * @return void
     */
    public function insertTimekeeping($userCurrent, $dataSuppTK)
    {
        $objView = new ManageTimeView();
        return $objView->insertTimekeeping($userCurrent, $dataSuppTK);
    }
}
