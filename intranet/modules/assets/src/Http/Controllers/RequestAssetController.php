<?php

namespace Rikkei\Assets\Http\Controllers;

use DB;
use Illuminate\Support\Facades\Redirect;
use Log;
use Lang;
use Exception;
use Carbon\Carbon;
use Rikkei\Assets\Model\AssetItem;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\View;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Team\View\Permission;
use Rikkei\Assets\Model\AssetCategory;
use Rikkei\Assets\Model\RequestAsset;
use Rikkei\Assets\Model\RequestAssetItem;
use Rikkei\Assets\Model\RequestAssetTeam;
use Rikkei\Assets\Model\RequestAssetHistory;
use Rikkei\Assets\View\AssetView;
use Rikkei\Assets\View\RequestView;
use Rikkei\Assets\View\AssetConst;
use Rikkei\Assets\View\RequestAssetPermission;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Team\Model\Employee;
use Illuminate\Http\Request;
use Rikkei\Assets\Model\RequestAssetItemsWarehouse;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Permission as PermissionsView;

class RequestAssetController extends Controller
{
    /**
     * Construct more
     */
    protected function _construct()
    {
        Breadcrumb::add('Asset', route('asset::asset.index'));
        Menu::setActive('Resource');
    }
    /**
     * List asset request
     * @return [view]
     */
    public function index(Request $request)
    {
        $data = $request->all();
        Breadcrumb::add('Asset request list');
        if (!Permission::getInstance()->isAllow('asset::resource.request.index')) {
            return View::viewErrorPermission();
        }
        $collectionModel = RequestAsset::getGridData($data);
        $requestAssetIDs = RequestAsset::getGridData()->pluck('id')->toArray();
        $assetState = AssetItem::countAssets();
        $assetCreator = RequestAsset::getAssetCreator($requestAssetIDs);
        $reviewersRequest = RequestAsset::getReviewersRequest($requestAssetIDs);
        $reqNotYet = RequestAsset::getRequestByState(RequestAsset::STATE_NOT_YET);
        $reqNotEnough = RequestAsset::getRequestByState(RequestAsset::STATE_NOT_ENOUGH);
        $reqEnough = RequestAsset::getRequestByState(RequestAsset::STATE_ENOUGH);

        $params = [
            'collectionModel' => $collectionModel,
            'assetState' => $assetState,
            'assetCreator' => $assetCreator,
            'reviewersRequest' => $reviewersRequest,
            'assetCategoriesList' => AssetCategory::getAssetCategoriesList(),
            'countReqNotYet' => count($reqNotYet),
            'countReqNotEnough' => count($reqNotEnough),
            'countReqEnough' => count($reqEnough),
        ];
        return view('asset::request.index')->with($params);
    }

