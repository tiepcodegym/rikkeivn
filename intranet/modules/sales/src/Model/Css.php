<?php

namespace Rikkei\Sales\Model;

use DateTime;
use Illuminate\Support\Facades\URL;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\Form;
use Rikkei\Project\Model\Project;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Permission as PermissionModel;
use Carbon\Carbon;
use Lang;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;

class Css extends CoreModel
{
    protected $table = 'css';

    use SoftDeletes;

    const CSS_TIME = '2021-07-09 00:00:00';
    const TYPE_OSDC = Project::TYPE_OSDC;
    const TYPE_BASE = Project::TYPE_BASE;
    const TYPE_ONSITE = Project::TYPE_ONSITE;
    const JAP_LANG = 0;
    const ENG_LANG = 1;
    const VIE_LANG = 2;
    const STATUS_NEW = 1;
    const STATUS_CANCEL = 2;
    const STATUS_FEEDBACK = 4;
    const STATUS_SUBMITTED = 7;
    const STATUS_APPROVED = 9;
    const STATUS_REVIEW = 10;

    public static function getCssProjectTypeValue()
    {
        return [self::TYPE_OSDC, self::TYPE_BASE, self::TYPE_ONSITE];
    }

    public static function getCssProjectTypeLabel()
    {
        return [self::TYPE_OSDC => 'OSDC', self::TYPE_BASE => 'BASE', self::TYPE_ONSITE => 'ONSITE'];
    }

    /**
     * Get Css by css_id and token
     * @param int $cssId
     * @param string $token
     * @return Css
     */
    public function getCssByIdAndToken($cssId, $token)
    {
        return self::where('id', $cssId)
            ->where('token', $token)
            ->first();
    }

    public function getCssByResultId($resultId)
    {
        return self::join('css_result', 'css.id', '=', 'css_result.css_id')
            ->where('css_result.id', $resultId)
            ->select('css.*')
            ->first();
    }

    public static function getCssByProjectId($projectName)
    {
        return self::where('css.project_name', $projectName)
            ->select('css.*')
            ->get();
    }

    /**
     * get css by project_type_id and list team ids
     * @param ing $project_type_id
     * @param string $team_ids
     * return Css list
     */
    public function getCssByProjectTypeAndTeam($projectTypeId, $teamIds)
    {
        $arrFilterTeam = explode(',', $teamIds);
        return self::leftJoin('projs', 'css.projs_id', '=', 'projs.id')
            ->leftJoin('team_members', 'team_members.employee_id', '=', 'projs.leader_id')
            ->leftJoin('teams', 'team_members.team_id', '=', 'teams.id')
            ->whereIn('teams.id', $arrFilterTeam)
            ->where('project_type_id', $projectTypeId)
            ->groupBy('css.id')
            ->select('css.*')
            ->get();
    }

    /**
     * get css by project_type_id and list team ids and employee
     * @param ing $project_type_id
     * @param string $team_ids
     * return Css list
     */
    public function getCssByProjectTypeAndTeamAndEmployee($project_type_id, $team_ids, $employeeId)
    {
        $result = self::leftJoin('projs', 'css.projs_id', '=', 'projs.id')
            ->leftJoin('team_members', 'team_members.employee_id', '=', 'projs.leader_id')
            ->leftJoin('teams', 'team_members.team_id', '=', 'teams.id')
            ->whereIn('teams.id', $team_ids)
            ->where('css.project_type_id', $project_type_id)
            ->where('css.employee_id', $employeeId)
            ->select('css.*');

        if (self::isUseSoftDelete()) {
            $result->whereNull('css.deleted_at');
        }

        return $result->orderBy('css.created_at', 'asc')->get();
    }

    /**
     * get css by project_type_id and list team ids and employee's team
     * @param int $projectTypeId
     * @param string $teamIds
     * @param array $arrEmployeeTeam
     * return Css list
     */
    public function getCssByProjectTypeAndTeamAndEmployeeTeam($projectTypeId, $teamIds, $arrEmployeeTeam)
    {
        $arrFilterTeam = explode(',', $teamIds);
        return self::leftJoin('projs', 'css.projs_id', '=', 'projs.id')
            ->leftJoin('team_members', 'team_members.employee_id', '=', 'projs.leader_id')
            ->leftJoin('teams', 'team_members.team_id', '=', 'teams.id')
            ->whereIn('teams.id', $arrEmployeeTeam)
            ->whereIn('teams.id', $arrFilterTeam)
            ->where('css.project_type_id', $projectTypeId)
            ->groupBy('css.id')
            ->select('css.*')
            ->get();
    }

    /**
     * get css by team_id and list project type ids
     * @param int $teamId
     * @param string $projectTypeIds
     * return Css list
     */
    public static function getCssByTeamIdAndListProjectType($teamId, $projectTypeIds)
    {
        $arrProjectType = explode(',', $projectTypeIds);
        return self::leftJoin('projs', 'css.projs_id', '=', 'projs.id')
            ->leftJoin('team_members', 'team_members.employee_id', '=', 'projs.leader_id')
            ->leftJoin('teams', 'team_members.team_id', '=', 'teams.id')
            ->where('teams.id', $teamId)
            ->whereIn('css.project_type_id', $arrProjectType)
            ->groupBy('css.id')
            ->select('css.*')
            ->get();
    }

    /**
     * get css by teamId and project type list and employee
     * @param ing $teamId
     * @param string $projectTypeIds
     * @param int $employeeId
     * return Css list
     */
    public function getCssByTeamIdAndListProjectTypeAndEmployee($teamId, $projectTypeIds, $employeeId)
    {
        $arrProjectType = explode(',', $projectTypeIds);
        return self::leftJoin('projs', 'css.projs_id', '=', 'projs.id')
            ->leftJoin('team_members', 'team_members.employee_id', '=', 'projs.leader_id')
            ->leftJoin('teams', 'team_members.team_id', '=', 'teams.id')
            ->where('teams.id', $teamId)
            ->whereIn('css.project_type_id', $arrProjectType)
            ->where('css.employee_id', $employeeId)
            ->groupBy('css.id')
            ->select('css.*')
            ->get();
    }

