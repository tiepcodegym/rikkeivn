<?php

namespace Rikkei\Project\View;

use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Ot\Model\OtRegister;
use Rikkei\Project\Model\AssumptionConstrain;
use Rikkei\Project\Model\Communication;
use Rikkei\Project\Model\CriticalDependencie;
use Rikkei\Project\Model\DevicesExpense;
use Rikkei\Project\Model\ExternalInterface;
use Rikkei\Project\Model\MemberCommunication;
use Rikkei\Project\Model\ProjDeliverable;
use Rikkei\Project\Model\ProjPointBaseline;
use Rikkei\Project\Model\ProjPointFlat;
use Rikkei\Project\Model\ProjQuality;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjectChangeWorkOrder;
use Rikkei\Project\Model\ProjectLog;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\ProjectMeta;
use Rikkei\Project\Model\ProjectPoint;
use Rikkei\Project\Model\ProjectWOBase;
use Rikkei\Project\Model\Risk;
use Rikkei\Project\Model\Security;
use Rikkei\Project\Model\StageAndMilestone;
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\ToolAndInfrastructure;
use Rikkei\Project\Model\Training;
use Rikkei\Sales\Model\CssResult;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\PqaResponsibleTeam;
use Rikkei\Team\View\CheckpointPermission;
use Rikkei\Team\View\Permission;
use Illuminate\Support\Facades\DB;

/**
 * View ouput gender
 */
class View {

    /**
     * check permission for edit project
     *
     * @param type $project
     * @return boolean
     */
    public static function checkPermissionEdit()
    {
        if (Permission::getInstance()->isScopeCompany(null, 'project::project.edit') || Permission::getInstance()->isScopeTeam(null, 'project::project.edit')) {
            return true;
        }
        return false;
    }

    /*
     * get label team of project
     * @param array
     * @param array
     * @return string
     */

    public static function getLabelTeamOfProject($teamOfProject, $allTeam)
    {
        $result = '';
        $count = 0;
        foreach ($teamOfProject as $key => $team) {
            if (in_array($team['id'], $allTeam)) {
                if ($count == 0) {
                    $result .= trim($team['name'], ' ');
                } else {
                    $result .= ', ' . trim($team['name'], ' ');
                }
                $count ++;
            }
        }
        return trim($result);
    }

    /**
     * get all color point project
     *
     * @return array
     */
    public static function getPointColor()
    {
        return [
            ProjectPoint::COLOR_STATUS_GREY => URL::asset('project/images/point-grey.png'),
            ProjectPoint::COLOR_STATUS_WHITE => URL::asset('project/images/point-white.png'),
            ProjectPoint::COLOR_STATUS_BLUE => URL::asset('project/images/point-blue.png'),
            ProjectPoint::COLOR_STATUS_YELLOW => URL::asset('project/images/point-yellow.png'),
            ProjectPoint::COLOR_STATUS_RED => URL::asset('project/images/point-red.png'),
        ];
    }

    /**
     * Caculator color follow array color
     *
     * @param array $arrayColor
     * @return int
     */
    public static function calculatorTotalColor($arrayColor)
    {
        if (in_array(ProjectPoint::COLOR_STATUS_RED, $arrayColor)) {
            return ProjectPoint::COLOR_STATUS_RED;
        }
        if (in_array(ProjectPoint::COLOR_STATUS_YELLOW, $arrayColor)) {
            return ProjectPoint::COLOR_STATUS_YELLOW;
        }
        if (in_array(ProjectPoint::COLOR_STATUS_BLUE, $arrayColor)) {
            return ProjectPoint::COLOR_STATUS_BLUE;
        }
        return ProjectPoint::COLOR_STATUS_WHITE;
    }

    /**
     * get css class by point
     * @param type $point
     * @return string
     */
    public static function getColorCssClass($point)
    {
        switch ($point) {
            case ProjectPoint::COLOR_STATUS_YELLOW:
                return 'pp-bg-yellow';
            case ProjectPoint::COLOR_STATUS_RED:
                return 'pp-bg-red';
            default:
                return null;
        }
    }

    /*
     * get label leader of project
     * @param array
     * @param array
     * @return string
     */

    public static function getLabelLeaderOfProject($teamOfProject, $allTeam)
    {
        $result = '';
        $count = 0;
        foreach ($teamOfProject as $key => $team) {
            if (in_array($team['value'], $allTeam)) {
                if ($count == 0) {
                    $result .= trim($team['leader_name'], ' ');
                } else {
                    $result .= ', ' . trim($team['leader_name'], ' ');
                }
                $count ++;
            }
        }
        return trim($result);
    }

    /**
     * get qa account from file .env
     *
     * @return string|null
     */
    public static function getQAAccount()
    {
        $qaLead = CoreConfigData::getQAAccount();
        if (isset($qaLead[0])) {
            return $qaLead[0];
        }
        return null;
    }

    /**
     * get color status workorder
     * @param  int
     * @return string
     */
    public static function getColorStatusWorkOrder($status)
    {
        switch ($status) {
            case ProjectWOBase::STATUS_APPROVED:
                return ProjectWOBase::CLASS_COLOR_STATUS_APPROVED;
                break;
            case ProjectWOBase::STATUS_DRAFT:
                return ProjectWOBase::CLASS_COLOR_STATUS_DRAFT;
                break;
            case ProjectWOBase::STATUS_SUBMITTED:
                return ProjectWOBase::CLASS_COLOR_STATUS_SUBMITTED;
                break;
            case ProjectWOBase::STATUS_REVIEWED:
                return ProjectWOBase::CLASS_COLOR_STATUS_REVIEWED;
                break;
            case ProjectWOBase::STATUS_FEEDBACK:
                return ProjectWOBase::CLASS_COLOR_STATUS_FEEDBACK;
                break;
            case ProjectWOBase::STATUS_DRAFT_EDIT:
                return ProjectWOBase::CLASS_COLOR_STATUS_DRAFT_EDIT;
                break;
            case ProjectWOBase::STATUS_DRAFT_DELETE:
                return ProjectWOBase::CLASS_COLOR_STATUS_DRAFT_DELETE;
                break;
            case ProjectWOBase::STATUS_SUBMIITED_EDIT:
                return ProjectWOBase::CLASS_COLOR_STATUS_SUBMIITED_EDIT;
                break;
            case ProjectWOBase::STATUS_SUBMMITED_DELETE:
                return ProjectWOBase::CLASS_COLOR_STATUS_SUBMMITED_DELETE;
                break;
            case ProjectWOBase::STATUS_REVIEWED_EDIT:
                return ProjectWOBase::CLASS_COLOR_STATUS_REVIEWED_EDIT;
                break;
            case ProjectWOBase::STATUS_REVIEWED_DELETE:
                return ProjectWOBase::CLASS_COLOR_STATUS_REVIEWED_DELETE;
                break;
            case ProjectWOBase::STATUS_FEEDBACK_EDIT:
                return ProjectWOBase::CLASS_COLOR_STATUS_FEEDBACK_EDIT;
                break;
            case ProjectWOBase::STATUS_FEEDBACK_DELETE:
                return ProjectWOBase::CLASS_COLOR_STATUS_FEEDBACK_DELETE;
                break;
            case ProjectWOBase::STATUS_DELETE_APPROVED:
                return ProjectWOBase::CLASS_COLOR_STATUS_DELETE_APPROVED;
                break;
            default:
                break;
        }
    }

