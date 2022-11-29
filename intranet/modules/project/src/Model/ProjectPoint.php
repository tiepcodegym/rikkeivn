<?php

namespace Rikkei\Project\Model;

use Rikkei\Project\View\View;
use Exception;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Team\View\Permission;
use Rikkei\Sales\Model\CssResult;
use Rikkei\Project\Model\ProjPointBaseline;
use Rikkei\Project\View\View as ViewProject;
use Carbon\Carbon;
use Rikkei\Core\Model\CoreConfigData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class ProjectPoint extends ProjectWOBase
{
    const KEY_CACHE = 'project_point';

    protected $table = 'proj_point';

    const COLOR_STATUS_GREY = -1;
    const COLOR_STATUS_WHITE = 0;
    const COLOR_STATUS_BLUE = 1;
    const COLOR_STATUS_YELLOW = 2;
    const COLOR_STATUS_RED = 3;

    const EVALUATION_FALSE = 0;
    const EVALUATION_ACCEPTABLE = 1;
    const EVALUATION_FAIR = 2;
    const EVALUATION_GOOD = 3;
    const EVALUATION_EXCELLENT = 4;

    const FLAG_COLOR_QUA_DEFECT_YELLOW = 1000;
    const FLAG_COLOR_QUA_DEFECT_RED = 1500;

    const RAISE_UP = 1;
    const RAISE_DOWN = 2;

    const COLOR_VALUE_GREEN = '#00a65a';
    const COLOR_VALUE_YELLOW = '#f3b812';
    const COLOR_VALUE_RED = '#dd4b39';

    protected $isBaseline = null;

    /**
     * point value default
     *
     * @return array
     */
    public static function pointValueDefault()
    {
        return [
            'cost_lcl' => 80,
            'cost_target' => 100,
            'cost_ucl' => 120,

            'cost_busy_rate_lcl' => 80,
            'cost_busy_rate_target' => 100,
            'cost_busy_rate_ucl' => 120,

            'cost_effort_efficiency_lcl' => 50,
            'cost_effort_efficiency_target' => 75,
            'cost_effort_efficiency_ucl' => 100,

            'tl_schedule_lcl' => 0,
            'tl_schedule_target' => 1,
            'tl_schedule_ucl' => 2,

            'tl_deliver_lcl' => 40,
            'tl_deliver_target' => 70,
            'tl_deliver_ucl' => 100,

            'css_css_lcl' => 60,
            'css_css_target' => 80,
            'css_css_ucl' => 100,

            'qua_leakage_lcl' => 3,
            'qua_leakage_target' => 5,
            'qua_leakage_ucl' => 7,
            'qua_defect_lcl' => 0,
            'qua_defect_target' => 0.5,
            'qua_defect_ucl' => 1,

            'proc_compliance_lcl' => 0,
            'proc_compliance_target' => 1,
            'proc_compliance_ucl' => 2
        ];
    }

    /**
     * find or create project point default
     *
     * @param int $projectId
     * @return \self
     * @throws Exception
     */
    public static function findFromProject($projectId)
    {
        if ($item = CacheHelper::get(self::KEY_CACHE, $projectId)) {
            return $item;
        }
        $item = self::where('project_id', $projectId)
            ->whereIn('status', [null, self::STATUS_APPROVED])    
            ->first();
        if ($item) {
            return $item;
        }
        $item = new self();
        $valuesDefault = self::pointValueDefault();
        $valuesDefault = array_merge($valuesDefault, [
            'project_id' => $projectId,
            'status' => 1,
        ]);
        $item->setData($valuesDefault);
        try {
            $item->save([], ['not_employee' => true]);
            CacheHelper::put(self::KEY_CACHE, $item, $projectId);
            return $item;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * get point follow array values
     *
     * @param array $array
     * @param float $value
     * @return int
     */
    public function getPointFollowArray($array, $value, $nullValue = 0, $config = [])
    {
        if ($value === null) {
            return $nullValue;
        }
        $result = 0;
        foreach ($array as $key => $item) {
            if ($item[0] === null) {
                if ($value <= $item[1]) {
                    $result = $key;
                    break;
                }
            } elseif ($item[1] === null) {
                if ($value > $item[0]) {
                    $result = $key;
                    break;
                }
            } else {
                if ($item[0] < $value && $value <= $item[1]) {
                    $result = $key;
                    break;
                }
            }
        }
        if (isset($config['type_return'])) {
            if ($config['type_return'] == 1) { //return int
                return (int) $result;
            }
            return (float) $result; //return float
        }
        return $result;
    }

    /**
     * get point int follow array values
     *
     * @param array $array
     * @param float $value
     * @return int
     */
    public function getPointFollowArrayInt($array, $value, $nullValue = 0)
    {
        if ($value === null) {
            return $nullValue;
        }
        foreach ($array as $key => $item) {
            if ($item[0] === null) {
                if ($value <= $item[1]) {
                    return $key;
                }
            } elseif ($item[1] === null) {
                if ($value > $item[0]) {
                    return $key;
                }
            } else {
                if ($item[0] < $value && $value <= $item[1]) {
                    return $key;
                }
            }
        }
        return 0;
    }

    /**
     * get color of status follow ucl and target
     *
     * @param type $keyTarget
     * @param type $keyUcl
     * @param type $value
     * @return int
     */
    public function getColor($keyTarget, $keyUcl, $value = null)
    {
        if (!$value === null) {
            return self::COLOR_STATUS_BLUE;
        }
        if ($value <= $this->{$keyTarget}) {
            return self::COLOR_STATUS_BLUE;
        }
        if ($this->{$keyTarget} < $value && $value <= $this->{$keyUcl}) {
            return self::COLOR_STATUS_YELLOW;
        }
        return self::COLOR_STATUS_RED;
    }

    /**
     * get color of status follow from lcl to target
     *
     * @param type $keyTarget
     * @param type $keylcl
     * @param type $value
     * @return int
     */
    public function getColorReverse($keyTarget, $keylcl, $value = null)
    {
        if (empty($value)) {
            return self::COLOR_STATUS_BLUE;
        }
        if ($value > $this->{$keyTarget}) {
            return self::COLOR_STATUS_BLUE;
        }
        if ($this->{$keylcl} < $value && $value <= $this->{$keyTarget}) {
            return self::COLOR_STATUS_YELLOW;
        }
        return self::COLOR_STATUS_RED;
    }

    /**
     * get color of status follow ucl and target
     *
     * @param type $keyTarget
     * @param type $keyUcl
     * @param type $value
     * @return int
     */
    public function getColorReverse2($keyTarget, $keyUcl, $value = null)
    {
        if ($value === null) {
            return self::COLOR_STATUS_BLUE;
        }
        if ($value == $this->{$keyUcl}) {
            return self::COLOR_STATUS_BLUE;
        }
        if ($this->{$keyTarget} < $value && $value < $this->{$keyUcl}) {
            return self::COLOR_STATUS_YELLOW;
        }
        return self::COLOR_STATUS_RED;
    }

    /**
     * Get color follow point
     *
     * @param float $point
     * @return int
     */
    public function getColorFollowPoint($point)
    {
        if ($point >= 0) {
            return self::COLOR_STATUS_BLUE;
        }
        if (-1 <= $point) {
            return self::COLOR_STATUS_YELLOW;
        }
        return self::COLOR_STATUS_RED;
    }

    /**
     * get cost color
     *
     * @param float $value
     * @return int
     */
    public function getCostBusyRateColor($value)
    {
        if ($value === null) {
            return self::COLOR_STATUS_BLUE;
        }
        if ($value <= 80) {
            return self::COLOR_STATUS_RED;
        }
        if ($value <= 85) {
            return self::COLOR_STATUS_YELLOW;
        }
        if ($value <= 115) {
            return self::COLOR_STATUS_BLUE;
        }
        if ($value <= 120) {
            return self::COLOR_STATUS_YELLOW;
        }
        return self::COLOR_STATUS_RED;
    }

    /**
     * get cost color
     *
     * @param float $costEffortEfftiveness
     * @param float $costBusyRate
     * @return int
     */
    public function getCostColor(
        $costEffortEfftiveness = null, 
        $costBusyRate = null,
        $options = []
    ) {
        if ($this->isBaseline()) {
            return $this->getAttrValue('cost');
        }
        if ($this->warinngAfterApproveNotFillEffort($options)) {
            return self::COLOR_STATUS_RED;
        }
        if ($costEffortEfftiveness === null) {
            $costEffortEfftiveness = $this->getCostEffortEfftiveness();
        }
        if ($costBusyRate === null) {
            $costBusyRate = $this->getCostBusyRate();
        }
        $costEffortEfftivenessColor = $this->getColor('cost_target', 'cost_ucl', $costEffortEfftiveness);
        $costBusyRateColor = $this->getCostBusyRateColor($costBusyRate);
        return View::calculatorTotalColor([$costEffortEfftivenessColor, $costBusyRateColor]);
    }

    /**
     * get cost billableEffort
     *
     * @param object $projectQuality
     * @return float
     */
    public function getCostBillableEffort($projectQuality = null)
    {
        if ($this->isBaseline()) {
            return $this->getAttrValue('cost_billable_effort');
        }
        if ($projectQuality === null) {
            $projectQuality = ProjQuality::getFollowProject($this->project_id);
        }
        if (!$projectQuality) {
            return null;
        }
        if ($projectQuality->billable_effort !== null) {
            return (float) $projectQuality->billable_effort;
        }
        return null;
    }

    /**
     * get cost billableEffort
     *
     * @param object $projectQuality
     * @return float
     */
    public function getCostApprovedProd($projectQuality = null)
    {
        if ($this->isBaseline()) {
            return $this->getAttrValue('cost_approved_production');
        }
        if ($projectQuality === null) {
            $projectQuality = ProjQuality::getFollowProject($this->project_id);
        }
        if (!$projectQuality) {
            return null;
        }
        if ($projectQuality->cost_approved_production !== null) {
            return (float) $projectQuality->cost_approved_production;
        }
        return null;
    }

    /**
     * get cost plan effort total
     *
     * @param object $projectQuality
     * @return float
     */
    public function getCostPlanEffortTotal($projectQuality = null)
    {
        if ($this->isBaseline()) {
            return $this->getAttrValue('cost_plan_effort_total');
        }
        if ($projectQuality === null) {
            $projectQuality = ProjQuality::getFollowProject($this->project_id);
        }
        if (!$projectQuality) {
            return null;
        }
        if ($projectQuality->plan_effort !== null) {
            return (float) $projectQuality->plan_effort;
        }
        return null;
    }

    /**
     * get cost plan effort total
     *
     * @param float $costPlanEffortTotal
     * @param model $project
     * @return float
     */
    public function getCostPlanEffortTotalPoint(
            $costPlanEffortTotal = null, 
            $project = null
    ) {
        if ($this->isBaseline()) {
            return $this->getAttrValue('cost_plan_effort_total_point');
        }
        if ($costPlanEffortTotal === null) {
            $costPlanEffortTotal = $this->getCostPlanEffortTotal();
        }
        if (!$project) {
            $project = Project::find($this->project_id);
        }
        if (Config::get('project.mm') && $project->type_mm == Project::MD_TYPE) {
            $costPlanEffortTotal /= Config::get('project.mm');
        }
        $arrayValues = [
            3 => [29.999, null],
            2 => [19.999, 29.999],
            1 => [9.999, 19.999],
            '0.5' => [null, 9.999]
        ];
        return $this->getPointFollowArray($arrayValues, $costPlanEffortTotal, 0.5, [
            'type_return' => 2
        ]);
        
    }

    /**
     * get cost plan effort current
     * 
     * @return float
     */
    public function getCostPlanEffortCurrent()
    {
        if ($this->cost_plan_effort_current !== null) {
            return (float) $this->cost_plan_effort_current;
        }
        return null;
    }

    /**
     * get cost resource allocation
     * @return array
     */
    public function getCostResourceAllocation()
    {
        return ProjectMember::getTotalEffortMemberApproved(null, $this->project_id);
    }

    /**
     * get cost resource allocation total
     *
     * @return float
     */
    public function getCostResourceAllocationTotal($costResourceAllocation = null)
    {
        if ($this->isBaseline()) {
            return $this->getAttrValue('cost_resource_allocation_total');
        }
        if ($costResourceAllocation === null) {
            $costResourceAllocation = $this->getCostResourceAllocation();
        }
        return $costResourceAllocation['total'];
    }

    /**
     * get value of resource allocation current
     * 
     * @return float
     */
    public function getCostResourceAllocationCurrent($costResourceAllocation = null)
    {
        if ($this->isBaseline()) {
            return $this->getAttrValue('cost_resource_allocation_current');
        }
        if ($costResourceAllocation === null) {
            $costResourceAllocation = $this->getCostResourceAllocation();
        }
        return $costResourceAllocation['current'];
    }

    /**
     * get value of actual effort current
     * 
     * @return float
     */
    public function getCostActualEffortCurrent()
    {
        if ($this->cost_actual_effort !== null) {
            return (float) round($this->cost_actual_effort /8/21, 2);
        }
        return null;
    }

    /**
     * get cost Effort Effectiveness value
     *
     * @return float
     */
    public function getCostEffortEfftiveness(
        $costPlanEffortCurrent = null, //fix from $costApprovedProd to $costPlanEffortCurrent
        $costActualEffortCurrent = null
    ) {
        if ($this->isBaseline()) {
            return $this->getAttrValue('cost_effort_effectiveness');
        }
        if($costPlanEffortCurrent === null) {
            $costPlanEffortCurrent = $this->getCostPlanEffortCurrent();
        }
        if(!$costPlanEffortCurrent) {
            return null;
        }
        if($costActualEffortCurrent === null) {
            $costActualEffortCurrent = $this->getCostActualEffortCurrent();
        }
        return round($costActualEffortCurrent / $costPlanEffortCurrent * 100, 2);
    }

    /**
     * get cost busy rate
     *
     * @return int
     */
    public function getCostBusyRate(
        $costActualEffortCurrent = null,
        $costResourceAllocationCurrent = null
    ) {
        if ($this->isBaseline()) {
            return $this->getAttrValue('cost_busy_rate');
        }
        if($costActualEffortCurrent === null) {
            $costActualEffortCurrent = $this->getCostActualEffortCurrent();
        }
        if($costResourceAllocationCurrent === null) {
            $costResourceAllocationCurrent = $this->getCostResourceAllocationCurrent();
        }
        if($costResourceAllocationCurrent && $costActualEffortCurrent) {
            return round($costActualEffortCurrent / $costResourceAllocationCurrent * 100, 2);
        }
        return null;
    }

    public function getValueMetricByCate($project, $projectPoint)
    {
        $checkCate = in_array($project->category_id, [Project::CATEGORY_DEVELOPMENT, Project::CATEGORY_MAINTENANCE]);
        $checkValueMetricCateMaintainance = self::getValueMetricCateMaintainance($project, $projectPoint);
        $checkCateDevelop = in_array($project->category_id, [Project::CATEGORY_DEVELOPMENT]);
        return [
            'cost_effort_efficiency_lcl' => $checkCateDevelop ? '80' : $checkValueMetricCateMaintainance['cost_effort_efficiency_lcl'],
            'cost_effort_efficiency_target' => $checkCateDevelop ? '92' : $checkValueMetricCateMaintainance['cost_effort_efficiency_target'],
            'cost_effort_efficiency_ucl' => $checkCateDevelop ? '100' : $checkValueMetricCateMaintainance['cost_effort_efficiency_ucl'],
            'proc_compliance_lcl' => $checkCate ? '80' : $projectPoint->proc_compliance_lcl,
            'proc_compliance_target' => $checkCate ? '90' : $projectPoint->proc_compliance_target,
            'proc_compliance_ucl' => $checkCate ? '100' : $projectPoint->proc_compliance_ucl,
            'tl_deliver_lcl' => $checkCate ? '80' : $projectPoint->tl_deliver_lcl,
            'tl_deliver_target' => $checkCate ? '90' : $projectPoint->tl_deliver_target,
            'tl_deliver_ucl' => $checkCate ? '100' : $projectPoint->tl_deliver_ucl,
            'qua_leakage_lcl' => $checkCate ? '0' : $projectPoint->qua_leakage_lcl,
            'css_css_lcl' => $checkCate ? '80' : $projectPoint->css_css_lcl,
            'css_css_target' => $checkCate ? '90' : $projectPoint->css_css_target,
            'css_css_ucl' => $checkCate ? '100' : $projectPoint->css_css_ucl,
            'qua_leakage_target' => $checkCateDevelop ? "0.2" : $checkValueMetricCateMaintainance['qua_leakage_target'],
            'qua_leakage_ucl' => $checkCateDevelop ? "0.4" : $checkValueMetricCateMaintainance['qua_leakage_ucl'],
            'qua_defect_lcl' => $checkCateDevelop ? "4.2" : $checkValueMetricCateMaintainance['qua_defect_lcl'],
            'qua_defect_target' => $checkCateDevelop ? "9" : $checkValueMetricCateMaintainance['qua_defect_target'],
            'qua_defect_ucl' => $checkCateDevelop ? "13.4" : $checkValueMetricCateMaintainance['qua_defect_ucl'],
            'correct_cost_lcl' => $checkCateDevelop ? "4" : $checkValueMetricCateMaintainance['correct_cost_lcl'],
            'correct_cost_target' => $checkCateDevelop ? "6" : $checkValueMetricCateMaintainance['correct_cost_target'],
            'correct_cost_ucl' => $checkCateDevelop ? "12" : $checkValueMetricCateMaintainance['correct_cost_ucl'],
        ];
    }

    public function getValueMetricCateMaintainance($project, $projectPoint)
    {
        $checkCateMaintain = in_array($project->category_id, [Project::CATEGORY_MAINTENANCE]);
        return [
            'qua_leakage_target' => $checkCateMaintain ? '0.2' : $projectPoint->qua_leakage_target,
            'qua_leakage_ucl' => $checkCateMaintain ? '0.4' : $projectPoint->qua_leakage_ucl,
            'qua_defect_lcl' => $checkCateMaintain ? '0' : $projectPoint->qua_defect_lcl,
            'qua_defect_target' => $checkCateMaintain ? '5.5' : $projectPoint->qua_defect_target,
            'qua_defect_ucl' => $checkCateMaintain ? '8.1' : $projectPoint->qua_defect_ucl,
            'correct_cost_lcl' => $checkCateMaintain ? '0.2' : '',
            'correct_cost_target' => $checkCateMaintain ? '2' : '',
            'correct_cost_ucl' => $checkCateMaintain ? '10' : '',
            'cost_effort_efficiency_lcl' => $checkCateMaintain ? '75' : '',
            'cost_effort_efficiency_target' => $checkCateMaintain ? '87' : '',
            'cost_effort_efficiency_ucl' => $checkCateMaintain ? '95' : '',
        ];
    }

    /**
     * get cost busy rate point
     *
     * @param float $costBusyRate
     * @return int
     */
    public function getCostBusyRatePoint($costBusyRate = null)
    {
        if ($this->isBaseline()) {
            return $this->getAttrValue('cost_busy_rate_point');
        }
        if ($costBusyRate === null) {
            $costBusyRate = $this->getCostBusyRate();
        }
        $arrayValues = [
            -2 => [null, 69.999],
             -1 => [69.999, 79.999],
             1 => [79.999, 89.999],
             2 => [89.999, 109.999],
             '1_suffix' => [109.999, 119.999],
             '-1_suffix' => [119.999, 139.999],
             '-2_suffix' => [139.999, null]
        ];
        return $this->getPointFollowArray($arrayValues, $costBusyRate, 2, [
            'type_return' => 1
        ]);
    }

    /**
     * get cost Effort Effectiveness point
     *
     * @return float
     */
    public function getCostEffortEfftivenessPoint($costEffortEfftiveness = null)
    {
        if ($this->isBaseline()) {
            return $this->getAttrValue('cost_effort_effectiveness_point');
        }
        if ($costEffortEfftiveness === null) {
            $costEffortEfftiveness = $this->getCostEffortEfftiveness();
        }
        $arrayValues = [
            3 => [null, 80],
            2 => [80, 100],
            1 => [100, 110],
            -1 => [110, 120],
            -2 => [120, 130],
            -3 => [130, null]
        ];
        return $this->getPointFollowArray($arrayValues, $costEffortEfftiveness, 1);
    }

    /**
     * return cost effort efficiency follow resource
     *
     * @return float
     */
    public function getCostEffortEfficiency1()
    {
        if ($this->isBaseline()) {
            return $this->getAttrValue('cost_effort_efficiency1');
        }
        $resourceAllocation = $this->getCostResourceAllocationCurrent();
        if ($resourceAllocation) {
            return round((($this->getCostActualEffortCurrent() / $resourceAllocation) 
                    * 100), 2);
        }
        return 0;
    }

    /**
     * return cost effort efficiency follow billable
     *
     * @return float
     */
    public function getCostEffortEfficiency2(
            $project = null,
            $costApprovedProd = null, 
            $costResourceAllocationTotal = null,
            $otDay = null
    ) {
        $now = Carbon::now();
        if (isset($project)) {
            $startDate = Carbon::parse($project->start_at);
            $endDate = Carbon::parse($project->end_at);
            $monthProj = $startDate->diffInMonths($endDate) + 1;
            $monthCurrent = ($project->end_at <= $now) ? $monthProj : ($startDate->diffInMonths($now) + 1);
        }
        if ($this->isBaseline()) {
            return $this->getAttrValue('cost_effort_efficiency2');
        }
        if ($costResourceAllocationTotal === null) {
            $costResourceAllocationTotal = $this->getCostResourceAllocationCurrent();
        }
        if (!$costResourceAllocationTotal) {
            return null;
        }
        $costApprovedProd = $this->caculateNewCostApporovedProd($project);
        return round($costApprovedProd / ($costResourceAllocationTotal + $otDay) * 100, 2);
    }

    public function caculateNewCostApporovedProd($project)
    {
        if (empty($project)) {
            return null;
        }
        $costApprovedProd = 0;
        $currentMonth = date('Y-m');
        $listPj = ProjectApprovedProductionCost::getProjectApprpveProductionCost($project->id, true);
        foreach ($listPj as $key => $value) {
            if ($key <= $currentMonth) {
                $costApprovedProd += $value['approved_production_cost'];
                if (!empty($value['detail'])) {
                    foreach ($value['detail'] as $detail) {
                        $costApprovedProd += $detail['approved_production_cost'];
                    }
                }
            }
        }
        return $costApprovedProd;
    }

    /**
     * return cost effort efficiency point
     *
     * @return int
     */
    public function getCostEffortEfficiency2Point($costEffortEfficiency2 = null)
    {
        if ($this->isBaseline()) {
            return $this->getAttrValue('cost_effort_efficiency2_point');
        }
        if ($costEffortEfficiency2 === null) {
            $costEffortEfficiency2 = $this->getCostEffortEfficiency2();
        }
        $arrayValues = [
            -2 => [null, 49.999],
            -1 => [49.999, 69.999],
            '0.5' => [69.999, 79.999],
            1 => [79.999, 89.999],
            2 => [89.999, null]
        ];
        return $this->getPointFollowArray($arrayValues, $costEffortEfficiency2, 1, [
            'type_return' => 2
        ]);
    }

    /**
     * get cost productivity
     *
     * @param type $projectMeta
     * @return float
     */
    public function getCostProductivity($projectMeta)
    {
        if ($this->isBaseline()) {
            return $this->getAttrValue('cost_productivity');
        }
        $actualEffort = $this->getCostActualEffortCurrent();
        if ($actualEffort) {
            $projectMeta->lineofcode_current = $projectMeta->lineofcode_current ? (int)$projectMeta->lineofcode_current : 0;
            return round($projectMeta->lineofcode_current / $actualEffort, 2);
        }
        return 0;
    }

    /**
     * get process compliance
     */
    public function getProcCompliance()
    {
        return $this->getAttrValue('proc_compliance');
    }

    public static function saveCompliance($request)
    {
        $projectPoint = self::where('project_id', $request['id'])->first();
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
     * get point of process none compliance
     *
     * @param float $procCompliance
     * @return int
     */
    public function getProcCompliancePoint($procCompliance = null)
    {
        if ($this->isBaseline()) {
            return $this->getAttrValue('proc_compliance_point');
        }
        if ($procCompliance === null) {
            $procCompliance = $this->getProcCompliance();
        }
        $arrayValues = [
            3 => [null, 0],
            2 => [0, 1],
            1 => [1, 2],
            0 => [2, 3],
            -1 => [3, 4],
            -2 => [4, 5],
            -3 => [5, null]
        ];
        return $this->getPointFollowArray($arrayValues, $procCompliance, 2);
    }

    /**
     * get process report all point
     *
     * @return array
     */
    public function getProcReportNumber()
    {
        return ProjPointReport::getCountSplit($this->project_id);
    }

    /**
     * get process report on time
     */
    public function getProcReportYes($procReportNumber = null)
    {
        if ($this->isBaseline()) {
            return $this->getAttrValue('proc_report_yes');
        }
        if ($procReportNumber === null) {
            $procReportNumber = $this->getProcReportNumber();
        }
        return $procReportNumber[ProjPointReport::POINT_YES];
    }

    /**
     * get process report delayed
     */
    public function getProcReportDelayed($procReportNumber = null)
    {
        if ($this->isBaseline()) {
            return $this->getAttrValue('proc_report_delayed');
        }
        if ($procReportNumber === null) {
            $procReportNumber = $this->getProcReportNumber();
        }
        return $procReportNumber[ProjPointReport::POINT_DELAY];
    }

    /**
     * get process no report 
     */
    public function getProcReportNo($procReportNumber = null)
    {
        if ($this->isBaseline()) {
            return $this->getAttrValue('proc_report_no');
        }
        if ($procReportNumber === null) {
            $procReportNumber = $this->getProcReportNumber();
        }
        return $procReportNumber[ProjPointReport::POINT_NO];
    }

    /**
     * get total report
     *
     * @return int
     */
    public function getProcReport($procReportNumber = null) {
        if ($this->isBaseline()) {
            return $this->getAttrValue('proc_report');
        }
        if ($procReportNumber === null) {
            $procReportNumber = $this->getProcReportNumber();
        }
        return $procReportNumber['point'];
    }

    /**
     * get process report point
     *
     * @return float
     */
    public function getProcReportPoint($procReport = null) {
        if ($this->isBaseline()) {
            return $this->getAttrValue('proc_report_point');
        }
        if ($procReport === null) {
            $procReport = $this->getProcReport();
        }
        return $procReport;
    }

    /**
     * get color of process
     *
     * @param float $procCompliance
     * @param float $procReportPoint
     * @return int
     */
    public function getProcColor($procCompliance = null, $procReportPoint = null)
    {
        if ($this->isBaseline()) {
            return $this->getAttrValue('proc');
        }
        if ($procCompliance === null) {
            $procCompliance = $this->getProcCompliance();
        }
        if ($procReportPoint === null) {
            $procReportPoint = $this->getProcReportPoint();
        }
        $procComplianceColor = $this->getColor('proc_compliance_target', 'proc_compliance_ucl', $procCompliance);
        $procReportPointColor = $this->getColorFollowPoint($procReportPoint);
        return View::calculatorTotalColor([$procComplianceColor, $procReportPointColor]);
    }

    /**
     * get timeliness schedule delayed
     *
     * @return float
     */
    public function getTlSchedule()
    {
        if ($this->tl_schedule !== null) {
            return (float) $this->tl_schedule;
        }
        return null;
    }

    /**
     * get timeliness schedule point
     *
     * @param type $tlSchedule
     * @return type
     */
    public function getTlSchedulePoint($tlSchedule = null)
    {
        if ($this->isBaseline()) {
            return $this->getAttrValue('tl_schedule_point');
        }
        if ($tlSchedule === null) {
            $tlSchedule = $this->getTlSchedule();
        }
        $arrayValues = [
            2 => [null, 0],
            1 => [0, 1],
            -1 => [1, 2],
            -2 => [2, null],
        ];
        return $this->getPointFollowArray($arrayValues, $tlSchedule, 2);
    }

    /**
     * get timeliness deliverable value
     *
     * @return float
     */
    public function getTlDeliver()
    {
        if ($this->isBaseline()) {
            return $this->getAttrValue('tl_deliver');
        }
        $delieverInfo = ProjDeliverable::getDeliverInfo($this->project_id);
        if ($delieverInfo['ontime'] === null) {
            return null;
        }
        if ($delieverInfo['total']) {
            return round($delieverInfo['ontime'] / $delieverInfo['total'] * 100, 2);
        }
        return null;
    }

    /**
     * get timeliness deliverable point
     *
     * @param array $delieverInfo
     * @param object $delivers
     * @return int
     */
    public function getTlDeliverPoint($tlDeliver = null)
    {
        if ($this->isBaseline()) {
            return $this->getAttrValue('tl_deliver_point');
        }
        if ($tlDeliver === null) {
            $tlDeliver = $this->getTlDeliver();
        }
        $arrayValues = [
            3 => [99.999, null],
            2 => [85, 99.999],
            1 => [70, 85],
            0 => [69.999, 70],
            -1 => [55, 69.999],
            -2 => [40, 55],
            -3 => [null, 40],
        ];
        return $this->getPointFollowArray($arrayValues, $tlDeliver, 3);
    }

    /**
     * get color of timeliness
     *
     * @param float $tlSchedule
     * @param float $tlDeliver
     * @return int
     */
    public function getTlColor($tlSchedule = null, $tlDeliver = null)
    {
        if ($this->isBaseline()) {
            return $this->getAttrValue('tl');
        }
        if ($tlSchedule === null) {
            $tlSchedule = $this->getTlSchedule();
        }
        if ($tlDeliver === null) {
            $tlDeliver = $this->getTlDeliver();
        }
        $tlScheduleColor = $this->getColor('tl_schedule_target', 'tl_schedule_ucl', $tlSchedule);
        $tlDeliverColor = $this->getColorReverse2('tl_deliver_target', 'tl_deliver_ucl', $tlDeliver);
        return View::calculatorTotalColor([$tlScheduleColor, $tlDeliverColor]);
    }

    /**
     * get quality leakage error
     *
     * @return int
     */
    public function getQuaLeakageError()
    {
        if ($this->qua_leakage_errors !== null) {
            return (int) $this->qua_leakage_errors;
        }
        return null;
    }

    public function getCorrectionCost()
    {
        if ($this->correction_cost !== null) {
                return (int) $this->correction_cost;
            }
        return null;
    }

    /**
     * Get quality leakage value
     *
     * @param int $quaLeakageError
     * @param int $quaDefectError
     * @return float
     */
    public function getQuaLeakage($quaLeakageError = null, $costResourceAllocationTotal = null , $otDay = null)
    {
        if ($this->isBaseline()) {
            return $this->getAttrValue('qua_leakage');
        }
        if ($quaLeakageError === null) {
            $quaLeakageError = $this->getQuaLeakageError();
        }
        if (($costResourceAllocationTotal + $otDay) !== null && ($costResourceAllocationTotal + $otDay) != 0 && $quaLeakageError !== null) {
            return round($quaLeakageError / ($costResourceAllocationTotal + $otDay), 2);
        }
        return null;
    }

    /**
     * Get quality leakage point
     *
     * @param float $quaLeakage
     * @return int
     */
    public function getQuaLeakagePoint($quaLeakage = null)
    {
        if ($this->isBaseline()) {
            return $this->getAttrValue('qua_leakage_point');
        }
        if ($quaLeakage === null) {
            $quaLeakage = $this->getQuaLeakage();
        }
        $arrayValues = [
            3 => [null, 3],
            2 => [3, 5],
            1 => [5, 7],
            '0.5' => [7, 9],
            -1 => [9, 11],
            -2 => [11, 13],
            -3 => [13, null],
        ];
        return $this->getPointFollowArray($arrayValues, $quaLeakage, 3, [
            'type_return' => 2
        ]);
    }

    /**
     * get quality defect error
     *
     * @return int
     */
    public function getQuaDefectError()
    {
        if ($this->qua_defect_errors !== null) {
            return (int) $this->qua_defect_errors;
        }
        return null;
    }

    /**
     * get quality defect value
     *
     * @param int $quaDefectError
     * @param float $costResourceAllocation
     * @return float
     */
    public function getQuaDefect($quaDefectError = null, $costResourceAllocation = null, $otDay = null)
    {
        if ($this->isBaseline()) {
            return $this->getAttrValue('qua_defect');
        }
        if ($quaDefectError === null) {
            $quaDefectError = $this->getQuaDefectError();
        }

        if (($costResourceAllocation + $otDay) !== null && ($costResourceAllocation + $otDay) != 0 && $quaDefectError !== null) {
            return round($quaDefectError / ($costResourceAllocation + $otDay), 2);
        }
        return null;
    }

    public function quaCorrectionCost($quaCorrectionCode = null, $costResourceAllocation = null, $otDay = null)
    {
        if ($quaCorrectionCode && ($costResourceAllocation + $otDay) != 0) {
            return round($quaCorrectionCode /8/21 / ($costResourceAllocation + $otDay) * 100, 2);
        }
        return null;
    }

    /**
     * Get quality defect point
     *
     * @param float $quaDefect
     * @return int
     */
    public function getQuaDefectPoint($quaDefect = null)
    {
        if ($this->isBaseline()) {
            return $this->getAttrValue('qua_defect_point');
        }
        if ($quaDefect === null) {
            $quaDefect = $this->getQuaDefect();
        }
        $arrayValues = [
            2 => [null, 1],
            1 => [1, 3],
            -1 => [3, 5],
            -2 => [5, null]
        ];
        return $this->getPointFollowArray($arrayValues, $quaDefect, 2);
    }

    /**
     * get quality color
     *
     * @param float $quaLeakage
     * @param float $quaDefect
     * @return int
     */
    public function getQuaColor($quaLeakage = null, $quaDefect = null)
    {
        if ($this->isBaseline()) {
            return $this->getAttrValue('quality');
        }
        if ($quaLeakage === null) {
            $quaLeakage = $this->getQuaLeakage();
        }
        if ($quaDefect === null) {
            $quaDefect = $this->getQuaDefect();
        }
        $quaLeakageColor = $this->getColor('qua_leakage_target', 'qua_leakage_ucl', $quaLeakage);
        $quaDefectColor = $this->getQuaDefectColor($quaDefect);
        $quaColor = View::calculatorTotalColor([$quaLeakageColor, $quaDefectColor]);
        if ($quaColor != self::COLOR_STATUS_BLUE) {
            return $quaColor;
        }
        $actualDate = StageAndMilestone::getFirstQualityGateActual($this->project_id);
        if (!$actualDate) {
            return $quaColor;
        }
        $now = Carbon::now();
        $diff = $now->diff($actualDate);
        if (!$diff->invert || $diff->days == 0) {
            return $quaColor;
        }
        if ($quaDefect >= 1) {
            return $quaColor;
        }
        return self::COLOR_STATUS_YELLOW;
    }

    /**
     * get qua defect color custom
     *
     * @param int $quaDefect
     * @return int
     */
    public function getQuaDefectColor($quaDefect)
    {
        return $this->getColor('qua_defect_target', 'qua_defect_ucl', 
            $quaDefect);
        /*if ($quaDefectError === null) {
            $quaDefectError = $this->getQuaDefectError();
        }
        if ($quaDefectError < self::FLAG_COLOR_QUA_DEFECT_YELLOW) {
            return $quaDefectColor;
        }
        // >= 1000 => yellow
        if ($quaDefectError < self::FLAG_COLOR_QUA_DEFECT_RED) {
            if ($quaDefectColor == self::COLOR_STATUS_BLUE) {
                return self::COLOR_STATUS_YELLOW;
            }
            return $quaDefectColor;
        }
        // >= 1500 => red
        return self::COLOR_STATUS_RED;*/
    }

    /**
     * get css value
     *
     * @return float
     */
    public function getCssCs()
    {
        return $this->getAttrValue('css_css');
    }

    /**
     * get css from css result
     *
     * @return type
     */
    public function getCssCssFromCssResult()
    {
        return CssResult::getCssFromProjId($this->project_id);
    }

    /**
     * get css follow deliver last actual date
     *
     * @return float
     */
    public function saveCssCssCheckDeliver()
    {
        $cssFromDb = $this->getAttrValue('css_css');
        $actualDate = ProjDeliverable::getLastActualApprovedItem($this->project_id);
        if (!$actualDate) {
            return false;
        }
        $modify = CoreConfigData::getCssAfterDeliver();
        if (!$modify) {
            return false;
        }
        $specialHolidays = CoreConfigData::getSpecialHolidays(2);
        $annualHolidays = CoreConfigData::getAnnualHolidays(2);
        $actualDate = Carbon::parse($actualDate);
        $actualLastDateModify = clone $actualDate;
        $numberModify = ViewProject::getNumberDayModifyActualLastDate($actualLastDateModify,
                                                                    $modify,
                                                                    $specialHolidays,
                                                                    $annualHolidays);
        if (!$numberModify) {
            return false;
        }
        $numberModify += 1;
        $actualLastDate = clone $actualDate;
        $actualLastDate = $actualLastDate->modify('+' . $numberModify .' day');
        $now = Carbon::now();
        // current between actual date and last actual date
        if (!$now->diff($actualLastDate)->invert) {
            return false;
        }
        $dateUpdatedCss = $this->date_updated_css;
        if ($dateUpdatedCss) {
            $dateUpdatedCss = Carbon::parse($dateUpdatedCss);
            // coo update > last actual date
            if ($dateUpdatedCss->diff($actualLastDate)->invert) {
                return false;
            }
        }
        if (CssResult::hasCssFromProjIdDurationDate(
                $this->project_id, 
                $actualDate->format('Y-m-d'), 
                $actualLastDate->format('Y-m-d'))
        ) {
            return false;
        }
        $cssFromCaculator = 0;
        $this->css_css = $cssFromCaculator;
        $this->save([], [
            'not_employee' => 1
        ]);
        return true;
    }

    /**
     * get css point
     *
     * @param float $cssCs
     * @return int
     */
    public function getCssCsPoint($cssCs = null)
    {
        if ($this->isBaseline()) {
            return $this->getAttrValue('css_css_point');
        }
        if ($cssCs === null) {
            $cssCs = $this->getCssCs();
        }
        $arrayValues = [
            3 => [90, null],
            2 => [80, 90],
            1 => [70, 80],
            '0.5' => [60, 70],
            -1 => [50, 60],
            -2 => [null, 50]
        ];
        return $this->getPointFollowArray($arrayValues, $cssCs, 0, [
            'type_return' => 2
        ]);
    }

    /**
     * get css idea negative
     *
     * @return int
     */
    public function getCssCiNegative($taskSplit = null)
    {
        if ($this->isBaseline()) {
            return $this->getAttrValue('css_ci_negative');
        }
        if ($taskSplit === null) {
            $taskSplit = Task::getTypeCommendedCriticizedSplit($this->project_id);
        }
        return $taskSplit[Task::TYPE_CRITICIZED];
    }

    /**
     * get css idea positive
     *
     * @return int
     */
    public function getCssCiPositive($taskSplit = null)
    {
        if ($this->isBaseline()) {
            return $this->getAttrValue('css_ci_positive');
        }
        if ($taskSplit === null) {
            $taskSplit = Task::getTypeCommendedCriticizedSplit($this->project_id);
        }
        return $taskSplit[Task::TYPE_COMMENDED];
    }

    /**
     * get css customer idea value
     *
     * @param int $cssCiPositive
     * @param int $cssCiNegative
     * @return int
     */
    public function getCssCi($cssCiPositive = null, $cssCiNegative = null)
    {
        if ($this->isBaseline()) {
            return $this->getAttrValue('css_ci');
        }
        if ($cssCiPositive === null) {
            $cssCiPositive = $this->getCssCiPositive();
        }
        if ($cssCiNegative === null) {
            $cssCiNegative = $this->getCssCiNegative();
        }
        return $cssCiNegative + $cssCiPositive;
    }

    /**
     * get css customer idea point
     * max 2, min -2
     *
     * @param int $cssCiPositive
     * @param int $cssCiNegative
     * @return int
     */
    public function getCssCiPoint($cssCiPositive = null, $cssCiNegative = null)
    {
        return 0;
        /*if ($this->isBaseline()) {
            return $this->getAttrValue('css_ci_point');
        }
        if ($cssCiPositive === null) {
            $cssCiPositive = $this->getCssCiPositive();
        }
        if ($cssCiNegative === null) {
            $cssCiNegative = $this->getCssCiNegative();
        }
        $point = $cssCiPositive - $cssCiNegative;
        if ($point > 2) {
            return 2;
        }
        if ($point < -2) {
            return -2;
        }
        return $point;*/
    }

    /**
     * get css color
     *
     * @param type $cssCs
     * @param type $cssCiNegative
     * @return type
     */
    public function getCssColor($cssCs = null, $cssCiNegative = null)
    {
        if ($this->isBaseline()) {
            return $this->getAttrValue('css');
        }
        if ($cssCs === null) {
            $cssCs = $this->getCssCs();
        }
        $cssCsColor = $this->getColorReverse('css_css_target', 'css_css_lcl', $cssCs);
        $cssCiColor = $this->getCssCiColor($cssCiNegative);
        return View::calculatorTotalColor([$cssCsColor, $cssCiColor]);
    }

    /**
     * get css color
     *
     * @param type $cssCs
     * @param type $cssCiPoint
     * @return type
     */
    public function getCssCiColor($cssCiNegative)
    {
        $arrayValues = [
            self::COLOR_STATUS_BLUE => [null, 0.1],
            self::COLOR_STATUS_YELLOW => [0.1, 1.1],
            self::COLOR_STATUS_RED => [1.1, null]
        ];
        return $this->getPointFollowArray($arrayValues, $cssCiNegative);
    }

    /**
     * get summary color
     *
     * @param int $costColor
     * @param int $quaColor
     * @param int $tlColor
     * @param int $procColor
     * @param int $cssColor
     * @return int
     */
    public function getSummaryColor(
            $costColor = null,
            $quaColor = null, 
            $tlColor = null, 
            $procColor = null, 
            $cssColor = null
    ) {
        if ($this->isBaseline()) {
            return $this->getAttrValue('summary');
        }
        if ($costColor === null) {
            $costColor = $this->getCostColor();
        }
        if ($quaColor === null) {
            $quaColor = $this->getQuaColor();
        }
        if ($tlColor === null) {
            $tlColor = $this->getTlColor();
        }
        if ($procColor === null) {
            $procColor = $this->getProcColor();
        }
        if ($cssColor === null) {
            $cssColor = $this->getCssColor();
        }
        return View::calculatorTotalColor([$costColor, $quaColor, $tlColor, $procColor, $cssColor]);
    }

    /**
     * get project total point
     *
     * @param array $arrayPoint
     * @return float
     */
    public function getTotalPoint($arrayPoint = [], $isGetAttr = false) {
        if ($this->isBaseline()) {
            return $this->getAttrValue('point_total');
        }
        if ($isGetAttr) {
            $costEffortEfftivenessPoint = $this->cost_effort_effectiveness_point;
        } elseif (!isset($arrayPoint['costEffortEfftivenessPoint'])) {
            $costEffortEfftivenessPoint = $this->getCostEffortEfftivenessPoint();
        } else {
            $costEffortEfftivenessPoint = $arrayPoint['costEffortEfftivenessPoint'];
        }

        if ($isGetAttr) {
            $costPlanEffortTotalPoint = $this->cost_plan_effort_total_point;
        } elseif (!isset($arrayPoint['costPlanEffortTotalPoint'])) {
            $costPlanEffortTotalPoint = $this->getCostPlanEffortTotalPoint();
        } else {
            $costPlanEffortTotalPoint = $arrayPoint['costPlanEffortTotalPoint'];
        }

        if ($isGetAttr) {
            $costBusyRatePoint = $this->cost_busy_rate_point;
        } elseif (!isset($arrayPoint['costBusyRatePoint'])) {
            $costBusyRatePoint = $this->getCostBusyRatePoint();
        } else {
            $costBusyRatePoint = $arrayPoint['costBusyRatePoint'];
        }

        if ($isGetAttr) {
            $costEffortEfficiency2Point = $this->cost_effort_efficiency2_point;
        } elseif (!isset($arrayPoint['costEffortEfficiency2Point'])) {
            $costEffortEfficiency2Point = $this->getCostEffortEfficiency2Point();
        } else {
            $costEffortEfficiency2Point = $arrayPoint['costEffortEfficiency2Point'];
        }

        if ($isGetAttr) {
            $cssCsPoint = $this->css_css_point;
        } elseif (!isset($arrayPoint['cssCsPoint'])) {
            $cssCsPoint = $this->getCssCsPoint();
        } else {
            $cssCsPoint = $arrayPoint['cssCsPoint'];
        }

        if ($isGetAttr) {
            $tlDeliverPoint = $this->tl_deliver_point;
        } elseif (!isset($arrayPoint['tlDeliverPoint'])) {
            $tlDeliverPoint = $this->getTlDeliverPoint();
        } else {
            $tlDeliverPoint = $arrayPoint['tlDeliverPoint'];
        }

        if ($isGetAttr) {
            $tlSchedulePoint = $this->tl_schedule_point;
        } elseif (!isset($arrayPoint['tlSchedulePoint'])) {
            $tlSchedulePoint = $this->getTlSchedulePoint();
        } else {
            $tlSchedulePoint = $arrayPoint['tlSchedulePoint'];
        }
        if ($isGetAttr) {
            $quaDefectPoint = $this->qua_defect_point;
        } elseif (!isset($arrayPoint['quaDefectPoint'])) {
            $quaDefectPoint = $this->getQuaDefectPoint();
        } else {
            $quaDefectPoint = $arrayPoint['quaDefectPoint'];
        }

        if ($isGetAttr) {
            $quaLeakagePoint = $this->qua_leakage_point;
        } elseif (!isset($arrayPoint['quaLeakagePoint'])) {
            $quaLeakagePoint = $this->getQuaLeakagePoint();
        } else {
            $quaLeakagePoint = $arrayPoint['quaLeakagePoint'];
        }

        if ($isGetAttr) {
            $procCompliancePoint = $this->proc_compliance_point;
        } elseif (!isset($arrayPoint['proc_compliance_point'])) {
            $procCompliancePoint = $this->getProcCompliancePoint();
        } else {
            $procCompliancePoint = $arrayPoint['procCompliancePoint'];
        }

        if ($isGetAttr) {
            $procReportPoint = $this->proc_report_point;
        } elseif (!isset($arrayPoint['procReportPoint'])) {
            $procReportPoint = $this->getProcReportPoint();
        } else {
            $procReportPoint = $arrayPoint['procReportPoint'];
        }

        $point = $costEffortEfftivenessPoint + $costBusyRatePoint 
            + $costEffortEfficiency2Point + $costPlanEffortTotalPoint
            + $cssCsPoint
            + $tlDeliverPoint + $tlSchedulePoint 
            + $quaDefectPoint + $quaLeakagePoint 
            + $procCompliancePoint + $procReportPoint;
        if ($point < 0) {
            $point = 0;
        }
        return $point;
    }

    /**
     * get project evaluation
     *
     * @param float $totalPoint
     * @return int
     */
    public function getProjectEvaluation($totalPoint = null)
    {
        if ($totalPoint === null) {
            $totalPoint = $this->getTotalPoint();
        }
        $arrayValues = [
            self::EVALUATION_EXCELLENT => [23.99, null],
            self::EVALUATION_GOOD => [19.99, 23.99],
            self::EVALUATION_FAIR => [14.99, 19.99],
            self::EVALUATION_ACCEPTABLE => [4.99, 14.99],
            self::EVALUATION_FALSE => [null, 4.99]
        ];
        return $this->getPointFollowArray(
                $arrayValues, 
                $totalPoint, 
                self::EVALUATION_FALSE);
    }

    /**
     * get label of project evauation
     *
     * @return array
     */
    public static function evaluationLabel()
    {
        return [
            self::EVALUATION_FALSE => 'Unacceptable',
            self::EVALUATION_ACCEPTABLE => 'Poor',
            self::EVALUATION_FAIR => 'Fair',
            self::EVALUATION_GOOD => 'Good',
            self::EVALUATION_EXCELLENT => 'Excellent'
        ];
    }

    /**
     * get project point by id
     * @param int
     * @return array
     */
    public static function getProjecPointtById($id)
    {
        return self::find($id);
    }

    /**
     * overide save the model to the database.
     *
     * @param  array  $options: projectPointInformation, project, dataInput
     * @return bool
     */
    public function save(array $options = array(), $config = []) {
        try {
            CacheHelper::forget(self::KEY_CACHE, $this->project_id);
            if (!isset($config['not_employee'])) {
                $employee = Permission::getInstance()->getEmployee();
                if ($employee && $employee->id) {
                    $this->changed_by = Permission::getInstance()->getEmployee()->id;
                }
            }
            if (isset($config['project']) && 
                $config['project']
            ) {
                $project = $config['project'];
            } else {
                $project = Project::find($this->project_id);
            }
            if (isset($config['projectPointInformation']) && 
                $config['projectPointInformation']
            ) {
                $projectPointInformation = $config['projectPointInformation'];
            } else {
                $projectPointInformation = ViewProject::getProjectPointInfo(
                    $project, 
                    $this
                );
            }
            if (($project->isTypeTrainingOfRD()) && isset($config['dataInput'])) {
                $config['dataInput']['color']['summary'] = 
                    View::calculatorTotalColor($config['dataInput']['color']);
                $colorMore = $config['dataInput']['color'];
            } else {
                $colorMore = null;
            }
            if (isset($config['is_change_color'])) {
                $isChangeColor = $config['is_change_color'];
            } else {
                $isChangeColor = true;
            }
            $result = parent::save($options);
            CacheHelper::forget(self::KEY_CACHE, $this->project_id);
            ProjPointFlat::flatItemProject($project, $this, $projectPointInformation, $isChangeColor, [
                'onsite_color' => $colorMore
            ]);
            return $result;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * check baseline
     *
     * @return boolean
     */
    public function isBaseline()
    {
        if ($this->isBaseline !== null) {
            return $this->isBaseline;
        }
        if (get_class($this) == 'Rikkei\Project\Model\ProjPointBaseline') {
            $this->isBaseline = true;
            return true;
        }
        $this->isBaseline = false;
        return false;
    }

    /**
     * set is baseline
     *
     * @param boolean $boolean
     */
    public function setIsBaseline($boolean)
    {
        $this->isBaseline = $boolean;
        return $this;
    }

    /**
     * get attribute value
     *
     * @param type $attribute
     * @return type
     */
    protected function getAttrValue($attribute)
    {
        if ($this->{$attribute} !== null) {
            return $this->{$attribute};
        }
        return null;
    }

    /**
     * set created_at and updated_at of point
     *  created_at: updated first in week
     *  updated_at: updated current
     */
    public function setTimeStamps($project = null)
    {
        list ($now, $firstWeek, $lastWeek) = View::getFirstLastDayOfWeek();
        if (!$project) {
            $project = Project::find($this->project_id);
        }
        $this->report_last_at = $now->format('Y-m-d H:i:s');
        ProjPointReport::reportItem([
            $project, 
            $now, 
            $firstWeek, 
            $lastWeek, 
            null,
            $this,
            true
        ], false);
    }

    /**
     * destroy all raise
     */
    public static function destroyRaiseAll()
    {
        $selfTable = self::getTableName();
        DB::table($selfTable)
            ->where('raise', 1)
            ->update(['raise' => 2]);
    }

    /**
     * waring if wo approved but not fill actual effort and plan effort
     *
     * @return boolean
     */
    public function warinngAfterApproveNotFillEffort($options = [])
    {
        if (isset($options['project']) && $options['project']) {
            if ($options['project']->type == Project::TYPE_ONSITE) {
                return false;
            }
        }
        $planEffortCurrent = $this->getCostPlanEffortCurrent();
        $actualEffortCureent = $this->getCostActualEffortCurrent();
        if ($planEffortCurrent && $actualEffortCureent) {
            return false;
        }
        $existsApproved = Task::checkHasTaskWorkorderApproved($this->project_id);
        if (!$existsApproved) {
            return false;
        }
        $projPointFlat = ProjPointFlat::findFlatFromProject($this->project_id);
        if ($projPointFlat->cost == self::COLOR_STATUS_RED) {
            return true;
        }
        $projPointFlat->setData([
            'summary' => self::COLOR_STATUS_RED,
            'cost' => self::COLOR_STATUS_RED,
        ])->save();
        return true;
    }

    /**
     * get notes of project
     *
     * @param array $ids
     * @return object
     */
    public static function getNotes($ids, $weekSlug = null, $noBaseline = false)
    {   
        $tableProject = Project::getTableName();
        $tablePoint = self::getTableName();
        $tablePointBaseline = ProjPointBaseline::getTableName();
        
        $columns = self::getAttrNote();
        array_push($columns, 'project_id');
        //check is baseline
        if ($weekSlug) {
            $tablePoint = $tablePointBaseline;
            //add alias columns select
            $columns = array_map(function ($item) use ($tablePoint) {
                $item = $tablePoint . '.' . $item;
                return $item;
            }, $columns);
            $collection = ProjPointBaseline::select($columns);
            //get project point baseline in week slug time
            $dayWeeks = self::parseWeekBySlug($weekSlug);
            $collection->where("{$tablePoint}.created_at", '>=', $dayWeeks[0]->toDateTimeString())
                ->where("{$tablePoint}.created_at", '<=', $dayWeeks[1]->toDateTimeString())
                ->addSelect('bl_summary_note');
        } else {
            //add alias columns select
            $columns = array_map(function ($item) use ($tablePoint) {
                $item = $tablePoint . '.' . $item;
                return $item;
            }, $columns);
            $collection = self::select($columns);
        }
        
        $collection->join($tableProject, "{$tableProject}.id", '=', "{$tablePoint}.project_id")
            ->whereIn($tablePoint . '.project_id', $ids)
            ->where($tableProject. '.status', Project::STATUS_APPROVED);
        if (Project::isUseSoftDelete()) {
            $collection->whereNull('deleted_at');
        }
      
        if ($noBaseline) {
            //check has note before week slug
            if (!$weekSlug) {
                $weekSlug = Carbon::now()->format('Y-W');
            }
            $dayWeeks = self::parseWeekBySlug($weekSlug);
            $collection->leftJoin(DB::raw('(SELECT project_id, MAX(created_at) as prev_time '
                    . 'FROM '. $tablePointBaseline .' '
                    . 'WHERE created_at < "'. $dayWeeks[0]->toDateTimeString() .'" '
                    . 'GROUP BY project_id) AS projbl'), 
                    $tablePoint . '.project_id', '=', 'projbl.project_id')
                    ->groupBy($tablePoint.'.id')
                    ->addSelect('projbl.prev_time');
        }
        return $collection->get();
    }

    /**
     * get first day and last day of week by format Y-W (year - week)
     * @param type $weekSlug
     * @return array
     */
    public static function parseWeekBySlug($weekSlug) {
        list($year, $week) = explode('-', $weekSlug);
        $week = (int) $week;
        $firstWeek = Carbon::now()->setISODate($year, $week, 1)->setTime(0,0,0);
        $lastWeek = Carbon::now()->setISODate($year, $week, 7)->setTime(23,59,59);
        return [$firstWeek, $lastWeek];
    }

    /**
     * get column note of project point
     *
     * @return array
     */
    public static function getAttrNote() {
        return [
            'cost_billable_note',
            'cost_approved_production_note',
            'cost_plan_total_note',
            'cost_plan_current_note',
            'cost_resource_total_note',
            'cost_resource_current_note',
            'cost_actual_effort_note',
            'cost_ees_note',
            'cost_eey2_note',
            'cost_busy_rate_note',
            'cost_productivity',
            'qua_leakage_note',
            'qua_defect_note',
            'tl_schedule_note',
            'tl_deliver_note',
            'css_css_note',
            'css_idea_note',
            'proc_compliance_note',
            'proc_report_note',
        ];
    }

    /**
     * reset all notes of project points
     * @return type
     */
    public static function resetAllNote() {
        $projPointTbl = ProjectPoint::getTableName();
        $projTbl = Project::getTableName();
        //get project points where project is processing and approved and not deleted
        $projectPoints = ProjectPoint::from($projPointTbl . ' as pp')
                ->join($projTbl . ' as proj', function ($join) {
                    $join->on('pp.project_id', '=', 'proj.id')
                        ->where('proj.status', '=', Project::STATUS_APPROVED)
                        ->where('proj.state', '=', Project::STATE_PROCESSING)
                        ->whereNull('proj.deleted_at');
                })
                ->groupBy('pp.id')
                ->select('pp.*')
                ->get();

        if ($projectPoints->isEmpty()) {
            return;
        }
        $noteColumns = array_diff(ProjectPoint::getAttrNote(), ['css_css_note']);
        foreach ($projectPoints as $projPoint) {
            foreach ($noteColumns as $col) {
                $projPoint->{$col} = null;
            }
            $projPoint->save([], ['not_employee' => true]);
        }
    }

    /**
     * remove programming language at attribute cost_productivity_proglang
     * @param int $id id of project
     * @param array $proglangs list id of programming langguage
     */
    public static function updateProductiveProgLang($id, $proglangs) {
        $projectPoint = ProjectPoint::findFromProject($id);
        $data = [];
        if ($projectPoint->cost_productivity_proglang) {
            $data = json_decode($projectPoint->cost_productivity_proglang, true);
            $oldProglangIds = array_keys($data);
            foreach ($data as $key => $value) {
                if (!in_array($key, $proglangs)) {
                    unset($data[$key]);
                }
            }
            foreach ($proglangs as $value) {
                if(!in_array($value, $oldProglangIds)) {
                    $data[$value] = [];
                }
            }
        } else {
            foreach ($proglangs as $value) {
                $data[$value] = [];
            }
        }
        $projectPoint->cost_productivity_proglang = json_encode($data);
        $projectPoint->save();
    }

}