    /**
     * get css by project_type_id and list team ids and employee's team
     * @param int $teamId
     * @param string $projectTypeIds
     * @param array $arrEmployeeTeam
     * return Css list
     */
    public function getCssByTeamIdAndListProjectTypeAndEmployeeTeam($teamId, $projectTypeIds, $arrEmployeeTeam)
    {
        $arrProjectType = explode(',', $projectTypeIds);
        return self::leftJoin('projs', 'css.projs_id', '=', 'projs.id')
            ->leftJoin('team_members', 'team_members.employee_id', '=', 'projs.leader_id')
            ->leftJoin('teams', 'team_members.team_id', '=', 'teams.id')
            ->whereIn('teams.id', $arrEmployeeTeam)
            ->where('teams.id', $teamId)
            ->whereIn('project_type_id', $arrProjectType)
            ->groupBy('css.id')
            ->select('css.*')
            ->get();
    }

    /**
     * get css by pm_name, team_id and list project type ids
     * @param string $pmName
     * @param string $teamIds
     * @param string $projectTypeIds
     * return Css list
     */
    public static function getCssByPmAndTeamIdsAndListProjectType($pmName, $teamIds, $projectTypeIds)
    {
        $arrProjectType = explode(',', $projectTypeIds);
        $arrTeam = explode(',', $teamIds);
        return self::leftJoin('projs', 'css.projs_id', '=', 'projs.id')
            ->leftJoin('team_members', 'team_members.employee_id', '=', 'projs.leader_id')
            ->leftJoin('teams', 'team_members.team_id', '=', 'teams.id')
            ->whereIn('teams.id', $arrTeam)
            ->whereIn('css.project_type_id', $arrProjectType)
            ->where('css.pm_name', $pmName)
            ->groupBy('css.id')
            ->select('css.*')
            ->get();
    }

    /**
     * get css by PM, team list, project type list and employee
     * @param string $pmName
     * @param string $teamIds
     * @param string $projectTypeIds
     * @param array $employeeId
     * return Css list
     */
    public function getCssByPmAndTeamIdsAndListProjectTypeAndEmployee($pmName, $teamIds, $projectTypeIds, $employeeId)
    {
        $arrProjectType = explode(',', $projectTypeIds);
        $arrTeam = explode(',', $teamIds);
        return self::leftJoin('projs', 'css.projs_id', '=', 'projs.id')
            ->leftJoin('team_members', 'team_members.employee_id', '=', 'projs.leader_id')
            ->leftJoin('teams', 'team_members.team_id', '=', 'teams.id')
            ->whereIn('teams.id', $arrTeam)
            ->whereIn('css.project_type_id', $arrProjectType)
            ->where('css.pm_name', $pmName)
            ->where('css.employee_id', $employeeId)
            ->groupBy('css.id')
            ->select('css.*')
            ->get();
    }

    /**
     * get css by PM, team list, project type list and employee's team
     * @param string $pmName
     * @param string $teamIds
     * @param string $projectTypeIds
     * @param array $arrEmployeeTeam
     * return Css list
     */
    public function getCssByPmAndTeamIdsAndListProjectTypeAndEmployeeTeam($pmName, $teamIds, $projectTypeIds, $arrEmployeeTeam)
    {
        $arrFilterTeam = explode(',', $teamIds);
        $arrFilterProjectType = explode(',', $projectTypeIds);
        return self::leftJoin('projs', 'css.projs_id', '=', 'projs.id')
            ->leftJoin('team_members', 'team_members.employee_id', '=', 'projs.leader_id')
            ->leftJoin('teams', 'team_members.team_id', '=', 'teams.id')
            ->whereIn('teams.id', $arrEmployeeTeam)
            ->whereIn('teams.id', $arrFilterTeam)
            ->whereIn('css.project_type_id', $arrFilterProjectType)
            ->where('css.pm_name', $pmName)
            ->groupBy('css.id')
            ->select('css.*')
            ->get();
    }

    /**
     * get css by brse_name, team list and project type list
     * @param string $brseName
     * @param string $teamIds
     * @param string $projectTypeIds
     * return Css list
     */
    public static function getCssByBrseAndTeamIdsAndListProjectType($brseName, $teamIds, $projectTypeIds)
    {
        $arrProjectType = explode(',', $projectTypeIds);
        $arrTeam = explode(',', $teamIds);
        return self::join('css_team', 'css.id', '=', 'css_team.css_id')
            ->whereIn('css_team.team_id', $arrTeam)
            ->whereIn('css.project_type_id', $arrProjectType)
            ->where('css.brse_name', $brseName)
            ->groupBy('css.id')
            ->select('css.*')
            ->get();
    }

    /**
     * get css by brse_name, team list, project type list and employee
     * @param string $brseName
     * @param string $teamIds
     * @param string $projectTypeIds
     * @param int $employeeId
     * return Css list
     */
    public static function getCssByBrseAndTeamIdsAndListProjectTypeAndEmployee($brseName, $teamIds, $projectTypeIds, $employeeId)
    {
        $arrProjectType = explode(',', $projectTypeIds);
        $arrTeam = explode(',', $teamIds);
        return self::join('css_team', 'css.id', '=', 'css_team.css_id')
            ->whereIn('css_team.team_id', $arrTeam)
            ->whereIn('css.project_type_id', $arrProjectType)
            ->where('css.brse_name', $brseName)
            ->where('css.employee_id', $employeeId)
            ->groupBy('css.id')
            ->select('css.*')
            ->get();
    }