    /**
     * get project information
     *
     * @param object $project
     * @param object $projectPoint
     * @param object $projectMeta
     * @return array
     */
    public static function getProjectPointInfo(
        $project,
        $projectPoint = null,
        $projectMeta = null,
        $delivers = null,
        $cssTasksCC = null,
        $procTaskCompliance = null,
        $procReportNumber = null,
        $otDay = null,
        $option = []
    ) {
        //is baseline
        if (is_object($projectPoint) &&
                in_array(get_class($projectPoint), ['Rikkei\Project\Model\Project',
                    'Rikkei\Project\Model\ProjPointBaseline'])
        ) {
            $isBaseline = true;
            $keyCache = ProjPointBaseline::KEY_CACHE;
        } else {
            $isBaseline = false;
            $keyCache = ProjectPoint::KEY_CACHE;
        }

        if ($isBaseline) {
            if (get_class($projectPoint) == 'Rikkei\Project\Model\Project') {
                $projectPoint = new ProjPointBaseline();
                $projectPoint->setData($project->getAttributes());
            }
            $projectQuality = null;
            $cssTasksCCSplit = null;
        } else { //is point dashboard
            if ($projectPoint === null) {
                $projectPoint = ProjectPoint::findFromProject($project->id);
            }
            if ($projectMeta === null) {
                $projectMeta = $project->getProjectMeta();
            }
            if ($procReportNumber === null) {
                $procReportNumber = $projectPoint->getProcReportNumber();
            }
            $projectQuality = ProjQuality::getFollowProject($project->id);
            $cssTasksCCSplit = Task::getTypeCommendedCriticizedSplit($project->id);
        }
        $allColorStatus = self::getPointColor();
        $costPlanEffortCurrent = $projectPoint->getCostPlanEffortCurrent();
        $costPlanEffortTotal = $projectPoint->getCostPlanEffortTotal($projectQuality);
        $costPlanEffortTotalPoint = $projectPoint->getCostPlanEffortTotalPoint($costPlanEffortTotal, $project);
        $costActualEffortCurrent = $projectPoint->getCostActualEffortCurrent();
        $costApprovedProd = $projectPoint->getCostApprovedProd($projectQuality);
        $costResourceAllocation = $projectPoint->getCostResourceAllocation();
        $costResourceAllocationTotal = $projectPoint->getCostResourceAllocationTotal($costResourceAllocation);
        $costResourceAllocationCurrent = $projectPoint->getCostResourceAllocationCurrent($costResourceAllocation);
        //fixed costApprovedProd to costPlanEffortCurrent
        $costEffortEfftiveness = $projectPoint->getCostEffortEfftiveness($costPlanEffortCurrent, $costActualEffortCurrent);
        $costBillableEffort = $projectPoint->getCostBillableEffort($projectQuality);
        $costEffortEfftivenessPoint = $projectPoint->getCostEffortEfftivenessPoint($costEffortEfftiveness);
        $costBusyRate = $projectPoint->getCostBusyRate($costActualEffortCurrent, $costResourceAllocationCurrent);
        $costBusyRatePoint = $projectPoint->getCostBusyRatePoint($costBusyRate);
        $costEffortEfficiency2 = $projectPoint->getCostEffortEfficiency2($project, $costApprovedProd, $costResourceAllocationCurrent, $otDay);
        $costEffortEfficiency2Point = $projectPoint->getCostEffortEfficiency2Point($costEffortEfficiency2);

        $procCompliance = $projectPoint->getProcCompliance();
        $procReportYes = $projectPoint->getProcReportYes($procReportNumber);
        $procReportNo = $projectPoint->getProcReportNo($procReportNumber);
        $procReportDelayed = $projectPoint->getProcReportDelayed($procReportNumber);
        $procReport = $projectPoint->getProcReport($procReportNumber);
        $procReportPoint = $projectPoint->getProcReportPoint($procReport);

        $procCompliancePoint = $projectPoint->getProcCompliancePoint($procCompliance);

        $tlSchedule = $projectPoint->getTlSchedule();
        $tlDeliver = $projectPoint->getTlDeliver();

        $tlDeliverPoint = $projectPoint->getTlDeliverPoint($tlDeliver);
        $tlSchedulePoint = $projectPoint->getTlSchedulePoint($tlSchedule);

        $quaLeakageError = $projectPoint->getQuaLeakageError();
        $quaDefectError = $projectPoint->getQuaDefectError();
        $quaLeakage = $projectPoint->getQuaLeakage($quaLeakageError, $costResourceAllocationCurrent, $otDay);
        $quaDefect = $projectPoint->getQuaDefect($quaDefectError, $costResourceAllocationCurrent, $otDay);
        $countIssueCusComplaint = Task::countOfIssueByProjectId($project->id);

        $correction_cost_value = $projectPoint->getCorrectionCost();
        $correction_cost = $projectPoint->quaCorrectionCost($correction_cost_value, $costResourceAllocationCurrent, $otDay);

        $quaLeakagePoint = $projectPoint->getQuaLeakagePoint($quaLeakage);
        $quaDefectPoint = $projectPoint->getQuaDefectPoint($quaDefect);

        $valueMetricByCate = $projectPoint->getValueMetricByCate($project, $projectPoint);

        $cssCs = CssResult::getCssResultFirstByProjId($project->id);
        $cssCiNegative = $projectPoint->getCssCiNegative($cssTasksCCSplit);
        $cssCiPositive = $projectPoint->getCssCiPositive($cssTasksCCSplit);
        $cssCiPoint = null;
        //$projectPoint->getCssCiPoint($cssCiPositive, $cssCiNegative);

        $cssCsPoint = $projectPoint->getCssCsPoint($cssCs);

        if (($project->isTypeTrainingOfRD()) && !$isBaseline) {
            $projFlat = ProjPointFlat::findFlatFromProject($project->id);
            $costColor = $projFlat->cost;
            $quaColor = $projFlat->quality;
            $tlColor = $projFlat->tl;
            $procColor = $projFlat->proc;
            $cssColor = $projFlat->css;
            $summary = $projectPoint->getSummaryColor(
                $costColor, $quaColor, $tlColor, $procColor, $cssColor);
        } else {
            $costColor = $projectPoint->getCostColor($costEffortEfftiveness, $costBusyRate, [
                'project' => $project
            ]);
            $quaColor = $projectPoint->getQuaColor($quaLeakage, $quaDefect);
            $tlColor = $projectPoint->getTlColor($tlSchedule, $tlDeliver);
            $procColor = $projectPoint->getProcColor($procCompliance, $procReportPoint);
            $cssColor = $projectPoint->getCssColor($cssCs, $cssCiNegative);
            $summary = $projectPoint->getSummaryColor(
                $costColor, $quaColor, $tlColor, $procColor, $cssColor);
        }

        $pointTotal = $projectPoint->getTotalPoint([
            'costPlanEffortTotalPoint' => $costPlanEffortTotalPoint,
            'costEffortEfftivenessPoint' => $costEffortEfftivenessPoint,
            'costBusyRatePoint' => $costBusyRatePoint,
            'costEffortEfficiency2Point' => $costEffortEfficiency2Point,
            'cssCsPoint' => $cssCsPoint,
            'cssCiPoint' => $cssCiPoint,
            'tlDeliverPoint' => $tlDeliverPoint,
            'tlSchedulePoint' => $tlSchedulePoint,
            'quaDefectPoint' => $quaDefectPoint,
            'quaLeakagePoint' => $quaLeakagePoint,
            'procCompliancePoint' => $procCompliancePoint,
            'procReportPoint' => $procReportPoint
        ]);
        $pointInfo =  [
            'cost_billable_effort' => $costBillableEffort,
            'cost_approved_production' => $costApprovedProd,
            'cost_plan_effort_total' => $costPlanEffortTotal,
            'cost_plan_effort_total_point' => $costPlanEffortTotalPoint,
            'cost_plan_effort_current' => $costPlanEffortCurrent,
            'cost_resource_allocation_total' => $costResourceAllocationTotal,
            'cost_resource_allocation_current' => $costResourceAllocationCurrent,
            'cost_actual_effort' => $costActualEffortCurrent,
            'cost_effort_effectiveness' => $costEffortEfftiveness,
            'cost_effort_effectiveness_point' => $costEffortEfftivenessPoint,
            'cost_lcl' => $projectPoint->cost_lcl,
            'cost_target' => $projectPoint->cost_target,
            'cost_ucl' => $projectPoint->cost_ucl,
            'cost_effort_efficiency1' => $projectPoint->getCostEffortEfficiency1(),
            'cost_effort_efficiency2' => $costEffortEfficiency2,
            'cost_effort_efficiency2_point' => $costEffortEfficiency2Point,
            'cost_productivity' => $projectPoint->getCostProductivity($projectMeta),
            'cost_busy_rate' => $costBusyRate,
            'cost_busy_rate_point' => $costBusyRatePoint,
            'cost_busy_rate_lcl' => $projectPoint->cost_busy_rate_lcl,
            'cost_busy_rate_target' => $projectPoint->cost_busy_rate_target,
            'cost_busy_rate_ucl' => $projectPoint->cost_busy_rate_ucl,
            'cost_effort_efficiency_lcl' => $valueMetricByCate['cost_effort_efficiency_lcl'],
            'cost_effort_efficiency_target' => $valueMetricByCate['cost_effort_efficiency_target'],
            'cost_effort_efficiency_ucl' => $valueMetricByCate['cost_effort_efficiency_ucl'],
            'cost' => $costColor,
            'proc_compliance' => $procCompliance,
            'proc_compliance_point' => $procCompliancePoint,
            'proc_compliance_lcl' => $valueMetricByCate['proc_compliance_lcl'],
            'proc_compliance_target' => $valueMetricByCate['proc_compliance_target'],
            'proc_compliance_ucl' => $valueMetricByCate['proc_compliance_ucl'],
            'proc_report' => $procReport,
            'proc_report_point' => $procReportPoint,
            'proc_report_yes' => $procReportYes,
            'proc_report_no' => $procReportNo,
            'proc_report_delayed' => $procReportDelayed,
            'proc' => $procColor,
            'tl_schedule' => $tlSchedule,
            'tl_schedule_point' => $tlSchedulePoint,
            'tl_schedule_lcl' => $projectPoint->tl_schedule_lcl,
            'tl_schedule_target' => $projectPoint->tl_schedule_target,
            'tl_schedule_ucl' => $projectPoint->tl_schedule_ucl,
            'tl_deliver' => $tlDeliver,
            'tl_deliver_point' => $tlDeliverPoint,
            'tl_deliver_lcl' => $valueMetricByCate['tl_deliver_lcl'],
            'tl_deliver_target' => $valueMetricByCate['tl_deliver_target'],
            'tl_deliver_ucl' => $valueMetricByCate['tl_deliver_ucl'],
            'tl' => $tlColor,
            'qua_leakage_errors' => $quaLeakageError,
            'qua_leakage' => $quaLeakage,
            'qua_leakage_point' => $quaLeakagePoint,
            'qua_defect_errors' => $quaDefectError,
            'qua_defect' => $quaDefect,
            'qua_defect_point' => $quaDefectPoint,
            'quality' => $quaColor,
            'qua_leakage_lcl' => $valueMetricByCate['qua_leakage_lcl'],
            'qua_leakage_target' => $valueMetricByCate['qua_leakage_target'],
            'qua_leakage_ucl' => $valueMetricByCate['qua_leakage_ucl'],
            'qua_defect_lcl' => $valueMetricByCate['qua_defect_lcl'],
            'qua_defect_target' => $valueMetricByCate['qua_defect_target'],
            'qua_defect_ucl' => $valueMetricByCate['qua_defect_ucl'],
            'css_css' => $cssCs,
            'css_css_point' => $cssCsPoint,
            'css_ci_negative' => $cssCiNegative,
            'css_ci_positive' => $cssCiPositive,
            'css_ci' => $projectPoint->getCssCi($cssCiPositive, $cssCiNegative),
            'css_ci_point' => $cssCiPoint,
            'css_css_lcl' => $valueMetricByCate['css_css_lcl'],
            'css_css_target' => $valueMetricByCate['css_css_target'],
            'css_css_ucl' => $valueMetricByCate['css_css_ucl'],
            'css' => $cssColor,
            'summary' => $summary,
            'point_total' => $pointTotal,
            'project_evaluation' => $projectPoint->getProjectEvaluation($pointTotal),
            'cost_productivity_proglang' => $projectPoint->cost_productivity_proglang,
            //giá trị correction cost tạm thời chỉ xét cho 2 giá trị category development và maintenance
            'correct_cost_lcl' => $valueMetricByCate['correct_cost_lcl'],
            'correct_cost_target' => $valueMetricByCate['correct_cost_target'],
            'correct_cost_ucl' => $valueMetricByCate['correct_cost_ucl'],
            'correction_cost' => $correction_cost,
            'countIssueCusComplaint' => $countIssueCusComplaint ? $countIssueCusComplaint : 0,
        ];
        if (isset($option['content_color'])) {
            $pointInfo['cost_effort_effectiveness_color'] = self::getColorCssClass($projectPoint->getColor('cost_target', 'cost_ucl', $costEffortEfftiveness));
            $pointInfo['cost_busy_rate_color'] = self::getColorCssClass($projectPoint->getCostBusyRateColor($costBusyRate));
            $pointInfo['qua_leakage_color'] = self::getColorCssClass($projectPoint->getColor('qua_leakage_target', 'qua_leakage_ucl', $quaLeakage));
            $pointInfo['qua_defect_color'] = self::getColorCssClass($projectPoint->getQuaDefectColor($quaDefect, $quaDefectError));
            $pointInfo['tl_schedule_color'] = self::getColorCssClass($projectPoint->getColor('tl_schedule_target', 'tl_schedule_ucl', $tlSchedule));
            $pointInfo['tl_deliver_color'] = self::getColorCssClass($projectPoint->getColorReverse2('tl_deliver_target', 'tl_deliver_ucl', $tlDeliver));
            $pointInfo['proc_compliance_color'] = self::getColorCssClass($projectPoint->getColor('proc_compliance_target', 'proc_compliance_ucl', $procCompliance));
            $pointInfo['proc_report_color'] = self::getColorCssClass($projectPoint->getColorFollowPoint($procReportPoint));
            $pointInfo['css_css_color'] = self::getColorCssClass($projectPoint->getColorReverse('css_css_target', 'css_css_lcl', $cssCs));
            $pointInfo['css_ci_color'] = self::getColorCssClass($projectPoint->getCssCiColor($cssCiNegative));
        }
        return $pointInfo;
    }

    /**
     * get work MM
     *
     * @param string $startDate Y-m-d
     * @param string $endDate Y-m-d
     * @param int $type 1: MM, 2: MD
     * @return float
     */
    public static function getMM($startDate, $endDate, $type = 2, &$rerurnResult = [])
    {
        if (!is_object($startDate)) {
            $start = new Carbon($startDate);
        } else {
            $start = $startDate;
        }
        $start->startOfDay();
        if (!is_object($endDate)) {
            $end = new Carbon($endDate);
        } else {
            $end = clone $endDate;
        }
        $end->startOfDay();
        if ($type == 1) {
            return self::getMMEachMonth($start, $end, $rerurnResult);
        }
        $end->modify('+1 day');
        $interval = $end->diff($start);
        $days = $interval->days;

        // create an iterateable period of date (P1D equates to 1 day)
        $period = new DatePeriod($start, new DateInterval('P1D'), $end);
        // best stored as array, so you can add more than one
        $specialHolidays = CoreConfigData::getSpecialHolidays(2);
        $annualHolidays = CoreConfigData::getAnnualHolidays(2);
        $weekend = (array) Config::get('project.weekend');
        foreach ($period as $dt) {
            $curr = $dt->format('D'); //day of week: mon,...
            // for the updated question
            if (in_array($curr, $weekend) ||
                    in_array($dt->format('Y-m-d'), $specialHolidays) ||
                    in_array($dt->format('m-d'), $annualHolidays)
            ) {
                $days--;
            }
        }
        if ($days < 0) {
            $days = 0;
        }
        switch ($type) {
            case 2:  // get man day
                return $days;
            default: // get man month = man day / 20
                $mm = (float) Config::get('project.mm');
                if ($mm) {
                    return round($days / $mm, 2);
                }
                return 0;
        }
    }

    /**
     * get man month follow month
     *
     * @param Datetime $start
     * @param Datetime $end
     */
    public static function getMMEachMonth($start, $end, &$rerurnResult = [])
    {
        $rerurnResult['dayWorks'] = 0;
        $lastDayMonthOfEnddate = clone $end;
        $lastDayMonthOfEnddate->endOfMonth()->startOfDay();
        $lastDayMonthOfEnddate->modify('+1 day');
        $startDayMonthOfEnddate = clone $start;
        $startDayMonthOfEnddate->startOfMonth();
        $end->modify('+1 day');
        $periodMonth = new DatePeriod($startDayMonthOfEnddate, new DateInterval('P1M'), $lastDayMonthOfEnddate);
        $resultMM = 0;
        $startMonth = $start;
        $specialHolidays = CoreConfigData::getSpecialHolidays(2);
        $annualHolidays = CoreConfigData::getAnnualHolidays(2);
        $weekend = (array) Config::get('project.weekend');
        foreach ($periodMonth as $lastDayMonth) {
            $daysInMonth = cal_days_in_month(
                    CAL_GREGORIAN, (int) $lastDayMonth->format('m'), (int) $lastDayMonth->format('Y'));
            $lastDayMonth->endOfMonth()->startOfDay();
            $startOfDayMonth = clone $lastDayMonth;
            $startOfDayMonth->startOfMonth();
            $lastDayMonth->modify('+1 day');
            $periodDay = new DatePeriod($startOfDayMonth, new DateInterval('P1D'), $lastDayMonth);
            if ($end->diff($lastDayMonth)->invert) { // enddate > last date of month
                $daysWorks = $lastDayMonth->diff($startMonth)->days;
            } else {
                $daysWorks = $end->diff($startMonth)->days;
            }
            foreach ($periodDay as $dt) {
                $curr = $dt->format('D'); //day of week: mon,...
                $diffDtWidthStart = $dt->diff($start);
                $diffDtWidthEnd = $dt->diff($end);
                if (in_array($curr, $weekend) ||
                        in_array($dt->format('Y-m-d'), $specialHolidays) ||
                        in_array($dt->format('m-d'), $annualHolidays)
                ) {
                    // period in from start to end
                    if (($diffDtWidthStart->invert || $diffDtWidthStart->days == 0) &&
                            ($diffDtWidthEnd->invert == 0 && $diffDtWidthEnd->days != 0)
                    ) {
                        $daysWorks--;
                    }
                    $daysInMonth--;
                }
            }
            if ($daysWorks < 0) {
                $daysWorks = 0;
            }
            if ($daysInMonth < 0) {
                $daysInMonth = 0;
            }
            $rerurnResult['dayWorks'] += $daysWorks;
            $resultMM += round($daysWorks / $daysInMonth, 2);
            $startMonth = clone $lastDayMonth;
        }
        return $resultMM;
    }

