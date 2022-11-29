<?php
namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\CacheHelper;

class Province extends CoreModel
{

    protected $table = 'lib_province';
    public $timestamps = false;

    const KEY_CACHE = 'lib_province';
    const HA_NOI = 'Hà Nội';

    /**
     * get all province
     *
     * @return string array
     */
    public static function getProvinceList()
    {
        if ($collection = CacheHelper::get(self::KEY_CACHE)) {
            return $collection;
        }
        $collection = self::select(['id', 'province', 'country_id'])
            ->orderBy('province')
            ->get();
        if (!count($collection)) {
            return [];
        }
        $result = [];
        foreach ($collection as $item) {
            $result[$item->country_id][$item->id] = $item->province;
        }
        CacheHelper::put(self::KEY_CACHE, $result);
        return $result;
    }
}