    /**
     * Edit request asset
     * @param [int] $requestId
     * @return [view]
     */
    public function editRequest($requestId = null)
    {
        $cddTeamId = Input::get('cdd_team_id');
        $requestAsset = null;
        if ($requestId) {
            // $requestAsset = RequestAsset::find($requestId);
            $tblEmployee = Employee::getTableName();
            $tblRequestAsset = RequestAsset::getTableName();
            $requestAsset = RequestAsset::select(
                "{$tblRequestAsset}.*",
                "{$tblEmployee}.name",
                "{$tblEmployee}.email"
                )
                ->join("{$tblEmployee}", "{$tblEmployee}.id", '=', "{$tblRequestAsset}.reviewer")
                ->where("{$tblRequestAsset}.id", '=', $requestId)
                ->first();
        }
        $isCreate = false;
        if (!$requestId || ($cddTeamId && !$requestAsset)) {
            $isCreate = true;
            $reqEmpId = Input::get('emp_id');
            $currUser = null;
            if ($reqEmpId) {
                $currUser = Employee::find($reqEmpId);
            }
            if (!$currUser) {
                $currUser = Permission::getInstance()->getEmployee();
            }
            $employeeId = $currUser->id;
            $leaderReview = RequestView::getReviewersByEmployee($employeeId, $currUser->isLeader() ? $currUser->id : null);
            $requestAsssetItem = null;
            $contactOfEmp = AssetView::getContactOfRequestUser($employeeId);
        } else {
            if (!$requestAsset) {
                return redirect()->route('asset::resource.request.index')->withErrors(Lang::get('asset::message.Not found item'));
            }
            if (!RequestAssetPermission::permissEditRequesets([$requestId], $requestAsset->created_by)) {
                View::viewErrorPermission();
            }
            if ($requestAsset->request_date) {
                $requestAsset->request_date = Carbon::createFromFormat('Y-m-d', $requestAsset->request_date)->format('d-m-Y');
            }
            $employeeId = $requestAsset->employee_id;
            $leaderReview = RequestView::getReviewersByEmployee($employeeId, $requestAsset->reviewer);
            $requestAsssetItem = RequestAssetItem::getRequestAssetItems($requestAsset->id);
            $contactOfEmp = null;
        }

        $defaultParams = [];
        if ($cddTeamId && $isCreate) {
            $requestAsssetItem = AssetCategory::getDefaultCats([
                'id as asset_category_id',
                'name as asset_category_name',
                \DB::raw('1 as quantity')
            ]);
            $leaderReview = RequestView::getReviewersByEmployee($employeeId);
            $defaultParams['request_name'] = trans('asset::view.request_asset_default_name');
            $defaultParams['request_date'] = Carbon::now()->format('d-m-Y');
            $cddId = Input::get('cdd_id');
            $candidate = Candidate::find($cddId);
            if ($candidate) {
                $cddName = $candidate->fullname;
                $cddEmp = $candidate->employee;
                if ($cddEmp) {
                    $cddName = View::getNickName($cddEmp->email);
                }
                $defaultParams['request_reason'] = trans('asset::view.reqeust_asset_default_reason', ['name' => $cddName]);
            }
        }
        $assetCategoriesList = AssetCategory::getAssetCategoriesList('count', 'desc');
        $petitionerInfo = RequestView::getPetitionerInfo($employeeId);
        if (!$requestId) {
            $teamNewest = $petitionerInfo->newestTeam();
            $defaultParams['request_name'] = $assetCategoriesList->first()->name;
            // $defaultParams['request_name'] = ($teamNewest ? $teamNewest->name . ' - ' : null) . View::getNickName($petitionerInfo->email);
        }
        $newDefaultParams = $petitionerInfo->employee_name.' - '.$petitionerInfo->employee_group;
        $params = [
            'requestAsset' => $requestAsset,
            'petitionerInfo' => $petitionerInfo,
            'leaderReview' => $leaderReview,
            'assetCategoriesList' => $assetCategoriesList,
            'requestAsssetItem' => $requestAsssetItem,
            'isCreate' => $isCreate,
            'contactOfEmp' => $contactOfEmp,
            'defaultParams' => $defaultParams,
            'newDefaultParams' => $newDefaultParams
        ];
        return view('asset::request.edit', $params);
    }

    /**
     * View detail request asset
     * @param [int] $requestId
     * @return [view]
     */
    public function viewRequest($requestId)
    {
        Breadcrumb::add('Asset request list', route('asset::resource.request.index'));
        Breadcrumb::add('Asset request detail');
        return RequestView::viewRequest($requestId);
    }

