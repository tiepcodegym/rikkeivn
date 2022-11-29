<?php

namespace Rikkei\Education\Model;

use Illuminate\Support\Facades\Lang;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Employee;

class EducationTeacher extends CoreModel
{
    protected $table = 'education_teachers';

    const SCOPE_COMPANY = 1;
    const SCOPE_BRANCH = 2;
    const SCOPE_DIVISION = 3;

    const REGISTER_TYPE_AVAILABLE = 1;
    const REGISTER_TYPE_NEED = 2;

    const STATUS_NEW = 1;
    const STATUS_UPDATE = 2;
    const STATUS_REJECT = 3;
    const STATUS_ARRANGEMENT = 4;
    const STATUS_SEND = 5;

    const ROUTER_REGISTER_HR = 'hr.teachings';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'scope', 'course_type_id', 'type', 'course_id', 'class_id', 'time', 'tranning_hour', 'object', 'tranning_manage_id', 'content', 'condition', 'employee_id', 'reject', 'status', 'target', 'teams'];

    /**
     * get Scope label of education_teachers
     *
     * @return array
     */

    public function educationCourses() {
        return $this->hasMany(EducationCourse::class, 'id', 'course_id');
    }

    public function Classes() {
        return $this->hasMany(EducationClass::class, 'id', 'class_id');
    }

    public function user() {
        return $this->belongsTo(Employee::class, 'tranning_manage_id', 'id');
    }

    public function employee() {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function teacherTime() {
        return $this->hasMany(EducationTeacherTime::class, 'education_teacher_id', 'id');
    }


    public static function getLableScope()
    {
        return [
            self::SCOPE_COMPANY => Lang::get('education::view.Company'),
            self::SCOPE_BRANCH => Lang::get('education::view.Branch'),
            self::SCOPE_DIVISION => Lang::get('education::view.Division'),
        ];
    }

    /**
     * get register type label of education_teachers
     *
     * @return array
     */
    public static function getLableRegisterType()
    {
        return [
            self::REGISTER_TYPE_AVAILABLE => Lang::get('education::view.Course available'),
            self::REGISTER_TYPE_NEED => Lang::get('education::view.According to demand')
        ];
    }

    /**
     * get status label of education_teachers
     *
     * @return array
     */
    public static function getLableStatus()
    {
        return [
            self::STATUS_NEW => Lang::get('education::view.Status new'),
            self::STATUS_UPDATE => Lang::get('education::view.Status update'),
            self::STATUS_REJECT => Lang::get('education::view.Status reject'),
            self::STATUS_ARRANGEMENT => Lang::get('education::view.Status Arrangement'),
            self::STATUS_SEND => Lang::get('education::view.Send')
        ];
    }

    /**
     * Scope a query to only include education_teachers of a given array $arrEmployeeIds.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $arrEmployeeIds
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfEmployeeIds($query, $arrEmployeeIds)
    {
        return $query->whereIn('employee_id', $arrEmployeeIds);
    }

    /**
     * Scope a query to only include education_teachers of a given array scope.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $scope
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfScope($query, $scope)
    {
        return $query->where('scope', $scope);
    }

    /**
     * Scope a query to only include education_teachers of a given array Type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include education_teachers of a given array Status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include education_teachers of a given array tranning_manage_id.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfTranningManageId($query, $id)
    {
        return $query->where('tranning_manage_id', $id);
    }

    /**
     * Scope a query to only include education_teachers of a given array course_id.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfCoursesId($query, $id)
    {
        return $query->where('course_id', $id);
    }
}
