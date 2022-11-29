<?php
namespace Rikkei\CallApi\Helpers;

use Rikkei\Core\Model\CoreConfigData;
use Redmine\Client;
use GuzzleHttp\Client as GuzzClient;
use Illuminate\Support\Facades\Log;
use Exception;
use Rikkei\Core\View\CacheBase;
use Rikkei\Team\Model\Team;

class Redmine
{
    protected $config;
    protected $redmine;
    protected $redmineClient;

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
        $this->reClone();
        return $this;
    }

    /**
     * clone object
     * 
     * @return \self
     */
    public function reClone()
    {
        try {
            $this->redmine = new Client($this->config['url'], $this->config['key']);
        } catch (Exception $ex) {
            Log::info($ex);
            $this->redmine = null;
        }
        return $this;
    }

    /**
     * create connect to server redmine by GuzzleHttp\Client
     *
     * @return \self
     */
    public function reCloneClient($isCreateNew = false)
    {
        if (!$isCreateNew && $this->redmineClient) {
            return $this->redmineClient;
        }
        $this->redmineClient = new GuzzClient([
            'base_uri' => $this->config['url'],
            'auth' => [$this->config['key'], ''],
            'allow_redirects' => true,
            'http_errors' => false,
        ]);
        return $this->redmineClient;
    }

    /**
     * create user in redmine
     *
     * @param object $employee
     * @return array
     */
    public function userCreate($employee)
    {
        $name = trim($employee->name);
        $pos = strrpos($name, ' ');
        if ($pos === false) {
            $first = $name;
            $last = 'u';
        } else {
            $first = substr($name, 0, $pos);
            $last = substr($name, $pos + 1);
        }
        if (!$first) {
            $first = 'u';
        }
        if (!$last) {
            $last = 'u';
        }
        $account = strtolower(preg_replace('/@.*$/', '', $employee->email));
        $pass = substr(md5($employee->email . mt_rand() . time()), 0, 10);
        try {
            $userCreate = $this->redmine->user->create([
                'login' => $account,
                'password' => $pass,
                'lastname' => $last,
                'firstname' => $first,
                'mail' => $employee->email,
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
                'message' => trans('call_api::message.redmine not connect'),
            ];
        }
        if ($userCreate->error->__toString()) {
            return [
                'success' => 0,
                'message' => $userCreate->error->__toString()
                    . '<br/>Url: <a href="'. $this->config['url'] .'" target="_blank">' . $this->config['url'] . '</a>'
                    . "<br/>Login: {$account}; email: {$employee->email}",
            ];
        }
        return [
            'success' => 1,
            'message' => trans('call_api::message.redmine create account success', [
                'url' => $this->config['url'],
                'account' => $account,
                'pass' => $pass,
            ]),
        ];
    }

    /**
     * change pass of user in redmine
     *
     * @param object $employee
     * @return array
     */
    public function userChangePass($employee)
    {
        $account = strtolower(preg_replace('/@.*$/', '', $employee->email));
        $userId = $this->redmine->user->getIdByUsername($account, [
            'name' => $account
        ]);
        if (!$userId) {
            return [
                'success' => 0,
                'message' => trans('call_api::message.Not found item'),
            ];
        }
        $pass = substr(md5($employee->email . mt_rand() . time()), 0, 10);
        $this->reClone();
        try {
            $user = $this->redmine->user->update($userId, [
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
            'message' => trans('call_api::message.redmine change password success', [
                'url' => $this->config['url'],
                'account' => $account,
                'pass' => $pass,
            ]),
        ];
    }

    /**
     * get project redmine
     *
     * @param object $project
     * @return array
     */
    public function getProjRedmine($project)
    {
        if ($project->is_check_redmine) {
            $projPath = $project->id_redmine;
        } else {
            $projPath = $project->id_redmine_external;
            if (!$projPath) {
                return null;
            }
            $url = $this->config['project_url'] ? $this->config['project_url'] : $this->config['url'];
            if (strpos($projPath, $url) !== 0) {
                return null;
            }
            $projPath = substr($projPath, strlen($url));
            $projPath = trim($projPath, '/');
        }
        return $this->isProjRedmineExisis($projPath);
    }

    /**
     * check isset project redmine follow project redmine path
     *
     * @param type $projRedminePath
     * @return type
     */
    public function isProjRedmineExisis($projRedminePath)
    {
        $api = $this->reCloneClient()->get("projects/{$projRedminePath}.json");
        if ($api->getStatusCode() !== 200) {
            return null;
        }
        $project = json_decode($api->getBody()->getContents(), true);
        if (!$project || !isset($project['project']) || !$project['project']) {
            return null;
        }
        return $project['project'];
    }

    /**
     * get trackers ids filter bug
     *
     * @return array
            'bug' => [], // flag bug, comment
            'leakage' => [], // flag bug leakage
            'defect_reward' => [], // flag bug defect reward
     */
    public function getTrackerIds()
    {
        $result = CacheBase::get('red_api_tracker');
        if ($result) {
            return $result;
        }
        $api = $this->reCloneClient()->get('trackers.json');
        if ($api->getStatusCode() !== 200) {
            return null;
        }
        $trackers = json_decode($api->getBody()->getContents(), true);
        if (!$trackers || !isset($trackers['trackers']) || !$trackers['trackers']) {
            return null;
        }
        $result = [
            'bug' => [], // flag bug, comment
            'leakage' => [], // flag bug leakage
            'defect_reward' => [], // flag bug defect reward
        ];
        $flagBug = (array)CoreConfigData::get('project.redmine_api.issue_title_bug_flag');
        $flagLeakage = (array)CoreConfigData::get('project.redmine_api.issue_leakage_flag');
        $flagDefRew = (array)CoreConfigData::get('project.redmine_api.issue_defect_reward_flag');
        foreach ($trackers['trackers'] as $tracker) {
            // check tracker is bug
            $isCheck = false;
            foreach ($flagBug as $fb) {
                if (strpos(strtolower($tracker['name']), $fb) !== false) {
                    $isCheck = true;
                    break;
                }
            }
            if (!$isCheck) {
                continue;
            }
            $result['bug'][] = $tracker['id'];
            // check tracker is leakage
            $isCheck = false;
            foreach ($flagLeakage as $fb) {
                if (strpos(strtolower($tracker['name']), $fb) !== false) {
                    $isCheck = true;
                    break;
                }
            }
            if ($isCheck) {
                $result['leakage'][] = $tracker['id'];
            }
            // check tracker is defect reward
            $isCheck = false;
            foreach ($flagDefRew as $fb) {
                if (strpos(strtolower($tracker['name']), $fb) !== false) {
                    $isCheck = true;
                    break;
                }
            }
            if ($isCheck) {
                $result['defect_reward'][] = $tracker['id'];
            }
        }
        CacheBase::put('red_api_tracker', $result);
        return $result;
    }
    
    /**
     * get status reject ids
     *
     * @return array
     */
    public function getStatusIds()
    {
        $result = CacheBase::get('red_api_status');
        if ($result) {
            return (array) $result;
        }
        $api = $this->reCloneClient()->get('issue_statuses.json');
        if ($api->getStatusCode() !== 200) {
            return null;
        }
        $status = json_decode($api->getBody()->getContents(), true);
        if (!$status || !isset($status['issue_statuses']) || !$status['issue_statuses']) {
            return null;
        }
        $result = [
            'reject' => [],
            'closed' => [],
        ];
        $flagBug = (array) CoreConfigData::get('project.redmine_api.issue_status_rejected');
        $closeFlag = (array) CoreConfigData::get('project.redmine_api.issue_status_closed');
        foreach ($status['issue_statuses'] as $item) {
            if (!isset($item['id']) || !isset($item['name'])) {
                continue;
            }
            // check tracker is bug
            $item['name'] = strtolower($item['name']);
            foreach ($flagBug as $fb) {
                if (strpos($item['name'], $fb) !== false) {
                    $result['reject'][] = $item['id'];
                }
            }
            foreach ($closeFlag as $fb) {
                if (strpos($item['name'], $fb) !== false) {
                    $result['closed'][] = $item['id'];
                }
            }
        }
        CacheBase::put('red_api_status', $result);
        return $result;
    }

    /**
     * get custom field incurred
     *
     * @return array
            incurred => []
     */
    public function getCustomFieldIds()
    {
        $result = CacheBase::get('red_api_cf');
        if ($result) {
            return $result;
        }
        $api = $this->reCloneClient()->get('custom_fields.json');
        if ($api->getStatusCode() !== 200) {
            return null;
        }
        $cf = json_decode($api->getBody()->getContents(), true);
        if (!$cf || !isset($cf['custom_fields']) || !$cf['custom_fields']) {
            return null;
        }
        $result = [
            'incurred' => [], // user make error
        ];
        $incurred = (array)CoreConfigData::get('project.redmine_api.issue_cf_incurred_flag');
        foreach ($cf['custom_fields'] as $c) {
            // check tracker is bug
            $isCheck = false;
            foreach ($incurred as $fb) {
                if (strpos(strtolower($c['name']), $fb) !== false &&
                    $c['field_format'] === 'user'
                ) {
                    $isCheck = true;
                    break;
                }
            }
            if ($isCheck) {
                $result['incurred'][] = $c['id'];
            }
        }
        CacheBase::put('red_api_cf', $result);
        return $result;
    }

    /**
     * get filter string, default status != reject
     *
     * @param array $status ids of attribute: status, tracker, ...
     * @param array option option filter key-value
     * @return type
     */
    public function filterAttrsString(array $status = [], array $option = [])
    {
        if (!$status) {
            $status = $this->getStatusIds();
            if (!$status || !isset($status['reject'])) {
                return '&';
            }
            $status = $status['reject'];
        }
        if (!$option) {
            $option = [
                'f' => 'status_id',
                'op' => '!',
            ];
        }
        $filters = [];
        $filters['f[]'] = $option['f'];
        $filters["op[{$option['f']}]"] = $option['op'];
        $fs = http_build_query($filters);
        foreach ($status as $item) {
            $fs .= '&'.http_build_query([
                "v[{$option['f']}][]" => $item,
            ]);
        }
        return $fs . '&';
    }

    /**
     * Blog list account redmine
     */
    public function blogAccount($account)
    {
        $instance = clone $this->redmine->user;
        $userId = $instance->getIdByUsername($account, [
            'name' => $account
        ]);
        if ($userId) {
            $this->redmine->user->update($userId, [
                'status' => 3,
            ]);
        }
    }

    public function getAccUsernameNotEqualEmail()
    {
        $accounts = $this->redmine->user->all([
           'limit' => 1000,
        ]);
        $listUser = [];
        foreach ($accounts['users'] as $acc) {
            if (in_array($acc['login'], $this->accIgnore())) {
                continue;
            }
            $account = strtolower(preg_replace('/@.*$/', '', $acc['mail']));
            if (strpos($acc['mail'], '@rikkeisoft.com') !== false && strtolower($acc['login']) != strtolower($account)) {
                $listUser[] = $acc;
            }
        }
        return $listUser;
    }

    public function updateUsername($user)
    {
        $account = strtolower(preg_replace('/@.*$/', '', $user['mail']));
        $this->redmine->user->update($user['id'], [
            'login' => $account,
        ]);
    }

    public function accIgnore()
    {
        return [
            'chungdt',
            'hoadt',
            'dungpt',
        ];
    }
}
