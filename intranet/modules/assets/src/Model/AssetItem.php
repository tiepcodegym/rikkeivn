<?php

namespace Rikkei\Assets\Model;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Database\Eloquent\SoftDeletes;
use Lang;
use Rikkei\Assets\Model\AssetHistory;
use Rikkei\Assets\View\AssetConst;
use Rikkei\Assets\View\AssetView;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\Permission;
use Rikkei\Assets\Model\AssetWarehouse;

class AssetItem extends CoreModel
{
    use SoftDeletes;
    const STATE_NOT_USED  = 0;
    const STATE_USING = 1;
    const STATE_BROKEN_NOTIFICATION = 2;
    const STATE_BROKEN = 3;
    const STATE_SUGGEST_REPAIR_MAINTENANCE = 4;
    const STATE_REPAIRED_MAINTAINED = 5;
    const STATE_LOST_NOTIFICATION = 6;
    const STATE_LOST = 7;
    const STATE_SUGGEST_LIQUIDATE = 8;
    const STATE_LIQUIDATE = 9;
    const STATE_CANCELLED = 10;
    const STATE_SUGGEST_HANDOVER = 11;
    const STATE_CONFRIM_REJECT = 12;
    const STATE_RETURN_CUSTOMER = 15;
    const ALLOCATION_CONFIRM_FALSE = 0;
    const ALLOCATION_CONFIRM_TRUE = 1;
    const ALLOCATION_CONFIRM_NONE = 2;
    const STATE_UNAPPROVE = 13;

    protected $table = 'manage_asset_items';

    protected $fillable = [
        'id', 'code', 'name', 'team_id', 'request_id', 'category_id', 'supplier_id', 'origin_id', 'manager_id',
        'serial', 'specification', 'purchase_date', 'warranty_priod', 'warranty_exp_date', 'employee_id', 'warehouse_id',
        'received_date', 'state', 'change_date', 'reason', 'note', 'allocation_confirm', 'prefix', 'created_at', 'updated_at', 'employee_note', 'note_of_emp',
        'out_of_date', 'days_before_alert_ood', 'configure',
    ];

    protected $formatDateFields = ['purchase_date', 'warranty_exp_date', 'out_of_date'];

    public $timestamps = true;

    /*
     * Get team manage name
     */
    public function getTeamManageName()
    {
        $team = Team::select('name')
            ->where('id', $this->team_id)
            ->first();
        if (!$team) {
            return '';
        }
        return $team->name;
    }

    /*
     * Get asset origin name
     */
    public function getAssetOriginName()
    {
        $origin = AssetOrigin::select('name')
            ->where('id', $this->origin_id)
            ->first();
        if (!$origin) {
            return '';
        }
        return $origin->name;
    }

    /*
     * Get asset supplier name
     */
    public function getAssetSupplierName()
    {
        $supplier = AssetSupplier::select('name')
            ->where('id', $this->supplier_id)
            ->first();
        if (!$supplier) {
            return '';
        }
        return $supplier->name;
    }

    /*
     * Get asset category name
     */
    public function getAssetCategoryName()
    {
        $category = AssetCategory::select('name')
            ->where('id', $this->category_id)
            ->first();
        if (!$category) {
            return '';
        }
        return $category->name;
    }

    /**
     * format original date to 'd-m-Y'
     *
     * @return $this
     */
    public function formatDateFields()
    {
        $fields = $this->formatDateFields;
        foreach ($fields as $field) {
            if (!$this->{$field}) {
                continue;
            }
            $this->{$field} = Carbon::parse($this->{$field})->format('d-m-Y');
        }
        return $this;
    }

    /**
     *  convert to date string format from 'd-m-Y'
     *
     * @param array $data list data need insert or update
     * @return array
     */
    public function toDateStringFields($data = [])
    {
        $fields = $this->formatDateFields;
        try {
            foreach ($fields as $field) {
                if (isset($data[$field]) && $data[$field]) {
                    $data[$field] = Carbon::createFromFormat('d-m-Y', $data[$field])->toDateString();
                } else {
                    $data[$field] = null;
                }
            }
        } catch (\Exception $ex) {
            //error format date
        }
        return $data;
    }

