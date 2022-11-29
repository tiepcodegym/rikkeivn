<?php
namespace Rikkei\Sales\View;

use Auth;
use Rikkei\Sales\Model\Css;
use Rikkei\Sales\Model\CssResult;
use Rikkei\Sales\Model\CssTeams;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Permission;

/**
 * class permission
 * 
 * check permssion auth
 */
class CssPermission
{
    /**
     * Get compare chart by Sale
     * @param int $saleId
     * @param int $teamId
     * @param date $startDate
     * @param date $endDate
     * @param string $projectTypeIds
     * @return CssResult list
     */
    public static function getCompareChartByQuestion($questionId,$teamIds,$startDate, $endDate, $projectTypeIds){
        $userAccount = Permission::getInstance()->getEmployee(); 
        $permission = new Permission();
        $model = new CssResult();
        
        if($permission->isScopeSelf()){
            $cssResult = $model->getCssResultByQuestionToChartAndEmployee($questionId,$teamIds,$startDate, $endDate, $projectTypeIds, $userAccount->id);
        }elseif ($permission->isScopeTeam()) {
            $arrEmployeeTeam = self::getArrTeamIdByEmployee($userAccount->id);
            $cssResult = $model->getCssResultByQuestionToChartAndEmployeeTeam($questionId,$teamIds,$startDate, $endDate, $projectTypeIds, $arrEmployeeTeam);
        }elseif ($permission->isScopeCompany()){
            $cssResult = $model->getCssResultByQuestionToChart($questionId,$teamIds,$startDate, $endDate, $projectTypeIds);
        }
        
        return $cssResult;
    }
    
    /**
     * Get compare chart by Sale
     * @param int $saleId
     * @param int $teamId
     * @param date $startDate
     * @param date $endDate
     * @param string $projectTypeIds
     * @return CssResult list
     */
    public static function getCompareChartBySale($saleId,$teamIds,$startDate, $endDate, $projectTypeIds){
        $userAccount = Permission::getInstance()->getEmployee(); 
        $permission = new Permission();
        $model = new CssResult();
        
        if($permission->isScopeSelf()){
            $cssResult = $model->getCssResultBySaleAndEmployee($saleId,$teamIds,$startDate, $endDate, $projectTypeIds, $userAccount->id);
        }elseif ($permission->isScopeTeam()) {
            $arrEmployeeTeam = self::getArrTeamIdByEmployee($userAccount->id);
            $cssResult = $model->getCssResultBySaleAndEmployeeTeam($saleId,$teamIds,$startDate, $endDate, $projectTypeIds, $arrEmployeeTeam);
        }elseif ($permission->isScopeCompany()){
            $cssResult = $model->getCssResultBySale($saleId,$teamIds,$startDate, $endDate, $projectTypeIds);
        }
        
        return $cssResult;
    }
    
    /**
     * Get compare chart by Customer
     * @param string $customerName
     * @param int $teamId
     * @param date $startDate
     * @param date $endDate
     * @param string $projectTypeIds
     * @return CssResult list
     */
    public static function getCompareChartByCustomer($customerName,$teamIds,$startDate, $endDate, $projectTypeIds){
        $userAccount = Permission::getInstance()->getEmployee(); 
        $permission = new Permission();
        $model = new CssResult();
        
        if($permission->isScopeSelf()){
            $cssResult = $model->getCssResultByCustomerNameAndEmployee($customerName,$teamIds,$startDate, $endDate, $projectTypeIds, $userAccount->id);
        }elseif ($permission->isScopeTeam()) {
            $arrEmployeeTeam = self::getArrTeamIdByEmployee($userAccount->id);
            $cssResult = $model->getCssResultByCustomerNameAndEmployeeTeam($customerName,$teamIds,$startDate, $endDate, $projectTypeIds, $arrEmployeeTeam);
        }elseif ($permission->isScopeCompany()){
            $cssResult = $model->getCssResultByCustomerName($customerName,$teamIds,$startDate, $endDate, $projectTypeIds);
        }
        
        return $cssResult;
    }

    public static function getCompareChartByProjectName($projectName,$teamIds,$startDate, $endDate, $projectTypeIds){
        $userAccount = Permission::getInstance()->getEmployee();
        $permission = new Permission();
        $model = new CssResult();

        if($permission->isScopeSelf()){
            $cssResult = $model->getCssResultByProjectNameAndEmployee($projectName,$teamIds,$startDate, $endDate, $projectTypeIds, $userAccount->id);
        }elseif ($permission->isScopeTeam()) {
            $arrEmployeeTeam = self::getArrTeamIdByEmployee($userAccount->id);
            $cssResult = $model->getCssResultByProjectNameAndEmployeeTeam($projectName,$teamIds,$startDate, $endDate, $projectTypeIds, $arrEmployeeTeam);
        }elseif ($permission->isScopeCompany()){
            $cssResult = $model->getCssResultByProjectName($projectName,$teamIds,$startDate, $endDate, $projectTypeIds);
        }

        return $cssResult;
    }
    
