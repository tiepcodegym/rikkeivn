<?php

namespace Rikkei\Education\Model;

use Rikkei\Core\Model\CoreModel;

class EducationTeacherTime extends CoreModel
{
    protected $table = 'education_teacher_times';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['education_teacher_id', 'name', 'start_date', 'end_date'];
}
