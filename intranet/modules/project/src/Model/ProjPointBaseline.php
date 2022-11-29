<?php

namespace Rikkei\Project\Model;

use Rikkei\Project\View\View;
use Exception;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\CacheHelper;
use Carbon\Carbon;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Project\Model\ProjectPoint;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Rikkei\Project\Model\TeamProject;
use DB;
use Rikkei\Sales\Model\Customer;
use Rikkei\Sales\Model\Company;
use Rikkei\Team\View\Config;
use Illuminate\Support\Facades\URL;
use Rikkei\Core\View\Form;
use Rikkei\Team\View\Permission;

class ProjPointBaseline extends ProjectPoint
{
    const KEY_CACHE = 'project_point_baseline';
    
    protected $table = 'proj_point_baselines';

    public $timestamps = true;
    /**
     * baseline all project
     * 
     * @return boolean
     */
    public static function baselineAllProject()
    {
        list ($now, $firstWeek, $lastWeek) = View::getFirstLastDayOfWeek();
        $projects = Project::select('*')
            ->where('state', Project::STATE_PROCESSING)
            ->where('status', Project::STATUS_APPROVED)
            ->get();
        if (!count($projects)) {
            return null;
        }
        foreach ($projects as $project) {
            self::baselineItem($project, $firstWeek, $lastWeek);
        }
        return true;
    }

