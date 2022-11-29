<?php

namespace Rikkei\ManageTime\View;

use Rikkei\Team\View\Permission;
use Rikkei\Team\View\TeamConst;
use Rikkei\Team\Model\Team;
use Rikkei\ManageTime\View\ManageTimeCommon;

class TimekeepingPermission
{
    /**
     * [isScopeOfTeam]
     * @param  [int]  $teamId
     * @return boolean
     */
    public static function isScopeOfTeam($teamId = null)
    {
        return Permission::getInstance()->isScopeTeam($teamId, 'manage_time::manage-time.manage.timekeeping');
    }

    /**
     * [isScopeOfCompany]
     * @return boolean
     */
    public static function isScopeOfCompany()
    {
        return Permission::getInstance()->isScopeCompany(null, 'manage_time::manage-time.manage.timekeeping');
    }

    /**
     * Get all teams child of a team
     *
     * @param int $teamId
     * @param array $teamsPath  array team path
     *
     * @return array
     */
    public static function getTeamsChild($teamId, $teamsPath)
    {
        $result = [];
        if (static::isScopeOfTeam($teamId)) {
            $result[] = $teamId;
            $teamChilds = $teamsPath[$teamId]['child'];
            if (count($teamChilds)) {
                $result = array_merge($result, $teamChilds);
                foreach ($teamChilds as $childId) {
                    $result = array_merge($result, static::getTeamsChild($childId, $teamsPath));
                }
            }
        }
        return $result;
    }

    /**
     * Get list teamId allowed create or view timekeeping table
     *
     * @return array    array of team_id
     */
    public static function getTeamIdAllowCreate()
    {
        $teamIds = [];

        // Scope company
        if (self::isScopeOfCompany()) {
            return Team::lists('id')->toArray();
        }

        // Scope team
        if ($teams = static::isScopeOfTeam()) {
            $teamsPath = Team::getTeamPathTree();
            foreach ($teams as $teamId) {
                $teamIds = array_merge($teamIds, static::getTeamsChild($teamId, $teamsPath));
            }
        }
        if ($teamIds) {
            $teamIds = array_values(array_unique($teamIds));
        }

        return $teamIds;
    }

    /**
     * check permission timekeekping: permission team and permissin company
     * @param  [int|null]  $teamId
     * @return boolean
     */
    public static function isPermission($teamId = null)
    {
        if (static::isScopeOfTeam($teamId) || static::isScopeOfCompany()) {
            return true;
        }
        return false;
    }

    /**
     * [isScopeOfCompanyView]
     * @return boolean
     */
    public static function isScopeOfCompanyView()
    {
        return Permission::getInstance()->isScopeCompany(null, 'manage_time::manage-time.manage.timekeeping.view');
    }

    /**
     * [isScopeOfTeamView]
     * @param  [int]  $teamId
     * @return boolean
     */
    public static function isScopeOfTeamView($teamId = null)
    {
        return Permission::getInstance()->isScopeTeam($teamId, 'manage_time::manage-time.manage.timekeeping.view');
    }

    /**
     * check permission view timekeekping: permission team and permissin company
     * @param  [int|null]  $teamId
     * @return boolean
     */
    public static function isPermissionView($teamId = null)
    {
        if (static::isScopeOfTeamView($teamId) || static::isScopeOfCompanyView()) {
            return true;
        }
        return false;
    }

     /**
     * Get all teams child of a team not check permission
     *
     * @param int $teamId
     * @param array $teamsPath  array team path
     *
     * @return array
     */
    public static function getTeamsChildAll($teamId, $teamsPath)
    {
        $result[] = $teamId;
        $teamChilds = $teamsPath[$teamId]['child'];
        if (count($teamChilds)) {
            $result = array_merge($result, $teamChilds);
            foreach ($teamChilds as $childId) {
                $result = array_merge($result, static::getTeamsChildAll($childId, $teamsPath));
            }
        }
        return $result;
    }

    /**
     * Get list teamId allowed create or view timekeeping table
     *
     * @return array array of team_id
     */
    public static function getTeamIdAllowCreateView()
    {
        $teamIds = [];

        // Scope company
        if (self::isScopeOfCompany() || static::isScopeOfCompanyView()) {
            return Team::lists('id')->toArray();
        }

        // Scope team
        if ($teams = self::isScopeOfTeam()) {
            $teamsPath = Team::getTeamPathTree();
            foreach ($teams as $teamId) {
                $teamIds = array_merge($teamIds, static::getTeamsChildAll($teamId, $teamsPath));
            }
        }
        if (empty($teamIds) && $teamsView = static::isScopeOfTeamView()) {
            $teamsPath = Team::getTeamPathTree();
            foreach ($teamsView as $teamViewId) {
                $teamIds = array_merge($teamIds, static::getTeamsChildAll($teamViewId, $teamsPath));
            }
        }
        if ($teamIds) {
            $teamIds = array_values(array_unique($teamIds));
        }

        return $teamIds;
    }

    //======= D lead =====

    /**
     * @return array|bool
     */
    public function getTeamPermissionViewTk()
    {
        return Permission::getInstance()->isScopeTeam(null, 'manage_time::division.list-tk-aggregates');
    }

    /**
     * @return bool
     */
    public function isCompanyViewTk()
    {
        return Permission::getInstance()->isScopeCompany(null, 'manage_time::division.list-tk-aggregates');
    }

    /**
     * @return array|bool
     */
    public function getTeamViewTk()
    {
        // Scope company
        if ($this->isCompanyViewTk()) {
            return Team::lists('id')->toArray();
        }

        // Scope team
        if ($teamIds = $this->getTeamPermissionViewTk()) {
            $teamIds = is_array($teamIds) ? $teamIds : [];
        }
        return $teamIds;
    }
}
