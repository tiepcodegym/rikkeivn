<?php

namespace Rikkei\Project\Model;

use Rikkei\Team\View\Permission;
use Illuminate\Support\Facades\DB;
use Exception;

class TaskReward extends Task
{
    /**
     * check delete available reward actual
     *
     * @param type $task
     * @param type $user
     * @return boolean
     */
    public static function isDeleteAvai($task, $user = false)
    {
        if ($user) {
            if (!Permission::getInstance()->isScopeCompany(null, 'project::reward.actual.delete')) {
                return false;
            }
        }
        if ($task->type != self::TYPE_REWARD ||
            $task->status == self::STATUS_APPROVED
        ) {
            return false;
        }
        return true;
    }
    
    public static function deleteActual($task)
    {
        DB::beginTransaction();
        try {
            ProjRewardBudget::where('project_id', $task->project_id)->delete();
            ProjRewardEmployee::where('task_id', $task->id)->delete();
            ProjRewardMeta::where('task_id', $task->id)->delete();
            $task->delete();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}