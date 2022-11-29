<?php
/**
 * Created by PhpStorm.
 * User: quanhv
 * Date: 17/01/20
 * Time: 15:42
 */

namespace Rikkei\Education\Http\Services;

use Carbon\Carbon;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\Form;
use Rikkei\Ot\Model\OtRegister;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Team\View\Config;
use Rikkei\Project\Model\Project;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Core\Model\User;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Permission as PermissionModel;
use Rikkei\Team\Model\Action;
use Rikkei\Team\Model\EmployeeRole;
use Rikkei\Team\View\TeamConst;
use Rikkei\Project\Model\TeamProject;
use Rikkei\Ot\View\OtPermission;
use Rikkei\ManageTime\Model\ManageTimeComment;
use Rikkei\ManageTime\View\ManageTimeConst;
use Rikkei\Project\Model\ProjectWOBase;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\Ot\Model\OtEmployee;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\PqaResponsibleTeam;
use Rikkei\Core\View\View;
use DB;
use Rikkei\Core\Model\CoreConfigData;

class EmployeeOtService
{

    // OT no salary
    const OT_NO_SALARY = 0;
    // OT have salary
    const OT_HAVE_SALARY = 1;
    // All category OT
    const ALL_OT = 2;

    // Member is onsite japan OT
    const OT_ONSITE_JAPAN = 1;

    // Start OT approved
    const OT_APPROVED = 4;

    // Coefficient
    const COEFFICIENT_OT_IN_WEEK = 1.5;
    const COEFFICIENT_OT_END_WEEK = 2;
    const COEFFICIENT_OT_HOLIDAY = 3;

    const REMOVE = 1;
    const REJECT = 2;
    const WAIT = 3;
    const DONE = 4;

    const REGISTER = 1;
    const APPROVER = 2;
    const RELATETO = 3;

    const IS_ONSITE = 1;
    const IS_NOT_ONSITE = 0;

    public static function getAllProject()
    {
        $tem = new Project();


        return $tem->teamProject()->get();
    }

    public function getOtCategories()
    {
        return [
            self::ALL_OT => 'All',
            self::OT_HAVE_SALARY => 'OT have salary',
            self::OT_NO_SALARY => 'OT no salary',
        ];
    }

