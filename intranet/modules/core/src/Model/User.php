<?php

namespace Rikkei\Core\Model;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;
use Rikkei\Team\Model\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\View as CoreView;
use Illuminate\Support\Facades\Config;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Rikkei\Core\View\CoreImageHelper;
use Rikkei\Core\View\CoreUrl;

class User extends CoreModel implements Authenticatable
{

    use SoftDeletes;

    /*
     * const avatar key store session
     */
    const AVATAR = 'account.logged.avatar';

    /*
     * primary key
     */
    protected $primaryKey = 'employee_id';
    public $incrementing = false;

    /*
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'employee_id', 'google_id', 'name', 'email', 'avatar_url', 'token', 'refresh_token', 'notify_num', 'expires_in'
    ];

    /*
     * The attributes that should be hidden for arrays.
     */
    protected $hidden = [
        'token',
    ];

    protected static $employee = null;

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return $this->getKeyName();
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return null;
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken()
    {
        return $this->{$this->getRememberTokenName()};
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param string $value
     * @return void
     */
    public function setRememberToken($value)
    {
        $this->{$this->getRememberTokenName()} = $value;
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return 'token';
    }

    /**
     * get Employee
     *
     * @return model
     */
    public function getEmployee()
    {
        return Employee::where('email', $this->email)
            ->first();
    }

    /**
     * get employee of user logged
     *
     * @return model
     */
    public static function getEmployeeLogged()
    {
        if (self::$employee === null) {
            if (CoreUrl::isApi()) {
                $user = Auth::guard('api')->user();
            } else {
                $user = Auth::user();
            }
            if (!$user) {
                self::$employee = false;
            } else {
                $employee = \session("employee_{$user->employee_id}");
                if (empty($employee)) {
                    $employee = Employee::find($user->employee_id);
                }
                self::$employee = $employee;
            }
        }
        return self::$employee;
    }

    /**
     * get avatar of user logged
     *
     * @return string
     */
    public static function getAvatar()
    {
        if (Auth::user()) {
            return Auth::user()->avatar_url;
        }
        return null;
    }

    /**
     * get nickname of user logged
     *
     * @return string
     */
    public static function getNickName()
    {
        $email = Permission::getInstance()->getEmployee()->email;
        return preg_replace('/@.*/', '', $email);
    }

    /**
     * save email for user if changing and delete that user session
     *
     * @return object
     */
    public static function saveEmail($employee)
    {
        $user = self::where('employee_id', $employee->id)->first();
        if ($user && $user->email !== $employee->email) {
            $user->email = $employee->email;
            $user->save();
            $user->destroyLastSession();
            return true;
        }
        return false;
    }

    /**
     * save lastest user session
     *
     * @return object
     */
    public function saveLastSession()
    {
        $new_sessid = Session::getId();
        $last_session = Session::getHandler()->read($this->last_sessid);
        if ($last_session) {
            Session::getHandler()->destroy($this->last_sessid);
        }
        $this->last_sessid = $new_sessid;
        $this->save();
    }

    /**
     * destroy lastest user session
     *
     * @return object
     */
    public function destroyLastSession()
    {
        $last_session = Session::getHandler()->read($this->last_sessid);
        if ($last_session) {
            Session::getHandler()->destroy($this->last_sessid);
        }
        $this->last_sessid = '';
        $this->save();
    }

    /**
     * force user log out when they change their employee email
     */
    public static function forceLogOut()
    {
        Auth::logout();
        return redirect('/');
    }

    /**
     * upload Avatar of user
     *
     * @param object $upload FileUpload Object
     * @param object $employee
     * @return string
     */
    public static function uploadAvatar($upload, $employee)
    {
        $response = [];
        if (!$upload) {
            $response['status'] = 1;
            $response['filePath'] = null;
            return $response;
        }
        $user = self::where('employee_id', $employee->id)
            ->first();
        if (!$user) {
            $user = new self();
            $user->employee_id = $employee->id;
            $user->email = $employee->email;
        }
        try {
            $fileName = CoreView::uploadFile(
                $upload,
                Config::get('general.upload_storage_public_folder') . '/' . Employee::AVATAR_FOLDER . $employee->id,
                Config::get('services.file.image_allow'),
                Config::get('services.file.image_max'),
                true
            );
            // remove avatar old
            if ($user->avatar_url) {
                $avatarInfo = CoreImageHelper::getInstance()->splitPath($user->avatar_url);
                if (Storage::disk('public')->exists(Employee::AVATAR_FOLDER
                    . $employee->id . '/' . $avatarInfo['1'] . $avatarInfo['2'])
                ) {
                    Storage::disk('public')->delete(Employee::AVATAR_FOLDER
                        . $employee->id . '/' . $avatarInfo['1'] . $avatarInfo['2']);
                }
            }
            $filePath = Config::get('general.upload_folder') . '/'
                . Employee::AVATAR_FOLDER . $employee->id
                . '/' . $fileName;
            $user->avatar_url = asset($filePath);
            $user->save();
            $response['status'] = 1;
            $response['filePath'] = $filePath;
            return $response;
        } catch (Exception $ex) {
            $response['status'] = 0;
            $response['message'] = 'Error: Unable to upload avatar';
            Log::error($ex);
            return $response;
        }
    }

    /**
     * render api token
     *
     * @param bool $isNull set api token is null
     * @param bool $isOverwrite overwirte token
     * @return string
     */
    public function generateApiToken($isNull = false, $isOverwrite = true)
    {
        if ($isNull) {
            $this->api_token = null;
            $this->token = null;
        } else {
            if (!$this->api_token || $isOverwrite) {
                $str = time();
                $strRandom = md5(str_random(5) . $str . mt_rand());
                if (strlen($str) > 20) {
                    $str = substr($str, 0, 20);
                }
                $str .= substr($strRandom, 0, 40 - strlen($str));
                $this->api_token = $str;
            }
        }
        $this->save();
        return $this->api_token;
    }

    /**
     * @param null $empId
     * @param bool $isDeleted
     */
    public function refreshGoogleAccessToken()
    {
        if (!$this->refresh_token) {
            return;
        }
        $configGoogle = config('services.google');
        $ggClient = new \Google_Client([
            'client_id' => $configGoogle['client_id'],
            'client_secret' => $configGoogle['client_secret']
        ]);
        $response = $ggClient->refreshToken($this->refresh_token);
        if (!isset($response['access_token'])) {
            return;
        }
        $this->token = $response['access_token'];
        $this->refresh_token = $response['refresh_token'];
        $this->expires_in = $response['expires_in'];
        return $this->save();
    }

    /**
     * @param int $empId
     */
    public static function changeRoles($empId = 0)
    {
        $condition = Employee::whereNull('deleted_at')
            ->where(function ($sql) {
                $sql->whereNull('leave_date')
                    ->orWhere('leave_date', '>', date('Y-m-d H:i:s'));
            })->select('id', 'email');
        if ($empId) {
            $employee = $condition->where('id', $empId)->first();
            self::checkExistUser($employee);
        } else {
            $condition->chunk(100, function ($employees) {
                foreach ($employees as $employe) {
                    self::checkExistUser($employe);
                }
            });
        }
    }

    /**
     * kiểm tra user tồn tại ở bảng users chưa(cho trường hợp đang nhập ở app mà chưa đăng nhập web)
     * @param $employe
     */
    public static function checkExistUser($employe)
    {
        $user = User::where('employee_id', $employe->id)->first();
        if ($user) {
            $user->roles = json_encode(self::preFilterSaveRoles($employe->getPermission()));
            $user->save();
        } else {
            $user = new User();
            $user->employee_id = $employe->id;
            $user->email = $employe->email;
            $user->roles = json_encode(self::preFilterSaveRoles($employe->getPermission()));
            $user->save();
        }
    }

    /**
     *  return old format employee permisison
     *
     * @param array $permissions
     * @return array
     */
    public static function preFilterSaveRoles($permissions)
    {
        if (isset($permissions['team'])) {
            if (isset($permissions['team']['route'])) {
                $routesTeam = [];
                foreach ($permissions['team']['route'] as $teamId => $routePermiss) {
                    $routesTeam[$teamId] = $routePermiss['permissScopes'];
                }
                $permissions['team']['route'] = $routesTeam;
            }
            if (isset($permissions['team']['action'])) {
                $actionsTeam = [];
                foreach ($permissions['team']['action'] as $teamId => $actionPermiss) {
                    $actionsTeam[$teamId] = $actionPermiss['permissScopes'];
                }
                $permissions['team']['action'] = $actionsTeam;
            }
        }
        return $permissions;
    }

    /**
     * get scope language
     *
     * @return array
     */
    public static function scopeLangArray()
    {
        return [
            1 => 'vi',
            2 => 'en',
            3 => 'jp'
        ];
    }
}
