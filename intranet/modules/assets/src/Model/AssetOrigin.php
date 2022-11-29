<?php

namespace Rikkei\Assets\Model;

use Lang;
use Exception;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;

class AssetOrigin extends CoreModel
{
    protected $table = 'manage_asset_origins';

    /**
     * Get collection to show grid
     * @return type
     */
    public static function getGridData()
    {
        $tblAssetOrigin = self::getTableName();
        $collection = self::select("{$tblAssetOrigin}.id", "{$tblAssetOrigin}.name", "{$tblAssetOrigin}.note");
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
     * Get asset origins list
     * @return [array]
     */
    public static function getAssetOriginsList()
    {
        return self::select('id', 'name')
            ->get();
    }

    /**
     * Count data related to origin
     * @return [int]
     */
    public static function countDataRelatedToOrigin($originId)
    {
        $tblAssetOrigin = self::getTableName();
        $tblAssetItem = AssetItem::getTableName();
        return self::join("{$tblAssetItem}", "{$tblAssetItem}.origin_id", "=", "{$tblAssetOrigin}.id")
            ->where("{$tblAssetOrigin}.id", $originId)
            ->count();
    }

    /**
     * Check exist asset origin name
     * @param  [int] $assetOriginId
     * @param  [string] $assetOriginName
     * @return [int]
     */
    public static function checkExistAssetOriginName($assetOriginId, $assetOriginName)
    {
        return self::where('name', $assetOriginName)
            ->where('id', '!=', $assetOriginId)
            ->count();
    }

    /**
     * Get info origin by name
     *
     * @param string $name
     * @param array $select
     * @return mixed
     */
    public static function getOriginByName($name, $select = ['id', 'name'])
    {
        return self::where('name', $name)
            ->select($select)
            ->first();
    }
}
