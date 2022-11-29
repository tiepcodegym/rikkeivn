<?php

namespace Rikkei\Sales\Model;

use Rikkei\Sales\Model\Css;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Project\Model\ProjectPoint;
use Rikkei\Project\Model\Project;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Project\Model\ProjPointBaseline;
use Illuminate\Support\Facades\Log;
use Lang;
use Rikkei\Core\View\Form;

class CssResult extends \Rikkei\Core\Model\CoreModel
{
    protected $table = 'css_result';
    
    use SoftDeletes;

    const STATUS_NEW = 1;
    const STATUS_CANCEL = 2;
    const STATUS_FEEDBACK = 4;
    const STATUS_SUBMITTED = 7;
    const STATUS_APPROVED = 9;
    const STATUS_REVIEW = 10;
    const KEY_CACHE = 'css_result_cache';
    /**
     * Insert into table css_result
     * @param array $data
     */
    public function insertCssResult($data){
        DB::beginTransaction();
        try {
            $cssResult = new CssResult();
            $cssResult->css_id = $data["css_id"];
            $cssResult->name = $data["name"];
            $cssResult->email = $data["email"];
            $cssResult->proposed = $data["proposed"];
            $cssResult->avg_point = $data["avg_point"];
            $cssResult->status = self::STATUS_NEW;
            $cssResult->code = $data["code"];
            $cssResult->save();
            try {
                self::afterSaveCssResult($cssResult);
            } catch (Exception $ex2) {
                Log::error($ex2);
            }
            DB::commit();
            return $cssResult->id;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
        
    }
    
    /**
     * after save process insert css result
     *  process project dashboard
     * 
     * @param object $cssResult
     */
    protected static function afterSaveCssResult($cssResult)
    {
        $css = Css::find($cssResult->css_id);
        $project = Project::find($css->projs_id);
        if (!$css || !$css->projs_id || !$project) {
            return;
        }
        $projectPoint = ProjectPoint::findFromProject($css->projs_id);
        $cssValue = self::getCssFromProjId($css->projs_id);
        $projectPoint->css_css = $cssValue;
        $projectPoint->save([], ['not_employee' => 1]);
        //refresh data
        $project->refreshFlatDataPoint();
        if ($project->state != Project::STATE_CLOSED) {
            return true;
        }
        //save project point baseline
        $projPointBaselineLast = ProjPointBaseline::getBlLast($css->projs_id);
        if (!$projPointBaselineLast) {
            return true;
        }
        $projPointBaselineLast->css_css = $cssValue;
        $projPointBaselineLast->setIsBaseline(false);
        $projPointBaselineLast->css_css_point = $projPointBaselineLast->getCssCsPoint($cssValue);
        // color
        $projPointBaselineLast->css = $projPointBaselineLast->getCssColor(
            $projPointBaselineLast->css_css,
            $projPointBaselineLast->css_ci_negative
        );
        if ($projPointBaselineLast->summary < $projPointBaselineLast->css) {
            $projPointBaselineLast->summary = $projPointBaselineLast->css;
        }
        $projPointBaselineLast->point_total = $projPointBaselineLast->getTotalPoint([], true);
        unset($projPointBaselineLast->isBaseline);
        $projPointBaselineLast->save([], ['not_employee' => 1]);
    }

    /**
     * Get css result count
     * @param type $cssId
     * @return count css result
     */
    public function getCountCssResultByCss($cssId){
        return self::where("css_id",$cssId)->count();
    }
    
    /**
     * When Css only have once Css result then use this to get Css result
     * @param int $cssId
     */
    public function getCssResultFirstByCss($cssId, $status = false){
        $sql = self::where('css_id', $cssId);
        if ($status) {
            $sql = $sql->where('status', '!=', self::STATUS_CANCEL);
        }
        $sql = $sql->first();
        return $sql;
    }

    public static function getCssResultFirstByProjId($projId){
        $sql = self::select('css_result.avg_point')
                    ->leftJoin('css', 'css.id', '=', 'css_result.css_id')
                    ->where('css.projs_id', $projId)
                    ->whereNull('css.deleted_at')
                    ->whereNull('css_result.deleted_at')
                    ->orderBy('css.updated_at', 'DESC')
                    ->orderBy('css_result.updated_at', 'DESC');
        $sql = $sql->first();
        if ($sql) {
            return $sql->avg_point;
        }
        return false;
    }
    
    /**
     * Get Css result list by Css
     * @param int $cssId
     * @param int $perPage
     * @return object list css result
     */
    public function getCssResulByCss($cssId, $order, $dir)
    {
        $textNotYet = Lang::get('sales::view.Not analyzed yet');
        $textAnalyzed = Lang::get('sales::view.Analyzed');
        $resultStatusNew = CssResult::STATUS_NEW;
        $resultStatusCancel = CssResult::STATUS_CANCEL;

        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('employees', 'employees.id', '=', 'css.employee_id')
                ->join(DB::raw("(select max(id) as last_id from css_result group by css_id, code) result_max"), "result_max.last_id", "=", "css_result.id")
                ->where("css_result.css_id",$cssId)
                ->orderBy($order, $dir)
                ->groupBy('css_result.id')
                ->select(
                        'css_result.*',
                        'employees.name as sale_name', 
                        'result_max.last_id',
                        DB::raw("(CASE
                                    WHEN css_result.status = {$resultStatusNew}  THEN '{$textNotYet}'
                                    ELSE '{$textAnalyzed}'
                                END) AS analyze_status"
                        ),
                        DB::raw("(Select count(cr.id) from css_result cr where css_id = css.id and code = css_result.code and status != {$resultStatusCancel}) as count_make"));
    }

    public function getAllCssResul($cssId, $order, $dir)
    {
        $textNotYet = Lang::get('sales::view.Not analyzed yet');
        $textAnalyzed = Lang::get('sales::view.Analyzed');
        $resultStatusNew = CssResult::STATUS_NEW;
        
        $list = self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('employees', 'employees.id', '=', 'css.employee_id')
                ->where("css_result.css_id",$cssId)
                ->orderBy($order, $dir)
                ->select([
                        'css_result.*',
                        'employees.name as sale_name', 
                        'css_result.status',
                    ]);
        $filterStatus = Form::getFilterData('except', 'css_result.status');
        if (!$filterStatus) {
            $filterStatus = static::getFilterStatusDefault();
        }
        $list->whereIn('css_result.status', $filterStatus);
        return $list;
    }
    
    /**
     * Get max, min, avg point of overview question
     * @param int $projectTypeId
     * @param date $startDate
     * @param date $endDate
     * @param string $teamIds
     */
    public function getQuestionOverviewInfoAnalyze($projectTypeId,$startDate, $endDate,$teamIds){
        return self::whereIn('css_id',function($query) use ($projectTypeId){
                        $query->select('id')
                            ->from(with(new Css)->getTable())
                            ->where('project_type_id', $projectTypeId);
                        })
                    ->where('created_at','>=',$startDate)
                    ->where('created_at','<=',$endDate)
                    ->get();
    }
    
