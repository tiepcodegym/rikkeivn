<?php

namespace Rikkei\Assets\Model;

use Exception;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;

class AssetGroup extends CoreModel
{
    protected $table = 'manage_asset_groups';
    protected $fillable = [
        'name', 'note', 'created_at', 'updated_at'
    ];
    const TYPE = 'NhomTS';

    /**
     * Get collection to show grid
     * @return type
     */
    public static function getGridData()
    {
        $tblAssetGroup = self::getTableName();
        $collection = self::select("{$tblAssetGroup}.id", "{$tblAssetGroup}.name", "{$tblAssetGroup}.note");
        $pager = Config::getPagerData(null, ['dir' => 'DESC']);
        $collection = $collection->orderBy($pager['order'], $pager['dir']);
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
     * Get asset groups list
     * @return [array]
     */
    public static function getAssetGroupsList()
    {
        return self::select('id', 'name')
            ->get();
    }

    /**
     * Count data related to group
     * @return [int]
     */
    public static function countDataRelatedToGroup($groupId)
    {
        $tblAssetGroup = self::getTableName();
        $tblAssetCategory = AssetCategory::getTableName();
        return self::join("{$tblAssetCategory}", "{$tblAssetCategory}.group_id", "=", "{$tblAssetGroup}.id")
            ->where("{$tblAssetGroup}.id", $groupId)
            ->count();
    }

    /**
     * Check exist asset group name
     * @param  [int] $assetGroupId
     * @param  [string] $assetGroupName
     * @return [int]
     */
    public static function checkExistAssetGroupName($assetGroupId, $assetGroupName)
    {
        return self::where('name', $assetGroupName)
            ->where('id', '!=', $assetGroupId)
            ->count();
    }

    /**
     * Get heading sheet import
     * @return array
     */
    public static function defineHeadingFile()
    {
        return [
            0 => "ten_nhom_ts",
            1 => "ghi_chu",
        ];
    }

    /**
     * Import asset group
     *
     * @param array $dataRow
     */
    public static function importFile($dataRow)
    {
        $dataInsert = [];
        foreach ($dataRow as $key => $row) {
            if (!$row['ten_nhom_ts']) {
               continue;
            }
            $group = AssetGroup::where('name', $row['ten_nhom_ts'])->first();
            $dataItem = [
                'name' => $row['ten_nhom_ts'],
                'note' => $row['ghi_chu'],
            ];
            if (!$group) {
                $dataInsert[] = $dataItem;
            } else {
                $group->fill($dataItem);
                $group->save();
            }
        }
        if (!empty($dataInsert)) {
            AssetGroup::insert($dataInsert);
        }
    }
}
