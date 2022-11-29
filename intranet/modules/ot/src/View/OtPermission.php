<?php
namespace Rikkei\Ot\View;

use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\Employee;
use Rikkei\Project\Model\Project;
use Rikkei\Team\Model\Team;
use DB;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Ot\Model\OtEmployee;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\Ot\Model\OtRelater;

class OtPermission 
{   
    /**
     * check if current user has authority over a team for manage time
     * @param int $teamId id of team to check
     * @return boolean
     */
    public static function isScopeManageOfTeam($teamId = null)
    {
        return Permission::getInstance()->isScopeTeam($teamId, 'manage_time::manage-time.manage.view');
    }

    /**
     * check if current user has authority over the company for manage time
     * @return boolean
     */
    public static function isScopeManageOfCompany()
    {
        return Permission::getInstance()->isScopeCompany(null, 'manage_time::manage-time.manage.view');
    }

    /**
     * [isScopeApproveOfTeam: is scope of self to approve register]
     * @param  [int]  $teamId
     * @return boolean
     */
    public static function isScopeApproveOfSelf($teamId = null)
    {
        return Permission::getInstance()->isScopeSelf($teamId, 'manage_time::manage-time.manage.ot.approve');
    }

    /**
     * check if current user has authority over a team for approve
     * @param int $teamId id of team to check
     * @return boolean
     */
    public static function isScopeApproveOfTeam($teamId = null)
    {
        return Permission::getInstance()->isScopeTeam($teamId, 'manage_time::manage-time.manage.ot.approve');
    }

    /**
     * check if current user has authority over the company for approve
     * @return boolean
     */
    public static function isScopeApproveOfCompany()
    {
        return Permission::getInstance()->isScopeCompany(null, 'manage_time::manage-time.manage.ot.approve');
    }
    
    /**
     * check if user can view current register
     * @param OtRegister $register register
     * @param int $employeeId user id
     * @return boolean
     */
    public static function isAllowView($register, $employeeId)
    {
        // check if user has company permission
        if (self::isScopeManageOfCompany() || self::isScopeApproveOfCompany() || self::allowCreateEditOther()) {
            return true;
        }    
        
        //check if user is the register or the approver
        if ($employeeId == $register->employee_id || $employeeId == $register->approver) {
            return true;
        }

        //check if user has team permission
        if (self::isScopeManageOfTeam() || self::isScopeApproveOfTeam()) {
           $listIdEmployeeRegister = OtEmployee::getListIdOtEmloyees($register->id);
           $listTeamOfEmployee = TeamMember::whereIn('employee_id', $listIdEmployeeRegister)->select('team_id')->lists('team_id')->toArray();
           $listTeamIdIsLeader = Team::getIdsTeam($employeeId);
           $listIdSameTeam = array_intersect($listTeamIdIsLeader, $listTeamOfEmployee);
           foreach ($listIdSameTeam as $team_id) {
               if (self::isScopeApproveOfTeam($team_id)) {
                  return true;
               };
           }
        }
        
        //check if user is a tagged employee in the registration
        $tagEmployees = OtEmployee::getOTEmployees($register->id);        
        foreach ($tagEmployees as $emp) {
            if ($emp->employee_id == $employeeId) {
                return true;
            }
        }      

        $relatedPersons = OtRelater::getRelatedPersons($register->id);
        if (count($relatedPersons)) {
            foreach ($relatedPersons as $item) {
                if ($item->relater_id == $employeeId) {
                    return true;
                }
            }
        }

        return false;
    }
    
    /**
     * check if user can approve current register
     * @param OtRegister $register register
     * @param int $employeeId user id
     * @return boolean
     */
    public static function isAllowApprove($register, $employeeId)
    {
        // check if user has team or company permission
        if (self::isScopeApproveOfCompany() || (self::isScopeApproveOfSelf() && $register->approver_id == $employeeId)) {
            return true;
        }
        
        //check if user has team permission
        if (self::isScopeApproveOfTeam()) {
            if ($register->approver_id == $employeeId) {
                return true;
            }
            $userTeamIds = ManageTimeCommon::getArrTeamIdByEmployee($employeeId);
            $registerTeams = TeamMember::select('team_id')->where('employee_id', $register->employee_id)->get();
            foreach($registerTeams as $team) {
                if (in_array($team->team_id, $userTeamIds)) {
                    return true;
                }
            }
        }
    }
    
