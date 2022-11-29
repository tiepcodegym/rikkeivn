<?php

namespace Rikkei\Statistic\Helpers;

use Rikkei\Project\View\ProjectGitlab;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Storage;
use Rikkei\Statistic\Models\STProjLoc;
use Rikkei\Statistic\Models\STEmplLoc;
use Rikkei\CallApi\Helpers\GitlabHelpers;
use Rikkei\Team\Model\Employee;
use Rikkei\Project\Model\TeamProject;
use Exception;

class STProjLocHelper extends ProjectGitlab
{
    private $dataInsertEmployee;
    private $employeeId;
    private $dataInsertProj;

    /**
     * process count loc project
     */
    public function processLocAll($projects = null, $nowDate = null)
    {
        if (!$nowDate) {
            $nowDate = Carbon::now();
        }
        if ($projects === null) {
            $projects = STProjHelper::getAllProject($nowDate);
        }
        if (!$projects) {
            return false;
        }
        try {
            Storage::deleteDirectory(self::FOLDER_CLONE);
        } catch (Exception $ex) {
            Log::error($ex);
        }
        $date = $nowDate->format('Y-m-d');
        $this->createFiles();
        $tomorrow = clone $nowDate;
        $tomorrow->modify('+1 days')->format('Y-m-d');
        $this->dataInsertEmployee = [];
        $this->dataInsertProj = [];
        $this->employeeId = [];
        foreach ($projects as $project) {
            try {
                $projectGitlab = $this->getProjGitlab($project);
                if (!$projectGitlab) {
                    sleep(2);
                    continue;
                }
                if ($project->team_ids === null) {
                    $project->team_ids = TeamProject::getProjTeamIdJoin($project->project_id);
                }
                $this->locEmployee($project, $projectGitlab, $date, $tomorrow);
                //$this->dataProjLoc($project, $this->locProj($project, $projectGitlab), $date);
            } catch (Exception $ex) {
                Log::error($ex);
            }
            sleep(10);
        }
        // store data employee to db
        STEmplLoc::whereDate('created_at', '=', $date)
            ->delete();
        if ($this->dataInsertEmployee) {
            STEmplLoc::insert($this->dataInsertEmployee);
        }
        // store data proj to db
        STProjLoc::whereDate('created_at', '=', $date)
            ->delete();
        if ($this->dataInsertProj) {
            STProjLoc::insert($this->dataInsertProj);
        }
        Log::info('Gitlab Loc done');
    }

    /**
     * count line of code of project
     *
     * @param model $project project info: gitlab path, redmine path
     * @return number
     */
    public function locProj($project, $projectGiblab)
    {
        try {
            Storage::deleteDirectory(self::FOLDER_CLONE . $project->project_id);
        } catch (Exception $ex) {
            Log::error($ex);
        }
        $projFolder = storage_path('app/'.self::FOLDER_CLONE . $project->project_id);
        // clone: git clone --depth=1 http://oauth2:token@git.com/path/repo.git forder-store-local
        $process = new Process("git clone --depth=1 {$this->config['protocol']}"
            . "://oauth2:{$this->config['token']}@{$this->config['host']}"
            . "/{$projectGiblab['path_with_namespace']}.git {$projFolder} && "// clone
            . "cd {$projFolder} && " // cd to folder
            . 'git ls-files ' // add to git repo and list all file
            . '| xargs cat | wc -l'); // count line
        $process->setTimeout(1800);
        $process->run();
        try {
            Storage::deleteDirectory(self::FOLDER_CLONE . $project->project_id);
        } catch (Exception $ex) {
            Log::error($ex);
        }
        if (!$process->isSuccessful()) {
            Log::error(new ProcessFailedException($process));
            return false;
        }
        $lines = preg_split('/\R|\r/m', $process->getOutput());
        if (!$lines) {
            return 0;
        }
        foreach ($lines as $line) {
            if (is_numeric(trim($line))) {
                return trim($line);
            }
        }
        return 0;
    }

