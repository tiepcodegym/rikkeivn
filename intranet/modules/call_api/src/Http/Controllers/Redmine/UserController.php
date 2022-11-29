<?php

namespace Rikkei\CallApi\Http\Controllers\Redmine;

use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\CallApi\Helpers\Redmine;
use Rikkei\Team\View\Permission;

class UserController extends Controller
{
    /**
     * create user in redmine
     *
     * @return type
     */
    public function create()
    {
        $redmine = new Redmine();
        $response = $redmine->userCreate(Permission::getInstance()->getEmployee());
        return response()->json($response);
    }

    /**
     * change password of user in redmine
     */
    public function changePass()
    {
        $redmine = new Redmine();
        $response = $redmine->userChangePass(Permission::getInstance()->getEmployee());
        return response()->json($response);
    }
}
