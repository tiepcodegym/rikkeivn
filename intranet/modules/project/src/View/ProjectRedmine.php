<?php

namespace Rikkei\Project\View;

use Redmine\Client;
use Rikkei\Core\Model\CoreConfigData;
use Exception;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Team\Model\Employee;
use Rikkei\Project\Model\Project;
use Illuminate\Support\Facades\Lang;
use Rikkei\Statistic\Helpers\STProjBugHelper;
use Rikkei\Statistic\Models\STProjBug;

class ProjectRedmine
{
    protected static $instance;

    protected $redmine;
    protected $config;
    protected $tracker;
    protected $status;
    protected $roles;
    protected $members;
    
    protected $message;
    
    const LIMIT = 10000;
    
    /**
     * contructor
     */
    public function __construct() 
    {
        $this->config = CoreConfigData::getRemineApi();
        if (!$this->config ||
            !isset($this->config['url']) ||
            !$this->config['url'] ||
            !isset($this->config['key']) ||
            !$this->config['key']
        ) {
            return null;
        }
        try {
            $this->redmine = new Client($this->config['url'], $this->config['key']);
        } catch (Exception $ex) {
            $this->redmine = null;
            $this->message = $ex->getMessage();
        }
        return $this;
    }
    
    /**
     * count bug in redmine
     * 
     * @param int|string $project project in redmine - source server
     * @return array
     */
    public function countBug($project)
    {
        $stBug = new STProjBugHelper();
        $bugs = $stBug->setDate()->countProjectBug($project);
        if (!$bugs) {
            return null;
        }
        $bugs = reset($bugs);
        return [
            'leakage' => $bugs[STProjBug::TYPE_BUG_LEAKAGE],
            'defect' => $bugs[STProjBug::TYPE_BUG_DEFECT],
            'defect_reward' => $bugs[STProjBug::TYPE_BUG_DR]
        ];
    }

    public function countActivity($project)
    {
        $stActivity = new STProjBugHelper();
        $activity = $stActivity->setDate()->countProjectActivity($project);
        if (!$activity) {
            return null;
        }
        $activity = reset($activity);
        return [
            'correction_cost' => $activity[STProjBug::CORRECTION_COST],
            'cost_actual_effort' => $activity[STProjBug::LOG_TIME],
        ];
    }
    
    /**
     * get emails of all member in project redmine
     * 
     * @param int|string $projectId id project in redmine
     * @return type
     */
    public function getMember($projectId)
    {
        if (!$this->redmine || $this->members) {
            return null;
        }
        $result = [
            'emails' => [],
            'users' => []
        ];
        $members = $this->redmine->membership->all($projectId);
        $this->cloneRedmine();
        if (!$members || !isset($members['memberships'])) {
            return null;
        }
        foreach ($members['memberships'] as $item) {
            if (!isset($item['user']['id']) || !isset($item['roles'])) {
                continue;
            }
            $user = $this->redmine->user->show($item['user']['id']);
            if (!$user || !isset($user['user'])) {
                continue;
            }
            $roleUsers = [];
            foreach ($item['roles'] as $role) {
                $roleUsers[] = $role['id'];
            }
            $result['users'][$item['user']['id']] = [
                'user' => $user['user']['mail'],
                'roles' => $roleUsers
            ];
            $result['emails'][$item['user']['id']] = $user['user']['mail'];
        }
        $this->members = $result;
        return $this;
    }
    
    /**
     * sync member system and redmine
     * 
     * @param int|string $projectId id project in redmine
     * @param object $project project in system
     */
    public function syncMember($projectId, $project)
    {
        $this->addMemberToSystem($projectId, $project);
        $this->addMemberToRedmine($projectId, $project);
    }
    