    /**
     * count line of code of project
     *
     * @param model $project project info: gitlab path, redmine path
     * @return number
     */
    /*public function locProjFollowArchive($project, $projectGiblab)
    {
        Storage::deleteDirectory(self::FOLDER_CLONE . $project->project_id);
        $projFolder = storage_path('app/'.self::FOLDER_CLONE . $project->project_id);
        $fileArchiveProject = self::FOLDER_CLONE . $project->project_id . '.tar.gz';
        try {
            $repo = $this->gitlab->api('repo')->archive($projectGiblab['id']);
        } catch (Exception $ex) {
            Log::error($ex);
            return false;
        }
        Storage::put($fileArchiveProject, $repo, self::ACCESS_FILE);
        if (!Storage::exists(self::FOLDER_CLONE . $project->project_id)) {
            Storage::makeDirectory(self::FOLDER_CLONE . $project->project_id, self::ACCESS_FOLDER);
        }
        @chmod($projFolder, self::ACCESS_FOLDER);
        $process = new Process('tar zxf ' . storage_path('app/' . $fileArchiveProject) . 
            ' -C ' . $projFolder . ' && ' // unzip repo
            . 'cd ' . $projFolder . ' && ' // cd to folder
            //. 'git init && git add . && git ls-files ' // add to git repo and list all file
            . 'find . ' // list all file in folder
            . '| xargs cat | wc -l');
        $process->run();
        Storage::delete($fileArchiveProject);
        if (!$process->isSuccessful()) {
            Storage::deleteDirectory(self::FOLDER_CLONE . $project->project_id);
            Log::error(new ProcessFailedException($process));
            return false;
        }
        Storage::deleteDirectory(self::FOLDER_CLONE . $project->project_id);
        $lines = preg_split('/\R|\r/m', $process->getOutput());
        if (!$lines) {
            return 0;
        }
        foreach ($lines as $line) {
            if (is_numeric(trim($line))) {
                return trim($line);
            }
        }
        return 0;
    }*/

    /**
     * count line of code employee each project
     *
     * @param model $project
     * @param array $projectGiblab
     * @param string $date
     */
    public function locEmployee($project, $projectGiblab, $date, $date2)
    {
        $commits = GitlabHelpers::with($this->gitlab)
            ->getPath('projects/'.$projectGiblab['id'].'/repository/commits', [
                'with_stats' => true,
                'since' => $date,
                'until' => $date2,
                'all' => true,
            ]);
        if (!$commits || !count($commits)) {
            return null;
        }
        foreach ($commits as $commit) {
            if (isset($commit['stats']['additions'])) {
                $line = $commit['stats']['additions'];
            } else {
                $signleC = GitlabHelpers::with($this->gitlab)
                    ->getPath('projects/'.$projectGiblab['id']
                        .'/repository/commits/'.$commit['id'], [
                        'with_stats' => true,
                    ]);
                if (isset($signleC['parent_ids']) && count($signleC['parent_ids']) > 1) {
                    continue; // merge request, resolve conflict => not line of code
                }
                $line = isset($signleC['stats']['additions']) ? $signleC['stats']['additions'] : 0;
            }
            if (!$line) {
                continue;
            }
            if (isset($this->employeeId[$commit['author_email']])) {
                $employeeId = $this->employeeId[$commit['author_email']];
            } else {
                $employeeId = Employee::select(['id'])
                    ->where('email', $commit['author_email'])
                    ->first();
                if (!$employeeId) {
                    $employeeId = 0;
                } else {
                    $employeeId = $employeeId->id;
                }
                $this->employeeId[$commit['author_email']] = $employeeId;
            }
            if (!$employeeId) {
                continue;
            }
            $key = $employeeId . '-' . $project->project_id;
            if (!isset($this->dataInsertEmployee[$key])) {
                $this->dataInsertEmployee[$key] = [
                    'empl_id' => $employeeId,
                    'proj_id' => $project->project_id,
                    'value' => $line,
                    'created_at' => $date,
                    'team_id' => $project->team_ids,
                ];
            } else {
                $this->dataInsertEmployee[$key]['value'] += $line;
            }
        }
    }

    /**
     * store line of code of project to statistic
     *
     * @param model $project
     * @param int $line
     * @param string $date Y-m-d
     */
    public function dataProjLoc($project, $line = 0, $date = null)
    {
        if (!$line) {
            return null;
        }
        if (!$date) {
            $date = Carbon::now()->format('Y-m-d');
        }
        $this->dataInsertProj[] = [
            'created_at' => $date,
            'proj_id' => $project->project_id,
            'value' => $line,
            'team_id' => $project->team_ids,
        ];
    }
}