    public function viewRequestItWarehouse($reqId)
    {
        $empTeamHistoryTbl = EmployeeTeamHistory::getTableName();
        $teamTbl = Team::getTableName();
        $userCurrent = PermissionsView::getInstance()->getEmployee();

        $reqAsset = RequestAsset::find($reqId);
        if (!$reqAsset || ($reqAsset && !in_array($reqAsset->status, [RequestAsset::STATUS_APPROVED, RequestAsset::STATUS_CLOSE]))) {
            return redirect()->back();
        }
        $assetAllocates = RequestAssetItemsWarehouse::getAssets($reqId, RequestAssetItemsWarehouse::STATUS_ALLOCATE);
        $assetUnallocates = RequestAssetItemsWarehouse::getAssets($reqId, RequestAssetItemsWarehouse::STATUS_UNALLOCATE);
        $reqIt = RequestAssetItemsWarehouse::where('request_id', $reqId)->where('status', RequestAssetItemsWarehouse::STATUS_UNALLOCATE)->first();
        $mainTeamCurrent = EmployeeTeamHistory::select(["{$empTeamHistoryTbl}.id", "{$empTeamHistoryTbl}.team_id", "{$empTeamHistoryTbl}.is_working", "{$teamTbl}.name", "{$teamTbl}.branch_code"])
        ->join("{$teamTbl}", "{$teamTbl}.id", '=', "{$empTeamHistoryTbl}.team_id")
        ->whereNull("{$empTeamHistoryTbl}.deleted_at")
        ->where("{$empTeamHistoryTbl}.employee_id", $userCurrent->id)
        ->where("{$empTeamHistoryTbl}.is_working", EmployeeTeamHistory::IS_WORKING)->first();

        $requestAsset = RequestAsset::getRequestDetail($reqId);
        if ($requestAsset->request_date) {
            $requestAsset->request_date = Carbon::createFromFormat('Y-m-d', $requestAsset->request_date)->format('d-m-Y');
        }
        $requestAsssetItem = RequestAssetItem::getRequestAssetItems($requestAsset, true);

        $viewData = [
            'reqAsset' => $reqAsset,
            'assetAllocates' => $assetAllocates,
            'assetUnallocates' => $assetUnallocates,
            'branchs' => AssetView::getAssetBranch(),
            'reqIt' => $reqIt,
            'mainTeamCurrent' => $mainTeamCurrent,
            'reqId' => $reqId,
            'userCurrent' => $userCurrent,
            'requestAsset' => $requestAsset,
            'requestAsssetItem' => $requestAsssetItem,
        ];
        
        return view('asset::request.view_it_warehouse')->with($viewData);
    }

