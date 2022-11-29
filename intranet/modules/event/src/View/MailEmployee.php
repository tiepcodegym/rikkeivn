<?php

namespace Rikkei\Event\View;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Core\View\View as CoreView;
use Illuminate\Support\Facades\DB;
use Exception;

class MailEmployee
{
    //old threshold to display old
    const OLD_THRESHOLD = 25;

    /**
     * replace content with info employee
     *
     * @param model $employee
     * @param array $arrayContent
     * @return array
     */
    public static function patternsNotiBirtday(
        $employee,
        array $arrayContent,
        &$resultReturn = null
    )
    {
        $birthday = Carbon::parse($employee->birthday);
        $old = (int)Carbon::now()->format('Y') -
            (int)$birthday->format('Y');
        if ($old < 0) {
            $old = 0;
        }
        $patterns = [
            '/\{\{\sname\s\}\}/',
            '/\{\{\semail\s\}\}/',
            '/\{\{\saccount\s\}\}/',
            '/\{\{\sold\s\}\}/',
            '/\{\{\sbirthday\s\}\}/',
        ];
        $replaces = [
            $employee->name,
            $employee->email,
            CoreView::getNickName($employee->email),
            $old,
            $birthday->format('d/m'),
        ];
        $result = [];
        foreach ($arrayContent as $key => $content) {
            $result[$key] = preg_replace($patterns, $replaces, $content);
        }
        $resultReturn = [
            'old' => $old
        ];
        return $result;
    }

    /**
     * replace content with info employee
     *
     * @param model $employee
     * @param array $arrayContent
     * @return array
     */
    public static function patternsNotiMembership(
        $employee,
        array $arrayContent,
        &$resultReturn = null
    )
    {
        $officialDate = Carbon::parse($employee->offcial_date);
        $old = (int)Carbon::now()->format('Y') -
            (int)$officialDate->format('Y');
        if ($old < 0) {
            $old = 0;
        }
        $patterns = [
            '/\{\{\sname\s\}\}/',
            '/\{\{\semail\s\}\}/',
            '/\{\{\saccount\s\}\}/',
            '/\{\{\syear\s\}\}/',
            '/\{\{\sofficial\sdate\s\}\}/',
        ];
        $replaces = [
            $employee->name,
            $employee->email,
            CoreView::getNickName($employee->email),
            $old,
            $officialDate->format('d/m/y'),
        ];
        $result = [];
        foreach ($arrayContent as $key => $content) {
            $result[$key] = preg_replace($patterns, $replaces, $content);
        }
        $resultReturn = [
            'old' => $old
        ];
        return $result;
    }

    /**
     * send all email to happy birthday
     */
    public static function sendAllEmployeeBirthday($template = 'event::employee_noti.mail.birth')
    {
        $now = Carbon::now();
        $collection = Employee::select('id', 'name', 'email', 'birthday', 'gender')
            ->whereRaw("DATE_FORMAT(birthday, '%m-%d') = '" .
                $now->format('m-d') . "'")
            ->where(function ($query) use ($now) {
                $query->orWhereNull('leave_date')
                    ->orWhereDate('leave_date', '>=', $now->format('Y-m-d'));
            })
            ->get();
        if (!count($collection)) {
            return;
        }
        $subject = CoreConfigData::getValueDb('event.mail.bitrhday.employee.subject');
        $emailQueue = new EmailQueue();
        $dataInsert = [];
        foreach ($collection as $employee) {
            $resultReturn = [];
            $dataSubject = self::patternsNotiBirtday($employee, [
                'subject' => $subject
            ], $resultReturn);
            //check threashold old and set other mail subject
            if ($employee->gender == Employee::GENDER_FEMALE && $resultReturn['old'] > self::OLD_THRESHOLD) {
                $dataSubject['subject'] = trans('event::message.mail_birthday_subject', ['name' => $employee->name]);
            }
            $emailQueue->setTo($employee->email, $employee->name)
                ->setSubject($dataSubject['subject'])
                ->setTemplate($template, [
                    // 'employee' => $employee,
                    'employee_name' => $employee->name,
                    'employee_birthday' => Carbon::parse($employee->birthday)->format('d/m')
                ]);
            $dataInsert[] = $emailQueue->getValue();
        }
        DB::beginTransaction();
        try {
            EmailQueue::insert($dataInsert);
            //set notify
            \RkNotify::put(
                $collection->lists('id')->toArray(),
                trans('event::message.mail_birthday_subject_notify'),
                null,
                ['icon' => 'employee.png', 'category_id' => RkNotify::CATEGORY_PERIODIC]
            );
            DB::commit();
            return true;
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
            return false;
        }
    }

    /**
     * send all email to happy membership
     */
    public static function sendAllEmployeeMembership()
    {
        $now = Carbon::now();
        $collection = Employee::select('id', 'name', 'email', 'birthday')
            ->whereRaw("DATE_FORMAT(birthday, '%m-%d') = '" .
                $now->format('m-d') . "'")
            ->where(function ($query) use ($now) {
                $query->orWhereNull('leave_date')
                    ->orWhereDate('leave_date', '>=', $now->format('Y-m-d'));
            })
            ->get();
        if (!count($collection)) {
            return;
        }
        $subject = CoreConfigData::getValueDb('event.mail.membership.employee.subject');
        $emailQueue = new EmailQueue();
        $dataInsert = [];
        foreach ($collection as $employee) {
            $dataSubject = self::patternsNotiMembership($employee, [
                'subject' => $subject
            ]);
            $emailQueue->setTo($employee->email, $employee->name)
                ->setSubject($dataSubject['subject'])
                ->setTemplate('event::employee_noti.mail.membership', [
                    'employee' => $employee->id
                ]);
            $dataInsert[] = $emailQueue->getValue();
            \RkNotify::put($employee->id, $dataSubject['subject'], null, ['actor_id' => null, 'icon' => 'employee.png', 'category_id' => RkNotify::CATEGORY_PERIODIC]);
        }
        return EmailQueue::insert($dataInsert);
    }

