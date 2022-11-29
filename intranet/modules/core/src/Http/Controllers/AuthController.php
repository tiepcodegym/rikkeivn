<?php

namespace Rikkei\Core\Http\Controllers;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Config;
use Auth;
use Socialite;
use Rikkei\Core\Model\User;
use URL;
use Session;
use Redirect;
use Illuminate\Support\ViewErrorBag;
use Lang;
use Illuminate\Support\MessageBag;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\View\View;
use Illuminate\Support\Facades\Log;
use Rikkei\Core\Http\Middleware\Authenticate;
use Exception;
use Rikkei\Notify\Model\NotifyFlag;


class AuthController extends Controller
{

    /**
     * Redirect to social's sign in page
     *
     * @param string $provider
     * @return \Illuminate\Http\Response
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function login($provider)
    {
        $providerKey = Config::get('services.' . $provider);
        if (empty($providerKey)) {
            throw new BadRequestHttpException("Provider '$provider' is invalid");
        }
        return Socialite::driver($provider)->with([
            'access_type' => 'offline',
            'prompt' => 'consent select_account'
        ])->redirect();
    }

    /**
     * Handler after social's sign in page callback
     *
     * @param string $provider
     * @return \Illuminate\Http\Response
     */
    public function callback($provider)
    {
        try {
            $user = Socialite::driver($provider)->user();
        } catch (\Exception $ex) {
            Log::info($ex);
            return redirect()->route('core::home');
        }
        $email = $user->email;
        if (!$email) {
            return redirect('/')->withErrors('Error Social connect');
        }
        //add check email allow
        if (! View::isEmailAllow($email)) {
            $this->processNewAccount();
            return Redirect::away($this->getGoogleLogoutUrl('/'))
                    ->send();
        }
        
        $nickName = !empty($user->nickname) ? $user->nickname : preg_replace('/@.*$/', '', $user->email);
        $account = User::where('email', $user->email)
            ->first();
        $employee = Employee::where('email', $user->email)
            ->first();
        
        //add employee if is root
        if (! $employee) {
            if (View::isRoot($email)) {
                $employee = new Employee();
                $employee->setData([
                    'email' => $user->email,
                    'name' => $user->name,
                    'nickname' => $nickName
                ]);
                $employee->save();
            } else {
                $this->processNewAccount(Lang::get('core::message.You donot have permission login'));
                return Redirect::away($this->getGoogleLogoutUrl('/'))
                    ->send();
            }
        } elseif (! View::isRoot($email) && ! $employee->isAllowLogin()) {
            $this->processNewAccount(Lang::get('core::message.You donot have permission login'));
            return Redirect::away($this->getGoogleLogoutUrl('/'))
                    ->send();
        }

        $employeeId = $employee->id;
        //create or update accout
        try {
            $fullAvatar = preg_replace('/s50\//', '', $user->avatar);
            if (! $account) {
                $account = User::create([
                    'email' => $user->email,
                    'name' => $user->name,
                    'token' => $user->token,
                    'refresh_token' => $user->refreshToken,
                    'employee_id' => $employeeId,
                    'google_id' => $user->id,
                    'avatar_url' => $fullAvatar,
                    'expires_in' => $user->expiresIn
                ]);
                $account->employee_id = $employeeId;
                $account->save();
                //add notify flag for employee
                NotifyFlag::create([
                    'employee_id' => $employeeId
                ]);
            } else {
                //update information of user
                $account = $account->setData([
                    'name' => $user->name,
                    'token' => $user->token,
                    'refresh_token' => $user->refreshToken,
                    'google_id' => $user->id,
                    'employee_id' => $employeeId,
                    'expires_in' => $user->expiresIn,
                ]);
                if (!$account->avatar_url) {
                    $account->avatar_url = $fullAvatar;
                } else {
                    //if link google then update
                    preg_match('/(.*)(googleusercontent.com)(.*)/', $account->avatar_url, $matches);
                    if ($matches) {
                        $account->avatar_url = $fullAvatar;
                    }
                }
                $account->save();
            }
            Auth::login($account);
            // save last user session
            $account->saveLastSession();
            $account->generateApiToken(false, false);
        } catch (Exception $ex) {
            Log::error($ex);
            return redirect('/')->withErrors($ex);
        }
        if(Session::get('curUrl')){
            $curUrl = Session::get('curUrl');
            Session::forget('curUrl');
            return redirect($curUrl);
        }
        return redirect('/');
    }

    /**
     * Logout action
     *
     * @return redirect
     */
    public function logout($message = null)
    {
        if (!Auth::check()) {
            return redirect('/');
        }
        $user = Auth::user();
        if ($user) {
            try {
                $user->generateApiToken(true);
            } catch (Exception $ex) {
                Log::error($ex);
            }
        }
        Auth::logout();
        Session::flush();
        if ($message) {
            $message = new MessageBag([
                $message
            ]);
            Session::flash(
                'errors', Session::get('errors', new ViewErrorBag)->put('default', $message)
            );
        }
        return Redirect::away($this->getGoogleLogoutUrl())
            ->send();
    }
    
    /**
     * google logout url
     * 
     * @param string $redirect
     * @return string
     */
    protected function getGoogleLogoutUrl($redirect = '/')
    {
        return 'https://www.google.com/accounts/Logout' . 
            '?continue=https://appengine.google.com/_ah/logout' . 
            '?continue=' . URL::to($redirect);
    }
    
    /**
     * process if login by not account rikkei
     * 
     * @return redirect
     */
    protected function processNewAccount($message = null)
    {
        if (! $message) {
            $message = new MessageBag([
                Lang::get('core::message.Please use Rikkisoft\'s Email!')
            ]);
        } else {
            $message = new MessageBag([
                $message
            ]);
        }
        Session::flash(
            'errors', Session::get('errors', new ViewErrorBag)->put('default', $message)
        );
        return Redirect::away($this->getGoogleLogoutUrl('/'))
            ->send();
    }

    /**
     * refresh google account data
     */
    public function refreshAccount()
    {
        if (!Auth::check()) {
            return response()->json(trans('core::message.You are not logged in'), 403);
        }
        $account = Auth::user();
        //if link google then update
        preg_match('/(.*)(googleusercontent.com)(.*)/', $account->avatar_url, $matches);
        if (!$matches) {
            return response()->json('OK');
        }
        $token = $account->token;
        $provider = 'google';
        try {
            $providerUser = Socialite::driver($provider)->userFromToken($token);
            if (!$providerUser) {
                throw new \Exception('Error!', 404);
            }
            if ($account->avatar_url != $providerUser->avatar) {
                $account->avatar_url = preg_replace('/s50\//', '', $providerUser->avatar);
                $account->save();
            }

            return [
                'avatar' => $providerUser->avatar
            ];
        } catch (\Exception $ex) {
            return response()->json(trans('core::message.An error occurred with your account, please logout and login again'), 500);
        }
    }
}
