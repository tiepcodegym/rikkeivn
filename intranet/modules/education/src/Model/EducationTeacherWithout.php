<?php

namespace Rikkei\Education\Model;

use Rikkei\Core\Model\CoreModel;

class EducationTeacherWithout extends CoreModel
{
    protected $table = 'education_teacher_withouts';
    protected $fillable = [
        'id', 'name'
    ];
}