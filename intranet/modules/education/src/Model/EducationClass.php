<?php

namespace Rikkei\Education\Model;

use Carbon\Carbon;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Employee;
use Rikkei\Education\Model\EducationClassDetail;

class EducationClass extends CoreModel
{
    const ROLE_TEACHER = 2;
    const CLASS_NOT_TEACHER = 0;

    protected $table = 'education_class';
    protected $fillable = [
        'id', 'class_code', 'class_name', 'related_id', 'related_name', 'start_date', 'end_date', 'course_code', 'course_id', 'created_at', 'updated_at', 'is_commitment'
    ];

    public function course() {
        return $this->belongsTo(EducationCourse::class, 'course_id');
    }

    public function classDetails()
    {
        return $this->hasMany(EducationClassDetail::class, 'class_id');
    }

    public function classShift()
    {
        return $this->hasMany(EducationClassShift::class, 'class_id')->where('end_time_register', '>=', Carbon::now());
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'related_id');
    }

    public function teacher()
    {
        return $this->belongsTo(EducationTeacherWithout::class, 'related_id');
    }

    public function educationsClassDetails()
    {
        return $this->hasMany(EducationClassDetail::class, 'class_id', 'id');
    }
}