    /**
     * add member to system from redmine
     * 
     * @param int|string $projectId id project in redmine
     * @param object $project project in system
     * @param array $projectMemberSystem
     */
    public function addMemberToSystem($projectId, $project, $projectMemberSystem = null)
    {
        $this->getMember($projectId);
        $this->syncRolesMemberType();
        if (!$this->members || !$this->roles) {
            return;
        }
        if (!$projectMemberSystem) {
            $tableEmployee = Employee::getTableName();
            $tableMember = ProjectMember::getTableName();
            $projectMemberSystem = ProjectMember::select("{$tableEmployee}.email")
            ->where('project_id', $project->id)
            ->join($tableEmployee, "{$tableEmployee}.id", '=', "{$tableMember}.employee_id")
            ->groupBy("{$tableMember}.employee_id")
            ->lists('email')->toArray();
        }
        foreach ($this->members['users'] as $item) {
            if (in_array($item['user'], $projectMemberSystem) || 
                !isset($this->roles[reset($item['roles'])])
            ) {
                continue;
            }
            
            //check member in system
            $employee = Employee::select('id')
                ->where('email', $item['user'])
                ->first();
            if (!$employee) {
                continue;
            }
            ProjectMember::insertProjectMember([
                'type' => $this->roles[reset($item['roles'])],
                'employee_id' => $employee->id,
                'effort' => '100',
                'number_record' => '1',
                'isAddNew' => true,
                'project_id' => $project->id,
            ]);
        }
    }
    
