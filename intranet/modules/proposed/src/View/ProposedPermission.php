<?php

namespace Rikkei\Proposed\View;

use Rikkei\Team\View\Permission;

class ProposedPermission
{
    /**
     * [isAllow description]
     * @return boolean
     */
    public static function isAllow()
    {
       return Permission::getInstance()->isAllow('proposed::manage-proposed.index');
    }
}
