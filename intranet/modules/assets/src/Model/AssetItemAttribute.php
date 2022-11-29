<?php

namespace Rikkei\Assets\Model;

use Exception;
use Rikkei\Core\Model\CoreModel;

class AssetItemAttribute extends CoreModel
{
    protected $table = 'manage_asset_item_attributes';

    /**
     * Get asset item attribute
     * @param  [int|null] $assetItemId
     * @return [array]
     */
    public static function getAssetItemAttributes($assetItemId)
    {
        $collection = self::select('attribute_id')
            ->where('asset_id', $assetItemId)
            ->get();
        if (!count($collection)) {
            return [];
        }
        $result = [];
        foreach ($collection as $item) {
            $result[] = $item->attribute_id;
        }
        return $result;
    }

    /**
     * Delete and insert asset item and attribute
     * @param [int|null] $assetItemId
     * @param [array] $dataInsert
     */
    public static function deleteAndInsert($assetItemId = null, $dataInsert = [])
    {
        if (!$assetItemId) {
            return;
        }
        try {
            self::where('asset_id', $assetItemId)->delete();
            if (count($dataInsert)) {
                    self::insert($dataInsert);
            }
        } catch (Exception $ex) {
            throw $ex;
        }
    }
}