    /**
     * Get collection to show grid
     * @param  [int|null] $employeeId
     * @return type
     */
    public static function getGridData($options = [])
    {
        $tblAssetItem = self::getTableName();
        $tblAssetCategory = AssetCategory::getTableName();
        $teamTable = Team::getTableName();
        $tblWarehouse = AssetWarehouse::getTableName();
        $teamMemberTable = TeamMember::getTableName();
        $collection = self::select("{$tblAssetItem}.id",
            "{$tblAssetItem}.code as asset_code",
            "{$tblAssetItem}.name as asset_name",
            "{$tblAssetItem}.state",
            "{$tblAssetItem}.received_date",
            "{$tblAssetItem}.allocation_confirm",
            "{$tblAssetItem}.note_of_emp",
            "{$tblAssetItem}.category_id",
            "{$tblAssetItem}.manager_id",
            "{$tblAssetItem}.employee_id",
            "{$teamTable}.name as team_name",
            "{$tblAssetCategory}.name as category_name",
            "{$tblWarehouse}.name as warehouse_name", "{$tblAssetItem}.state as state_asset",
            "{$tblAssetItem}.allocation_confirm as status_confirm")
            ->join("{$tblAssetCategory}", "{$tblAssetCategory}.id", "=", "{$tblAssetItem}.category_id")
            ->leftJoin("{$tblWarehouse}", "{$tblAssetItem}.warehouse_id", "=", "{$tblWarehouse}.id")
            ->leftJoin("{$teamTable}", "{$teamTable}.id", "=", "{$tblAssetItem}.team_id")
            ->orderBy("category_name")
            ->orderBy("{$tblAssetItem}.allocation_confirm");

        if (!empty($options['asset_name'])) {
            $collection->where("$tblAssetItem.name", 'Like', '%'.trim($options['asset_name']).'%');
        }
        if (!empty($options['asset_code'])) {
            $collection->where("$tblAssetItem.code", 'Like', '%'.trim($options['asset_code']).'%');
            // $collection->where(function($query) use ($tblAssetItem, $options) {
            //     $query->where("$tblAssetItem.code", 'Like', '%'.trim($options['asset_code']).'%')
            //         ->orWhere("$tblAssetItem.serial", 'Like', '%'.trim($options['asset_code']).'%');
            // });
        }
        if (!empty($options['category_name'])) {
            $collection->where('category_id', $options['category_name']);
        }
        if (!empty($options['warehouse_name'])) {
            $collection->where('warehouse_id', $options['warehouse_name']);
        }
        if (!empty($options['manager_name'])) {
            $listManagerID = Employee::getEmployeeIDByNameOrEmail($options['manager_name']);
            $collection->whereIn('manager_id', $listManagerID);
        }
        if (!empty($options['user_name'])) {
            $listEmployeeID = Employee::getEmployeeIDByNameOrEmail($options['user_name']);
            $collection->whereIn("{$tblAssetItem}.employee_id", $listEmployeeID);
        }
        if (is_numeric($options['state'])) {
            $collection->where("$tblAssetItem.state", $options['state']);
        }
        if (is_numeric($options['allocation_confirm'])) {
            $collection->where('allocation_confirm', $options['allocation_confirm']);
        }
        if (isset($options['employee_id'])) {
            $collection = $collection->where("$tblAssetItem.employee_id", $options['employee_id']);
        }
        if (isset($options['ids'])) {
            $collection = $collection->whereIn("$tblAssetItem.id", $options['ids']);
        }
        if (!empty($options['_configure']) || !empty($options['_serial'])) {
            $collection->where(function($query) use ($tblAssetItem, $options) {
                if (!empty($options['_configure'])) {
                    $query->whereNull("{$tblAssetItem}.configure")
                        ->orWhere("{$tblAssetItem}.configure" ,'=', '');
                }
                if (!empty($options['_serial'])) {
                    $query->orWhereNull("{$tblAssetItem}.serial")
                        ->orWhere("{$tblAssetItem}.serial" ,'=', '');
                }
            });
        }
        if (isset($options['check_profile']) && $options['check_profile']) {
            return $collection->groupBy("$tblAssetItem.id")->get();
        }
        $url =  route('asset::asset.index') . '/';
        if ($options) {
            Form::setFilterData('filter', $options, null, $url);
        } else {
            if (Form::getFilterData('filter', null, $url)) {
                Form::forgetFilter($url);
            }
        }
        $scope = Permission::getInstance();
        $curEmp = $scope->getEmployee();
        $regionEmp = AssetView::getRegionByEmp($curEmp->id);

        if ($scope->isScopeCompany()) {
            //nothing
        } elseif ($teamIds = $scope->isScopeTeam()) {
            !is_array($teamIds) ? array($teamIds) : $teamIds;
            $collection->join("$teamMemberTable", "$teamMemberTable.employee_id", "=", "$tblAssetItem.created_by");
            $collection->where(function ($sql) use ($teamMemberTable, $teamIds, $tblAssetItem, $regionEmp) {
                $sql->whereIn("$teamMemberTable.team_id", $teamIds)
                    ->orWhere("{$tblAssetItem}.prefix", $regionEmp);
            });
        } elseif ($scope->isScopeSelf()) {
            $collection->where(function ($query) use ($tblAssetItem, $curEmp) {
                $query->where("$tblAssetItem.created_by", $curEmp->id)
                    ->orWhere("$tblAssetItem.employee_id", $curEmp->id);
            });
        } else {
            View::viewErrorPermission();
        }
        return $collection->groupBy("$tblAssetItem.id")->get();
    }

    /**
     * Danh sách người duyệt khi tài sản đang được sử dụng
     * @return array
     */
    public static function getApprovedAsset()
    {
        $tblAssetItem = self::getTableName();
        $tblEmployee = Employee::getTableName();
        $assetHistoryTbl = AssetHistory::getTableName();
        $response = array();
        $data = DB::table("{$tblAssetItem} AS item1")
            ->select('item1.id', "{$tblEmployee}.name AS approver", 'item1.state', 'item2.created_by')
            ->join(
                DB::raw("(SELECT s.asset_id, s.created_by, s.state, s.created_at 
                FROM {$assetHistoryTbl} AS s 
                JOIN (SELECT MAX(id) AS maxid 
                FROM {$assetHistoryTbl} 
                GROUP BY {$assetHistoryTbl}.asset_id) AS s1 ON s.id = s1.maxid) AS item2"), 'item1.id', '=', 'item2.asset_id'
            )
            ->where('item1.state', '=', self::STATE_USING)
            ->join($tblEmployee, $tblEmployee . '.id', '=', 'item2.created_by')
            ->orderBy('id')
            ->groupBy('id')
            ->get();

        foreach ($data as $approver) {
            $response[$approver->id] = $approver;
        }

        return $response;
    }

    /**
     * @return array
     */
    public static function getRoleName()
    {
        $tblAssetItem = self::getTableName();
        $response = array();
        $data = DB::table("{$tblAssetItem}")->select(
            'team_members.employee_id AS employee_id',
            DB::raw("GROUP_CONCAT(DISTINCT CONCAT(roles.role, ' - ', teams.name) ORDER BY roles.role DESC SEPARATOR '; ') AS role_name")
        )
            ->join('team_members', 'team_members.employee_id', '=', "{$tblAssetItem}" . '.id')
            ->join('teams', 'team_members.team_id', '=', 'teams.id')
            ->join('roles', 'team_members.role_id', '=', 'roles.id')
            ->whereNull("{$tblAssetItem}.deleted_at")
            ->groupBy('employee_id')
            ->get();
        foreach ($data as $approver) {
            $response[$approver->employee_id] = $approver;
        }

        return $response;
    }

    /**
     * Get manager name for asset
     * @param $options
     * @return array
     */
    public static function getManagerName($options)
    {
        $tblAssetItem = self::getTableName();
        $tblEmployee = Employee::getTableName();
        $tblEmployeeAsManage = 'tbl_employee_manage';
        $response = array();
        $filterName = '';
        if (isset($options['manager_name']) && $options['manager_name']) {
            $filterName = $options['manager_name'];
        }
        $listManagerID = Employee::getEmployeeIDByNameOrEmail($filterName);
        $data = DB::table("{$tblAssetItem}")->select(
            "{$tblAssetItem}.manager_id",
            DB::raw("SUBSTRING({$tblEmployeeAsManage}.email, 1, LOCATE('@', {$tblEmployeeAsManage}.email) - 1) as manager_name")
        )
            ->leftJoin("{$tblEmployee} as $tblEmployeeAsManage", "{$tblEmployeeAsManage}.id", "=", "{$tblAssetItem}.manager_id")
            ->whereNull("{$tblAssetItem}.deleted_at");
        if ($filterName) {
            $data = $data->whereIn('manager_id', $listManagerID);
        }
        $data = $data->groupBy('manager_id')
            ->get();
        foreach ($data as $manager) {
            $response[$manager->manager_id] = $manager->manager_name;
        }

        return array_filter($response);
    }