    /**
     * baseline a project
     * 
     * @param object $project
     * @param datetime $firstWeek
     * @param datetime $lastWeek
     * @return object
     * @throws Exception
     */
    public static function baselineItem(
        $project,
        $firstWeek = null, 
        $lastWeek = null
    ) {
        if (is_numeric($project)) {
            $project = Project::find($project);
        }
        if (!$project) {
            return null;
        }
        CacheHelper::forget(Project::KEY_CACHE, $project->id);
        CacheHelper::forget(ProjectPoint::KEY_CACHE, $project->id);
        if (!$lastWeek || !$firstWeek) {
            list ($now, $firstWeek, $lastWeek) = View::getFirstLastDayOfWeek();
        }
        if (!isset($now) || !$now) {
            $now = Carbon::now();
        }
        //find baseline in week
        $baselineItem = self::where('project_id', $project->id)
            ->whereDate('created_at', '>=', $firstWeek->format('Y-m-d H:i:s'))
            ->whereDate('created_at', '<=', $lastWeek->format('Y-m-d H:i:s'))
            ->first();
        $projectPoint = ProjectPoint::findFromProject($project->id);
        if (!$baselineItem) {
            $baselineItem = new self();
            $baselineItem->setData([
                'project_id' => $project->id,
                'changed_by' => $projectPoint->changed_by,
                'position' => $projectPoint->position
            ]);
        }
        
        $dataProjectPoint = View::getProjectPointInfo($project, $projectPoint);
        // set report last
        if (!$baselineItem->first_report) {
            $reportLastAt = Carbon::parse($projectPoint->report_last_at);
            if ($firstWeek <= $reportLastAt && $reportLastAt <= $lastWeek) {
                $baselineItem->first_report = $projectPoint->report_last_at;
            }
        }
        
        /* update cost_productivity_proglang */
        $totalFlatResourceOfDev = ProjectMember::getTotalFlatResourceOfDev($project->id);
        $projectMeta = $project->getProjectMeta();
        $locCurrent = $projectMeta->lineofcode_current;
        $locBaseline = $projectMeta->lineofcode_baseline;
        $effortDevCurrent = ProjectMember::getTotalEffortMemberApproved(null, $project->id);
        $programLang = ProjectProgramLang::getProgramLangOfProject($project);
        $costProductProglang = [];
        if (count($programLang) == 1) {
            $costProductProglang[key($programLang)] = [
                'loc_current' =>  $locCurrent,
                'loc_baseline' => $locBaseline,
                'total_effort_of_dev' => $totalFlatResourceOfDev,
                'effort_dev_current' => $effortDevCurrent['effort_dev_current']
            ];
            $dataProjectPoint['cost_productivity_proglang'] = json_encode($costProductProglang);
        }
        /* end update cost_productivity_proglang */
        
        //add note columns
        $noteColumns = ProjectPoint::getAttrNote();
        foreach ($noteColumns as $col) {
            $dataProjectPoint[$col] = $projectPoint->{$col};
        }
        $baselineItem->setData($dataProjectPoint);
        if ($projectPoint->raise == ProjectPoint::RAISE_UP) {
            $baselineItem->raise = $projectPoint->raise;
            $baselineItem->raise_note = $projectPoint->raise_note;
        } else {
            $baselineItem->raise = ProjectPoint::RAISE_DOWN;
        }
        try {
            $baselineItem->save();
            return $baselineItem;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * overide save the model to the database.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = array(), $config = []) {
        try {
            CacheHelper::forget(self::KEY_CACHE, $this->project_id);
            return CoreModel::save($options);
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * get baseline item in week
     * 
     * @param Datetime $firstWeek
     * @param Datetime $lastWeek
     * @return object
     */
    public static function getItemInWeek($firstWeek, $lastWeek)
    {
        return self::whereDate('created_at', '>=', $firstWeek->format('Y-m-d H:i:s'))
            ->whereDate('created_at', '<=', $lastWeek->format('Y-m-d H:i:s'))
            ->first();
    }
    
    /**
     * get baseline item in week
     * 
     * @param Datetime $firstWeek
     * @param Datetime $lastWeek
     * @return object
     */
    public static function getItemInWeekProject($projectId, $firstWeek, $lastWeek)
    {
        return self::whereDate('created_at', '>=', $firstWeek->format('Y-m-d'))
            ->whereDate('created_at', '<=', $lastWeek->format('Y-m-d'))
            ->where('project_id', $projectId)
            ->first();
    }
    
    /**
     * get all baseline of a project
     * 
     * @param object $project
     * @param Datetime $now
     */
    public static function getAllItem($project, $now = null)
    {
        if (!$now) {
            $now = Carbon::now();
        }
        return self::where('project_id', $project->id)
            ->whereDate('created_at', '<', $now->format('Y-m-d H:i:s'))
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * get item baseline of a project have created at less time
     * 
     * @param object $project
     * @param string $time
     */
    public static function getItemCreatedAtLt($project, $time = null)
    {
        if (!$time) {
            $time = Carbon::now()->format('Y-m-d');
        } elseif (is_object($time)) {
            $time = $time->format('Y-m-d');
        }
        return self::select('created_at', 'id')
            ->where('project_id', $project->id)
            ->whereDate('created_at', '<', $time)
            ->orderBy('created_at', 'desc')
            ->limit(1)
            ->first();
    }
    
    /**
     * get item baseline of a project have created at greater time
     * 
     * @param object $project
     * @param string $time
     */
    public static function getItemCreatedAtGt($project, $time = null, $ltWeekNow = false)
    {
        $now = Carbon::now();
        if (!$time) {
            $time = $now->format('Y-m-d');
        } elseif (is_object($time)) {
            $time = $time->format('Y-m-d');
        }
        $item = self::select('created_at', 'id')
            ->where('project_id', $project->id)
            ->whereDate('created_at', '>', $time)
            ->orderBy('created_at', 'asc')
            ->limit(1);
        if ($ltWeekNow) {
            $baselineTimeCur = CoreView::getDateLastWeek($now, 6);
            $item->whereDate('created_at', '<=', $baselineTimeCur->format('Y-m-d'));
        }
        return $item->first();
    }
    
    /**
     * update point of all baseline flat
     */
    public static function updatePointBaselineAll()
    {
        $collection = self::get();
        if (!count($collection)) {
            return true;
        }
        foreach ($collection as $item) {
            self::updatePointBaselineItem($item);
        }
    }

    public static function saveComplianceBaseline($request)
    {
        $projectPoint = self::where('project_id', $request['id'])
            ->where('id', $request['viewBaselineId'])->first();
        if (isset($request['valProcCompliance'])) {
            $projectPoint->proc_compliance = $request['valProcCompliance'];
        }
        if (isset($request['valCostActual'])) {
            $projectPoint->cost_actual_effort = $request['valCostActual'];
        }
        if (isset($request['valCss'])) {
            $projectPoint->css_css = $request['valCss'];
        }
        $projectPoint->save();
        return $projectPoint;
    }
    
    /**
     * update point basline item
     */
    public static function updatePointBaselineItem($item)
    {
        $pointCurrent = $item->point_total;
        $attrsPoint = [
            'cost_plan_effort_total_point',
            'cost_effort_effectiveness_point',
            'cost_busy_rate_point',
            'cost_effort_efficiency2_point',
            'css_css_point',
            //'css_ci_point',
            'tl_schedule_point',
            'tl_deliver_point',
            'qua_defect_point',
            'qua_leakage_point',
            'proc_compliance_point',
            'proc_report_point'
        ];
        $pointCaculator = 0;
        foreach ($attrsPoint as $attr) {
            $pointCaculator += $item->{$attr};
        }
        if ($pointCaculator != $pointCurrent) {
            $item->point_total = $pointCaculator;
            $item->save();
        }
    }
    
    public static function getBaselineAll() {
        $lastMonday = date( "Y-m-d", strtotime( "last monday" ));
        $tableProjPointBaseline = self::getTableName();
        $tableProject = Project::getTableName();
        $tableEmployee = Employee::getTableName();
        $tableTeam = Team::getTableName();
        $tableMember = ProjectMember::getTableName();
        $tableTeamProject = TeamProject::getTableName();
        $tableCustomer = Customer::getTableName();
        $tableCompany = Company::getTableName();
        $tablePointFlat = ProjPointFlat::getTableName();
        $urlSubmitFilter = trim(URL::route('project::dashboard'), '/') . '/';
        
        $pager = Config::getPagerData($urlSubmitFilter, ['limit' => 50]);
        $collection = self::select($tableProjPointBaseline.'.id', $tableProjPointBaseline.'.summary',
                $tableProjPointBaseline.'.cost', $tableProjPointBaseline.'.quality',
                $tableProjPointBaseline.'.tl', $tableProjPointBaseline.'.proc',
                $tableProjPointBaseline.'.css', $tableProjPointBaseline.'.point_total', 
                $tableProjPointBaseline.'.project_evaluation',
                $tableProjPointBaseline.'.first_report',
                $tableProjPointBaseline.'.raise',
                $tableProjPointBaseline.'.position',
                $tableProjPointBaseline.'.created_at',
                $tableProject.'.end_at')
            ->where($tableProjPointBaseline.'.created_at', '<', $lastMonday)
            // ->orderBy("{$tableProjPointBaseline}.created_at", "DESC")
            ->leftJoin($tableProject, "{$tableProjPointBaseline}.project_id", '=', "{$tableProject}.id")
            ->leftJoin($tableEmployee, "{$tableEmployee}.id", '=', "{$tableProject}.manager_id")
            ->leftJoin($tableTeamProject, "{$tableTeamProject}.project_id", '=', "{$tableProject}.id")
            ->join($tableTeam, "{$tableTeam}.id", '=', "{$tableTeamProject}.team_id")
            ->groupBy("{$tableProjPointBaseline}.id")
            ->addSelect(
                DB::raw("GROUP_CONCAT( DISTINCT {$tableTeam}.name ORDER BY {$tableTeam}.name DESC SEPARATOR ', ') as name_team")
            )
            ->addSelect(
                "{$tableProject}.name as name", "{$tableProject}.state as state",
                "{$tableEmployee}.email as email", "{$tableProject}.type as type", "{$tableProject}.status as status_projs")
            ->leftJoin($tableCustomer, "{$tableCustomer}.id", '=', "{$tableProject}.cust_contact_id")
            ->leftJoin($tableCompany, "{$tableCompany}.id", '=', "{$tableCustomer}.company_id")
            ->addSelect("{$tableCustomer}.id as customer_id",
                "{$tableCustomer}.name as customer_name",
                "{$tableCustomer}.name_ja as customer_name_jp",
                "{$tableCustomer}.email as customer_email",
                "{$tableCompany}.id as company_id",
                "{$tableCompany}.company as company_name",
                "{$tableCompany}.name_ja as company_name_ja");
                
        if (Team::isUseSoftDelete()) {
            $collection = $collection->whereNull("{$tableTeam}.deleted_at");
        }
        // filter team
        $teamFilter = Form::getFilterData('exception', 'team_id', $urlSubmitFilter);
        if ($teamFilter) {
            $teamFilter = (int) $teamFilter;
            $arrayTeamFilter = [$teamFilter];
            $teamPath = Team::getTeamPath();
            if (isset($teamPath[$teamFilter]) && 
                isset($teamPath[$teamFilter]['child'])
            ) {
                $arrayTeamFilter = array_merge($arrayTeamFilter, 
                        (array) $teamPath[$teamFilter]['child']);
            }
            $collection->whereIn("{$tableProject}.id", function ($query) 
                use (
                    $tableProject,
                    $tableTeamProject,
                    $arrayTeamFilter
            ){
                $query->select("{$tableProject}.id")
                    ->from($tableProject)
                    ->join($tableTeamProject, "{$tableTeamProject}.project_id", '=', "{$tableProject}.id")
                    ->whereIn('team_id', $arrayTeamFilter);
            });
        }
        // check permission
        if (Permission::getInstance()->isScopeCompany(null, 'project::baseline.all')) {
        } elseif (Permission::getInstance()->isScopeTeam(null, 'project::baseline.all')) {
            $collection->join($tableMember, "{$tableMember}.project_id", '=',
            "{$tableProject}.id");
            $collection->where(function($query) use ($tableTeamProject, $tableMember) {
                $teams = Permission::getInstance()->getTeams();
                if ($teams) {
                    $query->orWhereIn("{$tableTeamProject}.team_id", $teams);
                    $query->orWhere(function($query) use ($tableMember) {
                        $query->where("{$tableMember}.employee_id", Permission::getInstance()->getEmployee()->id)
                            ->where("{$tableMember}.status", '=', ProjectMember::STATUS_APPROVED);
                    });
                }
            });
        } elseif (Permission::getInstance()->isScopeSelf(null, 'project::baseline.all')) { //view self project 
            $tableSaleProject = SaleProject::getTableName();
            $tableAssignee = TaskAssign::getTableName();
            $tableTask = Task::getTableName();
            
            $collection->join($tableMember, "{$tableMember}.project_id", '=',"{$tableProject}.id")
                ->leftJoin($tableSaleProject, "{$tableSaleProject}.project_id", '=', "{$tableProject}.id")
                ->leftJoin($tableTask,  "{$tableTask}.project_id", '=', "{$tableProject}.id")
                ->leftJoin($tableAssignee,  "{$tableAssignee}.task_id", '=', "{$tableTask}.id")
                ->where(function ($query) use (
                    $tableProject, $tableMember, $tableSaleProject, $tableAssignee
            ) {
                $userCurrent = Permission::getInstance()->getEmployee();
                $query->orWhere(function($query) use (
                        $tableMember, $tableSaleProject, $userCurrent, $tableAssignee
                ) { // or member
                    $query->orwhere(function($query) use ($userCurrent, $tableMember){
                        $query->where("{$tableMember}.employee_id", $userCurrent->id)
                              ->where("{$tableMember}.status", '=', ProjectMember::STATUS_APPROVED);
                    })
                    // or where sale
                    ->orwhere("{$tableSaleProject}.employee_id", $userCurrent->id)
                    // or where assign
                    ->orwhere("{$tableAssignee}.employee_id", $userCurrent->id);
                });
            });
        }

        if (Form::getFilterPagerData('order', $urlSubmitFilter)) {
            $collection->orderBy($pager['order'], $pager['dir']);
        }
        self::filterGrid($collection, ['exception'], $urlSubmitFilter);
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
                
        return $collection;
    }
    
    public static function getBaseLineByProject($projId, $month, $year)
    {
        return self::where('project_id', $projId)
                ->where(DB::raw('month(updated_at)'), $month)
                ->where(DB::raw('year(updated_at)'), $year)
                ->orderBy('updated_at', 'desc')
                ->first();
    }

    /**
     * get baseline last of project
     *
     * @param int $projId
     * @return model
     */
    public static function getBlLast($projId)
    {
        return self::where('project_id', $projId)
            ->orderBy('created_at', 'desc')
            ->limit(1)
            ->first();
    }

    /*
     * get baseline where css_css not null
     */
    public static function getBaseLineNotNullCss($projId, $month, $year)
    {
        return self::select('css_css', 'cost_approved_production')
                ->where('project_id', $projId)
                ->where(DB::raw('MONTH(updated_at)'), $month)
                ->where(DB::raw('YEAR(updated_at)'), $year)
                ->whereNotNull('css_css')
                ->orderBy('updated_at', 'desc')
                ->first();
    }
    
}
