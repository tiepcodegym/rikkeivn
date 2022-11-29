<?php

namespace Rikkei\Project\Model;

use Rikkei\Team\View\Permission;
use Rikkei\Project\Model\Task;
use Rikkei\Core\View\CacheHelper;
use DB;
use Lang;

class ProjectWONote extends ProjectWOBase
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'proj_wo_note';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['perf_startat', 'perf_endat',
                            'perf_duration', 'perf_plan_effort', 'perf_effort_usage',
                            'perf_dev', 'perf_pm', 'perf_qa',
                            'qua_billable', 'qua_plan', 'qua_actual',
                            'qua_effectiveness', 'qua_css', 'qua_timeliness',
                            'qua_leakage', 'qua_process', 'qua_report'];

    /**
     * update project wo note
     * @param array
     * @return boolean
     */
    public static function updateProjectWONote($input)
    {
        $projectNote = self::where('project_id', $input['id'])->first();
        $projectNote->fill($input['data']);
        if ($projectNote->save()) {
            return true;
        }
        return false;
    }

    /**
     * get project Wo note by project id
     * @param  int
     * @return collection
     */
    public static function getProjectWoNote($projectId)
    {
        $projectNote = self::where('project_id', $projectId)->first();
        if ($projectNote) {
            return $projectNote;
        }
        $projectNote = new ProjectWONote;
        $projectNote->project_id = $projectId;
        if($projectNote->save()) {
            return $projectNote;
        }
        return false;

    }

}