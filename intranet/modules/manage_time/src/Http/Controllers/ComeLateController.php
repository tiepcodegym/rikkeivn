<?php

namespace Rikkei\ManageTime\Http\Controllers;

use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Lang;
use Log;
use Response;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\View;
use Rikkei\ManageTime\Model\ComeLateDayWeek;
use Rikkei\ManageTime\Model\ComeLateRegister;
use Rikkei\ManageTime\Model\ComeLateRelater;
use Rikkei\ManageTime\Model\ComeLateTeam;
use Rikkei\ManageTime\Model\ManageTimeAttachment;
use Rikkei\ManageTime\Model\ManageTimeComment;
use Rikkei\ManageTime\Model\TimekeepingNotLate;
use Rikkei\ManageTime\Model\TimekeepingNotLateTime;
use Rikkei\ManageTime\View\ComeLatePermission;
use Rikkei\ManageTime\View\FileUploader;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\ManageTime\View\ManageTimeConst;
use Rikkei\ManageTime\View\ViewTimeKeeping;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Permission;

class ComeLateController extends Controller
{
    /**
     * [comelateRegister: view form register]
     * @return [view]
     */
    public function comelateRegister()
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('Late in early out');
        Menu::setActive('Profile');

        $userCurrent = Permission::getInstance()->getEmployee();
        $approversList = ManageTimeCommon::getApproversForEmployee($userCurrent->id);
        $registrantInformation = ManageTimeCommon::getRegistrantInformation($userCurrent->id);

        $params = [
            'approversList' => $approversList,
            'registrantInformation' => $registrantInformation,
            'suggestApprover' => ManageTimeCommon::suggestApprover(ManageTimeConst::TYPE_COMELATE, $userCurrent),
        ];