    /**
     * Get compare chart by Brse
     * @param string $brseName
     * @param int $teamId
     * @param date $startDate
     * @param date $endDate
     * @param string $projectTypeIds
     * @return CssResult list
     */
    public static function getCompareChartByBrse($brseName,$teamIds,$startDate, $endDate, $projectTypeIds){
        $userAccount = Permission::getInstance()->getEmployee(); 
        $permission = new Permission();
        $model = new CssResult();
        
        if($permission->isScopeSelf()){
            $cssResult = $model->getCssResultByBrseNameAndEmployee($brseName,$teamIds,$startDate, $endDate, $projectTypeIds, $userAccount->id);
        }elseif ($permission->isScopeTeam()) {
            $arrEmployeeTeam = self::getArrTeamIdByEmployee($userAccount->id);
            $cssResult = $model->getCssResultByBrseNameAndEmployeeTeam($brseName,$teamIds,$startDate, $endDate, $projectTypeIds, $arrEmployeeTeam);
        }elseif ($permission->isScopeCompany()){
            $cssResult = $model->getCssResultByBrseName($brseName,$teamIds,$startDate, $endDate, $projectTypeIds);
        }
        
        return $cssResult;
    }
    
    /**
     * Get compare chart by Pm
     * @param string $pmName
     * @param int $teamId
     * @param date $startDate
     * @param date $endDate
     * @param string $projectTypeIds
     * @return CssResult list
     */
    public static function getCompareChartByPm($pmName,$teamIds,$startDate, $endDate, $projectTypeIds){
        $userAccount = Permission::getInstance()->getEmployee(); 
        $permission = new Permission();
        $model = new CssResult();
        
        if($permission->isScopeSelf()){
            $cssResult = $model->getCssResultByPmNameAndEmployee($pmName,$teamIds,$startDate, $endDate, $projectTypeIds, $userAccount->id);
        }elseif ($permission->isScopeTeam()) {
            $arrEmployeeTeam = self::getArrTeamIdByEmployee($userAccount->id);
            $cssResult = $model->getCssResultByPmNameAndEmployeeTeam($pmName,$teamIds,$startDate, $endDate, $projectTypeIds, $arrEmployeeTeam);
        }elseif ($permission->isScopeCompany()){
            $cssResult = $model->getCssResultByPmName($pmName,$teamIds,$startDate, $endDate, $projectTypeIds);
        }
        
        return $cssResult;
    }
    
    
    /**
     * Get compare chart by team
     * @param int $teamId
     * @param date $startDate
     * @param date $endDate
     * @param string $projectTypeIds
     * @return CssResult list
     */
    public static function getCompareChartByTeam($teamId,$startDate, $endDate, $projectTypeIds){
        $userAccount = Permission::getInstance()->getEmployee(); 
        $permission = new Permission();
        $model = new CssResult();
        
        if($permission->isScopeSelf()){
            $cssResult = $model->getCssResultByTeamIdAndEmployee($teamId,$startDate, $endDate, $projectTypeIds, $userAccount->id);
        }elseif ($permission->isScopeTeam()) {
            $arrEmployeeTeam = self::getArrTeamIdByEmployee($userAccount->id);
            $cssResult = $model->getCssResultByTeamIdAndEmployeeTeam($teamId,$startDate, $endDate, $projectTypeIds, $arrEmployeeTeam);
        }elseif ($permission->isScopeCompany()){
            $cssResult = $model->getCssResultByTeamId($teamId,$startDate, $endDate, $projectTypeIds);
        }
        
        return $cssResult;
    }
    
    /**
     * Get compare chart by project type 
     * @param int $projectTypeId
     * @param date $startDate
     * @param date $endDate
     * @param string $teamIds
     * @return CssResult list
     */
    public static function getCompareChartByProjectType($projectTypeId,$startDate, $endDate, $teamIds){
        $userAccount = Permission::getInstance()->getEmployee(); 
        $permission = new Permission();
        $model = new CssResult();
        
        if($permission->isScopeSelf()){
            $cssResult = $model->getCssResultByProjectTypeIdAndEmployee($projectTypeId,$startDate, $endDate, $teamIds, $userAccount->id);
        }elseif ($permission->isScopeTeam()) {
            $arrEmployeeTeam = self::getArrTeamIdByEmployee($userAccount->id);
            $cssResult = $model->getCssResultByProjectTypeIdAndEmployeeTeam($projectTypeId,$startDate, $endDate, $teamIds, $arrEmployeeTeam);
        }elseif ($permission->isScopeCompany()){
            $cssResult = $model->getCssResultByProjectTypeId($projectTypeId,$startDate, $endDate, $teamIds);
        }
        
        return $cssResult;
    }
    
    /**
     * Get analyze by project type list
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate
     * @param string $teamIds
     * @return CssResult list
     */
    public static function getAnalyzeByProjectType($projectTypeIds,$startDate, $endDate, $teamIds){
        $userAccount = Permission::getInstance()->getEmployee(); 
        $permission = new Permission();
        $model = new CssResult();
        
        if($permission->isScopeSelf()){
            $cssResult = $model->getCssResultByProjectTypeIdsAndEmployee($projectTypeIds,$startDate, $endDate, $teamIds, $userAccount->id);
        }elseif ($permission->isScopeTeam()) {
            $arrEmployeeTeam = self::getArrTeamIdByEmployee($userAccount->id);
            $cssResult = $model->getCssResultByProjectTypeIdsAndEmployeeTeam($projectTypeIds,$startDate, $endDate, $teamIds, $arrEmployeeTeam);
        }elseif ($permission->isScopeCompany()){
            $cssResult = $model->getCssResultByProjectTypeIds($projectTypeIds,$startDate, $endDate, $teamIds);
        }
        
        return $cssResult;
    }
    