    public static function getStatusCss()
    {
        return [
            self::STATUS_NEW => 'New',
            self::STATUS_FEEDBACK => 'Feedback',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REVIEW => 'REVIEWED',
            self::STATUS_CANCEL => 'Cancel',
        ];
    }

    /**
     * get css by brse_name, team list, project type list and employee's team
     * @param string $brseName
     * @param string $teamIds
     * @param string $projectTypeIds
     * @param array $arrEmployeeTeam
     * return Css list
     */
    public static function getCssByBrseAndTeamIdsAndListProjectTypeAndEmployeeTeam($brseName, $teamIds, $projectTypeIds, $arrEmployeeTeam)
    {
        $arrProjectType = explode(',', $projectTypeIds);
        $arrTeam = explode(',', $teamIds);
        return self::join('css_team', 'css.id', '=', 'css_team.css_id')
            ->whereIn('css_team.team_id', $arrTeam)
            ->whereIn('css.project_type_id', $arrProjectType)
            ->where('css.brse_name', $brseName)
            ->whereIn('css_team.team_id', $arrEmployeeTeam)
            ->groupBy('css.id')
            ->select('css.*')
            ->get();
    }

    /**
     * get css by customer_name, team list and project type list
     * @param string $customerName
     * @param string $teamIds
     * @param string $projectTypeIds
     * return Css list
     */
    public static function getCssByCustomerAndTeamIdsAndListProjectType($customerName, $teamIds, $projectTypeIds)
    {
        $arrProjectType = explode(',', $projectTypeIds);
        $arrTeam = explode(',', $teamIds);
        return self::leftJoin('projs', 'css.projs_id', '=', 'projs.id')
            ->leftJoin('team_members', 'team_members.employee_id', '=', 'projs.leader_id')
            ->leftJoin('teams', 'team_members.team_id', '=', 'teams.id')
            ->whereIn('teams.id', $arrTeam)
            ->whereIn('css.project_type_id', $arrProjectType)
            ->where('css.customer_name', $customerName)
            ->groupBy('css.id')
            ->select('css.*')
            ->get();
    }

    /**
     * get css by customer_name, team list, project type list and employee
     * @param string $customerName
     * @param string $teamIds
     * @param string $projectTypeIds
     * @param int $employeeId
     * return Css list
     */
    public static function getCssByCustomerAndTeamIdsAndListProjectTypeAndEmployee($customerName, $teamIds, $projectTypeIds, $employeeId)
    {
        $arrProjectType = explode(',', $projectTypeIds);
        $arrTeam = explode(',', $teamIds);
        return self::leftJoin('projs', 'css.projs_id', '=', 'projs.id')
            ->leftJoin('team_members', 'team_members.employee_id', '=', 'projs.leader_id')
            ->leftJoin('teams', 'team_members.team_id', '=', 'teams.id')
            ->whereIn('teams.id', $arrTeam)
            ->whereIn('css.project_type_id', $arrProjectType)
            ->where('css.customer_name', $customerName)
            ->where('css.employee_id', $employeeId)
            ->groupBy('css.id')
            ->select('css.*')
            ->get();
    }


    /**
     * get css by customer_name, team list, project type list and employee
     * @param string $customerName
     * @param string $teamIds
     * @param string $projectTypeIds
     * @param array $arrEmployeeTeam
     * return Css list
     */
    public static function getCssByCustomerAndTeamIdsAndListProjectTypeAndEmployeeTeam($customerName, $teamIds, $projectTypeIds, $arrEmployeeTeam)
    {
        $arrProjectType = explode(',', $projectTypeIds);
        $arrTeam = explode(',', $teamIds);
        return self::leftJoin('projs', 'css.projs_id', '=', 'projs.id')
            ->leftJoin('team_members', 'team_members.employee_id', '=', 'projs.leader_id')
            ->leftJoin('teams', 'team_members.team_id', '=', 'teams.id')
            ->whereIn('teams.id', $arrTeam)
            ->whereIn('css.project_type_id', $arrProjectType)
            ->where('css.customer_name', $customerName)
            ->whereIn('css_team.team_id', $arrEmployeeTeam)
            ->groupBy('css.id')
            ->select('css.*')
            ->get();
    }

    /**
     * Get CSS by sale, team list and project type list and employee's team
     * @param int $saleId
     * @param string $teamIds
     * @param string $projectTypeIds
     * @param array $arrEmployeeTeam
     * return Css list
     */
    public static function getCssBySaleAndTeamIdsAndListProjectTypeAndEmployeeTeam($saleId, $teamIds, $projectTypeIds, $arrEmployeeTeam)
    {
        $arrProjectType = explode(',', $projectTypeIds);
        $arrTeam = explode(',', $teamIds);
        return self::leftJoin('projs', 'css.projs_id', '=', 'projs.id')
            ->leftJoin('team_members', 'team_members.employee_id', '=', 'projs.leader_id')
            ->leftJoin('teams', 'team_members.team_id', '=', 'teams.id')
            ->whereIn('teams.id', $arrTeam)
            ->whereIn('css.project_type_id', $arrProjectType)
            ->whereIn('teams.id', $arrEmployeeTeam)
            ->where('css.employee_id', $saleId)
            ->groupBy('css.id')
            ->select('css.*')
            ->get();
    }

