<?php

namespace Rikkei\Project\Model;

use Illuminate\Support\Facades\DB;
use Rikkei\Team\View\Permission;

class ProjectWatch extends ProjectWOBase
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'projs_watches';

    protected $fillable = [];

    /**
     * Get all tracking project ID
     *
     */
    public static function listMyTracking()
    {
        $userCurrent = Permission::getInstance()->getEmployee()->id;
        return self::where('employee_id', $userCurrent)
            ->pluck('project_id')
            ->toArray();
    }

    /**
     * Add or remove tracking project
     * @param int $projectId
     * @return string
     */
    public static function checkExists($projectId)
    {
        $userCurrent = Permission::getInstance()->getEmployee()->id;
        $watcher = self::where('employee_id', $userCurrent)
            ->where('project_id', $projectId)
            ->first();

        DB::beginTransaction();
        try {
            $status = 'removed';
            if (count($watcher)) {
                $watcher->delete();
            } else {
                self::insert(['employee_id' => $userCurrent, 'project_id' => $projectId]);
                $status = 'enabled';
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
        }

        return $status;
    }
}