    /**
     * check permission view project
     *
     * @param int|object $project
     * @return boolean
     */
    public static function isAccessViewProject($project, &$ownerTeam = [])
    {
        if (is_numeric($project)) {
            $project = Project::find($project);
        }
        if (!$project) {
            return false;
        }
        // check permission view
        if (Permission::getInstance()->isScopeCompany(null, 'project::dashboard')) {
            return true;
        } elseif (Permission::getInstance()->isScopeTeam(null, 'project::dashboard')) {
            $teamsProject = $project->getTeamIds();
            $teamsEmployee = Permission::getInstance()->isScopeTeam(null, 'project::dashboard');
            //Get teams child from employee's teams
            $teamsTemp = [];
            foreach ($teamsEmployee as $teamId) {
                $teamsTemp = array_merge($teamsTemp, CheckpointPermission::getTeamChild($teamId));
            }
            $teamsEmployee = array_unique($teamsTemp);
            $intersect = array_intersect($teamsEmployee, $teamsProject);
            if (count($intersect)) {
                $ownerTeam = $intersect;
                return true;
            }
        }
        //view self project
        $curEmp = Permission::getInstance()->getEmployee();
        return $project->getAccessAbleIds($curEmp->id)
                || static::isManagerCustomerOfProject($curEmp->id, $project->cust_contact_id);
    }

    public static function isManagerCustomerOfProject($empId, $cusId)
    {
        if (empty($cusId) || empty($empId)) {
            return false;
        }
        $customersManaged = \Rikkei\Sales\Model\Customer::listManagedByEmployee($empId);
        $customersId = $customersManaged->lists('id')->toArray();
        return in_array($cusId, $customersId);
    }

    /**
     * check permission view project
     *
     * @param int|object $project
     * @return boolean
     */
    /* public static function isAccessCreateProject($project)
      {
      if (is_numeric($project)) {
      $project = Project::find($project);
      }
      if (!$project) {
      return false;
      }
      // check permission view
      if (Permission::getInstance()->isScopeCompany(null, 'project::dashboard')) {
      return true;
      } elseif (Permission::getInstance()->isScopeTeam(null, 'project::dashboard')) {
      $teamsProject = $project->getTeamIds();
      $members = $project->getMemberIds();
      $teamsEmployee = Permission::getInstance()->getTeams();
      $intersect = array_intersect($teamsEmployee, $teamsProject);
      if (!count($intersect) &&
      !in_array(Permission::getInstance()->getEmployee()->id, $members)
      ) {
      return false;
      } else {
      return true;
      }
      } else { //view self project
      $members = $project->getMemberIds();
      if (!in_array(Permission::getInstance()->getEmployee()->id, $members)) {
      return false;
      } else {
      return true;
      }
      }
      return true;
      } */

    /**
     * check permission edit task of project
     *
     * @param object $project
     * @param int $typeTask
     * @param model $task
     * @return boolean
     */
    public static function isAccessEditTask(
    $project, $typeTask = null, $task = null
    )
    {
        if (is_numeric($project)) {
            $project = Project::find($project);
        }
        if (!$project) {
            return false;
        }
        // check permission edit task
        if (Permission::getInstance()->isScopeCompany(null, 'project::task.save')) {
            return true;
        } elseif (Permission::getInstance()->isScopeTeam(null, 'project::task.save')) {
            $teamsProject = $project->getTeamIds();
            $teamsEmployee = Permission::getInstance()->getTeams();
            $intersect = array_intersect($teamsEmployee, $teamsProject);
            if (count($intersect)) {
                return true;
            }
        }
        $allowParticipant = !in_array($typeTask, [Task::TYPE_COMMENDED, Task::TYPE_CRITICIZED]);
        //self project
        if ($task && $task->id && $task->isAssignOrCreatedBy($allowParticipant)) {
            return true;
        }
        $tasksToLeaderEdit = Project::checkIsLeaderToEditTask($project->id, auth()->id());
        if ($task && $task->id && count($tasksToLeaderEdit) > 0) {
            return true;
        }
        if ($task && $task->id && in_array($typeTask, [Task::TYPE_COMMENDED, Task::TYPE_CRITICIZED])) {
            if (Project::checkRelatersOfProject(Permission::getInstance()->getEmployee()->id, $project)) {
                return true;
            }
            return false;
        }
        $members = $project->getIdsEmployeeAccessViewProject();
        if (!in_array(Permission::getInstance()->getEmployee()->id, $members)) {
            return false;
        }
        if (in_array($typeTask, Task::getTypeIssues(true))) {
            return true;
        }
        $members = $project->getMemberTypes();
        $employeeCurrent = Permission::getInstance()->getEmployee();
        switch ($typeTask) {
            case Task::TYPE_WO: case Task::TYPE_SOURCE_SERVER:
                if ((isset($members[ProjectMember::TYPE_PQA]) &&
                        in_array($employeeCurrent->id, $members[ProjectMember::TYPE_PQA])) ||
                        (isset($members[ProjectMember::TYPE_QALEAD]) &&
                        in_array($employeeCurrent->id, $members[ProjectMember::TYPE_QALEAD])) ||
                        (isset($members[ProjectMember::TYPE_COO]) &&
                        in_array($employeeCurrent->id, $members[ProjectMember::TYPE_COO]))
                ) {
                    return true;
                }
                return false;
            case Task::TYPE_ISSUE:
                return true;
            case Task::TYPE_COMPLIANCE: //qa editable
                if ((isset($members[ProjectMember::TYPE_PQA]) &&
                        in_array($employeeCurrent->id, $members[ProjectMember::TYPE_PQA])) ||
                        (isset($members[ProjectMember::TYPE_SQA]) &&
                        in_array($employeeCurrent->id, $members[ProjectMember::TYPE_SQA]))
                ) {
                    return true;
                }
                return false;
            case Task::TYPE_COMMENDED: case Task::TYPE_CRITICIZED:
                //Creator and assignee only has permission edit
                return !($task && $task->id);
            case Task::TYPE_QUALITY_PLAN:
                if ((isset($members[ProjectMember::TYPE_PQA]) &&
                        in_array($employeeCurrent->id, $members[ProjectMember::TYPE_PQA])) ||
                        (isset($members[ProjectMember::TYPE_SQA]) &&
                        in_array($employeeCurrent->id, $members[ProjectMember::TYPE_SQA])) ||
                        (isset($members[ProjectMember::TYPE_PM]) &&
                        in_array($employeeCurrent->id, $members[ProjectMember::TYPE_PM])) ||
                        (isset($members[ProjectMember::TYPE_SUBPM]) &&
                        in_array($employeeCurrent->id, $members[ProjectMember::TYPE_SUBPM]))
                ) {
                    return true;
                }
                //Check sale has permission edit customer feedback
                if ($typeTask == Task::TYPE_COMMENDED) {
                    $saleOfProj = Project::getAllSaleOfProject($project->id);
                    if (!empty($saleOfProj) && in_array($employeeCurrent->id, $saleOfProj)) {
                        return true;
                    }
                }
                return false;
            default:
                return true;
        }
    }

    /**
     * check permission edit task of project
     *
     * @param object $project
     * @param int $typeTask
     * @param array $typeTaskAvailable
     * @return boolean
     */
    public static function getTaskTypeCreateAvailable($project)
    {
        if (is_numeric($project)) {
            $project = Project::find($project);
        }
        if (!$project) {
            return false;
        }
        $allType = Task::typeLabel();
        unset($allType[Task::TYPE_WO]);
        unset($allType[Task::TYPE_SOURCE_SERVER]);
        // check permission edit task
        if (Permission::getInstance()->isScopeCompany(null, 'project::task.save')) {
            $typeTaskAvailable = ['*'];
            return $allType;
        } elseif (Permission::getInstance()->isScopeTeam(null, 'project::task.save')) {
            $teamsProject = $project->getTeamIds();
            $teamsEmployee = Permission::getInstance()->getTeams();
            $intersect = array_intersect($teamsEmployee, $teamsProject);
            if (!count($intersect)) {
                return [];
            } else {
                return $allType;
            }
        }
        //self project
        $members = $project->getMemberIds();
        if (!in_array(Permission::getInstance()->getEmployee()->id, $members)) {
            return [];
        }
        $members = $project->getMemberTypes();
        $employeeCurrent = Permission::getInstance()->getEmployee();
        if ((isset($members[ProjectMember::TYPE_PQA]) &&
                in_array($employeeCurrent->id, $members[ProjectMember::TYPE_PQA])) ||
                (isset($members[ProjectMember::TYPE_SQA]) &&
                in_array($employeeCurrent->id, $members[ProjectMember::TYPE_SQA]))
        ) {
            return $allType;
        }
        if ((isset($members[ProjectMember::TYPE_PM]) &&
                in_array($employeeCurrent->id, $members[ProjectMember::TYPE_PM])) ||
                (isset($members[ProjectMember::TYPE_SUBPM]) &&
                in_array($employeeCurrent->id, $members[ProjectMember::TYPE_SUBPM]))
        ) {
            unset($allType[Task::TYPE_COMPLIANCE]);
            return $allType;
        }
        return Task::getTypeIssues();
    }

    /**
     * get hint point
     */
    public static function getHintPoint($key)
    {
        $hintPoint = [
            'cost_billable_value' => Lang::get('project::view.Effort notify to client, get from wordorder quality'),
            'cost_approved_production' => Lang::get('project::view.Approved production cost, get from wordorder quality'),
            'cost_plan_value' => Lang::get('project::view.Effort plan total, get from wordorder quality'),
            'cost_plan_effort_total_point' => Lang::get('project::view.Follow Plan Effort - total (MM): <10: 0.5, <=10-<20: 1, <=20-<30: 2, >=30: 3'),
            'cost_plan_value_current' => Lang::get('project::view.Effort plan current, PM fill, this value '
                    . '< plan effort total, if wo have been approved, but this value is null then cost color is red'),
            'cost_resource_allocation_total' => Lang::get('project::view.Follow total member in project'),
            'cost_resource_allocation_current' => Lang::get('project::view.Follow member in project till now'),
            'cost_actual_effort_current' => Lang::get('project::view.Actual effort till now, PM fill, if wo have been approved, but this value is null then cost color is red'),
            'cost_effort_efftiveness' => Lang::get('project::view.cost_effort_efftiveness'),
            'cost_effort_efftiveness_point' => Lang::get('project::view.Follow "Effort Effectiveness": null: 1, '
                    . '<=80: 3, 80-<=100: 2, 100-<=110: 1, 110-<=120: -1, 120-<=130: -2, '
                    . '>130: -3'),
            'cost_effort_efficiency1' => Lang::get('project::view.% effort resource: Actual Effort / Resource Allocation (current)'),
            'cost_effort_efficiency2' => Lang::get('project::view.cost_effort_efficiency2'),
            'cost_effort_efficiency2_point' => Lang::get('project::view.Follow Effort Efficiency: <50: -2, =50-<70: -1, =70-<80: 0.5, =80-<90: 1, >=90: 2'),
            'cost_busy_rate' => Lang::get('project::view.Actual Effort / Calendar Effort - current'),
            'cost_busy_rate_point' => Lang::get('project::view.Follow Busy rate: <70: -2, =70-<80: -1, =80-<90: 1, =90-<110: 2, =110-<120: 1, =120-<140: -1, >140: -2'),
            'cost_productivity' => Lang::get('project::view.Line of code (current) / actual Effort'),
            'proc_compliance' => Lang::get('project::view.Number process none compliance'),
            'proc_compliance_point' => Lang::get('project::view.Follow process none compliance: 0: 3, =1: 2, =2: 1, =3: 0, =4: -1, =5: -2, >5: -3'),
            'proc_report' => Lang::get('project::view.Report yes: +0.5; Report no: -1; Report delayed: -0.5; Calculated in weekly order'),
            'proc_report_point' => Lang::get('project::view.Report yes: +0.5; Report no: -1; Report delayed: -0.5; Calculated in weekly order; -2 <= point <= 2'),
            'proc_report_yes' => Lang::get('project::view.Report on time'),
            'proc_report_delayed' => Lang::get('project::view.Report delayed'),
            'proc_report_no' => Lang::get('project::view.None report'),
            'tl_schedule' => Lang::get('project::view.Number days slower than schedule, PM fill'),
            'tl_schedule_point' => Lang::get('project::view.Follow late schedule value: null: 2, 0: 2, 0-<=1: 1, 1-<=2: -1, >2: -2'),
            'tl_deliver' => Lang::get('project::view.Total deliverable on time / Total deliver till now (%)'),
            'tl_deliver_point' => Lang::get('project::view.Follow deliver value: <=40: -3, 40-<=55: -2, 55-<70: -1, =70: 0, 70-<=85: 1, 85-<100: 2, 100: 3'),
            'qua_leakage_errors' => Lang::get('project::view.Number bug that customer found after release'),
            'qua_leakage' => Lang::get('project::view.Leakage error / Defect error'),
            'qua_leakage_point' => Lang::get('project::view.Follow Leakage value: null: 3, <=3: 3, 3-<=5: 2, 5-<=7: 1, 7-<=9: 0.5, 9-<=11: -1, 11-<=13: -2, >13: -3'),
            'qua_defect_errors' => Lang::get('project::view.Total bug of project'),
            'qua_defect' => Lang::get('project::view.Defect error / Dev team effort (MD), if it exceeds the first quality gate actual date that this value < 1 then reporting yellow'),
            'qua_defect_point' => Lang::get('project::view.Follow Defect rate value: null: 2, <=1: 2, 1-<=3: 1, 3-<=5: -1, >5: -2'),
            'qua_it_st_defect_errors' => Lang::get("project::view.The total number of IT / ST bugs, this field applied to the base project and not use Rikkei's redmine. Using calculate project reward"),
            'css_cs' => Lang::get('project::view.guide css_css'),
            'css_cs_point' => Lang::get('project::view.Follow Customer satisfactions value: null: 0, '
                    . '90-<=100: 3, 80-<=90: 2, 70-<=80: 1, 60-<=70: 0.5, 50-<=60: -1, <=50: -2'),
            'css_ci' => Lang::get('project::view.Negative + Positive'),
            'css_ci_point' => Lang::get('project::view.Positive - Negative (max: 2, min: -2)'),
            'css_ci_negative' => Lang::get('project::view.Point criticized of customer'),
            'css_ci_positive' => Lang::get('project::view.Point commended of customer'),
            'total_point' => Lang::get('project::view.Effort Effectiveness + Customer Satisfation + '
                    . 'Deliverable + Leakage + Process None Compliance + Project Reports'),
            'project_evaluation' => Lang::get('project::view.project_point_guide_evaluation')
        ];
        if (isset($hintPoint[$key])) {
            return $hintPoint[$key];
        }
        return null;
    }

