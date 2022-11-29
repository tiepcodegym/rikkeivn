<?php

namespace Rikkei\Education\Model;

use Carbon\Carbon;
use Rikkei\Core\Model\CoreModel;

class EducationClassShift extends CoreModel
{
    protected $table = 'education_class_shifts';
    protected $fillable = [
        'id', 'class_id', 'class_code', 'name', 'start_date_time', 'end_date_time', 'event_id', 'location_name', 'calendar_id', 'is_finish', 'end_time_register'
    ];

    public function classDetails()
    {
        return $this->hasMany(EducationClassDetail::class, 'shift_id');
    }

    public static function getClassShift($classId, $className)
    {
        return self::where('class_code', $classId)->where('name', $className)->first();
    }

    public function educationClassDetail(){
        return self::hasMany(EducationClassDetail::class, 'shift_id', 'id');
    }

    /**
     * Danh sách ca học sắp đến giờ bắt đầu
     * @return mixed
     */
    public static function listClassNotify()
    {
        $starTime = Carbon::now()->addMinutes(-1);
        $endTime = Carbon::now()->addHours(1);
        return self::select(
            'id as shift_id',
            'class_id',
            'class_code',
            'name as shift_name',
            'start_date_time',
            'location_name'
        )
            ->whereBetween('start_date_time', [$starTime, $endTime])
            ->where('is_finish', 0)
            ->get();
    }
}
