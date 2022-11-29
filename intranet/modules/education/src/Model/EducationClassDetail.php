<?php

namespace Rikkei\Education\Model;

use Rikkei\Core\Model\CoreModel;

class EducationClassDetail extends CoreModel
{
    protected $table = 'education_class_details';
    protected $fillable = [
        'id', 'employee_id', 'role', 'feedback_teacher_point', 'feedback_company_point', 'feedback', 'class_id', 'created_at', 'updated_at', 'shift_id', 'is_attend', 'is_hr_added', 'is_mail_sent'
    ];

    const SENDED_REMIND = 2;

    public function educationClass()
    {
        return $this->belongsTo(EducationClass::class, 'class_id');
    }

    public function shift()
    {
        return $this->belongsTo(EducationClassShift::class, 'shift_id');
    }

    /**
     * @param int $employeeId
     * @param int $classId
     * @param int $shiftId
     */
    public static function checkImport($employeeId, $classId, $shiftId)
    {
        self::where('employee_id', $employeeId)
            ->where('class_id', $classId)
            ->where('shift_id', $shiftId)
            ->where('role', Status::ROLE_HOCVIEN)
            ->count();
    }

    /**
     * Danh sách học viên
     * @param object $class
     * @return array
     */
    public static function listEmployees($class)
    {
        $list = self::select('name', 'class_id', 'shift_id', 'email', 'class_name as class', 'education_class_details.id as class_detail_id')
            ->join('employees', 'employees.id', '=', 'employee_id')
            ->join('education_class', 'education_class.id', '=', 'class_id')
            ->where('class_id', $class->class_id)
            ->where('shift_id', $class->shift_id)
            ->where('role', Status::ROLE_HOCVIEN)
            ->where('is_mail_sent', '!=', self::SENDED_REMIND)
            ->get()
            ->toArray();

        foreach ($list as $key => $value) {
            $list[$key]['time'] = $class->start_date_time;
            $list[$key]['location'] = $class->location_name;
        }

        return $list;
    }

    /**
     * update các học viên đã đc gửi mail remind
     * @param array $classDetailIds
     */
    public static function updateSendedRemind($classDetailIds)
    {
        self::whereIn('id', $classDetailIds)->update(['is_mail_sent' => self::SENDED_REMIND]);
    }

    /**
     * Check employee is student
     * @param $courseId
     * @param $shiftId
     * @param $employeeId
     * @return int
     */
    public static function isStudent($courseId, $shiftId, $employeeId)
    {
        return self::join('education_class', 'education_class.id', '=', 'class_id')
            ->where('employee_id', $employeeId)
            ->where('education_class.course_id', $courseId)
            ->whereIn('shift_id', $shiftId)
            ->where('role', Status::ROLE_HOCVIEN)
            ->count();
    }
}