    /**
     * get first, last and now day of week
     *
     * @param Datetime $now
     * @return array
     */
    public static function getFirstLastDayOfWeek($now = null)
    {
        if (!$now) {
            $now = Carbon::now();
        }
        $numberInWeekCurrent = $now->format("w");
        if (!$numberInWeekCurrent) {
            $numberInWeekCurrent = 7;
        }
        $firstWeek = clone $now;
        $lastWeek = clone $now;
        $numberFirst = $numberInWeekCurrent - 1;
        $numberLast = 7 - $numberInWeekCurrent;
        $firstWeek->modify('-' . $numberFirst . ' days')
                ->setTime(0, 0, 0);
        $lastWeek->modify('+' . $numberLast . ' days')
                ->setTime(23, 59, 59);
        return [
            $now, $firstWeek, $lastWeek
        ];
    }

    /**
     * get first, last and now day of week
     *
     * @param Datetime $now
     * @return array
     */
    public static function getFirstLastDayOfLastWeek($now = null)
    {
        if (!$now) {
            $now = Carbon::now();
        }
        $weekCurrent = $now->format('W');
        $yearCurrent = $now->format('Y');
        $dateLastWeekMon = clone $now;
        $dateLastWeekSun = clone $now;
        $dateLastWeekMon->setISODate($yearCurrent, $weekCurrent - 1, 1)
                ->setTime(0, 0, 0);
        $dateLastWeekSun->setISODate($yearCurrent, $weekCurrent - 1, 7)
                ->setTime(23, 59, 59);
        return [
            $now, $dateLastWeekMon, $dateLastWeekSun
        ];
    }

    /**
     * get week list
     *
     * @param Datetime $time
     * @param int $limit
     * @return array
     */
    public static function getWeekList($time = null, $limit = 1)
    {
        $current = Carbon::now();
        if (!$time) {
            $time = clone $current;
            $weekCurrent = $time->format('W');
        } else {
            $weekCurrent = $current->format('W');
        }
        $results = [];
        $numberInWeekYear = $time->format("W");
        $year = $time->format('Y');

        $start = $numberInWeekYear - $limit;
        $end = $numberInWeekYear + $limit;
        $choose = $numberInWeekYear;
        if ($start === 0) {
            ++$year;
        }
        for ($i = $start; $i <= $end; $i++) {
            $time->setISODate($year, $i, 1);
            $startWeek = clone $time;
            $time->setISODate($year, $i, 7);
            $endWeek = clone $time;
            $yearFirst = $time->format('Y');
            $weekFirst = $time->format('W');
            $diffLastCurrent = $time->diff($current);
            if ($diffLastCurrent->invert) {
                $url = URL::route('project::dashboard', ['bl' => 1]);
                $textAfter = ''; // Lang::get('project::view.(cur)');
            } else {
                $url = URL::route('project::point.baseline', ['slug' => $yearFirst . '-' . $weekFirst, 'bl' => 1]);
                $textAfter = '';
            }
            $results[$i] = [
                'value' => $yearFirst . '-' . $weekFirst,
                'label' => self::formatPeriodDate($startWeek, $endWeek) . $textAfter,
                'url' => $url,
                'woy' => $weekFirst
            ];
            if ($diffLastCurrent->invert) {
                break;
            }
        }
        return ['list' => $results,
            'choose' => $choose,
            'now' => [
                'year' => $current->format('Y'),
                'week' => $weekCurrent,
                'text' => $current->format('Y') . '-' . $weekCurrent
            ]
        ];
    }

    /**
     * get week list
     *      [prev, current, next] baseline show
     * @param object $project
     * @param object $projectBaseline
     * @return array
     */
    public static function getWeekListBaselineDetail($project, $projectBaseline = null)
    {
        $current = Carbon::now();
        $baselinePrevId = $baselineNextId = null;
        if ($projectBaseline) {  // view in baseline
            $baselineCur = Carbon::parse($projectBaseline->created_at);
            $baselineNext = ProjPointBaseline::getItemCreatedAtGt($project, $projectBaseline->created_at, true);
            $baselinePrev = ProjPointBaseline::getItemCreatedAtLt($project, $projectBaseline->created_at);
            if (!$baselineNext) {
                $baselineNext = true;
            } else {
                $baselineNextId = $baselineNext->id;
                $baselineNext = Carbon::parse($baselineNext->created_at);
            }
            if ($baselinePrev) {
                $baselinePrevId = $baselinePrev->id;
                $baselinePrev = Carbon::parse($baselinePrev->created_at);
            }
        } else { // view in dashboard
            $baselineCur = clone $current;
            $baselineTimeCur = CoreView::getDateLastWeek($current, 7);
            $baselineNext = null;
            $baselinePrev = ProjPointBaseline::getItemCreatedAtLt($project, $baselineTimeCur->format('Y-m-d'));
            if ($baselinePrev) {
                $baselinePrevId = $baselinePrev->id;
                $baselinePrev = Carbon::parse($baselinePrev->created_at);
            }
        }
        if (!$baselineNext && !$baselinePrev) {
            return null;
        }
        $results = [
            0 => null, // prev
            1 => null, // current
            2 => null, // next
        ];
        //baseline prev
        if ($baselinePrev) {
            $baselinePrevStartWeek = clone $baselinePrev;
            $baselinePrevStartWeek->startOfWeek();
            $baselinePrevEndWeek = clone $baselinePrev;
            $baselinePrevEndWeek->endOfWeek();
            $results[0] = [
                'value' => $baselinePrev->format('Y') . '-' . $baselinePrev->format('W'),
                'label' => self::formatPeriodDate($baselinePrevStartWeek, $baselinePrevEndWeek),
                'id' => $baselinePrevId,
                'url' => URL::route('project::point.baseline.detail', ['id' => $baselinePrevId]),
                'woy' => $baselinePrev->format('W')
            ];
        }

        // view current
        $baselinePrevStartWeek = clone $baselineCur;
        $baselinePrevStartWeek->startOfWeek();
        $baselinePrevEndWeek = clone $baselineCur;
        $baselinePrevEndWeek->endOfWeek();
        $results[1] = [
            'label' => self::formatPeriodDate($baselinePrevStartWeek, $baselinePrevEndWeek),
            'id' => null,
            'url' => URL::route('project::point.edit', ['id' => $project->id])
        ];

        if ($baselineNext === true) {
            $currentStartWeek = clone $current;
            $currentEndtWeek = clone $current;
            $currentStartWeek->startOfWeek();
            $currentEndtWeek->startOfWeek();
            $results[2] = [
                'value' => $currentStartWeek->format('Y') . '-' . $currentEndtWeek->format('W'),
                'label' => self::formatPeriodDate($currentStartWeek, $currentEndtWeek),
                'id' => null,
                'url' => URL::route('project::point.edit', ['id' => $project->id]),
                'woy' => $currentStartWeek->format('W')
            ];
        } elseif ($baselineNext) {
            $baselinePrevStartWeek = clone $baselineNext;
            $baselinePrevStartWeek->startOfWeek();
            $baselinePrevEndWeek = clone $baselineNext;
            $baselinePrevEndWeek->endOfWeek();
            $results[2] = [
                'value' => $baselinePrevStartWeek->format('Y') . '-' . $baselinePrevStartWeek->format('W'),
                'label' => self::formatPeriodDate($baselinePrevStartWeek, $baselinePrevEndWeek),
                'id' => $baselineNextId,
                'url' => URL::route('project::point.baseline.detail', ['id' => $baselineNextId]),
                'woy' => $baselinePrevStartWeek->format('W')
            ];
        }
        return $results;
    }

    /**
     * get datetime of report ontime
     *
     * @param datetime $now
     * @param array $timeReport
     * @return datetime
     */
    public static function getDateProjectOntime($now = null, $timeReport = null)
    {
        if (!$now) {
            $now = Carbon::now();
        }
        if (!$timeReport) {
            $timeReport = CoreConfigData::getProjectReportYesTime();
        }
        if (!$timeReport) {
            return null;
        }
        if (!$timeReport[0]) {
            $timeReport[0] = 7;
        }
        $numberInWeekCurrent = $now->format("w");
        if (!$numberInWeekCurrent) {
            $numberInWeekCurrent = 7;
        }
        $timeReportTime = explode(':', $timeReport[1]);
        $nowTemp = clone $now;
        return $nowTemp->modify(($timeReport[0] - $numberInWeekCurrent) . ' days')
                        ->setTime($timeReportTime[0], $timeReportTime[1], $timeReportTime[2]);
    }

    /**
     * check permission edit workorder
     * @param  object
     * @return boolean
     */
    public static function checkPermissionEditWorkorder(
    $project, $routeName = 'project::project.add_critical_dependencies', $checkPQA = false
    )
    {
        if (!is_object($project)) {
            $project = Project::find($project);
        }
        $permissionEdit = false;
        $permissionEditPM = false;
        $permissionEditSubPM = false;
        $permissionEditQA = false;
        $permissionEditSale = false;
        $permissionEditPqa = false;
        if (!$project) {
            return [
                'permissionEidt' => $permissionEdit,
                'persissionEditPM' => $permissionEditPM,
                'permissionEditQA' => $permissionEditQA,
                'permissionEditSubPM' => $permissionEditSubPM,
                'permissionEditSale' => $permissionEditSale,
                'permissionEditPqa' => $permissionEditPqa,
            ];
        }
        //check permission edit
        if (Permission::getInstance()->isScopeCompany(null, $routeName)) {
            $permissionEdit = true;
            $permissionEditPM = true;
            $permissionEditQA = true;
            $permissionEditSubPM = true;
            $permissionEditSale = true;
            $permissionEditPqa = true;
        } elseif (Permission::getInstance()->isScopeTeam(null, $routeName) &&
                !$checkPQA
        ) {
            $teamsProject = $project->getTeamIds();
            $teamsEmployee = Permission::getInstance()->getTeams();
            $intersect = array_intersect($teamsEmployee, $teamsProject);
            if (count($intersect)) {
                $permissionEdit = true;
                $permissionEditPM = true;
                $permissionEditQA = true;
                $permissionEditSubPM = true;
                $permissionEditSale = true;
            }
        }
        //edit self project
        $members = $project->getMemberTypes();
        $employeeCurrent = Permission::getInstance()->getEmployee();
        $teamInCharge = Project::getTeamInChargeOfProject($project->id);
        $pqaLeaderTeam = PqaResponsibleTeam::getEmpIdResponsibleTeamAsTeamId($teamInCharge->team_id);
        $saleOfProj = Project::getAllSaleOfProject($project->id);
        if (isset($members[ProjectMember::TYPE_PM]) &&
                in_array($employeeCurrent->id, $members[ProjectMember::TYPE_PM])
        ) {
            $permissionEditPM = true;
        }
        if (
                (isset($members[ProjectMember::TYPE_PQA]) &&
                in_array($employeeCurrent->id, $members[ProjectMember::TYPE_PQA])) ||
                (isset($members[ProjectMember::TYPE_SQA]) &&
                in_array($employeeCurrent->id, $members[ProjectMember::TYPE_SQA])
                )
        ) {
            $permissionEditQA = true;
        }
        if (isset($members[ProjectMember::TYPE_SUBPM]) &&
                in_array($employeeCurrent->id, $members[ProjectMember::TYPE_SUBPM])
        ) {
            $permissionEditSubPM = true;
        }
        if ((!empty($saleOfProj) && in_array($employeeCurrent->id, $saleOfProj))
                || static::isManagerCustomerOfProject($employeeCurrent->id, $project->cust_contact_id)) {
            $permissionEditSale = true;
        }

        if ((isset($pqaLeaderTeam) && (in_array($employeeCurrent->id, $pqaLeaderTeam)))) {
            $permissionEditPqa = true;
        }
        $permissionEdit = $permissionEditPM || $permissionEditQA || $permissionEditSubPM;
        $permissionEditNote = $permissionEdit;

        $isCoo = self::isCoo();
        if ($isCoo) {
            $permissionEditPM = true;
            $permissionEditSubPM = true;
        } else {
            if ($project->state == Project::STATE_CLOSED || $project->state == Project::STATE_REJECT) {
                $permissionEditPM = false;
                $permissionEditSubPM = false;
            }
        }
        return [
            'permissionEidt' => $permissionEdit,
            'persissionEditPM' => $permissionEditPM,
            'permissionEditQA' => $permissionEditQA,
            'permissionEditNote' => $permissionEditNote,
            'permissionEditSubPM' => $permissionEditSubPM,
            'permissionEditSale' => $permissionEditSale,
            'permissionEditPqa' => $permissionEditPqa,
        ];
    }

