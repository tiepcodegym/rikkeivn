<?php
namespace Rikkei\Team\Model;

use Carbon\Carbon;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Project\Model\Project;
use Rikkei\Team\Model\TeamMember;
use Illuminate\Support\Facades\DB;
use Rikkei\Project\Model\TeamProject;

class PqaResponsibleTeam extends CoreModel
{
    protected $table = 'pqa_responsible_teams';

    const TYPE_REVIEWED = 8;
    const TYPE_APPROVED = 9;
    /*
     * The attributes that are mass assignable.
     */
    protected $fillable = ['team_id', 'employee_id', 'created_at', 'updated_at'];

    public $timestamps = true;

    /*
     * get list id of team responsible follow id of pqa.
     *
     * @param $employeeId
     * @return collection.
     */
    public static function getListTeamIdResponsibleTeam($employeeId)
    {
        $selfTable = self::getTableName();
        return self::select("{$selfTable}.team_id")
                    ->where("{$selfTable}.employee_id", '=', $employeeId)
                    ->where("{$selfTable}.type", '=', self::TYPE_REVIEWED)
                    ->get();
    }

    /*
     * get list id of team branch responsible follow id of employee.
     *
     * @param $employeeId
     * @return array.
     */
    public static function getListTeamIdResponsibleBranch($employeeId)
    {
        $selfTable = self::getTableName();
        $collection = self::select("{$selfTable}.team_id")
                    ->where("{$selfTable}.employee_id", '=', $employeeId)
                    ->where("{$selfTable}.type", '=', self::TYPE_APPROVED)
                    ->get();

            foreach ($collection as $item) {
                $listTeamId[] = $item->team_id;
            }
            return isset($listTeamId) ? $listTeamId : [];
    }

    /*
     * get id of employee responsible follow id of team.
     *
     * @param array $teamId
     * @return collection.
     */
    public static function getEmpIdResponsibleTeam($teamId)
    {
        $selfTable = self::getTableName();
        return self::select("{$selfTable}.employee_id")
                    ->whereIn("{$selfTable}.team_id", $teamId)
                    ->where("{$selfTable}.type", '=', self::TYPE_REVIEWED)
                    ->get();
    }

    public static function getEmpIdResponsibleTeamAsTeamId($teamId)
    {
        $team = self::where('team_id', '=', $teamId)->select('employee_id')->get();
        $pqaLeader = [];
        if(isset($team) && count($team)) {
            foreach($team as $item) {
                $pqaLeader[] = $item->employee_id;
            }
            return $pqaLeader;
        }
        return null;
    }

    /*
     * get id of employee responsible follow id of employee.
     *
     * @param array $teamId
     * @return collection.
     */
    public static function getEmpIdResponsibleBranch($teamId)
    {
        $selfTable = self::getTableName();
        return self::select("{$selfTable}.employee_id")
                    ->whereIn("{$selfTable}.team_id", $teamId)
                    ->where("{$selfTable}.type", '=', self::TYPE_APPROVED)
                    ->get();
    }

    /*
     * get employee pqa responsible main team of project.
     *
     * @param $projectId
     * @return collection.
     */
    public static function getPqaResponsibleTeamOfProjs($projectId)
    {
        $selfTable = self::getTableName();
        $empTable = Employee::getTableName();

        $curDate = Carbon::now()->format('Y-m-d H:i:s');
        $project = Project::find($projectId);
        $teamIdOfProjs = Project::getAllTeamOfProject($project->id);
        $teamIdOfGroupLeader = [];
        if (count($teamIdOfProjs) == 1) {
            $teamIdOfGroupLeader[] = $teamIdOfProjs[0];
        } else {
            $groupLeader = $project->getInformationGroupLeader();
            if ($groupLeader) {
                $teamIdOfGroupLeader = TeamMember::where('team_members.employee_id', '=', $groupLeader->id)
                    ->lists('team_id')->toArray();
            } else {
                $teamIdOfGroupLeader = TeamProject::where('project_id', $project->id)->lists('team_id')->toArray();
            }
        }

        return self::select("{$selfTable}.employee_id", "{$empTable}.email", "{$empTable}.name")
            ->join($empTable, "{$empTable}.id", '=', "{$selfTable}.employee_id")
            ->where(function ($query) use ($empTable, $curDate) {
               $query->whereNull("{$empTable}.leave_date")
                    ->orWhereDate("{$empTable}.leave_date", '>', $curDate);
            })
            ->whereDate("{$empTable}.join_date", '<', $curDate)
            ->whereIn("{$selfTable}.team_id", $teamIdOfGroupLeader)
            ->where("{$selfTable}.type", '=', self::TYPE_REVIEWED)
            ->get();
    }

    /*
     * get employee responsible branch main of project.
     *
     * @param $projectId
     * @return collection.
     */
    public static function getEmpResponsibleBranchOfProjs($projectId)
    {
        $selfTable = self::getTableName();
        $empTable = Employee::getTableName();

        $curDate = Carbon::now()->format('Y-m-d H:i:s');
        $project = Project::find($projectId);
        $groupLeader = $project->getInformationGroupLeader();
        $teamOfGroupLeader = $groupLeader->getTeamPositons();
        $listTeamIdOfGroupLeader = [];
        foreach ($teamOfGroupLeader as $team) {
            $listTeamIdOfGroupLeader[] = $team->team_id;
        }

        // get listTeamId branch main of groupleader.
        $getTeamPathTree = Team::getTeamPathTree();
        foreach ($getTeamPathTree as $key => $item) {
            if (in_array($key, $listTeamIdOfGroupLeader)) {
                $length = count($item['parent']);
                if ($length >= 3) {
                    $listTeamIdBranch[] = $item['parent'][$length - 3];
                }
            }
        }
        return self::select("{$selfTable}.employee_id")
                ->join($empTable, "{$empTable}.id", '=', "{$selfTable}.employee_id")
                ->where(function ($query) use ($empTable, $curDate) {
                   $query->whereNull("{$empTable}.leave_date")
                         ->orWhereDate("{$empTable}.leave_date", '>', $curDate);
                })
                ->whereDate("{$empTable}.join_date", '<', $curDate)
                ->whereIn("{$selfTable}.team_id", $listTeamIdBranch)
                ->where("{$selfTable}.type", '=', self::TYPE_APPROVED)
                ->first();   
    }
}