    /**
     * Get analyze by project type list
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate
     * @param string $teamIds
     * @param int $perPage
     * @param string $orderBy
     * @param string $ariaType
     * @param array $filter
     * @return CssResult list
     */
    public static function getAnalyzePaginateByProjectType(
        $projectTypeIds,
        $startDate, 
        $endDate, 
        $teamIds,
        $perPage,
        $orderBy,
        $ariaType,
        $filter = null
    ){
        $userAccount = Permission::getInstance()->getEmployee(); 
        $permission = Permission::getInstance();
        $model = new CssResult();
        
        if ($permission->isScopeCompany()){
            $cssResult = $model->getCssResultPaginateByProjectTypeIds(
                $projectTypeIds,
                $startDate, 
                $endDate, 
                $teamIds,
                $perPage,
                $orderBy,
                $ariaType,
                $filter
            );
        } elseif ($permission->isScopeTeam()) {
            $arrEmployeeTeam = self::getArrTeamIdByEmployee($userAccount->id);
            $cssResult = $model->getCssResultPaginateByProjectTypeIdsAndEmployeeTeam(
                $projectTypeIds,
                $startDate, 
                $endDate, 
                $teamIds,
                $perPage,
                $orderBy,
                $ariaType, 
                $arrEmployeeTeam,
                $filter
            );
        } elseif($permission->isScopeSelf()){
            $cssResult = $model->getCssResultPaginateByProjectTypeIdsAndEmployee(
                $projectTypeIds,
                $startDate, 
                $endDate, 
                $teamIds,
                $perPage,
                $orderBy,
                $ariaType, 
                $userAccount->id,
                $filter
            );
        }
        return $cssResult;
    }

    public static function getAnalyzePaginateByProjectName(
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
        $userAccount = Permission::getInstance()->getEmployee();
        $permission = Permission::getInstance();
        $model = new CssResult();

        if ($permission->isScopeCompany()){
            $cssResult = $model->getCssResultPaginateByListProjectName(
                $listProjectName,
                $projectTypeIds,
                $startDate,
                $endDate,
                $teamIds,
                $perPage,
                $orderBy,
                $ariaType,
                $filter
            );
        } elseif ($permission->isScopeTeam()) {
            $arrEmployeeTeam = self::getArrTeamIdByEmployee($userAccount->id);
            $cssResult = $model->getCssResultPaginateByListProjectNameAndEmployeeTeam(
                $listProjectName,
                $projectTypeIds,
                $startDate,
                $endDate,
                $teamIds,
                $perPage,
                $orderBy,
                $ariaType,
                $arrEmployeeTeam,
                $filter
            );
        } elseif($permission->isScopeSelf()){
            $cssResult = $model->getCssResultPaginateByListProjectNameAndEmployee(
                $listProjectName,
                $projectTypeIds,
                $startDate,
                $endDate,
                $teamIds,
                $perPage,
                $orderBy,
                $ariaType,
                $userAccount->id,
                $filter
            );
        }
        return $cssResult;
    }
    
    /**
     * Get analyze by PM list
     * @param string $listPmName
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate
     * @param string $teamIds
     * @param int $perPage
     * @param string $orderBy
     * @param string $ariaType
     * @param array $filter
     * @return CssResult list
     */
    public static function getAnalyzePaginateByPm(
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
        $userAccount = Permission::getInstance()->getEmployee(); 
        $permission = Permission::getInstance();
        $model = new CssResult();
        
        if ($permission->isScopeCompany()){
            $cssResult = $model->getCssResultPaginateByListPm(
                $listPmName,
                $projectTypeIds,
                $startDate, 
                $endDate, 
                $teamIds,
                $perPage,
                $orderBy,
                $ariaType,
                $filter
            );
        } elseif ($permission->isScopeTeam()) {
            $arrEmployeeTeam = self::getArrTeamIdByEmployee($userAccount->id);
            $cssResult = $model->getCssResultPaginateByListPmAndEmployeeTeam(
                $listPmName,
                $projectTypeIds,
                $startDate, 
                $endDate, 
                $teamIds,
                $perPage,
                $orderBy,
                $ariaType, 
                $arrEmployeeTeam,
                $filter
            );
        } elseif($permission->isScopeSelf()){
            $cssResult = $model->getCssResultPaginateByListPmAndEmployee(
                $listPmName,
                $projectTypeIds,
                $startDate, 
                $endDate, 
                $teamIds,
                $perPage,
                $orderBy,
                $ariaType, 
                $userAccount->id,
                $filter
            );
        }
        return $cssResult;
    }
    
