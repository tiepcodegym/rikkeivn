<?php

namespace Rikkei\Assets\Model;

use Exception;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;

class AssetAttribute extends CoreModel
{
    protected $table = 'manage_asset_attributes';

    /**
     * Get collection to show grid
     * @return type
     */
    public static function getGridData()
    {
        $tblAssetAttribute = self::getTableName();
        $tblAssetCategory = AssetCategory::getTableName();
        $collection = self::select("{$tblAssetAttribute}.id", "{$tblAssetAttribute}.name", "{$tblAssetAttribute}.note", "{$tblAssetAttribute}.category_id", "{$tblAssetCategory}.name as category_name")
            ->join("{$tblAssetCategory}", "{$tblAssetCategory}.id", "=", "{$tblAssetAttribute}.category_id");
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
     * Get asset attributes list
     * @return [array]
     */
    public static function getAssetAttributesList($categoryId = null)
    {
        $results = self::select('id', 'name');
        if ($categoryId) {
            $results = $results->where('category_id', $categoryId);
        }
        return $results->get();
    }

    /**
     * Count data related to attribute
     * @return [int]
     */
    public static function countDataRelatedToAttribute($attributeId)
    {
        $tblAssetAttribute = self::getTableName();
        $tblAssetItemAttribute = AssetItemAttribute::getTableName();
        return self::join("{$tblAssetItemAttribute}", "{$tblAssetItemAttribute}.attribute_id", "=", "{$tblAssetAttribute}.id")
            ->where("{$tblAssetAttribute}.id", $attributeId)
            ->count();
    }

    /**
     * Check exist asset attribute name
     * @param  [int] $assetAttributeId
     * @param  [string] $assetAttributeName
     * @return [int]
     */
    public static function checkExistAssetAttributeName($assetAttributeId, $assetAttributeName)
    {
        return self::where('name', $assetAttributeName)
            ->where('id', '!=', $assetAttributeId)
            ->count();
    }

    public static function getNameAttributeById($ids)
    {
        return self::whereIn('id', $ids)
            ->pluck('name')
            ->toArray();
    }

}

