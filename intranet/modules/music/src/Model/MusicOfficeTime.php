<?php

namespace Rikkei\Music\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Permission;
use Illuminate\Support\Facades\DB;
use Rikkei\Music\Model\MusicOffice;


class MusicOfficeTime extends CoreModel
{
    protected $table = 'music_office_time';

    /**
    * save order time of office
    */ 
    public static function saveTime($officeId, array $options = []){
        self::where('music_office_id','=',$officeId)->delete();
        for($i = 0; $i < count($options); $i++) {
            self::insert(['music_office_id'=>$officeId, 'time'=>$options[$i]]);
        }
    }

    /**
    * get time of office follow id
    */
    public static function getTimeOfOffice($officeId) 
    {
        return self::select('time')->where('music_office_id','=',$officeId)->get();
    }

    public static function getInforEmail($time) 
    {
        $officeTimeTable = self::getTableName();
        return self::join('music_offices', $officeTimeTable.'.music_office_id', '=', 'music_offices.id')
                    ->join('employees', 'music_offices.employee_noti','=','employees.id')
                    ->where($officeTimeTable.'.time','=',$time)
                    ->whereNull('music_offices.deleted_at')
                    ->where('music_offices.status','=',MusicOffice::ENABLE_STATUS)
                    ->select(
                        'music_offices.id',
                        'employees.email',
                        'employees.name as employee_name',
                        'employees.id as employee_id'
                    )
                    ->limit(10)
                    ->get();
    }
}