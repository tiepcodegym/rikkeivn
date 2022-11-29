<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Project\Model\Project;
use DB;
use Log;
use Rikkei\Resource\View\View as ResourceView;
use Rikkei\Team\Model\Team;
use Rikkei\Resource\Model\RecruitPlan;
use Rikkei\Project\Model\Task;
use Rikkei\Sales\Model\Css;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Project\Model\MeEvaluation;
use Rikkei\Project\Model\ProjPointBaseline;
use Rikkei\Sales\Model\CssResult;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Resource\Model\Dashboard;
use Rikkei\Team\View\Permission;
use Rikkei\Team\View\CheckpointPermission;

class MonthlyReport extends CoreModel
{
    use SoftDeletes;
    
    protected $table = 'monthly_report';
    
    const NOT_AVAILABLE = 'N/A';
    
    const IS_VALUE = 'value';
    const IS_POINT = 'point';
    
    const TYPE_BUSINESS = 1;
    const TYPE_OPERATION = 2;
    const TYPE_HR = 3;
    const TYPE_TRAINING = 4;
    
    const ROW_NA = 'na';
    const ROW_APPROVED_COST = 'approved_cost';
    const ROW_BILLABLE_EFFORT = 'billable_effort';
    const ROW_APPROVED_PROD_COST = 'totalApprovedProdCost';
    const ROW_PLAN_REVENUE = 'plan_revenue'; //Budget (Cash Man)
    const ROW_COST = 'cost';
    const ROW_PLAN = 'plan'; //Budget (MM)
    const ROW_ACTUAL = 'actual';
    const ROW_BILLACTUAL = 'billactual';
    const ROW_BILL_STAFF_PLAN = 'bill_staff_plan';
    const ROW_BILL_STAFF_ACTUAL = 'bill_staff_actual';
    const ROW_BUSINESS_EFFECTIVE = 'effective';
    const ROW_COMPLETED_PLAN = 'completed_plan';
    const ROW_BUSY_RATE = 'busy_rate';
    const ROW_ALLO_STAFF_ACTUAL = 'allocate_staff_actual';
    const ROW_PROD_STAFF_ACTUAL = 'prod_staff_actual';
    const ROW_PROJECT_POINT = 'project_point';
    const ROW_CSS_POINT = 'css_point';
    const ROW_CSS_IMPROVEMENT = 'css_improvement';
    const ROW_HR_PLAN = 'hr_plan';
    const ROW_HR_ACTUAL = 'hr_actual';
    const ROW_COMMENT = 'customer_comment';
    const ROW_HR_OUT = 'hr_out';
    const ROW_HR_COMPLETED_PLAN = 'hr_completed_plan';
    const ROW_HR_TURN_OVERATE = 'hr_turn_overate';
    const ROW_TRAINING_PLAN = 'training_plan';
    const ROW_TRAINING_ACTUAL = 'training_actual';
    const ROW_TRAINING_POINT = 'training_point';
    const ROW_LANGUAGE_INDEX = 'language_index';
    const ROW_AVG_LANGUAGE_INDEX = 'average_language_index';
    const ROW_AVG_SOCIAL_ACTIVITY = 'average_social_activity';
    const TOOLTIP_ROW_PROJECT_POINT = 'project_name_point';
    const TOOLTIP_ROW_ACTUAL = 'project_name_actual';
    const TOOLTIP_ROW_PLAN = 'project_name_plan';
    
    const MAX_POINT = 6;
    
    //Default display 6 month
    const DEFAULT_MONTH_BEFORE = 1;
    const DEFAULT_MONTH_AFTER = 2;
    
    const START_YEAR = 2017;
    
    public static function findData($year) 
    {
        return self::where('year', $year)->orderBy('id', 'desc')->first();
    }
    
    public static function getAll($order = 'desc')
    {
        return self::orderBy('year', $order)->get();
    }
    
