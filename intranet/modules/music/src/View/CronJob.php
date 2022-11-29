<?php

namespace Rikkei\Music\View;

use Rikkei\Music\Model\MusicOrder;
use Rikkei\Music\Model\MusicOfficeTime;
use Rikkei\Core\Model\CoreConfigData;
use Mail;
use Carbon\Carbon;


class CronJob
{
    /**
    * send email
    */ 
    public static function sendNoti() 
    {
        $now = Carbon::now();
        $time = clone $now;
        $timeString = $time->addMinutes(5)->format('H:i');
        if(!(self::notHoliday($now) && 
            $now->gte(Carbon::now()->hour(07)->minute(55)) &&
            $now->lte(Carbon::now()->hour(17)->minute(25)))
        ) {
            return;
        }

        $offices = MusicOfficeTime::getInforEmail($timeString);

        if(!count($offices)) {
            return;
        }
        libxml_use_internal_errors(true);
        foreach($offices as $office) {
            Mail::send('music::manage.office.email_noti',['officeId'=>($office->id),'name'=>($office->employee_name),'time'=>$timeString], function($message) use($office){
                $message->to($office->email)->subject(trans('music::view.Play music noti'));
                $message->from('rikkeihanoi@gmail.com','Rikkeisoft');
            });
        }
        //set notify
        $groupEmps = $offices->groupBy('employee_id');
        foreach ($groupEmps as $empId => $groupOffices) {
            $existsOffices = [];
            foreach ($groupOffices as $item) {
                if (!in_array($item->id, $existsOffices)) {
                    $existsOffices[] = $item->id;
                    \RkNotify::put(
                        $empId,
                        trans('music::view.Play music noti'),
                        route('music::order.office', $item->id),
                        ['actor_id' => null]
                    );
                }
            }
        }
    }

    /**
    * check holiday
    */ 
    public static function notHoliday($time) 
    {
        $anualHoliday = CoreConfigData::getHoliday(CoreConfigData::getAnnualHolidays(), $type = 2);
        $specialHoliday = CoreConfigData::getHoliday(CoreConfigData::getSpecialHolidays());
        $holydays = array_merge($anualHoliday, $specialHoliday);

        $dateString = $time->toDateString();
        $dateMonth = substr($dateString, 5);
        if(in_array($dateString, $holydays)||in_array($dateMonth, $holydays)){
            return false;
        }
        return true;
    }
}
