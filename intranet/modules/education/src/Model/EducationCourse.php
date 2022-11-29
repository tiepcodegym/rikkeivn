<?php

namespace Rikkei\Education\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Employee;

class EducationCourse extends CoreModel
{
    protected $table = 'education_courses';
    protected $fillable = [
        'id', 'course_code', 'name', 'status', 'hours', 'location', 'description', 'target', 'hr_feedback', 'teacher_feedback', 'cost', 'is_mail', 'hr_id', 'type', 'teacher_cost', 'scope_total', 'course_form', 'is_mail_list'
    ];

    const RELATED_ID = 'teacher_without';
    const MIN_DATE = '1970-01-01';
    const MAX_DATE = '9999-12-31';

    public function classes()
    {
        return $this->hasMany(EducationClass::class, 'course_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'hr_id');
    }

    public function typeEducation()
    {
        return $this->belongsTo(EducationType::class, 'type');
    }

    public function courseTeam()
    {
        return $this->hasMany(EducationCourseTeam::class, 'course_id');
    }

    public static function isTeacher($hrId, $courseCode)
    {
        return self::where('hr_id', $hrId)
            ->where('course_code', $courseCode)
            ->count();
    }

    public static function getCourseStatus($courseId)
    {
        return self::where('id', $courseId)
            ->pluck('status')
            ->first();
    }
}