    /**
     * compare two array
     *
     * @param array $changedData
     * @param array $originData
     * @return array
     */
    public static function diffArray(&$changedData, &$originData, $ignoreNull = true)
    {
        $result = [];
        $keyChnaged = array_keys($changedData);
        foreach ($keyChnaged as $key) {
            if ($ignoreNull) {
                if (!isset($originData[$key])) {
                    continue;
                }
            } else {
                if (!array_key_exists($key, $originData) && !array_key_exists($key, $changedData)) {
                    continue;
                }
                if (!array_key_exists($key, $originData) || !$originData[$key]) {
                    $originData[$key] = 'NULL';
                }
                if (!array_key_exists($key, $changedData) || !$changedData[$key]) {
                    $changedData[$key] = 'NULL';
                }
            }
            if ($changedData[$key] != $originData[$key]) {
                $result[$key] = $changedData[$key];
            }
        }
        return $result;
    }

    /**
     * check access change task wo
     *
     * @param type $project
     * @param type $assignees
     * @return boolean
     */
    public static function isAccessTaskWo($project, $assignees = null)
    {
        $permission = Permission::getInstance();
        if ($permission->isScopeCompany(null, 'project::task.save')) {
            return true;
        }
        if ($assignees == null) {
            $assignees = $project->getMemberAccessWO();
        }
        if (!$assignees || !isset($assignees['id'])) {
            return false;
        }
        $memberIds = $assignees['id'];
        if (in_array($permission->getEmployee()->id, $memberIds)) {
            return true;
        }
        return false;
    }

    /**
     * check submitable wo
     *
     * @param int $projectId
     * @return boolean
     */
    public static function checkSubmitWorkOrder($projectId)
    {
        $taskWOApproved = Task::getTaskWaitingApproveByType($projectId, Task::TYPE_WO);
        if ($taskWOApproved && $taskWOApproved->status == Task::STATUS_FEEDBACK) {
            return true;
        }
        $project = Project::getProjectById($projectId);
        if ($project) {
            $projectDraft = $project->projectChild;
            if (!count($projectDraft)) {
                $projectDraft = $project;
            }
        }
        if (config('project.workorder_approved.critical_dependencies')) {
            if (CriticalDependencie::checkStatusSubmit($projectId)) {
                return true;
            }
        }
        if (config('project.workorder_approved.assumption_constrain')) {
            if (AssumptionConstrain::checkStatusSubmit($projectId)) {
                return true;
            }
        }
        if (config('project.workorder_approved.risk')) {
            if (Risk::checkStatusSubmit($projectId)) {
                return true;
            }
        }
        if (config('project.workorder_approved.stage_and_milestone')) {
            if (StageAndMilestone::checkStatusSubmit($projectId)) {
                return true;
            }
            if (isset($projectDraft)) {
                if (StageAndMilestone::checkErrorTime($projectId, $projectDraft)) {
                    return true;
                }
            }
        }
        if (config('project.workorder_approved.training')) {
            if (Training::checkStatusSubmit($projectId)) {
                return true;
            }
        }
        if (config('project.workorder_approved.external_interface')) {
            if (ExternalInterface::checkStatusSubmit($projectId)) {
                return true;
            }
        }
        if (config('project.workorder_approved.tool_and_infrastructure')) {
            if (ToolAndInfrastructure::checkStatusSubmit($projectId)) {
                return true;
            }
        }
        if (config('project.workorder_approved.communication')) {
            if (Communication::checkStatusSubmit($projectId)) {
                return true;
            }
        }
        if (config('project.workorder_approved.deliverable')) {
            if (ProjDeliverable::checkStatusSubmit($projectId)) {
                return true;
            }
            if (isset($projectDraft)) {
                if (ProjDeliverable::checkErrorTime($projectId, $projectDraft)) {
                    return true;
                }
            }
        }
        if (config('project.workorder_approved.quality')) {
            if (ProjQuality::checkStatusSubmit($projectId)) {
                return true;
            }
        }
        if (config('project.workorder_approved.project_member')) {
            if (ProjectMember::checkStatusSubmit($projectId)) {
                return true;
            }
            if (isset($projectDraft)) {
                if (ProjectMember::checkErrorTime($projectId, $projectDraft)) {
                    return true;
                }
            }
        }
        if (Project::checkStatuSubmit($projectId)) {
            return true;
        }
        if (config('project.workorder_approved.devices_expenses')) {
            if (DevicesExpense::checkStatusSubmit($projectId)) {
                return true;
            }
        }
        return false;

        /* if ((isset($statusCritical) && $statusCritical) ||
          (isset($statusAssumption) && $statusAssumption) ||
          (isset($statusRisk) && $statusRisk) ||
          (isset($statusStage) && $statusStage) ||
          (isset($statusTraning) && $statusTraning) ||
          (isset($statusExternal) && $statusExternal) ||
          (isset($statusTool) && $statusTool) ||
          (isset($statusCommunication) && $statusCommunication) ||
          (isset($statusDeliverable) && $statusDeliverable) ||
          (isset($statusQuality) && $statusQuality) ||
          (isset($statusProjectMember) && $statusProjectMember) ||
          (isset($errorTimeStage) && $errorTimeStage) ||
          (isset($errorTimeProjectMember) && $errorTimeProjectMember) ||
          (isset($errorTimeDeliverable) && $errorTimeDeliverable) ||
          $statusProject) {
          return true;
          }
          return false; */
    }

    /**
     * check member is coo
     * @return boolean
     */
    public static function isCoo()
    {
        return Permission::getInstance()->isCOOAccount();
    }

    /**
     *  check change and insert project log
     *  @param int
     *  @param array
     *  @param array
     *  @param array
     */
    public static function checkChangeAndInsertProjectLog($projectId, $arrayFill, $attributes, $original)
    {
        foreach ($arrayFill as $key => $value) {
            if (!isset($attributes[$key]) ||
                    !isset($original[$key])) {
                continue;
            }
            if ($attributes[$key] == $original[$key]) {
                continue;
            }
            $status = false;
            if ($key == 'start_at') {
                if ($attributes[$key] . ' 00:00:00' != $original[$key]) {
                    $status = true;
                }
            } else {
                $status = true;
            }
            if ($status) {
                $nameCreated = self::getNickName();
                $textEdit = Lang::get('project::view.Updated');
                $content = $textEdit . ' ' . $value;
                ProjectLog::insertProjectLog($projectId, $content, $nameCreated);
            }
        }
    }

    /**
     * get nick name
     * @return string
     */
    public static function getNickName()
    {
        return Permission::getInstance()->getEmployee()->email;
    }

    /**
     * insert project log wO
     * @param  int
     * @param  string
     * @param  string
     * @param  array
     * @param  array
     */
    public static function insertProjectLogWO($projectId, $statusText, $labelElement, $attributes = null, $original = null)
    {
        $status = false;
        if (!$attributes && !$original) {
            $status = true;
        } else {
            if (array_diff($attributes, $original)) {
                $status = true;
            }
        }

        if ($status) {
            $nameCreated = self::getNickName();
            $content = $statusText . ' ' . $labelElement;
            ProjectLog::insertProjectLog($projectId, $content, $nameCreated);
        }
    }

    public static function insertProjectLogChangeWO($status, $projectId)
    {
        if ($status == Task::STATUS_APPROVED) {
            $content = 'Approved workorder v';
        }
        if ($status == Task::STATUS_FEEDBACK) {
            $content = 'Feedback workorder v';
        }
        if ($status == Task::STATUS_REVIEWED) {
            $content = 'Reviewed workorder v';
        }
        if ($status == Task::STATUS_SUBMITTED) {
            $content = 'Added workorder v';
        }
        if (isset($content)) {
            $versionWorkorder = ProjectChangeWorkOrder::getVersionLastest($projectId);
            $content .= $versionWorkorder;
            $nameCreated = self::getNickName();
            ProjectLog::insertProjectLog($projectId, $content, $nameCreated);
        }
    }

    /**
     * check permission edit task of project
     *
     * @param object $project
     * @param int $typeTask
     * @param array $typeTaskAvailable
     * @return boolean
     */
    public static function permissionInProject($project)
    {
        if (Permission::getInstance()->isScopeCompany(null, 'project-access::task.approve.save')) {
            return true;
        }
        //self project
        $members = $project->getMemberIds();
        if (!in_array(Permission::getInstance()->getEmployee()->id, $members)) {
            return false;
        }
        $members = $project->getMemberTypes();
        $employeeCurrent = Permission::getInstance()->getEmployee();
        if ((isset($members[ProjectMember::TYPE_COO]) &&
                in_array($employeeCurrent->id, $members[ProjectMember::TYPE_COO]))) {
            return ProjectMember::TYPE_COO;
        }
        if ((isset($members[ProjectMember::TYPE_SQA]) &&
                in_array($employeeCurrent->id, $members[ProjectMember::TYPE_SQA]))) {
            return ProjectMember::TYPE_SQA;
        }
        if ((isset($members[ProjectMember::TYPE_PQA]) &&
                in_array($employeeCurrent->id, $members[ProjectMember::TYPE_PQA]))) {
            return ProjectMember::TYPE_PQA;
        }
        if ((isset($members[ProjectMember::TYPE_QALEAD]) &&
                in_array($employeeCurrent->id, $members[ProjectMember::TYPE_QALEAD]))) {
            return ProjectMember::TYPE_QALEAD;
        }
        if ((isset($members[ProjectMember::TYPE_PM]) &&
                in_array($employeeCurrent->id, $members[ProjectMember::TYPE_PM]))) {
            return ProjectMember::TYPE_PM;
        }
        if ((isset($members[ProjectMember::TYPE_SUBPM]) &&
                in_array($employeeCurrent->id, $members[ProjectMember::TYPE_SUBPM]))) {
            return ProjectMember::TYPE_SUBPM;
        }
        if ((isset($members[ProjectMember::TYPE_DEV]) &&
                in_array($employeeCurrent->id, $members[ProjectMember::TYPE_DEV]))) {
            return ProjectMember::TYPE_DEV;
        }
        if ((isset($members[ProjectMember::TYPE_TEAM_LEADER]) &&
                in_array($employeeCurrent->id, $members[ProjectMember::TYPE_TEAM_LEADER]))) {
            return ProjectMember::TYPE_TEAM_LEADER;
        }
        if ((isset($members[ProjectMember::TYPE_BRSE]) &&
                in_array($employeeCurrent->id, $members[ProjectMember::TYPE_BRSE]))) {
            return ProjectMember::TYPE_BRSE;
        }
        if ((isset($members[ProjectMember::TYPE_COMTOR]) &&
                in_array($employeeCurrent->id, $members[ProjectMember::TYPE_COMTOR]))) {
            return ProjectMember::TYPE_COMTOR;
        }
    }

