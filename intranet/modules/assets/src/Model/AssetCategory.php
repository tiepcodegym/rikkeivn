<?php

namespace Rikkei\Assets\Model;

use Exception;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;

class AssetCategory extends CoreModel
{
    protected $table = 'manage_asset_categories';

    protected $fillable = [
        'name', 'group_id', 'prefix_asset_code', 'note', 'created_at', 'updated_at'
    ];
    const TYPE = 'LoaiTS';

    /**
     * Get collection to show grid
     * @return type
     */
    public static function getGridData()
    {
        $tblAssetCategory = self::getTableName();
        $tblAssetGroup = AssetGroup::getTableName();
        $collection = self::select(
            "{$tblAssetCategory}.id",
            "{$tblAssetCategory}.name",
            "{$tblAssetCategory}.note",
            "{$tblAssetCategory}.prefix_asset_code",
            "{$tblAssetCategory}.group_id",
            "{$tblAssetGroup}.name as group_name",
            "{$tblAssetCategory}.is_default"
        )
            ->join("{$tblAssetGroup}", "{$tblAssetGroup}.id", "=", "{$tblAssetCategory}.group_id");
        $pager = Config::getPagerData(null, ['dir' => 'DESC']);
        $collection = $collection->orderBy($pager['order'], $pager['dir']);
        $filterDefault = Form::getFilterData('excerpt', $tblAssetCategory . '.is_default');
        if (is_numeric($filterDefault)) {
            $collection->where($tblAssetCategory . '.is_default', $filterDefault);
        }
        $collection = self::filterGrid($collection, [], null, 'LIKE');
        return self::pagerCollection($collection, $pager['limit'], $pager['page']);
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
     * Get asset categories list
     * @return [array]
     */
    public static function getAssetCategoriesList($orderBy = 'name', $order = 'asc')
    {
        $catTbl = self::getTableName();
        $collect = self::select($catTbl.'.id', $catTbl.'.name', $catTbl.'.prefix_asset_code');
        if (!in_array($orderBy, self::getFillableCols())) {
            if ($orderBy == 'count') {
                $collect->leftJoin(RequestAssetItem::getTableName() . ' as rq_item', function ($join) use ($catTbl) {
                    $join->on($catTbl . '.id', '=', 'rq_item.asset_category_id');
                })
                ->leftJoin(RequestAsset::getTableName() . ' as req', function ($join) use ($catTbl) {
                    $join->on('rq_item.request_id', '=', 'req.id')
                            ->whereNull('req.deleted_at');
                })
                ->orderBy(\DB::raw('COUNT(DISTINCT(req.id))'), $order)
                ->groupBy($catTbl.'.id');
            }
            $orderBy = 'name';
        }
        return $collect->orderBy($orderBy, $order)->get();
    }

    /**
     * Count data related to category
     * @return [int]
     */
    public static function countDataRelatedToCategory($categoryId)
    {
        $tblAssetCategory = self::getTableName();
        $tblAssetItem = AssetItem::getTableName();
        $tblAssetAttribute = AssetAttribute::getTableName();
        $countData = self::join("{$tblAssetItem}", "{$tblAssetItem}.category_id", "=", "{$tblAssetCategory}.id")
            ->where("{$tblAssetCategory}.id", $categoryId)
            ->count();
        if ($countData) {
                return $countData;
        }
        return self::join("{$tblAssetAttribute}", "{$tblAssetAttribute}.category_id", "=", "{$tblAssetCategory}.id")
            ->where("{$tblAssetCategory}.id", $categoryId)
            ->count();
    }

    /**
     * Check exist in category
     *
     * @param array $inputData
     * @return string
     */
    public static function checkExit($inputData)
    {
        if (!empty($inputData['assetCategoryId'])) {
            return self::where($inputData['name'], $inputData['value'])
                ->where('id', '<>', $inputData['assetCategoryId'])->count() == AssetWarehouse::CHECK_EXIST ? 'false' : 'true';
        } else {
            return self::where($inputData['name'], $inputData['value'])->count() == AssetWarehouse::CHECK_EXIST ? 'false' : 'true';
        }
    }

    /**
     * Define heading sheet asset category
     *
     * @return array
     */
    public static function defineHeadingFile()
    {
        return [
            0 => "ten_loai_ts",
            1 => "nhom_tai_san",
            2 => "tien_to_ma_tai_san",
            3 => "ghi_chu",
        ];
    }

    /**
     * Import asset category
     *
     * @param array $dataRow
     */
    public static function importFile($dataRow)
    {
        $dataInsert = [];
        $errors = [];
        $listGroup = [];
        $listCateName = [];
        $listCatePrefix = [];
        foreach ($dataRow as $key => $row) {
            if (!$row['ten_loai_ts'] &&
                !$row['nhom_tai_san'] &&
                !$row['tien_to_ma_tai_san']
            ) {
                continue;
            }

            if (!$row['ten_loai_ts'] ||
                !$row['nhom_tai_san'] ||
                !$row['tien_to_ma_tai_san']
            ) {
                $errors[] = trans('asset::message.Row :row: miss name or group, prefix', ['row' => $key + 2]);
                continue;
            }
            $listGroup[] = $row['nhom_tai_san'];
            $listCatePrefix[] = $row['tien_to_ma_tai_san'];
            $listCateName[] = $row['ten_loai_ts'];
        }
        $listGroup = AssetGroup::whereIn('name', array_unique($listGroup))->pluck('id', 'name')->toArray();
        foreach ($dataRow as $row) {
            $cate = AssetCategory::where('name', $row['ten_loai_ts'])
                ->where('prefix_asset_code', $row['tien_to_ma_tai_san'])
                ->first();
            $data = [
                'name' => $row['ten_loai_ts'],
                'group_id' => null,
                'prefix_asset_code' => $row['tien_to_ma_tai_san'],
                'note' => $row['ghi_chu'],
            ];
            if ($row['nhom_tai_san'] &&
                isset($listGroup[$row['nhom_tai_san']]) &&
                $listGroup[$row['nhom_tai_san']]
            ) {
                $data['group_id'] = $listGroup[$row['nhom_tai_san']];
            } else {
                continue;
            }
            if (!$cate) {
                $dataInsert[] = $data;
            } else {
                $cate->fill($data);
                $cate->save();
            }
        }
        if (!empty($errors)) {
            return $errors;
        }
        if (!empty($dataInsert)) {
            AssetCategory::insert($dataInsert);
        }
    }

    /*
     * get default category
     */
    public static function getDefaultCats($selectQuery = ['id', 'name'])
    {
        return self::select($selectQuery)
                ->where('is_default', 1)
                ->get();
    }

    public static function getCatsName($ids)
    {
        return self::select('id', 'name')
            ->whereIn('id', $ids)
            ->get();
    }
}
