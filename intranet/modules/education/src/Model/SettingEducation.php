<?php

namespace Rikkei\Education\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Education\Model\EducationCourse;
use Rikkei\Education\Model\EducationRequest;

class SettingEducation extends CoreModel
{
    protected $table = 'education_types';

    const STATUS_DISABLED = 0;
    const STATUS_ENABLE = 1;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['code', 'name', 'status', 'description'];

    public function educationCoursesTypes() {
        return $this->hasMany(EducationCourse::class, 'type', 'id');
    }

    public function educationRequestTypes() {
        return $this->hasMany(EducationRequest::class, 'type_id', 'id');
    }
}
