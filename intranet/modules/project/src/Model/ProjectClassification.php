<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;

class ProjectClassification extends CoreModel
{
    protected $table = 'projs_classification';
    protected $fillable = ['classification_name', 'is_other_type'];

    public static function getClassById($classId)
    {
        $class = self::select('classification_name')->where('id', $classId)->first();
        return $class->classification_name;
    }
}