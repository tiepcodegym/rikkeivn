<?php

namespace Rikkei\Assets\Model;

use Exception;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;
use Rikkei\Team\Model\Employee;
use Rikkei\Assets\Model\AssetCategory;

class RequestAssetItemsWarehouse extends CoreModel
{
    protected $table = 'request_asset_items_from_warehouse';
    protected $fillable = [
        'employee_id', 'request_id', 'asset_category_id', 'branch', 'quantity', 'status', 'allocate', 'unallocate', 'created_at', 'updated_at'
    ];
    
    const STATUS_UNALLOCATE = 1;
    const STATUS_ALLOCATE = 2;

    public function assetCategory()
    {
        return $this->belongsTo('Rikkei\Assets\Model\AssetCategory', 'asset_category_id', 'id');
    }
  
    public static function getAll()
    {
        $pager = Config::getPagerData();
        $tblAssetItemWH = self::getTableName();
        $tblEmp = Employee::getTableName();

        $collection = self::select(
            $tblAssetItemWH . '.employee_id',
            'emp.name as emp_name'
        )
            ->join($tblEmp . ' as emp', $tblAssetItemWH.'.employee_id', '=', 'emp.id')
            ->where($tblAssetItemWH.'.status', self::STATUS_UNALLOCATE)
            ->groupBy('employee_id');

        self::filterGrid($collection, [], null, 'LIKE');
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    public static function findByEmpId($empId)
    {
        $tblAssetItemWH = self::getTableName();
        $tblEmp = Employee::getTableName();
        $tblAssetCate = AssetCategory::getTableName();

        $collection = self::select(
            $tblAssetItemWH . '.id',
            $tblAssetItemWH . '.employee_id',
            $tblAssetItemWH . '.request_id',
            $tblAssetItemWH . '.asset_category_id',
            $tblAssetItemWH . '.branch',
            $tblAssetItemWH . '.quantity',
            $tblAssetItemWH . '.allocate',
            $tblAssetItemWH . '.unallocate',
            $tblAssetItemWH . '.status',
            'assetCate.id as cate_id',
            'assetCate.name as cate_name',
            'emp.name as emp_name'
        )
            ->join($tblEmp . ' as emp', $tblAssetItemWH.'.employee_id', '=', 'emp.id')
            ->join($tblAssetCate . ' as assetCate', $tblAssetItemWH.'.asset_category_id', '=', 'assetCate.id')
            ->where($tblAssetItemWH.'.status', self::STATUS_UNALLOCATE)
            ->where($tblAssetItemWH.'.employee_id', $empId)
            ->get();

        return $collection;
    }

    public static function getAssets($reqId = null, $status = null)
    {
        $tblAssetItemWH = RequestAssetItemsWarehouse::getTableName();
        $tblAssetCate = AssetCategory::getTableName();
        $assets = RequestAssetItemsWarehouse::select(
            $tblAssetItemWH . '.id',
            $tblAssetItemWH . '.employee_id',
            $tblAssetItemWH . '.request_id',
            $tblAssetItemWH . '.asset_category_id',
            $tblAssetItemWH . '.branch',
            $tblAssetItemWH . '.quantity',
            $tblAssetItemWH . '.allocate',
            $tblAssetItemWH . '.unallocate',
            $tblAssetItemWH . '.status',
            'assetCate.id as cate_id',
            'assetCate.name as cate_name'
        )
            ->join($tblAssetCate . ' as assetCate', $tblAssetItemWH.'.asset_category_id', '=', 'assetCate.id')
            ->when($reqId, function ($query) use ($tblAssetItemWH, $reqId) {
                return $query->where($tblAssetItemWH.'.request_id', $reqId);
            })
            ->when($status, function ($query) use ($tblAssetItemWH, $status) {
                return $query->where($tblAssetItemWH.'.status', $status);
            })
            ->get();

        return $assets;
    }
}
