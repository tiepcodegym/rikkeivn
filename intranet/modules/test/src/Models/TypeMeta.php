<?php

namespace Rikkei\Test\Models;

use Rikkei\Core\Model\CoreModel;

class TypeMeta extends CoreModel
{
    protected $table = 'ntest_types_meta';
    protected $fillable = ['name', 'lang_code', 'type_id'];

    public $timestamps = false;

    public static function getByTypeId($typeId, $langCode = null)
    {
        $result = self::where('type_id', $typeId);
        if ($langCode) {
            $result->where('lang_code', $langCode);
            return $result->first();
        }
        return $result->get();
    }
            
}
