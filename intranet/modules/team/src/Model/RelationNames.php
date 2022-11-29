<?php

namespace Rikkei\Team\Model;

use Rikkei\Core\View\CacheHelper;
use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class RelationNames extends CoreModel
{
    use SoftDeletes;

    protected $table = 'relation_names';
    const KEY_CACHE  = 'relation_names';
    const MAP_KEY_CACHE = 'map_relation_names';

    /**
     * get list all relationName
     *
     * @return array
     */
    public static function getAllRelations()
    {
        if (($data = CacheHelper::get(self::KEY_CACHE))) {
            return $data;
        }
        $collection = self::select(['id','name'])->get();
        $result = [];
        foreach ($collection as $item) {
            $result[$item->id] = $item->name;
        }
        CacheHelper::put(self::KEY_CACHE, $result);
        return $result;
    }
}
