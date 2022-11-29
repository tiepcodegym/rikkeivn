<?php

namespace Rikkei\Assets\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\View\Config;
use Rikkei\Team\Model\Employee;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Assets\Model\InventoryItem;
use Rikkei\Assets\View\AssetConst;
use Carbon\Carbon;

class Inventory extends CoreModel
{
    protected $table = 'inventory_assets';
    protected $fillable = ['name', 'time', 'status', 'created_by'];

    /*
     * get group that belongs to
     */
    public function groups()
    {
        return $this->belongsToMany('\Rikkei\Team\Model\Team', 'inventory_asset_team', 'inventory_id', 'team_id');
    }

    /*
     * get list items
     */
    public function items()
    {
        return $this->hasMany('\Rikkei\Assets\Model\InventoryItem', 'inventory_id');
    }

    /*
     * create or update item
     */
    public static function createOrUpdate($data)
    {
        $teamIds = $data['team_ids'];
        if (isset($data['id'])) {
            $item = self::findOrFail($data['id']);
            $item->update($data);
        } else {
            $data['created_by'] = auth()->id();
            $item = self::create($data);
        }
        $item->groups()->sync($teamIds);
        InventoryItem::insertItem($item, $teamIds, $data['mail']);
        return $item;
    }

    /*
     * check exist inventory in time
     */
    public static function checkExists($data)
    {
        $time = $data['time'];
        $teamIds = $data['team_ids'];
        $result = self::select('inv.id', 'inv.time', DB::raw('GROUP_CONCAT(team.name SEPARATOR ", ") as team_names'))
                ->from(self::getTableName() . ' as inv')
                ->join('inventory_asset_team as iat', 'inv.id', '=', 'iat.inventory_id')
                ->join(Team::getTableName() . ' as team', 'iat.team_id', '=', 'team.id')
                ->where('inv.status', AssetConst::INV_STT_OPEN)
                ->where('inv.time', '>=', Carbon::now()->format('Y-m-d H:i'))
                ->where('inv.time', '<=', $time)
                ->whereIn('iat.team_id', $teamIds)
                ->groupBy('inv.id');
        if (isset($data['id'])) {
            $result->where('inv.id', '!=', $data['id']);
        }
        return $result->first();
    }

    /*
     * get list items
     */
    public static function getGridData($urlFilter = null, $getAll = false)
    {
        $tblTeam = Team::getTableName();
        $collection = self::select(
            'inv.id',
            'inv.name',
            'inv.time',
            'inv.status',
            'inv.created_at',
            'emp.email as creator_name',
            DB::raw('GROUP_CONCAT(DISTINCT(team.name) SEPARATOR ", ") as team_names')
        )
                ->from(self::getTableName() . ' as inv')
                ->leftJoin('inventory_asset_team as iat', 'inv.id', '=', 'iat.inventory_id')
                ->leftJoin($tblTeam . ' as team', 'iat.team_id', '=', 'team.id')
                ->leftJoin(Employee::getTableName() . ' as emp', 'inv.created_by', '=', 'emp.id')
                ->groupBy('inv.id');
        //check permisison
        $scope = Permission::getInstance();
        if ($scope->isScopeCompany()) {
            //get all
        } elseif ($scope->isScopeTeam()) {
            $currUser = $scope->getEmployee();
            $teamIds = TeamMember::where('employee_id', $currUser->id)
                    ->lists('team_id')
                    ->toArray();
            $collection->where(function ($query) use ($teamIds, $currUser) {
                $query->whereIn('team.id', $teamIds)
                        ->orWhere('inv.created_by', $currUser->id);
            });
        } elseif ($scope->isScopeSelf()) {
            $collection->where('inv.created_by', $scope->getEmployee()->id);
        } else {
            CoreView::viewErrorPermission();
        }
        //filter grid
        self::filterGrid($collection, [], $urlFilter);
        if ($filterTeamId = CoreForm::getFilterData('excerpt', 'team_id', $urlFilter)) {
            $collection->leftJoin('inventory_asset_team as iat_filter', 'inv.id', '=', 'iat_filter.inventory_id')
                    ->leftJoin($tblTeam . ' as team_filter', 'iat_filter.team_id', '=', 'team_filter.id')
                    ->where('team_filter.id', $filterTeamId);
        }
        $pager = Config::getPagerData($urlFilter);
        //sort order
        if (CoreForm::getFilterPagerData('order', null, $urlFilter)) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('inv.created_at', 'desc');
        }
        if ($getAll) {
            return $collection->get();
        }
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /*
     * get inventory avalable
     */
    public static function getInventoryOpenByEmp($employeeId)
    {
        return self::select('ivt.*', 'ivt_item.note', 'ivt_item.status as item_status', 'ivt_item.updated_at as ivted_at')
                ->from(self::getTableName() . ' as ivt')
                ->join(InventoryItem::getTableName() . ' as ivt_item', 'ivt.id', '=', 'ivt_item.inventory_id')
                ->where('ivt_item.employee_id', $employeeId)
                ->where('ivt.status', AssetConst::INV_STT_OPEN)
                ->where('ivt.time', '>=', Carbon::now()->format('Y-m-d H:i'))
                ->orderBy('ivt.time', 'asc')
                ->groupBy('ivt.id')
                ->first();
    }

}
