<?php
namespace Rikkei\ManageTime\View;

use Rikkei\Team\View\Permission;

class ProjectPermission
{

    /**
     * [isAllowReport: is Allow Report Permission]
     * @return boolean
     */
    public static function isAllowReport()
    {
        return Permission::getInstance()->isAllow('manage_time::timekeeping.manage.report_project_timekeeping_systena');
    }
}
