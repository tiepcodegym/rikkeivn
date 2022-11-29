<?php

namespace Rikkei\ManageTime\Http\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\View as CoreView;
use Rikkei\ManageTime\Model\WktRegisterComment;
use Rikkei\ManageTime\Model\WorkingTime as WorkingTimeModel;
use Rikkei\ManageTime\Model\WorkingTimeDetail;
use Rikkei\ManageTime\Model\WorkingTimeRegister;
use Rikkei\ManageTime\View\View;
use Rikkei\ManageTime\View\ViewTimeKeeping;
use Rikkei\ManageTime\View\WorkingTime as WTviewC;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Project\Model\Project;
use Rikkei\Resource\View\View as ResourceView;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Team\View\Permission;

class WorkingTimeCompayController extends Controller
{
    /**
     * @var WTviewC
     */
    protected $workingTime;
    /**
     * @var WorkingTimeRegister
     */
    protected $workingTimeRegister;
    /**
     * @var WorkingTimeDetail
     */
    protected $workingTimeDetail;

    const ROUTE_REGISTER = 'manage_time::permiss.wktime.register';

    /*
     * constructer
     */
    public function _construct()
    {
        parent::_construct();
        $this->workingTime = new WTviewC();
        $this->workingTimeRegister = new WorkingTimeRegister();
        $this->workingTimeDetail = new WorkingTimeDetail();
    }
    
     /*
     * register form
     */
    public function register(Request $request)
    {
        if (!Permission::getInstance()->isAllow(self::ROUTE_REGISTER)) {
            CoreView::viewErrorPermission();
        }
        Breadcrumb::add(trans('manage_time::view.Register working time'));

        $curEmp = Permission::getInstance()->getEmployee();
        $workingTimeFrame = $this->workingTime->getWorkingTimeFrame();
        $workingTimeHalfFrame = $this->workingTime->getWorkingTimeHalfFrame();
        $wKTRelationShip = $this->workingTime->workingTimeHalfRelationship();

        $objProject = new Project();
        $projects = [];
        $strPermiss = $this->workingTime->getPermissByRoute();
        if ($strPermiss) {
            $projects = $objProject->getProjectByPermission($strPermiss, $curEmp->id);
        }

        $param = [
            'employee' => $curEmp,
            'workingTimeFrame' => $workingTimeFrame,
            'workingTimeHalfFrame' => $workingTimeHalfFrame,
            'projects' => $projects,
            'wKTRelationShip' => $wKTRelationShip,
        ];
        return view('manage_time::working-time.register', $param);
    }

    /**
     * validate form data before save working time register
     * format data input after validate successfully
     *
     * @param array $dataInput
     * @param null|integer $wtrId
     * @return array
     */
    public function validateSaveRegister($dataInput, $wtrId = null)
    {
        $response = ['status' => false];
        $rules = [
            'approver_id' => 'required|integer',
            'related_ids' => 'array',
            'reason' => 'required|string',
            'from_date' => 'required|date_format:Y-m-d',
            'to_date' => 'required|date_format:Y-m-d',
            'key_working_time' => 'required|integer',
            'key_working_time_half' => 'required|integer',
            'proj_id' => 'integer',
            'wtDetail' => 'required|array|min:1',
            'wtDetail.*.employee_id' => 'required|integer',
            'wtDetail.*.start_date' => 'required',
            'wtDetail.*.end_date' => 'required',
            'wtDetail.*.key_working_time' => 'required',
            'wtDetail.*.key_working_time_half' => 'required',
        ];

        /* validate basic form data */
        $validator = Validator::make($dataInput, $rules);
        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['message'] = trans('core::message.Data is invalid!');
            return $response;
        }
        $arrayWorkingTimeFrame = $this->workingTime->getWorkingTimeFrame();
        $arrayWorkingTimeHalfFrame = $this->workingTime->getWorkingTimeHalfFrame();
        if (!isset($arrayWorkingTimeFrame[$dataInput['key_working_time']])
            || !isset($arrayWorkingTimeHalfFrame[$dataInput['key_working_time_half']])) {
            $response['message'] = trans('core::message.Data is invalid!');
            return $response;
        }

