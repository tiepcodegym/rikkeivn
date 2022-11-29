<?php

namespace Rikkei\Api\Sync;

use Rikkei\Api\Sync\BaseSync;
use Rikkei\Api\Models\ApiQueue;
use Rikkei\Core\Model\User;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Team;
//use Rikkei\Team\View\TeamConst;
use Carbon\Carbon;

/**
 * call api update users
 *
 * @author lamnv
 */
class EmployeeSync extends BaseSync
{
    const API_LIST = [
        'login' => 'login',
        'info' => 'users.info',
        'created' => 'users.create',
        'updated' => 'users.update',
        'deleted' => 'users.delete'
    ];

    /*
     * get base api url from config
     */
    public static function getBaseUrl()
    {
        return trim(config('api.sync_base_url.employee'), '/') . '/api/v1/';
    }

    /*
     * request get token api
     * @param: $type: string ('user', 'employee')
     */
    public static function getApiToken($currentUser = null, $returnId = false, $retry = 1)
    {
        if (!$currentUser) {
            return null;
        }
        if ($returnId && $currentUser->im_user_id) {
            return $currentUser->im_user_id;
        }
        if ($currentUser->im_token) {
            return $currentUser->im_token;
        }
        $apiUrl = static::getBaseUrl() . static::API_LIST['login'];
        $dataRequest = [
            'serviceName' => 'google',
            'accessToken' => $currentUser->token,
            'idToken' => $currentUser->google_id,
            'expiresIn' => (int) $currentUser->expires_in
        ];
        $response = static::callApi($apiUrl, 'POST', $dataRequest);
        if ($response['status'] == self::STT_ERROR) {
            if ($retry >= 2) {
                return null;
            }
            $currentUser->refreshGoogleAccessToken();
            $retry++;
            return self::getApiToken($currentUser, $returnId, $retry);
        }
        $currentUser->im_token = $response['data']['authToken'];
        $currentUser->im_user_id = $response['data']['userId'];
        $currentUser->save();
        if ($returnId) {
            return $response['data']['userId'];
        }
        return $response['data']['authToken'];
    }

    /*
     * call event create, update, delete user
     */
    public static function callEvent($event, $data)
    {
        $apiUrl = self::getBaseUrl();
        $apiList = self::API_LIST;
        if (!isset($apiList[$event])) {
            return false;
        }
        $apiUrl .= $apiList[$event];
        $dataFilter = self::filterDataBody($event, $data);
        if (!$dataFilter['isCall']) {
            return false;
        }
        $apiQueue = new ApiQueue();
        $empId = isset($data['id']) ? $data['id'] : null;
        $apiQueue->setUrl($apiUrl, $dataFilter['method'], true)
                ->setBodyData($dataFilter['dataBody'])
                ->setType('employee_sync', $empId)
                ->setActorId(auth()->id());
        //check delete
        $scheduleExists = null;
        if ($event == 'deleted') {
            $scheduleExists = ApiQueue::where('employee_id', $empId)
                    ->where('api_url', 'LIKE', '%delete')
                    ->whereNull('called_at')
                    ->first();
        }
        if ($dataFilter['schedule']) {
            $apiQueue->setSchedule($dataFilter['schedule']);
        }
        if ($scheduleExists && $dataFilter['schedule']) {
            $scheduleExists->setSchedule($dataFilter['schedule']);
            $scheduleExists->save();
        } else {
            $apiQueue->save();
        }
    }

    /*
     * filter data to send to api
     */
    public static function filterDataBody($event, $data = [])
    {
        $result = [
            'dataBody' => [],
            'method' => 'post',
            'isCall' => true,
            'schedule' => null
        ];
        switch ($event) {
            //create user
            case 'created':
                if (!isset($data['email'])) {
                    $result['isCall'] = false;
                    break;
                }
                $email = $data['email'];
                $account = preg_replace('/@.*/', '', $email);
                $result['dataBody'] = [
                    'email' => $email,
                    'name' => $data['name'],
                    'password' => strtoupper($account) . '0000',
                    'username' => $account,
                    'active' => true,
                    'verified' => true
                ];
                break;
            //update user
            case 'updated':
                $fieldsUpdate = ['email', 'name', 'change_team'];
                $fieldsUnset = ['change_team'];
                //if change leave_date then user delete api
                if (isset($data['new']['leave_date']) && isset($data['id'])) {
                    self::callEvent('deleted', ['id' => $data['id'], 'schedule' => $data['new']['leave_date']]);
                    $result['isCall'] = false;
                    break;
                }
                $dataUpdate = array_only($data['new'], $fieldsUpdate);
                if (!isset($data['id']) || !isset($data['new']) || !$dataUpdate) {
                    $result['isCall'] = false;
                    break;
                }
                $employeeId = $data['id'];
                $user = User::find($employeeId);
                foreach ($fieldsUnset as $usField) {
                    unset($dataUpdate[$usField]);
                }
                $result['dataBody'] = [
                    'userId' => $user ? $user->im_user_id : null,
                    'data' => $dataUpdate
                ];
                break;
            //delete user
            case 'deleted':
                if (!isset($data['id'])) {
                    $result['isCall'] = false;
                    break;
                }
                $userId = self::getImUserId($data['id']);
                $result['dataBody'] = [
                    'userId' => $userId
                ];
                $result['method'] = 'post';
                if (isset($data['schedule'])) {
                    $result['schedule'] = $data['schedule'];
                }
                break;
            default:
                $result['isCall'] = false;
                break;
        }
        return $result;
    }

