<?php

namespace Rikkei\ManageTime\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplementReasons extends CoreModel 
{
    protected $table = 'supplement_reasons';

    public $timestamps = true;

    use SoftDeletes;

    //Defined value check image required
    const IS_IMAGE_REQUIRED = 1;

    //Defined value check type is other
    const TYPE_OTHER = 1;

    public static function getOtherType()
    {
        return self::where('is_type_other', self::TYPE_OTHER)->first();
    }
}