    /**
     * Save request asset
     */
    public function saveRequest()
    {
        $dataRequest = Input::get('request');
        $rules = [
            'employee_id' => 'required',
            'request_date' => 'required',
            'reviewer' => 'required',
            'request_reason' => 'required',
            'skype' => 'required',
        ];
        $messages = [
            'employee_id.required' => Lang::get('asset::message.Petitioner is field required'),
            'request_date.required' => Lang::get('asset::message.Request date is field required'),
            'reviewer.required' => Lang::get('asset::message.Request approver is field required'),
            'request_reason.required' => Lang::get('asset::message.Note is field required'),
            'skype.required' => Lang::get('asset::message.Skype is field required'),
        ];
        $validator = Validator::make($dataRequest, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        $dataAsset = Input::get('asset');
        $arrayCheck = array();
        if (count($dataAsset)) {
            foreach ($dataAsset as $valueCheck) {
                if (!in_array($valueCheck['name'], $arrayCheck)) {
                    array_push($arrayCheck, $valueCheck['name']);
                } else {
                    return redirect()->back()->withErrors(Lang::get('asset::message.Asset category request is different field'))->withInput();
                }
                if (!$valueCheck['name']) {
                    return redirect()->back()->withErrors(Lang::get('asset::message.Asset category request is field required'))->withInput();
                }
                if (!$valueCheck['number']) {
                    return redirect()->back()->withErrors(Lang::get('asset::message.Quantity is field required'))->withInput();
                }
            }
        } else {
            return redirect()->back()->withErrors(Lang::get('asset::message.Asset information is field required'))->withInput();
        }
        $candidateId = Input::get('candidate_id');
        DB::beginTransaction();
        try {
            $dataHistory = [];
            $userCurrent = Permission::getInstance()->getEmployee();
            $requestId = Input::get('id');
            $isCreate = false;
            $isUpdate = false;
            if ($requestId) {
                $requestAsset = RequestAsset::find($requestId);
                if (!$requestAsset) {
                    if ($candidateId) {
                        return view('layouts.popup')->withErrors(Lang::get('asset::message.Not found item'));
                    }
                    return redirect()->back()->withErrors(Lang::get('asset::message.Not found item'));
                }
                $requestAsset->status = RequestAsset::STATUS_INPROGRESS;
                $dataHistory['action'] = RequestAssetHistory::ACTION_UPDATE;
                $isUpdate = true;
            } else {
                $isCreate = true;
                $requestAsset = new RequestAsset();
                $dataHistory['action'] = RequestAssetHistory::ACTION_CREATE;
                $dataRequest['created_by'] = $userCurrent->id;
            }


            $teamCode = Employee::getNewestTeamCode($dataRequest['employee_id']);
            $prefix = AssetConst::getAssetPrefixByCode($teamCode);
            $dataRequest['team_prefix'] = $prefix;
            $dataRequest['request_date'] = Carbon::createFromFormat('d-m-Y', $dataRequest['request_date'])->format('Y-m-d');
            $requestAsset->setData(array_map('trim', $dataRequest));
            if ($requestAsset->save()) {
                if ($requestId) {
                    RequestAssetItem::where('request_id', $requestId)->delete();
                    RequestAssetTeam::where('request_id', $requestId)->delete();
                }
                foreach ($dataAsset as $item) {
                    $requestItem = new RequestAssetItem;
                    $requestItem->request_id = $requestAsset->id;
                    $requestItem->asset_category_id = $item['name'];
                    $requestItem->quantity = $item['number'];
                    $requestItem->save();
                }
                $teamsByEmployee = RequestView::getTeamsByEmployee($requestAsset->employee_id);
                if (count($teamsByEmployee)) {
                    $dataRequestTeam = [];
                    foreach ($teamsByEmployee as $item) {
                        $dataRequestTeam[] = [
                            'request_id' => $requestAsset->id,
                            'team_id' => $item->team_id,
                        ];
                    }
                    RequestAssetTeam::insert($dataRequestTeam);
                }
                $now = Carbon::now();
                $dataHistory['request_id'] = $requestAsset->id;
                $dataHistory['employee_id'] = $userCurrent->id;
                $dataHistory['created_at'] = $now;
                $dataHistory['updated_at'] = $now;
                RequestAssetHistory::insert($dataHistory);

                if ($isCreate) {
                    RequestAsset::afterCreateRequest($requestAsset);
                    //update candidate request
                    if ($candidateId) {
                        $candidate = Candidate::find($candidateId);
                        if ($candidate) {
                            $candidate->request_asset_id = $requestAsset->id;
                            $candidate->save();
                        }
                    }
                }
                if ($isUpdate) {
                    RequestAsset::afterUpdateRequest($requestAsset);
                }
                // synchronized skype
                AssetView::synchronizedSkype($requestAsset->employee_id, $requestAsset->skype);
            }
            DB::commit();
            $messages = [
                'success'=> [
                    Lang::get('asset::message.Save data success'),
                ]
            ];
            $params = ['id' => $requestAsset->id];
            if ($candidateId) {
                $params['is_popup'] = 1;
                $params['cdd_id'] = $candidateId;
                if ($isCreate) {
                    return redirect()->route('asset::resource.request.edit', $params)->with('messages', $messages)
                            ->with('window_script', true);
                }
            }
            return redirect()->route('asset::resource.request.edit', $params)->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return redirect()->back()->withErrors(trans('core::message.Error system, please try later!'));
        }
    }

    /**
     * Delete request asset
     */
    public function delete()
    {
        $requestId = Input::get('id');
        $status = Input::get('status');
        DB::beginTransaction();
        try {
            $requestAsset = RequestAsset::find($requestId);
            if (!$requestAsset) {
                return redirect()->back()->withErrors(Lang::get('asset::message.Not found item'));
            }
            $requestAsset->status = RequestAsset::STATUS_CANCEL;
            $requestAsset->delete();
            DB::commit();
            $messages = [
                'success'=> [
                    Lang::get('asset::message.Delete data success'),
                ]
            ];
            if ($status) {
                return redirect()->back()->with('messages', $messages);
            }
            return redirect()->route('asset::resource.request.index')->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            $messages = [
                'errors'=> [
                    Lang::get('asset::message.System error')
                ]
            ];
            return redirect()->back()->withErrors($messages);
        }
    }

    /**
     * Review request
     * @return [type]
     */
    public function reviewRequest()
    {
        $requestId = Input::get('id');
        $statusSubmit = Input::get('status');
        $dataSendMail = [];
        DB::beginTransaction();
        try {
            $dataHistory = [];
            $requestAsset = RequestAsset::find($requestId);
            if (!$requestAsset) {
                return redirect()->back()->withErrors(Lang::get('asset::message.Not found item'));
            }
            $userCurrent = Permission::getInstance()->getEmployee();
            if (!RequestAssetPermission::isAllowReviewRequest($requestAsset, $userCurrent->id)) {
                View::viewErrorPermission();
            }
            $isReview = false;
            if ($statusSubmit == AssetConst::APPROVE_REQUEST) {
                $isReview = true;
                $requestAsset->status = RequestAsset::STATUS_REVIEWED;
                $dataHistory['action'] = RequestAssetHistory::ACTION_REVIEW;
                $dataSendMail['status'] = Lang::get('asset::view.Reviewed');
            } else {
                $dataReject['disapprove_reason'] = Input::get('disapprove_reason');
                $rules = [
                    'disapprove_reason' => 'required',
                ];
                $messages = [
                    'disapprove_reason.required' => Lang::get('asset::message.Request.Disapprove reason is field required'),
                ];
                $validator = Validator::make($dataReject, $rules, $messages);
                if ($validator->fails()) {
                    return redirect()->back()->withErrors($validator);
                }
                $requestAsset->status = RequestAsset::STATUS_REJECT;
                $dataHistory['action'] = RequestAssetHistory::ACTION_REJECT;
                $dataHistory['note'] = $dataReject['disapprove_reason'];
                $dataSendMail['status'] = Lang::get('asset::view.Reject');
            }
            $requestAsset->reviewer = $userCurrent->id;
            $messages = [
                'success'=> [
                    Lang::get('asset::message.Save data success'),
                ]
            ];
            if ($requestAsset->save()) {
                $now = Carbon::now();
                $dataHistory['request_id'] = $requestAsset->id;
                $dataHistory['employee_id'] = $userCurrent->id;
                $dataHistory['created_at'] = $now;
                $dataHistory['updated_at'] = $now;
                RequestAssetHistory::insert($dataHistory);

                $dataSendMail['mail_title'] = Lang::get('asset::view.[Rikkeisoft intranet] Request asset');
                $dataSendMail['request_name'] = $requestAsset->request_name;
                $dataSendMail['request_date'] = $requestAsset->request_date;
                $dataSendMail['href'] = route('asset::resource.request.view', ['id' => $requestAsset->id]);
                $dataSendMail['receiver_name'] = '';
                $dataSendMail['reviewer_name'] = '';
                $dataSendMail['creator_name'] = '';
                $dataSendMail['petitioner_name'] = '';
                $creator = $requestAsset->getCreatorInfomation();
                $reviewer = $requestAsset->getReviewerInfomation();
                $petitioner = $requestAsset->getPetitionerInfomation();
                if ($creator) {
                    $dataSendMail['creator_name'] = $creator->name;
                    $dataSendMail['mail_title'] .= ' ' . $creator->getNickName();
                }
                if ($reviewer) {
                    $dataSendMail['reviewer_name'] = $reviewer->name;
                }
                if ($petitioner) {
                    $dataSendMail['to_id'] = $petitioner->id;
                    $dataSendMail['mail_to'] = $petitioner->email;
                    $dataSendMail['receiver_name'] = $petitioner->name;
                    $dataSendMail['petitioner_name'] = $petitioner->name;
                    $template = 'asset::request.mail.review_request_send_to_petitioner';
                    $dataSendMail['to_id'] = $petitioner->id;
                    $dataSendMail['noti_content'] = trans('asset::view.The request asset created for you has change status to:').' '.$dataSendMail['status'];
                    AssetView::pushEmailToQueue($dataSendMail, $template);
                }
                if ($requestAsset->employee_id != $requestAsset->created_by && $creator) {
                    $dataSendMail['to_id'] = $creator->id;
                    $dataSendMail['mail_to'] = $creator->email;
                    $dataSendMail['receiver_name'] = $creator->name;
                    $template = 'asset::request.mail.review_request_send_to_creator';
                    $dataSendMail['to_id'] = $creator->id;
                    $dataSendMail['noti_content'] = trans('asset::view.The request asset created by you has change status to:').' '.$dataSendMail['status'];
                    AssetView::pushEmailToQueue($dataSendMail, $template);
                }
                if ($isReview) {
                    $employeesCanApprove = RequestAssetPermission::getEmployeesCanApproveRequest($petitioner);
                    if (!$employeesCanApprove->isEmpty()) {
                        $template = 'asset::request.mail.review_request_send_to_approver';
                        $recieverIds = [];
                        foreach ($employeesCanApprove as $employee) {
                            $dataSendMail['mail_to'] = $employee['employee_email'];
                            $dataSendMail['receiver_name'] = $employee['employee_name'];
                            AssetView::pushEmailToQueue($dataSendMail, $template, false);
                            $recieverIds[] = $employee->employee_id;
                        }
                        //set notify
                        \RkNotify::put(
                            $recieverIds,
                            trans('asset::view.Have new a request assset has been viewed.'),
                            $dataSendMail['href'],
                            ['category_id' => RkNotify::CATEGORY_HUMAN_RESOURCE]
                        );
                    }
                }
            }

            DB::commit();
            return redirect()->back()->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return redirect()->back()->withErrors($ex->getMessage());
        }
    }

    /**
     * Approve request
     * @return [type]
     */
    public function approveRequest()
    {
        $requestId = Input::get('id');
        $statusSubmit = Input::get('status');
        $dataSendMail = [];
        DB::beginTransaction();
        try {
            $dataHistory = [];
            $requestAsset = RequestAsset::find($requestId);
            if (!$requestAsset) {
                return redirect()->back()->withErrors(Lang::get('asset::message.Not found item'));
            }
            $userCurrent = Permission::getInstance()->getEmployee();
            if (!RequestAssetPermission::isAllowApproveRequest($requestAsset, $userCurrent->id)) {
                View::viewErrorPermission();
            }
            if ($statusSubmit == AssetConst::APPROVE_REQUEST) {
                $requestAsset->status = RequestAsset::STATUS_APPROVED;
                $dataHistory['action'] = RequestAssetHistory::ACTION_APPROVE;
                $dataSendMail['status'] = Lang::get('asset::view.Approved');
            } else {
                $dataReject['disapprove_reason'] = Input::get('disapprove_reason');
                $rules = [
                    'disapprove_reason' => 'required',
                ];
                $messages = [
                    'disapprove_reason.required' => Lang::get('asset::message.Request.Disapprove reason is field required'),
                ];
                $validator = Validator::make($dataReject, $rules, $messages);
                if ($validator->fails()) {
                    return redirect()->back()->withErrors($validator);
                }
                $requestAsset->status = RequestAsset::STATUS_REJECT;
                $dataHistory['action'] = RequestAssetHistory::ACTION_REJECT;
                $dataHistory['note'] = $dataReject['disapprove_reason'];
                $dataSendMail['status'] = Lang::get('asset::view.Reject');
            }
            $requestAsset->approver = $userCurrent->id;
            $messages = [
                'success'=> [
                    Lang::get('asset::message.Save data success'),
                ]
            ];
            if ($requestAsset->save()) {
                $now = Carbon::now();
                $dataHistory['request_id'] = $requestAsset->id;
                $dataHistory['employee_id'] = $userCurrent->id;
                $dataHistory['created_at'] = $now;
                $dataHistory['updated_at'] = $now;
                RequestAssetHistory::insert($dataHistory);

                $dataSendMail['mail_title'] = Lang::get('asset::view.[Rikkeisoft intranet] Request asset');
                $dataSendMail['request_name'] = $requestAsset->request_name;
                $dataSendMail['request_date'] = $requestAsset->request_date;
                $dataSendMail['href'] = route('asset::resource.request.view', ['id' => $requestAsset->id]);
                $dataSendMail['receiver_name'] = '';
                $dataSendMail['petitioner_name'] = '';
                $dataSendMail['creator_name'] = '';
                $dataSendMail['approver_name'] = '';
                $approver = $requestAsset->getApproverInfomation();
                if ($approver) {
                    $dataSendMail['approver_name'] = $approver->name;
                }
                $petitioner = $requestAsset->getPetitionerInfomation();
                $creator = $requestAsset->getCreatorInfomation();
                if ($creator) {
                    $dataSendMail['creator_name'] = $creator->name;
                }
                if ($petitioner) {
                    $dataSendMail['mail_to'] = $petitioner->email;
                    $dataSendMail['receiver_name'] = $petitioner->name;
                    $dataSendMail['petitioner_name'] = $petitioner->name;
                    $template = 'asset::request.mail.approve_request_send_to_petitioner';
                    //set data notify
                    $dataSendMail['to_id'] = $petitioner->id;
                    $dataSendMail['noti_content'] = trans('asset::view.The request asset created for you has change status to:').' '.$dataSendMail['status'];
                    AssetView::pushEmailToQueue($dataSendMail, $template);
                }
                if ($requestAsset->employee_id != $requestAsset->created_by && $creator) {
                    $dataSendMail['mail_to'] = $creator->email;
                    $dataSendMail['receiver_name'] = $creator->name;
                    $template = 'asset::request.mail.approve_request_send_to_creator';
                    //set data notify
                    $dataSendMail['to_id'] = $creator->id;
                    $dataSendMail['noti_content'] = trans('asset::view.The request asset created by you has change status to:').' '.$dataSendMail['status'];
                    AssetView::pushEmailToQueue($dataSendMail, $template);
                }
            }
            DB::commit();
            return redirect()->back()->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return redirect()->back()->withErrors($ex->getMessage());
        }
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
            RequestAssetPermission::searchEmployeeAjax(Input::get('q'), [
                'page' => Input::get('page'),
            ])
        );
    }

