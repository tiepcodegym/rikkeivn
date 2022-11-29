<?php

namespace Rikkei\Sales\Model;

use Illuminate\Database\Eloquent\Model;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Project\Model\Project;

class CssProjectType extends Model
{
    protected $table = 'css_project_type';
    const KEY_CACHE = 'css_project_type';
    
    public static function getTextById($id) {
        if ($item = CacheHelper::get(self::KEY_CACHE, $id)) {
            return $item;
        }
        $item = self::find($id);
        CacheHelper::put(self::KEY_CACHE, $item->name, $id);
        return $item->name;
    }
}
