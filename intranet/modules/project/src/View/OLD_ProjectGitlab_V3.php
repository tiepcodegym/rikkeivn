<?php

namespace Rikkei\Project\View;

use Rikkei\Core\Model\CoreConfigData;
use Exception;
use Gitlab\Client;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Storage;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\SourceServer;
use Illuminate\Support\Facades\Lang;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Team\Model\Employee;
use Illuminate\Support\Str;

class OldProjectGitlabV3
{
    protected static $instance;

    protected $gitlab;
    protected $config;
    
    protected $message;

    const LIMIT = 10000;
    const FOLDER_GIT = 'git';
    const FOLDER_CLONE = 'git/clone';
    const FOLDER_PROCESS = 'git/process';
    
    const RUN_WAIT = 1;
    const RUN_PROCESS = 2;
    const RUN_ERROR = 3;

    const ACCESS_FOLDER = 0777;
    const ACCESS_FILE = 'public';
    
    /**
     * contructor
     */
    public function __construct() 
    {
        $this->config = CoreConfigData::getGitlabApi();
        if (!$this->config) {
            return null;
        }
        try {
            $this->gitlab = new Client($this->config['url']);
            $this->gitlab->authenticate($this->config['token']);
        } catch (Exception $ex) {
            $this->gitlab = null;
            $this->message = $ex->getMessage();
        }
        return $this;
    }
    
    /**
     * count line of code of project
     * 
     * @param int|string $projectPath project in gitlab
     * @return array
     */
    public function loc($projectPath, $projectId)
    {
        if (!$this->gitlab) {
            return null;
        }
        try {
            $project = $this->gitlab->api('projects')->show($projectPath);
            if (!$project || !count($project) || !isset($project['ssh_url_to_repo'])) {
                return null;
            }
            $repo = $this->gitlab->api('repo')->archive($projectPath);
        } catch (Exception $ex) {
            return null;
        }
        $fileArchiveProject = self::FOLDER_CLONE. '/' . $projectId . '.tar.gz';
        if (!Storage::exists($fileArchiveProject)) {
            Storage::put($fileArchiveProject, $repo, self::ACCESS_FILE);
        }
        if (!Storage::exists($fileArchiveProject)) {
            return null;
        }
        if (!Storage::exists(self::FOLDER_CLONE . '/'. $projectId)) {
            Storage::makeDirectory(self::FOLDER_CLONE . '/'. $projectId, self::ACCESS_FOLDER);
        }
        @chmod(storage_path('app/' . self::FOLDER_CLONE . '/'. $projectId), self::ACCESS_FOLDER);
        $fileArchiveProjectPath = storage_path('app/' . self::FOLDER_CLONE 
            . '/'. $projectId);
        $process = new Process('tar zxf ' . storage_path('app/' . $fileArchiveProject) . 
            ' -C ' . $fileArchiveProjectPath);
        $process->run();
        if (!$process->isSuccessful()) {
            Storage::deleteDirectory(self::FOLDER_CLONE . '/'. $projectId);
            Storage::delete($fileArchiveProject);
            Storage::delete(self::FOLDER_PROCESS . '/'. $projectId);
            throw new ProcessFailedException($process);
        }
        $process = new Process("cd {$fileArchiveProjectPath} && git init && git add .");
        $process->run();
        if (!$process->isSuccessful()) {
            Storage::deleteDirectory(self::FOLDER_CLONE . '/'. $projectId);
            Storage::delete($fileArchiveProject);
            Storage::delete(self::FOLDER_PROCESS . '/'. $projectId);
            throw new ProcessFailedException($process);
        }
        $process = new Process("cd {$fileArchiveProjectPath} "
         . " && git ls-files | xargs cat | wc -l");
        $process->run();
        // executes after the command finishes
        if (!$process->isSuccessful()) {
            Storage::deleteDirectory(self::FOLDER_CLONE . '/'. $projectId);
            Storage::delete($fileArchiveProject);
            Storage::delete(self::FOLDER_PROCESS . '/'. $projectId);
            throw new ProcessFailedException($process);
        }
        $line = $process->getOutput();
        Storage::deleteDirectory(self::FOLDER_CLONE . '/'. $projectId);
        Storage::delete($fileArchiveProject);
        Storage::delete(self::FOLDER_PROCESS . '/'. $projectId);
        return $line;
    }
    
