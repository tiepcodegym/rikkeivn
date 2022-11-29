<?php
namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\CacheHelper;

class MilitaryPosition extends CoreModel
{
    protected $table = 'military_position';
    
    const KEY_CACHE = 'military_position';
    
    /**
     * getAll data military_position
     * @return array Description
     */
    public static function getAll()
    {
        $data = CacheHelper::get(self::KEY_CACHE);
        if($data) {
            return $data;
        }
        $collection = self::select(['id', 'name'])
               ->where('state', '=', 1)
               ->get();
        $result = [];
        foreach ($collection as $item) {
            $result[$item->id] = $item->name;
        }
        CacheHelper::put(self::KEY_CACHE, $result);
        return $result;
    }
    
    /**
     * overide save function
     * @param array $options
     */
    public function save(array $options = array()) {
        CacheHelper::forget(self::KEY_CACHE);
        return parent::save($options);
    }
}
