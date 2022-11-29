<?php

namespace Rikkei\Resource\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\Employee;

class EmployeeNote extends CoreModel
{
    protected $table = 'employee_notes';
    protected $fillable = ['employee_id', 'note', 'employee_note_id'];

    /**
     * get not by list candidate of week
     * @param type $list
     * @return type
     */
    public static function getNoteEmployees($employeeIds)
    {
        if (!$employeeIds) {
            return [];
        }
        $collection = self::select('note.employee_id', 'note.note', 'emp.email')
                ->from(self::getTableName().' as note')
                ->join(Employee::getTableName() . ' as emp', 'emp.id', '=', 'note.employee_note_id')
                ->whereIn('note.employee_id', $employeeIds)
                ->groupBy('note.id')
                ->orderBy('note.updated_at', 'desc');
        return $collection->get()->groupBy('employee_id');
    }

    /**
     * create or update item
     * @param string $employeeId
     * @param string $note
     * @return array
     */
    public static function insertOrUpdate($employeeId, $note = null)
    {
        $currUser = Permission::getInstance()->getEmployee();
        $employeeNoteId = $currUser->id;
        $data = [
            'employee_id' => $employeeId,
            'note' => $note,
            'employee_note_id' => $employeeNoteId
        ];
        $item = self::where('employee_id', $employeeId)
                ->where('employee_note_id', $employeeNoteId)
                ->first();
        if (!$item) {
            $item = self::create($data);
        } else {
            if ($item->employee_note_id != $employeeNoteId) {
                return [
                    'error' => 1,
                    'message' => trans('resource::message.Error permission')
                ];
            }
            if (!trim($note)) {
                $item->delete();
                return [
                    'delete' => 1,
                    'note' => ''
                ];
            }
            $item->update($data);
        }
        return [
            'delete' => 0,
            'name' => ucfirst(preg_replace('/@.*/', '', $currUser->email)),
            'note' => $item->note
        ];
    }
}