    public static function getUsersProperty($options)
    {
        $tblAssetItem = self::getTableName();
        $tblEmployee = Employee::getTableName();
        $tblEmployeeAsUse = 'tbl_employee_use';
        $response = array();
        $filterName = '';
        if (isset($options['user_name']) && $options['user_name']) {
            $filterName = $options['user_name'];
        }
        $listEmployeeID = Employee::getEmployeeIDByNameOrEmail($filterName);
        $data = DB::table("{$tblAssetItem}")->select(
            "{$tblEmployeeAsUse}.name as user_name",
            "{$tblEmployeeAsUse}.email as email",
            "{$tblAssetItem}.employee_id"
        )
            ->leftJoin("{$tblEmployee} as $tblEmployeeAsUse", "{$tblEmployeeAsUse}.id", "=", "{$tblAssetItem}.employee_id")
            ->whereNotNull("{$tblAssetItem}.employee_id")
            ->whereNull("{$tblAssetItem}.deleted_at");
        if ($filterName) {
            $data = $data->whereIn('employee_id', $listEmployeeID);
        }
        $data = $data->groupBy('employee_id')
            ->get();
        foreach ($data as $manager) {
            $response[$manager->employee_id] = $manager;
        }

        return array_filter($response);
    }

    /*
     * ajax get asset item of employee
     */
    public static function getGridDataAjax($dataFilter, $employeeId = null)
    {
        $collection = self::select('id', 'code as asset_code', 'name as asset_name', 'allocation_confirm', 'employee_note')
                ->where('allocation_confirm', '!=', self::ALLOCATION_CONFIRM_NONE);
        if ($employeeId) {
            $collection = $collection->where('employee_id', $employeeId);
        }
        $pagerData = ['dir' => 'DESC'];
        if (isset($dataFilter['page'])) {
            $pagerData['page'] = $dataFilter['page'];
        }
        $pager = Config::getPagerData(null, $pagerData);
        $collection->orderBy($pager['order'], $pager['dir']);
        self::filterGrid($collection, [], null, 'LIKE');
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /*
     * check unconfirmed asset item of employee
     */
    public static function hasUnconfirmedAsset($employeeId)
    {
        $items = self::select('code')
                ->where('employee_id', $employeeId)
                ->where('allocation_confirm', self::ALLOCATION_CONFIRM_NONE)
                ->get();
        if ($items->isEmpty()) {
            return null;
        }
        $result = [];
        foreach ($items as $item) {
            $result[] = e($item->code);
        }
        return implode(', ', $result);
    }

    /**
     * Get asset item by id
     * @param  [int|null] $assetItemId
     * @return [model]
     */
    public static function getAssetItemById($assetItemId)
    {
        $tblAssetItem = self::getTableName();
        $tblAssetCategory = AssetCategory::getTableName();
        $tblRequestAsset = RequestAsset::getTableName();
        $tblEmployee = Employee::getTableName();
        $tblEmployeeAsManage = 'tbl_employee_manage';
        $tblEmployeeAsUse = 'tbl_employee_use';
        $tblWarehouse = AssetWarehouse::getTableName();

        $collection = self::select(
                "{$tblAssetItem}.id",
                "{$tblAssetItem}.code",
                "{$tblAssetItem}.name",
                "{$tblAssetItem}.team_id",
                "{$tblAssetItem}.category_id",
                "{$tblAssetItem}.supplier_id",
                "{$tblAssetItem}.origin_id",
                "{$tblAssetItem}.serial",
                "{$tblAssetItem}.specification",
                "{$tblAssetItem}.purchase_date",
                "{$tblAssetItem}.warranty_priod",
                "{$tblAssetItem}.warranty_exp_date",
                "{$tblAssetItem}.received_date",
                "{$tblAssetItem}.note",
                "{$tblAssetItem}.state",
                "{$tblAssetItem}.manager_id",
                "{$tblAssetItem}.employee_id",
                "{$tblEmployeeAsUse}.name as employee_name",
                "{$tblAssetCategory}.name as category_name",
                "{$tblEmployeeAsManage}.name as manager_name",
                "{$tblEmployeeAsManage}.email as manager_email",
                "{$tblRequestAsset}.request_name",
                "{$tblWarehouse}.name as warehouse_name",
                "$tblAssetItem.warehouse_id",
                "$tblAssetItem.prefix",
                "$tblAssetItem.out_of_date",
                "$tblAssetItem.days_before_alert_ood",
                "$tblAssetItem.configure"
            )
            ->join("{$tblAssetCategory}", "{$tblAssetCategory}.id", "=", "{$tblAssetItem}.category_id")
            ->leftJoin("{$tblEmployee} as $tblEmployeeAsManage", "{$tblEmployeeAsManage}.id", "=", "{$tblAssetItem}.manager_id")
            ->leftJoin("{$tblEmployee} as $tblEmployeeAsUse", "{$tblEmployeeAsUse}.id", "=", "{$tblAssetItem}.employee_id")
            ->leftJoin("{$tblRequestAsset}", "{$tblRequestAsset}.id", "=", "{$tblAssetItem}.request_id")
            ->leftJoin("{$tblWarehouse}", "{$tblWarehouse}.id", "=", "{$tblAssetItem}.warehouse_id");
        if (is_array($assetItemId)) {
            return $collection->whereIn("{$tblAssetItem}.id", $assetItemId)->get();
        } else {
            return $collection->where("{$tblAssetItem}.id", $assetItemId)->first();
        }
    }

    /**
     * Rewrite save
     * @param array $options
     */
    public function save(array $options = [])
    {
        try {
            parent::save($options);
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Get employee asset allocated
     *
     * @param int $employeeId
     * @return [model]
     */
    public static function getEmployeeAllocated($employeeId)
    {
        if (!$employeeId) {
            return null;
        }
        return Employee::select('name', 'email')
            ->where('id', $employeeId)
            ->first();
    }

    /**
     * Get asset code max by category
     * @param  [int|null] $categoryId
     * @param  [string] $prefixTeamCode
     * @return [string]
     */
    public static function getMaxAssetCodeByCategory($categoryId, $prefixAsset = null)
    {
        $collection = self::select('code')->where('category_id', $categoryId);
        if ($prefixAsset) {
            $collection->where('code', 'LIKE', $prefixAsset.'%');
        }
        return $collection->withTrashed()->orderBy('id', 'desc')->first();
    }

    /**
     * Get next asset code
     * @param  [int] $assetCategoryId
     * @param  [int|null] $assetItemId
     * @return [string]
     */
    public static function getAssetCodeByCategory($assetCategoryId, $assetItemId = null)
    {
        if ($assetItemId) {
            $assetItem = self::select('code')->where('id', $assetItemId)->where('category_id', $assetCategoryId)->first();
            if ($assetItem) {
                return $assetItem->code;
            }
        }
        $assetCategory = AssetCategory::find($assetCategoryId);
        if (!$assetCategory) {
            return '';
        }
        $assetCodePrefix = $assetCategory->prefix_asset_code;
        $curEmp = Permission::getInstance()->getEmployee();
        $regionByEmp = AssetView::getRegionByEmp($curEmp->id);
        $assetCodePrefix = $regionByEmp . $assetCodePrefix;
        $maxAssetCode = self::getMaxAssetCodeByCategory($assetCategoryId, $assetCodePrefix);
        $maxAssetCode = filter_var($maxAssetCode, FILTER_SANITIZE_NUMBER_INT);
        $maxAssetCode = intval($maxAssetCode);

        return AssetView::generateCode($assetCodePrefix, $maxAssetCode);
    }

    /**
     * Get assets list by state
     * @param  [array] $states
     * @return [array]
     */
    public static function getAssetItemsByState($states)
    {
        $tblAssetItem = self::getTableName();
        $tblAssetCategory = AssetCategory::getTableName();
        $tblEmployee = Employee::getTableName();
        $tblEmployeeAsManage = 'tbl_employee_manage';
        $tblEmployeeAsUse = 'tbl_employee_use';
        $roleTable = Role::getTableName();
        $teamTable = Team::getTableName();
        $teamMemberTable = TeamMember::getTableName();
        $tblPosition = 'tbl_position';

        return self::select("{$tblAssetItem}.id", "{$tblAssetItem}.code", "{$tblAssetItem}.name", "{$tblAssetItem}.serial", "{$tblAssetItem}.change_date", "{$tblAssetItem}.reason", "{$tblAssetItem}.state", "{$tblAssetItem}.employee_id", "{$tblEmployeeAsUse}.name as user_name", "{$tblAssetCategory}.name as category_name", "{$tblPosition}.role_name")
            ->join("{$tblAssetCategory}", "{$tblAssetCategory}.id", "=", "{$tblAssetItem}.category_id")
            ->leftJoin("{$tblEmployee} as $tblEmployeeAsManage", "{$tblEmployeeAsManage}.id", "=", "{$tblAssetItem}.manager_id")
            ->leftJoin("{$tblEmployee} as $tblEmployeeAsUse", "{$tblEmployeeAsUse}.id", "=", "{$tblAssetItem}.employee_id")
            ->leftJoin(DB::raw("(SELECT {$teamMemberTable}.employee_id as employee_id, GROUP_CONCAT(DISTINCT CONCAT({$roleTable}.role, ' - ', {$teamTable}.name) ORDER BY {$roleTable}.role DESC SEPARATOR '; ') as role_name FROM {$teamMemberTable} JOIN {$tblEmployee} ON {$teamMemberTable}.employee_id = {$tblEmployee}.id JOIN {$teamTable} ON {$teamMemberTable}.team_id = {$teamTable}.id JOIN {$roleTable} ON {$teamMemberTable}.role_id = {$roleTable}.id GROUP BY {$tblEmployee}.id) AS {$tblPosition}"), "{$tblPosition}.employee_id", '=', "{$tblAssetItem}.employee_id"
            )
            ->whereIn("{$tblAssetItem}.state", $states)
            ->get();
    }

    /**
     * Get assets list by category and team
     *
     * @param  [int] $categoryId
     * @param  [int] $teamId
     * @return [array]
     */
    public static function getAssetItemsByCategory($categoryId = null, $teamId = null, $dateForm = null, $dateTo = null)
    {
        $tblAssetItem = self::getTableName();
        $tblAssetCategory = AssetCategory::getTableName();
        $tblEmployee = Employee::getTableName();
        $tblEmployeeAsManage = 'tbl_employee_manage';
        $tblEmployeeAsUse = 'tbl_employee_use';
        $tblRole = Role::getTableName();
        $tblTeam = Team::getTableName();
        $tblHistory = AssetHistory::getTableName();
        DB::statement("SET @duplicateEmpl:=-1");
        $collection = self::select(
            "{$tblAssetItem}.id",
            "{$tblAssetItem}.code",
            "{$tblAssetItem}.name",
            "{$tblAssetItem}.serial",
            "{$tblAssetItem}.change_date",
            "{$tblAssetItem}.reason",
            "{$tblAssetItem}.reason",
            "{$tblAssetItem}.received_date",
            "{$tblAssetItem}.state",
            "{$tblAssetItem}.employee_id",
            "{$tblEmployeeAsUse}.name as user_name",
            "{$tblAssetCategory}.name as category_name",
            DB::raw("CONCAT({$tblRole}.role, ' - ', {$tblTeam}.name) as role_name")
        )
        ->leftJoin("{$tblEmployee} as $tblEmployeeAsManage", "{$tblEmployeeAsManage}.id", "=", "{$tblAssetItem}.manager_id")
        ->leftJoin("{$tblEmployee} as $tblEmployeeAsUse", "{$tblEmployeeAsUse}.id", "=", "{$tblAssetItem}.employee_id")
        ->leftJoin(
            DB::raw("(select employee_id, team_id, start_at, role_id, is_duplicate, end_at
            FROM (SELECT employee_id, team_id, start_at, role_id, end_at, IF(@duplicateEmpl = employee_id, 1, 2) as is_duplicate, @duplicateEmpl := employee_id FROM `employee_team_history` as t_eth
            WHERE end_at IS NULL
            ORDER BY employee_id asc, start_at DESC) as ttt) as tmp_teh"), function ($join) {
            $join->on('tmp_teh.employee_id', '=', "manage_asset_items.employee_id")
                ->where('is_duplicate', '=', 2);
            }
        )
        ->join("{$tblAssetCategory}", "{$tblAssetCategory}.id", "=", "{$tblAssetItem}.category_id")
        ->leftJoin($tblTeam, "$tblTeam.id", '=', 'tmp_teh.team_id')
        ->leftJoin($tblRole, "$tblRole.id", '=', 'tmp_teh.role_id')
        ->join("{$tblHistory}", "{$tblAssetItem}.id", '=', "{$tblHistory}.asset_id");
        // ->where("{$tblAssetItem}.state", '<>', AssetItem::STATE_NOT_USED);
        if ($categoryId) {
            $collection->where("{$tblAssetItem}.category_id", $categoryId);
        }
        if ($dateForm) {
            $dateForm = date("Y-m-d", strtotime($dateForm));
            $collection->where("{$tblAssetItem}.received_date", '>=', $dateForm);
        }
        if ($dateTo) {
            $dateTo = date("Y-m-d", strtotime($dateTo));
            $collection->where("{$tblAssetItem}.received_date", '<=', $dateTo);
        }
        if ($teamId) {
            $teamIds = [];
            $teamIds[] = (int) $teamId;
            AssetView::getTeamChildRecursive($teamIds, $teamId);
            $collection->whereIn("{$tblTeam}.id", $teamIds);
        }
        return $collection->orderBy("{$tblAssetItem}.received_date", 'ASC')->distinct("{$tblAssetItem}.id")->get();
    }

    /**
     * Get asset item to report
     * @param  [int] $state
     * @param  [int] $typeReport
     * @param  array  $options
     * @return [array]
     */
    public static function getAssetItemsReport($state, $typeReport, array $options = [])
    {
        $tblAssetItem = self::getTableName();
        $tblAssetCategory = AssetCategory::getTableName();
        $tblEmployee = Employee::getTableName();
        $tblEmployeeAsManage = 'tbl_employee_manage';
        $tblEmployeeAsUse = 'tbl_employee_use';
        $tblTeam = Team::getTableName();
        $tblRole = Role::getTableName();
        DB::statement("SET @duplicateEmpl:=-1");
        $collection = self::select("{$tblAssetItem}.id", "{$tblAssetItem}.code", "{$tblAssetItem}.name", "{$tblAssetItem}.serial", "{$tblAssetItem}.change_date", "{$tblAssetItem}.reason", "{$tblAssetItem}.state", "{$tblAssetItem}.employee_id", "{$tblEmployeeAsUse}.name as user_name", "{$tblAssetCategory}.name as category_name", DB::raw("CONCAT({$tblRole}.role, ' - ', {$tblTeam}.name) as role_name"))
            ->join("{$tblAssetCategory}", "{$tblAssetCategory}.id", "=", "{$tblAssetItem}.category_id")
            ->leftJoin("{$tblEmployee} as $tblEmployeeAsManage", "{$tblEmployeeAsManage}.id", "=", "{$tblAssetItem}.manager_id")
            ->leftJoin("{$tblEmployee} as $tblEmployeeAsUse", "{$tblEmployeeAsUse}.id", "=", "{$tblAssetItem}.employee_id")
            ->leftjoin($tblEmployee, "$tblEmployee.id", '=', "$tblAssetItem.employee_id")
            ->leftJoin(
                DB::raw("(select employee_id, team_id, start_at, role_id, is_duplicate, end_at
                FROM (SELECT employee_id, team_id, start_at, role_id, end_at, IF(@duplicateEmpl = employee_id, 1, 2) as is_duplicate, @duplicateEmpl := employee_id FROM `employee_team_history` as t_eth
                WHERE end_at IS NULL
                ORDER BY employee_id asc, start_at DESC) as ttt) as tmp_teh"), function ($join) {
                $join->on('tmp_teh.employee_id', '=', 'employees.id')
                    ->where('is_duplicate', '=', 2);
                }
            )
            ->leftjoin($tblTeam, "$tblTeam.id", '=', 'tmp_teh.team_id')
            ->leftjoin($tblRole, "$tblRole.id", '=', 'tmp_teh.role_id')
            ->whereIn("{$tblAssetItem}.state", $state);
        if ($typeReport == AssetConst::REPORT_TYPE_LOST_AND_BROKEN) {
            $collection->whereBetween("{$tblAssetItem}.change_date", [$options['start_date']->format('Y-m-d'), $options['end_date']->format('Y-m-d')]);
            if (!empty($options['team_id'])) {
                $teamIds = [];
                $teamIds[] = (int) $options['team_id'];
                AssetView::getTeamChildRecursive($teamIds, $options['team_id']);
                $collection = $collection->whereIn("{$tblTeam}.id", $teamIds);
            }
        }
        return $collection->get();
    }

    /**
     * Get label state
     * @return array
     */
    public static function labelStates()
    {
        return [
            self::STATE_NOT_USED => Lang::get('asset::view.Not used'),
            self::STATE_USING => Lang::get('asset::view.Using'),
            self::STATE_BROKEN_NOTIFICATION => Lang::get('asset::view.Broken notification'),
            self::STATE_BROKEN => Lang::get('asset::view.Broken'),
            self::STATE_SUGGEST_REPAIR_MAINTENANCE => Lang::get('asset::view.Suggest repair, maintenance'),
            self::STATE_REPAIRED_MAINTAINED => Lang::get('asset::view.Repaired, maintained'),
            self::STATE_LOST_NOTIFICATION => Lang::get('asset::view.Lost notification'),
            self::STATE_LOST => Lang::get('asset::view.Lost'),
            self::STATE_SUGGEST_LIQUIDATE => Lang::get('asset::view.Suggest liquidate'),
            self::STATE_LIQUIDATE => Lang::get('asset::view.Liquidated'),
            self::STATE_CANCELLED => Lang::get('asset::view.Cancelled'),
            self::STATE_SUGGEST_HANDOVER => Lang::get('asset::view.Suggest Handover'),
            self::STATE_RETURN_CUSTOMER => Lang::get('asset::view.Has returned'),
        ];
    }

    /**
     * Get label allocation confirm
     * @return array
     */
    public static function labelAllocationConfirm()
    {
        return [
            self::ALLOCATION_CONFIRM_FALSE => Lang::get('asset::view.False'),
            self::ALLOCATION_CONFIRM_TRUE => Lang::get('asset::view.True'),
            self::ALLOCATION_CONFIRM_NONE => Lang::get('asset::view.Unconfirmed'),
        ];
    }

    /**
     * Count asset by warehouseId
     *
     * @param int $warehouseId
     * @return mixed
     */
    public static function countAssetByWarehouse($warehouseId)
    {
        return self::where('warehouse_id', $warehouseId)
            ->groupBy('warehouse_id')
            ->count('id');
    }

    /**
     * Get function by state asset
     *
     * @return array
     */
    public static function getFuncByState()
    {
        return [
            'allocation' => [
                self::STATE_NOT_USED
            ],
            'retrieval' => [
                self::STATE_USING,
                self::STATE_SUGGEST_HANDOVER,
                self::STATE_REPAIRED_MAINTAINED,
            ],
            'lostNotify' => [
                self::STATE_USING,
                self::STATE_NOT_USED,
            ],
            'brokenNotify' => [
                self::STATE_USING,
                self::STATE_NOT_USED,
            ],
            'sugLiquidate' => [
                self::STATE_NOT_USED,
                self::STATE_USING,
                self::STATE_SUGGEST_REPAIR_MAINTENANCE,
                self::STATE_REPAIRED_MAINTAINED,
                self::STATE_BROKEN_NOTIFICATION,
                self::STATE_BROKEN,
            ],
            'repair' => [
                self::STATE_USING,
                self::STATE_BROKEN_NOTIFICATION,
                self::STATE_BROKEN,
                self::STATE_NOT_USED,
            ],
            'handover' => [
                self::STATE_USING,
                self::STATE_BROKEN_NOTIFICATION,
            ],
            'returnCustomer' => [
                self::STATE_NOT_USED,
            ],
        ];
    }

    /**
     * Define heading sheet  import asset
     *
     * @return array
     */
    public static function defineHeadingFile()
    {
        return [
            0 => "ma_tai_san",
            1 => "ten_tai_san",
            2 => "loai_tai_san",
            3 => "so_seri",
            4 => "quy_cach_tai_san",
            5 => "ma_kho",
            6 => "ma_nha_cung_cap",
            7 => "ngay_mua",
            8 => "thoi_gian_bao_hanh",
            9 => "thuoc_don_vi",
            10 => "tinh_trang",
            11 => "ma_nhan_vien",
            12 => "ho_va_ten_nguoi_su_dung",
            13 => "ngay_nhan",
            14 => "ghi_chu",
            15 => "nguoi_quan_ly_ts",
            16 => "nguon_goc",
            17 => "ngay_thay_doi",
            18 => "don_vi_cong_tac",
            19 => "vi_tri_dia_ly",
            20 => "thoi_gian_hieu_luc",
            21 => "cau_hinh_tai_san",
        ];
    }

    /**
     * Define heading sheet compel
     *
     * @return array
     */
    public static function defineHeadingCompel()
    {
        return [
            0 => "ma_tai_san",
            1 => "ten_tai_san",
            2 => "loai_tai_san",
            3 => "tinh_trang",
        ];
    }

    /**
     * Generate warranty_exp_date form purchase_date and warranty_priod
     *
     * @param date $purchaseDate
     * @param int $warrantyPriod
     * @return static
     */
    public static function genWarrantyExpDate($purchaseDate, $warrantyPriod)
    {
        if ($purchaseDate && $warrantyPriod) {
            return Carbon::parse($purchaseDate)->addMonth($warrantyPriod);
        }
    }

    public static function getAssetByEmployee($employeeIds)
    {
        $categories = AssetCategory::pluck('name', 'id');
        $labelStates = AssetItem::labelStates();
        $tblEmployee = Employee::getTableName();
        $tblTeam = Team::getTableName();
        $tblRole = Role::getTableName();
        $tblAssetItem = self::getTableName();

        DB::statement("SET @duplicateEmpl:=-1");
        $data = Employee::select([
            "{$tblEmployee}.id as employee_id",
            "{$tblEmployee}.employee_code",
            "{$tblEmployee}.name as employee_name",
            "{$tblAssetItem}.name",
            "{$tblAssetItem}.code",
            "{$tblAssetItem}.serial",
            "{$tblAssetItem}.received_date",
            "{$tblAssetItem}.reason",
            "{$tblAssetItem}.state",
            "{$tblAssetItem}.specification",
            "{$tblAssetItem}.category_id",
            DB::raw("CONCAT({$tblRole}.role, ' - ', {$tblTeam}.name) as role_name"),
        ])
            ->leftJoin(
                DB::raw("(select employee_id, team_id, start_at, role_id, is_duplicate, end_at
                FROM (SELECT employee_id, team_id, start_at, role_id, end_at, IF(@duplicateEmpl = employee_id, 1, 2) as is_duplicate, @duplicateEmpl := employee_id FROM `employee_team_history` as t_eth
                WHERE end_at IS NULL
                ORDER BY employee_id asc, start_at DESC) as ttt) as tmp_teh"), function ($join) {
                $join->on('tmp_teh.employee_id', '=', 'employees.id')
                    ->where('is_duplicate', '=', 2);
                }
            )
            ->leftjoin($tblTeam, "$tblTeam.id", '=', 'tmp_teh.team_id')
            ->leftjoin($tblRole, "$tblRole.id", '=', 'tmp_teh.role_id')
            ->leftjoin($tblAssetItem, "$tblAssetItem.employee_id", '=', "$tblEmployee.id")
            ->whereNull("{$tblAssetItem}.deleted_at")
            ->whereIn('employees.id', $employeeIds)
            ->get();

        $output = [];
        foreach ($data as $item) {
            if (!isset($output[$item->employee_id])) {
                $output[$item->employee_id] = [
                    'employee_id' => $item->employee_id,
                    'employee_code' => $item->employee_code,
                    'employee_name' => $item->employee_name,
                    'role_name' => $item->role_name,
                    'assets' => [],
                ];
            }
            if ($item->code && $output[$item->employee_id]['employee_id'] == $item->employee_id) {
                $output[$item->employee_id]['assets'][] = [
                    'code' => $item->code,
                    'name' => $item->name,
                    'serial' => $item->serial,
                    'state_asset' => $labelStates[$item->state],
                    'received_date' => $item->received_date,
                    'specification' => $item->specification,
                    'category' => $categories[$item->category_id],
                ];
            }
        }
        return array_values($output);
    }

