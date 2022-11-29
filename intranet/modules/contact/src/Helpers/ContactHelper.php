<?php
namespace Rikkei\Contact\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\View\CacheBase;
use Rikkei\Team\Model\EmployeeContact;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Permission;
use Illuminate\Support\Facades\Lang;

class ContactHelper
{
    private static $instance;

    /**
     * get contact info
     *
     * @param array $params
     * @return array {
     *      status, per_page, current_page, data, is_next_page, no_avatar, team, role
     * }
     */
    public function getContact(array $params = [])
    {
        $response = $this->getList($params);
        if (!Permission::getInstance()->isAllow('contact::get.list')) {
            foreach ($response['data'] as $item) {
                if ($item->can_show_birthday === (string)EmployeeContact::NOT_SHOW_BIRTHDAY) {
                    $item->birthday = null;
                } elseif ($item->can_show_birthday === (string)EmployeeContact::SHOW_ONLY_YEAR && $item->birthday) {
                    $item->birthday = Carbon::parse($item->birthday)->format('Y');
                } else {
                    //nothing
                }
                if ($item->can_show_phone === (string)EmployeeContact::NOT_SHOW_PHONE) {
                    $item->mobile_phone = null;
                }
            }
        }
        if (isset($params['isExistsTeam']) && $params['isExistsTeam']) {
            return $response;
        }
        $teamRoles = $this->getTeamAndRole();
        $response['no_avatar'] = asset('common/images/noavatar.png');
        return array_merge($response, $teamRoles);
    }

    /**
     * get list contact
     *
     * @param array $params
     * @return type
     */
    protected function getList(array $params = [])
    {
        $route = route('team::member.profile.index');
        if (!isset($params['s']) || strlen($params['s']) < 3) {
            return [];
        }
        $collection = DB::table('employees as t_e')
            ->select(['t_e.id', 't_e.email', 't_e.name', 't_e.employee_code', 't_e.birthday',
                't_ew.bank_account', 't_ew.bank_name', DB::raw("
                        (CASE "
                            . " WHEN contract_type = 1 THEN '" . Lang::get("team::profile.Contract probationary")
                            . "' WHEN contract_type = 2 THEN '" . Lang::get("team::profile.Contract apprenticeship")
                            . "' WHEN contract_type = 3 THEN '" . Lang::get("team::profile.Contract seasonal")
                            . "' WHEN contract_type = 4 THEN '" . Lang::get("team::profile.Contract limit time")
                            . "' WHEN contract_type = 5 THEN '" . Lang::get("team::profile.Contract unlimit time")
                            . "' WHEN contract_type = 6 THEN '" . Lang::get("team::profile.Contract borrow")
                            . "' ELSE 'N/A' "
                        . "END) AS contract"),
                't_ec.skype', 't_ec.mobile_phone', 't_ec.can_show_phone', 't_ec.can_show_birthday',
                't_u.avatar_url', 'trial_date', 'offcial_date'
            ])
            ->leftJoin('team_members as t_tm', 't_tm.employee_id', '=', 't_e.id')
            ->leftJoin('employee_works as t_ew', 't_ew.employee_id', '=', 't_e.id')
            ->leftJoin('employee_contact as t_ec', 't_ec.employee_id', '=', 't_e.id')
            ->leftJoin('users as t_u', 't_u.employee_id', '=', 't_e.id')
            ->whereNull('t_e.deleted_at')
            ->where(function($query) {
                $query->orWhereNull('t_e.leave_date')
                    ->orWhereDate('t_e.leave_date', '>=', Carbon::now()->format('Y-m-d'));
            })
            ->groupBy('t_e.id')
            ->orderBy('t_e.email', 'asc');
        $collection->addSelect(DB::raw('group_concat(DISTINCT CONCAT(t_tm.team_id,"-",t_tm.role_id) SEPARATOR ";") AS team'));
        $collection->addSelect(DB::raw('group_concat(DISTINCT CONCAT("' . $route . '/",t_u.employee_id)) AS profile_url'));
        $collection->where(function ($query) use ($params) {
            $query->orWhere('t_e.name', 'like', '%'.$params['s'].'%')
                ->orWhere('t_e.email', 'like', $params['s'].'%');
        });
        return $this->paginResponse($collection, $params);
    }

    /**
     * get team and position
     *
     * @return type
     */
    public function getTeamAndRole()
    {
        if ($result = CacheBase::getFile(CacheBase::GENERAL, 'team_pos')) {
            return $result;
        }
        $teams = DB::table('teams')
            ->select(['id', 'name'])
            ->whereNull('deleted_at')
            ->get();
        $roles = DB::table('roles')
            ->select(['id', 'role'])
            ->where('special_flg', 1)
            ->whereNull('deleted_at')
            ->get();
        $result = [
            'team' => $this->renderCollectionKey($teams),
            'role' => $this->renderCollectionKey($roles),
        ];
        CacheBase::putFile(CacheBase::GENERAL, 'team_pos', $result);
        return $result;
    }

    /**
     * render fomat by key
     *
     * @param collection|array $collection
     * @param string $key
     * @return array
     */
    public function renderCollectionKey($collection, $key = 'id')
    {
        $result = [];
        foreach ($collection as $item) {
            $result[$item->{$key}] = (array) $item;
        }
        return $result;
    }

    /**
     * get pagination format
     * {current_page, data, is_next_page, per_page, is_prev_page}
     *
     * @param type $collection
     * @return type
     */
    public function paginResponse($collection, array $params = [])
    {
        if (!isset($params['page'])) {
            $params['page'] = 1;
        } else {
            $params['page'] = (int) $params['page'];
        }
        $limit = 51;
        $response = [
            'status' => 1,
            'per_page' => $limit,
            'current_page' => $params['page'],
        ];
        if ($response['current_page'] > 1) {
            $response['is_prev_page'] = 1;
        }
        $response['data'] = $collection->limit($limit + 1)
            ->offset($limit * ($params['page'] - 1))
            ->get();
        if (count($response['data']) > $limit) {
            $response['is_next_page'] = 1;
            unset($response['data'][$limit]);
        }
        return $response;
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
}