    /**
     *  check change source server
     *  @param int
     *  @param array
     *  @param array
     *  @param array
     */
    public static function checkChangeSourceServer($arrayFill, $attributes, $original)
    {
        $arrayChange = [];
        if (!$original) {
            foreach ($arrayFill as $key => $value) {
                $arrayChange[$key] = true;
            }
        } else {
            foreach ($arrayFill as $key => $value) {
                if ($attributes[$key] != $original[$key]) {
                    $arrayChange[$key] = $value;
                } else {
                    $arrayChang[$key] = $value;
                }
            }
        }
        return $arrayChange;
    }

    /**
     * check employee is leader
     * @param int
     * @return int|null
     */
    public static function isLeader($employeeId)
    {
        if (!$employeeId) {
            return null;
        } else {
            $employee = Employee::find($employeeId);
            if (!$employee) {
                return null;
            }
            if ($employee->isLeader()) {
                return $employeeId;
            }
            return null;
        }
    }

    /**
     * get values with keys
     *
     * @param array $keys
     * @param array $values
     * @return array
     */
    public static function getValuesFollowKey($keys, $values, $returnKey = false)
    {
        $keyStatus = array_keys($values);
        $result = [];
        $resultKey = [];
        foreach ($keys as $key) {
            if (in_array($key, $keyStatus)) {
                $result[$key] = $values[$key];
                $resultKey[] = $key;
            }
        }
        if ($returnKey) {
            return $resultKey;
        }
        return $result;
    }

    /**
     * check length string less than $length
     *
     * @param sting $note
     * @param int $length
     * @return boolean
     */
    public static function isLtLength($note, $length = 50)
    {
        if (!$note) {
            return true;
        }
        //have break line
        if (preg_match("/\r\n|\n|\r/", $note)) {
            return false;
        }
        $lenString = Str::length($note);
        if ($lenString > $length) {
            return false;
        }
        return true;
    }

    /**
     *  get duration of project
     *  @param int
     *  @return string
     */
    public static function getDurationProject($project)
    {
        $projectDraft = Project::where('parent_id', $project->id)
                ->where('status', '!=', Project::STATUS_APPROVED)
                ->first();

        $duration = '';
        if ($project->end_at) {
            $duration .= Carbon::parse($project->start_at)->diffInDays(Carbon::parse($project->end_at));
        }
        if ($projectDraft) {
            $durationDraft = Carbon::parse($projectDraft->start_at)->diffInDays(Carbon::parse($projectDraft->end_at));
            if ($duration != $durationDraft) {
                $duration .= ' <span class="label value label-warning status">' . $durationDraft . '</span>';
            }
        }
        return $duration;
    }

    /**
     * generate value element in performance
     * @param int
     * @return array
     */
    public static function generateValueElementInPerformance($projecId)
    {
        $effort = ProjectMember::getTotalEffortTeamApproved(null, $projecId);
        $effortDraft = ProjectMember::getTotalEffortAllTeam($projecId);
        $memberDraft = ProjectMember::countMemberDraft($projecId);
        if (!$memberDraft) {
            return $effort;
        }
        $result = [];
        $arrayElement = ['count', 'total', 'dev', 'pm', 'qa'];
        foreach ($arrayElement as $element) {
            $result[$element] = $effort[$element];
            if ($effortDraft[$element] && $effort[$element] != $effortDraft[$element]) {
                $result[$element] .= ' <span class="label value label-warning status">' . $effortDraft[$element] . '</span>';
            }
        }
        return $result;
    }

    /**
     * check status delete
     * @param int
     * @return boolean
     */
    public static function checkStatusProjectMemeberDelete($status)
    {
        if (in_array($status, [ProjectWOBase::STATUS_DRAFT_DELETE,
                    ProjectWOBase::STATUS_SUBMMITED_DELETE,
                    ProjectWOBase::STATUS_REVIEWED_DELETE,
                    ProjectWOBase::STATUS_FEEDBACK_DELETE])) {
            return true;
        }
        return false;
    }

    /**
     * generate stage name
     * @param array
     * @param array
     * @return string
     */
    public static function generateStage($stage, $allStage)
    {
        if (array_key_exists($stage->stage, $allStage)) {
            return $allStage[$stage->stage];
        }
        return $stage->stage;
    }

    /**
     * get stqage deliverable
     * @param array
     * @param array
     * @return string
     */
    public static function getStageDeliverable($deliverable, $allStage)
    {
        if ($deliverable->stage_id) {
            $stage = StageAndMilestone::find($deliverable->stage_id);
            if ($stage && isset($allStage[$stage->stage])) {
                return $allStage[$stage->stage];
            }
        }
        return $deliverable->stage;
    }

    /**
     * is selected stage in deliverable
     * @param  array
     * @param array
     * @return  boolean
     */
    public static function isSelectedStageInDeliverable($deliverable, $stage)
    {
        if ($deliverable->stage_id) {
            if ($deliverable->stage_id == $stage->id) {
                return true;
            }
        }
        return false;
    }

    /**
     * get content stage
     * @param array
     * @param array
     * @return  string
     */
    public static function getContentStage($deliverable, $allStage)
    {
        if ($deliverable->stage_id) {
            foreach ($allStage as $stage) {
                if ($deliverable->stage_id == $stage->id)
                    return $stage->stage;
            }
        }
        return $deliverable->stage;
    }

    /**
     * get content participants of training plan
     * @param array
     * @param array
     * @return string
     */
    public static function getContentParticipants($training, $allEmployee)
    {
        $allMember = Training::getAllMemberOfTraining($training->id);
        if ($allMember) {
            $result = '';
            $countElement = 0;
            foreach ($allEmployee as $key => $employee) {
                if (in_array($employee->id, $allMember)) {
                    $countElement++;
                    if ($countElement == 1) {
                        $result .= preg_replace('/@.*/', '', $employee->email);
                    } else {
                        $result .= ', ' . preg_replace('/@.*/', '', $employee->email);
                    }
                }
            }
            return $result;
        }
        return;
    }

    public static function getContentSecurity($security, $allEmployee)
    {
        $allMember = Security::getAllMemberOfSecurity($security->id);
        if ($allMember) {
            $result = '';
            $countElement = 0;
            foreach ($allEmployee as $key => $employee) {
                if (in_array($employee->id, $allMember)) {
                    $countElement++;
                    if ($countElement == 1) {
                        $result .= preg_replace('/@.*/', '', $employee->email);
                    } else {
                        $result .= ', ' . preg_replace('/@.*/', '', $employee->email);
                    }
                }
            }
            return $result;
        }
        return;
    }

    public static function generateContentQuality($data, $project, $checkEditWorkOrder, $permissionAdd)
    {
        $type = $data['data']['type'];
        $valueNotApproved = '';
        if ($type == 'billable_effort') {
            $qualityDraft = ProjQuality::getQualityDraft($project->id, 'billable_effort');
            if ($qualityDraft) {
                $valueNotApproved = $qualityDraft->billable_effort;
            }
        } elseif ($type == 'plan_effort') {
            $qualityDraft = ProjQuality::getQualityDraft($project->id, 'plan_effort');
            if ($qualityDraft) {
                $valueNotApproved = $qualityDraft->plan_effort;
            }
        } else {
            $qualityDraft = ProjQuality::getQualityDraft($project->id, 'cost_approved_production');
            if ($qualityDraft) {
                $valueNotApproved = $qualityDraft->cost_approved_production;
            }
        }
        $quality = ProjQuality::getFollowProject($project->id);
        if ($quality) {
            if ($type == 'billable_effort') {
                $valueApproved = $quality->billable_effort;
            } elseif ($type == 'plan_effort') {
                $valueApproved = $quality->plan_effort;
            } else {
                $valueApproved =  $quality->cost_approved_production;
            }
        } else {
            $valueApproved = '';
        }

        return view('project::template.content-quality', ['permissionEdit' => $permissionAdd, 'checkEditWorkOrder' => $checkEditWorkOrder, 'valueApproved' => $valueApproved, 'valueNotApproved' => $valueNotApproved, 'qualityDraft' => $qualityDraft, 'type' => $type])->render();
    }

    /*
     * get label sale employee of project
     * @param array
     * @param array
     * @return string
     */

    public static function getLabelSaleOfProject($employees, $allSaleEmployee)
    {
        $result = '';
        $count = 0;
        foreach ($employees as $key => $employee) {
            if (in_array($employee->id, $allSaleEmployee)) {
                if ($count == 0) {
                    $result .= trim($employee->name, ' ');
                } else {
                    $result .= ', ' . trim($employee->name, ' ');
                }
                $count ++;
            }
        }
        return trim($result);
    }

    /**
     * generate color status
     * @param array
     * @param int
     * @param int
     * @return string
     */
    public static function generateColorStatus($allColorStatus, $value, $type)
    {
        if (isset($allColorStatus[$value])) {
            return $allColorStatus[$value];
        }
        return $allColorStatus[ProjectPoint::COLOR_STATUS_BLUE];
    }

    /**
     * format period date
     *
     * @param Datetime $start
     * @param Datetime $end
     */
    public static function formatPeriodDate($start, $end)
    {
        $firstDay = $start->format('d');
        $firstMonth = $start->format('M');
        $firstYear = $start->format('Y');

        $lastDay = $end->format('d');
        $lastMonth = $end->format('M');
        $lastYear = $end->format('Y');

        $resultYear = '';
        $resultMonth = '';
        $labelFirst = '';
        $labelLast = '';
        if ($lastYear == $firstYear) {
            $resultYear = ', ' . $firstYear;
        } else {
            $labelFirst = ', ' . $firstYear;
            $labelLast = ', ' . $lastYear;
        }

        if ($lastMonth == $firstMonth) {
            $resultMonth = $firstMonth;
            $labelFirst = $firstDay . $labelFirst;
            $labelLast = $lastDay . $labelLast;
        } else {
            $labelFirst = $firstMonth . ' ' . $firstDay . $labelFirst;
            $labelLast = $lastMonth . ' ' . $lastDay . $labelLast;
        }
        return $resultMonth . ' ' . $labelFirst . ' - ' . $labelLast . $resultYear;
    }

    /**
     * check change value project
     * @param array
     * @param array
     * @param string
     * @return boolean
     */
    public static function isChangeValueProject($projectNew, $projectOld, $field)
    {
        if (!$projectNew || !$projectOld) {
            return false;
        }
        if ($field == 'team_id') {
            $allTeam = Project::getAllTeamOfProject($projectOld->id);
            $allTeamDraft = Project::getAllTeamOfProject($projectNew->id);
            if (count($allTeamDraft)) {
                if (!self::compareTwoArray($allTeam, $allTeamDraft)) {
                    return false;
                }
                return true;
            }
            return false;
        } else {
            if ($projectNew[$field] == $projectOld[$field]) {
                return false;
            }
        }
        return true;
    }

    /**
     * compare two array
     * @param array
     * @param array
     * @return boolean
     */
    public static function compareTwoArray($arrayFirst, $arraySecond)
    {
        if (is_array($arrayFirst) && is_array($arraySecond) &&
                count($arrayFirst) == count($arraySecond) &&
                array_diff($arrayFirst, $arraySecond) === array_diff($arraySecond, $arrayFirst)) {
            return false;
        }
        return true;
    }

    /**
     *  check member disable
     *  @param array
     *  @param array
     *  @return boolean
     */
    public static function checkMemberDisable($pm, $pmDraf)
    {
        if ($pm->end_at >= $pmDraf->end_at) {
            return true;
        }
        return false;
    }