    /**
     * Search employee by ajax
     */
    public function searchEmployeeReviewAjax()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        return response()->json(
            RequestAssetPermission::searchEmployeeAjaxReview(Input::get('q'), Input::get('employee'), [
                'page' => Input::get('page'),
            ])
        );
    }

    public function searchLeaderReviewAjax()
    {
        $leaderReview = RequestView::getReviewersByEmployee(Input::get('employee_id'));
        if ($leaderReview) {
            $leaderReview["email"] = preg_replace('/@.*/', '', $leaderReview["email"]);
        }
        //Get Skype of user
        $contact = \Rikkei\Team\Model\EmployeeContact::getByEmp(Input::get('employee_id'));
        return response()->json([
            'reviewer' => $leaderReview,
            'contact' => $contact,
        ]);
    }
    /*
     * Ajax get request asset to allocation
     */
    public function ajaxGetRequestAssetToAllocation()
    {
        $employeeId = Input::get('employeeId');
        $assetCategoryId = Input::get('listCateId');
        $requestAsset = RequestAsset::getRequestAssetToAllocation($employeeId, $assetCategoryId);
        $totalRequestAsset = count($requestAsset);
        $params = [
            'requestAsset' => $requestAsset,
        ];
        $html = view('asset::request.include.select_request_asset')->with($params)->render();

        return response()->json(['html' => $html, 'totalRequestAsset' => $totalRequestAsset]);
    }

    /**
     * update quantity of request asset
     * @param type $requestId
     * @param Request $request
     * @return type
     */
    public function updateQuantity($requestId, Request $request)
    {
        $requestItem = RequestAsset::find($requestId);
        if (!$requestItem) {
            return response()->json(trans('asset::message.Not found item'), 404);
        }
        if (($requestItem->status != RequestAsset::STATUS_APPROVED) || !Permission::getInstance()->isAllow('asset::asset.asset-allocation')) {
            return response()->json(trans('core::message.You don\'t have access'), 401);
        }
        $catId = $request->get('cat_id');
        $quantity = $request->get('quantity');
        if (!$catId || !$quantity) {
            return response()->json(trans('asset::message.Invalid input data'), 422);
        }
        return RequestAssetItem::updateQuantity($requestId, $catId, $quantity);
    }

    /**
     * update category id of request asset
     * @param type $requestId
     * @param Request $request
     * @return type
     */
    public function updateCategoryId($requestId, Request $request)
    {
        $requestItem = RequestAsset::find($requestId);
        if (!$requestItem) {
            return response()->json(trans('asset::message.Not found item'), 404);
        }
        if (($requestItem->status != RequestAsset::STATUS_APPROVED) || !Permission::getInstance()->isAllow('asset::asset.asset-allocation')) {
            return response()->json(trans('core::message.You don\'t have access'), 401);
        }
        $catId = $request->get('cat_id');
        $catIdNew = $request->get('catIdNew');
        //if catId = 0 then create new
        if (!$catIdNew) {
            return response()->json(trans('asset::message.Invalid input data'), 422);
        }
        return RequestAssetItem::updateCategoryId($requestId, $catId, $catIdNew);
    }

    /**
     * Delete request asset
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteRequest(Request $request)
    {
        $ids = explode(',', $request->id);
        if (!is_array($ids)) {
            return redirect()->back()->with('messages', [
                'errors'=> [
                    Lang::get("core::message.Error system, please try later!"),
                ]
            ]);
        }
        $permissionDel = RequestAssetPermission::checkPermissionDel();
        if (is_array($permissionDel) && array_diff($ids, $permissionDel)) {
            return redirect()->back()->with('messages', [
                'errors'=> [
                    Lang::get("asset::message.You don't has permission delete request checked"),
                ]
            ]);
        }
        DB::beginTransaction();
        try {
            if (is_array($ids)) {
                $requests = RequestAsset::whereIn('id', $ids);
                if ($requests->get()->isEmpty()) {
                    return redirect()->back()->with('messages', [
                        'errors'=> [
                            Lang::get('asset::message.Not found item'),
                        ]
                    ]);
                }
                $requests->update([
                    'status' => RequestAsset::STATUS_CANCEL
                ]);
                $requests->delete();
            }
            DB::commit();

            $url = $request->get('url_previous');
            if ($url && strpos($url, 'request-asset')) {
                return Redirect::to($url)->with('messages', [
                    'success' => [
                        Lang::get('asset::message.Delete data success'),
                    ]
                ]);
            }
            // Khi url trc đó ko phải trang tài sản và k có quyền admin thì redirect về profile
            $action = Permission::getInstance()->isAllow('asset::resource.request.index') ? 'asset::resource.request.index' : 'asset::profile.my_request_asset';

            return redirect()->route($action)->with('messages', [
                'success' => [
                    Lang::get('asset::message.Delete data success'),
                ]
            ]);
        } catch (Exception $ex) {
            Log::info($ex);
            DB::rollback();
            return redirect()->back()->withErrors($ex->getMessage());
        }
    }
}
