<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Permission;
use Rikkei\Project\Model\MeEvaluation;

class MeHistory extends CoreModel
{
    protected $table = 'me_histories';
    protected $fillable = ['eval_id', 'employee_id', 'version', 'action_type', 'type_id', 'content'];

    const AC_CHANGE_POINT = 1;
    const AC_COMMENT = 2;
    const AC_SUBMIT = 3;
    const AC_FEEDBACK = 4;
    const AC_NOTE = 5;
    const AC_APPROVED = 6;
   
    public static function checkUserAction($eval_id, $action_types, $employee_id = null) {
        if (!is_array($action_types)) {
            $action_types = [$action_types];
        }
        if (!$employee_id) {
            $employee = Permission::getInstance()->getEmployee();
            $employee_id = $employee->id;
        }
        
        $eval_item = MeEvaluation::find($eval_id, ['version']);
        if (!$eval_item) {
            return false;
        }
        $check = self::where('eval_id', $eval_id)
                ->where('employee_id', $employee_id)
                ->where('version', $eval_item->version)
                ->whereIn('action_type', $action_types)
                ->first();
        if ($check) {
            return true;
        }
        return false;
    }
    
    public static function checkUserAssignee($eval_id, $employee_id = null) {
        if (!$employee_id) {
            $employee = Permission::getInstance()->getEmployee();
            $employee_id = $employee->id;
        }
        $check = self::where('eval_id', $eval_id)
                ->where('action_type', self::AC_SUBMIT)
                ->where('type_id', $employee_id)
                ->first();
        if ($check) {
            return true;
        }
        return false;
    }
    
    /**
     * delete history after update leader
     * @param type $leader_id
     * @param type $project_id
     * @return type
     */
    public static function delByLeader($leader_id, $project_id) {
        $evalTbl = MeEvaluation::getTableName();
        $historyTbl = self::getTableName();
        return self::join($evalTbl.' as evl', function ($join) use ($historyTbl, $project_id){
            $join->on($historyTbl.'.eval_id', '=', 'evl.id')
                    ->where('evl.project_id', '=', $project_id);
        })
        ->where($historyTbl.'.action_type', self::AC_SUBMIT)
                ->where($historyTbl.'.type_id', $leader_id)
                ->delete();
    }
    
}
