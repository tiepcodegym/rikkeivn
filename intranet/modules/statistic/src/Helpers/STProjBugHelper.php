<?php

namespace Rikkei\Statistic\Helpers;

use Rikkei\CallApi\Helpers\Redmine;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Rikkei\Team\Model\Employee;
use Rikkei\Project\Model\TeamProject;
use Exception;
use Rikkei\Core\View\BaseHelper;
use Rikkei\Statistic\Models\STEmplBug;
use Rikkei\Statistic\Models\STProjBug;
use Rikkei\Project\Model\ProjectPoint;

class STProjBugHelper extends Redmine
{
    use BaseHelper;

    private $dataValueProj;
    private $dataValueEmpl;
    private $dataInsertProj;
    private $dataInsertEmpl;
    private $date; // now date
    private $filterAddition;
    private $status;

    public function setDate($nowDate = null)
    {
        if (!$nowDate) {
            $nowDate = Carbon::now();
        }
        $this->date = $nowDate->format('Y-m-d');
        $this->trackers = $this->getTrackerIds();
        $this->status = $this->getStatusIds();
        $this->filterAddition = $this->filterAttrsString($this->trackers['bug'], [
            'f' => 'tracker_id',
            'op' => '='
        ]) . $this->filterAttrsString($this->status['reject'], [
            'f' => 'status_id',
            'op' => '!',
        ]);
        $this->customFieldIds = $this->getCustomFieldIds();
        $this->dataValueProj = [];
        $this->dataValueEmpl = [];
        $this->dataInsertProj = [];
        $this->dataInsertEmpl = [];
        return $this;
    }

    /**
     * process count bug project
     */
    public function processBugAll($projects = null, $nowDate = null)
    {
        $this->setDate($nowDate);
        if ($projects === null) {
            $projects = STProjHelper::getAllProject($nowDate);
        }
        if (!$projects) {
            return false;
        }
        foreach ($projects as $project) {
            if ($project->team_ids === null) {
                $project->team_ids = TeamProject::getProjTeamIdJoin($project->project_id);
            }
            $this->countProjectBug($project);
            sleep(5);
        }
        $this->storeEmplBug($projects);
        $this->storeProjBug($projects);
        Log::info('Redmine bug done');
    }

    /**
     * count bug a project
     *
     * @param object $projectSs
     * @return boolean
     */
    public function countProjectBug($projectSs)
    {
        try {
            $projRedmine = $this->getProjRedmine($projectSs);
            if (!$projRedmine) {
                return false;
            }
            $this->countAllBug($projectSs, $projRedmine, 0);
        } catch (Exception $ex) {
            Log::error($ex);
        }
        return $this->dataValueProj;
    }

    public function countProjectActivity($projectSs)
    {
        try {
            $projRedmine = $this->getProjRedmine($projectSs);
            if (!$projRedmine) {
                return false;
            }
            $this->countAllActivity($projectSs, $projRedmine, 0);
        } catch (Exception $ex) {
            Log::error($ex);
        }
        return $this->dataValueProj;
    }

    /**
     * count bug recursive - each 100 item
     *
     * @param object $project model project
     * @param array $projRedmine
     * @param array $result
     * @param ing $offset
     * @return boolean|array|int
     */
    private function countAllBug($project, $projRedmine, $offset = 0)
    {
        $queryProject = http_build_query([
            'project_id' => $projRedmine['id'],
            'set_filter' => 1,
            'limit' => 100,
            'offset' => $offset,
            'status_id' => '*'
        ]);
        $api = $this->reCloneClient()->get('issues.json', [
            'query' => $queryProject
        ]);
        if ($api->getStatusCode() !== 200) {
            return false;
        }
        $tasks = json_decode($api->getBody()->getContents(), true);
        if (!$tasks || !isset($tasks['issues'])) {
            return null;
        }

        if (!count($tasks['issues'])) {
            return null;
        }

        if (!isset($this->dataValueProj[$project->project_id])) {
            $this->dataValueProj[$project->project_id] = [
                STProjBug::TYPE_BUG_DEFECT => 0,
                STProjBug::TYPE_BUG_LEAKAGE => 0,
                STProjBug::TYPE_BUG_DR => 0,
            ];
        }
        foreach ($tasks['issues'] as $item) {
            if ($item['tracker']['name'] == 'Bug' && $item['status']['name'] !== 'Cancelled') {
                if (isset($item['custom_fields']) && $item['custom_fields'][0]['name'] === 'QC Activity' ) {
                    $this->dataValueProj[$project->project_id][STProjBug::TYPE_BUG_DEFECT]++;
                    if (($item['custom_fields'][0]['value'] === 'Acceptance Test' || $item['custom_fields'][0]['value'] === 'Acceptance Review')) {
                        $this->dataValueProj[$project->project_id][STProjBug::TYPE_BUG_LEAKAGE]++;
                    }
                };
            } else {
                continue;
            }
        }
        if ($tasks['total_count'] > $tasks['limit'] + $tasks['offset']) {
            $this->countAllBug($project, $projRedmine, $tasks['limit'] + $tasks['offset']);
        }
    }

