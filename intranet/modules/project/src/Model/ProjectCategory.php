<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;

class ProjectCategory extends CoreModel
{
    protected $table = 'projs_category';
    protected $fillable = ['category_name', 'is_other_type'];

    public static function getCateById($cateId)
    {
        $cate = self::select('category_name')->where('id', $cateId)->first();
        return $cate->category_name;
    }
}