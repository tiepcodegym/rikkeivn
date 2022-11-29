<?php

namespace Rikkei\Resource\Model;

use Rikkei\Core\Model\CoreModel;

class EmplAvailTask extends CoreModel
{
    protected $table = 'employee_available_task';
    protected $fillable = ['employee_id', 'task_id'];

    /**
     * get not by list candidate of week
     * @param type $list
     * @return type
     */
    public static function getTaskEmployees($employeeIds)
    {
        if (!$employeeIds) {
            return [];
        }
        return self::select('et.employee_id', 'et.task_id')
                ->from(self::getTableName().' as et')
                ->whereIn('et.employee_id', $employeeIds)
                ->groupBy('et.employee_id')
                ->lists('et.task_id', 'et.employee_id')
                ->toArray();
    }

    /**
     * create or update item
     * @param integer $employeeId
     * @param integer $taskId
     * @return object
     */
    public static function insertOrUpdate($employeeId, $taskId)
    {
        $data = [
            'employee_id' => $employeeId,
            'task_id' => $taskId
        ];
        $item = self::where('employee_id', $employeeId)
                ->where('task_id', $taskId)
                ->first();
        if (!$item) {
            $item = self::create($data);
        } else {
            $item->update($data);
        }
        return $item;
    }
}