    /**
     * Get analyze by pm list
     * @param string $listPmName
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate
     * @param string $teamIds
     * @return CssResult list
     */
    public static function getAnalyzeByPm($listPmName,$projectTypeIds,$startDate, $endDate, $teamIds){
        $userAccount = Permission::getInstance()->getEmployee(); 
        $permission = new Permission();
        $model = new CssResult();
        
        if($permission->isScopeSelf()){
            $cssResult = $model->getCssResultByListPmAndEmployee($listPmName,$projectTypeIds,$startDate, $endDate, $teamIds, $userAccount->id);
        }elseif ($permission->isScopeTeam()) {
            $arrEmployeeTeam = self::getArrTeamIdByEmployee($userAccount->id);
            $cssResult = $model->getCssResultByListPmAndEmployeeTeam($listPmName,$projectTypeIds,$startDate, $endDate, $teamIds, $arrEmployeeTeam);
        }elseif ($permission->isScopeCompany()){
            $cssResult = $model->getCssResultByListPm($listPmName,$projectTypeIds,$startDate, $endDate, $teamIds);
        }
        
        return $cssResult;
    }

    public static function getAnalyzeByProjectName($listProjectName, $projectTypeIds, $startDate, $endDate, $teamIds){
        $userAccount = Permission::getInstance()->getEmployee();
        $permission = new Permission();
        $model = new CssResult();

        if($permission->isScopeSelf()){
            $cssResult = $model->getCssResultByListProjectNameAndEmployee($listProjectName, $projectTypeIds, $startDate, $endDate, $teamIds, $userAccount->id);
        }elseif ($permission->isScopeTeam()) {
            $arrEmployeeTeam = self::getArrTeamIdByEmployee($userAccount->id);
            $cssResult = $model->getCssResultByListProjectNameAndEmployeeTeam($listProjectName, $projectTypeIds, $startDate, $endDate, $teamIds, $arrEmployeeTeam);
        }elseif ($permission->isScopeCompany()){
            $cssResult = $model->getCssResultByListProjectName($listProjectName, $projectTypeIds, $startDate, $endDate, $teamIds);
        }

        return $cssResult;
    }
    
    /**
     * Get analyze by brse list
     * @param string $listCustomerName
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate
     * @param string $teamIds
     * @return CssResult list
     */
    public static function getAnalyzeByBrse($listBrseName,$projectTypeIds,$startDate, $endDate, $teamIds){
        $userAccount = Permission::getInstance()->getEmployee(); 
        $permission = new Permission();
        $model = new CssResult();
        
        if($permission->isScopeSelf()){
            $cssResult = $model->getCssResultByListBrseAndEmployee($listBrseName,$projectTypeIds,$startDate, $endDate, $teamIds, $userAccount->id);
        }elseif ($permission->isScopeTeam()) {
            $arrEmployeeTeam = self::getArrTeamIdByEmployee($userAccount->id);
            $cssResult = $model->getCssResultByListBrseAndEmployeeTeam($listBrseName,$projectTypeIds,$startDate, $endDate, $teamIds, $arrEmployeeTeam);
        }elseif ($permission->isScopeCompany()){
            $cssResult = $model->getCssResultByListBrse($listBrseName,$projectTypeIds,$startDate, $endDate, $teamIds);
        }
        
        return $cssResult;
    }
    
    /**
     * Get analyze by brse list
     * @param string $saleIds
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate
     * @param string $teamIds
     * @param int $perPage
     * @param string $orderBy
     * @param string $ariaType
     * @return CssResult list
     */
    public static function getAnalyzePaginateByBrse($listBrseName,$projectTypeIds,$startDate, $endDate, $teamIds,$perPage,$orderBy,$ariaType){
        $userAccount = Permission::getInstance()->getEmployee(); 
        $permission = new Permission();
        $model = new CssResult();
        
        if($permission->isScopeSelf()){
            $cssResult = $model->getCssResultPaginateByListBrseAndEmployee($listBrseName,$projectTypeIds,$startDate, $endDate, $teamIds,$perPage,$orderBy,$ariaType, $userAccount->id);
        }elseif ($permission->isScopeTeam()) {
            $arrEmployeeTeam = self::getArrTeamIdByEmployee($userAccount->id);
            $cssResult = $model->getCssResultPaginateByListBrseAndEmployeeTeam($listBrseName,$projectTypeIds,$startDate, $endDate, $teamIds,$perPage,$orderBy,$ariaType, $arrEmployeeTeam);
        }elseif ($permission->isScopeCompany()){
            $cssResult = $model->getCssResultPaginateByListBrse($listBrseName,$projectTypeIds,$startDate, $endDate, $teamIds,$perPage,$orderBy,$ariaType);
        }
        
        return $cssResult;
    }
    