    /**
     * check change value
     * @param array
     * @param array
     * @return boolean
     */
    public static function isChangeValue($model, $modelOld)
    {
        if (!$model || !$modelOld) {
            return false;
        }
        if (in_array($modelOld->status, [
                    ProjectWOBase::STATUS_DRAFT_DELETE,
                    ProjectWOBase::STATUS_SUBMMITED_DELETE,
                    ProjectWOBase::STATUS_FEEDBACK_DELETE,
                    ProjectWOBase::STATUS_REVIEWED_DELETE])
        ) {
            return true;
        }
        $arrayField = self::getArrayFiledUpdate($model);
        foreach ($arrayField as $field) {
            if ($field == 'qua_gate_plan' ||
                    $field == 'committed_date' ||
                    $field == 'actual_date' ||
                    $field == 'start_at' ||
                    $field == 'end_at'
            ) {
                if ($model[$field] != $modelOld[$field] . ' 00:00:00') {
                    return true;
                }
            } else if ($field == 'prog_langs') {
                if (array_diff($model->prog_langs, $modelOld->prog_langs) ||
                        array_diff($modelOld->prog_langs, $model->prog_langs)
                ) {
                    return true;
                }
            } else {
                if ($model[$field] != $modelOld[$field]) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * update value when approved
     * @param array
     * @param array
     */
    public static function updateValueWhenApproved($model, $modelOld)
    {
        if (!$model || !$modelOld) {
            return;
        }

        $arrayField = self::getArrayFiledUpdate($model);

        foreach ($arrayField as $field) {
            $model[$field] = $modelOld[$field];
        }
        $modelOld->delete();
        $model->save();
    }

    /**
     * get array field update by table name
     * @param array
     * @return array
     */
    public static function getArrayFiledUpdate($model)
    {
        $stageTable = StageAndMilestone::getTableName();
        $tableNameModel = $model->getTableName();
        $tableDeliverable = ProjDeliverable::getTableName();
        $tableProjecMember = ProjectMember::getTableName();
        switch ($tableNameModel) {
            case $stageTable:
                $arrayField = ['stage', 'description', 'milestone', 'qua_gate_plan'];
                break;
            case $tableDeliverable:
                $arrayField = ['title', 'committed_date', 'actual_date', 're_commited_date', 'change_request_by', 'stage_id', 'note'];
                break;
            case $tableProjecMember:
                $arrayField = ['employee_id', 'start_at', 'end_at', 'effort',
                    'type', 'flat_resource', 'prog_langs'];
                break;
            default:
                $arrayField = [];
                break;
        }
        return $arrayField;
    }

    /*     * get number day modify actual last date
     * @param  object
     * @param  int
     * @param  array
     * @param  array
     * @return int
     */

    public static function getNumberDayModifyActualLastDate($actualLastDate, $modify, $specialHolidays, $annualHolidays)
    {
        $numberDateMofify = 0;
        $actualLastDateModify = clone $actualLastDate;
        $dateMofify = $actualLastDateModify->modify('+' . $modify . ' day');
        $interval = DateInterval::createFromDateString('1 day');
        $isChangeDateModify = true;
        $isView = false;
        while ($isChangeDateModify) {
            if (!$isView) {
                $isView = true;
                $period = new DatePeriod($actualLastDate, $interval, $dateMofify);
            } else {
                if ($dateMofifyClone != $dateMofify) {
                    $period = new DatePeriod($dateMofifyClone, $interval, $dateMofify);
                }
            }
            $dateMofifyClone = clone $dateMofify;
            foreach ($period as $dt) {
                $dateFull = $dt->format('Y-m-d');
                $date = $dt->format('m-d');
                if ($dt->isWeekend()) {
                    $dateMofify = $dateMofify->modify('+ 1 day');
                    $numberDateMofify++;
                } else {
                    if (in_array($dateFull, $specialHolidays)) {
                        $dateMofify = $dateMofify->modify('+ 1 day');
                        $numberDateMofify++;
                    }
                    if (in_array($date, $annualHolidays)) {
                        $dateMofify = $dateMofify->modify('+ 1 day');
                        $numberDateMofify++;
                    }
                }
            }
            if ($dateMofify == $dateMofifyClone) {
                $isChangeDateModify = false;
            }
        }
        return $numberDateMofify + $modify;
    }

    /**
     * check permission edit task general
     * @param model $task
     * @return boolean
     */
    public static function isAccessEditTaskGeneral(
    $task, $route = 'project::task.general.edit'
    ) {
        // check permission edit task
        if (Permission::getInstance()->isScopeCompany(null, $route)) {
            return true;
        }
        if (!$task || !$task->id) {
            return true;
        }
        //self project
        if ($task && $task->id && $task->isAssignOrCreatedBy()) {
            return true;
        }
        return false;
    }

    /**
     * check item is parent
     * @param int
     * @return boolean
     */
    public static function checkItemIsParent($status)
    {
        if (in_array($status, [
                    ProjectWOBase::STATUS_APPROVED,
                    ProjectWOBase::STATUS_DRAFT,
                    ProjectWOBase::STATUS_SUBMITTED,
                    ProjectWOBase::STATUS_REVIEWED,
                    ProjectWOBase::STATUS_FEEDBACK
                ])) {
            return true;
        }
        return false;
    }

    /**
     * send email every day when month reward
     * */
    public static function sendEmailMonthReward()
    {
        $current = Carbon::today();
        $collection = Project::getInforProjectEmail($current->subMonthNoOverflow(3));
        if (!count($collection)) {
            return;
        }

        foreach ($collection as $key => $item) {
            $emailQueue = new EmailQueue();
            $data = [
                "month_reward" => $item["month_reward"],
                "project_data" => $item["pm_project"],
                "pm_name" => $item["pm_name"],
            ];
            $projNames = [];
            foreach ($item['pm_project'] as $value) {
                $projNames[] = $value['proj_name'];
            }
            $subject = trans('project::email.[Project reward] : Please create reward for project');
            $emailLeadWatcher = CoreConfigData::getValueDb('project.email_lead_watcher');
            $emailQueue->addCc($emailLeadWatcher)
                    ->addCcNotify(Employee::getIdByEmail($emailLeadWatcher));
            $emailQueue->setTo($item["pm_email"], $item["pm_name"])
                    ->setTemplate('project::emails.alert_month_reward', $data)
                    ->setSubject($subject)
                    ->setNotify(
                        $key,
                        $subject . ' ('. implode(', ', $projNames) .')',
                        route('project::dashboard'),
                        ['actor_id' => null, 'icon' => 'reward.png', 'category_id' => RkNotify::CATEGORY_PROJECT]
                    )
                    ->save();
        }
    }

    /***
     * waring if wo approved but not fill actual effort and plan effort
     */

    public static function warningCostTab($id, $costActualEffort)
    {
        $projectPoint = ProjectPoint::findFromProject($id);
        $listFields = '';
        if (isset($costActualEffort)) {
            $costActualEffortCurrent = $costActualEffort;
        } else {
            $costActualEffortCurrent = $projectPoint->getCostActualEffortCurrent();
        }

        $existsApproved = Task::checkHasTaskWorkorderApproved($id);
        if ($existsApproved && !$costActualEffortCurrent) {
            $listFields .= '<li>' . Lang::get('project::view.Tab Cost/Actual Effort') . '</li>';
        }
        return $listFields;
    }

    /**
     * Check in Cost Tab to find fields have wrong value
     * when report project
     * @param $project_id, $cost_plan_erffor _current, $cost_actual_effort, $pointBaselinePre
     * @return $listFields
     */
    public static function checkCostTab($id, $costActualEffort, $pointBaselinePre = null)
    {
        return ''; // cost not check because effort auto increment
        $projectPoint = ProjectPoint::findFromProject($id);
        $listFields = '';

        if (isset($costActualEffort)) {
            $costActualEffortCurrent = $costActualEffort;
        } else {
            $costActualEffortCurrent = $projectPoint->getCostActualEffortCurrent();
        }
        // Get color Effort Effectiveness (%)
        $costEffortEfftiveness = $projectPoint->getCostEffortEfftiveness(null, $costActualEffortCurrent);
        $costEffectivenessColor = self::getColorCssClass($projectPoint->getColor('cost_target', 'cost_ucl', $costEffortEfftiveness));
        if (($costEffectivenessColor === 'pp-bg-red' || $costEffectivenessColor === 'pp-bg-yellow') &&
            $pointBaselinePre->cost_effort_effectiveness != $costEffortEfftiveness) {
            if (trim($projectPoint['cost_ees_note']) === '') {
                $listFields .= '<li>' . Lang::get('project::view.Tab Cost/Effort Effectiveness (%)') . '</li>';
            }
        }
        // Get color Busy rate (%)
        $costResourceAllocation = $projectPoint->getCostResourceAllocation();
        $costResourceAllocationCurrent = $projectPoint->getCostResourceAllocationCurrent($costResourceAllocation);
        $costBusyRate = $projectPoint->getCostBusyRate($costActualEffortCurrent, $costResourceAllocationCurrent);
        $costBusyRateColor = self::getColorCssClass($projectPoint->getCostBusyRateColor($costBusyRate));
        if (($costBusyRateColor === 'pp-bg-red' || $costBusyRateColor === 'pp-bg-yellow') &&
            $pointBaselinePre->cost_resource_allocation_total != $costResourceAllocation) {
            if (trim($projectPoint['cost_busy_rate_note']) === '') {
                $listFields .= '<li>' . Lang::get('project::view.Tab Cost/Busy rate (%)') . '</li>';
            }
        }
        return $listFields;
    }

    /**
     * Check in Quality Tab to find fields have wrong value
     * when report project
     * @param $project_id, $qua_leakage_errors _current, $qua_defect_errors
     * @param object $getPointByPreWeek
     * @return $listFields
     */
    public static function checkQualityTab($id, $quaLeakageErrors, $quaDefectErrors, $getPointByPreWeek = null)
    {
        if (!$getPointByPreWeek) {
            return '';
        }
        $projectPoint = ProjectPoint::findFromProject($id);
        $listFields = '';

        if (isset($quaLeakageErrors)) {
            $quaLeakageError = $quaLeakageErrors;
        } else {
            $quaLeakageError = $projectPoint->getQuaLeakageError();
        }
        if (isset($quaDefectErrors)) {
            $quaDefectError = $quaDefectErrors;
        } else {
            $quaDefectError = $projectPoint->getQuaDefectError();
        }
        $costResourceAllocation = $projectPoint->getCostResourceAllocation();
        $costResourceAllocationCurrent = $projectPoint->getCostResourceAllocationCurrent($costResourceAllocation);
        $quaLeakage = $projectPoint->getQuaLeakage($quaLeakageError, $quaDefectError);
        $quaDefect = $projectPoint->getQuaDefect($quaDefectError, $costResourceAllocationCurrent);
        // Get color Leakage (%)
        $quaLeakageColor = self::getColorCssClass($projectPoint->getColor('qua_leakage_target', 'qua_leakage_ucl', $quaLeakage));
        if (($quaLeakageColor === 'pp-bg-red' || $quaLeakageColor === 'pp-bg-yellow')
            && ($quaLeakage != $getPointByPreWeek->qua_leakage)
        ) {
            if (trim($projectPoint['qua_leakage_note']) === '') {
                $listFields .= '<li>' . Lang::get('project::view.Tab Quality/Leakage (%)') . '</li>';
            }
        }
        // Get color Defect rate
        $quaDefectColor = self::getColorCssClass($projectPoint->getQuaDefectColor($quaDefect, $quaDefectError));
        if (($quaDefectColor === 'pp-bg-red' || $quaDefectColor === 'pp-bg-yellow') &&
            $quaDefect != $getPointByPreWeek->qua_defect
        ) {
            if (trim($projectPoint['qua_defect_note']) === '') {
                $listFields .= '<li>' . Lang::get('project::view.Tab Quality/Defect rate') . '</li>';
            }
        }
        return $listFields;
    }

    /**
     * Check in Timelines Tab to find fields have wrong value
     * when report project
     * @param $project_id, $tl_schedule
     * @param object|null $getPointByPreWeek
     * @return $listFields
     */
    public static function checkTimelineTab($id, $tlSchedule, $getPointByPreWeek = null)
    {
        if (!$getPointByPreWeek) {
            return '';
        }
        $projectPoint = ProjectPoint::findFromProject($id);
        $listFields = '';
        if (isset($tlSchedule)) {
            $timelineSchedule = $tlSchedule;
        } else {
            $timelineSchedule = $projectPoint->getTlSchedule();
        }
        $tlDeliver = $projectPoint->getTlDeliver();
        // Get color Late Schedule (days)
        $timelineScheduleColor = self::getColorCssClass($projectPoint->getColor('tl_schedule_target', 'tl_schedule_ucl', $timelineSchedule));
        if (($timelineScheduleColor === 'pp-bg-red' || $timelineScheduleColor === 'pp-bg-yellow') &&
            $timelineSchedule != $getPointByPreWeek->tl_schedule
        ) {
            if (trim($projectPoint['tl_schedule_note']) === '') {
                $listFields .= '<li>' . Lang::get('project::view.Tab Timeliness/Late Schedule (days)') . '</li>';
            }
        }
        // Get color Deliverable (%)
        $tlDeliverColor = self::getColorCssClass($projectPoint->getColorReverse2('tl_deliver_target', 'tl_deliver_ucl', $tlDeliver));
        if (($tlDeliverColor === 'pp-bg-red' || $tlDeliverColor === 'pp-bg-yellow') &&
            $tlDeliver != $getPointByPreWeek->tl_deliver
        ) {
            if (trim($projectPoint['tl_deliver_note']) === '') {
                $listFields .= '<li>' . Lang::get('project::view.Tab Timeliness/Deliverable (%)') . '</li>';
            }
        }
        return $listFields;
    }

    /**
     * Check in Process Tab to find fields have wrong value
     * when report project
     * @param $project_id
     * @param object|null $getPointByPreWeek
     * @return $listFields
     */
    public static function checkProcessTab($id, $getPointByPreWeek = null)
    {
        if (!$getPointByPreWeek) {
            return '';
        }
        $projectPoint = ProjectPoint::findFromProject($id);
        $listFields = '';

        $procCompliance = $projectPoint->getProcCompliance();
        $procReportPoint = $projectPoint->getProcReportPoint();
        $procComplianceColor = self::getColorCssClass($projectPoint->getColor('proc_compliance_target', 'proc_compliance_ucl', $procCompliance));
        $procReportColor = self::getColorCssClass($projectPoint->getColorFollowPoint($procReportPoint));
        // Get color Process None Compliance
        if (($procComplianceColor === 'pp-bg-red' || $procComplianceColor === 'pp-bg-yellow') &&
            $procCompliance != $getPointByPreWeek->proc_compliance
        ) {
            if (trim($projectPoint['proc_compliance_note']) === '') {
                $listFields .= '<li>' . Lang::get('project::view.Tab Process/Process None Compliance') . '</li>';
            }
        }
        // Get color Project Reports
        if (($procReportColor === 'pp-bg-red' || $procReportColor === 'pp-bg-yellow') &&
            $procReportPoint != $getPointByPreWeek->proc_report_point
        ) {
            if (trim($projectPoint['proc_report_note']) === '') {
                $listFields .= '<li>' . Lang::get('project::view.Tab Process/Project Reports') . '</li>';
            }
        }
        return $listFields;
    }

    /**
     * Check in Css Tab to find fields have wrong value
     * when report project
     * @param $project_id, $css_css
     * @return $listFields
     */
    public static function checkCssTab($id, $cssCss, $getPointByPreWeek = null)
    {
        if (!$getPointByPreWeek) {
            return '';
        }
        $projectPoint = ProjectPoint::findFromProject($id);
        $listFields = '';
        if (isset($cssCss)) {
            $cssCs = $cssCss;
        } else {
            $cssCs = $projectPoint->getCssCs();
        }
        $cssTasksCCSplit = Task::getTypeCommendedCriticizedSplit($id);
        $cssCiNegative = $projectPoint->getCssCiNegative($cssTasksCCSplit);
        $cssColor = self::getColorCssClass($projectPoint->getColorReverse('css_css_target', 'css_css_lcl', $cssCs));
        $ciColor = self::getColorCssClass($projectPoint->getCssCiColor($cssCiNegative));
        // Get color Customer satisfactions
        if (($cssColor === 'pp-bg-red' || $cssColor === 'pp-bg-yellow') &&
            $cssCs != $getPointByPreWeek->css_css
        ) {
            if (trim($projectPoint['css_css_note']) === '') {
                $listFields .= '<li>' . Lang::get('project::view.Tab CSS/Customer satisfactions') . '</li>';
            }
        }
        return $listFields;
    }

    /**
     * Group projects by leader
     * Store email information: email of leader, project name, url to project report (tab reward)
     *
     * @param Project collection $projects
     * @return array
     */
    public static function groupProjectsByLeader($projects)
    {
        $leaders = [];
        foreach ($projects as $proj) {
            if (!isset($leaders[$proj->leader_id]['leaderInfo'])) {
                $leaders[$proj->leader_id]['leaderInfo'] = [
                    'leaderName' => $proj->leader_name,
                    'leaderEmail' => $proj->leader_email,
                ];
            }
            $leaders[$proj->leader_id]['projInfo'][] = [
                'projectName' => $proj->name,
                'urlProject' => route("project::point.edit", $proj->id) . '#reward',
            ];
        }
        return $leaders;
    }

    /**
     * Send mail prompt review project budget reward to leader
     *
     * @return void
     */
    public static function sendMailPromptReviewBudget()
    {
        $projectUnreviewed = Project::getProjectUnreviewedRewardBudget();
        if ($projectUnreviewed) {
            $leaders = static::groupProjectsByLeader($projectUnreviewed);
            $emailApprover = CoreConfigData::getValueDB('project.account_approver_reward');
            $approverId = Employee::getIdByEmail($emailApprover);
            foreach ($leaders as $leaderId => $itemArray) {
                //Send email prompt to leader, cc to COO
                $emailQueue = new EmailQueue();
                $emailQueue->setTo($itemArray['leaderInfo']['leaderEmail'], $itemArray['leaderInfo']['leaderName'])
                    ->setFrom(config('mail.username'), config('mail.name'))
                    ->setSubject(Lang::get('project::view.[Rikkeisoft Intranet] Please review project reward budget'))
                    ->setTemplate('project::emails.prompt_review_budget', $itemArray);
                $emailApprover = CoreConfigData::getValueDB('project.account_approver_reward');
                if (!empty($emailApprover)) {
                    $emailQueue->addCc($emailApprover);
                }
                $emailQueue->save();
            }
            //set notify
            $toNotifyIds = array_keys($leaders);
            if ($approverId) {
                array_push($toNotifyIds, $approverId);
            }
            \RkNotify::put(
                $toNotifyIds,
                Lang::get('project::view.[Rikkeisoft Intranet] Please review project reward budget'),
                route('project::dashboard'),
                ['icon' => 'reward.png', 'category_id' => RkNotify::CATEGORY_PROJECT]
            );
        }
    }

    public static function errorNotPermission()
    {
        if (request()->ajax() || request()->wantsJson() || CoreUrl::isApi()) {
            echo response()->json([
                'message' => trans('core::message.You don\'t have access'),
                'error' => 1,
            ])->content();
            exit;
        }
        echo view('errors.permission');
        exit;
    }

    public static function errorNotFound()
    {
        if (request()->wantsJson() || request()->ajax() || CoreUrl::isApi()) {
            echo response()->json([
                'error' => 1,
                'message' => Lang::get('project::message.The project does not exist!'),
            ])->content();
            exit;
        }
        echo view('errors.not-found');
        exit;
    }
        
    /**
     * getEmpPQAProject
     *
     * @param  int $idProj
     * @return collections
     */
    public function getEmpPQAProject($idProj)
    {
        $empPQAs = [];
        $projPQA = ProjectMember::getEmpPQAOfProjectLifetime($idProj);
        if (count($projPQA)) {
            $empPQAs = $projPQA;
        } else {
            $pqaResponsible = PqaResponsibleTeam::getPqaResponsibleTeamOfProjs($idProj);
            if (count($pqaResponsible)) {
                $empPQAs = $pqaResponsible;
            } else {
                $emailPqa = CoreConfigData::getQAAccount();
                $empPQAs = collect([Employee::getEmpItemByEmail($emailPqa)]);
            }
        }
        return $empPQAs;
    }

    /**
     * get leader pqa of project
     *
     * @return collection
     */
    public function getLeaderPQA($employeesPQA)
    {
        if (count($employeesPQA)) {
            $empIds = $employeesPQA->pluck('employee_id')->toArray();
            $empIds = implode(',', $empIds);
            return Employee::select(
                'employees.id',
                'employees.name',
                'employees.email'
            )
            ->join(DB::raw("(SELECT teams.leader_id, teams.id, teams.code
                FROM teams
                LEFT JOIN team_members AS tm ON tm.team_id =  teams.id
                WHERE tm.employee_id IN ({$empIds})
                    AND teams.code like '%pqa%'
                    AND teams.deleted_at IS NULL) AS team_leader"
                ), 'team_leader.leader_id', '=', 'employees.id')
            ->first();
        }
        return null;
    }

    public function getCostColor($costEffortEfficiency2, $costEffortEfficiencyTarget, $costEffortEfficiencyLcl,
            $correctionCost, $correctCostTarget, $correctCostUcl, $correctCostLcl, $checkCate)
    {
        $costEEColor = $this->getCostEEColor($costEffortEfficiency2, $costEffortEfficiencyTarget, $costEffortEfficiencyLcl, $checkCate);
        $correctionCostColor = $this->getCorrectionCostColor($correctionCost, $correctCostTarget, $correctCostUcl, $correctCostLcl, $checkCate);
        return $costEEColor > $correctionCostColor ? $costEEColor : $correctionCostColor;
    }

    public function getCostEEColor($costEffortEfficiency2, $costEffortEfficiencyTarget, $costEffortEfficiencyLcl, $checkCate)
    {
        if (!$checkCate || empty($costEffortEfficiency2)) {
            return ProjectPoint::COLOR_STATUS_WHITE;
        }
        if($costEffortEfficiency2 < $costEffortEfficiencyLcl) {
            return ProjectPoint::COLOR_STATUS_RED;
        } elseif(($costEffortEfficiencyLcl <= $costEffortEfficiency2)
                && ($costEffortEfficiency2 < $costEffortEfficiencyTarget)) {
            return ProjectPoint::COLOR_STATUS_YELLOW;
        } elseif($costEffortEfficiencyTarget <= $costEffortEfficiency2) {
            return ProjectPoint::COLOR_STATUS_BLUE;
        } else {
            return ProjectPoint::COLOR_STATUS_WHITE;
        }
    }

    public function getCorrectionCostColor($correctionCost, $correctCostTarget, $correctCostUcl, $correctCostLcl, $checkCate)
    {
        if (!$checkCate || empty($correctionCost)) {
            return ProjectPoint::COLOR_STATUS_WHITE;
        }
        if($correctionCost < $correctCostLcl
                || $correctionCost > $correctCostUcl) {
            return ProjectPoint::COLOR_STATUS_RED;
        } elseif($correctionCost == $correctCostTarget) {
            return ProjectPoint::COLOR_STATUS_BLUE;
        } else {
            return ProjectPoint::COLOR_STATUS_YELLOW;
        }
    }

    public function getQualiTyColor($leakage, $leakageTarget, $leakageUcl, $defect, $defectTarget, $defectUcl, $checkCate)
    {
        $leakageColor = $this->getLeakageColor($leakage, $leakageTarget, $leakageUcl, $checkCate);
        $defectColor = $this->getDefectColor($defect, $defectTarget, $defectUcl, $checkCate);
        return $leakageColor > $defectColor ? $leakageColor : $defectColor;
    }

    public function getLeakageColor($leakage, $leakageTarget, $leakageUcl, $checkCate)
    {
        if (!$checkCate || empty($leakage)) {
            return ProjectPoint::COLOR_STATUS_WHITE;
        }
        if($leakage > $leakageUcl) {
            return ProjectPoint::COLOR_STATUS_RED;
        } elseif($leakageTarget == $leakage) {
            return ProjectPoint::COLOR_STATUS_BLUE;
        } else {
            return ProjectPoint::COLOR_STATUS_YELLOW;
        }
    }

    public function getDefectColor($defect, $defectTarget, $defectUcl, $checkCate)
    {
        if (!$checkCate || empty($defect)) {
            return ProjectPoint::COLOR_STATUS_WHITE;
        }
        if($defect > $defectUcl) {
            return ProjectPoint::COLOR_STATUS_RED;
        } elseif($defectTarget == $defect) {
            return ProjectPoint::COLOR_STATUS_BLUE;
        } else {
            return ProjectPoint::COLOR_STATUS_YELLOW;
        }
    }

    public function getTimelinessColor($tlDeliver, $tlDeliverTarget, $tlDeliverLcl, $tlDeliverUcl, $checkCate)
    {
        if (!$checkCate || emtpy($tl_deliver)) {
            return ProjectPoint::COLOR_STATUS_WHITE;
        }
        if ($tlDeliver < $tlDeliverLcl) {
            return ProjectPoint::COLOR_STATUS_RED;
        } elseif (($tlDeliverLcl <= $tlDeliver) 
                && ($tlDeliver < $tlDeliverUcl)) {
            return ProjectPoint::COLOR_STATUS_YELLOW;
        } elseif ($tlDeliver >= $tlDeliverTarget) {
            return ProjectPoint::COLOR_STATUS_BLUE;
        } else {
            return ProjectPoint::COLOR_STATUS_WHITE;
        }
    }

    public function getProcessColor($procCompliance, $procComplianceTarget, $procComplianceLcl, $checkCate)
    {
        if (!$checkCate || empty($procCompliance)) {
            return ProjectPoint::COLOR_STATUS_WHITE;
        }
        if ($procCompliance < $procComplianceLcl) {
            $colorInput = ProjectPoint::COLOR_STATUS_RED;
        } elseif (($procComplianceLcl <= $procCompliance) && ($procCompliance < $procComplianceTarget)) {
            $colorInput = ProjectPoint::COLOR_STATUS_YELLOW;
        } elseif ($procComplianceTarget <= $procCompliance) {
            $colorInput = ProjectPoint::COLOR_STATUS_BLUE;
        } else {
            return ProjectPoint::COLOR_STATUS_WHITE;
        }
    }

    public function getCssColor($cssCss, $target, $lcl, $checkCate)
    {
        if (!$checkCate || $cssCss) {
            return ProjectPoint::COLOR_STATUS_WHITE;
        }
        if ($cssCss < $lcl) {
            $colorInput = ProjectPoint::COLOR_STATUS_RED;
        } elseif (($lcl <= $cssCss) && ($cssCss < $target)) {
            $colorInput = ProjectPoint::COLOR_STATUS_YELLOW;
        } elseif ($target <= $cssCss) {
            $colorInput = ProjectPoint::COLOR_STATUS_BLUE;
        } else {
            return ProjectPoint::COLOR_STATUS_WHITE;
        }
    }

    public function getColorByValue()
    {
        return [
            ProjectPoint::COLOR_STATUS_RED => '#dd4b39',
            ProjectPoint::COLOR_STATUS_YELLOW => '#f3b812',
            ProjectPoint::COLOR_STATUS_BLUE => '#00a65a',
            ProjectPoint::COLOR_STATUS_WHITE => '#fff',
        ];
    }
}
