<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;

class ProjectBusiness extends CoreModel
{
    protected $table = 'projs_business';
    protected $fillable = ['business_name', 'is_other_type'];

    public static function getBusinessById($businessId)
    {
        $business = self::select('business_name')->where('id', $businessId)->first();
        return $business->business_name;
    }
}