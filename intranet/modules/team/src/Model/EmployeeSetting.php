<?php

namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Core\Model\EmailQueue;
use Illuminate\Support\Facades\DB;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\Model\EmployeeWork;

class EmployeeSetting extends CoreModel
{
    protected $table = 'employee_setting';
    protected $fillable = ['employee_id', 'key', 'value', 'is_current'];

    const KEY_PASS_FILE = 'pass_open_file';
    const AUTHOR_MAIL = 'hungnt2@rikkeisoft.com';

    /**
     * get key object model
     * @param type $employeeId
     * @param type $key
     * @return object
     */
    public static function getKeyItem($employeeId, $key)
    {
        return self::where('employee_id', $employeeId)
                ->where('key', $key)
                ->where('is_current', 1)
                ->first();
    }

    /**
     * get key value
     * @param type $employeeId
     * @param type $key
     * @return string
     */
    public static function getKeyValue($employeeId, $key)
    {
        $keyCache = 'emp_setting_' . $key;
        if ($value = CacheHelper::get($keyCache, $employeeId)) {
            return $value;
        }
        $keyItem = self::getKeyItem($employeeId, $key);
        $value = null;
        if ($keyItem) {
            $value = $keyItem->value;
        }
        CacheHelper::put($keyCache, $value, $employeeId);
        return $value;
    }

    /**
     * insert or update item
     * @param type $employeeId
     * @param type $data
     * @return boolean
     */
    public static function insertOrUpdate($employeeId, $data = [])
    {
        if (!$data) {
            return false;
        }
        foreach ($data as $key => $value) {
            $item = self::getKeyItem($employeeId, $key);
            if (is_array($value)) {
                $value = json_encode($value);
            }
            if ($item) {
                $item->value = $value;
                $item->save();
            } else {
                self::create([
                    'employee_id' => $employeeId,
                    'key' => $key,
                    'value' => $value
                ]);
            }
        }
        return true;
    }

    /*
     * get key value history
     */
    public static function getKeyValHistory($employeeId, $key)
    {
        $collect = self::where('employee_id', $employeeId)
                ->where('key', $key)
                ->orderBy('is_current', 'desc')
                ->orderBy('updated_at', 'desc')
                ->get();
        if ($key == self::KEY_PASS_FILE) {
            return $collect->map(function ($item) {
                $item->value = decrypt($item->value);
                return $item;
            });
        }
        return $collect;
    }

    /**
     * @overide before save
     * @param array $options
     */
    public function save(array $options = array()) {
        CacheHelper::forget('emp_setting_' . $this->key, $this->employee_id);
        parent::save($options);
    }

    /**
     * cronjob send password open salary mail attachment file, need contain database transaction
     * @return type
     */
    public static function cronSendFilePass($employees = null, $create = false)
    {
        $tblEmp = Employee::getTableName();
        $key = self::KEY_PASS_FILE;
        if (!$employees) {
            $employees = Employee::select($tblEmp.'.id', $tblEmp.'.email', $tblEmp.'.name', 'setting.value')
                    ->join(self::getTableName() . ' as setting', $tblEmp.'.id', '=', 'setting.employee_id')
                    ->where('setting.key', $key)
                    ->where('setting.is_current', 1)
                    ->whereNotNull('setting.value')
                    ->where(function ($query) use ($tblEmp) {
                        $query->whereNull($tblEmp.'.leave_date')
                            ->orWhereRaw('DATE('. $tblEmp .'.leave_date) > CURDATE()');
                    })
                    ->groupBy($tblEmp.'.id')
                    ->get();
        }

        if ($employees->isEmpty()) {
            return;
        }

        $template = 'event::send_email.pass_open_file';
        $subject = trans('team::profile.mail_password_open_file_subject');
        foreach ($employees->chunk(500) as $chunk) {//insert each 500 item
            $dataInsert = [];
            foreach ($chunk as $emp) {
                if ($create) {
                    if (!$emp->value) {
                        $keyValue = encrypt(str_random(8));
                        $settingItem = self::getKeyItem($emp->id, $key);
                        if (!$settingItem) {
                            //create
                            self::create([
                                'employee_id' => $emp->id,
                                'key' => $key,
                                'value' => $keyValue,
                                'is_current' => 1
                            ]);
                        } elseif (!$settingItem->value) {
                            //update
                            $settingItem->value = $keyValue;
                            $settingItem->save();
                        } else {
                            //set $keyValue
                            $keyValue = $settingItem->value;
                        }
                        $emp->value = $keyValue;
                    }
                }
                $emailQueue = new EmailQueue();
                $emailQueue->setTo($emp->email, $emp->name)
                    ->setSubject($subject)
                    ->setTemplate($template, [
                        'pass' => decrypt($emp->value),
                        'emp_name' => $emp->name,
                        'employee_id' => $emp->id,
                    ]);

                $dataInsert[] = $emailQueue->getValue();
            }
            EmailQueue::insert($dataInsert);
        }
    }

    /**
     * create password and send email
     * @return type
     */
    public static function createFilePassAndSendMail()
    {
        $tblEmp = Employee::getTableName();
        $contractsExclude = [getOptions::WORKING_BORROW];
        $key = self::KEY_PASS_FILE;
        $timeNow = \Carbon\Carbon::now();
        $employees = Employee::select($tblEmp . '.id', $tblEmp . '.email', $tblEmp . '.name', 'setting.value', $tblEmp.'.join_date')
                ->leftJoin(self::getTableName() . ' as setting', function ($query) use ($tblEmp, $key) {
                    $query->on($tblEmp . '.id', '=', 'setting.employee_id')
                            ->where('setting.key', '=', $key)
                            ->where('setting.is_current', '=', 1);
                })
                ->leftJoin(EmployeeWork::getTableName() . ' as work', $tblEmp . '.id', '=', 'work.employee_id')
                ->where(function ($query) use ($contractsExclude) {
                    $query->whereNull('work.contract_type')
                            ->orWhereNotIn('work.contract_type', $contractsExclude);
                })
                ->where(function ($query) use ($tblEmp) {
                    $query->whereNull($tblEmp.'.leave_date')
                        ->orWhereRaw('DATE('. $tblEmp .'.leave_date) > CURDATE()');
                })
                ->where(DB::raw('DATE('.$tblEmp.'.join_date)'), '=', $timeNow->subDay()->toDateString())
                ->get();

        if ($employees->isEmpty()) {
            return;
        }
        DB::beginTransaction();
        try {
            self::cronSendFilePass($employees, true);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
        }
    }
}
