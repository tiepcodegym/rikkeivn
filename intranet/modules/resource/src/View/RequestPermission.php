<?php

namespace Rikkei\Resource\View;

use Rikkei\Team\Model\Checkpoint;
use Rikkei\Team\Model\Employee;
use Rikkei\Sales\View\CssPermission;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Permission;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Resource\Model\ResourceRequest;
use Rikkei\Team\View\CheckpointPermission;

class RequestPermission {
    
    /**
     *  store this object
     * @var object
     */
    protected static $instance;
    
    public function isCOOAccount()
    {
        $curEmp = Permission::getInstance()->getEmployee();
        $cooAccounts = CoreConfigData::getCOOAccount();
        return in_array($curEmp->email, $cooAccounts);
    }
    
    /**
     * Singleton instance
     * 
     * @return \Rikkei\Team\View\CheckpointPermission
     */
    public static function getInstance()
    {
        if (! isset(self::$instance)) {
            self::$instance = new static;
        }
        return self::$instance;
    }
    
    /**
     * Get request list by permission
     * 
     * @param string $order
     * @param string $dir
     * @return list ResourceRequest
     */
    public function getList($order, $dir, $filterTeam, $filterProLangs, $filterTitle, $filterRecruiter) {
        $emp = Permission::getInstance()->getEmployee();
        $model = new ResourceRequest();
        $per = new Permission();
        $list = null;
        
        if (Permission::getInstance()->isScopeCompany()) {
            $list = $model->getList($order, $dir, $filterTeam, $filterProLangs, null, null, $filterTitle, $filterRecruiter);
        } elseif (Permission::getInstance()->isScopeTeam()) {
            $teamIds = Permission::getInstance()->isScopeTeam();
            $list = $model->getList($order, $dir, $filterTeam, $filterProLangs, $emp, $teamIds, $filterTitle, $filterRecruiter);
        } elseif (Permission::getInstance()->isScopeSelf()) {
            $list = $model->getList($order, $dir, $filterTeam, $filterProLangs, $emp, null, $filterTitle, $filterRecruiter);
        }
          
        return $list;
    }

    /**
     * Get sum of all resource request having inprogress status
     * 
     * @return list ResourceRequest
     */
    public function countAllResourceRequest($filterTeam, $filterProLangs) {
        $emp = Permission::getInstance()->getEmployee();
        $model = new ResourceRequest();
        $per = new Permission();
        $list = null;
        
        if (Permission::getInstance()->isScopeCompany()) {
            $list = $model->countAllResourceRequest( $filterTeam, $filterProLangs);
        } elseif (Permission::getInstance()->isScopeTeam()) {
            $teamIds = CheckpointPermission::getArrTeamIdByEmployee($emp->id);
            $list = $model->countAllResourceRequest( $filterTeam, $filterProLangs, $emp, $teamIds);
        } elseif (Permission::getInstance()->isScopeSelf()) {
            $list = $model->countAllResourceRequest( $filterTeam, $filterProLangs, $emp);
        }
          
        return $list;
    }

    public function getRequestsProgress($candidate, $isExpired = false)
    {
        $where = [
            ['status', getOptions::STATUS_INPROGRESS], 
            ['approve', getOptions::APPROVE_ON],
            ['requests.type', getOptions::TYPE_RECRUIT],
        ];
        $orWhere = [['requests.id', $candidate->request_id]];

        return ResourceRequest::getInstance()->getAllList($where, $orWhere, $isExpired);
    }
}
