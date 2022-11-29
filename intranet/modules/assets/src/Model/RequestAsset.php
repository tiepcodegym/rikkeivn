<?php

namespace Rikkei\Assets\Model;

use Illuminate\Support\Facades\DB;
use Lang;
use Exception;
use Carbon\Carbon;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\CheckpointPermission;
use Rikkei\Team\View\Config;
use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\TeamMember;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
use Rikkei\Team\View\Permission;
use Rikkei\Assets\View\AssetView;
use Rikkei\Assets\View\AssetConst;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Assets\Model\RequestAssetHistory;
use Rikkei\Assets\Model\AssetsHistoryRequest;
use Rikkei\Assets\Model\AssetItem;
use Rikkei\Team\View\TeamConst;
use Rikkei\Assets\Model\RequestAssetItemsWarehouse;

class RequestAsset extends CoreModel
{
    use SoftDeletes;

    protected $table = 'request_assets';

    protected $fillable = [
        'id', 'request_name', 'employee_id', 'reviewer', 'approver', 'request_date', 'request_reason',
        'status', 'state', 'created_by', 'created_at', 'updated_at'
    ];

    const STATUS_CANCEL = 0;
    const STATUS_INPROGRESS = 1;
    const STATUS_REJECT = 2;
    const STATUS_REVIEWED = 3;
    const STATUS_APPROVED = 4;
    const STATUS_CLOSE = 5;

    const STATE_NOT_YET = 0;
    const STATE_NOT_ENOUGH = 1;
    const STATE_ENOUGH = 2;
    /*
     * ignore warning check status
     */
    public static function ignoreStatus()
    {
        return $ignoreStatus = [self::STATUS_CLOSE, self::STATUS_APPROVED, self::STATUS_REJECT, self::STATUS_CANCEL];
    }