    /**
     * Get CSS by sale, team list and project type list and employee's team
     * @param int $saleId
     * @param string $teamIds
     * @param string $projectTypeIds
     * @param int $employeeId
     * return Css list
     */
    public static function getCssBySaleAndTeamIdsAndListProjectTypeAndEmployee($saleId, $teamIds, $projectTypeIds, $employeeId)
    {
        $arrProjectType = explode(',', $projectTypeIds);
        $arrTeam = explode(',', $teamIds);
        return self::leftJoin('projs', 'css.projs_id', '=', 'projs.id')
            ->leftJoin('team_members', 'team_members.employee_id', '=', 'projs.leader_id')
            ->leftJoin('teams', 'team_members.team_id', '=', 'teams.id')
            ->whereIn('teams.id', $arrTeam)
            ->whereIn('css.project_type_id', $arrProjectType)
            ->where('css.employee_id', $employeeId)
            ->where('css.employee_id', $saleId)
            ->groupBy('css.id')
            ->select('css.*')
            ->get();
    }

    /**
     * Get CSS by sale, team list, project type list
     * @param int $saleId
     * @param string $teamIds
     * @param string $projectTypeIds
     * return Css list
     */
    public static function getCssBySaleAndTeamIdsAndListProjectType($saleId, $teamIds, $projectTypeIds)
    {
        $arrProjectType = explode(',', $projectTypeIds);
        $arrTeam = explode(',', $teamIds);
        return self::leftJoin('projs', 'css.projs_id', '=', 'projs.id')
            ->leftJoin('team_members', 'team_members.employee_id', '=', 'projs.leader_id')
            ->leftJoin('teams', 'team_members.team_id', '=', 'teams.id')
            ->whereIn('teams.id', $arrTeam)
            ->whereIn('css.project_type_id', $arrProjectType)
            ->where('css.employee_id', $saleId)
            ->groupBy('css.id')
            ->select('css.*')
            ->get();
    }

    public static function getCssByProjectNameAndTeamIdsAndListProjectTypeAndEmployee($projectName, $teamIds, $projectTypeIds, $employeeId)
    {
        $arrProjectType = explode(',', $projectTypeIds);
        $arrTeam = explode(',', $teamIds);
        return self::leftJoin('projs', 'css.projs_id', '=', 'projs.id')
            ->leftJoin('team_members', 'team_members.employee_id', '=', 'projs.leader_id')
            ->leftJoin('teams', 'team_members.team_id', '=', 'teams.id')
            ->whereIn('teams.id', $arrTeam)
            ->whereIn('css.project_type_id', $arrProjectType)
            ->where('css.project_name', $projectName)
            ->where('css.employee_id', $employeeId)
            ->groupBy('css.id')
            ->select('css.*')
            ->get();
    }

    public static function getCssByProjectNameAndTeamIdsAndListProjectTypeAndEmployeeTeam($projectName, $teamIds, $projectTypeIds, $arrEmployeeTeam)
    {
        $arrProjectType = explode(',', $projectTypeIds);
        $arrTeam = explode(',', $teamIds);
        return self::leftJoin('projs', 'css.projs_id', '=', 'projs.id')
            ->leftJoin('team_members', 'team_members.employee_id', '=', 'projs.leader_id')
            ->leftJoin('teams', 'team_members.team_id', '=', 'teams.id')
            ->whereIn('teams.id', $arrTeam)
            ->whereIn('css.project_type_id', $arrProjectType)
            ->where('css.project_name', $projectName)
            ->whereIn('teams.id', $arrEmployeeTeam)
            ->groupBy('css.id')
            ->select('css.*')
            ->get();
    }

    public static function getCssByProjectNameAndTeamIdsAndListProjectType($projectName, $teamIds, $projectTypeIds)
    {
        $arrProjectType = explode(',', $projectTypeIds);
        $arrTeam = explode(',', $teamIds);
        return self::leftJoin('projs', 'css.projs_id', '=', 'projs.id')
            ->leftJoin('team_members', 'team_members.employee_id', '=', 'projs.leader_id')
            ->leftJoin('teams', 'team_members.team_id', '=', 'teams.id')
            ->whereIn('teams.id', $arrTeam)
            ->whereIn('css.project_type_id', $arrProjectType)
            ->where('css.project_name', $projectName)
            ->groupBy('css.id')
            ->select('css.*')
            ->get();
    }

    /**
     *
     * @param string $cssResultIds
     * return object
     */

    /**
     * Get list leater 3*
     *
     * @param string $cssResultIds
     * @param int $perPage
     * @param string $orderBy
     * @param string $ariaType
     * @param array|null $filter
     * @return type
     */
    public static function getListLessThreeStar($cssResultIds, $perPage, $orderBy, $ariaType, $filter = null)
    {
        $arrResultId = explode(',', $cssResultIds);
        $result = CssResult::join('css', 'css.id', '=', 'css_result.css_id')
            ->join('css_result_detail', 'css_result_detail.css_result_id', '=', 'css_result.id')
            ->join('css_question', 'css_result_detail.question_id', '=', 'css_question.id')
            ->whereIn('css_result.id', $arrResultId)
            ->where('css_result_detail.point', '>=', 1)
            ->where('css_result_detail.point', '<=', 2)
            ->orderBy($orderBy, $ariaType)
            ->orderBy('comment', 'ASC')
            ->select('css_result.*', 'css_question.content as question_name', 'css.project_name', 'css_result_detail.point as point', 'css_result_detail.comment as comment', 'css_result.avg_point as result_point', 'css_result.created_at as result_make');
        if ($filter && count($filter)) {
            self::filterDataNormal($result, $filter);
        }
        return $result->paginate($perPage);
    }