    /**
     * Get analyze by customer list
     * @param string $listCustomerName
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate
     * @param string $teamIds
     * @param int $perPage
     * @param string $orderBy
     * @param string $ariaType
     * @param array $filter
     * @return CssResult list
     */
    public static function getAnalyzePaginateByCustomer(
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
        $userAccount = Permission::getInstance()->getEmployee(); 
        $permission = Permission::getInstance();
        $model = new CssResult();
        
        if ($permission->isScopeCompany()){
            $cssResult = $model->getCssResultPaginateByListCustomer(
                $listCustomerName,
                $projectTypeIds,
                $startDate, 
                $endDate, 
                $teamIds,
                $perPage,
                $orderBy,
                $ariaType,
                $filter
            );
        } elseif ($permission->isScopeTeam()) {
            $arrEmployeeTeam = self::getArrTeamIdByEmployee($userAccount->id);
            $cssResult = $model->getCssResultPaginateByListCustomerAndEmployeeTeam(
                $listCustomerName,
                $projectTypeIds,
                $startDate, 
                $endDate, 
                $teamIds,
                $perPage,
                $orderBy,
                $ariaType, 
                $arrEmployeeTeam,
                $filter
            );
        } elseif($permission->isScopeSelf()){
            $cssResult = $model->getCssResultPaginateByListCustomerAndEmployee(
                $listCustomerName,
                $projectTypeIds,
                $startDate, 
                $endDate, 
                $teamIds,
                $perPage,
                $orderBy,
                $ariaType, 
                $userAccount->id,
                $filter
            );
        }
        return $cssResult;
    }
    
    /**
     * Get analyze by sale list
     * @param string $saleIds
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate
     * @param string $teamIds
     * @return type
     */
    public static function getAnalyzeByCustomer($listCustomerName,$projectTypeIds,$startDate, $endDate, $teamIds){
        $userAccount = Permission::getInstance()->getEmployee(); 
        $permission = new Permission();
        $model = new CssResult();
        
        if($permission->isScopeSelf()){
            $cssResult = $model->getCssResultByListCustomerAndEmployee($listCustomerName,$projectTypeIds,$startDate, $endDate, $teamIds, $userAccount->id);
        }elseif ($permission->isScopeTeam()) {
            $arrEmployeeTeam = self::getArrTeamIdByEmployee($userAccount->id);
            $cssResult = $model->getCssResultByListCustomerAndEmployeeTeam($listCustomerName,$projectTypeIds,$startDate, $endDate, $teamIds, $arrEmployeeTeam);
        }elseif ($permission->isScopeCompany()){
            $cssResult = $model->getCssResultByListCustomer($listCustomerName,$projectTypeIds,$startDate, $endDate, $teamIds);
        }
        
        return $cssResult;
    }
    
    /**
     * Get analyze by sale list
     * @param string $saleIds
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate
     * @param string $teamIds
     * @return type
     */
    public static function getAnalyzeBySale($saleIds,$projectTypeIds,$startDate, $endDate, $teamIds){
        $userAccount = Permission::getInstance()->getEmployee(); 
        $permission = new Permission();
        $model = new CssResult();
        
        if($permission->isScopeSelf()){
            $cssResult = $model->getCssResultByListSaleAndEmployee($saleIds,$projectTypeIds,$startDate, $endDate, $teamIds, $userAccount->id);
        }elseif ($permission->isScopeTeam()) {
            $arrEmployeeTeam = self::getArrTeamIdByEmployee($userAccount->id);
            $cssResult = $model->getCssResultByListSaleAndEmployeeTeam($saleIds,$projectTypeIds,$startDate, $endDate, $teamIds, $arrEmployeeTeam);
        }elseif ($permission->isScopeCompany()){
            $cssResult = $model->getCssResultByListSale($saleIds,$projectTypeIds,$startDate, $endDate, $teamIds);
        }
        
        return $cssResult;
    }
    
    /**
     * Get analyze by sale list
     * @param string $saleIds
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate
     * @param string $teamIds
     * @param int $perPage
     * @param string $orderBy
     * @param string $ariaType
     * @param array $filter
     * @return type
     */
    public static function getAnalyzePaginateBySale(
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
        $userAccount = Permission::getInstance()->getEmployee(); 
        $permission = Permission::getInstance();
        $model = new CssResult();
        
        if ($permission->isScopeCompany()){
            $cssResult = $model->getCssResultPaginateByListSale(
                $saleIds,
                $projectTypeIds,
                $startDate, 
                $endDate, 
                $teamIds,
                $perPage,
                $orderBy,
                $ariaType,
                $filter
            );
        } elseif ($permission->isScopeTeam()) {
            $arrEmployeeTeam = self::getArrTeamIdByEmployee($userAccount->id);
            $cssResult = $model->getCssResultPaginateByListSaleAndEmployeeTeam(
                $saleIds,
                $projectTypeIds,
                $startDate, 
                $endDate, 
                $teamIds,
                $perPage,
                $orderBy,
                $ariaType, 
                $arrEmployeeTeam,
                $filter
            );
        } elseif($permission->isScopeSelf()){
            $cssResult = $model->getCssResultPaginateByListSaleAndEmployee(
                $saleIds,
                $projectTypeIds,
                $startDate, 
                $endDate, 
                $teamIds,
                $perPage,
                $orderBy,
                $ariaType, 
                $userAccount->id,
                $filter
            );
        }
        return $cssResult;
    }
    