    /**
     * Create file tmp in to check process loc
     * 
     * @param int $projectId
     */
    public function createSchedule($projectId)
    {
        $this->createFiles();
        if (!Storage::exists(self::FOLDER_PROCESS . '/'. $projectId)) {
            Storage::put(self::FOLDER_PROCESS . '/'. $projectId, self::RUN_WAIT,
                self::ACCESS_FILE);
        }
        @chmod(storage_path('app/' . self::FOLDER_PROCESS . '/'. $projectId),
                self::ACCESS_FOLDER);
    }
    
    /**
     * check process git
     * 
     * @param type $projectId
     * @return boolean
     */
    public function isProcess($projectId)
    {
        if (!Storage::exists(self::FOLDER_PROCESS . '/'. $projectId)) {
            return false;
        }
        return true;
    }
    
    /**
     * process count loc project
     */
    public function processAll()
    {
        $files = Storage::files(self::FOLDER_PROCESS);
        if (!$files || !count($files)) {
            return;
        }
        $this->createFiles();
        foreach ($files as $pathFile) {
            $content = (int) Storage::get($pathFile);
            if ($content == self::RUN_PROCESS) {
                continue;
            }
            preg_match('/[0-9]+$/', $pathFile, $projectId);
            if ($projectId) {
                Storage::put($pathFile, self::RUN_PROCESS, self::ACCESS_FILE);
                $this->processItem((int) reset($projectId));
            }
            Storage::delete($pathFile);
        }
    }
    
    /**
     * process item count loc
     * 
     * @param int $projectId
     * @return boolean
     */
    public function processItem($projectId)
    {
        $project = Project::find($projectId);
        if (!$project) {
            return false;
        }   
        $projectMeta = $project->getProjectMeta();
        $sourceServer = SourceServer::getSourceServer($projectId);
        $isSyncSourceServer = SourceServer::getSyncSourceServer($projectId, $sourceServer);
        if (!$projectMeta || !$isSyncSourceServer['git']) {
            return false;
        }
        $line = $this->loc($sourceServer->id_git, $projectId);
        if (!$projectMeta->lineofcode_baseline) {
            $projectMeta->lineofcode_baseline = $line;
        }
        $projectMeta->lineofcode_current = $line;
        $projectMeta->save();
        return true;
    }
    
    /**
     * create and chmod files use git
     */
    public function createFiles()
    {
        if (!Storage::exists(self::FOLDER_GIT)) {
            Storage::makeDirectory(self::FOLDER_GIT, self::ACCESS_FOLDER);
        }
        @chmod(storage_path('app/' . self::FOLDER_GIT), self::ACCESS_FOLDER);
        if (!Storage::exists(self::FOLDER_CLONE)) {
            Storage::makeDirectory(self::FOLDER_CLONE, self::ACCESS_FOLDER);
        }
        @chmod(storage_path('app/' . self::FOLDER_CLONE), self::ACCESS_FOLDER);
        if (!Storage::exists(self::FOLDER_PROCESS)) {
            Storage::makeDirectory(self::FOLDER_PROCESS, self::ACCESS_FOLDER);
        }
        @chmod(storage_path('app/' . self::FOLDER_PROCESS), self::ACCESS_FOLDER);
    }
    