    /**
     * lay danh sach cau hoi duoi 3 sao
     * @param int $questionId
     * @param string $cssResultIds
     * @param int $offset
     * @param int $perPage
     * @param array $filter
     * return object
     */
    public static function getListLessThreeStarByQuestionId($questionId, $cssResultIds, $perPage, $orderBy, $ariaType, $filter = null)
    {
        $arrResultId = explode(',', $cssResultIds);
        $result = CssResult::join('css', 'css.id', '=', 'css_result.css_id')
            ->join('css_result_detail', 'css_result_detail.css_result_id', '=', 'css_result.id')
            ->join('css_question', 'css_result_detail.question_id', '=', 'css_question.id')
            ->whereIn('css_result.id', $arrResultId)
            ->where('css_result_detail.point', '>=', 1)
            ->where('css_result_detail.point', '<=', 2)
            ->where('css_question.id', $questionId)
            ->orderBy($orderBy, $ariaType)
            ->orderBy('comment', 'ASC')
            ->select('css_result.*', 'css_question.content as question_name', 'css.project_name', 'css_result_detail.point as point', 'css_result_detail.comment as comment', 'css_result.avg_point as result_point', 'css_result.created_at as result_make');
        if ($filter && count($filter)) {
            self::filterDataNormal($result, $filter);
        }
        return $result->paginate($perPage);
    }

    /**
     * Get count less 3* list
     * @param string $cssResultIds
     * return int
     */
    public static function getCountListLessThreeStar($cssResultIds)
    {
        $sql = 'select * from css_result_detail '
            . 'where css_result_id IN (' . $cssResultIds . ') and point between 1 and 2';
        if (CssResultDetail::isUseSoftDelete()) {
            $sql .= ' and deleted_at is null ';
        }
        $result = DB::select($sql);
        return count($result);
    }

    /**
     * Get count less 3* list by question
     * @param int $questionId
     * @param string $cssResultIds
     * return int
     */
    public static function getCountListLessThreeStarByQuestion($questionId, $cssResultIds)
    {
        $sql = 'select * from css_result_detail '
            . 'where css_result_id IN (' . $cssResultIds . ') and point between 1 and 2 and question_id = ' . $questionId;
        if (CssResultDetail::isUseSoftDelete()) {
            $sql .= ' and deleted_at is null ';
        }
        $result = DB::select($sql);
        return count($result);
    }

    /**
     * get proposes list
     *
     * @param string $cssResultIds
     * @param int $perPage
     * @param string $orderBy
     * @param string $ariaType
     * @param array $filter
     * @return object
     */
    public static function getProposes($cssResultIds, $perPage, $orderBy, $ariaType, $filter)
    {
        $arrResultId = explode(',', $cssResultIds);
        $result = CssResult::join('css', 'css.id', '=', 'css_result.css_id')
            ->whereIn('css_result.id', $arrResultId)
            ->where('css_result.proposed', '<>', '')
            ->orderBy($orderBy, $ariaType)
            ->orderBy('proposed', 'desc')
            ->groupBy('css_result.id')
            ->select('css_result.*',
                'css_result.proposed as proposed',
                'css.project_name',
                'css_result.avg_point as result_point',
                'css_result.created_at as result_make'
            );
        if ($filter && count($filter)) {
            self::filterDataNormal($result, $filter);
        }
        return $result->paginate($perPage);
    }

    /**
     * get proposes list by question
     *
     * @param int $questionId
     * @param string $cssResultIds
     * @param int $perPage
     * @param string $orderBy
     * @param string $ariaType
     * @param array $filter
     * @return object list
     */
    public static function getProposesByQuestion(
        $questionId,
        $cssResultIds,
        $perPage,
        $orderBy,
        $ariaType,
        $filter = null
    )
    {
        $arrResultId = explode(',', $cssResultIds);
        $result = CssResult::join('css', 'css.id', '=', 'css_result.css_id')
            ->join('css_result_detail', 'css_result_detail.css_result_id', '=', 'css_result.id')
            ->whereIn('css_result.id', $arrResultId)
            ->where('css_result.proposed', '<>', '')
            ->where('css_result_detail.question_id', $questionId)
            ->where('css_result_detail.point', '>=', 1)
            ->where('css_result_detail.point', '<=', 2)
            ->orderBy($orderBy, $ariaType)
            ->orderBy('proposed', 'desc')
            ->select('css_result.*',
                'css_result.proposed as proposed',
                'css.project_name',
                'css_result.avg_point as result_point',
                'css_result.created_at as result_make');
        if ($filter && count($filter)) {
            self::filterDataNormal($result, $filter);
        }
        return $result->paginate($perPage);
    }

    /**
     * get count Proposes
     * @param string $cssResultIds
     * @return int
     */
    public static function getCountProposes($cssResultIds)
    {
        $sql = "Select * from css_result where id in ($cssResultIds) and proposed <> ''";
        if (CssResult::isUseSoftDelete()) {
            $sql .= ' and deleted_at is null';
        }
        $cssResult = DB::select($sql);
        return count($cssResult);
    }

    /**
     * get count Proposes
     * @param int $questionId
     * @param string $cssResultIds
     * @return int
     */
    public static function getCountProposesByQuestion($questionId, $cssResultIds)
    {
        $arrResultId = explode(',', $cssResultIds);
        $cssResult = CssResult::join('css', 'css.id', '=', 'css_result.css_id')
            ->join('css_result_detail', 'css_result_detail.css_result_id', '=', 'css_result.id')
            ->whereIn('css_result.id', $arrResultId)
            ->where('css_result.proposed', '<>', '')
            ->where('css_result_detail.question_id', $questionId)
            ->where('css_result_detail.point', '>=', 1)
            ->where('css_result_detail.point', '<=', 2)
            ->select('css_result.id')
            ->get();
        return count($cssResult);
    }

    /**
     * Get list pm
     */
    public static function getListPm()
    {
        $sql = "select distinct(pm_name) from css";
        if (self::isUseSoftDelete()) {
            $sql .= ' where deleted_at is null';
        }
        $pm = DB::select($sql);
        return $pm;
    }

    /**
     * Get list brse
     */
    public static function getListBrse()
    {
        $sql = "select distinct(brse_name) from css";
        if (self::isUseSoftDelete()) {
            $sql .= ' where deleted_at is null';
        }
        $brse = DB::select($sql);
        return $brse;
    }

