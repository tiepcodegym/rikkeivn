<?php

namespace Rikkei\Sales\View;

use Rikkei\Team\View\Permission;

class CompanyHelp
{
    /**
     * Check has permission edit company
     * 
     * @param Company $company
     *
     * @return boolean
     */
    public function hasPermissionEdit($company)
    {
        $urlRoute = 'sales::company.list';
        if (Permission::getInstance()->isScopeCompany(null, $urlRoute)) {
            return true;
        }

        $currentUser = Permission::getInstance()->getEmployee();
        if ((Permission::getInstance()->isScopeSelf(null, $urlRoute)
                || Permission::getInstance()->isScopeTeam(null, $urlRoute))
            && in_array($currentUser->id, [$company->manager_id, $company->sale_support_id, $company->created_by])) {
            return true;
        }

        return false;
    }
}