    /**
     * add member to redmine from system
     * 
     * @param int|string $projectId id project in redmine
     * @param object $project project in system
     * @param array $projectMemberSystem
     */
    public function addMemberToRedmine($projectId, $project, $projectMemberSystem = null)
    {
        $projectRedmine = $this->redmine->project->show($projectId);
        if (!$projectRedmine) {
            return false;
        }
        $this->cloneRedmine();
        $this->syncRolesMemberType();
        if (!$this->roles) {
            return false;
        }
        $this->getMember($projectId);
        if (!$this->members['emails']) {
            $memberRedmine = [];
        } else {
            $memberRedmine = $this->members['emails'];
        }
        if (!$projectMemberSystem) {
            $tableEmployee = Employee::getTableName();
            $tableMember = ProjectMember::getTableName();
            $projectMemberSystem = ProjectMember::select("{$tableEmployee}.email",
                    "{$tableMember}.type")
                ->where('project_id', $project->id)
                ->where("{$tableMember}.status", ProjectMember::STATUS_APPROVED)
                ->whereNotIn("{$tableEmployee}.email", $memberRedmine)
                ->join($tableEmployee, "{$tableEmployee}.id", '=', "{$tableMember}.employee_id")
                ->groupBy("{$tableMember}.employee_id")
                ->get();
        }
        $projectLeader = Project::getLeaderOfProject($project->id);
        if ($projectLeader) {
            $user = $this->redmine->user->all([
                'name' => $projectLeader->email
            ]);
            $this->cloneRedmine();
            if($user || isset($user['users']) || count($user['users'])) {
                $roleRedmine = array_search(ProjectMember::TYPE_PM, $this->roles);
            $this->redmine->membership->create($projectId, [
                'user_id' => $user['users'][0]['id'],
                'role_ids' => [$roleRedmine],
            ]);
            $this->cloneRedmine();
            }
        }
        try {
            foreach ($projectMemberSystem as $item) {
                $roleRedmine = array_search($item->type, $this->roles);
                if (!$roleRedmine) {
                    continue;
                }
                $user = $this->redmine->user->all([
                    'name' => $item->email
                ]);
                $this->cloneRedmine();
                if (!$user || !isset($user['users']) || !count($user['users'])) {
                    continue;
                }
                $user = reset($user['users']);
                $result = $this->redmine->membership->create($projectId, [
                    'user_id' => $user['id'],
                    'role_ids' => [$roleRedmine],
                ]);
                $this->cloneRedmine();
            }
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }
    
    /**
     * add project to redmine
     * 
     * @param int|string $projectId id project in redmine
     */
    public function addProject($projectId, $projectName = null)
    {
        if (!$this->redmine) {
            return;
        }
        $project = $this->redmine->project->show($projectId);
        if ($project && isset($project['project']) && $project['project'] ) {
            return true;
        }
        $this->cloneRedmine();
        try {
            $this->redmine->project->create([
                'name' => $projectName ? $projectName : $projectId,
                'identifier' => $projectId,
                'is_public' => 0
            ]);
            $this->cloneRedmine();
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }
    
    /**
     * get all trancker
     * 
     * @return \Rikkei\Project\View\ProjectRedmine
     */
    public function tracker()
    {
        if ($this->redmine) {
            $this->tracker = $this->redmine->tracker->all();
        }
        return $this;
    }
    
    /**
     * get all status
     * 
     * @return \Rikkei\Project\View\ProjectRedmine
     */
    public function status()
    {
        if ($this->redmine && !$this->status) {
            $this->status = $this->redmine->issue_status->all();
        }
        return $this;
    }
    
    /**
     * get id status rejected
     * 
     * @return \Rikkei\Project\View\ProjectRedmine
     */
    public function getIdStatusRejected()
    {
        $this->status();
        if (!isset($this->status['issue_statuses']) ||
            !count($this->status['issue_statuses'])
        ) {
            return null;
        }
        foreach ($this->status['issue_statuses'] as $item) {
            if ($item['name'] == $this->config['issue_type_rejected']) {
                return $item['id'];
            }
        }
        return null;
    }
    
    /**
     * Singleton instance
     * 
     * @return \Rikkei\Project\View\ProjectRedmine
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new static;
        }
        return self::$instance;
    }
    
    /**
     * sync roles in remine with member type of system
     *      id_role_redmine => type_memeber_system
     * 
     * @return \Rikkei\Project\View\ProjectRedmine
     */
    public function syncRolesMemberType()
    {
        if ($this->roles) {
            return $this;
        }
        $rolesRemine = $this->redmine->role->all();
        dd($this);
        $this->cloneRedmine();
        if (!$rolesRemine || 
                !isset($rolesRemine['roles']) || 
                !count($rolesRemine['roles'])
        ) {
            return;
        }
        $arrayTitleSync = [
            'Manager' => ProjectMember::TYPE_PM,
            'Developer' => ProjectMember::TYPE_DEV,
            'Tester' => ProjectMember::TYPE_SQA
        ];
        $result = [];
        foreach ($rolesRemine['roles'] as $item) {
            if (!$item['name'] || 
                    !$item['id'] || 
                    !isset($arrayTitleSync[$item['name']])
            ) {
                continue;
            }
            $result[$item['id']] = $arrayTitleSync[$item['name']];
        }
        $this->roles = $result;
        return $this;
    }
    
    /**
     * get redmine api
     * 
     * @return type
     */
    public function getApi()
    {
        return $this->redmine;
    }
    
    /**
     * check connection to redmine server
     */
    public function checkConnection()
    {
        $response = [];
        if ($this->message) {
            $response['error'] = 1;
            $response['message'] = $this->message;
            return $response;
        }
        if (!$this->redmine) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not connect to redmine');
            return $response;
        }
        try {
            $user = $this->redmine->api('user')->getCurrentUser();
        } catch (Exception $ex) {
            $response['error'] = 1;
            $response['message'] = $ex->getMessage();
            return $response;
        }
        if (!$user || !isset($user['user'])) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not connect to redmine');
            return $response;
        }
        $response['success'] = 1;
        $response['message'] = Lang::get('project::message.Connected to redmine by account :email',[
            'email' => $user['user']['mail']
        ]);
        return $response;
    }
    
    /**
     * clone object
     * 
     * @return \self
     */
    protected function cloneRedmine()
    {
        try {
            $this->redmine = new Client($this->config['url'], $this->config['key']);
        } catch (Exception $ex) {
            $this->redmine = null;
            $this->message = $ex->getMessage();
        }
        return $this;
    }
    
    /**
     * search a array include a work in element
     * 
     * @param array $array
     * @param string $work
     * @return boolean
     */
    private function searchArrayHaveWork(array $array = [], $work)
    {
        if (!count($array)) {
            return false;
        }
        foreach ($array as $item) {
            if (strpos(strtolower($work), $item) !== false) {
                return true;
            }
        }
        return false;
    }
}
