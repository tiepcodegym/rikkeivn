<?php

namespace Rikkei\Sales\View;

use Rikkei\Team\View\Permission;
use Rikkei\Project\Model\Project;
use Rikkei\Sales\Model\Company;
use DB;

class CustomerHelp
{
    /**
     * Check has permission edit customer
     * Has permission if current user who created this customer
     * or created company of customer
     * or manger of company of customer
     * or saler support of company of customer
     * 
     * @param Customer $customer
     *
     * @return boolean
     */
    public function hasPermissionEdit($customer)
    {
        $urlRoute = 'sales::customer.edit';
        $company = Company::find($customer->company_id);
        $currentUser = Permission::getInstance()->getEmployee();
        // get teams of current user
        $teamsOfEmp = \Rikkei\Team\View\CheckpointPermission::getArrTeamIdByEmployee($currentUser->id);
        // get list employees of team
        $employeesOfTeam = DB::table('team_members')->whereIn('team_id', $teamsOfEmp);
        $employeeIds = $employeesOfTeam->lists('employee_id');
        if (Permission::getInstance()->isScopeCompany(null, $urlRoute)) {
            return true;
        } elseif (Permission::getInstance()->isScopeTeam(null, $urlRoute) && in_array($customer->created_by, $employeeIds)) {
            return true;
        } elseif (Permission::getInstance()->isScopeSelf(null, $urlRoute)
            && in_array($currentUser->id, [
                $company->manager_id,
                $company->sale_support_id,
                $company->created_by,
                $customer->created_by,
            ])) {
            return true;
        }

        return false;
    }

    /**
     * Check permission view customer edit page
     * Has permisison if current user has permission edit
     * or is saler or pqa of project of customer
     *
     * @param Customer $customer
     * @return boolean
     */
    public function hasPermissionView($customer)
    {
        //get project by permission project dashboard
        $projectModel = new Project();
        $projects = $projectModel->getProjectsJoined();

        $customerIds = $projects->lists('cust_contact_id')->toArray();

        return $this->hasPermissionEdit($customer) || in_array($customer->id, $customerIds);
    }
}
