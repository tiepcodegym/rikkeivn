<?php

namespace Rikkei\CallApi\Http\Controllers\Sonar;

use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\CallApi\Helpers\Sonar;
use Rikkei\Team\View\Permission;

class UserController extends Controller
{
    public function create()
    {
        $employee = Permission::getInstance()->getEmployee();
        $password = substr(md5(time() . mt_rand()), 0, 10);
        $account = $employee->getAccount();
        // search user
        $isAccount = Sonar::isUserExists($account);
        if (is_array($isAccount)) {
            return $isAccount;
        }
        if ($isAccount) {
            $response['success'] = 1;
            $response['message'] = trans(
                'call_api::message.Account :account exists',
                ['account' => $account]
            );
            return $response;
        }
        $sonar = Sonar::connect();
        $api = $sonar->post('users/create', ['form_params' => [
            'email' => $employee->email,
            'login' => $account,
            'name' => $employee->name,
            'password' => $password,
        ]]);
        $response = [];
        if ($api->getStatusCode() != 200) {
            $response['error'] = 1;
            $response['message'] = Sonar::getError($api);
            return $response;
        }
        $response['success'] = 1;
        $response['message'] = trans('call_api::message.Create user success. User login: :login, user password: :pass', [
            'login' => $account,
            'pass' => $password,
        ]);
        return $response;
    }

    /**
     * change password
     */
    public function changePassword()
    {
        $employee = Permission::getInstance()->getEmployee();
        $password = substr(md5(time() . mt_rand()), 0, 10);
        $account = $employee->getAccount();
        // search user
        $isAccount = Sonar::isUserExists($account);
        if (is_array($isAccount)) {
            return $isAccount;
        }
        if (!$isAccount) {
            $response['success'] = 1;
            $response['message'] = trans(
                'call_api::message.Account :account not found',
                ['account' => $account]
            );
            return $response;
        }
        $sonar = Sonar::connect();
        $api = $sonar->post('users/change_password', ['form_params' => [
            'login' => $account,
            'password' => $password,
        ]]);
        $response = [];
        if (!in_array($api->getStatusCode(), [200, 204])) {
            $response['error'] = 1;
            $response['message'] = Sonar::getError($api);
            return $response;
        }
        $response['success'] = 1;
        $response['message'] = trans('call_api::message.Change password success. User login: :login, user password: :pass', [
            'login' => $account,
            'pass' => $password,
        ]);
        return $response;
    }
}