    /*
     * get user id stored from api
     */
    public static function getImUserId($employeeId, $callApi = false)
    {
        $user = User::find($employeeId);
        if (!$user) {
            return null;
        }
        if ($user->im_user_id) {
            return $user->im_user_id;
        }
        if ($callApi) {
            return self::getApiToken($user, true);
        }
        return null;
    }

    /*
     * get IM User by employee
     */
    public static function getImUserByEmp($employeeId)
    {
        $employee = Employee::find($employeeId);
        if ($employee) {
            $apiUrl = static::getBaseUrl() . static::API_LIST['login'];
            $response = static::callApi($apiUrl, 'POST', [
                'user' => $employee->email,
                'password' => strtoupper($employee->getNickName()) . '0000'
            ]);
            if ($response['status'] == self::STT_ERROR) {
                return null;
            }
            return [
                'userId' => $response['data']['userId'],
                'authToken' => $response['data']['authToken']
            ];
        }
        return null;
    }

    public static function checkImUserAndCreate($employeeId, $actorToken, $actorId)
    {
        $employee = Employee::find($employeeId);
        if (!$employee) {
            return null;
        }
        $account = preg_replace('/@.*/', '', $employee->email);
        $apiUrl = static::getBaseUrl() . static::API_LIST['info'];
        $dataRequest = [
            'username' => $account
        ];
        $response = static::callApi($apiUrl, 'GET', $dataRequest, [
            'token' => $actorToken,
            'actorId' => $actorId
        ]);
        if ($response['status'] == self::STT_SUCCESS && isset($response['user'])) {
            return $response['user']['_id'];
        }
        //call api create
        $resCreate = static::callApi(
            static::getBaseUrl() . static::API_LIST['created'],
            'POST',
            [
                'email' => $employee->email,
                'name' => $employee->name,
                'password' => strtoupper($account) . '0000',
                'username' => $account,
                'active' => true,
                'verified' => true
            ],
            [
                'token' => $actorToken,
                'actorId' => $actorId
            ]
        );
        if ($resCreate['status'] == self::STT_ERROR) {
            return null;
        }
        if (isset($resCreate['user'])) {
            return $resCreate['user']['_id'];
        }
        return null;
    }

    /*
     * call api foreach item
     */
    public static function callItem($item, $retry = 1, $config = [])
    {
        $apiToken = null;
        $imActorId = null;
        if ($item->is_auth) {
            $apiToken = isset($config['authToken']) ? $config['authToken'] : null;
            $imActorId = isset($config['userId']) ? $config['userId'] : null;
        }
        $dataItem = $item->data ? json_decode($item->data, true) : [];
        if (array_key_exists('userId', $dataItem) && !$dataItem['userId']) {
            $imUserId = self::getImUserId($item->employee_id, true);
            //if api update check exists users
            if (!$imUserId && preg_match('/(.*)'. self::API_LIST['updated'] .'/', $item->api_url) && $item->employee_id) {
                //check exists user and create
                $imUserId = self::checkImUserAndCreate($item->employee_id, $apiToken, $imActorId);
            }
            $dataItem['userId'] = $imUserId;
        }
        //add division to request params
        if ($item->employee_id &&
                (preg_match('/(.*)'. self::API_LIST['created'] .'/', $item->api_url)
                || preg_match('/(.*)'. self::API_LIST['updated'] .'/', $item->api_url))) {
            $employee = Employee::find($item->employee_id);
            $dataExtra = [];
            if ($employee) {
                $dataExtra = [
                    'dateOfBirth' => Carbon::parse($employee->birthday)->format('Y/m/d')
                ];
                $empContact = $employee->getItemRelate('contact');
                if ($empContact) {
                    $dataExtra = array_merge($dataExtra, [
                        'address' => $empContact->tempo_addr ? $empContact->tempo_addr :
                                        ($empContact->native_addr ? $empContact->native_addr : ''),
                        'phone' => $empContact->mobile_phone ? $empContact->mobile_phone : ''
                    ]);
                }
            }
            if (array_key_exists('data', $dataItem)) {
                $dataItem['data']['userDivisions'] = self::getUserDivisions($item->employee_id);
                $dataItem['data'] = array_merge($dataItem['data'], $dataExtra);
            } else {
                $dataItem['userDivisions'] = self::getUserDivisions($item->employee_id);
                $dataItem = array_merge($dataItem, $dataExtra);
            }
        }

        //call api
        $result = self::callApi(
            $item->api_url,
            $item->method,
            $dataItem,
            [
                'authToken' => $apiToken,
                'userId' => $imActorId
            ]
        );
        //if not auth then recall
        if ($apiToken && $result['status'] == self::STT_ERROR
                && $result['code'] == self::CODE_AUTH) {
            $retry++;
            //num retry = 2
            if ($retry <= 2) {
                $user = User::where('employee_id', $item->employee_id)->first();
                if ($user) {
                    $user->im_token = null;
                    $user->save();
                }
                $result = self::callItem($item, $retry, $config);
            }
        }
        if ($result['status'] == self::STT_ERROR) {
            $item->error = $result['message'];
        } else {
            $item->called_at = Carbon::now()->toDateTimeString();
            $item->error = null;
        }
        return $item->save();
    }

