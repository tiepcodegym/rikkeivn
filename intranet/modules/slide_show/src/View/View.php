<?php

namespace Rikkei\SlideShow\View;
use Carbon\Carbon;
use Rikkei\SlideShow\Model\Repeat;
use Rikkei\Core\Model\CoreConfigData;

/**
 * View ouput gender
 */
class View
{
    /**
     * Check hours of time frame
     * @param string
     * @param string
     * @return boolean
     */
    public static function checkHourSlide($hour, $slide, $allTypeRepeat)
    {
        $response = [];
        $response['result'] = false;
        $response['isMainSlider'] = false;
        $arrayHour = explode(":",$hour);
        $arrayHourStart = explode(":",$slide['hour_start']);
        $timeHourForm =  Carbon::createFromTime($arrayHour[0], $arrayHour[1]);
        $timeHourTo =  $timeHourForm->addHour(1);
        $timeHourForm =  Carbon::createFromTime($arrayHour[0], $arrayHour[1]);
        $timeHourStart =  Carbon::createFromTime($arrayHourStart[0], $arrayHourStart[1]);
        if ($timeHourForm <= $timeHourStart
            && $timeHourTo > $timeHourStart ) {
            $response['result'] = true;
            $response['isMainSlider'] = true;
            return $response;
        }
        if (in_array(Repeat::TYPE_REPEAT_HOURLY, $allTypeRepeat)) {
            $timeHourToNew =  $timeHourForm->addHour(1);
            $timeHourFormNew =  Carbon::createFromTime($arrayHour[0], $arrayHour[1]);
            if ($timeHourFormNew >= $timeHourStart
                && $timeHourToNew > $timeHourStart) {
                $response['result'] = true;
                return $response;
            }
        }
        return $response;
    }

    /**
     * get time and of slide
     * @param array
     * @return object
     */
    public static function getTimeEndSlide($slide)
    {
        $dateNow = date("Y-m-d");
        $arrayDateNow = explode("-", $dateNow);
        if (!$slide) {
            $hourNow = date("H:i");
            $dateTimeNow = Carbon::create($arrayDateNow[0], $arrayDateNow[1], $arrayDateNow[2]);
            $arrayHourNow = explode(":", $hourNow);
            $minute = (int)((int)$arrayHourNow[1]/10);
            if($minute == 5) {
                $minute = 0;
                if ($arrayHourNow[0] == 23) {
                    $hourEnd = 0;
                    $dateTimeNow = $dateTimeNow->addDay(1);
                } else {
                    $hourEnd = (int)$arrayHourNow[0] + 1;
                }
            } else {
                $minute = ($minute + 1)*10;
                $hourEnd = (int)$arrayHourNow[0];
            }
            $dateTimeNow = Carbon::parse($dateTimeNow)->format('Y-m-d');
            $arrayDateNow = explode("-", $dateTimeNow);
            $dateTimeEnd = Carbon::create($arrayDateNow[0], $arrayDateNow[1], $arrayDateNow[2], $hourEnd, $minute);
        } else {
            $hourNow = date("H:i");
            $arrayhourNow = explode(":", $hourNow);
            if((int)$arrayhourNow[1] >= 50) {
                $arrayhourNow[0] = (int)$arrayhourNow[0] + 1;
            }
            $hour = $arrayhourNow[0];
            $hourEnd = $slide->hour_end;
            $arrayhourEnd = explode(":", $hourEnd);
            $dateTimeEnd = Carbon::create($arrayDateNow[0], $arrayDateNow[1], $arrayDateNow[2], $hour, $arrayhourEnd[1]);
        }
        return $dateTimeEnd;
    }

    /**
     * get second play video
     * @param array
     * @return int
     */
    public static function getSecondPlayVideo($slide)
    {
        $timeEnd = self::getTimeEndSlide($slide);
        $timeNow = Carbon::now();
        return $timeEnd->diffInSeconds($timeNow);
    }

    /*
     * check allow repeat hourly
     */
    public static function checkAllowRepeatHourly($data)
    {
        $arrayHourStart = explode(":", $data['hour_start']);
        $arrayHourEnd = explode(":", $data['hour_end']);
        if ((int)$arrayHourStart[0] < (int)$arrayHourEnd[0] && (int)$arrayHourEnd[1] > 0) {
            return true;
        }
        return false;
    }

    /**
     * generate url video youtube
     * @param int
     * @return string
     */
    public static function urlVideoYoutube($idVideo)
    {
        return "https://www.youtube.com/embed/".$idVideo."?playlist=".$idVideo."&loop=1&autoplay=1&cc_load_policy=1&rel=0&amp;controls=0&amp;showinfo=0&vq=hd1080";   
    }

    /*
     * 
     */
    public static function generateHourLoop($slide, $hourIndex, $allTypeRepeat)
    {
        $arrayHourStart  = explode(":", $slide['hour_start']);
        $arrayHourEnd  = explode(":", $slide['hour_end']);
        $arrayHourIndex  = explode(":", $hourIndex);
        if (in_array(Repeat::TYPE_REPEAT_HOURLY, $allTypeRepeat)) {
            $arrayHourIndexTo = $arrayHourIndex[0];
            if ((int)$arrayHourEnd[1] == 0) {
                $arrayHourIndexTo = (int)$arrayHourIndexTo + 1;
            }
            return $arrayHourIndex[0] . ":" . $arrayHourStart[1] .' - '. $arrayHourIndexTo . ":" . $arrayHourEnd[1];
        } else {
            return $slide['hour_start'] .' - '. $slide['hour_end'];
        }
    }

    /**
     * get second to brithday
     * @return int
     */
    public static function getSecondToBirthday()
    {
        $birthday = CoreConfigData::where('key', 'slide_show.birthday_company')->first();
        if (!$birthday) {
            return 0;
        }
        $birthday = Carbon::parse($birthday->value);
        $timeNow = Carbon::now();
        if ($birthday > $timeNow) {
            return $birthday->diffInSeconds($timeNow);
        }
        return 0;
    }

    /**
     * check display birthday
     * 
     * @return boolean
     */
    public static function checkDisplayBirthday()
    {
        $birthday = CoreConfigData::getValueDb('slide_show.birthday_company');
        if (!$birthday) {
            return null;
        }
        $birthday = Carbon::parse($birthday);
        $timeNow = Carbon::now();
        $birthday = $birthday->modify('+7 day');
        if ($birthday > $timeNow) {
            return true;
        }
        return false;
    }
}