    /**
     * Get Css result by projects type 
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @return object
     */
    public function getCssResultByProjectTypeIds($projectTypeIds,$startDate, $endDate, $teamIds){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=', $startDate)
                ->where('css_result.created_at','<=', $endDate)
                ->orderBy('css.end_date','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get Css result by projects type 
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @param int $employeeId
     * @return object
     */
    public function getCssResultByProjectTypeIdsAndEmployee($projectTypeIds,$startDate, $endDate, $teamIds, $employeeId){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->whereDate('css_result.created_at','>=', $startDate)
                ->whereDate('css_result.created_at','<=', $endDate)
                ->where('css.employee_id','=',$employeeId)
                ->orderBy('css_result.created_at','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get Css result by projects type 
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @param array $arrEmployeeTeam
     * @return object
     */
    public function getCssResultByProjectTypeIdsAndEmployeeTeam($projectTypeIds,$startDate, $endDate, $teamIds, $arrEmployeeTeam){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->whereIn('css_team.team_id',$arrEmployeeTeam)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->orderBy('css.end_date','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get Css result by projects type 
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @return object
     */
    public function getCssResultPaginateByProjectTypeIds(
        $projectTypeIds,
        $startDate, 
        $endDate, 
        $teamIds,
        $perPage,
        $orderBy,
        $ariaType,
        $filter = null
    ){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        
        $result = self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->orderBy($orderBy,$ariaType)
                ->groupBy('css_result.id')
                ->select('css_result.*',
                        'css.end_date',
                        'css.project_name',
                        'css.pm_name as pmName',
                        'teams.name as teamName',
                        'css_result.avg_point as result_point',
                        'css_result.created_at as result_make');
        if ($filter) {
            self::filterDataNormal($result, $filter);
        }
        return $result->paginate($perPage);
    }
    
    /**
     * Get Css result by projects type 
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @param int $employeeId
     * @param array $filter
     * @return object
     */
    public function getCssResultPaginateByProjectTypeIdsAndEmployee(
        $projectTypeIds,
        $startDate, 
        $endDate, 
        $teamIds,
        $perPage,
        $orderBy,
        $ariaType,
        $employeeId,
        $filter = null
    ){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        
        $result = self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->where('css.employee_id','=',$employeeId)
                ->orderBy($orderBy,$ariaType)
                ->groupBy('css_result.id')
                ->select('css_result.*',
                        'css.end_date',
                        'css.project_name',
                        'css.pm_name as pmName',
                        'teams.name as teamName',
                        'css_result.avg_point as result_point',
                        'css_result.created_at as result_make');
        if ($filter) {
            self::filterDataNormal($result, $filter);
        }
        return $result->paginate($perPage);
    }
    
    /**
     * Get Css result by projects type 
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @param array $arrEmployeeTeam
     * @return object
     */
    public function getCssResultPaginateByProjectTypeIdsAndEmployeeTeam(
        $projectTypeIds,
        $startDate, 
        $endDate, 
        $teamIds,
        $perPage,
        $orderBy,
        $ariaType,
        $arrEmployeeTeam,
        $filter = null
    ){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        
        $result = self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->whereIn('css_team.team_id',$arrEmployeeTeam)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->orderBy($orderBy,$ariaType)
                ->groupBy('css_result.id')
                ->select('css_result.*',
                        'css.end_date',
                        'css.project_name',
                        'css.pm_name as pmName',
                        'teams.name as teamName',
                        'css_result.avg_point as result_point',
                        'css_result.created_at as result_make');
        if ($filter) {
            self::filterDataNormal($result, $filter);
        }
        return $result->paginate($perPage);
    }
    
    /**
     * Get Css result by project type
     * @param int $projectTypeId
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @return CssResult list
     */
    public function getCssResultByProjectTypeId($projectTypeId,$startDate, $endDate, $teamIds){
        $arrTeamId = explode(",", $teamIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->where('css.project_type_id',$projectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get Css result by project type
     * @param int $projectTypeId
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @param int $employeeId
     * @return CssResult list
     */
    public function getCssResultByProjectTypeIdAndEmployee($projectTypeId,$startDate, $endDate, $teamIds, $employeeId){
        $arrTeamId = explode(",", $teamIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->where('css.project_type_id',$projectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->where('css.employee_id','=',$employeeId)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get Css result by project type
     * @param int $projectTypeId
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @param array $arrEmployeeTeam
     * @return CssResult list
     */
    public function getCssResultByProjectTypeIdAndEmployeeTeam($projectTypeId,$startDate, $endDate, $teamIds, $arrEmployeeTeam){
        $arrTeamId = explode(",", $teamIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->where('css.project_type_id',$projectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->whereIn('css_team.team_id',$arrEmployeeTeam)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get css result by team id
     * @param int $teamId
     * @param date $startDate
     * @param date $endDate 
     * @param string $projectTypeIds
     * @return object
     */
    public function getCssResultByTeamId($teamId,$startDate, $endDate, $projectTypeIds){
        $arrProjectTypeId = explode(",", $projectTypeIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->where('css_team.team_id',$teamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get css result by team id
     * @param int $teamId
     * @param date $startDate
     * @param date $endDate 
     * @param string $projectTypeIds
     * @param int $employeeId
     * @return object
     */
    public function getCssResultByTeamIdAndEmployee($teamId,$startDate, $endDate, $projectTypeIds,$employeeId){
        $arrProjectTypeId = explode(",", $projectTypeIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->where('css_team.team_id',$teamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->where('css.employee_id','=',$employeeId)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get css result by team id
     * @param int $teamId
     * @param date $startDate
     * @param date $endDate 
     * @param string $projectTypeIds
     * @param array $arrEmployeeTeam
     * @return object
     */
    public function getCssResultByTeamIdAndEmployeeTeam($teamId,$startDate, $endDate, $projectTypeIds,$arrEmployeeTeam){
        $arrProjectTypeId = explode(",", $projectTypeIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->where('css_team.team_id',$teamId)
                ->whereIn('css_team.team_id',$arrEmployeeTeam)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get css result by team id
     * @param string $pmName
     * @param string $teamId
     * @param date $startDate
     * @param date $endDate 
     * @param string $projectTypeIds
     * @return object
     */
    public function getCssResultByPmName($pmName,$teamIds,$startDate, $endDate, $projectTypeIds){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->where('css.pm_name', $pmName)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get css result by team id
     * @param string $pmName
     * @param string $teamId
     * @param date $startDate
     * @param date $endDate 
     * @param string $projectTypeIds
     * @param int $employeeId
     * @return object
     */
    public function getCssResultByPmNameAndEmployee($pmName,$teamIds,$startDate, $endDate, $projectTypeIds,$employeeId){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->where('css.employee_id','=',$employeeId)
                ->where('css.pm_name', $pmName)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get css result by team id
     * @param string $pmName
     * @param string $teamId
     * @param date $startDate
     * @param date $endDate 
     * @param string $projectTypeIds
     * @param array $arrEmployeeTeam
     * @return object
     */
    public function getCssResultByPmNameAndEmployeeTeam($pmName,$teamIds,$startDate, $endDate, $projectTypeIds,$arrEmployeeTeam){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->whereIn('css_team.team_id',$arrEmployeeTeam)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->where('css.pm_name', $pmName)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get css result by team id
     * @param string $pmName
     * @param string $teamId
     * @param date $startDate
     * @param date $endDate 
     * @param string $projectTypeIds
     * @return object
     */
    public function getCssResultByBrseName($brseName,$teamIds,$startDate, $endDate, $projectTypeIds){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->where('css.brse_name', $brseName)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get css result by team id
     * @param string $pmName
     * @param string $teamId
     * @param date $startDate
     * @param date $endDate 
     * @param string $projectTypeIds
     * @param int $employeeId
     * @return object
     */
    public function getCssResultByBrseNameAndEmployee($brseName,$teamIds,$startDate, $endDate, $projectTypeIds, $employeeId){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->where('css.employee_id','=',$employeeId)
                ->where('css.brse_name', $brseName)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get css result by team id
     * @param string $pmName
     * @param string $teamId
     * @param date $startDate
     * @param date $endDate 
     * @param string $projectTypeIds
     * @param array $arrEmployeeTeam
     * @return object
     */
    public function getCssResultByBrseNameAndEmployeeTeam($brseName,$teamIds,$startDate, $endDate, $projectTypeIds, $arrEmployeeTeam){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->whereIn('css_team.team_id',$arrEmployeeTeam)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->where('css.brse_name', $brseName)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get css result by Customer
     * @param string $pmName
     * @param string $teamId
     * @param date $startDate
     * @param date $endDate 
     * @param string $projectTypeIds
     * @return object
     */
    public function getCssResultByCustomerName($customerName,$teamIds,$startDate, $endDate, $projectTypeIds){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->where('css.customer_name', $customerName)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }

    public function getCssResultByProjectName($projectName,$teamIds,$startDate, $endDate, $projectTypeIds){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
            ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
            ->join('teams', 'teams.id', '=', 'css_team.team_id')
            ->whereIn('css.project_type_id',$arrProjectTypeId)
            ->whereIn('css_team.team_id',$arrTeamId)
            ->where('css_result.created_at','>=',$startDate)
            ->where('css_result.created_at','<=',$endDate)
            ->where('css.project_name', $projectName)
            ->orderBy('css_result.created_at','ASC')
            ->orderBy('css_result.id','ASC')
            ->groupBy('css_result.id')
            ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
            ->get();
    }
    
    /**
     * Get css result by Customer
     * @param string $pmName
     * @param string $teamId
     * @param date $startDate
     * @param date $endDate 
     * @param string $projectTypeIds
     * @param int $employeeId
     * @return object
     */
    public function getCssResultByCustomerNameAndEmployee($customerName,$teamIds,$startDate, $endDate, $projectTypeIds, $employeeId){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->where('css.employee_id', $employeeId)
                ->where('css.customer_name', $customerName)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }

    public function getCssResultByProjectNameAndEmployee($projectName,$teamIds,$startDate, $endDate, $projectTypeIds, $employeeId){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
            ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
            ->join('teams', 'teams.id', '=', 'css_team.team_id')
            ->whereIn('css.project_type_id',$arrProjectTypeId)
            ->whereIn('css_team.team_id',$arrTeamId)
            ->where('css_result.created_at','>=',$startDate)
            ->where('css_result.created_at','<=',$endDate)
            ->where('css.employee_id', $employeeId)
            ->where('css.project_name', $projectName)
            ->orderBy('css_result.created_at','ASC')
            ->orderBy('css_result.id','ASC')
            ->groupBy('css_result.id')
            ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
            ->get();
    }
    
    /**
     * Get css result by Customer
     * @param string $pmName
     * @param string $teamId
     * @param date $startDate
     * @param date $endDate 
     * @param string $projectTypeIds
     * @param array $arrEmployeeTeam
     * @return object
     */
    public function getCssResultByCustomerNameAndEmployeeTeam($customerName,$teamIds,$startDate, $endDate, $projectTypeIds, $arrEmployeeTeam){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->whereIn('css_team.team_id',$arrEmployeeTeam)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->where('css.customer_name', $customerName)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }

    public function getCssResultByProjectNameAndEmployeeTeam($projectName,$teamIds,$startDate, $endDate, $projectTypeIds, $arrEmployeeTeam){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
            ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
            ->join('teams', 'teams.id', '=', 'css_team.team_id')
            ->whereIn('css.project_type_id',$arrProjectTypeId)
            ->whereIn('css_team.team_id',$arrTeamId)
            ->whereIn('css_team.team_id',$arrEmployeeTeam)
            ->where('css_result.created_at','>=',$startDate)
            ->where('css_result.created_at','<=',$endDate)
            ->where('css.project_name', $projectName)
            ->orderBy('css_result.created_at','ASC')
            ->orderBy('css_result.id','ASC')
            ->groupBy('css_result.id')
            ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
            ->get();
    }
    
    /**
     * Get list css by list pm name, list team id, start date, end date and list project type id
     * @param string $listPmName
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @return object
     */
    public function getCssResultByListPm($listPmName,$projectTypeIds,$startDate, $endDate, $teamIds){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrPmName = explode(",", $listPmName);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->whereIn('css.pm_name', $arrPmName)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get list css by list pm name, list team id, start date, end date and list project type id
     * @param string $listPmName
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @param int $employeeId
     * @return object
     */
    public function getCssResultByListPmAndEmployee($listPmName,$projectTypeIds,$startDate, $endDate, $teamIds,$employeeId){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrPmName = explode(",", $listPmName);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->where('css.employee_id','=',$employeeId)
                ->whereIn('css.pm_name', $arrPmName)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get list css by list pm name, list team id, start date, end date and list project type id
     * @param string $listPmName
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @param array $arrEmployeeTeam
     * @return object
     */
    public function getCssResultByListPmAndEmployeeTeam($listPmName,$projectTypeIds,$startDate, $endDate, $teamIds,$arrEmployeeTeam){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrPmName = explode(",", $listPmName);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->whereIn('css_team.team_id',$arrEmployeeTeam)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->whereIn('css.pm_name', $arrPmName)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }

    public function getCssResultByListProjectNameAndEmployee($listProjectName, $projectTypeIds, $startDate, $endDate, $teamIds,$employeeId){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrProjectName = explode(",", $listProjectName);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
            ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
            ->join('teams', 'teams.id', '=', 'css_team.team_id')
            ->whereIn('css.project_type_id', $arrProjectTypeId)
            ->whereIn('css_team.team_id', $arrTeamId)
            ->where('css_result.created_at','>=', $startDate)
            ->where('css_result.created_at','<=', $endDate)
            ->where('css.employee_id','=',$employeeId)
            ->whereIn('css.project_name', $arrProjectName)
            ->orderBy('css_result.created_at','ASC')
            ->orderBy('css_result.id','ASC')
            ->groupBy('css_result.id')
            ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
            ->get();
    }

    public function getCssResultByListProjectNameAndEmployeeTeam($listProjectName, $projectTypeIds, $startDate, $endDate, $teamIds, $arrEmployeeTeam){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrProjectName = explode(",", $listProjectName);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
            ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
            ->join('teams', 'teams.id', '=', 'css_team.team_id')
            ->whereIn('css.project_type_id',$arrProjectTypeId)
            ->whereIn('css_team.team_id', $arrTeamId)
            ->whereIn('css_team.team_id', $arrEmployeeTeam)
            ->where('css_result.created_at','>=', $startDate)
            ->where('css_result.created_at','<=', $endDate)
            ->whereIn('css.project_name', $arrProjectName)
            ->orderBy('css_result.created_at','ASC')
            ->orderBy('css_result.id','ASC')
            ->groupBy('css_result.id')
            ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
            ->get();
    }

    public function getCssResultByListProjectName($listProjectName, $projectTypeIds, $startDate, $endDate, $teamIds){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrProjectName = explode(",", $listProjectName);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
            ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
            ->join('teams', 'teams.id', '=', 'css_team.team_id')
            ->whereIn('css.project_type_id',$arrProjectTypeId)
            ->whereIn('css_team.team_id', $arrTeamId)
            ->where('css_result.created_at','>=', $startDate)
            ->where('css_result.created_at','<=', $endDate)
            ->whereIn('css.project_name', $arrProjectName)
            ->orderBy('css_result.created_at','ASC')
            ->orderBy('css_result.id','ASC')
            ->groupBy('css_result.id')
            ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
            ->get();
    }
    
    /**
     * Get list css by list pm name, list team id, start date, end date and list project type id
     * @param string $listPmName
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @param int $perPage
     * @param string $orderBy
     * @param string $ariaType
     * @param array $filter
     * @return object
     */
    public function getCssResultPaginateByListPm(
        $listPmName,
        $projectTypeIds,
        $startDate, 
        $endDate, 
        $teamIds,
        $perPage,
        $orderBy,
        $ariaType,
        $filter = null
    ){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrPmName = explode(",", $listPmName);
        $result = self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->whereIn('css.pm_name', $arrPmName)
                ->orderBy($orderBy,$ariaType)
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*',
                        'css.end_date',
                        'css.project_name',
                        'css.pm_name as pmName',
                        'teams.name as teamName',
                        'css_result.avg_point as result_point',
                        'css_result.created_at as result_make');
        if ($filter) {
            self::filterDataNormal($result, $filter);
        }
        return $result->paginate($perPage);
    }

    public function getCssResultPaginateByListProjectName(
        $listProjectName,
        $projectTypeIds,
        $startDate,
        $endDate,
        $teamIds,
        $perPage,
        $orderBy,
        $ariaType,
        $filter = null
    ){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrProjectName = explode(",", $listProjectName);
        $result = self::join('css', 'css.id', '=', 'css_result.css_id')
            ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
            ->join('teams', 'teams.id', '=', 'css_team.team_id')
            ->whereIn('css.project_type_id', $arrProjectTypeId)
            ->whereIn('css_team.team_id', $arrTeamId)
            ->where('css_result.created_at','>=', $startDate)
            ->where('css_result.created_at','<=', $endDate)
            ->whereIn('css.project_name', $arrProjectName)
            ->orderBy($orderBy, $ariaType)
            ->orderBy('css_result.id','ASC')
            ->groupBy('css_result.id')
            ->select('css_result.*',
                'css.end_date',
                'css.project_name',
                'css.pm_name as pmName',
                'teams.name as teamName',
                'css_result.avg_point as result_point',
                'css_result.created_at as result_make');
        if ($filter) {
            self::filterDataNormal($result, $filter);
        }
        return $result->paginate($perPage);
    }
    
    /**
     * Get list css by list pm name, list team id, start date, end date and list project type id
     * @param string $listPmName
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @param int $perPage
     * @param string $orderBy
     * @param string $ariaType
     * @param int $employeeId
     * @param array $filter
     * @return CssResult list
     */
    public function getCssResultPaginateByListPmAndEmployee(
        $listPmName,
        $projectTypeIds,
        $startDate, 
        $endDate, 
        $teamIds,
        $perPage,
        $orderBy,
        $ariaType,
        $employeeId,
        $filter = null
    ){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrPmName = explode(",", $listPmName);
        $collection = self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->where('css.employee_id','=',$employeeId)
                ->whereIn('css.pm_name', $arrPmName)
                ->orderBy($orderBy,$ariaType)
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*',
                        'css.end_date',
                        'css.project_name',
                        'css.pm_name as pmName',
                        'teams.name as teamName',
                        'css_result.avg_point as result_point',
                        'css_result.created_at as result_make');
        if ($filter) {
            self::filterDataNormal($collection, $filter);
        }
        return $collection->paginate($perPage);
    }

    public function getCssResultPaginateByListProjectNameAndEmployee(
        $listProjectName,
        $projectTypeIds,
        $startDate,
        $endDate,
        $teamIds,
        $perPage,
        $orderBy,
        $ariaType,
        $employeeId,
        $filter = null
    ){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrProjectName = explode(",", $listProjectName);
        $collection = self::join('css', 'css.id', '=', 'css_result.css_id')
            ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
            ->join('teams', 'teams.id', '=', 'css_team.team_id')
            ->whereIn('css.project_type_id', $arrProjectTypeId)
            ->whereIn('css_team.team_id', $arrTeamId)
            ->where('css_result.created_at','>=', $startDate)
            ->where('css_result.created_at','<=', $endDate)
            ->where('css.employee_id','=', $employeeId)
            ->whereIn('css.project_name', $arrProjectName)
            ->orderBy($orderBy, $ariaType)
            ->orderBy('css_result.id','ASC')
            ->groupBy('css_result.id')
            ->select('css_result.*',
                'css.end_date',
                'css.project_name',
                'css.pm_name as pmName',
                'teams.name as teamName',
                'css_result.avg_point as result_point',
                'css_result.created_at as result_make');
        if ($filter) {
            self::filterDataNormal($collection, $filter);
        }
        return $collection->paginate($perPage);
    }
    
    /**
     * Get list css by list pm name, list team id, start date, end date and list project type id
     * @param string $listPmName
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @param array $perPage
     * @param string $orderBy
     * @param string $ariaType
     * @param array $arrEmployeeTeam
     * @param array $filter
     * @return CssResult list
     */
    public function getCssResultPaginateByListPmAndEmployeeTeam(
        $listPmName,
        $projectTypeIds,
        $startDate, 
        $endDate, 
        $teamIds,
        $perPage,
        $orderBy,
        $ariaType,
        $arrEmployeeTeam,
        $filter = null
    ){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrPmName = explode(",", $listPmName);
        $collection = self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->whereIn('css_team.team_id',$arrEmployeeTeam)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->whereIn('css.pm_name', $arrPmName)
                ->orderBy($orderBy,$ariaType)
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*',
                        'css.end_date',
                        'css.project_name',
                        'css.pm_name as pmName',
                        'teams.name as teamName',
                        'css_result.avg_point as result_point',
                        'css_result.created_at as result_make');
        if ($filter) {
            self::filterDataNormal($collection, $filter);
        }
        return $collection->paginate($perPage);
    }

    public function getCssResultPaginateByListProjectNameAndEmployeeTeam(
        $listProjectName,
        $projectTypeIds,
        $startDate,
        $endDate,
        $teamIds,
        $perPage,
        $orderBy,
        $ariaType,
        $arrEmployeeTeam,
        $filter = null
    ){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrProjectName = explode(",", $listProjectName);
        $collection = self::join('css', 'css.id', '=', 'css_result.css_id')
            ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
            ->join('teams', 'teams.id', '=', 'css_team.team_id')
            ->whereIn('css.project_type_id', $arrProjectTypeId)
            ->whereIn('css_team.team_id', $arrTeamId)
            ->whereIn('css_team.team_id', $arrEmployeeTeam)
            ->where('css_result.created_at','>=', $startDate)
            ->where('css_result.created_at','<=', $endDate)
            ->whereIn('css.project_name', $arrProjectName)
            ->orderBy($orderBy, $ariaType)
            ->orderBy('css_result.id','ASC')
            ->groupBy('css_result.id')
            ->select('css_result.*',
                'css.end_date',
                'css.project_name',
                'css.pm_name as pmName',
                'teams.name as teamName',
                'css_result.avg_point as result_point',
                'css_result.created_at as result_make');
        if ($filter) {
            self::filterDataNormal($collection, $filter);
        }
        return $collection->paginate($perPage);
    }
    
    /**
     * Get list css by list brse name, list team id, start date, end date and list project type id
     * @param string $listBrseName
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @return object
     */
    public function getCssResultByListBrse($listBrseName,$projectTypeIds,$startDate, $endDate, $teamIds){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrBrseName = explode(",", $listBrseName);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->whereIn('css.brse_name', $arrBrseName)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get list css by list brse name, list team id, start date, end date and list project type id
     * @param string $listBrseName
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @param int $employeeId
     * @return object
     */
    public function getCssResultByListBrseAndEmployee($listBrseName,$projectTypeIds,$startDate, $endDate, $teamIds,$employeeId){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrBrseName = explode(",", $listBrseName);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->where('css.employee_id','=',$employeeId)
                ->whereIn('css.brse_name', $arrBrseName)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get list css by list brse name, list team id, start date, end date and list project type id
     * @param string $listBrseName
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @param array $arrEmployeeTeam
     * @return object
     */
    public function getCssResultByListBrseAndEmployeeTeam($listBrseName,$projectTypeIds,$startDate, $endDate, $teamIds,$arrEmployeeTeam){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrBrseName = explode(",", $listBrseName);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->whereIn('css_team.team_id',$arrEmployeeTeam)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->whereIn('css.brse_name', $arrBrseName)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get list css by list brse name, list team id, start date, end date and list project type id
     * @param string $listBrseName
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @return object
     */
    public function getCssResultPaginateByListBrse($listBrseName,$projectTypeIds,$startDate, $endDate, $teamIds,$perPage,$orderBy,$ariaType){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrBrseName = explode(",", $listBrseName);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->whereIn('css.brse_name', $arrBrseName)
                ->orderBy($orderBy,$ariaType)
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName','css_result.avg_point as result_point','css_result.created_at as result_make')
                ->paginate($perPage);
    }
    
    /**
     * Get list css by list brse name, list team id, start date, end date and list project type id
     * @param string $listBrseName
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @param int $employeeId
     * @return object
     */
    public function getCssResultPaginateByListBrseAndEmployee($listBrseName,$projectTypeIds,$startDate, $endDate, $teamIds,$perPage,$orderBy,$ariaType,$employeeId){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrBrseName = explode(",", $listBrseName);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->where('css.employee_id','=',$employeeId)
                ->whereIn('css.brse_name', $arrBrseName)
                ->orderBy($orderBy,$ariaType)
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName','css_result.avg_point as result_point','css_result.created_at as result_make')
                ->paginate($perPage);
    }
    
    /**
     * Get list css by list brse name, list team id, start date, end date and list project type id
     * @param string $listBrseName
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @param array $arrEmployeeTeam
     * @return object
     */
    public function getCssResultPaginateByListBrseAndEmployeeTeam($listBrseName,$projectTypeIds,$startDate, $endDate, $teamIds,$perPage,$orderBy,$ariaType,$arrEmployeeTeam){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrBrseName = explode(",", $listBrseName);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->whereIn('css_team.team_id',$arrEmployeeTeam)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->whereIn('css.brse_name', $arrBrseName)
                ->orderBy($orderBy,$ariaType)
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName','css_result.avg_point as result_point','css_result.created_at as result_make')
                ->paginate($perPage);
    }
    
    /**
     * Get list css by list customer name, list team id, start date, end date and list project type id
     * @param string $listCustomerName
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @return object
     */
    public function getCssResultByListCustomer($listCustomerName,$projectTypeIds,$startDate, $endDate, $teamIds){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrCustomerName = explode(",", $listCustomerName);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->whereIn('css.customer_name', $arrCustomerName)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get list css by list customer name, list team id, start date, end date and list project type id
     * @param string $listCustomerName
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @param int $employeeId
     * @return object
     */
    public function getCssResultByListCustomerAndEmployee($listCustomerName,$projectTypeIds,$startDate, $endDate, $teamIds, $employeeId){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrCustomerName = explode(",", $listCustomerName);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->where('css.employee_id',$employeeId)
                ->whereIn('css.customer_name', $arrCustomerName)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get list css by list customer name, list team id, start date, end date and list project type id
     * @param string $listCustomerName
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @param array $arrEmployeeTeam
     * @return object
     */
    public function getCssResultByListCustomerAndEmployeeTeam($listCustomerName,$projectTypeIds,$startDate, $endDate, $teamIds, $arrEmployeeTeam){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrCustomerName = explode(",", $listCustomerName);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->whereIn('css_team.team_id',$arrEmployeeTeam)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->whereIn('css.customer_name', $arrCustomerName)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get list css by list sale(user_id), list team id, start date, end date and list project type id
     * @param string $listCustomerName
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @return object
     */
    public function getCssResultByListSale($saleIds,$projectTypeIds,$startDate, $endDate, $teamIds){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrSaleId = explode(",", $saleIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->whereIn('css.employee_id', $arrSaleId)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get list css by list sale(user_id), list team id, start date, end date and list project type id
     * @param string $listCustomerName
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @param int $employeeId
     * @return object
     */
    public function getCssResultByListSaleAndEmployee($saleIds,$projectTypeIds,$startDate, $endDate, $teamIds, $employeeId){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrSaleId = explode(",", $saleIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->whereIn('css.employee_id', $arrSaleId)
                ->where('css.employee_id', $employeeId)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get list css by list sale(user_id), list team id, start date, end date and list project type id
     * @param string $listCustomerName
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @param array $arrEmployeeTeam
     * @return object
     */
    public function getCssResultByListSaleAndEmployeeTeam($saleIds,$projectTypeIds,$startDate, $endDate, $teamIds, $arrEmployeeTeam){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrSaleId = explode(",", $saleIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->whereIn('css_team.team_id',$arrEmployeeTeam)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->whereIn('css.employee_id', $arrSaleId)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get list css by list sale(employee_id), list team id, start date, end date and list project type id
     * @param string $saleIds
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @param int $perPage
     * @param string $orderBy
     * @param string $ariaType
     * @param array $filter
     * @return object
     */
    public function getCssResultPaginateByListSale(
        $saleIds,
        $projectTypeIds,
        $startDate, 
        $endDate, 
        $teamIds,
        $perPage,
        $orderBy,
        $ariaType,
        $filter = null
    ){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrSaleId = explode(",", $saleIds);
        $collection = self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->whereIn('css.employee_id', $arrSaleId)
                ->orderBy($orderBy,$ariaType)
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*',
                        'css.end_date',
                        'css.project_name',
                        'css.pm_name as pmName',
                        'teams.name as teamName',
                        'css_result.avg_point as result_point',
                        'css_result.created_at as result_make');
        if ($filter) {
            self::filterDataNormal($collection, $filter);
        }
        return $collection->paginate($perPage);
    }
    
    /**
     * Get list css by list sale(employee_id), list team id, start date, end date and list project type id
     * @param string $saleIds
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @param int $employeeId
     * @param array $filter
     * @return object
     */
    public function getCssResultPaginateByListSaleAndEmployee(
        $saleIds,
        $projectTypeIds,
        $startDate, 
        $endDate, 
        $teamIds,
        $perPage,
        $orderBy,
        $ariaType,
        $employeeId,
        $filter = null
    ){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrSaleId = explode(",", $saleIds);
        $collection = self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->whereIn('css.employee_id', $arrSaleId)
                ->where('css.employee_id', $employeeId)
                ->orderBy($orderBy,$ariaType)
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*',
                        'css.end_date',
                        'css.project_name',
                        'css.pm_name as pmName',
                        'teams.name as teamName',
                        'css_result.avg_point as result_point',
                        'css_result.created_at as result_make');
        if ($filter) {
            self::filterDataNormal($collection, $filter);
        }
        return $collection->paginate($perPage);
    }
    
    /**
     * Get list css by list sale(employee_id), list team id, start date, end date and list project type id
     * @param string $saleIds
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @param array $arrEmployeeTeam
     * @param array $filter
     * @return object
     */
    public function getCssResultPaginateByListSaleAndEmployeeTeam(
        $saleIds,
        $projectTypeIds,
        $startDate, 
        $endDate, 
        $teamIds,
        $perPage,
        $orderBy,
        $ariaType,
        $arrEmployeeTeam,
        $filter = null
    ){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrSaleId = explode(",", $saleIds);
        $collection = self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->whereIn('css.employee_id', $arrSaleId)
                ->whereIn('css_team.team_id', $arrEmployeeTeam)
                ->orderBy($orderBy,$ariaType)
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*',
                        'css.end_date',
                        'css.project_name',
                        'css.pm_name as pmName',
                        'teams.name as teamName',
                        'css_result.avg_point as result_point',
                        'css_result.created_at as result_make');
        if ($filter) {
            self::filterDataNormal($collection, $filter);
        }
        return $collection->paginate($perPage);
    }
    
    /**
     * Get list css by list question, list team id, start date, end date and list project type id
     * @param string $questionIds
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @return object list
     */
    public function getCssResultByListQuestion($questionIds,$projectTypeIds,$startDate, $endDate, $teamIds){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrQuestionId = explode(",", $questionIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->join('css_result_detail', 'css_result_detail.css_result_id', '=', 'css_result.id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->where('css_result_detail.point','>',0)
                ->whereIn('css_result_detail.question_id', $arrQuestionId)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get list css by list question, list team id, start date, end date and list project type id and employee
     * @param string $questionIds
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @param int $employeeId
     * @return object list
     */
    public function getCssResultByListQuestionAndEmployee($questionIds,$projectTypeIds, $startDate, $endDate,$teamIds,$employeeId){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrQuestionId = explode(",", $questionIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->join('css_result_detail', 'css_result_detail.css_result_id', '=', 'css_result.id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->where('css_result_detail.point','>',0)
                ->where('css.employee_id',$employeeId)
                ->whereIn('css_result_detail.question_id', $arrQuestionId)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get list css by list question, list team id, start date, end date and list project type id and employee's team
     * @param string $questionIds
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @param array $arrEmployeeTeam
     * @return object list
     */
    public function getCssResultByListQuestionAndEmployeeTeam($questionIds,$projectTypeIds, $startDate, $endDate,$teamIds,$arrEmployeeTeam){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrQuestionId = explode(",", $questionIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->join('css_result_detail', 'css_result_detail.css_result_id', '=', 'css_result.id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->where('css_result_detail.point','>',0)
                ->whereIn('css_team.team_id',$arrEmployeeTeam)
                ->whereIn('css_result_detail.question_id', $arrQuestionId)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get list css by list question, list team id, start date, end date and list project type id
     * @param string $questionIds
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @return object list
     */
    public function getCssResultPaginateByListQuestion(
        $questionIds,
        $projectTypeIds,
        $startDate, 
        $endDate, 
        $teamIds,
        $perPage,
        $orderBy,
        $ariaType,
        $filter = null
    ){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrQuestionId = explode(",", $questionIds);
        $collection = self::leftJoin('css', 'css.id', '=', 'css_result.css_id')
                ->leftJoin('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->leftJoin('teams', 'teams.id', '=', 'css_team.team_id')
                ->leftJoin('css_result_detail', 'css_result_detail.css_result_id', '=', 'css_result.id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->where('css_result_detail.point','>',0)
                ->whereIn('css_result_detail.question_id', $arrQuestionId)
                ->orderBy($orderBy,$ariaType)
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*',
                        'css.end_date',
                        'css.project_name',
                        'css.pm_name as pmName',
                        'teams.name as teamName',
                        'css_result.avg_point as result_point',
                        'css_result.created_at as result_make');
        if ($filter) {
            self::filterDataNormal($collection, $filter);
        }
        return $collection->paginate($perPage);
    }
    
    /**
     * Get list css by list question, list team id, start date, end date and list project type id and employee
     * @param string $questionIds
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @param int $perPage
     * @param string $orderBy
     * @param string $ariaType
     * @param int $employeeId
     * @param array $filter
     * @return object
     */
    public function getCssResultPaginateByListQuestionAndEmployee(
        $questionIds,
        $projectTypeIds,
        $startDate, 
        $endDate, 
        $teamIds,
        $perPage,
        $orderBy,
        $ariaType,
        $employeeId,
        $filter = null
    ){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrQuestionId = explode(",", $questionIds);
        $collection = self::leftJoin('css', 'css.id', '=', 'css_result.css_id')
                ->leftJoin('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->leftJoin('teams', 'teams.id', '=', 'css_team.team_id')
                ->leftJoin('css_result_detail', 'css_result_detail.css_result_id', '=', 'css_result.id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->where('css.employee_id',$employeeId)
                ->where('css_result_detail.point','>',0)
                ->whereIn('css_result_detail.question_id', $arrQuestionId)
                ->orderBy($orderBy,$ariaType)
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*',
                        'css.end_date',
                        'css.project_name',
                        'css.pm_name as pmName',
                        'teams.name as teamName',
                        'css_result.avg_point as result_point',
                        'css_result.created_at as result_make');
        if ($filter) {
            self::filterDataNormal($collection, $filter);
        }
        return $collection->paginate($perPage);
    }
    
    /**
     * Get list css by list question, list team id, start date, end date and list project type id and employee's team
     * @param string $questionIds
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @param array $arrEmployeeTeam
     * @param array $filter
     * @return object list
     */
    public function getCssResultPaginateByListQuestionAndEmployeeTeam(
        $questionIds,
        $projectTypeIds,
        $startDate, 
        $endDate, 
        $teamIds,
        $perPage,
        $orderBy,
        $ariaType,
        $arrEmployeeTeam,
        $filter = null
    ){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrQuestionId = explode(",", $questionIds);
        $collection = self::leftJoin('css', 'css.id', '=', 'css_result.css_id')
                ->leftJoin('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->leftJoin('teams', 'teams.id', '=', 'css_team.team_id')
                ->leftJoin('css_result_detail', 'css_result_detail.css_result_id', '=', 'css_result.id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->whereIn('css_team.team_id',$arrEmployeeTeam)
                ->where('css_result_detail.point','>',0)
                ->whereIn('css_result_detail.question_id', $arrQuestionId)
                ->orderBy($orderBy,$ariaType)
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*',
                        'css.end_date',
                        'css.project_name',
                        'css.pm_name as pmName',
                        'teams.name as teamName',
                        'css_result.avg_point as result_point',
                        'css_result.created_at as result_make');
        if ($filter) {
            self::filterDataNormal($collection, $filter);
        }
        return $collection->paginate($perPage);
    }
    
    
    /**
     * Get list css by list customer name, list team id, start date, end date and list project type id
     * @param string $listCustomerName
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @param int $perPage
     * @param string orderBy
     * @param string ariaType
     * @param array $filter
     * @return object
     */
    public function getCssResultPaginateByListCustomer(
        $listCustomerName,
        $projectTypeIds,
        $startDate, 
        $endDate, 
        $teamIds,
        $perPage,
        $orderBy,
        $ariaType,
        $filter = null
    ){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrCustomerName = explode(",", $listCustomerName);
        $collection = self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->whereIn('css.customer_name', $arrCustomerName)
                ->orderBy($orderBy,$ariaType)
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*',
                        'css.end_date',
                        'css.project_name',
                        'css.pm_name as pmName',
                        'teams.name as teamName',
                        'css_result.avg_point as result_point',
                        'css_result.created_at as result_make');
        if ($filter) {
            self::filterDataNormal($collection, $filter);
        }
        return $collection->paginate($perPage);
    }
    
    /**
     * Get list css by list customer name, list team id, start date, end date and list project type id
     * @param string $listCustomerName
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @param int $perPage
     * @param string orderBy
     * @param string ariaType
     * @param int $employeeId
     * @param array $filter
     * @return object
     */
    public function getCssResultPaginateByListCustomerAndEmployee(
        $listCustomerName,
        $projectTypeIds,
        $startDate, 
        $endDate, 
        $teamIds,
        $perPage,
        $orderBy,
        $ariaType,
        $employeeId,
        $filter = null
    ){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrCustomerName = explode(",", $listCustomerName);
        $collection = self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->where('css.employee_id','=',$employeeId)
                ->whereIn('css.customer_name', $arrCustomerName)
                ->orderBy($orderBy,$ariaType)
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*',
                        'css.end_date',
                        'css.project_name',
                        'css.pm_name as pmName',
                        'teams.name as teamName',
                        'css_result.avg_point as result_point',
                        'css_result.created_at as result_make');
        if ($filter) {
            self::filterDataNormal($collection, $filter);
        }
        return $collection->paginate($perPage);
    }
    
    /**
     * Get list css by list customer name, list team id, start date, end date and list project type id
     * @param string $listCustomerName
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $teamIds
     * @param int $perPage
     * @param string orderBy
     * @param string ariaType
     * @param array $arrEmployeeTeam
     * @param array $filter
     * @return object
     */
    public function getCssResultPaginateByListCustomerAndEmployeeTeam(
        $listCustomerName,
        $projectTypeIds,
        $startDate, 
        $endDate, 
        $teamIds,
        $perPage,
        $orderBy,
        $ariaType,
        $arrEmployeeTeam,
        $filter = null
    ){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $arrCustomerName = explode(",", $listCustomerName);
        $collection = self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->whereIn('css_team.team_id',$arrEmployeeTeam)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->whereIn('css.customer_name', $arrCustomerName)
                ->orderBy($orderBy,$ariaType)
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*',
                        'css.end_date',
                        'css.project_name',
                        'css.pm_name as pmName',
                        'teams.name as teamName',
                        'css_result.avg_point as result_point',
                        'css_result.created_at as result_make');
        if ($filter) {
            self::filterDataNormal($collection, $filter);
        }
        return $collection->paginate($perPage);
    }
    
    /**
     * Get CSS result by question, team and date
     * @param int $questionId
     * @param date $startDate
     * @param date $endDate
     * @param string $teamIds
     * @return type
     */
    public function getCssResultByQuestion($questionId,$startDate,$endDate,$teamIds){
        $arrTeamId = explode(",", $teamIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->join('css_result_detail', 'css_result_detail.css_result_id', '=', 'css_result.id')
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->where('css_result_detail.question_id', $questionId)
                ->orderBy('css.end_date','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get CSS result by question, date, team and employee
     * @param int $questionId
     * @param date $startDate
     * @param date $endDate
     * @param string $teamIds
     * @param int $employeeId
     * @return type
     */
    public function getCssResultByQuestionAndEmployee($questionId,$startDate,$endDate,$teamIds,$employeeId){
        $arrTeamId = explode(",", $teamIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->join('css_result_detail', 'css_result_detail.css_result_id', '=', 'css_result.id')
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->where('css.employee_id',$employeeId)
                ->where('css_result_detail.question_id', $questionId)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get CSS result by question, date, team and employee
     * @param int $questionId
     * @param date $startDate
     * @param date $endDate
     * @param string $teamIds
     * @param array $arrEmployeeTeam
     * @return type
     */
    public function getCssResultByQuestionAndEmployeeTeam($questionId,$startDate,$endDate,$teamIds,$arrEmployeeTeam){
        $arrTeamId = explode(",", $teamIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->join('css_result_detail', 'css_result_detail.css_result_id', '=', 'css_result.id')
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->whereIn('css_team.team_id',$arrEmployeeTeam)
                ->where('css_result_detail.question_id', $questionId)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get css result by employee_id
     * @param int $employee_id
     * @param string $teamIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $projectTypeIds
     * @return object
     */
    public function getCssResultBySale($employee_id,$teamIds,$startDate, $endDate, $projectTypeIds){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->where('css.employee_id', $employee_id)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get css result by employee_id
     * @param int $saleId
     * @param string $teamIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $projectTypeIds
     * @param int $employeeId
     * @return object
     */
    public function getCssResultBySaleAndEmployee($saleId,$teamIds,$startDate, $endDate, $projectTypeIds, $employeeId){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->where('css.employee_id', $saleId)
                ->where('css.employee_id', $employeeId)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get css result by employee_id
     * @param int $saleId
     * @param string $teamIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $projectTypeIds
     * @param array $arrEmployeeTeam
     * @return object
     */
    public function getCssResultBySaleAndEmployeeTeam($saleId,$teamIds,$startDate, $endDate, $projectTypeIds, $arrEmployeeTeam){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->whereIn('css_team.team_id',$arrEmployeeTeam)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->where('css.employee_id', $saleId)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get css result by question
     * @param string $questionId
     * @param string $teamIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $projectTypeIds
     * @return object
     */
    public static function getCssResultByQuestionToChart($questionId,$teamIds,$startDate, $endDate, $projectTypeIds){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->join('css_result_detail', 'css_result_detail.css_result_id', '=', 'css_result.id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->where('css_result_detail.question_id', $questionId)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get css result by question
     * @param string $questionId
     * @param string $teamIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $projectTypeIds
     * @param array $arrEmployeeTeam
     * @return object
     */
    public static function getCssResultByQuestionToChartAndEmployeeTeam($questionId,$teamIds,$startDate, $endDate, $projectTypeIds, $arrEmployeeTeam){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->join('css_result_detail', 'css_result_detail.css_result_id', '=', 'css_result.id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->whereIn('css_team.team_id',$arrEmployeeTeam)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->where('css_result_detail.question_id', $questionId)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * Get css result by question
     * @param string $questionId
     * @param string $teamIds
     * @param date $startDate
     * @param date $endDate 
     * @param string $projectTypeIds
     * @param int $employeeId
     * @return object
     */
    public static function getCssResultByQuestionToChartAndEmployee($questionId,$teamIds,$startDate, $endDate, $projectTypeIds, $employeeId){
        $arrTeamId = explode(",", $teamIds);
        $arrProjectTypeId = explode(",", $projectTypeIds);
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                ->join('css_team', 'css_result.css_id', '=', 'css_team.css_id')
                ->join('teams', 'teams.id', '=', 'css_team.team_id')
                ->join('css_result_detail', 'css_result_detail.css_result_id', '=', 'css_result.id')
                ->whereIn('css.project_type_id',$arrProjectTypeId)
                ->whereIn('css_team.team_id',$arrTeamId)
                ->where('css_result.created_at','>=',$startDate)
                ->where('css_result.created_at','<=',$endDate)
                ->where('css.employee_id', $employeeId)
                ->where('css_result_detail.question_id', $questionId)
                ->orderBy('css_result.created_at','ASC')
                ->orderBy('css_result.id','ASC')
                ->groupBy('css_result.id')
                ->select('css_result.*','css.end_date','css.project_name','css.pm_name as pmName','teams.name as teamName')
                ->get();
    }
    
    /**
     * get avg css point from project id
     *  if same name, get last css
     * @param int $projectId
     * @return float
     */
    public static function getCssFromProjId($projectId)
    {
        $cssResultTbl = self::getTableName();
        $cssTbl = Css::getTableName();
        $listResults = static::cssResultsOfProject($projectId);

        if (empty($listResults)) {
            return null;
        }
        $total = 0;
        $count = 0;
        foreach ($listResults as $item) {
            $total += $item->avg_point;
            $count++;
        }
        return empty($count) ? null : round($total / $count, 2);
    }
    
    /**
     * check has css in a duration
     * 
     * @param int $projectId
     * @param string $fromDate
     * @param string $toDate
     * @return boolean
     */
    public static function hasCssFromProjIdDurationDate($projectId, $fromDate, $toDate)
    {
        if ($item = CacheHelper::get(self::KEY_CACHE, $projectId)) {
            return self::flagToBoolean($item);
        }
        $tableCssResult = self::getTableName();
        $tableCss = Css::getTableName();
        
        $result = self::select("{$tableCssResult}.id")
            ->join($tableCss, "{$tableCss}.id", '=', "{$tableCssResult}.css_id")
            ->where("{$tableCss}.projs_id", (int) $projectId)
            ->whereDate("{$tableCssResult}.created_at", '>=', $fromDate)
            ->whereDate("{$tableCssResult}.created_at", '<=', $toDate)
            ->first();
        if (!$result) {
            $item = false;
        } else {
            $item = true;
        }
        CacheHelper::put(self::KEY_CACHE, self::booleanToFlag($item), $projectId);
        return $item;
    }
    
    public static function getCode($cssId) {
        return self::where('css_id', $cssId)
                    ->groupBy('code')
                    ->select('code')
                    ->get();
    }
    
    /**
     * Get list code of css result by id
     * @param int $cssId
     */
    public static function getCodeResult($cssId) {
        $results = self::where('css_id', $cssId)->select('code')->get();
        $codes = [];
        if (count($results)) {
            foreach ($results as $item) {
                $codes[] = $item->code;
            }
        }
        return $codes;
    }
    
    /**
     * Get list result 
     * 
     * @param array $conditions
     * @param array $conditionsRaw
     * @param string $order
     * @param string $dir
     * @return CssResult collection
     */
    public static function getList($conditions = null, $conditionsRaw = null, $order = null, $dir = null)
    {
        $textNotYet = Lang::get('sales::view.Not analyzed yet');
        $textAnalyzed = Lang::get('sales::view.Analyzed');
        $resultStatusNew = CssResult::STATUS_NEW;

        $result = self::select([
            '*', 
            DB::raw("(CASE
                        WHEN css_result.status = {$resultStatusNew}  THEN '{$textNotYet}'
                        ELSE '{$textAnalyzed}'
                    END) AS analyze_status"
            )]);
        if ($conditions) {
            foreach ($conditions as $field => $value) {
                $result->where($field, $value);
            }
        }
        if ($conditionsRaw) {
            foreach ($conditionsRaw as $value) {
                $result->whereRaw($value);
            }
        }
        if ($order) {
            if (!$dir) {
                $dir = 'asc';
            } 
            $result->orderBy($order, $dir);
        }
        return $result->get();
    }

    public function getCssResulByCssForDetail($cssId, $order, $dir) {
        return self::join('css', 'css.id', '=', 'css_result.css_id')
            ->join('employees', 'employees.id', '=', 'css.employee_id')
            ->leftJoin('css_mail','css_mail.code','=','css_result.code')
            ->where("css_result.css_id",$cssId)
            ->orderBy($order, $dir)
            ->groupBy('css_result.id')
            ->select('css_result.*','employees.name as sale_name','css_mail.mail_to as email_cus');
    }

    /**
     * Get all CSS result by project
     *
     * @param int $projId
     *
     * @return CssResult collection
     */
    public static function cssResultsOfProject($projId)
    {
        $cssTbl = Css::getTableName();
        $cssResultTbl = CssResult::getTableName();
        return Css::join("{$cssResultTbl}", "{$cssTbl}.id", "=", "{$cssResultTbl}.css_id")
                ->where("{$cssTbl}.projs_id", $projId)
                ->where("{$cssResultTbl}.status", "!=", self::STATUS_CANCEL)
        ->select('avg_point', 'name', "{$cssResultTbl}.created_at", "{$cssResultTbl}.id")
        ->get();
    }

    /**
     * get label status css result.
     */
    public static function getLabelStatusCssResult()
    {
        return [
            self::STATUS_NEW => 'New',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_FEEDBACK => 'Feedback',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REVIEW => 'Reviewed',
            self::STATUS_CANCEL => 'Cancel',
        ];
    }

    public static function getFilterStatusDefault()
    {
        return [
            self::STATUS_NEW,
            self::STATUS_SUBMITTED,
            self::STATUS_FEEDBACK,
            self::STATUS_APPROVED,
        ];
    }

    /*
     * get list cssResultId follow status feedback or new.
     *
     * @param array $cssId
     * @return collection
     */
    public static function getCssResultFeedback($cssId)
    {
        $statusCondition = [self::STATUS_NEW, self::STATUS_FEEDBACK];
        return self::join('css', 'css.id', '=', 'css_result.css_id')
                    ->whereIn('css.id', $cssId)
                    ->whereIn('css_result.status', $statusCondition)
                    ->groupBy('css_result.id')
                    ->select('css_result.id')
                    ->get();
    }
}
