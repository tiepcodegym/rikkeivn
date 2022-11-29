<?php

namespace Rikkei\Core\Model;

class CoreConfigDataInt extends CoreConfigData
{
    protected $table = 'core_config_data_int';
    
    /**
     * increment version
     */
    public static function increVersion($key)
    {
        $item = self::getItem($key);
        $value = (int) $item->value;
        if ($value > 9e9) {
            $value = 1;
        } else {
            $value++;
        }
        $item->value = $value;
        $item->save();
    }
}