    /**
     * Get analyze paginate by question list
     * @param string $questionIds
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate
     * @param string $teamIds
     * @param int $perPage
     * @param string $orderBy
     * @param string $ariaType
     * @param array $filter
     * @return $cssResult list
     */
    public static function getAnalyzePaginateByQuestion(
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
        $userAccount = Permission::getInstance()->getEmployee(); 
        $permission = Permission::getInstance();
        $model = new CssResult();
        
        if ($permission->isScopeCompany()){
            $cssResult = $model->getCssResultPaginateByListQuestion(
                $questionIds,
                $projectTypeIds,
                $startDate, 
                $endDate, 
                $teamIds,
                $perPage,
                $orderBy,
                $ariaType,
                $filter
            );
        } elseif ($permission->isScopeTeam()) {
            $arrEmployeeTeam = self::getArrTeamIdByEmployee($userAccount->id);
            $cssResult = $model->getCssResultPaginateByListQuestionAndEmployeeTeam(
                $questionIds,
                $projectTypeIds,
                $startDate, 
                $endDate, 
                $teamIds,
                $perPage,
                $orderBy,
                $ariaType, 
                $arrEmployeeTeam,
                $filter
            );
        } elseif($permission->isScopeSelf()){
            $cssResult = $model->getCssResultPaginateByListQuestionAndEmployee(
                $questionIds,
                $projectTypeIds,
                $startDate, 
                $endDate, 
                $teamIds,
                $perPage,
                $orderBy,
                $ariaType, 
                $userAccount->id,
                $filter
            );
        }
        
        return $cssResult;
    }

    /**
     * Get analyze by question list
     * @param string $questionIds
     * @param string $projectTypeIds
     * @param date $startDate
     * @param date $endDate
     * @param string $teamIds
     * @return type
     */
    public static function getAnalyzeByQuestion($questionIds, $projectTypeIds, $startDate, $endDate,$teamIds){
        $userAccount = Permission::getInstance()->getEmployee(); 
        $permission = new Permission();
        $model = new CssResult();
        
        if($permission->isScopeSelf()){
            $cssResult = $model->getCssResultByListQuestionAndEmployee($questionIds, $projectTypeIds, $startDate, $endDate, $teamIds, $userAccount->id);
        }elseif ($permission->isScopeTeam()) {
            $arrEmployeeTeam = self::getArrTeamIdByEmployee($userAccount->id);
            $cssResult = $model->getCssResultByListQuestionAndEmployeeTeam($questionIds, $projectTypeIds, $startDate, $endDate, $teamIds, $arrEmployeeTeam);
        }elseif ($permission->isScopeCompany()){
            $cssResult = $model->getCssResultByListQuestion($questionIds, $projectTypeIds, $startDate, $endDate, $teamIds);
        }
        
        return $cssResult;
    }
    
    /**
     * Get filter analyze by question
     * @param int $saleId
     * @param string $teamIds
     * @param string $projectTypeIds
     * @return Css list
     */
    public static function getFilterAnalyzeByQuestion($questionId,$startDate, $endDate,$teamIds){
        $userAccount = Permission::getInstance()->getEmployee();
        $permission = new Permission();
        $model = new CssResult();
        
        if($permission->isScopeSelf()){
            $css = $model->getCssResultByQuestionAndEmployee($questionId,$startDate, $endDate,$teamIds,$userAccount->id);
        }elseif ($permission->isScopeTeam()) {
            $arrEmployeeTeam = self::getArrTeamIdByEmployee($userAccount->id);
            $css = $model->getCssResultByQuestionAndEmployeeTeam($questionId,$startDate, $endDate,$teamIds,$arrEmployeeTeam);
        }elseif ($permission->isScopeCompany()){
            $css = $model->getCssResultByQuestion($questionId,$startDate, $endDate,$teamIds);
        }
        
        return $css;
    }
    
    /**
     * Get filter analyze by Sale (Employee)
     * @param int $saleId
     * @param string $teamIds
     * @param string $projectTypeIds
     * @return Css list
     */
    public static function getFilterAnalyzeBySale($saleId,$teamIds,$projectTypeIds){
        $userAccount = Permission::getInstance()->getEmployee();
        $permission = new Permission();
        $model = new Css();
        
        if($permission->isScopeSelf()){
            $css = $model->getCssBySaleAndTeamIdsAndListProjectTypeAndEmployee($saleId, $teamIds,$projectTypeIds,$userAccount->id);
        }elseif (!$permission->isScopeCompany() && $permission->isScopeTeam()) {
            $arrEmployeeTeam = self::getArrTeamIdByEmployee($userAccount->id);
            $css = $model->getCssBySaleAndTeamIdsAndListProjectTypeAndEmployeeTeam($saleId, $teamIds,$projectTypeIds,$arrEmployeeTeam);
        }elseif ($permission->isScopeCompany()){
            $css = $model->getCssBySaleAndTeamIdsAndListProjectType($saleId,$teamIds,$projectTypeIds);
        }
        
        return $css;
    }

    public static function getFilterAnalyzeByProjectName($projectName,$teamIds,$projectTypeIds){
        $userAccount = Permission::getInstance()->getEmployee();
        $permission = new Permission();
        $model = new Css();

        if($permission->isScopeSelf()){
            $css = $model->getCssByProjectNameAndTeamIdsAndListProjectTypeAndEmployee($projectName, $teamIds,$projectTypeIds,$userAccount->id);
        }elseif (!$permission->isScopeCompany() && $permission->isScopeTeam()) {
            $arrEmployeeTeam = self::getArrTeamIdByEmployee($userAccount->id);
            $css = $model->getCssByProjectNameAndTeamIdsAndListProjectTypeAndEmployeeTeam($projectName, $teamIds,$projectTypeIds,$arrEmployeeTeam);
        }elseif ($permission->isScopeCompany()){
            $css = $model->getCssByProjectNameAndTeamIdsAndListProjectType($projectName,$teamIds,$projectTypeIds);
        }

        return $css;
    }
    