    /**
     * get case assets branch hanoi by email of employee
     * @param $email
     * @param array $codeAsset
     * @return mixed
     */
    public function getCaseHNAssetByEmailEmployee($email, $codeAsset = ['HNCA'])
    {
        $tblEmployee = Employee::getTableName();
        $tblAssetItem = self::getTableName();

        return Employee::select([
            "{$tblEmployee}.id as employee_id",
            "{$tblEmployee}.employee_code",
            "{$tblEmployee}.email as employee_email",
            "{$tblEmployee}.name as employee_name",
            "{$tblAssetItem}.id as asset_id",
            "{$tblAssetItem}.name as asset_name",
            "{$tblAssetItem}.code as asset_code"
        ])
        ->leftjoin($tblAssetItem, "$tblAssetItem.employee_id", '=', "$tblEmployee.id")
        ->whereNull("{$tblAssetItem}.deleted_at")
        ->where(function ($query) use ($codeAsset) {
            foreach ($codeAsset as $code) {
                $query->orWhere('code', 'LIKE', $code . '%');
            }
        })
        ->whereNotNull("{$tblAssetItem}.id")
        ->whereIn('employees.email', $email)
        ->get();
    }

    /*
     * list id => confirm by employee id
     */
    public static function listIdsByEmployee($employeeId)
    {
        return self::select('id', 'allocation_confirm')
                ->where('employee_id', $employeeId)
                ->lists('id')
                ->toArray();
    }

