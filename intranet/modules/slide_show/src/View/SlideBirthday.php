<?php

namespace Rikkei\SlideShow\View;

use Carbon\Carbon;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\SlideShow\Model\Slide;
use Rikkei\SlideShow\Model\SlideBirthday as SlideBirthdayModel;
use Rikkei\SlideShow\Http\Requests\AddSlideRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Lang;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\Model\Team;

class SlideBirthday
{
    /**
     * replace content with info employee
     * 
     * @param model $employee
     * @param array $arrayContent
     * @return array
     */
    public static function partternSlideBirthday( $employee, $content ) {
        $birthday = Carbon::parse($employee->birthday);
        $old = (int) Carbon::now()->format('Y') - 
            (int) $birthday->format('Y');
        if ($old < 0) {
            $old = 0;
        }
        $teams = Team::getTeamForEmployee($employee->id);
        $teamArrayName = array();
        $teamString = '';
        if(count($teams)) {
            foreach($teams as $teamItem) {
                $teamArrayName[] = $teamItem['name'];
            }
            $teamString = implode(', ', $teamArrayName);
        }
        $patterns = [
            '/\{\{\sname\s\}\}/',
            '/\{\{\semail\s\}\}/',
            '/\{\{\saccount\s\}\}/',
            '/\{\{\sold\s\}\}/',
            '/\{\{\sbirthday\s\}\}/',
            '/\{\{\steam\s\}\}/',
        ];
        $replaces = [
            $employee->name,
            $employee->email,
            CoreView::getNickName($employee->email),
            $old,
            $birthday->format('d/m'),
            $teamString,
        ];
        $result = preg_replace($patterns, $replaces, $content);
        return $result;
    }

    /**
     * get employees having birthday today
     */
    public static function getEmployeeHavingBirthday() {

        $now = Carbon::now();
        $emTable = Employee::getTableName();
        $slideBirthTable = SlideBirthdayModel::getTableName();
        $collection = Employee::whereRaw("DATE_FORMAT(birthday, '%m-%d') = '" . 
            $now->format('m-d') . "'")
            ->where(function($query) use ($now) {
                $query->orWhereNull('leave_date')
                    ->orWhereDate('leave_date', '>=', $now->format('Y-m-d'));
            })
            ->join("{$slideBirthTable}", "{$emTable}.id", '=', "{$slideBirthTable}.employee_id")
            ->whereRaw("Date({$slideBirthTable}.created_at) = CURDATE()")
            ->select("{$emTable}.id as employee_id ", "{$emTable}.name", "{$emTable}.email",
                    "{$emTable}.birthday", "{$emTable}.nickname", 
                    "{$slideBirthTable}.id as slideBrithday_id", "{$slideBirthTable}.content")
            ->get();
        if (!count($collection)) {
            return false;
        } else {
            return $collection;
        }
    }
    /**
     * send all email to happy birthday
     */
    public static function addBirthdaySlide()
    {
        $now = Carbon::now();
        $collection = self::getEmployeeHavingBirthday();
        $collection = Employee::select('id', 'name', 'email', 'birthday')
            ->whereRaw("DATE_FORMAT(birthday, '%m-%d') = '" . 
                $now->format('m-d') . "'")
            ->where(function($query) use ($now) {
                $query->orWhereNull('leave_date')
                    ->orWhereDate('leave_date', '>=', $now->format('Y-m-d'));
            })
            ->get();
        if (!count($collection)) {
            return;
        } 
        $titleConfig = CoreConfigData::getValueDb(Slide::BIRTHDAY_CONFIG_TITLE);
        $contentConfig = CoreConfigData::getValueDb(Slide::BIRTHDAY_CONFIG_DATA);
        // insert slide at 7:40
        $dataSlide = [
            'title' => ($titleConfig)? $titleConfig : Lang::get('slide_show::view.Happy birthday'),
            'date' => $now->format('Y-m-d'),
            'hour_start' => Slide::BIRTHDAY_SHOW_HOUR[0][0],
            'hour_end' => Slide::BIRTHDAY_SHOW_HOUR[0][1],
            'option' => Slide::OPTION_BIRTHDAY,
        ];

        $validFirstSlide = AddSlideRequest::validateData($dataSlide);
        if ($validFirstSlide->fails()) {
            Log::info($validFirstSlide->errors());
            return ;
        }
        DB::beginTransaction();
        try {
            $slide = Slide::create($dataSlide);
            $dataInsert = array();
            foreach ($collection as $employee) {
                $singleArray = array();
                $singleArray['slide_id'] = $slide->id;
                $singleArray['employee_id'] = $employee->id;
                $singleArray['content'] = self::partternSlideBirthday($employee, $contentConfig);
                $singleArray['avatar'] = str_replace("?sz=50", "?sz=308", Employee::getAvatar($employee->id));
                $singleArray['created_at'] = $now;
                $singleArray['updated_at'] = $now;
                $dataInsert[] = $singleArray;
            }
            SlideBirthdayModel::insert($dataInsert);
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
        // insert slide at 11:40
        $nextDataSlide = [
            'title' => ($titleConfig)? $titleConfig : Lang::get('slide_show::view.Happy birthday'),
            'date' => $now->format('Y-m-d'),
            'hour_start' => Slide::BIRTHDAY_SHOW_HOUR[1][0],
            'hour_end' => Slide::BIRTHDAY_SHOW_HOUR[1][1],
            'option' => Slide::OPTION_BIRTHDAY,
        ];

        $validNextSlide = AddSlideRequest::validateData($nextDataSlide);
        if ($validNextSlide->fails()) {
            Log::info($validNextSlide->errors());
            return ;
        }
        DB::beginTransaction();
        try {
            $nextSlide = Slide::create($nextDataSlide);
            $dataInsert = array();
            foreach ($collection as $employee) {
                $singleArray = array();
                $singleArray['slide_id'] = $nextSlide->id;
                $singleArray['employee_id'] = $employee->id;
                $singleArray['content'] = self::partternSlideBirthday($employee, $contentConfig);
                $singleArray['avatar'] = str_replace("?sz=50", "?sz=308", Employee::getAvatar($employee->id));
                $singleArray['created_at'] = $now;
                $singleArray['updated_at'] = $now;
                $dataInsert[] = $singleArray;
            }
            SlideBirthdayModel::insert($dataInsert);
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
        
    }

    
}