    public function getList($urlFilter = null, $projects = null)
    {

        $projectIds = [];
        if (!is_null($projects)){
            $projectIds = $projects->pluck('id')->toArray();
        }

        // index weekday (from Monday to Friday)
        $indexDayWorking = [0, 1, 2, 3, 4];
        $indexDayWorking = implode(',', $indexDayWorking);
        // index weekday (Saturday and Sunday)
        $indexEndDayOfWeek = [5, 6];
        $indexEndDayOfWeek = implode(',', $indexEndDayOfWeek);


        $registerTable = OtRegister::getTableName();
        $tblOtEmployee = OtEmployee::getTableName();
        $employeeTable = Employee::getTableName();
        $projectTable = Project::getTableName();
        $teamTable = Team::getTableName();
        $employeeTeamHistory = EmployeeTeamHistory::getTableName();

        $annualHolidays = CoreConfigData::getAnnualHolidays(2);
        $annualHolidays = join(",", $annualHolidays);
        $annualHolidays = str_replace(',', "','", $annualHolidays);
        $dataFilter = Form::getFilterData('except', null, $urlFilter);
        $startDate = isset($dataFilter) && isset($dataFilter['date_from']) ? $dataFilter['date_from'] : '';
        $endDate = isset($dataFilter) && isset($dataFilter['date_to']) ? $dataFilter['date_to'] : '';

        $employeeCode = Form::getFilterData('except', 'employee_code', $urlFilter);
        $categoryIdFilter = Form::getFilterData('except', 'category_id', $urlFilter);
        $projectName = Form::getFilterData('except', 'project', $urlFilter);
        $teamId = Form::getFilterData('except', 'team_id', $urlFilter);
        if (is_null($teamId)){
            $teamId = Permission::getInstance()->isScopeTeam();
        }
        $categoryId = null;
        if (!is_null($categoryIdFilter) && $categoryIdFilter != self::ALL_OT) {
            $categoryId = $categoryIdFilter;
        }

        $employeeIdsFilter = Form::getFilterData('except', 'sale_id', $urlFilter);
        if (is_null($dataFilter)) {
            $startDate = date("Y-m-01", strtotime(Carbon::now()));
        }
        if (is_null($dataFilter)) {
            $endDate = date("Y-m-t", strtotime(Carbon::now()));
        }

        $specialHolidaysJapan = $this->getSpecialHistory('japan');
        $specialHolidaysHaNoi = $this->getSpecialHistory('hanoi');
        $specialHolidaysHCM = $this->getSpecialHistory('hcm');
        $specialHolidaysDaNang = $this->getSpecialHistory('danang');

        $collection = DB::table($employeeTable)->select(
            DB::raw("{$employeeTable}.id AS employee_id"),
            DB::raw("{$employeeTable}.employee_code AS employee_code"),
            DB::raw("{$employeeTable}.name AS employee_name"),
            // DB::raw("{$teamTable}.name AS team_name"),
            // DB::raw("{$teamTable}.id AS team_id"),
            DB::raw("GROUP_CONCAT(DISTINCT ({$teamTable}.name) SEPARATOR ', ') AS teams"),
            DB::raw("Count(DISTINCT({$employeeTeamHistory}.id)) as team_count"),
            DB::raw("{$projectTable}.id AS proj_id"),
            DB::raw("{$projectTable}.name AS project_name"),
            DB::raw("SUM(
                        CASE WHEN WEEKDAY({$tblOtEmployee}.start_at) in ({$indexDayWorking})
                            AND DATE_FORMAT({$tblOtEmployee}.start_at, '%m-%d') NOT IN ('{$annualHolidays}')
                            AND (CASE
                                    WHEN {$teamTable}.branch_code = 'hanoi' THEN DATE_FORMAT({$tblOtEmployee}.start_at, '%Y-%m-%d') NOT IN ({$specialHolidaysHaNoi})
                                    WHEN {$teamTable}.branch_code = 'danang' THEN DATE_FORMAT({$tblOtEmployee}.start_at, '%Y-%m-%d') NOT IN ({$specialHolidaysDaNang})
                                    WHEN {$teamTable}.branch_code = 'hcm' THEN DATE_FORMAT({$tblOtEmployee}.start_at, '%Y-%m-%d') NOT IN ({$specialHolidaysHCM})
                                    WHEN {$teamTable}.branch_code = 'japan' THEN DATE_FORMAT({$tblOtEmployee}.start_at, '%Y-%m-%d') NOT IN ({$specialHolidaysJapan})
                                END )
                            THEN
                                (TIMESTAMPDIFF(MINUTE, {$tblOtEmployee}.start_at, {$tblOtEmployee}.end_at) / 60 - {$tblOtEmployee}.time_break)
                            ELSE 0
                        END ) AS 'ot_in_week'"),

            DB::raw("SUM(
                        CASE WHEN WEEKDAY({$tblOtEmployee}.start_at) in ({$indexEndDayOfWeek})
                            AND DATE_FORMAT({$tblOtEmployee}.start_at, '%m-%d') NOT IN ('{$annualHolidays}')
                            AND (CASE
                                    WHEN {$teamTable}.branch_code = 'hanoi' THEN DATE_FORMAT({$tblOtEmployee}.start_at, '%Y-%m-%d') NOT IN ({$specialHolidaysHaNoi})
                                    WHEN {$teamTable}.branch_code = 'danang' THEN DATE_FORMAT({$tblOtEmployee}.start_at, '%Y-%m-%d') NOT IN ({$specialHolidaysDaNang})
                                    WHEN {$teamTable}.branch_code = 'hcm' THEN DATE_FORMAT({$tblOtEmployee}.start_at, '%Y-%m-%d') NOT IN ({$specialHolidaysHCM})
                                    WHEN {$teamTable}.branch_code = 'japan' THEN DATE_FORMAT({$tblOtEmployee}.start_at, '%Y-%m-%d') NOT IN ({$specialHolidaysJapan})
                                END )
                            THEN
                                (TIMESTAMPDIFF(MINUTE, {$tblOtEmployee}.start_at, {$tblOtEmployee}.end_at) / 60 - {$tblOtEmployee}.time_break)
                            ELSE 0
                        END ) AS 'ot_end_week'"),

            DB::raw("SUM(
                        CASE WHEN DATE_FORMAT({$tblOtEmployee}.start_at, '%m-%d') IN ('{$annualHolidays}')
                            OR (CASE
                                    WHEN {$teamTable}.branch_code = 'hanoi' THEN DATE_FORMAT({$tblOtEmployee}.start_at, '%Y-%m-%d') IN ({$specialHolidaysHaNoi})
                                    WHEN {$teamTable}.branch_code = 'danang' THEN DATE_FORMAT({$tblOtEmployee}.start_at, '%Y-%m-%d') IN ({$specialHolidaysDaNang})
                                    WHEN {$teamTable}.branch_code = 'hcm' THEN DATE_FORMAT({$tblOtEmployee}.start_at, '%Y-%m-%d') IN ({$specialHolidaysHCM})
                                    WHEN {$teamTable}.branch_code = 'japan' THEN DATE_FORMAT({$tblOtEmployee}.start_at, '%Y-%m-%d') IN ({$specialHolidaysJapan})
                                END )
                            THEN
                                (TIMESTAMPDIFF(MINUTE, {$tblOtEmployee}.start_at, {$tblOtEmployee}.end_at) / 60 - {$tblOtEmployee}.time_break)
                            ELSE 0
                        END ) AS 'ot_holidays_week'")
        )->join("{$employeeTeamHistory}", "{$employeeTable}.id", '=', "{$employeeTeamHistory}.employee_id")
            ->join("{$teamTable}", "{$teamTable}.id", '=', "{$employeeTeamHistory}.team_id")
            ->join("{$tblOtEmployee}", "{$employeeTable}.id", '=', "{$tblOtEmployee}.employee_id")
            ->join("{$registerTable}", "{$registerTable}.id", '=', "{$tblOtEmployee}.ot_register_id")
            ->leftJoin("{$projectTable}", "{$projectTable}.id", '=', "{$registerTable}.projs_id")
            ->whereRaw("( DATE_FORMAT({$employeeTeamHistory}.start_at, '%Y-%m-%d') <= '{$endDate}' OR {$employeeTeamHistory}.start_at IS NULL )")
            ->whereRaw("( DATE_FORMAT({$employeeTeamHistory}.end_at, '%Y-%m-%d') >= '{$startDate}' OR {$employeeTeamHistory}.end_at IS NULL )")
            ->whereRaw("( DATE_FORMAT({$tblOtEmployee}.start_at, '%Y-%m-%d') <= '{$endDate}')")
            ->whereRaw("( DATE_FORMAT({$tblOtEmployee}.end_at, '%Y-%m-%d') >= '{$startDate}')")
            ->whereRaw("( {$employeeTeamHistory}.start_at <= {$tblOtEmployee}.start_at OR {$employeeTeamHistory}.start_at IS NULL )")
            ->whereRaw("( {$employeeTeamHistory}.end_at >= {$tblOtEmployee}.start_at OR {$employeeTeamHistory}.end_at IS NULL )")
            ->whereNull("{$employeeTeamHistory}.deleted_at")
            ->whereNull("{$registerTable}.deleted_at")
            ->whereNull("{$projectTable}.deleted_at")
            ->whereNull("{$tblOtEmployee}.deleted_at")
            ->where("{$registerTable}.status", OtRegister::DONE)
            ->groupBy("{$employeeTable}.id", "{$projectTable}.id");
        // Search by employee code
        if (!is_null($employeeCode)) {
            $collection = $collection->where("{$employeeTable}.employee_code", '=', $employeeCode);
        }

        // Search by category
        if (!is_null($categoryId)) {
            $collection = $collection->where("{$tblOtEmployee}.is_paid", '=', $categoryId);
        }

        // Search by employee id
        if (!is_null($employeeIdsFilter)) {
            $collection = $collection->where("{$employeeTable}.id", $employeeIdsFilter);
        }

        // Search by project
        if (!is_null($projectName)) {
            $collection = $collection->where("{$projectTable}.id", '=', "{$projectName}");
        }
        // Search by team
        if (is_array($teamId)) {
            $collection = $collection->whereIn("{$teamTable}.id", $teamId);
        } elseif (is_numeric($teamId)){
            $collection = $collection->where("{$teamTable}.id", '=', $teamId);
        }

        if (!is_null($projects)) {
            $collection = $collection->whereIn("{$projectTable}.id", $projectIds);
        }

        return $collection;
    }