    /*
     * update employee confirmed asset item
     */
    public static function updateEmployeeConfirmed($employeeId, $assetIds, $employeeNotes = [])
    {
        //update not confirmed
        self::where('employee_id', $employeeId)
                ->whereNotIn('id', $assetIds)
                ->update(['allocation_confirm' => self::ALLOCATION_CONFIRM_FALSE]);
        //update confirmed
        self::where('employee_id', $employeeId)
                ->whereIn('id', $assetIds)
                ->update(['allocation_confirm' => self::ALLOCATION_CONFIRM_TRUE]);
        //update note
        if ($employeeNotes && is_array($employeeNotes)) {
            foreach ($employeeNotes as $assetId => $note) {
                self::where('id', $assetId)->update(['employee_note' => $note]);
            }
        }
    }

    /*
     * search ajax (select2)
     */
    public static function searchAjax($name, array $config = [], $empId = '', $status = self::STATE_NOT_USED)
    {
        $result = [];
        $arrayDefault = [
            'page' => 1,
            'limit' => 20,
        ];
        $config = array_merge($arrayDefault, $config);
        $collection = self::select('id', DB::raw('CONCAT(code, " - ", name) as text'))
                ->where(function ($query) use ($name) {
                    $query->where('name', 'LIKE', '%' . $name . '%')
                            ->orWhere('code', 'LIKE', '%' . $name . '%');
                })
                ->where('state', $status)
                ->orderBy('code');
        if (isset($config['cat_id']) && $config['cat_id']) {
            $collection->where('category_id', $config['cat_id']);
        }
        if (isset($config['exclude_ids']) && $config['exclude_ids']) {
            $collection->whereNotIn('id', $config['exclude_ids']);
        }
        if ($empId) {
            $collection->where('employee_id', $empId);
        }
//        if (isset($config['employee_id']) && ($empId = $config['employee_id'])) {
//            $assetPrefix = AssetConst::getAssetPrefixByCode(Employee::getNewestTeamCode($empId));
//            if ($assetPrefix === null) {
//                //set null
//                $collection->whereNull('id');
//            } elseif ($assetPrefix === AssetConst::PREFIX_ALL) {
//                //get all
//            } else {
//                $collection->where('prefix', $assetPrefix);
//            }
//        }

        self::pagerCollection($collection, $config['limit'], $config['page']);
        $result['items'] = $collection->items();
        $result['total_count'] = $collection->total();
        $result['avatar'] = false;
        return $result;
    }