    /**
     *
     * @param type $aryEmails
     * @return collection
     */
    public static function getEmpWithLeaderByEmails($aryEmails = [])
    {
        $empTbl = Employee::getTableName();
        $tmbTbl = TeamMember::getTableName();
        $teamTbl = Team::getTableName();
        return Employee::select(
            $empTbl . '.id',
            $empTbl . '.email',
            DB::raw('GROUP_CONCAT(ld.id ORDER BY ld.id ASC) as ld_id'),
            DB::raw('GROUP_CONCAT(ld.email ORDER BY ld.id ASC) as ld_email'),
            DB::raw('GROUP_CONCAT(ld.name ORDER BY ld.id ASC) as ld_name'),
            'tmb.team_id',
            'team_ld.name as team_name'
        )
            ->join($tmbTbl . ' as tmb', 'tmb.employee_id', '=', $empTbl . '.id')
            ->join($teamTbl . ' as team', 'team.id', '=', 'tmb.team_id')
            ->leftJoin($tmbTbl . ' as tmb_ld', function ($join) {
                $join->on('tmb_ld.team_id', '=', 'tmb.team_id');
            })
            ->leftJoin($empTbl . ' as ld', function ($join) {
                $join->on('ld.id', '=', 'tmb_ld.employee_id')
                    ->where('tmb_ld.role_id', '=', Team::ROLE_TEAM_LEADER);
            })
            ->leftJoin($teamTbl . ' as team_ld', 'team_ld.id', '=', 'tmb_ld.team_id')
            ->whereIn($empTbl . '.email', $aryEmails)
            ->groupBy('tmb.team_id', 'tmb.employee_id')
            ->get();
    }

    /**
     * cron send email special date
     * @throws Exception
     */
    public static function mailSpecialDate()
    {
        $data = Employee::getListSpecialDate();
        $patternsArray = [
            '/\{\{\sname\s\}\}/',
            '/\{\{\stime\s\}\}/',
            '/\{\{\syear\s\}\}/',
        ];
        $dataInsert = [];
        Log::useFiles(storage_path() . '/logs/mailLog.log');
        foreach ($data as $item) {
            if (isset($item['email']) && $item['email']) {
                $emailQueue = new EmailQueue();
                $year = Carbon::now()->format('Y') - Carbon::parse($item['date'])->format('Y');
                $replacesArray = [
                    $item['name'],
                    Carbon::now()->format('Y-m-d'),
                    $year,
                ];

                $emailQueue->setTo($item['email'], $item['name'])
                    ->setFrom(config('mail.special_date_mail'))
                    ->addBcc('hungnt2@rikkeisoft.com')
                    ->setSubject('Happy Special Day ' . $item['name'])
                    ->setTemplate('event::eventday.special-date', [
                        'content' => preg_replace(
                            $patternsArray,
                            $replacesArray,
                            trans('event::view.Special date content')
                        )
                    ]);
                $dataInsert[] = $emailQueue->getValue();
                Log::info($item['email'] . ' at: ' . Carbon::now()->format('Y-m-d h:i:s'));
            }
        }

        DB::beginTransaction();
        try {
            EmailQueue::insert($dataInsert);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::error($ex);
        }
    }

    public static function sendMailHRday($template = 'core::emails.11_HRday')
    {
        $now = Carbon::now();
            $collection = Employee::select('id', 'name', 'email', 'birthday', 'gender')
                ->whereRaw("DATE_FORMAT(birthday, '%m-%d') = '" .
                    $now->format('m-d') . "'")
                ->where(function ($query) use ($now) {
                    $query->orWhereNull('leave_date')
                        ->orWhereDate('leave_date', '>=', $now->format('Y-m-d'));
                })
                ->get();
            if (!count($collection)) {
                return;
            }
            $subject = CoreConfigData::getValueDb('event.mail.bitrhday.employee.subject');
            $emailQueue = new EmailQueue();
            $dataInsert = [];
            foreach ($collection as $employee) {

                $emailQueue->setTo('tiepnv@rikkeisoft.com', $employee->name)
                    ->setSubject('Chúc mừng Rikkei HR\'s day')
                    ->setTemplate($template, [
                        // 'employee' => $employee,
                        'employee_name' => "tiepnv",
                        'employee_birthday' => Carbon::parse($now)->format('d/m')
                    ]);
                $dataInsert[] = $emailQueue->getValue();
            }
            DB::beginTransaction();
            try {
                EmailQueue::insert($dataInsert);
                //set notify
                \RkNotify::put(
                    $collection->lists('id')->toArray(),
                    trans('event::message.mail_birthday_subject_notify'),
                    null,
                    ['icon' => 'employee.png', 'category_id' => RkNotify::CATEGORY_PERIODIC]
                );
                DB::commit();
                return true;
            } catch (\Exception $ex) {
                DB::rollback();
                \Log::info($ex);
                return false;
            }
    }
}