    public static function insert($data = [])
    {
        $values = $data['values'];
        $year = $data['year'];
        DB::beginTransaction();
        try {
            $objMonthlyReport = new MonthlyReport();
            $objMonthlyReport->values = json_encode($values);
            $objMonthlyReport->year = $year;
            $objMonthlyReport->save();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex->getMessage());
        }
        
    }

    /**
     * get default filter month and year
     * @return array
     */
    public static function getDefaultTime()
    {
        $curMonth = (int)date('m');
        //Check if current month = 12, display next year
        if ($curMonth < 12) {
            $endMonth = (int)date('m') + self::DEFAULT_MONTH_AFTER;
            $startMonth = (int)date('m') - self::DEFAULT_MONTH_BEFORE;
        } else {
            $startMonth = 1;
            $endMonth = $startMonth + self::DEFAULT_MONTH_AFTER;
        }

        if ($startMonth < 1) {
            $startMonth = 1;
        }
        if ($endMonth > 12) {
            $endMonth = 12;
        }
        $year = (int)date('m') == 12 ? (int)date('Y') + 1 : (int)date('Y');
        return [$startMonth, $endMonth, $year];
    }
    
    /**
     * Get report value
     * 
     * @param Team collection $teamsChildest
     * @param int $findYear
     * @param array $monthsOfYear
     * @return array
     */
    public static function getValues($teamsChildest, $findYear, $monthsOfYear = null)
    {
        if (!$monthsOfYear) {
            $monthsOfYear = ResourceView::getMonthsInYear($findYear);
        }
        $notAvailable = MonthlyReport::NOT_AVAILABLE;
        $values = [];
        $curYear = (int)date('Y');
        $curMonth = (int)date('m');
        foreach ($monthsOfYear as $monthValue) {
            foreach ($teamsChildest as $team) {
                $values[$team->id][$monthValue[0]] = [
                    MonthlyReport::ROW_APPROVED_COST => ['value' => 0],
                    MonthlyReport::ROW_BILLABLE_EFFORT => ['value' => 0],
                    MonthlyReport::ROW_PLAN => ['value' => 0],
                    MonthlyReport::ROW_ACTUAL => ['value' => 0],
                    MonthlyReport::ROW_BUSY_RATE => ['value' => 0],
                    MonthlyReport::ROW_PROJECT_POINT => ['value' => 0],
                    MonthlyReport::ROW_CSS_POINT => ['value' => 0],
                    'current_effort' => 0,
                    'totalProjPoint' => 0,
                    'countProjsOfTeam' => 0,
                    'totalCssPoint' => 0,
                    'countCssOfTeam' => 0,
                    'cssBaseline' => 0,
                    self::ROW_APPROVED_PROD_COST => ['value' => 0],
                    'totalCssApprovedProdCost' => 0,
                    MonthlyReport::TOOLTIP_ROW_PROJECT_POINT => '', //tooltip
                    MonthlyReport::TOOLTIP_ROW_ACTUAL => '', //tooltip
                    MonthlyReport::TOOLTIP_ROW_PLAN => '', //tooltip
                ];
            }
        }
        $HrPlan = RecruitPlan::getRecruitPlanGroupTeam($findYear);
        foreach ($monthsOfYear as $monthValue) {
            $month = $monthValue[0];
            $year = $monthValue[1];
            $results = Project::getBillActualEffortInMonth($month, $year);
            $cusComment = [];
            $projProdCostList = [];
            foreach ($results as $res) {
                $baseLine = ProjPointBaseline::getBaseLineByProject($res->id, $month, $year);
                $cssBaseLine = ProjPointBaseline::getBaseLineNotNullCss($res->id, $month, $year);
                if ($baseLine) {
                    $actualEffort = $baseLine->cost_actual_effort ? (float)$baseLine->cost_actual_effort : 0;
                    $pointOfProject = $baseLine->point_total ? (float)$baseLine->point_total : 0;
                    $effortAllocationCurrent = $baseLine->cost_resource_allocation_current ? (float)$baseLine->cost_resource_allocation_current : 0;
                    //get baseline of previous month
                    $previous = ResourceView::getPreviousMonth($month, $year);
                    $baseLinePrevious = ProjPointBaseline::getBaseLineByProject($res->id, $previous['month'], $previous['year']);
                    if ($baseLinePrevious) {
                        $actualEffortPre = $baseLinePrevious->cost_actual_effort ? (float)$baseLinePrevious->cost_actual_effort : 0;
                    } else {
                        $actualEffortPre = 0;
                    }
                    //get effort in month
                    $actualEffortInMonth = $actualEffort - $actualEffortPre;
                    //get bill in month
                    $effortAllocationTotal = $baseLine->cost_resource_allocation_total ? (float)$baseLine->cost_resource_allocation_total : 0;
                    //approved production cost
                    $approvedProdCost = $baseLine->cost_approved_production;
                    //approved cost
                    $approvedCost = $baseLine->approved_cost;
                    if($res->type_mm == Project::MD_TYPE) {
                        $actualEffortInMonth = round($actualEffortInMonth/20, 2);
                        $approvedProdCost = $approvedProdCost ? round($approvedProdCost / 20, 2) : 0;
                        $approvedCost = $approvedCost ? round($approvedCost / 20, 2) : 0;
                    }
                } else {
                    $actualEffortInMonth = 0;
                    $pointOfProject = 0;
                    $approvedProdCost = 0;
                    $approvedCost = 0;
                }
                //css baseline
                $cssPoint = null;
                $factorCssProdCost = 1;
                if ($cssBaseLine) {
                    $cssPoint = $cssBaseLine->css_css;
                    if ($cssBaseLine->cost_approved_production !== null) {
                        $factorCssProdCost = $cssBaseLine->cost_approved_production;
                        if ($res->type_mm == Project::MD_TYPE) {
                            $factorCssProdCost /= 20;
                        }
                    }
                }
                $factorProdCost = $approvedProdCost ? $approvedProdCost : 1;
                $projProdCostList[$res->id] = $factorProdCost;
                
                //get team of leader
                $teamOfLeader = [];
                $teams = EmployeeTeamHistory::getTeamOfEmp($month, $year, $res->leader_id);
                
                if (!empty($teams)) {
                    foreach ($teams as $t) {
                        $teamOfLeader[] = $t->team_id;
                    }
                }
                foreach ($teamsChildest as $team) {
                    $currentEffort = $values[$team->id][$month]['current_effort'];
                    $countProjsOfTeam = $values[$team->id][$month]['countProjsOfTeam'];
                    $totalProjPoint = $values[$team->id][$month]['totalProjPoint'];
                    $countCssOfTeam = $values[$team->id][$month]['countCssOfTeam'];
                    //$totalCssPoint = $values[$team->id][$month]['totalCssPoint'];
                    //tooltip
                    $projectNamePoint = $values[$team->id][$month][MonthlyReport::TOOLTIP_ROW_PROJECT_POINT];
                    $projectNameActual = $values[$team->id][$month][MonthlyReport::TOOLTIP_ROW_ACTUAL];
                    $projectNamePlan = $values[$team->id][$month][MonthlyReport::TOOLTIP_ROW_PLAN];
                    
                    $teamId = $team->id;
                    if (in_array($teamId, $teamOfLeader)) {
                        $countProjsOfTeam += $factorProdCost;
                        //Customer comment
                        $tasksInMonth = Task::getTasksInMonth($month, $year, $res->id);
                        if (count($tasksInMonth)) {
                            if (isset($cusComment[$teamId]) && count($cusComment[$teamId])) {
                                $cusComment[$teamId] .= "<br><strong>" . $res->name. "</strong>";
                            } else {
                                $cusComment[$teamId] = "<strong>" . $res->name. "</strong>";
                            }
                            foreach ($tasksInMonth as $task) {
                                $cusComment[$teamId] .= "<br>- " . $task->content;
                            }
                        }
                        $cssComment = Css::getCssCommentByProject($res->id, $month, $year);
                        if (count($cssComment)) {
                            if (isset($cusComment[$teamId]) && count($cusComment[$teamId])) {
                                $cusComment[$teamId] .= "<br><strong>" . $res->name. "</strong>";
                            } else {
                                $cusComment[$teamId] = "<strong>" . $res->name. "</strong>";
                            }
                            foreach ($cssComment as $css) {
                                $cusComment[$teamId] .= "<br>- " . $css->content . ": " . $css->comment;
                            }
                        }

                        //actual current effort 
                        $values[$team->id][$month]['current_effort'] = $currentEffort + $actualEffortInMonth;
                        $values[$team->id][$month]['countProjsOfTeam'] = $countProjsOfTeam;
                        $values[$team->id][$month]['totalProjPoint'] = $totalProjPoint + $pointOfProject * $factorProdCost;
                        if ($cssPoint !== null) {
                            $values[$team->id][$month]['totalCssPoint'] += $cssPoint * $factorCssProdCost;
                            $values[$team->id][$month]['totalCssApprovedProdCost'] += $factorCssProdCost;
                        }
                        $values[$team->id][$month]['countCssOfTeam'] = $countCssOfTeam;
                        $values[$team->id][$month]['cssBaseline'] = static::getCssBaseline($team->id, $month, $year);
                        $values[$team->id][$month][self::ROW_APPROVED_PROD_COST]['value'] += $factorProdCost;
                        $values[$team->id][$month][self::ROW_APPROVED_COST]['value'] += $approvedCost;
                        $values[$team->id][$month][MonthlyReport::ROW_COMMENT] = isset($cusComment[$teamId]) ? $cusComment[$teamId] : '';
                        //tooltip
                        $values[$team->id][$month][MonthlyReport::TOOLTIP_ROW_PROJECT_POINT] = $projectNamePoint;
                    }
                }

            }
            //opportunity
            $opportunities = Opportunity::getBillalbeEffortInMonth($month, $year)->groupBy('team_id');

            foreach ($teamsChildest as $team) {
                //approved cost & approved production cost
                if (isset($opportunities[$team->id]) && !$opportunities[$team->id]->isEmpty()) {
                    $totalApprovedCost = 0;
                    $totalApprovedProdCost = 0;
                    $totalBillEffort = 0;
                    foreach ($opportunities[$team->id] as $opp) {
                        $totalApprovedCost += $opp->total_approved_cost;
                        $totalApprovedProdCost += $opp->total_cost_approved_production;
                        $totalBillEffort += $opp->total_billable_effort;
                    }
                    $values[$team->id][$month][MonthlyReport::ROW_APPROVED_COST]['value'] += round($totalApprovedCost, 2);
                    $values[$team->id][$month][self::ROW_APPROVED_PROD_COST]['value'] += round($totalApprovedProdCost, 2);
                    $values[$team->id][$month][MonthlyReport::ROW_BILLABLE_EFFORT]['value'] += round($totalBillEffort, 2);
                }

                //allocation current effort
                $values[$team->id][$month][MonthlyReport::ROW_ACTUAL]['value'] = Dashboard::getManMonth($month, $year, $team->id, [$team->id], false);
                
                //busy rate
                //month from 1 to 3 has not baseline
                if (in_array($month, [1, 2, 3]) && $year == 2017) {
                    $values[$team->id][$month]['busy_rate']['value'] = $notAvailable;
                } else {
                    $values[$team->id][$month]['busy_rate']['value'] = empty($values[$team->id][$month][MonthlyReport::ROW_ACTUAL]['value']) 
                        ? $notAvailable : round($values[$team->id][$month]['current_effort']/$values[$team->id][$month][MonthlyReport::ROW_ACTUAL]['value']*100, 2);
                }
                //project point
                $values[$team->id][$month][MonthlyReport::ROW_PROJECT_POINT]['value'] = empty($values[$team->id][$month]['countProjsOfTeam']) 
                        ? $notAvailable : round($values[$team->id][$month]['totalProjPoint']/$values[$team->id][$month]['countProjsOfTeam'], 2);
                //CSS point
                //$values[$team->id][$month][MonthlyReport::ROW_CSS_POINT]['value'] = static::getCssPointAverageInMonth($team->id, $month, $year, $projProdCostList);
                $values[$team->id][$month][MonthlyReport::ROW_CSS_POINT]['value'] = empty($values[$team->id][$month]['totalCssApprovedProdCost'])
                        ? $notAvailable : round($values[$team->id][$month]['totalCssPoint'] / $values[$team->id][$month]['totalCssApprovedProdCost'], 2);
                //CSS imporvement
                $values[$team->id][$month][MonthlyReport::ROW_CSS_IMPROVEMENT]['value'] = empty($values[$team->id][$month]['cssBaseline']) || !is_numeric($values[$team->id][$month][MonthlyReport::ROW_CSS_POINT]['value'])
                        ? $notAvailable : round($values[$team->id][$month][MonthlyReport::ROW_CSS_POINT]['value']/$values[$team->id][$month]['cssBaseline']*100, 2);
                //Hr plan
                if (!$HrPlan || !count($HrPlan)) {
                    $values[$team->id][$month][MonthlyReport::ROW_HR_PLAN]['value'] = 0;
                } else {
                    foreach ($HrPlan as $plan) {
                        if ($plan->month == $month && $plan->team_id == $team->id) {
                            $values[$team->id][$month][MonthlyReport::ROW_HR_PLAN]['value'] = $plan->number;
                        }
                    }
                }
                //Hr actual
                if ($year < $curYear || ($year == $curYear && $month <= $curMonth)) {
                    $values[$team->id][$month][MonthlyReport::ROW_HR_ACTUAL]['value'] = EmployeeTeamHistory::getCountEmployeeOfMonth($month, $year, $team->id);
                } else {
                    $values[$team->id][$month][MonthlyReport::ROW_HR_ACTUAL]['value'] = $notAvailable;
                }
                
                //Employee out
                $values[$team->id][$month][MonthlyReport::ROW_HR_OUT]['value'] = \Rikkei\Team\Model\Employee::getEmpOutInMonth($year, $month, $team->id);
                $values[$team->id][$month][MonthlyReport::ROW_HR_COMPLETED_PLAN]['value'] = empty($values[$team->id][$month][MonthlyReport::ROW_HR_PLAN]['value'])
                        ? $notAvailable : round($values[$team->id][$month][MonthlyReport::ROW_HR_ACTUAL]['value']/$values[$team->id][$month][MonthlyReport::ROW_HR_PLAN]['value']*100, 2);
                //HR Turn overate
                if (empty($values[$team->id][$month][MonthlyReport::ROW_HR_ACTUAL]['value'])
                        || $values[$team->id][$month][MonthlyReport::ROW_HR_ACTUAL]['value'] == $notAvailable) {
                    $values[$team->id][$month][MonthlyReport::ROW_HR_TURN_OVERATE]['value'] = $notAvailable;
                } else {
                    $values[$team->id][$month][MonthlyReport::ROW_HR_TURN_OVERATE]['value'] = round($values[$team->id][$month][MonthlyReport::ROW_HR_OUT]['value']/$values[$team->id][$month][MonthlyReport::ROW_HR_ACTUAL]['value']*100, 2);
                }
                
                //Training actual (me point)
                $mePointEffort = static::getMeEffortPoint($team->id, $month, $year);
                if (!count($mePointEffort)) {
                    $values[$team->id][$month][MonthlyReport::ROW_TRAINING_ACTUAL]['value'] = 0;
                } else {
                    $mePoint = 0;
                    foreach ($mePointEffort as $item) {
                        $mePoint += $item->point;
                    }
                    
                    $values[$team->id][$month][MonthlyReport::ROW_TRAINING_ACTUAL]['value'] = $mePoint;
                }
                //Training point
                if ($values[$team->id][$month][MonthlyReport::ROW_HR_ACTUAL]['value'] == 0 || $values[$team->id][$month][MonthlyReport::ROW_HR_ACTUAL]['value'] == $notAvailable) {
                    $values[$team->id][$month][MonthlyReport::ROW_TRAINING_POINT]['value'] = $notAvailable;
                } else {
                    $values[$team->id][$month][MonthlyReport::ROW_TRAINING_POINT]['value'] = round($values[$team->id][$month][MonthlyReport::ROW_TRAINING_ACTUAL]['value']/$values[$team->id][$month][MonthlyReport::ROW_HR_ACTUAL]['value'], 2);
                }
                //Me social activity
                $meSocialActivity = static::getMeSocialActivity($team->id, $month, $year);
                $values[$team->id][$month][MonthlyReport::ROW_AVG_SOCIAL_ACTIVITY]['value'] = $meSocialActivity;
            }
            unset($results);
        }

        return static::setPoint($values, $teamsChildest);
    }
    
    /**
     * Set new point when value change
     * 
     * @param array $values
     * @param Team collection $teamsChildest
     * @return array
     */
    public static function setPoint($values, $teamsChildest = null)
    {
        $pointArray = [];
        $temp = [];
        //array store fields set point
        $keyValues = [
            self::ROW_BILLABLE_EFFORT,
            self::ROW_APPROVED_COST,
            self::ROW_APPROVED_PROD_COST,
            self::ROW_ALLO_STAFF_ACTUAL,
            self::ROW_PROD_STAFF_ACTUAL,
            MonthlyReport::ROW_BUSINESS_EFFECTIVE,
            MonthlyReport::ROW_COMPLETED_PLAN,
            MonthlyReport::ROW_BUSY_RATE,
            MonthlyReport::ROW_CSS_IMPROVEMENT,
            MonthlyReport::ROW_HR_COMPLETED_PLAN,
            MonthlyReport::ROW_HR_TURN_OVERATE,
            MonthlyReport::ROW_TRAINING_POINT,
            MonthlyReport::ROW_AVG_LANGUAGE_INDEX,
            MonthlyReport::ROW_AVG_SOCIAL_ACTIVITY
        ];
        foreach ($values as $team => $value) {
            for ($month=1; $month<=12; $month++) {
                foreach ($keyValues as $k => $v) {
                    $pointArray[$month][$v][$team] = isset($values[$team][$month][$v]['value']) && $values[$team][$month][$v]['value'] != "N/A" ? $values[$team][$month][$v]['value'] : 0;
                    $temp[$month][$v][$team] = $pointArray[$month][$v][$team];
                    rsort($temp[$month][$v]);
                    $temp[$month][$v] = array_unique($temp[$month][$v]);
                }
            }
        }
        if (empty($teamsChildest)) {
            $teamsChildest = Team::getTeamsChildest();
        }
        $setPointMax = count($teamsChildest) - 1;
        foreach ($temp as $month => $value) {
            foreach ($keyValues as $k => $v) {
                $getPoint = $setPointMax;
                for ($stt=0; $stt<MonthlyReport::MAX_POINT; $stt++) {
                    if (isset($temp[$month][$v][$stt])) {
                        $searchArray = static::searchItemInArray($pointArray[$month][$v], $temp[$month][$v][$stt]);
                        foreach ($searchArray as $team => $search) {
                            if (!isset($values[$team][$month][$v]) || !is_array($values[$team][$month][$v])) {
                                $values[$team][$month][$v] = [];
                            }
                            if (empty($search) || !isset($values[$team][$month][$v]['value']) || empty((float)$values[$team][$month][$v]['value']) 
                                    || $values[$team][$month][$v]['value'] == MonthlyReport::NOT_AVAILABLE) {
                                $values[$team][$month][$v]['point'] = 0;
                            } else {
                                $values[$team][$month][$v]['point'] = $getPoint;
                            }
                        }
                        $getPoint--;
                    } else {
                        break;
                    }
                }
            }
        }
        return $values;
    }
    
    /**
     * Search items in array by value
     * 
     * @param array $array
     * @param int|string $value
     * @return array
     */
    public static function searchItemInArray($array, $value)
    {
        $result = [];
        foreach ($array as $key => $val) {
            if ($val == $value) {
                $result[$key] = $val;
            }
        }
        return $result;
    }
    
    /**
     * Cronjob update new data
     * 
     * @param int $year
     * @return void
     */
    public static function cronData($year = null)
    {
        //fields get from old values
        $keyValues = [
            self::ROW_APPROVED_COST,
            self::ROW_BILLABLE_EFFORT,
            self::ROW_ACTUAL,
            self::ROW_COMPLETED_PLAN,
            self::ROW_BUSY_RATE,
            self::ROW_PROJECT_POINT,
            self::ROW_CSS_POINT,
            self::ROW_CSS_IMPROVEMENT,
            self::ROW_COMMENT,
            self::ROW_HR_PLAN,
            self::ROW_HR_ACTUAL,
            self::ROW_HR_OUT,
            self::ROW_HR_COMPLETED_PLAN,
            self::ROW_HR_TURN_OVERATE,
            self::ROW_TRAINING_ACTUAL,
            self::ROW_TRAINING_POINT,
            self::ROW_AVG_SOCIAL_ACTIVITY,
            self::TOOLTIP_ROW_PROJECT_POINT => '', //tooltip
            self::TOOLTIP_ROW_ACTUAL => '', //tooltip
            self::TOOLTIP_ROW_PLAN => '', //tooltip
            self::ROW_APPROVED_PROD_COST
        ];
        $teamsChildest = Team::getTeamsChildest();
        //get Time cron
        $arrSearchMonth = static::getTimeCron();
        foreach($arrSearchMonth as $year => $months) {
            $valueDb = MonthlyReport::findData($year);
            if ($valueDb && is_array(json_decode($valueDb->values, true))) {
                $values = json_decode($valueDb->values, true);
                $newValues = static::getValues($teamsChildest,$year, $months);
                foreach ($newValues as $team =>  $value) {
                    foreach ($months as $monthValue) {
                        $month = $monthValue[0];
                        foreach ($keyValues as $k => $v) {
                            if (isset($newValues[$team][$month][$v])) {
                                $values[$team][$month][$v] = $newValues[$team][$month][$v];
                            }
                        }
                    }
                }
                $valueDb->deleted_at = Carbon::now();
                $valueDb->save();
                static::insert(['values' => $values, 'year' => $year]);
            } else {
                $newValues = static::getValues($teamsChildest, $year);
                static::insert(['values' => $newValues, 'year' => $year]);
            }
            unset($newValues);
        } 
        
    }
    
    /**
     * Get time cron
     * 
     * @return array
     */
    public static function getTimeCron()
    {
        $arrMonths = [];
        $curMonth = date('m');
        $maxloop = $curMonth == 12 ? 12 - $curMonth + 12 : 12 - $curMonth;
        for ($next=-3; $next<=$maxloop; $next++) {
            $carbon = Carbon::now();
            $curMonthInForLoop = $carbon->addMonthsNoOverflow($next)->format('m');
            $carbon = Carbon::now();
            $curYearInForLoop = $carbon->addMonthsNoOverflow($next)->format('Y');
            $arrMonths[] = [
                'month' => (int)$curMonthInForLoop,
                'year' => (int)$curYearInForLoop,
            ];
        }
        $arrSearchMonth = [];
        foreach($arrMonths as $months) {
            $arrSearchMonth[$months['year']][] = [$months['month'], $months['year']];
        }
        return $arrSearchMonth;
    }
    
    /**
     * Get point Css average in 6 month
     * January -> June || July -> December
     * 
     * @param int $teamId
     * @param int $month
     * @param int $year
     * @return float
     */
    public static function getCssBaseline($teamId,  $month, $year)
    {
        $firstMonth = $month > 6 ? 1 : 7;
        $lastMonth = $month > 6 ? 6 : 12;
        $yearBaseline = $month > 6 ? $year : $year - 1;
        $firstLastMonth = ResourceView::getInstance()->getFirstLastDaysOfMonth($firstMonth, $yearBaseline);
        $firstDay = $firstLastMonth[0];
        $firstLastMonth = ResourceView::getInstance()->getFirstLastDaysOfMonth($lastMonth, $yearBaseline);
        $lastDay = $firstLastMonth[1];
        return static::getCssPointAverage($firstDay, $lastDay, $teamId);
        
    }
    
    public static function getCssPointAverageInMonth($teamId, $month, $year, $projProdCostList = [])
    {
        $firstLastMonth = ResourceView::getInstance()->getFirstLastDaysOfMonth($month, $year);
        $firstDay = $firstLastMonth[0];
        $lastDay = $firstLastMonth[1];
        $sql = "select `employee_id` from `employee_team_history` where (DATE(start_at) <= DATE('$lastDay') or start_at is null) and (DATE(end_at) >= DATE('$firstDay') or end_at is null) and `team_id` = {$teamId}";
        $cssResultInMonth = CssResult::join('css', 'css.id', '=', 'css_result.css_id')
                ->join(DB::raw("(select max(id) as last_id from css_result where DATE(updated_at) <= DATE('$lastDay') and DATE(updated_at) >= DATE('$firstDay') and deleted_at is null group by css_id, code) result_max"), "result_max.last_id", "=", "css_result.id")
                ->join('projs', 'projs.id', '=', 'css.projs_id')
                ->whereRaw("projs.leader_id IN ($sql)")
                ->whereRaw("DATE(css_result.updated_at) <= DATE('$lastDay')")
                ->whereRaw("DATE(css_result.updated_at) >= DATE('$firstDay')")
                ->whereNull('css_result.deleted_at')
                ->groupBy('css_result.id')
                ->select('css_result.avg_point', 'css.projs_id')
                ->get();
        if (!count($cssResultInMonth)) {
            return 0;
        }
        $countCss = 0;
        $totalCss = 0;
        foreach ($cssResultInMonth as $cssResult) {
            $factorProdCost = isset($projProdCostList[$cssResult->projs_id]) ? $projProdCostList[$cssResult->projs_id] : 1;
            $countCss += $factorProdCost;
            $totalCss += $cssResult->avg_point * $factorProdCost;
        }
        return $countCss == 0 ? 0 : round($totalCss/$countCss, 2);
    }
    
    public static function getCssPointAverage($firstDay, $lastDay, $teamId)
    {
        $sql = "select  t_bl.project_id, css_css, t_bl.updated_at
                from proj_point_baselines as t_bl
                    join (
                        select project_id, max(updated_at) as max_updated_at
                        from proj_point_baselines
                        where DATE(updated_at) <= DATE('$lastDay') and DATE(updated_at) >= DATE('$firstDay')
                        group by project_id
                ) as tbl_tmp1 on tbl_tmp1.project_id = t_bl.project_id and tbl_tmp1.max_updated_at = t_bl.updated_at
                join team_projs on team_projs.project_id = t_bl.project_id
                where team_projs.team_id = $teamId
                order by t_bl.updated_at desc";
        $cssPoint = DB::select($sql);
        if (!count($cssPoint)) {
            return 0;
        }
        $countCss = 0;
        $totalCss = 0;
        foreach ($cssPoint as $point) {
            if (!empty($point->css_css)) {
                $countCss++;
                $totalCss += $point->css_css;
            }
        }
        return $countCss == 0 ? 0 : round($totalCss/$countCss, 2);
    }
    
    public static function getMeEffortPoint($teamId, $month, $year)
    {
        $firstLastMonth = ResourceView::getInstance()->getFirstLastDaysOfMonth($month, $year);
        $firstDay = $firstLastMonth[0];
        $lastDay = $firstLastMonth[1];
        return MeEvaluation::join('employee_team_history', 'employee_team_history.employee_id', '=', 'me_evaluations.employee_id')
                ->join('me_points', 'me_points.eval_id', '=', 'me_evaluations.id')
                ->join('me_attributes', 'me_attributes.id', '=', 'me_points.attr_id')
                ->where(DB::raw('MONTH(eval_time)'), $month)
                ->where(DB::raw('YEAR(eval_time)'), $year)
                ->whereRaw("(employee_team_history.start_at is null OR DATE(employee_team_history.start_at) <= DATE('$lastDay')) AND (employee_team_history.end_at is null OR DATE(employee_team_history.end_at) >= DATE('$firstDay'))")
                ->where('me_evaluations.status', MeEvaluation::STT_CLOSED)
                ->where('employee_team_history.team_id', $teamId)
                ->where('me_attributes.type', MeAttribute::TYPE_PRO_ACTIVITY)
                //->groupBy('me_evaluations.employee_id')
                ->select('me_points.point', 'eval_id')
                ->get();
    }

    /**
     * get ME social activity
     * @param type $teamId
     * @param type $month
     * @param type $year
     * @return type
     */
    public static function getMeSocialActivity($teamId, $month, $year)
    {
        $firstLastMonth = ResourceView::getInstance()->getFirstLastDaysOfMonth($month, $year);
        $firstDay = $firstLastMonth[0];
        $lastDay = $firstLastMonth[1];
        $collect = MeEvaluation::from(MeEvaluation::getTableName() . ' as me')
                ->join(EmployeeTeamHistory::getTableName() . ' as eth', 'eth.employee_id', '=', 'me.employee_id')
                ->join('me_points as mep', 'mep.eval_id', '=', 'me.id')
                ->join(MeAttribute::getTableName() . ' as meattr', 'meattr.id', '=', 'mep.attr_id')
                ->where(DB::raw('DATE_FORMAT(me.eval_time, "%Y-%m")'), $year.'-'. ($month < 10 ? '0' . intval($month) : $month))
                ->whereRaw('(eth.start_at IS NULL OR DATE(eth.start_at) <= DATE("'. $lastDay. '")) '
                        . 'AND (eth.end_at IS NULL OR DATE(eth.end_at) >= DATE("'. $firstDay .'"))')
                ->where('me.status', MeEvaluation::STT_CLOSED)
                ->where('eth.team_id', $teamId)
                ->where('meattr.type', MeAttribute::TYPE_SOCIAL_ACTIVITY)
                ->groupBy('me.id')
                ->select('mep.point', 'mep.eval_id')
                ->get();
        if ($collect->isEmpty()) {
            return 0;
        }
        return round($collect->sum('point') / $collect->count(), 2);
    }

    /**
     * Delete data from monthly_report table
     * where created_at <> current month
     */
    public static function cronDeleteOldData()
    {
        DB::beginTransaction();
        try {
            $start = Carbon::now()->startOfMonth();
            self::whereNotNull('deleted_at')
                    ->whereRaw("DATE(updated_at) < DATE(?)", [$start])
                    ->withTrashed()
                    ->forceDelete();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex->getMessage());
        }
    }

    /**
     * Get team by permission
     * 
     * @return Team collection
     */
    public static function getTeam()
    {
        if (Permission::getInstance()->isScopeCompany()) {
            $teamsChildest = Team::getTeamsChildest();
        } else {
            $curEmp = Permission::getInstance()->getEmployee();
            $myTeams = CheckpointPermission::getArrTeamIdByEmployee($curEmp->id);
            $teamsChildest = Team::getTeamsChildest($myTeams);
        }
        return $teamsChildest;
    }

}