    public static function searchAjaxByWarehouse($name, array $config = [], $managerId = '', $branch = null)
    {
        if (!$branch) {
            return [];
        }
        $managers = AssetWarehouse::where('manager_id', $managerId)->get();
        $isCheck = false;
        if ($managers) {
            foreach ($managers as $item) {
                if ($item->branch == $branch) {
                    $isCheck = true;
                    break;
                }
            }
            if ($isCheck) {
                $warehouseIds = AssetWarehouse::where('branch', $branch)->get()->pluck('id')->toArray();
            }
        }
        if (empty($warehouseIds)) {
            return [];
        }

        $result = [];
        $arrayDefault = [
            'page' => 1,
            'limit' => 20,
        ];
        $config = array_merge($arrayDefault, $config);
        $collection = self::select('id', DB::raw('CONCAT(code, " - ", name) as text'))
                ->where(function ($query) use ($name) {
                    $query->where('name', 'LIKE', '%' . $name . '%')
                            ->orWhere('code', 'LIKE', '%' . $name . '%');
                })
                ->whereIn('warehouse_id', $warehouseIds)
                ->where('state', self::STATE_NOT_USED)
                ->orderBy('code');
        if (isset($config['cat_id']) && $config['cat_id']) {
            $collection->where('category_id', $config['cat_id']);
        }
        if (isset($config['exclude_ids']) && $config['exclude_ids']) {
            $collection->whereNotIn('id', $config['exclude_ids']);
        }

        self::pagerCollection($collection, $config['limit'], $config['page']);
        $result['items'] = $collection->items();
        $result['total_count'] = $collection->total();
        $result['avatar'] = false;
        return $result;
    }