    /**
     * Get list customer css
     */
    public static function getListCustomer()
    {
        $sql = "select distinct(customer_name) from css";
        if (self::isUseSoftDelete()) {
            $sql .= ' where deleted_at is null';
        }
        $cus = DB::select($sql);
        return $cus;
    }

    /**
     * Get list sale css
     */
    public static function getListSale()
    {
        $sql = "select distinct(employee_id) from css";
        if (self::isUseSoftDelete()) {
            $sql .= ' where deleted_at is null';
        }
        $sale = DB::select($sql);
        return $sale;
    }

    public static function getListProjectName()
    {
        $sql = "select distinct(project_name) from css";
        if (self::isUseSoftDelete()) {
            $sql .= ' where deleted_at is null';
        }
        $projName = DB::select($sql);
        return $projName;
    }

    /**
     *
     * @param ing $cssId
     * @param date $startDate
     * @param date $endDate
     */
    public static function getCssResultByCssId($cssId, $startDate, $endDate)
    {
        return CssResult::join('css', 'css.id', '=', 'css_result.css_id')
            ->where("css_id", $cssId)
            ->where("css_result.created_at", ">=", $startDate)
            ->where("css_result.created_at", "<=", $endDate)
            ->select('css_result.*')
            ->get();
    }

    /**
     * Get all Css list
     * @param int $perPage
     * @return Css list
     * @throws \Exception
     */
    public function getCssList($employee, $arrTeamId, $order, $dir)
    {
        $urlFilter = trim(URL::route('sales::css.list'), '/') . '/';
        $collection = self::leftJoin('css_project_type', 'css_project_type.id', '=', 'css.project_type_id')
            ->leftJoin('css_result', 'css.id', '=', 'css_result.css_id')
            ->leftJoin('css_view', 'css.id', '=', 'css_view.css_id')
            ->leftJoin('css_team', 'css.id', '=', 'css_team.css_id')
            ->leftJoin('employees as employee', 'employee.id', '=', 'css.employee_id')
            ->leftJoin('projs', 'projs.id', '=', 'css.projs_id')
            ->leftJoin('employees as leader', 'leader.id', '=', 'projs.leader_id')
            ->leftJoin('team_members', 'leader.id', '=', 'team_members.employee_id')
            ->leftJoin('teams as leader_team', 'team_members.team_id', '=', 'leader_team.id')
            ->leftJoin('teams', 'teams.id', '=', 'css_team.team_id');

        if ($employee) {
            $collection->where(function ($query) use ($employee) {
                $query->where('css.employee_id', $employee->id)
                    ->orWhere('css.pm_email', $employee->email)
                    ->orWhere('css.rikker_relate', 'LIKE', '%' . $employee->email . '%');
            });
            if ($arrTeamId) {
                $collection->orWhereIn('css_team.team_id', $arrTeamId);
            }
        }

        $categoryFilter = Form::getFilterData('except','css_project_type.name', $urlFilter);
        if ($categoryFilter) {
            $collection->where('css_project_type.id', $categoryFilter);
        }

        $projectFilter = Form::getFilterData('except', 'project_name', $urlFilter);
        if ($projectFilter) {
            $collection->where('css.project_name', 'LIKE', "%".addslashes(trim($projectFilter)) . "%");
        }

        $employeeFilter = Form::getFilterData('except','employees.name', $urlFilter);
        if ($employeeFilter) {
            $collection->where('employee.name', 'LIKE', "%".addslashes(trim($employeeFilter)) . "%");
        }

        $companyFilter = Form::getFilterData('except','company_name', $urlFilter);
        if ($companyFilter) {
            $collection->where('css.company_name', 'LIKE', "%".addslashes(trim($companyFilter)) . "%");
        }

        $customerFilter = Form::getFilterData('except','customer_name', $urlFilter);
        if ($customerFilter) {
            $collection->where('css.customer_name', 'LIKE', "%".addslashes(trim($customerFilter)) . "%");
        }
        
        $teamChargeFilter = Form::getFilterData('except','team_charge_id', $urlFilter);
        if ($teamChargeFilter) {
            $collection->where('leader_team.id', $teamChargeFilter);
        }

        $teamFilter = Form::getFilterData('except','teams.name', $urlFilter);
        if ($teamFilter) {
            $collection->where('css_team.team_id', $teamFilter);
        }

        $resultStatusNew = CssResult::STATUS_NEW;
        $resultStatusCancel = CssResult::STATUS_CANCEL;
        $resultStatusApproved = CssResult::STATUS_APPROVED;

        $collection = $collection->select('css.*',
            'css_project_type.name as project_type_name',
            'employee.name as sale_name',
            DB::raw("(select avg(avg_point) from css_result 
                              where css_id = css.id and status != {$resultStatusCancel} and (deleted_at is null or deleted_at = '0000-00-00 00:00:00')
                              ) AS avg_point"),
            DB::raw(
                '(select COUNT(css_view.id) from css_view where css_id = css.id) as countViewCss'),
            DB::raw(
                '(select max(created_at) from css_result
                            where css_id = css.id) as lastWork'),
            DB::raw("GROUP_CONCAT(DISTINCT css_result.status) AS analyze_status"),
            'leader_team.name as team_leader_name'
        );
        $collection->orderBy($order, $dir)
            ->groupBy('css.id');

        $dateCssCreatedFilter = Form::getFilterData('except', 'css_created_from', $urlFilter);
        if ($dateCssCreatedFilter) {
            $collection->whereDate('css.created_at', '>=', $dateCssCreatedFilter);
        }
        $dateCssCreatedFilterTo = Form::getFilterData('except', 'css_created_to', $urlFilter);
        if ($dateCssCreatedFilterTo) {
            $collection->whereDate('css.created_at', '<=', $dateCssCreatedFilterTo);
        }
        $dateFromFilter = Form::getFilterData('except','created_at', $urlFilter);
        // if ($dateFromFilter) {
        //     $collection->whereDate('css_result.created_at', '>=', $dateFromFilter);
        // }
        $dateToFilter = Form::getFilterData('except','updated_at', $urlFilter);
        // if ($dateToFilter) {
        //     $collection->whereDate('css_result.created_at', '<=', $dateToFilter);
        // }
        if ($dateFromFilter && $dateToFilter) {
            $collection->addSelect(DB::raw(
                "(select COUNT(css_result.id) from css_result where css_id = css.id and status != {$resultStatusCancel} and date(css_result.created_at) >= date('{$dateFromFilter}') and date(css_result.created_at) <= date('{$dateToFilter}') and (deleted_at is null or deleted_at = '0000-00-00 00:00:00') ) as countMakeCss"));
        } elseif ($dateFromFilter && !$dateToFilter) {
            $collection->addSelect(DB::raw(
                "(select COUNT(css_result.id) from css_result where css_id = css.id and status != {$resultStatusCancel} and date(css_result.created_at) >= date('{$dateFromFilter}') and (deleted_at is null or deleted_at = '0000-00-00 00:00:00') ) as countMakeCss")
            );
        } elseif (!$dateFromFilter && $dateToFilter) {
            $collection->addSelect(DB::raw(
                "(select COUNT(css_result.id) from css_result where css_id = css.id and status != {$resultStatusCancel} and date(css_result.created_at) <= date('{$dateToFilter}') and (deleted_at is null or deleted_at = '0000-00-00 00:00:00') ) as countMakeCss"));
        } else {
            $collection->addSelect(DB::raw(
                "(select COUNT(css_result.id) from css_result where css_id = css.id and status != {$resultStatusCancel} and (deleted_at is null or deleted_at = '0000-00-00 00:00:00') ) as countMakeCss")
            );
        }

        $filterStatus = Form::getFilterData('except', 'analyze_status', $urlFilter);
        if ($filterStatus) {
            if ($filterStatus == 3) {
                $collection->whereNull('css_result.id');
            } elseif ($filterStatus == CssResult::STATUS_NEW) {
                $collection->havingRaw("FIND_IN_SET({$resultStatusNew}, GROUP_CONCAT(DISTINCT css_result.status))");
            } else {
                $collection->havingRaw("NOT FIND_IN_SET({$resultStatusNew}, GROUP_CONCAT(DISTINCT css_result.status))");
            }
        }

        $filterStatusApprove = Form::getFilterData('except', 'approve_status', $urlFilter);
        if ($filterStatusApprove) {
            if ($filterStatusApprove == 3) {
                $collection->whereNull('css_result.id');
            } elseif ($filterStatusApprove == CssResult::STATUS_APPROVED) {
                $collection->havingRaw("FIND_IN_SET(GROUP_CONCAT(DISTINCT css_result.status), {$resultStatusApproved})");
            } else {
                $collection->havingRaw("NOT FIND_IN_SET(GROUP_CONCAT(DISTINCT css_result.status), {$resultStatusApproved})");
            }
        }
        return $collection;
    }