    /**
     * Get filter analyze by Customer
     * @param string $customerName
     * @param string $teamIds
     * @param string $projectTypeIds
     * @return Css list
     */
    public static function getFilterAnalyzeByCustomer($customerName,$teamIds,$projectTypeIds){
        $userAccount = Permission::getInstance()->getEmployee();
        $permission = new Permission();
        $model = new Css();
        
        if($permission->isScopeSelf()){
            $css = $model->getCssByCustomerAndTeamIdsAndListProjectTypeAndEmployee($customerName, $teamIds,$projectTypeIds,$userAccount->id);
        }elseif (!$permission->isScopeCompany() && $permission->isScopeTeam()) {
            $arrEmployeeTeam = self::getArrTeamIdByEmployee($userAccount->id);
            $css = $model->getCssByCustomerAndTeamIdsAndListProjectTypeAndEmployeeTeam($customerName, $teamIds,$projectTypeIds,$arrEmployeeTeam);
        }elseif ($permission->isScopeCompany()){
            $css = $model->getCssByCustomerAndTeamIdsAndListProjectType($customerName,$teamIds,$projectTypeIds);
        }
        
        return $css;
    }
    
    /**
     * Get filter analyze by BrSE
     * @param string $brseName
     * @param string $teamIds
     * @param string $projectTypeIds
     * @return Css list
     */
    public static function getFilterAnalyzeByBrse($brseName,$teamIds,$projectTypeIds){
        $userAccount = Permission::getInstance()->getEmployee();
        $permission = new Permission();
        $model = new Css();
        
        if($permission->isScopeSelf()){
            $css = $model->getCssByBrseAndTeamIdsAndListProjectTypeAndEmployee($brseName, $teamIds,$projectTypeIds,$userAccount->id);
        }elseif ($permission->isScopeTeam()) {
            $arrEmployeeTeam = self::getArrTeamIdByEmployee($userAccount->id);
            $css = $model->getCssByBrseAndTeamIdsAndListProjectTypeAndEmployeeTeam($brseName, $teamIds,$projectTypeIds,$arrEmployeeTeam);
        }elseif ($permission->isScopeCompany()){
            $css = $model->getCssByBrseAndTeamIdsAndListProjectType($brseName,$teamIds,$projectTypeIds);
        }
        
        return $css;
    }
    
    /**
     * Get filter analyze by PM
     * @param string $pmName
     * @param string $teamIds
     * @param string $projectTypeIds
     * @return Css list
     */
    public static function getFilterAnalyzeByPm($pmName,$teamIds,$projectTypeIds){
        $userAccount = Permission::getInstance()->getEmployee(); 
        $permission = new Permission();
        $model = new Css();
        
        if($permission->isScopeSelf()){
            $css = $model->getCssByPmAndTeamIdsAndListProjectTypeAndEmployee($pmName, $teamIds,$projectTypeIds,$userAccount->id);
        }elseif (!$permission->isScopeCompany() && $permission->isScopeTeam()) {
            $arrEmployeeTeam = self::getArrTeamIdByEmployee($userAccount->id);
            $css = $model->getCssByPmAndTeamIdsAndListProjectTypeAndEmployeeTeam($pmName, $teamIds,$projectTypeIds,$arrEmployeeTeam);
        }elseif ($permission->isScopeCompany()){
            $css = $model->getCssByPmAndTeamIdsAndListProjectType($pmName,$teamIds,$projectTypeIds);
        }
        
        return $css;
    }
    
    /**
     * Get filter analyze by team
     * @param string $teamId
     * @param int $projectTypeIds
     * @return Css list
     */
    public static function getFilterAnalyzeByTeam($teamId,$projectTypeIds){
        $userAccount = Permission::getInstance()->getEmployee(); 
        $permission = new Permission();
        $model = new Css();

        if($permission->isScopeSelf()){
            $css = $model->getCssByTeamIdAndListProjectTypeAndEmployee($teamId,$projectTypeIds,$userAccount->id);
        }elseif (!$permission->isScopeCompany() && $permission->isScopeTeam()) {
            $arrEmployeeTeam = self::getArrTeamIdByEmployee($userAccount->id);
            $css = $model->getCssByTeamIdAndListProjectTypeAndEmployeeTeam($teamId,$projectTypeIds,$arrEmployeeTeam);
        }elseif ($permission->isScopeCompany()){
            $css = $model->getCssByTeamIdAndListProjectType($teamId,$projectTypeIds);
        }
        
        return $css;
    }
    