    public function filterList($collection)
    {
        $pager = Config::getPagerData(null, ['order' => 'employee_id', 'dir' => 'asc']);
        $collection = $collection->orderBy($pager['order'], $pager['dir']);
        CoreModel::filterGrid($collection, [], null, 'LIKE');
        CoreModel::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;
    }

    public function filterListAll($collection)
    {
        $dataPage = [
            'order' => 'employee_id',
            'dir' => 'asc'
        ];
        $pager = Config::getPagerData(null, $dataPage);
        $collection = $collection->orderBy($pager['order'], $pager['dir']);
        CoreModel::filterGrid($collection, [], null, 'LIKE');
        $collection = $collection->get();

        return $collection;
    }

    private function getSpecialHistory($location)
    {
        $specialHoliday = CoreConfigData::getSpecialHolidays(2, $location);
        $specialHoliday = join(",", $specialHoliday);
        $specialHoliday = "'" . str_replace(',', "','", $specialHoliday) . "'";
        return $specialHoliday;
    }

    public function getEmployeeIdFromTeamId()
    {
        $teamIds = Permission::getInstance()->getTeams();
        $teamArr = Team::select(['id', 'name', 'parent_id', 'leader_id'])->get()->toArray();
        $childTeamIds = [];
        foreach ($teamIds as $teamId) {
            $recursiveArr = self::getTeamIdRecursive($teamArr, $teamId);
            $childTeamIds = array_merge($childTeamIds, self::getKeyArray($recursiveArr));
        }
        $teamIds = array_merge($childTeamIds, $teamIds);

        return array_unique($teamIds);
    }