    /**
     * get
     * @param integer $employeeId
     * @return array
     */
    public static function getUserDivisions($employeeId)
    {
        $listRoles = [
            Team::ROLE_MEMBER => 'Member',
            Team::ROLE_SUB_LEADER => 'Sub-Leader',
            Team::ROLE_TEAM_LEADER => 'TeamLeader',
        ];
        /*$listTeams = [
            TeamConst::CODE_BOD => 'BOD',
            TeamConst::CODE_HN_D1 => 'D1',
            TeamConst::CODE_HN_D2 => 'D2',
            TeamConst::CODE_HN_D3 => 'D3',
            TeamConst::CODE_HN_D5 => 'D5',
            TeamConst::CODE_HN_D6 => 'D6',
            TeamConst::CODE_HN_GD => 'GD',
            TeamConst::CODE_HN_HR => 'HR',
            TeamConst::CODE_HN_HCTH => 'TCKT',
            TeamConst::CODE_HN_HC => 'HC',
            TeamConst::CODE_HN_PR => 'PR',
            TeamConst::CODE_HN_IT => 'IT',
            TeamConst::CODE_HN_PRODUCTION => 'Production',
            TeamConst::CODE_JAPAN => 'RikkeiJapan',
            TeamConst::CODE_JAPAN_DEV => 'RikkeiJapan-PTPM',
            TeamConst::CODE_JAPAN_SALE => 'RikkeiJapan-Sales',
            TeamConst::CODE_DANANG => 'RikkeiDanang',
            TeamConst::CODE_DN_HCTH => 'RikkeiDanang-HCTH',
            TeamConst::CODE_DN_DEV => 'RikkeiDanang-PTPM',
            TeamConst::CODE_DN_IT => 'RikkeiDanang-IT',
            TeamConst::CODE_DN_D0 => 'DN0',
            TeamConst::CODE_DN_D1 => 'DN1',
            TeamConst::CODE_DN_D2 => 'DN2',
            TeamConst::CODE_DN_D3 => 'DN3',
            TeamConst::CODE_HN_SALES => 'Sales',
            TeamConst::CODE_HN_SYSTENA => 'Systena',
            TeamConst::CODE_HN_QA => 'QA',
            TeamConst::CODE_PQA => 'TQC',
            TeamConst::CODE_HN_VD => 'VD',
            TeamConst::CODE_RS => 'RikkeiSystem',
            TeamConst::CODE_AI => 'RikkeiAI',
            TeamConst::CODE_HCM_PTPM => 'RikkeiHCM-PTPM',
            TeamConst::CODE_HCM_HCTH => 'RikkeiHCM-HCTH',
            TeamConst::CODE_HN_TRAINING => 'Training',
        ];*/
        $employeeTeams = TeamMember::select('team.code', 'tmb.role_id')
                ->from(TeamMember::getTableName() . ' as tmb')
                ->join(Team::getTableName() . ' as team', 'tmb.team_id', '=', 'team.id')
                ->where('tmb.employee_id', $employeeId)
                ->groupBy('tmb.team_id', 'tmb.employee_id')
                ->get();

        if ($employeeTeams->isEmpty()) {
            return [];
        }
        $result = [];
        foreach ($employeeTeams as $empTeam) {
            $result[] = [
                'division' => $empTeam->code,
                'position' => isset($listRoles[$empTeam->role_id]) ? $listRoles[$empTeam->role_id] : null
            ];
        }
        return $result;
    }
}