    /**
     * Get filter analyze by project type
     * @param int $projectTypeId
     * @param string $teamIds
     * @return type
     */
    public static function getFilterAnalyzeByProjectType($projectTypeId,$teamIds){
        $userAccount = Permission::getInstance()->getEmployee(); 
        $permission = new Permission();
        $model = new Css();
        
        if($permission->isScopeSelf()){
            $css = $model->getCssByProjectTypeAndTeamAndEmployee($projectTypeId,$teamIds,$userAccount->id);
        }elseif (!$permission->isScopeCompany() && $permission->isScopeTeam()) {
            $arrEmployeeTeam = self::getArrTeamIdByEmployee($userAccount->id);
            $css = $model->getCssByProjectTypeAndTeamAndEmployeeTeam($projectTypeId,$teamIds,$arrEmployeeTeam);
        }elseif ($permission->isScopeCompany()){
            $css = $model->getCssByProjectTypeAndTeam($projectTypeId,$teamIds);
        }
        
        return $css;
    }


    /**
     * Get CSS list on CSS list page
     * @param int $perPage
     * @return Css list
     */
    public static function getCssListByPermission($order, $dir){
        $curEmp = Permission::getInstance()->getEmployee(); 
        $permission = new Permission();
        $model = new Css();

        if ($permission->isScopeCompany()) {
            $css = $model->getCssList(null, null, $order, $dir);
        } elseif ($arrTeamId = $permission->isScopeTeam()) {
            $arrTeamId = is_array($arrTeamId) ? $arrTeamId : [];
            $css = $model->getCssList($curEmp, $arrTeamId, $order, $dir);
        } elseif($permission->isScopeSelf()) {
            $css = $model->getCssList($curEmp, null, $order, $dir);
        }
        
        return $css;
    }
    
    /**
     * Get Css detail permission
     * @param int $cssId
     * @param int $employeeId
     * @return boolean
     */
    public static function isCssPermission($css)
    {
        $permission = new Permission();
        $relates = explode(',', $css->rikker_relate);
        $userAccount = $permission->getEmployee();
        if (in_array($userAccount->email, $relates) //relater
            || strtolower($userAccount->email) == strtolower($css->pm_email)) {
            return true;
        }

        return static::hasPermission($css);
    }

    /**
     * Check current user has CSS permission
     *
     * @param type $css
     * @return boolean
     */
    public static function hasPermission($css)
    {
        return Permission::getInstance()->isScopeCompany()
                || static::isCssTeam($css)
                || static::isCssSelf($css);
    }

    /**
     * Check Css of self
     * @param int $employeeId
     * @return boolean
     */
    protected static function isCssSelf($css)
    {
        $userAccount = Permission::getInstance()->getEmployee();        
        return ($css->employee_id == $userAccount->id); 
    }
    
    /**
     * Check Css of self team
     * @param int $cssId
     * @param int $employeeId
     * @return boolean
     */
    protected static function isCssTeam($css)
    {
        if (!Permission::getInstance()->isScopeTeam()) {
            return false;
        }
        $arrTeamId = Permission::getInstance()->isScopeTeam();

        if (count($arrTeamId)) {
            //Get CssTeam by teams
            $cssTeamModel = new CssTeams();
            $cssTeams = $cssTeamModel->getCssTeamByCssIdAndTeamIds($css->id, $arrTeamId);

            //Check is css team
            if (count($cssTeams)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get team child lowest list by employee
     * @param int $employeeId
     * @return array teamId
     */
    public static function getArrTeamIdByEmployee($employeeId)
    {
        $teamMembersModel = new TeamMember();
        $teamMembers = $teamMembersModel->getTeamMembersByEmployee($employeeId);

        //get teams of current user
        $arrTeamIdTemp = [];
        foreach ($teamMembers as $item) {
            $arrTeamIdTemp[] = self::getTeamChild($item->team_id);
        }

        $arrTeamId = [];
        for ($i=0; $i<count($arrTeamIdTemp); $i++) {
            for ($j=0; $j<count($arrTeamIdTemp[$i]); $j++) {
                $arrTeamId[] = $arrTeamIdTemp[$i][$j];
            }
        }
        return $arrTeamId;
    }

    /**
     * Get team child lowest list by teamId
     * @param int $teamId
     * @return array teamId
     */
    public static function getTeamChild($teamId)
    {
        $arrTeamId = [];
        if (self::isTeamChildLowest($teamId)) {
            $arrTeamId[] = $teamId;
            return $arrTeamId;
        } else {
            $arrTeamId[] = $teamId;
            $model = new Team();
            $teamChilds = $model->getTeamByParentId($teamId);

            if (count($teamChilds)) {
                foreach ($teamChilds as $child) {
                    if (self::isTeamChildLowest($child->id)) {
                        $arrTeamId[] = $child->id;
                    } else {
                        $arrTeamId[] = $child->id;
                        $childs = self::getTeamChild($child->id);
                        $count = count($childs);
                        for ($i=0; $i<$count; $i++) {
                            $arrTeamId[] = $childs[$i];
                        }
                    }
                }
            }

            return $arrTeamId;
        }
    }

    /**
     * Check is team child lowest
     * @param int $teamId
     * @return boolean
     */
    public static function isTeamChildLowest($teamId)
    {
        $model = new Team();
        $teamChilds = $model->getTeamByParentIdNoTrashed($teamId);
        if (count($teamChilds)) {
            return false;
        }

        return true;
    }
}