        return view('manage_time::comelate.comelate_register', $params);
    }

    /**
     * view form register for permission
     * @return [view]
     */
    public function adminRegister()
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('Late in early out');
        Menu::setActive('Profile');

        if (!ComeLatePermission::allowCreateEditOther()) {
            View::viewErrorPermission();
        }
        return view('manage_time::comelate.comelate_admin_register');
    }

    /**
     * [comelateRegisterList: view registers list of registrant]
     * @param  [int|null] $status
     * @return [view]
     */
    public function comelateRegisterList($status = null)
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('Late in early out');
        Menu::setActive('Profile');

        $userCurrent = Permission::getInstance()->getEmployee();
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];

        $collectionModel = ComeLateRegister::getListRegisters($userCurrent->id, null, $status, $dataFilter);
        
        $params = [
            'collectionModel' => $collectionModel,
            'status'          => $status
        ];

        return view('manage_time::comelate.comelate_register_list', $params);
    }

    /**
     * [comelateApproveList: view approves list of approver]
     * @param  [int|null] $status
     * @return [view]
     */
    public function comelateApproveList($status = null)
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('Late in early out');
        Menu::setActive('Profile');

        $userCurrent = Permission::getInstance()->getEmployee();
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];

        $isScopeApproveOfSelf = ComeLatePermission::isScopeApproveOfSelf();
        $isScopeApproveOfTeam = ComeLatePermission::isScopeApproveOfTeam();
        $isScopeApproveOfCompany = ComeLatePermission::isScopeApproveOfCompany();
        if ($isScopeApproveOfSelf || $isScopeApproveOfTeam || $isScopeApproveOfCompany) {
            $collectionModel = ComeLateRegister::getListRegisters(null, $userCurrent->id, $status, $dataFilter);
        } else {
            View::viewErrorPermission();
        }
        
        $params = [
            'collectionModel' => $collectionModel,
            'status'          => $status
        ];

        return view('manage_time::comelate.comelate_approve_list', $params);
    }

    /**
     * [comelateManageList: view register list of manager]
     * @param  [int] $id
     * @return [view]
     */
    public function comelateManageList($id = null)
    {
        Breadcrumb::add('HR');
        Breadcrumb::add('Manage time');
        Breadcrumb::add('Late in early out');
        Menu::setActive('HR');

        $userCurrent = Permission::getInstance()->getEmployee();
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];

        $teamIdsAvailable = null;
        $teamTreeAvailable = [];

        $isScopeManageOfTeam = ComeLatePermission::isScopeManageOfTeam();
        $isScopeManageOfCompany = ComeLatePermission::isScopeManageOfCompany();

        if ($isScopeManageOfCompany) {
            $teamIdsAvailable = true;
        } elseif ($isScopeManageOfTeam) {
            $teamIdsAvailable = (array) ManageTimeCommon::getTeamIdIsScopeTeam($userCurrent->id, 'manage_time::manage-time.manage.view');
            if (! $teamIdsAvailable) {
                View::viewErrorPermission();
            }
            
            foreach ($teamIdsAvailable as $key => $teamId) {
                if (! ComeLatePermission::isScopeManageOfTeam($teamId)) {
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

        $collectionModel = ComeLateRegister::getListManageRegisters($id, $dataFilter);
        
        $params = [
            'collectionModel'   => $collectionModel,
            'teamIdCurrent'     => $id,
            'teamIdsAvailable'  => $teamIdsAvailable,
            'teamTreeAvailable' => $teamTreeAvailable,
        ];

        return view('manage_time::comelate.comelate_manage_list', $params);
    }

    /**
     * [comelateEditRegister: view form edit register]
     * @param  [int] $registerId
     * @return [view]
     */
    public function comelateEditRegister($registerId)
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('Late in early out');
        Menu::setActive('Profile');

        $userCurrent = Permission::getInstance()->getEmployee();

        $registerRecord = ComeLateRegister::getInformationRegister($registerId);
        if (!$registerRecord) {
            return redirect()->route('manage_time::profile.comelate.register-list')->withErrors(Lang::get('team::messages.Not found item.'));
        }

        if ($userCurrent->id != $registerRecord->creator_id && !ComeLatePermission::allowCreateEditOther()) {
            View::viewErrorPermission();
        }

        $approversList = ManageTimeCommon::getApproversForEmployee($registerRecord->creator_id);
        $relatedPersonsList = ComeLateRelater::getRelatedPersons($registerId);
        $daysApply = ComeLateDayWeek::getDaysApply($registerId);
        $commentsList = ManageTimeComment::getReasonDisapprove($registerId, ManageTimeConst::TYPE_COMELATE);
        $attachmentsList = ManageTimeAttachment::getAttachments($registerId, ManageTimeConst::TYPTE_LATE_IN_EARLY_OUT);

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

        $isAllowEdit = false;
        if ($registerRecord->status != ComeLateRegister::STATUS_APPROVED && $registerRecord->status != ComeLateRegister::STATUS_CANCEL) {
            $isAllowEdit = true;
        }

        $params = [
            'approversList'      => $approversList,
            'registerRecord'     => $registerRecord,
            'relatedPersonsList' => $relatedPersonsList,
            'daysApply'          => $daysApply,
            'commentsList'       => $commentsList,
            'isAllowEdit'        => $isAllowEdit,
            'appendedFiles'      => json_encode($appendedFiles),
            'attachmentsList'    => $attachmentsList
        ];

        return view('manage_time::comelate.comelate_register_edit', $params);
    }

    /**
     * [comelateDetailRegister: view page detail of register]
     * @param  [int] $registerId
     * @return [view]
     */
    public function comelateDetailRegister($registerId)
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('Late in early out');
        Menu::setActive('Profile');

        $userCurrent = Permission::getInstance()->getEmployee();

        $registerRecord = ComeLateRegister::getInformationRegister($registerId);
        if (!$registerRecord) {
            return redirect()->route('manage_time::profile.comelate.approve-list')->withErrors(Lang::get('team::messages.Not found item.'));
        }

        $isAllowView = ComeLatePermission::isAllowView($registerId, $userCurrent->id);
        $attachmentsList = ManageTimeAttachment::getAttachments($registerId, ManageTimeConst::TYPTE_LATE_IN_EARLY_OUT);

        if ($isAllowView) {
            $approversList = ManageTimeCommon::getApproversForEmployee($registerRecord->creator_id);
            $relatedPersonsList = ComeLateRelater::getRelatedPersons($registerId);
            $daysApply = ComeLateDayWeek::getDaysApply($registerId);
            $commentsList = ManageTimeComment::getReasonDisapprove($registerId, ManageTimeConst::TYPE_COMELATE);
        } else {
            View::viewErrorPermission();
        }

        $isAllowApprove = false;
        if (ComeLatePermission::isAllowApprove($registerRecord, $userCurrent->id) && $registerRecord->status != ComeLateRegister::STATUS_CANCEL) {
            $isAllowApprove = true;
        }

        $params = [
            'approversList'      => $approversList,
            'registerRecord'     => $registerRecord,
            'relatedPersonsList' => $relatedPersonsList,
            'daysApply'          => $daysApply,
            'commentsList'       => $commentsList,
            'isAllowApprove'     => $isAllowApprove,
            'attachmentsList'    => $attachmentsList
        ];

        return view('manage_time::comelate.comelate_register_edit', $params);
    }

    /**
     * [comelateRegisterViewPopup: show information of register]
     * @return show detail register to modal
     */
    public function comelateRegisterViewPopup(Request $request)
    {      
        $registerId = $request->registerId;

        $registerRecord = ComeLateRegister::getInformationRegister($registerId);
        $relatedPersonsList = ComeLateRelater::getRelatedPersons($registerId);

        $params = [
            'registerRecord'   => $registerRecord,
            'relatedPersonsList'   => $relatedPersonsList,
        ];

        echo view('manage_time::include.modal.modal_view_comelate', $params);
    }

    /**
     * Save register by admin
     * @param  Request $request
     * @return [type]
     */
    public function saveAdminRegister(Request $request)
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        if (!ComeLatePermission::allowCreateEditOther()) {
            View::viewErrorPermission();
        }
        $rules = [
            'employee_id'      => 'required',
            'start_date'       => 'required',
            'end_date'         => 'required',
            'late_start_shift' => 'integer|max:120',
            'early_mid_shift'  => 'integer|max:120',
            'late_mid_shift'   => 'integer|max:120',
            'early_end_shift'  => 'integer|max:120',
            'reason'           => 'required',
        ];
        $messages = [
            'employee_id.required'     => Lang::get('manage_time::view.The registrant field is required'),
            'start_date.required'      => Lang::get('manage_time::view.The start date field is required'),
            'end_date.required'        => Lang::get('manage_time::view.The end date field is required'),
            'late_start_shift.integer' => Lang::get('manage_time::view.The late start shift field must be an integer'),
            'late_start_shift.max' => Lang::get('manage_time::view.Please enter on the late start shift field a time between 1 minute and 120 minutes'),
            'early_mid_shift.integer'  => Lang::get('manage_time::view.The early mid shift field must be an integer'),
            'early_mid_shift.max'  => Lang::get('manage_time::view.Please enter on the early mid shift field a time between 1 minute and 120 minutes'),
            'late_mid_shift.integer'   => Lang::get('manage_time::view.The late mid shift field must be an integer'),
            'late_mid_shift.max'   => Lang::get('manage_time::view.Please enter on the late mid shift field a time between 1 minute and 120 minutes'),
            'early_end_shift.integer'  => Lang::get('manage_time::view.The early end shift field must be an integer'),
            'early_end_shift.max'  => Lang::get('manage_time::view.Please enter on the early end shift field a time between 1 minute and 120 minutes'),
            'reason.required'          => Lang::get('manage_time::view.The reason field is required'),
        ];
        if (empty($request->late_start_shift) && empty($request->early_mid_shift) && empty($request->late_mid_shift) && empty($request->early_end_shift)) {
            $rules['time'] = 'required';
            $messages['time.required'] = Lang::get('manage_time::view.You must enter a time for at least one of the following fields: late start shift field or early mid shift field or late mid shift field or early end shift field');
        }
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }
        DB::beginTransaction();
        try {
            $employeeId = $request->employee_id;
            $startDate = Carbon::createFromFormat('d-m-Y', $request->start_date)->toDateString();
            $endDate = Carbon::createFromFormat('d-m-Y', $request->end_date)->toDateString();
            $checkRegisterExist = ComeLateRegister::checkRegisterExist($employeeId, $startDate, $endDate);
            if ($checkRegisterExist) {
                return redirect()->back()->withErrors(Lang::get('manage_time::message.Registration time has been identical'));
            }
            $registerRecord = new ComeLateRegister;
            $registerRecord->employee_id      = $employeeId;
            $registerRecord->approver         = $userCurrent->id;
            $registerRecord->date_start       = $startDate;
            $registerRecord->date_end         = $endDate;
            $registerRecord->late_start_shift = $request->late_start_shift;
            $registerRecord->early_mid_shift  = $request->early_mid_shift;
            $registerRecord->late_mid_shift   = $request->late_mid_shift;
            $registerRecord->early_end_shift  = $request->early_end_shift;
            $registerRecord->reason           = $request->reason;
            $registerRecord->status           = ComeLateRegister::STATUS_APPROVED;

            $data = [];

            if ($registerRecord->save()) {
                $registerTeam = [];
                $teamsOfRegistrant = ManageTimeCommon::getTeamsOfEmployee($employeeId);
                foreach ($teamsOfRegistrant as $team) {
                    $registerTeam[] = array('come_late_id' => $registerRecord->id, 'team_id'=> $team->id, 'role_id' => $team->role_id);
                }
                ComeLateTeam::insert($registerTeam);

                $allDays = $request->all_day;
                $registerDaysWeek = [];
                if ($allDays) {
                    $allDaysHidden = explode(',', $request->all_day_hidden);

                    foreach ($allDaysHidden as $key => $value) {
                        $registerDaysWeek [] = array('come_late_id' => $registerRecord->id, 'day'=> $value);
                    }

                    ComeLateDayWeek::insert($registerDaysWeek);
                } else {
                    $comeLateDays = $request->come_late_days;
                    if (!empty($comeLateDays)) {
                        foreach ($comeLateDays as $key => $value) {
                            $registerDaysWeek [] = array('come_late_id' => $registerRecord->id, 'day'=> $value);
                        }
                        ComeLateDayWeek::insert($registerDaysWeek);
                    }
                }
            }
            self::inserstFile($registerRecord, 'comelate');
            DB::commit();

            $messages = [
                'success'=> [
                    Lang::get('manage_time::message.Register success'),
                ]
            ];

            return redirect()->route('manage_time::profile.comelate.edit', ['id' => $registerRecord->id])->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex->getMessage());
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('manage_time::message.An error occurred')]]);
        }
    }

    /**
     * [comelateSaveRegister: save register]
     * @param  Request $request
     * @return [view]
     */
    public function comelateSaveRegister(Request $request)
    {
        $userCurrent = Permission::getInstance()->getEmployee();

        $rules = self::rules();
        $messages = self::messages();
        if (empty($request->late_start_shift) && empty($request->early_mid_shift) && empty($request->late_mid_shift) && empty($request->early_end_shift)) {
            $rules['time'] = 'required';
            $messages['time.required'] = Lang::get('manage_time::view.You must enter a time for at least one of the following fields: late start shift field or early mid shift field or late mid shift field or early end shift field');
        }
        if (!empty($request->admin)) {
            $rules['employee_id'] = 'required';
            $messages['employee_id.required'] = Lang::get('manage_time::view.The registrant field is required');
        }
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return redirect()->route('manage_time::profile.comelate.register')
                        ->withErrors($validator)
                        ->withInput();
        }
        
        DB::beginTransaction();
        try {
            if (!empty($request->employee_id)) {
                $employeeId = $request->employee_id;
            } else {
                $employeeId = $userCurrent->id;
            }
            $startDate = Carbon::createFromFormat('d-m-Y', $request->start_date)->toDateString();
            $endDate = Carbon::createFromFormat('d-m-Y', $request->end_date)->toDateString();
            $checkRegisterExist = ComeLateRegister::checkRegisterExist($employeeId, $startDate, $endDate);
            if ($checkRegisterExist) {
                return redirect()->back()->withErrors(Lang::get('manage_time::message.Registration time has been identical'));
            }
            $registerRecord = new ComeLateRegister;
            $registerRecord->employee_id      = $employeeId;
            $registerRecord->approver         = $request->approver;
            $registerRecord->date_start       = $startDate;
            $registerRecord->date_end         = $endDate;
            $registerRecord->late_start_shift = $request->late_start_shift;
            $registerRecord->early_mid_shift  = $request->early_mid_shift;
            $registerRecord->late_mid_shift   = $request->late_mid_shift;
            $registerRecord->early_end_shift  = $request->early_end_shift;
            $registerRecord->reason           = $request->reason;
            $registerRecord->status           = ComeLateRegister::STATUS_UNAPPROVE;

            $data = [];

            if ($registerRecord->save()) {
                $registerTeam = [];
                $teamsOfRegistrant = ManageTimeCommon::getTeamsOfEmployee($employeeId);
                foreach ($teamsOfRegistrant as $team) {
                    $registerTeam[] = array('come_late_id' => $registerRecord->id, 'team_id'=> $team->id, 'role_id' => $team->role_id);
                }
                ComeLateTeam::insert($registerTeam);

                $registerRecordNew = ComeLateRegister::getInformationRegister($registerRecord->id);
                $data['user_mail']        = $userCurrent->email;
                $data['mail_to']          = $registerRecordNew->approver_email;
                $data['mail_cc']          = ComeLateRegister::getDataCcMail($request->related_persons_list);
                $data['mail_title']       = Lang::get('manage_time::view.[Late in early out] :name register late in early out, from date :start_date to date :end_date', ['name' => $registerRecordNew->creator_name, 'start_date' => Carbon::parse($registerRecordNew->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecordNew->date_end)->format('d/m/Y')]);
                $data['status']           = Lang::get('manage_time::view.Unapprove');
                $data['registrant_name']  = $registerRecordNew->creator_name;
                $data['approver_name']    = $registerRecordNew->approver_name;
                $data['team_name']        = $registerRecordNew->role_name;
                $data['start_date']       = Carbon::parse($registerRecordNew->date_start)->format('d/m/Y');
                $data['end_date']         = Carbon::parse($registerRecordNew->date_end)->format('d/m/Y');
                $data['late_start_shift'] = $registerRecordNew->late_start_shift;
                $data['early_mid_shift']  = $registerRecordNew->early_mid_shift;
                $data['late_mid_shift']   = $registerRecordNew->late_mid_shift;
                $data['early_end_shift']  = $registerRecordNew->early_end_shift;
                $data['reason']           = View::nl2br(ManageTimeCommon::limitText($registerRecordNew->reason, 50));
                $data['link']             = route('manage_time::profile.comelate.detail', ['id' => $registerRecordNew->register_id]);
                $data['to_id']            = $registerRecordNew->approver_id;
                $data['noti_content']     = $data['mail_title'];

                $template = 'manage_time::template.comelate.mail_register.mail_register_to_approver';
                $notificationData = [
                    'category_id' => RkNotify::CATEGORY_TIMEKEEPING
                ];
                ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData);

                $relatedPersons = $request->related_persons_list;
                if (!empty($relatedPersons)) {
                    $comeLateRelaters = [];
                    foreach ($relatedPersons as $key => $value) {
                        $registerRelaters [] = array('come_late_id' => $registerRecord->id, 'employee_id'=> $value);
                    }
                    ComeLateRelater::insert($registerRelaters);
                }

                $allDays = $request->all_day;
                $registerDaysWeek = [];
                if($allDays)
                {
                    $allDaysHidden = explode(',', $request->all_day_hidden);

                    foreach ($allDaysHidden as $key => $value) 
                    {
                        $registerDaysWeek [] = array('come_late_id' => $registerRecord->id, 'day'=> $value);
                    }

                    ComeLateDayWeek::insert($registerDaysWeek);
                } else {
                    $comeLateDays = $request->come_late_days;
                    if(!empty($comeLateDays))
                    {
                        foreach ($comeLateDays as $key => $value) 
                        {
                            $registerDaysWeek [] = array('come_late_id' => $registerRecord->id, 'day'=> $value);
                        }
                        
                        ComeLateDayWeek::insert($registerDaysWeek);
                    }
                }
                self::inserstFile($registerRecord, 'comelate');
            }
            DB::commit();

            $messages = [
                'success'=> [
                    Lang::get('manage_time::message.Register success'),
                ]
            ];

            return redirect()->route('manage_time::profile.comelate.edit', ['id' => $registerRecord->id])->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex->getMessage());
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('manage_time::message.An error occurred')]]);
        }
    }

    /**
     * [comelateUpdateRegister: update register]
     * @param  Request $request
     * @return [view]
     */
    public function comelateUpdateRegister(Request $request)
    {
        $userCurrent = Permission::getInstance()->getEmployee();

        $registerRecord = ComeLateRegister::getInformationRegister($request->register_id);
        $registerId = $request->register_id;
        if (!$registerRecord) {
            return redirect()->route('manage_time::profile.comelate.register-list')->withErrors(Lang::get('team::messages.Not found item.'));
        }

        if ($userCurrent->id != $registerRecord->creator_id && !ComeLatePermission::allowCreateEditOther()) {
            View::viewErrorPermission();
        }

        if ($registerRecord->status == ComeLateRegister::STATUS_APPROVED) {
            return redirect()->route('manage_time::profile.comelate.edit', ['id' => $registerId])->withErrors(Lang::get('manage_time::message.The register of late in early out has been approved can not edit'));
        }

        if ($registerRecord->status == ComeLateRegister::STATUS_CANCEL) {
            return redirect()->route('manage_time::profile.comelate.edit', ['id' => $registerId])->withErrors(Lang::get('manage_time::message.The register of late in early out has been canceled can not edit'));
        }

        $rules = self::rules();
        $messages = self::messages();
        if (empty($request->late_start_shift) && empty($request->early_mid_shift) && empty($request->late_mid_shift) && empty($request->early_end_shift)) {
            $rules['time'] = 'required';
            $messages['time.required'] = Lang::get('manage_time::view.You must enter a time for at least one of the following fields: late start shift field or early mid shift field or late mid shift field or early end shift field');
        }
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return redirect()->route('manage_time::profile.comelate.register')
                        ->withErrors($validator)
                        ->withInput();
        }

        DB::beginTransaction();
        try {
            $startDate = Carbon::createFromFormat('d-m-Y', $request->start_date)->toDateString();
            $endDate = Carbon::createFromFormat('d-m-Y', $request->end_date)->toDateString();
            $checkRegisterExist = ComeLateRegister::checkRegisterExist($registerRecord->creator_id, $startDate, $endDate, $registerId);
            if ($checkRegisterExist) {
                return redirect()->back()->withErrors(Lang::get('manage_time::message.Registration time has been identical'));
            }
            $registerRecord->approver         = $request->approver;
            $registerRecord->date_start       = $startDate;
            $registerRecord->date_end         = $endDate;
            $registerRecord->late_start_shift = $request->late_start_shift;
            $registerRecord->early_mid_shift  = $request->early_mid_shift;
            $registerRecord->late_mid_shift   = $request->late_mid_shift;
            $registerRecord->early_end_shift  = $request->early_end_shift;
            $registerRecord->reason           = $request->reason;
            $registerRecord->status           = ComeLateRegister::STATUS_UNAPPROVE;

            $data = [];

            if ($registerRecord->save()) {
                ComeLateTeam::where('come_late_id', $registerId)->delete();
                $registerTeam = [];
                $teamsOfRegistrant = ManageTimeCommon::getTeamsOfEmployee($userCurrent->id);
                foreach ($teamsOfRegistrant as $team) {
                    $registerTeam[] = array('come_late_id' => $registerId, 'team_id'=> $team->id, 'role_id' => $team->role_id);
                }
                ComeLateTeam::insert($registerTeam);

                $registerRecordNew = ComeLateRegister::getInformationRegister($registerRecord->id);
                $data['user_mail']        = $userCurrent->email;
                $data['mail_to']          = $registerRecordNew->approver_email;
                $data['mail_cc']          = ComeLateRegister::getDataCcMail($request->related_persons_list);
                $data['mail_title']       = Lang::get('manage_time::view.[Late in early out] :name register late in early out, from date :start_date to date :end_date', ['name' => $registerRecordNew->creator_name, 'start_date' => Carbon::parse($registerRecordNew->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecordNew->date_end)->format('d/m/Y')]);
                $data['status']           = Lang::get('manage_time::view.Unapprove');
                $data['registrant_name']  = $registerRecordNew->creator_name;
                $data['approver_name']    = $registerRecordNew->approver_name;
                $data['team_name']        = $registerRecordNew->role_name;
                $data['start_date']       = Carbon::parse($registerRecordNew->date_start)->format('d/m/Y');
                $data['end_date']         = Carbon::parse($registerRecordNew->date_end)->format('d/m/Y');
                $data['late_start_shift'] = $registerRecordNew->late_start_shift;
                $data['early_mid_shift']  = $registerRecordNew->early_mid_shift;
                $data['late_mid_shift']   = $registerRecordNew->late_mid_shift;
                $data['early_end_shift']  = $registerRecordNew->early_end_shift;
                $data['reason']           = View::nl2br(ManageTimeCommon::limitText($registerRecordNew->reason, 50));
                $data['link']             = route('manage_time::profile.comelate.detail', ['id' => $registerRecordNew->register_id]);
                $data['to_id']            = $registerRecordNew->approver_id;
                $data['noti_content']     = $data['mail_title'];

                $template = 'manage_time::template.comelate.mail_register.mail_register_to_approver';
                $notificationData = [
                    'category_id' => RkNotify::CATEGORY_TIMEKEEPING
                ];
                ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData);
                ComeLateRelater::where('come_late_id', $registerId)->delete();
                $relatedPersons = $request->related_persons_list;
                if (!empty($relatedPersons)) {
                    $comeLateRelaters = [];
                    foreach ($relatedPersons as $key => $value) {
                        $registerRelaters [] = array('come_late_id' => $registerId, 'employee_id'=> $value);
                    }
                    ComeLateRelater::insert($registerRelaters);
                }

                ComeLateDayWeek::where('come_late_id', $registerId)->delete();
                $allDays = $request->all_day;
                $registerDaysWeek = [];
                if($allDays)
                {
                    $allDaysHidden = explode(',', $request->all_day_hidden);

                    foreach ($allDaysHidden as $key => $value) 
                    {
                        $registerDaysWeek [] = array('come_late_id' => $registerId, 'day'=> $value);
                    }

                    ComeLateDayWeek::insert($registerDaysWeek);
                } else {
                    $comeLateDays = $request->come_late_days;
                    if(!empty($comeLateDays))
                    {
                        foreach ($comeLateDays as $key => $value) 
                        {
                            $registerDaysWeek [] = array('come_late_id' => $registerId, 'day'=> $value);
                        }
                        
                        ComeLateDayWeek::insert($registerDaysWeek);
                    }
                }
            }
            self::updateFile($registerRecord, $registerId, 'comelate');
            DB::commit();

            $messages = [
                'success'=> [
                    Lang::get('manage_time::message.Update success'),
                ]
            ];

            return redirect()->route('manage_time::profile.comelate.edit', ['id' => $registerId])->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex->getMessage());
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('manage_time::message.An error occurred')]]);
        } 
    }

    /**
     * [comelateDeleteRegister: delete register]
     * @param  Request $request
     * @return [json]
     */
    public function comelateDeleteRegister(Request $request)
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        $urlCurrent = $request->urlCurrent;

        $registerId = $request->registerId;
        $registerRecord = ComeLateRegister::find($registerId);
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

        if ($userCurrent->id != $registerRecord->employee_id && !ComeLatePermission::allowCreateEditOther()) {
            $messages = [
                'errors'=> [
                    Lang::get('manage_time::message.You do not have permission to delete this object'),
                ]
            ];

            $request->session()->flash('messages', $messages);
            echo json_encode(['url' => $urlCurrent]);
            return;
        }
        
        if ($registerRecord->status == ComeLateRegister::STATUS_APPROVED) {
            $messages = [
                'errors'=> [
                    Lang::get('manage_time::message.The register of late in early out has been approved cannot delete'),
                ]
            ];

            $request->session()->flash('messages', $messages);
            echo json_encode(['url' => $urlCurrent]);
            return;
        }

        $registerRecord->status = ComeLateRegister::STATUS_CANCEL;
        $registerRecord->save();
        $registerRecord->delete();
        
        $messages = [
            'success'=> [
                Lang::get('manage_time::message.The register of late in early out has delete success'),
            ]
        ];

        $request->session()->flash('messages', $messages);
        echo json_encode(['url' => $urlCurrent]);
    }

    /**
     * [comelateApproveRegister: approve register]
     * @param  Request $request
     * @return [json]
     */
    public function comelateApproveRegister(Request $request)
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        $urlCurrent = $request->urlCurrent;
        $personCc = $request->personList;
        $arrRegisterId = explode(',', $request->registerId);
        $listRegisterIdApprove = ComeLateRegister::getRegisterByStatus($arrRegisterId, null);
        
        if (count($listRegisterIdApprove)) {
            foreach ($listRegisterIdApprove as $registerId) {
                $registerRecord = ComeLateRegister::getInformationRegister($registerId);
                $registerRecord->status = ComeLateRegister::STATUS_APPROVED;
                $registerRecord->approver = $userCurrent->id;
                $data = [];
                if ($registerRecord->save()) {
                    $data['user_mail']        = $userCurrent->email;
                    $data['mail_to']          = $registerRecord->creator_email;
                    $data['mail_cc']          = ComeLateRegister::getDataCcMail($personCc);
                    $data['mail_title']       = Lang::get('manage_time::view.[Approved][Late in early out] :name register late in early out, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
                    $data['status']           = Lang::get('manage_time::view.Approved');
                    $data['registrant_name']  = $registerRecord->creator_name;
                    $data['team_name']        = $registerRecord->role_name;
                    $data['approver_name']    = $registerRecord->approver_name;
                    $data['start_date']       = Carbon::parse($registerRecord->date_start)->format('d/m/Y');
                    $data['end_date']         = Carbon::parse($registerRecord->date_end)->format('d/m/Y');
                    $data['late_start_shift'] = $registerRecord->late_start_shift;
                    $data['early_mid_shift']  = $registerRecord->early_mid_shift;
                    $data['late_mid_shift']   = $registerRecord->late_mid_shift;
                    $data['early_end_shift']  = $registerRecord->early_end_shift;
                    $data['reason']           = View::nl2br(ManageTimeCommon::limitText($registerRecord->reason, 50));
                    $data['link']             = route('manage_time::profile.comelate.detail', ['id' => $registerRecord->register_id]);
                    $data['approver_position'] = '';
                    $approver = $registerRecord->getApproverInformation();
                    if ($approver) {
                        $data['approver_position'] = $approver->approver_position;
                    }
                    $data['to_id']            = $registerRecord->creator_id;
                    $data['noti_content']     = trans('manage_time::view.The register of late in early out has been considered:').' '.$data['status'];
                    $template = 'manage_time::template.comelate.mail_approve.mail_approve_to_registrant';
                    $notificationData = [
                        'category_id' => RkNotify::CATEGORY_TIMEKEEPING
                    ];
                    ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData);
                }
            }
        }
        
        $messages = [
            'success'=> [
                Lang::get('manage_time::message.The register of late in early out has approve success'),
            ]
        ];

        $request->session()->flash('messages', $messages);
        echo json_encode(['url' => $urlCurrent]);
    }

    /**
     * [comelateDisapproveRegister: approve register]
     * @param  Request $request
     * @return [json]
     */
    public function comelateDisapproveRegister(Request $request)
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        $urlCurrent = $request->urlCurrent;
        $arrRegisterId = explode(',', $request->registerId);
        $reasonDisapprove = $request->reasonDisapprove;
        $personCc = $request->personList;
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

        $listRegisterIdDisapprove = ComeLateRegister::getRegisterByStatus($arrRegisterId, null);
        
        if (count($listRegisterIdDisapprove)) {
            foreach ($listRegisterIdDisapprove as $registerId) {
                $registerRecord = ComeLateRegister::getInformationRegister($registerId);
                $registerRecord->status = ComeLateRegister::STATUS_DISAPPROVE;
                $registerRecord->approver = $userCurrent->id;
                
                $data = [];
                if ($registerRecord->save()) {
                    $registerComment = new ManageTimeComment;
                    $registerComment->register_id = $registerId;
                    $registerComment->comment = $reasonDisapprove;
                    $registerComment->type = ManageTimeConst::TYPE_COMELATE;
                    $registerComment->created_by = $userCurrent->id;
                    $registerComment->save();

                    $data['user_mail']         = $userCurrent->email;
                    $data['mail_to']           = $registerRecord->creator_email;
                    $data['mail_cc']           = ComeLateRegister::getDataCcMail($personCc);
                    $data['mail_title']        = Lang::get('manage_time::view.[Unapproved][Late in early out] :name register late in early out, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
                    $data['status']            = Lang::get('manage_time::view.Unapprove');
                    $data['registrant_name']   = $registerRecord->creator_name;
                    $data['team_name']         = $registerRecord->role_name;
                    $data['approver_name']     = $registerRecord->approver_name;
                    $data['start_date']        = Carbon::parse($registerRecord->date_start)->format('d/m/Y');
                    $data['end_date']          = Carbon::parse($registerRecord->date_end)->format('d/m/Y');
                    $data['late_start_shift']  = $registerRecord->late_start_shift;
                    $data['early_mid_shift']   = $registerRecord->early_mid_shift;
                    $data['late_mid_shift']    = $registerRecord->late_mid_shift;
                    $data['early_end_shift']   = $registerRecord->early_end_shift;
                    $data['reason']            = View::nl2br(ManageTimeCommon::limitText($registerRecord->reason, 50));
                    $data['reason_disapprove'] = View::nl2br(ManageTimeCommon::limitText($reasonDisapprove, 50));
                    $data['link']              = route('manage_time::profile.comelate.detail', ['id' => $registerRecord->register_id]);
                    $data['to_id']             = $registerRecord->creator_id;
                    $data['noti_content']      = trans('manage_time::view.The register of late in early out has been considered:').' '.$data['status'];
                    $template = 'manage_time::template.comelate.mail_disapprove.mail_disapprove_to_registrant';
                    $notificationData = [
                        'category_id' => RkNotify::CATEGORY_TIMEKEEPING
                    ];
                    ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData);
                }
            } 
        }
        
        $messages = [
            'success'=> [
                Lang::get('manage_time::message.The register of late in early out has disapprove success'),
            ]
        ];

        $request->session()->flash('messages', $messages);
        echo json_encode(['url' => $urlCurrent]);
    }

    /*
     * Search employee can approve
     */
    public function searchEmployeeCanApproveAjax($route)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $userCurrent = Permission::getInstance()->getEmployee();
        return response()->json(
            ManageTimeCommon::searchEmployeesCanApprove(Input::get('q'), $userCurrent->id, $route)
        );
    }

    /**
     * Search employee by ajax
     */
    public function searchEmployeeAjax()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        return response()->json(
            ManageTimeCommon::searchEmployeeAjax(
                Input::get('q'), [
                    'page' => Input::get('page'),
                ],
                Input::get('type')
            )
        );
    }
    /**
     * Search employee by ajax
     */
    public function searchEmployeeOtDisallowAjax()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        return response()->json(
            ManageTimeCommon::searchEmployeeOtDisallowAjax(
                Input::get('q'), [
                    'page' => Input::get('page'),
                ],
                Input::get('type'),
                Input::get('t')
            )
        );
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
        $userCurrent = Permission::getInstance()->getEmployee();
        $employeeId = $request->employeeId;
        if (!$employeeId) {
            $employeeId = $userCurrent->id;
        }
        $startDate = $request->startDate;
        $endDate = $request->endDate;
        $registerId = $request->registerId;
        $checkRegisterExist = ComeLateRegister::checkRegisterExist($employeeId, Carbon::parse($startDate), Carbon::parse($endDate), $registerId);

        return Response::json($checkRegisterExist);
    }

    /**
     * Get the rules for the defined validation.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'approver'         => 'required',
            'start_date'       => 'required',
            'end_date'         => 'required',
            'late_start_shift' => 'integer|max:120',
            'early_mid_shift'  => 'integer|max:120',
            'late_mid_shift'   => 'integer|max:120',
            'early_end_shift'  => 'integer|max:120',
            'reason'           => 'required',
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
            'approver.required'        => Lang::get('manage_time::view.The approver field is required'),
            'start_date.required'      => Lang::get('manage_time::view.The start date field is required'),
            'end_date.required'        => Lang::get('manage_time::view.The end date field is required'),
            'late_start_shift.integer' => Lang::get('manage_time::view.The late start shift field must be an integer'),
            'late_start_shift.max' => Lang::get('manage_time::view.Please enter on the late start shift field a time between 1 minute and 120 minutes'),
            'early_mid_shift.integer'  => Lang::get('manage_time::view.The early mid shift field must be an integer'),
            'early_mid_shift.max'  => Lang::get('manage_time::view.Please enter on the early mid shift field a time between 1 minute and 120 minutes'),
            'late_mid_shift.integer'   => Lang::get('manage_time::view.The late mid shift field must be an integer'),
            'late_mid_shift.max'   => Lang::get('manage_time::view.Please enter on the late mid shift field a time between 1 minute and 120 minutes'),
            'early_end_shift.integer'  => Lang::get('manage_time::view.The early end shift field must be an integer'),
            'early_end_shift.max'  => Lang::get('manage_time::view.Please enter on the early end shift field a time between 1 minute and 120 minutes'),
            'reason.required'          => Lang::get('manage_time::view.The reason field is required'),
        ];

        return $messages;
    }

    /**
    * insert file
    *  @param [object] $registerRecord
    *  @param [array] $data
    *  @param [string] $nameFolder
    */
    public static function inserstFile($registerRecord, $nameFolder, $type = ManageTimeConst::TYPTE_LATE_IN_EARLY_OUT)
    {
        // create folder
        $structure = base_path('public/storage/' . $nameFolder);
        @mkdir($structure, 0777, true);

        $fileUploader = new FileUploader('files', array(
            'uploadDir' => base_path('public/storage/' . $nameFolder),
            'title' => 'name'
        ));
        // call to upload the files
        $data = $fileUploader->uploadDynamic($nameFolder);
        // if uploaded and success
        if ($data['isSuccess'] && count($data['files']) > 0) {
            $uploadedFiles = $data['files'];
        }
        // get the fileList
        $fileList = $fileUploader->getFileList();
        if (count($fileList)) {
            $attachments = [];
            foreach ($fileList as $key) {
                $attachments[] = [
                    'register_id' => $registerRecord->id,
                    'file_name' => $key['title'] . '.' . $key['extension'],
                    'path' => $key['file'],
                    'size' => $key['size'],
                    'mime_type' => $key['type'],
                    'type' => $type,
                ];
            }
            ManageTimeAttachment::insert($attachments);
        }
    }

    /**
    * update file
    *  @param [object] $registerRecord
    *  @param [array] $data
    *  @param [int] $registerId
    *  @param [string] $nameFolder
    */
    public static function updateFile($registerRecord, $registerId, $nameFolder, $type = ManageTimeConst::TYPTE_LATE_IN_EARLY_OUT)
    {
        $structure = base_path('public/storage/' . $nameFolder);
        @mkdir($structure, 0777, true);

        $attachmentsList = ManageTimeAttachment::getAttachments($registerId, $type);

        $appendedFiles = [];
        foreach ($attachmentsList as $file) {
            $appendedFiles[] = [
                'attachment_id' => $file->attachment_id,
                'name'          => $file->file_name,
                'type'          => $file->mime_type,
                'size'          => $file->size,
                'file'          => url($file->path),
                'path'          => $file->path,
                'uploaded'      => false,
                'data'          => ['url' => url($file->path)]
            ];
        }

        $fileUploader = new FileUploader('files', array(
            'uploadDir' => base_path('public/storage/' . $nameFolder),
            'title' => 'name',
            'files' => $appendedFiles
        ));

        // call to upload the files
        $data = $fileUploader->uploadDynamic($nameFolder);

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
                    $attachments[] = [
                        'register_id' => $registerRecord->id,
                        'file_name' => $key['title'] . '.' . $key['extension'],
                        'path' => $key['file'],
                        'size' => $key['size'],
                        'mime_type' => $key['type'],
                        'type' => $type
                    ];
                }
            }
            ManageTimeAttachment::insert($attachments);
        }
    }
    //======================= version new =======================

    /**
     * get employee not late
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showNotLateTime()
    {
        $objNotLateTime = new TimekeepingNotLateTime();
        $empNotLateTime = $objNotLateTime->getNotLateTimePager();

        $params = [
            'collectionModel' => $empNotLateTime,
        ];
        return view('manage_time::comelate.staff_late.notLateTime', $params);
    }


    /**
     * thêm nhân viên có thời gian không đi muộn
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createNotLateTime(Request $request)
    {

        $rules = [
            'empid' => 'required',
            'startDate' => 'required|date_format:d/m/Y',
            'endDate' => 'required|date_format:d/m/Y',
            'minute' => 'required|numeric',
        ];

        $valid = Validator::make($request->all(), $rules);
        if ($valid->fails()) {
          return response()->json(['errors' => $valid->errors()]);
        }

        DB::beginTransaction();
        try {
            $startDate = Carbon::createFromFormat('d/m/Y', $request->startDate);
            $endDate = Carbon::createFromFormat('d/m/Y', $request->endDate);
            $objNotLateTime = new TimekeepingNotLateTime();
            $objNotLateTime->checkDateEmp($request->empid, $startDate->format('Y-m-d'), $endDate->format('Y-m-d'));
            $data = [
                'employee_id' =>  $request->empid,
                'start_date' =>  $startDate->format('Y-m-d'),
                'end_date' =>  $endDate->format('Y-m-d'),
                'minute' =>  $request->minute,
            ];
            if ($objNotLateTime->checkDateEmp($data['employee_id'], $data['start_date'], $data['end_date'])) {
                return response()->json([
                   'status' => 0,
                   'message' => trans('manage_time::message.Employee has the same time'),
                ]);
            }
            $date = $this->checkCloseTKTable($data['start_date']);
            if ($date !== '') {
                return response()->json([
                    'status' => 0,
                    'message' => trans('manage_time::message.You cannot add/change dates of the closed months timekeeping table.')
                ]);
            }
            $collection = $objNotLateTime->create($data);
            DB::commit();
            return response()->json([
                'status' => 1,
                'data' => $objNotLateTime->getNotLateTimeById($collection->id),
                'message' => trans('manage_time::message.Save success'),
            ]);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return response()->json([
                'status' => 0,
                'message' => $ex->getMessage(),
            ]);
        }
    }

    /**
     * update nhân viên có thời gian không đi muộn
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateNotLateTime(Request $request)
    {
        $rules = [
            'id' => 'required',
            'empid' => 'required',
            'startDate' => 'required|date_format:d/m/Y',
            'endDate' => 'required|date_format:d/m/Y',
            'minute' => 'required|numeric',
        ];

        $valid = Validator::make($request->all(), $rules);
        
        if ($valid->fails()) {
          return response()->json(['errors' => $valid->errors()]);
        }
        $startDate = Carbon::createFromFormat('d/m/Y', $request->startDate);
        $endDate = Carbon::createFromFormat('d/m/Y', $request->endDate);
        $objNotLateTime = new TimekeepingNotLateTime();

        DB::beginTransaction();
        try {
            $notLateTime = TimekeepingNotLateTime::find($request->id);
            if (!$notLateTime) {
                return response()->json([
                   'status' => 0,
                   'message' => trans('core::message.Not found entity'),
                ]);
            }
            $data = [
                'employee_id' =>  $request->empid,
                'start_date' =>  $startDate->format('Y-m-d'),
                'end_date' =>  $endDate->format('Y-m-d'),
                'minute' =>  $request->minute,
            ];
            if ($objNotLateTime->checkDateEmp($data['employee_id'], $data['start_date'], $data['end_date'], $request->id)) {
                return response()->json([
                   'status' => 0,
                   'message' => trans('manage_time::message.Employee has the same time'),
                ]);
            }
            $start = $notLateTime->start_date == $data['start_date'] ? '' : $data['start_date'];
            $date = $this->checkCloseTKTable($start, $data['end_date']);
            if ($date !== '') {
                return response()->json([
                    'status' => 0,
                    'message' => trans('manage_time::message.You cannot add/change dates of the closed months timekeeping table.')
                ]);
            }
            $dateNow = Carbon::now();
            if ($dateNow->day > 6 && $dateNow->format('Y-m-d') > $data['end_date']) {
                unset($data['minute']);
            }
            $notLateTime->update($data);
            DB::commit();
            return response()->json([
                'status' => 1,
                'data' => $notLateTime->getNotLateTimeById($request->id),
                'message' => trans('manage_time::message.Update success'),
            ]);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return response()->json([
                'status' => 0,
                'message' => $ex->getMessage(),
            ]);
        }
    }

    /**
     * xóa nhân viên có thời gian không đi muộn
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteNotLateTime(Request $request)
    {
        $rules = [
            'id' => 'required',
        ];

        $valid = Validator::make($request->all(), $rules);
        
        if ($valid->fails()) {
          return response()->json(['errors' => $valid->errors()]);
        }
        DB::beginTransaction();
        try {
            $notLateTime = TimekeepingNotLateTime::find($request->id);
            if (!$notLateTime) {
                return response()->json([
                   'status' => 0,
                   'message' => trans('core::message.Not found entity'),
                ]);
            }
            $id = $notLateTime->delete();
            DB::commit();
            return response()->json([
                'status' => 1,
                'data'=> ['id' => $id],
                'message' => trans('manage_time::message.Delete success!'),
            ]);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return response()->json([
                'status' => 0,
                'message' => $ex->getMessage(),
            ]);
        }
    }
    
    /**
     * kiểm tra các tháng đã chốt bảng công
     *
     * @param  date $dateStart 'Y-m-d'
     * @param  date $dateEnd 'Y-m-d'
     * @return string
     */
    public function checkCloseTKTable($dateStart = '', $dateEnd = '')
    {
        $dateNow = Carbon::now();
        if ($dateNow->day > 6) {
            $date = $dateNow->format('Y-m-01');
        } else {
            $date = $dateNow->subDay()->format('Y-m-01');
        }
        if ($dateStart && $dateStart < $date) {
            return $date;
        }
        $dateEndMax = Carbon::parse($date)->subMonth()->lastOfMonth()->toDateString();
        if ($dateEnd && $dateEnd < $dateEndMax) {
            return $date;
        }
        return '';
    }

    /**
     * get employee not late
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showNotLate()
    {
        $objNotLate = new TimekeepingNotLate();
        $empNotLate = $objNotLate->getEmployeeNotLate();

        $params = [
            'empNotLate' => $empNotLate,
        ];
        return view('manage_time::comelate.staff_late.notLate', $params);
    }

    /**
     * update nhân viên không đi muộn
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateNotLate(Request $request)
    {
        if (!count($request->weekdays)) {
            return response()->json([
                'status' => 0,
                'message' => 'Thứ trong tuần không để trống',
            ]);
        }
        $objTKNotLate = new TimekeepingNotLate();
        $tkNotLate = $objTKNotLate->getNotLateEById($request->id);
        if ($tkNotLate) {
            DB::beginTransaction();
            try {
                $data = [
                    'employee_id' => $request->empId,
                    'weekdays' => implode(',', $request->weekdays),
                ];
                $notLates = $objTKNotLate->getNotLateByEmpDate([$data['employee_id']]);
                if (count($notLates)) {
                    foreach ($notLates as $notLate) {
                        if ($notLate->id != $request->id) {
                            return response()->json([
                                'status' => 0,
                                'message' => trans('manage_time::message.Employee: :employee exists', ["employee" => $notLate->emp_name]),
                            ]);
                        }
                    }
                }
                $tkNotLate->update($data);
                $tkNotLate = $objTKNotLate->getNotLateEById($request->id);
                DB::commit();
                return response()->json([
                    'status' => 1,
                    'message' => trans('manage_time::message.Update success'),
                    'data' => $tkNotLate,
                ]);
            } catch (Exception $ex) {
                DB::rollback();
                Log::info($ex);
                return response()->json([
                    'status' => 0,
                    'message' => $ex->getMessage(),
                ]);
            }
        } else {
            return response()->json([
                'status' => 0,
                'message' => 'Không tìm thấy giá trị',
            ]);
        }
    }

    /**
     * thêm nhân viên không đi muộn
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createNotLate(Request $request)
    {
        if (!count($request->weekdays)) {
            return response()->json([
                'status' => 0,
                'message' => 'Thứ trong tuần không để trống',
            ]);
        }

        DB::beginTransaction();
        try {
            $data = [
                'employee_id' => $request->empId,
                'weekdays' => implode(',', $request->weekdays),
            ];
            $objTKNotLate = new TimekeepingNotLate();
            $notLate = $objTKNotLate->getNotLateByEmpDate([$data['employee_id']]);
            if (count($notLate)) {
                return response()->json([
                    'status' => 0,
                    'message' => trans('manage_time::message.Employee: :employee exists', ["employee" => $notLate->first()->emp_name]),
                ]);
            }
            $collection = TimekeepingNotLate::create($data);
            $tkNotLate = $objTKNotLate->getNotLateEById($collection->id);
            DB::commit();
            return response()->json([
                'status' => 1,
                'message' => trans('manage_time::message.Save success'),
                'data' => $tkNotLate,
            ]);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return response()->json([
                'status' => 0,
                'message' => $ex->getMessage(),
            ]);
        }
    }

    /**
     * xóa nhân viên có thời gian không đi muộn
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteNotLate(Request $request)
    {
        $rules = [
            'id' => 'required',
        ];

        $valid = Validator::make($request->all(), $rules);
        
        if ($valid->fails()) {
          return response()->json(['errors' => $valid->errors()]);
        }
        DB::beginTransaction();
        try {
            $notLate = TimekeepingNotLate::find($request->id);
            if (!$notLate) {
                return response()->json([
                   'status' => 0,
                   'message' => trans('core::message.Not found entity'),
                ]);
            }
            $id = $notLate->delete();
            DB::commit();
            return response()->json([
                'status' => 1,
                'data'=> ['id' => $id],
                'message' => trans('manage_time::message.Delete success!'),
            ]);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return response()->json([
                'status' => 0,
                'message' => $ex->getMessage(),
            ]);
        }
    }
}