        $resourceView = new ResourceView();
        $wKTRelationShip = $this->workingTime->workingTimeHalfRelationship();
        $employeeTags = [];
        foreach ($dataInput['wtDetail'] as $key => $wtDetail) {
            if (!$resourceView->checkIsDate($wtDetail['start_date'])
                || !$resourceView->checkIsDate($wtDetail['end_date'])
                || $wtDetail['start_date'] > $wtDetail['end_date']
                || !isset($arrayWorkingTimeFrame[$wtDetail['key_working_time']])
                || !isset($arrayWorkingTimeHalfFrame[$wtDetail['key_working_time_half']])
                || $wKTRelationShip[$wtDetail['key_working_time']] != $wtDetail['key_working_time_half']
            ) {
                $response['message'] = trans('core::message.Data is invalid!');
                return $response;
            }
            /* merge detail data */
            $employeeTags[] = $wtDetail['employee_id'];
            $workingTimeFrame = $arrayWorkingTimeFrame[$wtDetail['key_working_time']];
            $workingTimeHalfFrame = $arrayWorkingTimeHalfFrame[$wtDetail['key_working_time_half']];
            $wtDetail['from_date'] = $wtDetail['start_date'];
            $wtDetail['to_date'] = $wtDetail['end_date'];
            $wtDetail['start_time1'] = $workingTimeFrame[0];
            $wtDetail['end_time1'] = $workingTimeFrame[1];
            $wtDetail['start_time2'] = $workingTimeFrame[2];
            $wtDetail['end_time2'] = $workingTimeFrame[3];
            $wtDetail['half_morning'] = $workingTimeHalfFrame[0];
            $wtDetail['half_afternoon'] = $workingTimeHalfFrame[1];
            $dataInput['wtDetail'][$key] = $wtDetail;
        }

        /* validate found registration when update */
        $wtRegister = $wtrId ? $this->workingTimeRegister->find($wtrId) : null;
        if ($wtrId && !$wtRegister) {
            $response['message'] = trans('core::message.Data is invalid!');
            return $response;
        }

        /* validate permission save registration */
        $wtrId && $wtRegister->approver_id = $dataInput['approver_id'];
        $permission = $this->workingTime->getPermisison($wtRegister);
        if (!$permission['edit'] && !$permission['update_approved']) {
            $response['message'] = trans('manage_time::message.You do not have permission to edit');
            return $response;
        }

        /* validate overlap with other registration */
        $registerIds = [];
        $wtrId && $registerIds[] = $wtrId;
        $parentWTR = !empty($wtRegister->parent_id) ? $this->workingTimeRegister->find($wtRegister->parent_id) : null;
        if ($parentWTR && (int)$parentWTR->status === WorkingTimeRegister::STATUS_APPROVE) {
            $oldRegisterId = $parentWTR->id;
            $registerIds[] = $oldRegisterId;
        } elseif ($wtRegister && (int)$wtRegister->status === WorkingTimeRegister::STATUS_APPROVE) {
            $oldRegisterId = $wtRegister->id;
        } else {
            $oldRegisterId = null;
        }
        $wtrExist = $this->workingTimeDetail->checkExists($dataInput['wtDetail'], $registerIds);
        if ($wtrExist) {
            $response['exists'] = ['working_time' => $wtrExist];
            $response['message'] = trans('manage_time::message.Employee has the same time');
            return $response;
        }

