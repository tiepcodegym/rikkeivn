<?php

namespace Rikkei\Assets\Model;

use DB;
use Lang;
use Exception;
use Carbon\Carbon;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;
use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\Employee;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetWarehouse extends CoreModel
{
    protected $table = 'manage_asset_warehouse';

    const CHECK_EXIST = 1;


    public static function getGridData()
    {
        $collection = self::select('id', 'code', 'name', 'address', 'manager_id', 'branch');
        $pager = Config::getPagerData(null, ['dir' => 'DESC']);
        $collection = $collection->orderBy($pager['order'], $pager['dir']);
        $collection = self::filterGrid($collection, [], null, 'LIKE');
        return self::pagerCollection($collection, $pager['limit'], $pager['page']);
    }

    /**
     * Check exist
     *
     * @param array $inputData
     * @return string
     */
    public static function checkExist($inputData)
    {
        if (!empty($inputData['warehouseId'])) {
            return self::where($inputData['name'], $inputData['value'])
                    ->where('id', '<>', $inputData['warehouseId'])->count() == AssetWarehouse::CHECK_EXIST ? 'false' : 'true';
        } else {
            return self::where($inputData['name'], $inputData['value'])->count() == AssetWarehouse::CHECK_EXIST ? 'false' : 'true';
        }
    }

    public static function listWarehouse()
    {
        return self::select('name', 'id', 'code')->get();
    }

}