    /**
     * @param $assetItems
     */
    public static function updateAllocationConfirm($assetItems)
    {
        $assetItems->where('state', '<>', AssetItem::STATE_NOT_USED)->update(['allocation_confirm' => AssetItem::ALLOCATION_CONFIRM_NONE]);
    }

    /**
     * get asset state by action
     * @param type $action
     * @return type
     */
    public static function getStateByAction($action)
    {
        switch ($action) {
            case AssetConst::TYPE_HANDING_OVER:
                return AssetItem::STATE_NOT_USED;
            case AssetConst::TYPE_BROKEN_NOTIFY:
                return AssetItem::STATE_BROKEN;
            case AssetConst::TYPE_LOST_NOTIFY:
                return AssetItem::STATE_LOST;
            default:
                return null;
        }
    }

    /**
     * get employee not confirm assets
     */
    public static function getEmployeeNotConfirmAssets()
    {
        $tblEmployee = Employee::getTableName();
        $tblAssetItem = self::getTableName();
        $tblRequestAsset = RequestAsset::getTableName();
        $tblEmployeeAsReviewer = 'tbl_employee_review';
        $tblEmployeeAsUse = 'tbl_employee_use';
        $tblEmployeeCreator = 'tbl_employee_Creator';
        $collection = self::select(
            "{$tblAssetItem}.request_id",
            "{$tblRequestAsset}.request_name",
            "{$tblRequestAsset}.request_reason",
            "{$tblAssetItem}.id as asset_id",
            "{$tblAssetItem}.code",
            "{$tblRequestAsset}.reviewer as reviewer_id",
            "{$tblRequestAsset}.employee_id",
            "{$tblEmployeeAsReviewer}.name as reviewer_name",
            "{$tblEmployeeAsReviewer}.email as reivew_email",
            "{$tblEmployeeAsUse}.name as employee_name",
            "{$tblEmployeeAsUse}.email as employee_email",
            "{$tblEmployeeCreator}.name as creator_name",
            "{$tblRequestAsset}.created_at as request_date"
            )
            ->leftJoin("{$tblRequestAsset}", "{$tblRequestAsset}.id", "=", "{$tblAssetItem}.request_id")
            ->leftJoin("{$tblEmployee} as $tblEmployeeAsReviewer", "{$tblEmployeeAsReviewer}.id", "=", "{$tblRequestAsset}.reviewer")
            ->leftJoin("{$tblEmployee} as $tblEmployeeAsUse", "{$tblEmployeeAsUse}.id", "=", "{$tblRequestAsset}.employee_id")
            ->leftJoin("{$tblEmployee} as $tblEmployeeCreator", "{$tblEmployeeCreator}.id", "=", "{$tblRequestAsset}.created_by")
            ->where("{$tblAssetItem}.allocation_confirm", "=", AssetItem::ALLOCATION_CONFIRM_NONE);
        return $collection->get();
    }

    /**
     * get label action
     * @param type $action
     * @return type
     */
    public static function getLabelByAction($action)
    {
        switch ($action) {
            case AssetConst::TYPE_HANDING_OVER:
                return trans('asset::view.Approve asset handover');
            case AssetConst::TYPE_BROKEN_NOTIFY:
                return trans('asset::view.Approval of broken notification asset');
            case AssetConst::TYPE_LOST_NOTIFY:
                return trans('asset::view.Approval of lost notification asset');
            default:
                return null;
        }
    }

    /**
     * @param array $assetIds
     * @param array $colSelected
     * @return mixed
     */
    public static function getAssetByIds($assetIds, $colSelected = ['*'])
    {
        return self::select($colSelected)->whereIn('id', $assetIds)->get();
    }