    /**
     * check connection to gitlab server
     */
    public function checkConnection()
    {
        $response = [];
        
        if ($this->message) {
            $response['error'] = 1;
            $response['message'] = $this->message;
            return $response;
        }
        if (!$this->gitlab) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not connect to gitlab');
            return $response;
        }
        try {
            $user = $this->gitlab->api('users')->me();
        } catch (Exception $ex) {
            $response['error'] = 1;
            $response['message'] = $ex->getMessage();
            return $response;
        }
        if (!$user || !count($user) || !isset($user['username'])) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not connect to gitlab');
            return $response;
        }
        $response['success'] = 1;
        $response['message'] = Lang::get('project::message.Connected to gitlab by account :email',[
            'email' => $user['username']
        ]);
        return $response;
    }
    
    /**
     * return gitlab api controller
     * 
     * @return type
     */
    public function getApi()
    {
        return $this->gitlab;
    }
    
    /**
     * Singleton instance
     * 
     * @return \self
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new static;
        }
        return self::$instance;
    }
    
    /**
     * create project in gitlab
     * 
     * @param object $project
     * @param object $projectSourceInfo
     * @return boolean|object
     */
    public function createProject($project, $projectSourceInfo)
    {
        $projectSourceInfo->id_git = trim($projectSourceInfo->id_git);
        $splitId = explode('/', $projectSourceInfo->id_git);
        if (!$splitId || count($splitId) != 2) {
            return false;
        }
        try {
            $projectGitlab = $this->gitlab->api('projects')
                ->show($projectSourceInfo->id_git);
        } catch (Exception $ex) {
            $projectGitlab = null;
        }
        if ($projectGitlab &&
            count($projectGitlab) &&
            isset($projectGitlab['ssh_url_to_repo'])
        ) {
            $this->addMemberToGitlab($project, $projectGitlab);
            return $projectGitlab;
        }
        $splitId[0] = trim($splitId[0]);
        $splitId[1] = trim($splitId[1]);
        $page = 0;
        while(1) {
            $page++;
            $namespaces = $this->gitlab->api('namespaces')
                ->search($splitId[0], $page);
            if (!$namespaces) { //create namespace group
                $namespace = $this->gitlab->api('groups')->create(
                    $splitId[0],
                    $splitId[0]
                );
                break;
            }
            // because git search follow Like => find
            $namespace = null;
            foreach ($namespaces as $item) {
                if ($item['path'] === $splitId[0]) {
                    $namespace = $item;
                    break;
                }
            }
            if ($namespace) {
                break;
            }
        }
        $nameProject = preg_replace('/[\s]{2,}|[^a-zA-Z0-9\-\_]+/', ' ', $project->name);
        $nameProject = preg_replace('/^(\s+)|(\s+)$/', '', $nameProject);
        try {
            $projectGitlab = $this->gitlab->api('projects')->create($nameProject, [
                'path' => $splitId[1],
                'namespace_id' => $namespace['id'],
                'visibility_level' => 0
            ]);
            $this->addMemberToGitlab($project, $projectGitlab);
        } catch (Exception $ex) {
            $projectGitlab = null;
        }
        if (!$projectGitlab ||
            !count($projectGitlab) ||
            !isset($projectGitlab['ssh_url_to_repo'])
        ) {
            return false;
        }
        return $projectGitlab;
    }
    
    /**
     * add member to gitlab from system
     * 
     * @param object $project project in system
     * @param array $projectGitlab
     */
    public function addMemberToGitlab($project, $projectGitlab)
    {
        $members = $this->gitlab->api('projects')
            ->members($projectGitlab['id']);
        if ($members) {
            return false;
        }
        // get member system
        $tableEmployee = Employee::getTableName();
        $tableMember = ProjectMember::getTableName();
        $projectMemberSystem = ProjectMember::select("{$tableEmployee}.email")
            ->where('project_id', $project->id)
            ->where("{$tableMember}.status", ProjectMember::STATUS_APPROVED)
            ->where("{$tableMember}.type",  ProjectMember::TYPE_PM)
            ->join($tableEmployee, "{$tableEmployee}.id", '=', "{$tableMember}.employee_id")
            ->groupBy("{$tableMember}.employee_id")
            ->get();
        if (!count($projectMemberSystem)) {
            return false;
        }
        // add PM to gitlab
        foreach ($projectMemberSystem as $item) {
            $userGitlab = $this->gitlab->api('users')->search($item->email);
            if (!$userGitlab) {
                continue;
            }
            $userGitlab = reset($userGitlab);
            try {
                $this->gitlab->api('projects')
                    ->addMember($projectGitlab['id'], $userGitlab['id'] , 40);
            } catch (Exception $ex) {
            }
        }
    }

    /**
     * is project exists
     *
     * @param string $projectKey
     * @return boolean
     */
    public function isProjectExists($projectKey)
    {
        $splitId = explode('/', $projectKey);
        if (!$splitId || count($splitId) != 2) {
            return false;
        }
        try {
            $projectGitlab = $this->gitlab->api('projects')
                ->show($projectKey);
        } catch (Exception $ex) {
            return false;
        }
        if ($projectGitlab &&
            count($projectGitlab) &&
            isset($projectGitlab['ssh_url_to_repo'])
        ) {
            return true;
        }
        return false;
    }
}
