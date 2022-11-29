<?php

namespace Rikkei\CallApi\Http\Controllers\Gitlab;

use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Project\View\ProjectGitlab;
use Rikkei\Team\View\Permission;

class UserController extends Controller
{
    /**
     * create user in gitlab
     *
     * @return type
     */
    public function create()
    {
        $gitlab = new ProjectGitlab();
        $response = $gitlab->createAccount(Permission::getInstance()->getEmployee());
        return response()->json($response);
    }

    /**
     * change password of user in gitlab
     */
    public function changePass()
    {
        $gitlab = new ProjectGitlab();
        $response = $gitlab->userChangePass(Permission::getInstance()->getEmployee());
        return response()->json($response);
    }
}
