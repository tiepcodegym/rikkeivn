<?php

namespace Rikkei\Project\View;

use Rikkei\Core\Model\CoreConfigData;
use Exception;
use Gitlab\Client;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Lang;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Team\Model\Employee;
use Illuminate\Support\Facades\Log;

class ProjectGitlab
{
    protected static $instance;

    protected $gitlab;
    protected $config;
    
    protected $message;

    const LIMIT = 10000;
    const FOLDER_GIT = 'git/';
    const FOLDER_CLONE = 'git/clone/';

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
        $parse = parse_url($this->config['url']);
        $this->config['host'] = $parse['host'];
        $this->config['protocol'] = $parse['scheme'];
        try {
            $this->gitlab = Client::create($this->config['url'])
                ->authenticate($this->config['token'], Client::AUTH_URL_TOKEN);
        } catch (Exception $ex) {
            $this->gitlab = null;
            $this->message = $ex->getMessage();
        }
        return $this;
    }

    /**
     * get project gitlab path from project
     *
     * @param model $project
     * @return array
     */
    public function getProjGitlab($project)
    {
        if (!$this->gitlab) {
            return null;
        }
        if ($project->is_check_git) {
            $projGitPath = $project->id_git;
        } else {
            $projGitPath = $project->id_git_external;
            if (!$projGitPath) {
                return null;
            }
            $parse = parse_url($projGitPath);
            if (!isset($parse['host'])) {
                return null;
            }
            if ($this->config['host'] !== $parse['host']) {
                return null;
            }
            $projGitPath = trim($parse['path'], '/');
            if (!$projGitPath) {
                return null;
            }
        }
        return $this->isProjectExists($projGitPath);
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
            isset($projectGitlab['id'])
        ) {
            $this->addMemberToGitlab($project, $projectGitlab);
            return $projectGitlab;
        }
        $splitId[0] = trim($splitId[0]);
        $splitId[1] = trim($splitId[1]);
        // create namespace group
        try {
            $group = $this->gitlab->api('groups')->show($splitId[0]);
        } catch (Exception $ex) {
            $code = $ex->getCode();
            if ($code === 404) {// not have group
                $group = null;
            } else {
                return false;
            }
        }
        if (!$group) { //create namespace group
            $group = $this->gitlab->api('groups')->create(
                $splitId[0],
                $splitId[0]
            );
        }
        $nameProject = preg_replace('/[\s]{2,}|[^a-zA-Z0-9\-\_]+/', ' ', $project->name);
        $nameProject = preg_replace('/^(\s+)|(\s+)$/', '', $nameProject);
        try {
            $projectGitlab = $this->gitlab->api('projects')->create($nameProject, [
                'path' => $splitId[1],
                'namespace_id' => $group['id'],
                'visibility_level' => 'private'
            ]);
        } catch (Exception $ex) {
            $projectGitlab = null;
        }
        if (!$projectGitlab ||
            !isset($projectGitlab['id'])
        ) {
            return false;
        }
        $this->addMemberToGitlab($project, $projectGitlab);
        return $projectGitlab;
    }
    
    /**
     * add member (PM) to gitlab from system
     * 
     * @param object $project project in system
     * @param array $projectGitlab
     */
    public function addMemberToGitlab($project, $projectGitlab)
    {
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
            $account = preg_replace('/@.*/', '', $item->email);
            $userGitlab = $this->gitlab->api('users')->all([
                'username' => $account,
            ]);
            if (!$userGitlab) {
                continue;
            }
            $userGitlab = reset($userGitlab);
            try {
                $this->gitlab->api('projects')
                    ->addMember($projectGitlab['id'], $userGitlab['id'] , 40);// PM access
            } catch (Exception $ex) {
            }
        }
    }

    public function showMembersOfProject($projectId)
    {
        return $this->gitlab->api('projects')
                    ->members($projectId);
    }

    /**
     * Get danh sách dự án
     * Limit 50 bản ghi / trang
     *
     * @param int $page
     * @return array
     */
    public function getAllProjects($page)
    {
        return $this->gitlab->api('projects')->all([
            'per_page' => 50,
            'page' => $page,
            'order_by' => 'name',
            'sort' => 'asc',
        ]);
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
            isset($projectGitlab['id'])
        ) {
            return $projectGitlab;
        }
        return false;
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
    }

    public function hasUsernameExist($username)
    {
        $userGitlab = $this->gitlab->api('users')->all([
            'username' => $username,
        ]);
        return count($userGitlab) > 0;
    }

    /**
     * Blog account git
     * @param string $account
     */
    public function blogAccountGit($account)
    {
        $userGitlab = $this->gitlab->api('users')->all([
        'username' => $account,
        ]);
        if (!empty($userGitlab)) {
            $this->gitlab->api('users')->block($userGitlab[0]['id']);
        }
    }

    /**
     * Get list account gitlab username not equal email
     *
     * @param int $page
     * @param int $perPage
     *
     * @return array
     */
    public function getAccUsernameNotEqualEmail($page = 1, $perPage = 100)
    {
        $userGitlab = $this->gitlab->api('users')->all([
            'active' => true,
            'page' => $page,
            'per_page' => $perPage,
        ]);
        $listUser = [];
        foreach ($userGitlab as $user) {
            $account = strtolower(preg_replace('/@.*$/', '', $user['email']));
            if (strtolower($user['username']) != strtolower($account)) {
                $listUser[] = $user;
            }
        }
        return $listUser;
    }

    /**
     * Change gitlab username by email
     *
     * @param array $user
     *
     * @return void
     */
    public function changeUsername($user)
    {
        $account = strtolower(preg_replace('/@.*$/', '', $user['email']));
        $this->gitlab->api('users')->update($user['id'], [
            'username' => $account,
        ]);
    }

    /**
     * Create account gitlab
     *
     * @param $employee
     * @return array
     */
    public function createAccount($employee)
    {
        $account = strtolower(preg_replace('/@.*$/', '', $employee->email));
        $pass = substr(md5($employee->email . mt_rand() . time()), 0, 10);
        try {
            $checkExist = $this->gitlab->api('users')->all([
                'username' => $account
            ]);
            if ($checkExist) {
                return [
                    'success' => 0,
                    'message' => 'Username has already been taken'
                        . '<br/>Url: <a href="'. $this->config['project_url'] .'" target="_blank">' . $this->config['project_url']. $account. '</a>'
                        . "<br/>Login: {$account}; email: {$employee->email}",
                ];
            }
            $userCreate = $this->gitlab->api('users')->create($employee->email, $pass, [
                'name' => trim($employee->name),
                'username' => $account,
                'skip_confirmation' => true,
            ]);
        } catch (Exception $ex) {
            Log::error($ex);
            return [
                'status' => 0,
                'success' => 0,
                'message' => $ex->getMessage(),
            ];
        }
        if (!$userCreate) {
            return [
                'success' => 0,
                'message' => trans('call_api::message.gitlab not connect'),
            ];
        }
        return [
            'success' => 1,
            'message' => trans('call_api::message.gitlab create account success', [
                'url' => $this->config['project_url'],
                'account' => $account,
                'pass' => $pass,
            ]),
        ];
    }

    public function userChangePass($employee)
    {
        $account = strtolower(preg_replace('/@.*$/', '', $employee->email));
        $user = $this->gitlab->api('users')->all([
            'username' => $account
        ]);

        if (empty($user)) {
            return [
                'success' => 0,
                'message' => trans('call_api::message.Not found item'),
            ];
        }
        $pass = substr(md5($employee->email . mt_rand() . time()), 0, 10);
        try {
            $this->gitlab->api('users')->update($user[0]['id'], [
                'password' => $pass,
            ]);
        } catch (Exception $ex) {
            Log::error($ex);
            return [
                'status' => 0,
                'success' => 0,
                'message' => $ex->getMessage(),
            ];
        }
        return [
            'success' => 1,
            'status' => 1,
            'message' => trans('call_api::message.gitlab change password success', [
                'url' => $this->config['project_url'],
                'account' => $account,
                'pass' => $pass,
            ]),
        ];
    }

    /**
     * Get tất cả thành viên trong dự án
     *
     * @param int $projectId    id của dự án trên gitlab
     * @return string   danh sách thành viên trong dự án
     */
    public function showMembers($projectId)
    {
        $members = $this->showMembersOfProject($projectId);
        $arrMems = [];
        if (count($members)) {
            foreach ($members as $mem) {
                $arrMems[] = $mem['username'];
            }
        }
        return implode(', ', $arrMems);
    }
}