    public static function getAnalyzeStatus($analyzeStatus)
    {
        if (empty($analyzeStatus)) {
            return '';
        }
        $arrayStatus = explode(',', $analyzeStatus);
        if (is_array($arrayStatus)) {
            if (in_array(CssResult::STATUS_NEW, ($arrayStatus))) {
                return Lang::get('sales::view.Not analyzed yet');
            }
        }
        return Lang::get('sales::view.Analyzed');
    }

    /**
     * Export css point by quarter of team
     * @param null $year
     * @return mixed
     */
    public static function exportCss($year = null)
    {
        $collection = self::rightJoin('css_result', 'css_result.css_id', '=', 'css.id')
            ->join('projs', 'css.projs_id', '=', 'projs.id')
            ->join('team_members', 'team_members.employee_id', '=', 'projs.leader_id')
            ->join('teams', 'teams.id', '=', 'team_members.team_id')
            ->whereNotNull('projs.leader_id')
            ->groupBy(['team_id', 'quarter']);

        if (!empty($year)) {
            $collection->whereRaw("YEAR(css_result.created_at) = {$year}");
        }

        $select = [
            'css.projs_id',
            'teams.id as team_id',
            'teams.name as team_name',
            'projs.leader_id',
            'css.created_at',
            DB::raw('QUARTER(css_result.created_at) as quarter'),
            DB::raw('avg((select avg(avg_point) from css_result
                              where css_id = css.id and id In
                              (select max(id) from css_result where css_id = css.id group by css_id, code)
                              and (deleted_at is null or deleted_at = "0000-00-00 00:00:00")
                              )) AS avg_point')
        ];