    /**
     * Get collection request to show grid
     * @return type
     */
    public static function getGridData($filter = [])
    {
        $employeeId = Permission::getInstance()->getEmployee()->id;
        $dataFilter = Form::getFilterData('except');
        $dataFilterStatus = Form::getFilterData('number');
        $type = isset($filter['type']) ? $filter['type'] : '';

        $route = 'asset::resource.request.index';
        $tblRequestAsset = self::getTableName();
        $tblRequestAssetTeam = RequestAssetTeam::getTableName();
        $tblEmployee = Employee::getTableName();
        $tblEmployeeAsApprover = 'tbl_employee_as_approver';
        $roleTable = Role::getTableName();
        $teamTable = Team::getTableName();
        $tblReAssetItem = RequestAssetItem::getTableName();
        $tblAssetCate = AssetCategory::getTableName();
        $tblRequestAssetHistory = RequestAssetHistory::getTableName();
        $tblAssetsHistoryRequest = AssetsHistoryRequest::getTableName();
        $teamMemberTable = TeamMember::getTableName();
        $tblReqAssetWH = RequestAssetItemsWarehouse::getTableName();
        $tblRequest = 'tbl_request';
        $requestSql = self::select(
            "{$tblRequestAsset}.id",
            "{$tblRequestAsset}.request_name",
            "{$tblRequestAsset}.request_date",
            "{$tblRequestAsset}.status",
            "{$tblRequestAsset}.employee_id",
            "{$tblRequestAsset}.reviewer",
            "{$tblEmployee}.name as petitioner_name",
            "{$tblEmployee}.email as petitioner_email",
            "{$tblEmployeeAsApprover}.name as approver_name",
            "{$tblReAssetItem}.asset_category_id",
            DB::raw("GROUP_CONCAT(DISTINCT CONCAT({$roleTable}.role, ' - ', {$teamTable}.name) ORDER BY {$roleTable}.role DESC SEPARATOR '; ') as role_name"),
            DB::Raw("(select GROUP_CONCAT(DISTINCT {$teamTable}.name SEPARATOR ', ') FROM {$tblRequestAssetTeam} inner join {$teamTable} on {$teamTable}.id = {$tblRequestAssetTeam}.team_id where {$tblRequestAssetTeam}.request_id = {$tblRequestAsset}.id) as team_name"),
            DB::raw("(select GROUP_CONCAT(DISTINCT {$teamTable}.id SEPARATOR ', ') FROM {$tblRequestAssetTeam} inner join {$teamTable} on {$teamTable}.id = {$tblRequestAssetTeam}.team_id where {$tblRequestAssetTeam}.request_id = {$tblRequestAsset}.id) as team_id"),
            "{$tblAssetCate}.name as cate_name",
            "{$tblAssetCate}.id as cate_id",
            DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(' . $tblReAssetItem . '.asset_category_id, "-", ' . $tblReAssetItem . '.quantity)) SEPARATOR ",") as str_qty'),
            $tblRequestAsset . '.created_by',
            DB::raw('CASE WHEN '.$tblReqAssetWH.'.id > 0 THEN 1 ELSE 0 END as is_request')
        )
            ->leftJoin("{$tblRequestAssetTeam}", "{$tblRequestAssetTeam}.request_id", "=", "{$tblRequestAsset}.id")
            ->join("{$tblEmployee}", "{$tblEmployee}.id", "=", "{$tblRequestAsset}.employee_id")
            ->leftJoin("{$tblEmployee} as {$tblEmployeeAsApprover}", "{$tblEmployeeAsApprover}.id", "=", "{$tblRequestAsset}.approver")
            ->join("{$teamMemberTable}", "{$teamMemberTable}.employee_id", "=", "{$tblEmployee}.id")
            ->leftJoin("{$teamTable}", "{$teamTable}.id", "=", "{$teamMemberTable}.team_id")
            ->leftJoin("{$roleTable}", "{$roleTable}.id", "=", "{$teamMemberTable}.role_id")
            ->leftJoin("{$tblReAssetItem}", "{$tblReAssetItem}.request_id", "=", "{$tblRequestAsset}.id")
            ->leftJoin("{$tblAssetCate}", "{$tblReAssetItem}.asset_category_id", "=", "{$tblAssetCate}.id")
            ->leftJoin("{$tblReqAssetWH}", "{$tblReqAssetWH}.request_id", '=', "{$tblRequestAsset}.id")
            ->leftJoin(
                DB::raw(
                    '(SELECT SUM(quantity) as quantity, request_id '
                    . 'FROM ' . $tblReAssetItem . ' '
                    . 'GROUP BY request_id) as rai_q'
                ),
                $tblRequestAsset . '.id',
                '=',
                'rai_q.request_id'
            );

        if (!in_array($type, ['not_yet', 'not_enough', 'enough'])) {
            if (!empty($dataFilterStatus['tbl_request.status'])) {
                $requestSql->where("{$tblRequestAsset}.status", $dataFilterStatus['tbl_request.status']);
            } else {
                $requestSql->whereNotIn("{$tblRequestAsset}.status", [self::STATUS_CANCEL, self::STATUS_CLOSE, self::STATUS_REJECT]);
            }
        }

        if (!empty($filter['type'])) {
            switch ($filter['type']) {
                case 'not_yet':
                    $requestSql->where("{$tblRequestAsset}.status", self::STATUS_APPROVED)->where("{$tblRequestAsset}.state", self::STATE_NOT_YET);
                    break;
                case 'not_enough':
                    $requestSql->where("{$tblRequestAsset}.state", self::STATE_NOT_ENOUGH);
                    break;
                case 'enough':
                    $requestSql->where("{$tblRequestAsset}.state", self::STATE_ENOUGH);
                    break;
            }
        }

        $scope = Permission::getInstance();
        if ($scope->isScopeCompany(null, $route)) {

        } elseif ($teamIds = $scope->isScopeTeam(null, $route)) {
            !is_array($teamIds) ? array($teamIds) : $teamIds;
            $teamCode = Employee::getNewestTeamCode($employeeId);
            $teamCode = explode('_', $teamCode)[0];

            $requestSql->leftJoin($teamMemberTable . ' as tmb_creator', $tblRequestAsset . '.created_by', '=', 'tmb_creator.employee_id')
                ->leftJoin($teamTable . ' as team_creator', 'tmb_creator.team_id', '=', 'team_creator.id')
                ->leftJoin($teamMemberTable . ' as tmb_requester', $tblRequestAsset . '.employee_id', '=', 'tmb_requester.employee_id')
                ->leftJoin($teamTable . ' as team_requester', 'team_requester.id', '=', 'tmb_requester.team_id')
                ->where(function ($query) use ($teamIds, $employeeId, $tblRequestAsset, $teamCode) {
                    $query->orwhere("{$tblRequestAsset}.employee_id", $employeeId)
                        ->orwhere("{$tblRequestAsset}.created_by", $employeeId)
                        ->orwhereIn('team_creator.id', $teamIds)
                        ->orwhereIn('team_requester.id', $teamIds)
                        ->orWhere('team_creator.code', 'like', $teamCode . '%');
                    if (in_array($teamCode, [Team::CODE_PREFIX_HN, Team::CODE_TEAM_IT])) {
                        $query->orWhere('team_requester.code', Team::CODE_PREFIX_AI);
                        $query->orWhere('team_requester.code' , 'like', Team::CODE_PREFIX_ACADEMY . '%');
                    }
                });
        } else {
            $requestSql->where(function ($query) use ($employeeId, $tblRequestAsset) {
                $query->where("{$tblRequestAsset}.employee_id", $employeeId)
                    ->orwhere("{$tblRequestAsset}.created_by", $employeeId);
            });
        }
        $collection = DB::table(DB::raw("({$requestSql->groupBy($tblRequestAsset.'.id')->toSql()}) as tbl_request"))
            ->mergeBindings($requestSql->groupBy($tblRequestAsset . '.id')->getQuery());

        try {
            if (isset($dataFilter["{$tblRequest}.request_date"])) {
                $requestDateFilter = Carbon::parse($dataFilter["{$tblRequest}.request_date"])->toDateString();
                $collection->whereDate("{$tblRequest}.request_date", "=", $requestDateFilter);
            }
            if (isset($dataFilter["{$tblRequest}.team_id"])) {
                $collection->whereRaw('FIND_IN_SET("' . $dataFilter["{$tblRequest}.team_id"] . '",tbl_request.team_id)');
            }

            if (isset($dataFilter["{$tblRequest}.id"])) {
                $collection->where("{$tblRequest}.id", $dataFilter["{$tblRequest}.id"]);
            }
            if (isset($dataFilter["{$tblRequest}.petitioner_name"])) {
                $collection->whereIn('employee_id', Employee::getEmployeeIDByNameOrEmail($dataFilter["{$tblRequest}.petitioner_name"]));
            }
            if (isset($dataFilter["{$tblRequest}.creator_name"])) {
                $collection->whereIn('created_by', Employee::getEmployeeIDByNameOrEmail($dataFilter["{$tblRequest}.creator_name"]));
            }
            if (isset($dataFilter["{$tblRequest}.reviewer_name"])) {
                $collection->whereIn('reviewer', Employee::getEmployeeIDByNameOrEmail($dataFilter["{$tblRequest}.reviewer_name"]));
            }
        } catch (Exception $ex) {
            return null;
        }
        $collection->groupBy("tbl_request.id");
        $pager = Config::getPagerData();
        if (Form::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy("{$tblRequest}.request_date", 'desc');
        }
        self::filterGrid2($collection, [], null, 'LIKE', $type);
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        $assetHistory = RequestAssetHistory::select("{$tblRequestAssetHistory}.request_id", "{$tblAssetsHistoryRequest}.allocation_confirm")
            ->join("{$tblAssetsHistoryRequest}", "{$tblAssetsHistoryRequest}.request_asset_history_id", '=', "{$tblRequestAssetHistory}.id")
            ->get();
        $arrayAssetConfirm = [];
        foreach ($assetHistory as $key => $value) {
            if (!array_key_exists($value->request_id, $arrayAssetConfirm)) {
                $arrayAssetConfirm[$value->request_id] = $value->allocation_confirm;
            } else {
                if ($arrayAssetConfirm[$value->request_id] != $value->allocation_confirm) {
                    switch ($value->allocation_confirm) {
                        case AssetItem::ALLOCATION_CONFIRM_FALSE:
                            $arrayAssetConfirm[$value->request_id] = AssetItem::ALLOCATION_CONFIRM_FALSE;
                            break;
                        case AssetItem::ALLOCATION_CONFIRM_TRUE:
                            if ($arrayAssetConfirm[$value->request_id] != AssetItem::ALLOCATION_CONFIRM_FALSE) {
                                $arrayAssetConfirm[$value->request_id] = AssetItem::ALLOCATION_CONFIRM_NONE;
                            }
                            break;
                        case AssetItem::ALLOCATION_CONFIRM_NONE:
                            if ($arrayAssetConfirm[$value->request_id] == AssetItem::ALLOCATION_CONFIRM_TRUE) {
                                $arrayAssetConfirm[$value->request_id] = AssetItem::ALLOCATION_CONFIRM_NONE;
                            }
                            break;
                        default:
                            break;
                    }
                }
            }
        }
        foreach ($collection as $key => $value) {
            if (array_key_exists($value->id, $arrayAssetConfirm)) {
                $value->state = $arrayAssetConfirm[$value->id];
            }
        }
        return $collection;
    }

    public static function filterGrid2(&$collection, $except = [], $urlSubmitFilter = null, $compare = 'REGEXP', $data = null)
    {
        $type = $data;
        if (is_array($urlSubmitFilter)) {
            $filter = $urlSubmitFilter;
        } else {
            $filter = Form::getFilterData(null, null, $urlSubmitFilter);
        }
        if ($filter && count($filter)) {
            if ($type && in_array($type, ['not_yet', 'not_enough', 'enough'])) {
                unset($filter['number']['tbl_request.status']);
                if (empty($filter['number'])) {
                    unset($filter['number']);
                }
            }
            if (!empty($filter)) {
                foreach ($filter as $key => $value) {
                    if (in_array($key, $except)) {
                        continue;
                    }
                    if (is_array($value)) {
                        if ($key == 'number' && $value) {
                            foreach ($value as $col => $filterValue) {
                                if ($filterValue === '') {
                                    continue;
                                }
                                if ($filterValue == 'NULL') {
                                    $collection = $collection->whereNull($col);
                                } else {
                                    $collection = $collection->where($col, $filterValue);
                                }
                            }
                        } elseif ($key == 'in' && $value) {
                            foreach ($value as $col => $filterValue) {
                                $collection = $collection->whereIn($col, $filterValue);
                            }
                        } elseif ($key == 'date' && $value) {
                            foreach ($value as $col => $filterValue) {
                                if ($filterValue == 'NULL') {
                                    $collection = $collection->whereNull($col);
                                } elseif (preg_match('/^[0-9\-\:\s]+$/', $filterValue)) {
                                    $collection = $collection->where($col, $filterValue);
                                }
                            }
                        } else {
                            if (isset($value['from']) && $value['from']) {
                                $collection = $collection->where($key, '>=', $value['from']);
                            }
                            if (isset($value['to']) && $value['to']) {
                                $collection = $collection->where($key, '<=', $value['to']);
                            }
                        }
                    } else {
                        $value = trim($value);
                        if ($value == '') {
                            continue;
                        }
                        switch ($compare) {
                            case 'LIKE':
                                $collection = $collection->where($key, $compare, addslashes("%$value%"));
                                break;
                            default:
                                $collection = $collection->where($key, $compare, addslashes("$value"));
                        }
                    }
                }
            }
        }
        return $collection;
    }

    /**
     * Get creator for request-asset by request ID
     * @param array|null $requestAssetIDs
     * @return array
     */
    public static function getAssetCreator($requestAssetIDs)
    {
        $tblRequestAsset = self::getTableName();
        $tblEmployee = Employee::getTableName();
        $response = array();
        $data = self::select(
            "{$tblEmployee}.name as creator_name",
            "{$tblRequestAsset}.id"
        )
            ->leftJoin("{$tblEmployee}", "{$tblEmployee}.id", "=", "{$tblRequestAsset}.created_by")
            ->whereIn("{$tblRequestAsset}.id", $requestAssetIDs)
            ->get();

        foreach ($data as $creator) {
            $response[$creator->id] = $creator->creator_name;
        }

        return array_filter($response);
    }

    /**
     * get reviewer request by Request ID
     * @param array|null $requestAssetIDs
     * @return array
     */
    public static function getReviewersRequest($requestAssetIDs)
    {
        $tblRequestAsset = self::getTableName();
        $tblEmployee = Employee::getTableName();
        $response = array();
        $data = self::select(
            "{$tblRequestAsset}.id",
            "{$tblEmployee}.name as reviewer_name"
        )
            ->join("{$tblEmployee} as {$tblEmployee}", "{$tblEmployee}.id", "=", "{$tblRequestAsset}.reviewer")
            ->whereIn("{$tblRequestAsset}.id", $requestAssetIDs)
            ->get();

        foreach ($data as $reviewer) {
            $response[$reviewer->id] = $reviewer->reviewer_name;
        }

        return array_filter($response);
    }

    /*
     * Get information petitioner
     */
    public function getPetitionerInfomation()
    {
        return Employee::find($this->employee_id);
    }

    /*
     * Get information reviewer
     */
    public function getReviewerInfomation()
    {
        return Employee::find($this->reviewer);
    }

    /*
     * Get information approver
     */
    public function getApproverInfomation()
    {
        return Employee::find($this->approver);
    }

    /*
     * Get information created by
     */
    public function getCreatorInfomation()
    {
        return Employee::find($this->created_by);
    }

    /*
     * Get request asset to allocation
     */
    public static function getRequestAssetToAllocation($employeeId, $assetCategoryId)
    {
        $tblRequestAsset = self::getTableName();
        $tblRequestAssetItem = RequestAssetItem::getTableName();
        $assetCategoryId = array_unique($assetCategoryId);
        sort($assetCategoryId);
        $collection = self::select("{$tblRequestAsset}.id", "{$tblRequestAsset}.request_name")
            ->join("{$tblRequestAssetItem}", "{$tblRequestAssetItem}.request_id", "=", "{$tblRequestAsset}.id")
            ->where("{$tblRequestAsset}.status", self::STATUS_APPROVED)
            ->where("{$tblRequestAsset}.employee_id", $employeeId)
            ->having(DB::raw('GROUP_CONCAT(' . $tblRequestAssetItem . '.asset_category_id ORDER BY ' . $tblRequestAssetItem . '.asset_category_id ASC)'), '=', implode(',', $assetCategoryId))
            ->groupBy("{$tblRequestAssetItem}.request_id");

        return $collection->get();
    }

    /**
     * Get label state
     * @return array
     */
    public static function labelStates()
    {
        return [
            self::STATUS_INPROGRESS => Lang::get('asset::view.Inprogress'),
            self::STATUS_REJECT => Lang::get('asset::view.Reject'),
            self::STATUS_REVIEWED => Lang::get('asset::view.Reviewed'),
            self::STATUS_APPROVED => Lang::get('asset::view.Approved'),
            self::STATUS_CLOSE => Lang::get('asset::view.Closed')
        ];
    }

    public static function countByAssetCateId($cateId = null)
    {
        $collection = AssetItem::select(DB::raw('count(id) as count'), 'category_id')
            ->where('state', AssetItem::STATE_NOT_USED)
            ->groupBy('category_id');
        if ($cateId) {
            return $collection->where('category_id', $cateId)->get()->toArray();
        }
        return $collection->get()->toArray();
    }

    /*
     * render html status
     */
    public static function renderStatusHtml($status, $statuses, $class = 'callout')
    {
        $html = '<div class="' . $class . ' text-center white-space-nowrap ' . $class;
        switch ($status) {
            case self::STATUS_INPROGRESS:
                $html .= '-warning">' . $statuses[$status];
                break;
            case self::STATUS_REJECT:
                $html .= '-default">' . $statuses[$status];
                break;
            case self::STATUS_REVIEWED:
                $html .= '-info">' . $statuses[$status];
                break;
            case self::STATUS_APPROVED:
                $html .= '-success">' . $statuses[$status];
                break;
            case self::STATUS_CLOSE:
                $html .= '-danger">' . $statuses[$status];
                break;
            default:
                return null;
        }
        return $html .= '</div>';
    }

    /*
     * render html status item
     */
    public function renderStatusHtmlItem($statuses, $class = 'callout')
    {
        return static::renderStatusHtml($this->status, $statuses, $class);
    }

    /*
     * asset category items
     */
    public function catItems()
    {
        return $this->belongsToMany('\Rikkei\Assets\Model\AssetCategory', 'request_asset_items', 'request_id', 'asset_category_id');
    }

    /**
     * get array asset_category_id => quantity of request
     * @return array
     */
    public function getArrCatsQty()
    {
        return RequestAssetItem::where('request_id', $this->id)
            ->lists('quantity', 'asset_category_id')
            ->toArray();
    }

    /*
     * create default for candidate
     */
    public static function createDefaultCdd($candidate, $employee)
    {
        if (!$employee) {
            return;
        }
        $leader = TeamMember::getLeaderByTeamId($candidate->team_id)->first();
        if (!$leader) {
            return;
        }
        $cddName = $candidate->fullname;
        $cddEmp = $candidate->employee;
        if ($cddEmp) {
            $cddName = View::getNickName($cddEmp->email);
        }
        $currUserId = auth()->id();
        $dataRequest = [
            'employee_id' => $employee->id,
            'reviewer' => $leader->id,
            'request_name' => trans('asset::view.request_asset_default_name'),
            'request_date' => Carbon::now()->format('Y-m-d'),
            'request_reason' => trans('asset::view.reqeust_asset_default_reason', ['name' => $cddName]),
            'created_by' => $currUserId,
        ];
        $requestAsset = self::create($dataRequest);
        $candidate->request_asset_id = $requestAsset->id;
        $candidate->save();
        //action after create
        self::afterCreateRequest($requestAsset);
        //history
        RequestAssetHistory::create([
            'request_id' => $requestAsset->id,
            'action' => RequestAssetHistory::ACTION_CREATE,
            'employee_id' => $currUserId
        ]);

        $requestAsssetItem = AssetCategory::getDefaultCats(['id', 'name']);
        if ($requestAsssetItem->isEmpty()) {
            return;
        }
        $dataRequestCats = [];
        foreach ($requestAsssetItem as $item) {
            $dataRequestCats[$item->id] = ['quantity' => 1];
        }
        $requestAsset->catItems()->attach($dataRequestCats);
    }

    /*
     * actions after create request asset
     */
    public static function afterCreateRequest($requestAsset)
    {
        $reviewer = $requestAsset->getReviewerInfomation();
        $petitioner = $requestAsset->getPetitionerInfomation();
        $creator = $requestAsset->getCreatorInfomation();
        $dataSendMail['mail_title'] = Lang::get('asset::view.[Rikkeisoft intranet] Request asset');
        $dataSendMail['href'] = route('asset::resource.request.view', ['id' => $requestAsset->id]);
        $dataSendMail['request_name'] = $requestAsset->request_name;
        $dataSendMail['request_date'] = $requestAsset->request_date;
        $dataSendMail['receiver_name'] = '';
        $dataSendMail['creator_name'] = '';
        $dataSendMail['reviewer_name'] = '';
        $dataSendMail['petitioner_name'] = '';
        if ($petitioner) {
            $dataSendMail['petitioner_name'] = $petitioner->name;
        }
        if ($creator) {
            $dataSendMail['creator_name'] = $creator->name;
        }
        if ($reviewer) {
            $dataSendMail['mail_to'] = $reviewer->email;
            $dataSendMail['receiver_name'] = $reviewer->name;
            $dataSendMail['reviewer_name'] = $reviewer->name;
            $template = 'asset::request.mail.create_request_send_to_reviewer';
            $dataSendMail['to_id'] = $reviewer->id;
            $dataSendMail['noti_content'] = trans('asset::view.Have new a request assset has been created and asign to you to review.');
            AssetView::pushEmailToQueue($dataSendMail, $template);
        }
        if ($requestAsset->employee_id != $requestAsset->created_by && $petitioner) {
            $dataSendMail['mail_to'] = $petitioner->email;
            $dataSendMail['receiver_name'] = $petitioner->name;
            $template = 'asset::request.mail.create_request_send_to_petitioner';
            $dataSendMail['to_id'] = $petitioner->id;
            $dataSendMail['noti_content'] = trans('asset::view.Have new a request assset has been created for you.');
            AssetView::pushEmailToQueue($dataSendMail, $template);
        }
    }

    /*
     * actions after update request asset
     */
    public static function afterUpdateRequest($requestAsset)
    {
        $reviewer = $requestAsset->getReviewerInfomation();
        $petitioner = $requestAsset->getPetitionerInfomation();
        $creator = $requestAsset->getCreatorInfomation();
        $dataSendMail['mail_title'] = Lang::get('asset::view.[Rikkeisoft intranet] Request asset');
        $dataSendMail['href'] = route('asset::resource.request.view', ['id' => $requestAsset->id]);
        $dataSendMail['request_name'] = $requestAsset->request_name;
        $dataSendMail['request_date'] = $requestAsset->request_date;
        $dataSendMail['receiver_name'] = '';
        $dataSendMail['creator_name'] = '';
        $dataSendMail['reviewer_name'] = '';
        $dataSendMail['petitioner_name'] = '';
        if ($petitioner) {
            $dataSendMail['petitioner_name'] = $petitioner->name;
        }
        if ($creator) {
            $dataSendMail['creator_name'] = $creator->name;
        }
        if ($reviewer) {
            $dataSendMail['mail_to'] = $reviewer->email;
            $dataSendMail['receiver_name'] = $reviewer->name;
            $dataSendMail['reviewer_name'] = $reviewer->name;
            $template = 'asset::request.mail.create_request_send_to_reviewer';
            $dataSendMail['to_id'] = $reviewer->id;
            $dataSendMail['noti_content'] = trans('asset::view.Have new a request assset has been updated and asign to you to review.');
            AssetView::pushEmailToQueue($dataSendMail, $template);
        }
        if ($requestAsset->employee_id != $requestAsset->created_by && $petitioner) {
            $dataSendMail['mail_to'] = $petitioner->email;
            $dataSendMail['receiver_name'] = $petitioner->name;
            $template = 'asset::request.mail.create_request_send_to_petitioner';
            $dataSendMail['to_id'] = $petitioner->id;
            $dataSendMail['noti_content'] = trans('asset::view.Have new a request assset has been updated for you.');
            AssetView::pushEmailToQueue($dataSendMail, $template);
        }
    }

    /*
     * list my requests
     */
    public static function getMyRequests($data = [])
    {
        $opts = [
            'type' => 'creator',
            'status' => null
        ];
        $opts = array_merge($opts, $data);
        $type = $opts['type'];
        $status = $opts['status'];

        $pager = Config::getPagerData();
        $scope = Permission::getInstance();
        $employeeId = $scope->getEmployee()->id;
        $tblRq = self::getTableName();
        $tblEmp = Employee::getTableName();
        $tblTmb = TeamMember::getTableName();
        $tblTeam = Team::getTableName();
        $tblRqAsHistory = RequestAssetHistory::getTableName();

        $collection = self::select(
            $tblRq . '.id',
            $tblRq . '.request_name',
            $tblRq . '.request_date',
            $tblRq . '.status',
            'emp.email as emp_email',
            'reviewer.email as reviewer_email',
            'approver.email as approver_email',
            'creator.email as creator_email',
            DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(role.role, " - ", team.name)) SEPARATOR "; ") as team_names'),
            DB::raw('GROUP_CONCAT(rqAsHis.created_at ORDER BY rqAsHis.id DESC) as action_date')
        )
            ->leftJoin($tblEmp . ' as approver', $tblRq . '.approver', '=', 'approver.id')
            ->leftJoin($tblEmp . ' as reviewer', $tblRq . '.reviewer', '=', 'reviewer.id')
            ->join($tblEmp . ' as emp', $tblRq . '.employee_id', '=', 'emp.id')
            ->leftJoin($tblEmp . ' as creator', $tblRq . '.created_by', '=', 'creator.id')
            ->join($tblTmb . ' as tmb', $tblRq . '.employee_id', '=', 'tmb.employee_id')
            ->leftJoin($tblTeam . ' as team', 'tmb.team_id', '=', 'team.id')
            ->leftJoin(Role::getTableName() . ' as role', 'tmb.role_id', '=', 'role.id')
            ->leftJoin($tblRqAsHistory . ' as rqAsHis', 'rqAsHis.request_id', '=', $tblRq.'.id')
            ->groupBy($tblRq . '.id');

        if ($status) {
            $collection->where($tblRq . '.status', $status);
        }

        switch ($type) {
            case 'reviewer':
                $collection->where($tblRq . '.reviewer', $employeeId);
                break;
            case 'approver':
                $routeApprove = 'asset::resource.request.approve';
                $teamCode = explode('_', Employee::getNewestTeamCode($employeeId))[0];

                if (Permission::getInstance()->isScopeCompany(null, $routeApprove)) {
                    //get all
                } elseif (Permission::getInstance()->isScopeTeam(null, $routeApprove)) {
                    $teamIds = TeamMember::where('employee_id', $employeeId)
                        ->lists('team_id')
                        ->toArray();
                    $teamIds = Team::teamChildIds($teamIds);
                    $collection->leftJoin($tblTmb . ' as tmb_creator', $tblRq . '.created_by', '=', 'tmb_creator.employee_id')
                        ->leftJoin($tblTeam . ' as team_creator', 'tmb_creator.team_id', '=', 'team_creator.id')
                        ->where(function ($query) use ($teamIds, $employeeId, $tblRq, $teamCode) {
                            $query->whereIn('tmb.team_id', $teamIds)
                                ->orWhere($tblRq . '.employee_id', $employeeId)
                                ->orWhere($tblRq . '.created_by', $employeeId)
                                ->orWhere('team.code', 'like', $teamCode . '%')
                                ->orWhere('team_creator.code', 'like', $teamCode . '%');
                        });
                } else {
                    $collection->whereNull($tblRq . '.id');
                }
                break;
            case 'creator':
            default:
                $collection->where(function ($query) use ($tblRq, $employeeId) {
                    $query->where($tblRq . '.employee_id', $employeeId)
                        ->orWhere($tblRq . '.created_by', $employeeId);
                });
                break;
        }

        if ($teamFilter = Form::getFilterData('excerpt', 'team')) {
            $collection->leftJoin($tblTmb . ' as tmb_filter', $tblRq . '.employee_id', '=', 'tmb_filter.employee_id')
                ->where('tmb_filter.team_id', $teamFilter);
        }
        if (Form::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy($tblRq . '.request_date', 'desc');
        }
        self::filterGrid($collection);
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /*
     * my register statistic
     */
    public static function myStatistic()
    {
        $listStatues = self::labelStates();
        $listStatues[null] = null;
        $empId = Permission::getInstance()->getEmployee()->id;
        $routeApprove = 'asset::resource.request.approve';
        $hasPermissApprove = Permission::getInstance()->isAllow($routeApprove);

        $collect = self::whereNull('deleted_at');

        foreach (array_keys($listStatues) as $status) {
            $sqlStatus = '';
            $asStatus = '';
            if ($status) {
                $sqlStatus = ' AND status = ' . $status;
                $asStatus = '_' . $status;
            }
            //register
            $collect->addSelect(DB::raw('SUM(CASE WHEN (employee_id = ' . $empId . ' OR created_by = ' . $empId . ')' . $sqlStatus . ' THEN 1 ELSE 0 END) AS register' . $asStatus));
            //related
            $collect->addSelect(DB::raw('SUM(CASE WHEN reviewer = ' . $empId . $sqlStatus . ' THEN 1 ELSE 0 END) AS reviewer' . $asStatus));
        }
        $collect = $collect->first()->toArray();

        //approve
        if ($hasPermissApprove) {
            $tblRq = self::getTableName();
            $teamMbTbl = TeamMember::getTableName();
            $teamTable = Team::getTableName();

            $collection = self::whereNull($tblRq . '.deleted_at')
                ->join($teamMbTbl . ' as tmb', $tblRq . '.employee_id', '=', 'tmb.employee_id')
                ->join($teamTable . ' as team', 'tmb.team_id', '=', 'team.id');
            if (Permission::getInstance()->isScopeCompany(null, $routeApprove)) {
                //get all
            } elseif (Permission::getInstance()->isScopeTeam(null, $routeApprove)) {
                $teamIds = TeamMember::where('employee_id', $empId)
                    ->lists('team_id')
                    ->toArray();
                $teamIds = Team::teamChildIds($teamIds);
                $teamCode = Employee::getNewestTeamCode($empId);
                $teamCode = explode('_', $teamCode)[0];
                $collection->leftJoin($teamMbTbl . ' as tmb_creator', $tblRq . '.created_by', '=', 'tmb_creator.employee_id')
                    ->leftJoin($teamTable . ' as team_creator', 'tmb_creator.team_id', '=', 'team_creator.id')
                    ->where(function ($query) use ($teamIds, $empId, $tblRq, $teamCode) {
                        $query->whereIn('tmb.team_id', $teamIds)
                            ->orWhere($tblRq . '.employee_id', $empId)
                            ->orWhere($tblRq . '.created_by', $empId)
                            ->orWhere('team.code', 'like', $teamCode . '%')
                            ->orWhere('team_creator.code', 'like', $teamCode . '%');
                    });
            } else {
                $collection->whereNull($tblRq . '.id');
            }
            foreach (array_keys($listStatues) as $status) {
                $sqlStatus = '';
                $asStatus = '';
                if ($status) {
                    $sqlStatus = ' AND ' . $tblRq . '.status = ' . $status;
                    $asStatus = '_' . $status;
                }
                $collection->addSelect(DB::raw('COUNT(DISTINCT(CASE WHEN 1' . $sqlStatus . ' THEN ' . $tblRq . '.id ELSE NULL END)) AS approver' . $asStatus));
            }
            $collection = $collection->first()->toArray();
            $collect = array_merge($collect, $collection);
        }
        return $collect;
    }

    public static function getRequestDetail($requestId)
    {
        return self::join('team_members', 'team_members.employee_id', '=', 'request_assets.employee_id')
            ->join('teams', 'teams.id', '=', 'team_members.team_id')
            ->groupBy('request_assets.id')
            ->where('request_assets.id', $requestId)
            ->select([
                'request_assets.*',
                DB::raw("GROUP_CONCAT(DISTINCT CONCAT(teams.name) SEPARATOR ', ') AS divison"),
            ])
            ->first();
    }

    public static function getRequestByState($state = self::STATE_NOT_YET)
    {
        return self::where('status', self::STATUS_APPROVED)
            ->where('state', $state)
            ->get();
    }
}