        /* validate successfully */
        $dataWTDetail = $this->workingTimeDetail->processMergeOldDataWTDetail($dataInput['wtDetail'], $oldRegisterId);
        $relatedIds = (array) $dataInput['related_ids'];
        $dataInput['employee_tags'] = $employeeTags;
        $dataInput['employee_ids'] = array_unique(array_merge($employeeTags, $relatedIds));
        $dataInput['related_ids'] = implode(',', $relatedIds);
        $dataInput['proj_id'] = !empty($dataInput['proj_id']) ? $dataInput['proj_id'] : null;
        $dataInput['updated_by'] = Permission::getInstance()->getEmployee()->id;
        $dataInput['data_detail'] = $dataWTDetail;
        // current user has permission to approve and update registration => edit + approve immediately
        $dataInput['status'] = $permission['update_approved'] ? WorkingTimeRegister::STATUS_APPROVE : WorkingTimeRegister::STATUS_UNAPPROVE;
        return [
            'status' => true,
            'dataInput' => $dataInput,
            'wt_register' => $wtRegister,
            'permission' => $permission,
        ];
    }
    
        
    /**
     * save register
     *
     * @param  Request $request
     * @return json
     */
    public function saveRegister(Request $request)
    {
        $dataValidate = $this->validateSaveRegister($request->all());
        if (!$dataValidate['status']) {
            return response()->json([
                'status' => false,
                'message' => $dataValidate['message'],
                'exists' => isset($dataValidate['exists']) ? $dataValidate['exists'] : [],
            ]);
        }

        $dataInput = $dataValidate['dataInput'];
        $currUser = Permission::getInstance()->getEmployee();
        $teamHis = EmployeeTeamHistory::getCurrentTeamsWorking($currUser->id);
        $teamId = null;
        if ($teamHis) {
            $teamId = $teamHis->first()->team_id;
        }

        $dataInput['employee_id'] = $currUser->id;
        $dataInput['team_id'] = $teamId;

        DB::beginTransaction();
        try {
            $wtRegister = $this->insertOrUpdate($dataInput);
            $this->sendMailNeedApprove($wtRegister);
            $this->sendEmailRelated($dataInput['employee_ids'], $wtRegister);
            DB::commit();
            request()->session()->flash('messages', ['success' => [trans('manage_time::message.Save success')]]);
            return response()->json([
                'status' => true,
                'redirect' => route('manage_time::wktime.detail', ['id' => $wtRegister->id])
            ]);
        } catch (\Exception $ex) {
            Log::info($ex);
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => trans('manage_time::message.Error. Please try again later.'),
            ]);
        }
    }

    /**
     * update register
     *
     * @param  Request $request
     * @param  int $id
     * @return view
     */
    public function update(Request $request, $id)
    {
        $dataValidate = $this->validateSaveRegister($request->all(), $id);
        if (!$dataValidate['status']) {
            return response()->json([
                'status' => false,
                'message' => $dataValidate['message'],
                'exists' => isset($dataValidate['exists']) ? $dataValidate['exists'] : [],
            ]);
        }

        $dataInput = $dataValidate['dataInput'];
        $wtRegister = $dataValidate['wt_register'];
        $permission = $dataValidate['permission'];

        DB::beginTransaction();
        try {
            /* current user only has permission to edit the approved registration => create child registration */
            if (!$permission['update_approved'] && (int)$wtRegister->status === WorkingTimeRegister::STATUS_APPROVE) {
                $dataInput['employee_id'] = $wtRegister->employee_id;
                $dataInput['team_id'] = $wtRegister->team_id;
                $dataInput['parent_id'] = $wtRegister->id;
                $wtRegister = null;
            }

            $wtRegister = $this->insertOrUpdate($dataInput, $wtRegister);

            /* sync leave day, business trip register */
            if ($permission['update_approved']) {
                $syncResult = $this->workingTimeDetail->syncAllOtherRegistrations($dataInput['data_detail'], WorkingTimeRegister::STATUS_APPROVE);
                if (!$syncResult['status']) {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => trans('manage_time::message.Employee has the same time'),
                        'exists' => $syncResult['errors'],
                    ]);
                }
                /* remove cache working time setting and time quarter */
                foreach ($dataInput['employee_ids'] as $empId) {
                    CacheHelper::forget(CacheHelper::CACHE_TIME_SETTING_PREFIX, $empId);
                    CacheHelper::forget(CacheHelper::CACHE_TIME_QUATER, $empId);
                }
            }

            $this->sendEmailRelated($dataInput['employee_ids'], $wtRegister);
            if ($permission['update_approved']) {
                $this->sendMailApproved($wtRegister);
            } else {
                $this->sendMailNeedApprove($wtRegister);
            }
            $this->createFileTimeInOutEmployee($dataInput['data_detail']);
            DB::commit();
            request()->session()->flash('messages', ['success' => [trans('manage_time::message.Save success')]]);
            return response()->json([
                'status' => true,
                'redirect' => route('manage_time::wktime.detail', ['id' => $wtRegister->id])
            ]);
        } catch (\Exception $ex) {
            Log::info($ex);
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => trans('manage_time::message.Error. Please try again later.'),
            ]);
        }
    }
    
    /**
     * insert or update working time register and refresh related data
     *
     * @param array $dataInput
     * @param WorkingTimeRegister|null $wtRegister
     * @return WorkingTimeRegister
     */
    public function insertOrUpdate($dataInput, $wtRegister = null)
    {
        $dataInsert = array_only($dataInput, [
            'employee_id', 'team_id', 'approver_id', 'related_ids', 'from_date', 'to_date',
            'key_working_time', 'key_working_time_half', 'proj_id', 'reason', 'updated_by', 'status', 'parent_id',
        ]);
        $wtRegister = $this->workingTimeRegister->insertOrUpdate($dataInsert, $wtRegister);

        /* merge data working time detail to insert */
        $teamWorkings = EmployeeTeamHistory::getTeamWorkingIdOfEmployees($dataInput['employee_tags']);
        $now = Carbon::now()->toDateTimeString();
        foreach($dataInput['wtDetail'] as $key => $item) {
            $item = array_only($item, [
                'employee_id', 'key_working_time', 'key_working_time_half', 'from_date', 'to_date',
                'start_time1', 'end_time1', 'start_time2', 'end_time2', 'half_morning', 'half_afternoon',
            ]);
            $item['working_time_id'] = $wtRegister->id;
            $item['team_id'] = isset($teamWorkings[$item['employee_id']]) ? $teamWorkings[$item['employee_id']] : null;
            $item['created_at'] = $now;
            $item['updated_at'] = $now;
            $dataInput['wtDetail'][$key] = $item;
        }

        $this->workingTimeDetail->insertOrUpdate($dataInput['wtDetail'], $wtRegister->id);
        return $wtRegister;
    }

    /**
     * view edit|detail register working time
     *
     * @param  int $id
     * @return view
     */
    public function edit($id)
    {
        $workingTime = $this->workingTimeRegister->find($id);
        if (!$workingTime) {
            return redirect()->route('manage_time::wktime.register')->withErrors(Lang::get('asset::message.Not found item'));
        }
        $permiss = $this->workingTime->getPermisison($workingTime);
        if (!$permiss['view']) {
            CoreView::viewErrorPermission();
        }
        $curEmp = Permission::getInstance()->getEmployee();
        $workingTimeFrame = $this->workingTime->getWorkingTimeFrame();
        $workingTimeHalfFrame = $this->workingTime->getWorkingTimeHalfFrame();

        $objProject = new Project();
        $projects = [];
        $strPermiss = $this->workingTime->getPermissByRoute();
        if ($strPermiss) {
            $projects = $objProject->getProjectByPermission($strPermiss, $curEmp->id);
        }
        $firstWTDetail = $workingTime->workingTimeDetails->first();
        $morningIn = $firstWTDetail->start_time1;
        $morningHalf = $firstWTDetail->half_morning;
        $idEmpRelate = explode(',', $workingTime->related_ids);
        $wKTRelationShip = $this->workingTime->workingTimeHalfRelationship();

        $param = [
            'employee' => $workingTime->employee,
            'workingTime' => $workingTime,
            'relatedPersonsList' => Employee::find($idEmpRelate),
            'listEmployeeDetail' => $workingTime->workingTimeDetails,
            'workingTimeFrame' => $workingTimeFrame,
            'workingTimeHalfFrame' => $workingTimeHalfFrame,
            'projects' => $projects,
            'morningIn' => $morningIn,
            'morningHalf' => $morningHalf,
            'permiss' => $permiss,
            'isPageDetail' => request()->route()->getName() === 'manage_time::wktime.detail',
            'wKTRelationShip' => $wKTRelationShip
        ];
        if ((int)$workingTime->status === WorkingTimeRegister::STATUS_REJECT) {
            $param['comments'] = $this->workingTimeRegister->getReasonDisapprove($id);
        }
        return view('manage_time::working-time.edit', $param);
    }
   
     /*
     * delete register
     */
    public function deleteRegister($id)
    {
        $item = WorkingTimeRegister::findOrFail($id);
        if (!$item->isDelete()) {
            return redirect()->back()->with('messages', ['errors' => [trans('manage_time::message.Fill was approved, can not delete')]]);
        }
        //check permission - tạm thời
        $currUser = Permission::getInstance()->getEmployee();
        if (!$item->employee_id == $currUser->id) {
            CoreView::viewErrorPermission();
        }
        $item->delete();
        return redirect()->back()->with('messages', ['success' => [trans('manage_time::message.Delete data success')]]);
    }

    /*
     * list my register
     */
    public function listRegister($status = null)
    {
        Menu::setActive('profile');
        Breadcrumb::add(trans('manage_time::view.Register working time'));

        $pageTitle = trans('manage_time::view.My working time register');
        $workingTimeFrame = $this->workingTime->getWorkingTimeFrame();
        $workingTimeHalfFrame = $this->workingTime->getWorkingTimeHalfFrame();
 
        $collectionModel = $this->workingTimeRegister->listRegister($status, WorkingTimeRegister::STR_REGISTER);
        $permiss = $this->workingTime->getPermisison();
        $param = [
            'collectionModel' => $collectionModel,
            'status' => $status,
            'pageTitle' => $pageTitle,
            'workingTimeFrame' => $workingTimeFrame,
            'workingTimeHalfFrame' => $workingTimeHalfFrame,
            'permiss' => $permiss,
        ];

        return view('manage_time::working-time.list', $param);
    }
    
     /*
     * list my register aproved
     */
    public function listRegisterApprove($status = null)
    {
        WorkingTimeModel::permissRegister();
        Menu::setActive('profile');
        Breadcrumb::add(trans('manage_time::view.Register working time'));
        $pageTitle = trans('manage_time::view.I approve working time register');
        $workingTimeFrame = $this->workingTime->getWorkingTimeFrame();
        $workingTimeHalfFrame = $this->workingTime->getWorkingTimeHalfFrame();

        $collectionModel = $this->workingTimeRegister->listRegister($status, WorkingTimeRegister::STR_APPROVE);
        $permiss = $this->workingTime->getPermisison();
        $param = [
            'collectionModel' => $collectionModel,
            'status' => $status,
            'pageTitle' => $pageTitle,
            'workingTimeFrame' => $workingTimeFrame,
            'workingTimeHalfFrame' => $workingTimeHalfFrame,
            'permiss' => $permiss,
        ];

        return view('manage_time::working-time.list', $param);
    }
    
    /*
     * approve or unapprove item
     */
    public function approveRegister(Request $request)
    {
        $rulesValid = [
            'ids' => 'required',
            'status' => 'required',
        ];
        $valid = Validator::make($request->all(), $rulesValid);
        if ($valid->fails()) {
            return redirect()->route('manage_time::wktime.register.approve.list')->withErrors(Lang::get('asset::message.Not found item'));
        }
        $listStatus = $this->workingTime->listWorkingTimeStatuses();

        DB::beginTransaction();
        try {
            $ids = explode(',', $request->ids);
            $status = (int) $request->get('status');
            if (!$request->ids || !isset($listStatus[$status])) {
                return redirect()->back()->with('messages', ['errors' => [trans('manage_time::message.invalid_input_data')]]);
            }
            foreach ($ids as $id) {
                $wtRegister = $this->workingTimeRegister->find($id);
                $permission = $this->workingTime->getPermisison($wtRegister);
                if (!$permission['approve']) {
                    DB::commit();
                    CoreView::viewErrorPermission();
                }
                // validate exist with other registration
                // (new, reject) => approve register or approved => reject
                $wtDetails = $wtRegister->workingTimeDetails;
                $wtrParent = $wtRegister->parent_id ? WorkingTimeRegister::find($wtRegister->parent_id) : null;
                if ($status === WorkingTimeRegister::STATUS_APPROVE
                    && (int)$wtRegister->status !== WorkingTimeRegister::STATUS_APPROVE
                    || $status === WorkingTimeRegister::STATUS_REJECT
                    && (int)$wtRegister->status === WorkingTimeRegister::STATUS_APPROVE) {
                    $dataWTDetail = [];
                    foreach ($wtDetails as $wtDetail) {
                        $dataWTDetail[] = [
                            "employee_id" => $wtDetail->employee_id,
                            "start_date" => $wtDetail->from_date,
                            "end_date" => $wtDetail->to_date,
                            "key_working_time" => $wtDetail->key_working_time,
                            "key_working_time_half" => $wtDetail->key_working_time_half,
                        ];
                    }
                    /* validate overlap with other registration */
                    $registerIds = [$wtRegister->id];
                    if ($wtrParent && (int)$wtrParent->status === WorkingTimeRegister::STATUS_APPROVE) {
                        $oldRegisterId = $wtrParent->id;
                        $registerIds[] = $wtrParent->id;
                    } else {
                        $oldRegisterId = null;
                    }
                    $wtrExist = [];
                    if ($status === WorkingTimeRegister::STATUS_APPROVE) {
                        $wtrExist = $this->workingTimeDetail->checkExists($dataWTDetail, $registerIds);
                    }
                    $dataWTDetail = $this->workingTimeDetail->processMergeOldDataWTDetail($dataWTDetail, $oldRegisterId);
                    $this->workingTimeRegister->updateStatusItem($wtRegister, $status); /* update for sync */
                    $syncResult = $this->workingTimeDetail->syncAllOtherRegistrations($dataWTDetail, $status);
                    if (count($wtrExist) || !$syncResult['status']) {
                        $dataExist = [];
                        $dataExist['working_time'] = $wtrExist;
                        !empty($syncResult['errors']) && $dataExist = array_merge($dataExist, $syncResult['errors']);
                        request()->session()->flash('dataErrors', $dataExist);
                        return redirect()->back()->withErrors(Lang::get('manage_time::message.Employee has the same time'));
                    }
                    $this->createFileTimeInOutEmployee($dataWTDetail);
                }

                /* delete old registration approved */
                if ($status === WorkingTimeRegister::STATUS_APPROVE && $wtrParent) {
                    $wtrParent->delete();
                }

                $this->workingTimeRegister->updateStatusItem($wtRegister, $status);
                WktRegisterComment::insertData($id, $request->all());
                $this->sendMailApproved($wtRegister);
                $empIds = $wtDetails->lists('employee_id')->toArray();
                if ($wtRegister->related_ids) {
                    $empIds = array_unique(array_merge($empIds, explode(",", $wtRegister->related_ids)));
                }
                $this->sendEmailRelated($empIds, $wtRegister);

                foreach ($empIds as $empId) {
                    CacheHelper::forget(CacheHelper::CACHE_TIME_SETTING_PREFIX, $empId);
                    CacheHelper::forget(CacheHelper::CACHE_TIME_QUATER, $empId);
                }
            }
            DB::commit();

            return redirect()->back()->with('messages', ['success' => [trans('manage_time::message.Update success')]]);
        } catch (\Exception $ex) {
            Log::info($ex);
            DB::rollback();
            return redirect()
                ->back()
                ->withInput()
                ->with('messages', ['errors' => [trans('manage_time::message.Error. Please try again later.')]]);
        }
    }

    /*
     * list my register related
     */
    public function listRegisterRelated($status = null)
    {
        WorkingTimeModel::permissRegister();
        Menu::setActive('profile');
        Breadcrumb::add(trans('manage_time::view.Register working time'));

        $pageTitle = trans('manage_time::view.Working time register relates to me');
        $workingTimeFrame = $this->workingTime->getWorkingTimeFrame();
        $workingTimeHalfFrame = $this->workingTime->getWorkingTimeHalfFrame();

        $collectionModel = $this->workingTimeRegister->listRegister($status, WorkingTimeRegister::STR_RELATED);
        $param = [
            'collectionModel' => $collectionModel,
            'status' => $status,
            'pageTitle' => $pageTitle,
            'workingTimeFrame' => $workingTimeFrame,
            'workingTimeHalfFrame' => $workingTimeHalfFrame,
        ];
        return view('manage_time::working-time.list', $param);
    }

    /**
     * send mail need approve (create new registration) to approver
     *
     * @param WorkingTimeRegister $workingTime
     * @return void
     * @throws Exception
     */
    public function sendMailNeedApprove($workingTime)
    {
        $statusText = $this->workingTime->getTextStatus($workingTime->status);
        $detailLink = route('manage_time::wktime.detail', ['id' => $workingTime->id]);
        $fromDate = $workingTime->getFromDate();
        $toDate = $workingTime->getToDate();

        $strMonth = '('. $fromDate . ' ' . trans('manage_time::view.time_to') . ' ' . $toDate . ')';
        $relatedSubject = trans('manage_time::view.mail_subject_working_time_approve_related', [
            'name' => $workingTime->employee->name,
            'month' => $strMonth,
            'status' => $statusText,
        ]);
        $dataMail = [
            'dearName' => $workingTime->approver->name,
            'employeeName' => $workingTime->employee->name,
            'detailLink' => $detailLink,
            'employeeAccount' => preg_replace('/@.*/', '',$workingTime->employee->email),
            'isUpdate' => false,
        ];
        $emailQueue = new EmailQueue();
        $emailQueue->setTo($workingTime->approver->email)
            ->setSubject($relatedSubject)
            ->setTemplate('manage_time::working-time.mails.submit-form', $dataMail)
            ->setNotify($workingTime->approver->id, null, $detailLink, ['category_id' => RkNotify::CATEGORY_TIMEKEEPING])
            ->save();
        return;
    }

    /**
     * send mail approved or reject to employee register working time
     *
     * @param WorkingTimeRegister $workingTime
     * @return void
     * @throws Exception
     */
    public function sendMailApproved($workingTime)
    {
        $statusText = $this->workingTime->getTextStatus($workingTime->status);
        $detailLink = route('manage_time::wktime.detail', ['id' => $workingTime->id]);
        $fromDate = $workingTime->getFromDate();
        $toDate = $workingTime->getToDate();

        $strMonth = '('. $fromDate . ' ' . trans('manage_time::view.time_to') . ' ' . $toDate . ')';
        $subject = trans('manage_time::view.mail_subject_working_time_approve', ['month' => $strMonth, 'status' => $statusText]);
        $dataMail = [
            'dearName' => $workingTime->employee->name,
            'content' => $subject,
            'detailLink' => $detailLink,
            'fromMonth' => $fromDate,
            'toMonth' => $toDate
        ];
        $emailQueue = new EmailQueue();
        $emailQueue->setTo($workingTime->employee->email)
            ->setSubject($subject)
            ->setTemplate('manage_time::working-time.mails.approve-form', $dataMail)
            ->setNotify($workingTime->employee->id, null, $detailLink, ['category_id' => RkNotify::CATEGORY_TIMEKEEPING])
            ->save();
    }
    
    /**
     * send email to employee related
     *
     * @param  array $empIds
     * @param  WorkingTimeRegister $workingTime
     * @return void
     * @throws Exception
     */
    public function sendEmailRelated($empIds, $workingTime)
    {
        $statusText = $this->workingTime->getTextStatus($workingTime->status);
        $detailLink = route('manage_time::wktime.detail', ['id' => $workingTime->id]);
        $fromDate = $workingTime->getFromDate();
        $toDate = $workingTime->getToDate();

        $strMonth = '('. $fromDate . ' ' . trans('manage_time::view.time_to') . ' ' . $toDate . ')';
        $relateds = Employee::select('id', 'name', 'email')->whereIn('id', $empIds)->get();
        if ($relateds) {
            $relatedSubject = trans('manage_time::view.mail_subject_working_time_approve_related', [
                'name' => $workingTime->employee->name,
                'month' => $strMonth,
                'status' => $statusText,
            ]);
            $dataMail['detailLink'] = $detailLink;
            foreach ($relateds as $relate) {
                if ($workingTime->employee_id == $relate->id) {
                    continue;
                }
                $dataMail['dearName'] = $relate->name;
                $dataMail['content'] = $relatedSubject . ', ' . trans('manage_time::view.related_to_you');
                $emailRelated = new EmailQueue();
                $emailRelated->setTo($relate->email)
                    ->setSubject($relatedSubject)
                    ->setTemplate('manage_time::working-time.mails.approve-form', $dataMail)
                    ->setNotify($relate->id, null, $detailLink, ['category_id' => RkNotify::CATEGORY_TIMEKEEPING])
                    ->save();
            }
        }
    }

     /*
     * list my times
     */
    public function index()
    {
        Menu::setActive('profile');
        Breadcrumb::add(trans('manage_time::view.Working time'));
        $collectionModel = $this->workingTimeDetail->listMyTimes();
        $pageTitle = trans('manage_time::view.I approve working time register');
        $workingTimeFrame = $this->workingTime->getWorkingTimeFrame();
        $workingTimeHalfFrame = $this->workingTime->getWorkingTimeHalfFrame();
        $permissRegister = $this->workingTime->getPermissByRoute();

        $param = [
            'collectionModel' => $collectionModel,
            'pageTitle' => $pageTitle,
            'workingTimeFrame' => $workingTimeFrame,
            'workingTimeHalfFrame' => $workingTimeHalfFrame,
            'permissRegister' => $permissRegister,
        ];

        return view('manage_time::working-time.index', $param);
    }

    /*
     * list items manage
     */
    public function listManage()
    {
        Menu::setActive('admin');
        Breadcrumb::add('Admin');
        Breadcrumb::add(trans('manage_time::view.Manage register working time'));

        $collectionModel = $this->workingTimeRegister->listRegister(null, WorkingTimeRegister::STR_MANAGER);

        $pageTitle = trans('manage_time::view.I approve working time register');
        $workingTimeFrame = $this->workingTime->getWorkingTimeFrame();
        $workingTimeHalfFrame = $this->workingTime->getWorkingTimeHalfFrame();
        $permissRegister = $this->workingTime->getPermissByRoute();
        $selectStatus = $this->workingTime->getLabelStatusRegister();

        $param = [
            'collectionModel' => $collectionModel,
            'pageTitle' => $pageTitle,
            'workingTimeFrame' => $workingTimeFrame,
            'workingTimeHalfFrame' => $workingTimeHalfFrame,
            'permissRegister' => $permissRegister,
            'selectStatus' => $selectStatus,
        ];
        return view('manage_time::working-time.manage.index', $param);
    }

    /**
     * create file in, out of emolyee from database timekeeping
     *
     * @param  data $dataWTDetail
     * @return void
     */
    public function createFileTimeInOutEmployee($dataWTDetail)
    {
        $dataEmployee = [];
        foreach($dataWTDetail as $key => $item) {
            if (isset($item['periods'])) {
                if (isset($dataEmployee[$item['employee_id']])) {
                    $dataEmployee[$item['employee_id']] = array_merge($dataEmployee[$item['employee_id']], $item['periods']);
                } else {
                    $dataEmployee[$item['employee_id']] = $item['periods'];
                }
            }
        }
        if (!$dataEmployee) {
            return;
        }
        $objViewTK = new ViewTimeKeeping();
        return $objViewTK->exportTimeInOutByListEmp($dataEmployee);
    }
}