    /**
     * get list of project leaders
     * @param int $emp_id
     * @return array
     */
    public static function getProjectLeaders ($emp_id = null)
    {
        $projsLeaders = DB::table('project_members as member')
                      ->leftJoin('projs', 'projs.id', '=', 'member.project_id')
                      ->leftJoin('employees', 'employees.id', '=', 'projs.leader_id')
                      ->where('member.status', '=', Project::STATE_PROCESSING)
                      ->whereNull('member.deleted_at');
        
        if (!$emp_id) {
            $projsLeaders = $projsLeaders->select('projs.leader_id', 'employees.name as empName');
        } else {
            $projsLeaders = $projsLeaders->select('projs.leader_id', 'employees.name as empName')
                    ->where('member.employee_id', '=', $emp_id)            
                    ->where('projs.leader_id', '!=', $emp_id);
        }
        
        return $projsLeaders->orderBy('empName')->distinct()->get();
    }
    
    /**
     * get list of teams id current user have authority over
     * @param int $employeeId user's id
     * @param string $route route's name
     * @return array $teamIds
     */
    public static function getTeamIdIsScopeTeam($employeeId, $route)
    {
        $teams = self::getTeamsOfEmployee($employeeId);

        $teamIds = [];

        if ($teams) {
            foreach ($teams as $team) {
                if (Permission::getInstance()->isScopeTeam($team->id, $route)) {
                    $teamIds[] = $team->id;
                }
            }
        }

        return $teamIds;
    }
    
    /**
     * get list of teams current user is in
     * @param type $employeeId
     * @return type
     */
    public static function getTeamsOfEmployee($employeeId)
    {
        $teamTable = Team::getTableName();
        $teamTableAs = 'team_table';
        $employeeTable = Employee::getTableName();
        $employeeTableAs = 'employee_table';
        $teamMemberTable = TeamMember::getTableName();
        $teamMemberTableAs = $teamMemberTable;

        if (empty($employeeId)) {
            return null;
        }

        $teams = TeamMember::select("{$teamTableAs}.id as id", "{$teamTableAs}.name as name")
                ->join("{$employeeTable} as {$employeeTableAs}", "{$employeeTableAs}.id", '=', "{$teamMemberTableAs}.employee_id")
                ->join("{$teamTable} as {$teamTableAs}", "{$teamTableAs}.id", '=', "{$teamMemberTableAs}.team_id")
                ->where("{$teamMemberTableAs}.employee_id", $employeeId)
                ->whereNull("{$teamTableAs}.deleted_at")
                ->get();

        return $teams;
    }
    
    /**
     * get team and its children
     * @param int $teamId parent team's id
     * @return array $descendant
     */
    public static function getTeamDescendants($teamId) 
    {
        $teamChildren = Team::select('id', 'parent_id')
            ->where('parent_id', $teamId)
            ->get();       
        $descendant = array();       
        
        if (count($teamChildren) > 0) {
            # It has children, let's get them.
            foreach ($teamChildren as $child) {                
                # Add the child to the list of children, and get its subchildren
                $descendant[$child['id']] = self::getTeamDescendants($child['id']);               
            }
        }
        
        return $descendant;
    }
    
    /**
     * return key array of input array
     * @param array input array
     * @return array of keys
     */
    static function getKeyArray(array $array)
    {
        $keys = array();

        foreach ($array as $key => $value) {
            $keys[] = $key;

            if (is_array($value)) {
                $keys = array_merge($keys, self::getKeyArray($value));
            }
        }

        return $keys;
    }
    
    /**
     * get list of team BOD member
     * @return collection
     */
    public static function getBODmember()
    {
        $employeeTbl = Employee::getTableName();
        $teamMemberTbl = TeamMember::getTableName();
        
        return TeamMember::select("{$teamMemberTbl}.employee_id as app_id", "{$employeeTbl}.name as app_name")
                ->leftJoin("{$employeeTbl}", "{$employeeTbl}.id", "=", "{$teamMemberTbl}.employee_id")
                ->where("{$teamMemberTbl}.team_id", 1)->get();
    }

    /**
     * Check permission create or edit for other employee
     * @return [boolean]
     */
    public static function allowCreateEditOther()
    {
        return Permission::getInstance()->isAllow('manage_time::manage.create.other');
    }

    /**
     * check permission view report register ot
     * @return boolean
     */
    public static function isAllowReportOt()
    {
        return Permission::getInstance()->isAllow('ot::ot.manage.report_manage_ot');
    }
}