    /**
     * Báo cáo biến động tài sản
     *
     * @param $data
     * @return mixed
     */
    public function getReportFluctuation($data)
    {
        $categoryId = $data['categoryId'];
        $teamId = $data['teamId'];
        $dateForm = $data['dateFrom'];
        $dateTo = $data['dateTo'];

        $tblAssetItem = self::getTableName();
        $tblHistory = AssetHistory::getTableName();
        $tblEmpTeamHis = EmployeeTeamHistory::getTableName();
        $collection = self::select(
            "{$tblAssetItem}.id",
            "{$tblAssetItem}.code",
            "{$tblAssetItem}.name",
            "{$tblAssetItem}.serial",
            "{$tblAssetItem}.change_date",
            "{$tblAssetItem}.reason",
            "{$tblAssetItem}.reason",
            "{$tblAssetItem}.received_date",
            "{$tblAssetItem}.state",
            "{$tblAssetItem}.employee_id",
            "eth.team_id"
        )
        ->leftJoin("{$tblHistory} as mah", "mah.asset_id", "=", "{$tblAssetItem}.id")
        ->leftJoin("{$tblEmpTeamHis} as eth", "eth.employee_id", "=", "{$tblAssetItem}.employee_id")
        ->leftJoin("{$tblEmpTeamHis} as ethh", function($join) {
            $join->on("ethh.employee_id", "=", "mah.employee_id")
                ->on(function ($query) {
                    $query->on("ethh.end_at", ">=", "mah.created_at")
                        ->orWhereNull('ethh.end_at');
                })
                ->on("ethh.start_at", "<=", "mah.created_at");
        })
        ->whereNull("{$tblAssetItem}.deleted_at")
        ->whereNotNull("mah.employee_id")
        ->whereNull("ethh.deleted_at");
        if ($categoryId) {
            $collection->where("{$tblAssetItem}.category_id", $categoryId);
        }
        if ($dateForm) {
            $dateForm = date("Y-m-d", strtotime($dateForm));
            $collection->whereDate("mah.change_date", '>=', $dateForm);
        }
        if ($dateTo) {
            $dateTo = date("Y-m-d", strtotime($dateTo));
            $collection->whereDate("mah.change_date", '<=', $dateTo);
        }
        if ($teamId) {
            $teamIds = [];
            $teamIds[] = (int) $teamId;
            AssetView::getTeamChildRecursive($teamIds, $teamId);
            $collection->whereIn("ethh.team_id", $teamIds);
        }
        return $collection->groupBy("{$tblAssetItem}.id")->orderBy("{$tblAssetItem}.received_date", 'ASC')->get();
    }

   public function getAssetByCode($codes)
   {
       return self::where(function ($query) use ($codes) {
           foreach ($codes as $code) {
               $query->orWhere('code', 'LIKE', $code . '%');
           }
       })->get();
   }

    /**
     * update $data, column configure
     * @param $data [codeAsset => value]
     * @param $keyCodes [where like code asset]
     */
   public function updateConfigure($data, $keyCodes)
   {
       if (!$data) {
           return;
       }
       $cases = '';
       $table = self::getTableName();
       $where = '';
       foreach ($keyCodes as $item) {
           $where .= "code LIKE '". $item . "%'" . ' or ';
       }
       $where = trim(trim($where), 'or');
       foreach ($data as $code => $value) {
           $cases .= "WHEN code = '" . $code . "' THEN '" . $value . "'\n";
       }
       $cases = 'CASE ' . $cases . 'ELSE configure END';
       $query = 'UPDATE ' . $table . ' SET configure = '. $cases . ' WHERE (' . $where . ')';
       return DB::statement($query);
   }

    /**
     * Count assets to get class danger for request-asset
     * @return array
     */
    public static function countAssets()
    {
        return self::select(
            'state',
            'prefix',
            'category_id',
            'request_id',
            'id',
            DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(category_id, "-", id)) SEPARATOR ",") as count_assets')
        )
            ->whereIn('state', RequestAsset::ignoreStatus())
            ->whereNull('deleted_at')
            ->first();
    }

    public static function fieldNames()
    {
        return array(
            'code' => trans('asset::view.Asset code'),
            'name' => trans('asset::view.Asset name'),
            'category_id' => trans('asset::view.Asset category'),
            'serial' => trans('asset::view.Serial'),
            'warehouse_id' => trans('asset::view.Address warehouse'),
            'manager_id' => trans('asset::view.Manage asset person'),
            'origin_id' => trans('asset::view.Origin'),
            'supplier_id' => trans('asset::view.Supplier'),
            'purchase_date' => trans('asset::view.Purchase date'),
            'warranty_priod' => trans('asset::view.Warranty priod'),
            'warranty_exp_date' => trans('asset::view.Warranty exp date'),
            'out_of_date' => trans('asset::view.Out of date'),
            'days_before_alert_ood' => trans('asset::view.Days before alert out of date'),
            'specification' => trans('asset::view.Specification'),
            'configure' => trans('asset::view.Configure'),
            'note' => trans('asset::view.Note'),
            'attribute' => trans('asset::view.Asset attribute'),
        );
    }

    public static function getItemName($field, &$old, &$new)
    {
        switch ($field) {
            case 'category_id':
                $data = AssetCategory::getCatsName([$old, $new])
                    ->map(function ($item) {
                        return [$item->id => $item->name];
                    });
                break;
            case 'warehouse_id':
                $data = AssetWarehouse::listWarehouse()
                    ->map(function ($item) {
                        return [$item->id => $item->name];
                    });
                break;
            case 'manager_id':
                $data = Employee::getEmpByIds([$old, $new], ['id', 'name'])
                    ->map(function ($item) {
                        return [$item->id => $item->name];
                    });
                break;
            case 'origin_id':
                $data = AssetOrigin::getAssetOriginsList()
                    ->map(function ($item) {
                        return [$item->id => $item->name];
                    });
                break;
            case 'supplier_id':
                $data = AssetSupplier::getAssetSuppliersList()
                    ->map(function ($item) {
                        return [$item->id => $item->name];
                    });
                break;
            default:
                break;
        }

        foreach ($data as $value) {
            if (isset($value[$old])) $old = $value[$old];
            if (isset($value[$new])) $new = $value[$new];
        }
    }

    public function attributes()
    {
        return $this->belongsToMany('\Rikkei\Assets\Model\AssetAttribute', 'manage_asset_item_attributes', 'asset_id', 'attribute_id');
    }
    
    /**
     * update column serial number
     * @param array $data
     */
    public function updateSerialNumber($data)
    {
       if (!$data) {
           return;
       }
       $cases = '';
       $table = self::getTableName();
       $strCode = '';
       foreach ($data as $codeAsset => $serial) {
           if ($codeAsset && $serial) {
               $cases .= "WHEN code = '" . $codeAsset . "' THEN '" . $serial . "'\n";
               $strCode .= "'" . $codeAsset . "',";
           }
       }
       if ($cases) {
            $strCode = trim(trim($strCode), ',');
            $cases = 'CASE ' . $cases . 'ELSE "serial" END';
            $query = 'UPDATE ' . $table . ' SET serial = '. $cases . ' WHERE code IN (' . $strCode . ')';
            return DB::statement($query);
       }
       return;
    }
}