    public function countAllActivity($project, $projRedmine, $offset = 0)
    {
        $queryProject = http_build_query([
            'project_id' => $projRedmine['id'],
            'set_filter' => 1,
            'limit' => 100,
            'offset' => $offset,
            'status_id' => '*'
        ]);

        $api = $this->reCloneClient()->get('time_entries.json', [
            'query' => $queryProject
        ]);
        if ($api->getStatusCode() !== 200) {
            return false;
        }
        $tasks = json_decode($api->getBody()->getContents(), true);
        if (!$tasks || !isset($tasks['time_entries'])) {
            return null;
        }

        if (!count($tasks['time_entries'])) {
            return null;
        }
        if (!isset($this->dataValueProj[$project->project_id])) {
            $this->dataValueProj[$project->project_id] = [
                STProjBug::CORRECTION_COST => 0,
                STProjBug::LOG_TIME => 0,
            ];
        }

        foreach ($tasks['time_entries'] as $item) {
            $this->dataValueProj[$project->project_id][STProjBug::LOG_TIME] += $item['hours'];
            if ($item['activity']['name'] == 'Correct') {
                $this->dataValueProj[$project->project_id][STProjBug::CORRECTION_COST] += $item['hours'];
            } else {
                continue;
            }
        }
        if ($tasks['total_count'] > $tasks['limit'] + $tasks['offset']) {
            $this->countAllActivity($project, $projRedmine, $tasks['limit'] + $tasks['offset']);
        }
    }

    /**
     * store data employee bug into db
     *
     * @params array $projects
     */
    public function storeEmplBug($projects)
    {
        $dataInsertEmpl = [];
        foreach ($this->dataValueEmpl as $emplRedId => $projBugTypes) {
            $api = $this->redmineClient->get("users/{$emplRedId}.json");
            if ($api->getStatusCode() !== 200) {
                $employeeId = null;
            } else {
                $user = json_decode($api->getBody()->getContents(), true);
                if (!$user || !isset($user['user'])) {
                    $employeeId = null;
                } else {
                    $employee = Employee::select(['id'])
                        ->where('email', $user['user']['mail'])
                        ->first();
                    if (!$employee) {
                        $employeeId = null;
                    } else {
                        $employeeId = $employee->id;
                    }
                }
            }
            foreach ($projBugTypes as $projectId => $bugTypeValue) {
                foreach ($bugTypeValue as $bugType => $value) {
                    if (!$value) {
                        continue;
                    }
                    $dataInsertEmpl[] = [
                        'created_at' => $this->date,
                        'proj_id' => $projectId,
                        'empl_id' => $employeeId,
                        'value' => $value,
                        'team_id' => $projects[$projectId]->team_ids,
                        'type' => $bugType,
                    ];
                }
            }
        }
        STEmplBug::whereDate('created_at', '=', $this->date)
            ->delete();
        if ($dataInsertEmpl) {
            STEmplBug::insert($dataInsertEmpl);
        }
    }

    /**
     * store data project bug
     *
     * @param array $projects
     */
    public function storeProjBug($projects)
    {
        $dataInsertProj = [];
        foreach ($this->dataValueProj as $projectId => $bugTypeValue) {
            try {
                $projectPoint = ProjectPoint::findFromProject($projectId);
                $projectPoint->qua_leakage_errors = $bugTypeValue[STProjBug::TYPE_BUG_LEAKAGE];
                $projectPoint->qua_defect_errors = $bugTypeValue[STProjBug::TYPE_BUG_DEFECT];
                $projectPoint->qua_defect_reward_errors = $bugTypeValue[STProjBug::TYPE_BUG_DR];
                $projectPoint->save();
            } catch (Exception $ex) {
                Log::error($ex);
            }
            foreach ($bugTypeValue as $bugType => $value) {
                if (!$value) {
                    continue;
                }
                $dataInsertProj[] = [
                    'created_at' => $this->date,
                    'proj_id' => $projectId,
                    'value' => $value,
                    'team_id' => $projects[$projectId]->team_ids,
                    'type' => $bugType,
                ];
            }
        }
        
        STProjBug::whereDate('created_at', '=', $this->date)
            ->delete();
        if ($dataInsertProj) {
            STProjBug::insert($dataInsertProj);
        }
    }
}
