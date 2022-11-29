<?php

namespace Rikkei\AdminSetting\Model;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\View\Config;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminDivision extends CoreModel
{
    use SoftDeletes;
    protected $table = 'admin_division';

    /**
     * get collection to show grid data
     * @return collection model
     */
    // $collection->WHERE deleted_at IS NULL (SoftDeletes)
    public static function getGridData()
    {
        $pager = Config::getPagerData();
        $collection = AdminDivision::groupBy('division');
        $collection->orderBy($pager['order'], $pager['dir']);
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
     * get model by division
     * @return array
     */
    public static function getByDivision($division, $getArrDivision = false)
    {
        $result = self::where('division', '=', $division)->first();
        if ($getArrDivision && isset($result)) {
            return explode(',', str_replace(['{', '}'], '', $result['admin']));
        }
        return $result;
    }

    /**
     * get employee have birthday today
     */
    public static function getEmployeeHasBirthdayInDay()
    {
        $day = Carbon::now()->day;
        $month = Carbon::now()->month;
        $currentDay = date("Y-m-d");
        $collection = AdminDivision::join('team_members as tm', 'admin_division.division', '=', 'tm.team_id')
                        ->join('employees as emp', 'tm.employee_id', '=', 'emp.id')
                        ->where(function ($query) use ($currentDay) {
                            $query->whereDate('emp.leave_date', '>=', $currentDay)
                                ->orWhereNull('emp.leave_date');
                        })
                        ->whereNull('emp.deleted_at')
                        ->select('admin_division.*', 'emp.id as empId', 'emp.name', 'emp.email', 'emp.birthday', 'emp.leave_date')
                        ->get();
        foreach ($collection as $key => $value) {
            if (empty($value->birthday) || Carbon::parse($value->birthday)->day !== $day || Carbon::parse($value->birthday)->month !== $month) {
                unset($collection[$key]);
            }
        }
        return $collection;
    }

    /**
     * Send mail notify admin staff birthday
     */
    public static function sendMailNotifyAdminEmpBirthday($admins, $val)
    {
        foreach ($admins as $ad) {
            $employee = Employee::getEmpIsWorking($ad);
            if (isset($employee)) {
                $data = [
                    'admin' => Employee::getEmpIsWorking($ad)->name,
                    'employees' => $val
                ];
                $emailQueue = new EmailQueue();
                $subject = trans('admin_setting::view.title');
                $emailQueue->setTo(Employee::getEmpIsWorking($ad)->email, Employee::getEmpIsWorking($ad)->name)
                    ->setTemplate('team::mail.notification_emp_birthday', $data)
                    ->setSubject($subject)
                    ->save();
            }
        }
    }

    public static function getById($id)
    {
        return self::find($id);
    }
}
