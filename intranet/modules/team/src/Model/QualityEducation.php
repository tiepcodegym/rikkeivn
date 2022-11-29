<?php

namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\CacheHelper;

class QualityEducation extends CoreModel
{
    protected $table = 'quality_education';
    const KEY_CACHE = 'quality_education';

    /**
     * list qualityEducation
     *
     * @return array [id => name]
     */
    public static function getAll()
    {
        $data = CacheHelper::get(self::KEY_CACHE);
        if ($data) {
            return $data;
        }
        $data = [];
        $collection = self::select(['id','name'])
            ->get();
        foreach($collection as $item) {
            $data[$item->id]  = $item->name;
        }
        CacheHelper::put(self::KEY_CACHE, $data);
        return $data;
    }
}