    public function getTeamIdRecursive(array $elements, $parentId = null)
    {
        $ids = [];
        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = $this->getTeamIdRecursive($elements, $element['id']);
                $ids[$element['id']] = $element['id'];
                if ($children) {
                    $ids[$element['id']] = $children;
                }
            }
        }

        return $ids;
    }

    public function getKeyArray(array $recursiveArr)
    {
        $keys = array();

        foreach ($recursiveArr as $key => $value) {
            $keys[] = $key;

            if (is_array($value)) {
                $keys = array_merge($keys, self::getKeyArray($value));
            }
        }

        return $keys;
    }

    public function getData($teamIds = null)
    {
        $employeeTeamHistoryTable = EmployeeTeamHistory::getTableName();

        $employeeIds = DB::Table("{$employeeTeamHistoryTable}");
        if (!is_null($teamIds)) {
            $employeeIds = $employeeIds->whereIn('team_id', $teamIds);
        }

        $employeeIds = $employeeIds->where('deleted_at', null)
            ->where(function ($query) {
                $query->where('start_at', null)->orWhere('end_at', null);
            })->pluck('employee_id');
        return array_unique($employeeIds);
    }

    public function getListEmployee()
    {
        $employeeTable = Employee::getTableName();
        $collection = DB::table("{$employeeTable}")
            ->select("{$employeeTable}.id", "{$employeeTable}.email", "{$employeeTable}.email as text", "{$employeeTable}.name");
        return $collection->get();
    }


    public static function getTeamTree($id)
    {

        if ($id === null) {
            $id = 0;
        }

        $teamIdsAvailable = null;
        $teamTreeAvailable = [];
        $route = 'education::education.ot.index';
        //scope company => view all team
        if (Permission::getInstance()->isScopeCompany(null, $route)) {
            $teamIdsAvailable = true;
        } else {// permission team or self profile.
            if (Permission::getInstance()->isScopeTeam(null, $route)) {
                $teamIdsAvailable = (array)Permission::getInstance()->getTeams();
            }
            // get list team_id responsible by pqa.
            $curEmp = Permission::getInstance()->getEmployee();
            $teamIdsResonsibleByPqa = PqaResponsibleTeam::getListTeamIdResponsibleTeam($curEmp->id);
            if ($teamIdsResonsibleByPqa) {
                foreach ($teamIdsResonsibleByPqa as $teamId) {
                    $teamIdsAvailable[] = $teamId->team_id;
                }
            }
            if (!$teamIdsAvailable || !Permission::getInstance()->isAllow($route)) {
                View::viewErrorPermission();
            }
            $teamIdsAvailable = array_unique($teamIdsAvailable);
            if (!$id) {
                $id = end($teamIdsAvailable);
            }
            //get team and all child avaliable
            $teamIdsChildAvailable = [];
            if (is_array($teamIdsAvailable) && count($teamIdsAvailable)) {
                $teamPathTree = Team::getTeamPath();
                foreach ($teamIdsAvailable as $teamId) {
                    if (isset($teamPathTree[$teamId]) && $teamPathTree[$teamId]) {
                        if (isset($teamPathTree[$teamId]['child'])) {
                            $teamTreeAvailable = array_merge($teamTreeAvailable, $teamPathTree[$teamId]['child']);
                            $teamIdsChildAvailable = array_merge($teamIdsChildAvailable, $teamPathTree[$teamId]['child']);
                            unset($teamPathTree[$teamId]['child']);
                        }
                        $teamTreeAvailable = array_merge($teamTreeAvailable, $teamPathTree[$teamId]);
                    }
                    $teamTreeAvailable = array_merge($teamTreeAvailable, [$teamId]);
                }
                $teamIdsAvailable = array_merge($teamIdsAvailable, $teamIdsChildAvailable);
            }
            if (is_array($teamIdsAvailable) && count($teamIdsAvailable) == 1) {
                $teamIdsAvailable = Team::where('id', $id)->select('id')->get()->toArray();
                if (count($teamIdsAvailable) > 0){
                    $teamIdsAvailable = array_values($teamIdsAvailable[0]);
                }else{
                    View::viewErrorPermission();
                }
            }
        }

        return ['teamIdsAvailable' => $teamIdsAvailable, 'teamTreeAvailable' => $teamTreeAvailable, 'teamIdActive' => $id];
    }

    public function getProjectByRole($currentId){
        $route = 'education::education.ot.index';
        $collection = Project::whereNull('deleted_at');
        if (Permission::getInstance()->isScopeCompany(null, $route)) {

        } elseif (Permission::getInstance()->isScopeTeam(null, $route)) {
            $collection = $collection->where(function ($query) use ($currentId) {
                $query->where('manager_id', '=', $currentId)
                    ->orWhere('leader_id', '=', $currentId);
            });

        } elseif (Permission::getInstance()->isScopeSelf(null, $route)){
            $collection = $collection->Where('leader_id', '=', $currentId);
        }
        $collection = $collection->select('id', 'name')->get();
        return $collection;
    }

    public function getTeamIdsByProject($projects)
    {
        $teamTable = Team::getTableName();
        $teamMemberTable = TeamMember::getTableName();
        $projectMemberTable = ProjectMember::getTableName();
        $arrProjectIds = $projects->pluck('id');

        $collection = DB::table("{$projectMemberTable}")
                        ->join("{$teamMemberTable}", "{$teamMemberTable}.employee_id", '=', "{$projectMemberTable}.employee_id")
                        ->join("{$teamTable}", "{$teamMemberTable}.team_id", '=', "{$teamTable}.id")
                        ->select("{$teamTable}.id")
                        ->groupBy("{$teamTable}.id")
                        ->whereIn("{$projectMemberTable}.project_id",  $arrProjectIds);

        return $collection->pluck('id');
    }

}