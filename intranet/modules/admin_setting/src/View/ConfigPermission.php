<?php

namespace Rikkei\AdminSetting\View;

use Rikkei\Team\View\Permission;

class ConfigPermission
{
    /**
     * [isAllow description]
     * @return boolean
     */
    public function isAllow()
    {
       return Permission::getInstance()->isAllow('mobile-config::mobile-config.index');
    }
}