        return $collection->select($select)->get();
    }

    /**
     * @param null $year
     * @return mixed
     */
    public static function exportCssComment($year = null)
    {
        $collection = self::rightJoin('css_result', 'css_result.css_id', '=', 'css.id')
            ->leftJoin('css_result_detail', 'css_result_detail.css_result_id', '=', 'css_result.id')
            ->leftJoin('projs', 'css.projs_id', '=', 'projs.id')
            ->leftJoin('team_members', 'team_members.employee_id', '=', 'projs.leader_id')
            ->leftJoin('teams', 'teams.id', '=', 'team_members.team_id')
            ->whereNotNull('projs.leader_id')
            ->where(function ($query) {
                $query->where('css_result_detail.comment', '!=', '')
                    ->orWhere('css_result_detail.analysis', '!=', '');
            });

        if (!empty($year)) {
            $collection->whereRaw("YEAR(css_result.created_at) = {$year}");
        }

        $select = [
            'css_result.id',
            'css.projs_id',
            'css_result.created_at',
            'teams.id as team_id',
            'teams.name as team_name',
            'projs.name as proj_name',
            'css_result_detail.comment as css_comment',
            'css_result_detail.analysis as css_analysis',
            DB::raw('QUARTER(css_result.created_at) as quarter'),
        ];

        return $collection->select($select)->get();
    }

    /**
     * Get project make information
     * @param int $resultId
     */
    public function projectMakeInfo($resultId)
    {
        return self::join('css_result', 'css.id', '=', 'css_result.css_id')
            ->join('employees', 'employees.id', '=', 'css.employee_id')
            ->leftJoin('projs', 'projs.id', '=', 'css.projs_id')
            ->where('css_result.id', $resultId)
            ->groupBy('css_result.id')
            ->select('css.*', 'employees.japanese_name', 'css_result.created_at as make_date', 'css_result.name as make_name', 'css_result.email as make_email', 'css_result.avg_point as point', 'css_result.proposed', 'projs.type')
            ->first();
    }

    public function clearAll()
    {
        DB::beginTransaction();
        try {
            DB::table("css_result_detail")->delete();
            DB::table("css_result")->delete();
            DB::table("css_view")->delete();
            DB::table("css_team")->delete();
            DB::table("css_mail")->delete();
            DB::table("css")->delete();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /**
     * Get total view CSS
     *
     * @param int $cssId
     * @return int
     */
    public static function getTotalView($cssId)
    {
        return CssView::where('css_id', $cssId)->count();
    }

    /**
     * Get total make CSS
     *
     * @param int $cssId
     * @return int
     */
    public static function getTotalMake($cssId)
    {
        return CssResult::where('css_id', $cssId)->count();
    }

    public static function getCssCommentByProject($projId, $month, $year)
    {
        $projTable = Project::getTableName();
        $cssTable = self::getTableName();
        $cssResultTable = CssResult::getTableName();
        $cssResultDetailTable = CssResultDetail::getTableName();
        $cssQuestionTable = CssQuestion::getTableName();
        return self::join("{$projTable}", "{$projTable}.id", "=", "{$cssTable}.projs_id")
            ->join("{$cssResultTable}", "{$cssResultTable}.css_id", "=", "{$cssTable}.id")
            ->join("{$cssResultDetailTable}", "{$cssResultDetailTable}.css_result_id", "=", "{$cssResultTable}.id")
            ->join("{$cssQuestionTable}", "{$cssResultDetailTable}.question_id", "=", "{$cssQuestionTable}.id")
            ->where("{$projTable}.id", $projId)
            ->where("{$cssResultDetailTable}.comment", "<>", "")
            ->whereRaw("MONTH({$cssTable}.updated_at) = $month")
            ->whereRaw("YEAR({$cssTable}.updated_at) = $year")
            ->select('content', 'comment')
            ->get();
    }

    /**
     * get css of project, follow status css is new or feedback.
     * without css don't customers do.
     *
     * @param $projectId
     * @return collection
     */
    public static function getCssFollowStatus($projectId)
    {
        $statusCondition = [self::STATUS_NEW, self::STATUS_FEEDBACK];
        $cssTbl = self::getTableName();
        $cssResultTbl = CssResult::getTableName();

        $collection = self::select("{$cssTbl}.id",
            "{$cssTbl}.token",
            "{$cssTbl}.status",
            DB::raw('(select COUNT(css_result.id) from css_result where css_id = css.id and deleted_at IS NULL) as countMakeCss')
        )
            ->where("{$cssTbl}.projs_id", '=', $projectId)
            ->whereIn("{$cssTbl}.status", $statusCondition)
            ->get();

        // get collection where  countMakeCss > 0.
        return $collection->reject(function ($value, $key) {
            return $value->countMakeCss == 0;
        });
    }

    public static function getCssNotMakeByProject($projectId)
    {
        $statusCondition = [self::STATUS_NEW, self::STATUS_FEEDBACK];
        $cssTbl = self::getTableName();
        $cssResultTbl = CssResult::getTableName();
        return self::whereIn("{$cssTbl}.status", $statusCondition)
            ->where("{$cssTbl}.projs_id", '=', $projectId)
            ->whereRaw("id NOT IN (SELECT css_id FROM {$cssResultTbl} WHERE deleted_at is null)")
            ->get();
    }

    /**
     * get label status css.
     */
    public static function getLabelStatusCss()
    {
        return [
            self::STATUS_NEW => 'New',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_FEEDBACK => 'Feedback',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REVIEW => 'Reviewed',
        ];
    }

    /**
     * get rikker relate css and has permission approve css detail.
     *
     * @param [object] $css.
     * @return array.
     */
    public static function getRikkerRelateCssId($css)
    {
        $stringEmail = $css->rikker_relate;
        $listEmail = explode(',', $stringEmail);
        $arrayEmpId = [];
        if (isset($listEmail) && $listEmail) {
            $collection = Employee::getEmpByEmails($listEmail);

            foreach ($collection as $itemEmp) {
                if (PermissionModel::isScopeCompanyOfRoute($itemEmp->id, 'approve.detail.css')) {
                    $arrayEmpId[] = $itemEmp->id;
                }
            }
        }
        return $arrayEmpId;
    }

    public static function cancelCssById($request)
    {
        if (isset($request->cssId)) {
            $css = self::find($request->cssId);
            $css->status = self::STATUS_CANCEL;
            $css->save();
            return $css;
        }
        return false;
    }

    public static function checkCustomerConfirmCss($id)
    {
        return CssResult::where('css_result.css_id', '=', $id)->count();
    }

    /*
     * get onsite range date
     */
    public function getOnsiteRangeDate()
    {
        if (!$this->start_onsite_date) {
            return;
        }
        return Carbon::parse($this->start_onsite_date)->format('Y/m/d')
            . ' - ' . Carbon::parse($this->end_onsite_date)->format('Y/m/d');
    }
}
