<?php 
namespace Rikkei\SlideShow\Services;

use Illuminate\Validation\Validator;
use Rikkei\SlideShow\Model\Repeat;
use Rikkei\SlideShow\Model\Slide;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Lang;

class CustomValidator extends Validator
{
    /**
     * validate repeat slider
     * @param   $attribute  
     * @param   $value      
     * @param   $parameters 
     * @return  boolean          
     */
    public function validateRepeatSlide($attribute, $value, $parameters)
    {
        $allTypeRepeat = Repeat::getAllTypeRepeat();
        foreach ($value as $type) {
            if(!in_array($type, array_keys($allTypeRepeat))) {
                return false;
            }
        }
        return true;
    }

    /**
     * validate type slider
     * @param   $attribute  
     * @param   $value      
     * @param   $parameters 
     * @return  boolean          
     */
    public function validateTypeSlide($attribute, $value, $parameters)
    {
        $allTypeSlide = Slide::getAllTypeSlide();
        if(!in_array($value, array_keys($allTypeSlide))) {
            return false;
        }
        return true;
    }

     /**
     * validate unique hour start
     * @param   $attribute  
     * @param   $value      
     * @param   $parameters 
     * @return  boolean          
     */
    public function validateUniqueHourStart($attribute, $value, $parameters)
    {
        if (Session::has('messageUniqueHour')) {
            Session::forget('messageUniqueHour');
        }
        $slides = Slide::where('date', $parameters[0]);
        if ($parameters[1]) {
            $slides->whereNotIn('id', [$parameters[1]]);
        }
        $slides = $slides->get();
        $arrayHour = explode(":",$value);
        $timeHourValue =  Carbon::createFromTime($arrayHour[0], $arrayHour[1]);
        $arrayHourTo = explode(":",$parameters[2]);
        $timeHourTo =  Carbon::createFromTime($arrayHourTo[0], $arrayHourTo[1]);

        $allHour = Slide::getHour();
        end($allHour);
        $hourLast = key($allHour);
        $arrayHourLast =  explode(":", $hourLast);
        $timeHourLast =  Carbon::createFromTime($arrayHourLast[0], $arrayHourLast[1]);

        $allDayOfWeek = Repeat::getLabelDayOfWeek();

        foreach ($slides as $key => $slide) {
            $arrayHourStart = explode(":",$slide->hour_start);
            $arrayhourEnd = explode(":",$slide->hour_end);
            $timeHourStart =  Carbon::createFromTime($arrayHourStart[0], $arrayHourStart[1]);
            $timeHourEnd =  Carbon::createFromTime($arrayhourEnd[0], $arrayhourEnd[1]);
            if ($timeHourValue > $timeHourStart && $timeHourValue < $timeHourEnd ||
                $timeHourTo > $timeHourStart && $timeHourTo < $timeHourEnd ||
                $timeHourValue < $timeHourStart && $timeHourTo > $timeHourEnd ||
                $timeHourValue == $timeHourStart || $timeHourTo == $timeHourEnd) {
                $message = Lang::get('slide_show::message.There has been slide show in') . ' ' .date('Y-m-d', strtotime($slide->date)).' ('. $slide->hour_start. ' - ' . $slide->hour_end . ')' ;
                Session::push('messageUniqueHour', $message);
                return false;
            }
            // check repeat hour
            $repeatHourly = Repeat::where('slide_id', $slide->id)
                                    ->where('type', Repeat::TYPE_REPEAT_HOURLY)
                                    ->first();
            
            if($repeatHourly) {
                do {
                    $hourNextStart = $timeHourStart->addHour(1);
                    $hourNextEnd = $timeHourEnd->addHour(1);
                    if ($timeHourValue > $hourNextStart && $timeHourValue < $hourNextEnd ||
                        $timeHourTo > $hourNextStart && $timeHourTo < $hourNextEnd ||
                        $timeHourValue < $hourNextStart && $timeHourTo > $hourNextEnd ||
                        $timeHourValue == $hourNextStart || $timeHourTo == $hourNextEnd) {
                        $message = Lang::get('slide_show::message.There has been slide show in') . ' ' .date('Y-m-d', strtotime($slide->date)).' ('. $slide->hour_start. ' - ' . $slide->hour_end . ') '. Lang::get('slide_show::view.repeat hourly');
                        Session::push('messageUniqueHour', $message);
                        return false;
                        break;
                    }
                } while ($timeHourLast >= $hourNextEnd) ;
            }
        }

        if ($parameters[1]) {
            $allSildes = Slide::whereNotIn('id', [$parameters[1]]) ->get();
        } else {
            $allSildes = Slide::all();
        }
        $arrayDateInput = explode("-", $parameters[0]);
        $dateTimeInput = Carbon::create($arrayDateInput[0], $arrayDateInput[1], $arrayDateInput[2]);
        foreach ($allSildes as $key => $slide) {
            $repeatDaily = Repeat::where('slide_id', $slide->id)
                                    ->where('type', Repeat::TYPE_REPEAT_DAILY)
                                    ->first();
            // check repeat daily
            if ($repeatDaily) {
                if ($slide->date < $parameters[0]) {
                    $arrayHourStart = explode(":",$slide->hour_start);
                    $arrayhourEnd = explode(":",$slide->hour_end);
                    $timeHourStart =  Carbon::createFromTime($arrayHourStart[0], $arrayHourStart[1]);
                    $timeHourEnd =  Carbon::createFromTime($arrayhourEnd[0], $arrayhourEnd[1]);
                    if ($timeHourValue > $timeHourStart && $timeHourValue < $timeHourEnd ||
                    $timeHourTo > $timeHourStart && $timeHourTo < $timeHourEnd ||
                    $timeHourValue < $timeHourStart && $timeHourTo > $timeHourEnd ||
                    $timeHourValue == $timeHourStart || $timeHourTo == $timeHourEnd) {
                        $message = Lang::get('slide_show::message.There has been slide show in') . ' ' .date('Y-m-d', strtotime($slide->date)).' ('. $slide->hour_start. ' - ' . $slide->hour_end . ') '. Lang::get('slide_show::view.repeat daily');
                        Session::push('messageUniqueHour', $message);
                        return false;
                    }
                }
            }
            // check repeat weekly
            $repeatWeekly = Repeat::where('slide_id', $slide->id)
                                    ->where('type', Repeat::TYPE_REPEAT_WEEKLY)
                                    ->first();
            if ($repeatWeekly) {
                if ($slide->date < $parameters[0]) {
                    $arrayDay = explode("-", date_format(date_create($slide->date), 'Y-m-d'));
                    $dateTime = Carbon::create($arrayDay[0], $arrayDay[1], $arrayDay[2]);
                    if ($dateTime->dayOfWeek == $dateTimeInput->dayOfWeek) {
                        $arrayHourStart = explode(":",$slide->hour_start);
                        $arrayhourEnd = explode(":",$slide->hour_end);
                        $timeHourStart =  Carbon::createFromTime($arrayHourStart[0], $arrayHourStart[1]);
                        $timeHourEnd =  Carbon::createFromTime($arrayhourEnd[0], $arrayhourEnd[1]);
                        if ($timeHourValue > $timeHourStart && $timeHourValue < $timeHourEnd ||
                        $timeHourTo > $timeHourStart && $timeHourTo < $timeHourEnd ||
                        $timeHourValue < $timeHourStart && $timeHourTo > $timeHourEnd ||
                        $timeHourValue == $timeHourStart || $timeHourTo == $timeHourEnd) {
                            $message = Lang::get('slide_show::message.There has been slide show in') . ' ' .date('Y-m-d', strtotime($slide->date)).' ('. $slide->hour_start. ' - ' . $slide->hour_end . ') '. Lang::get('slide_show::view.repeat'). ' ' . Lang::get('slide_show::view.each') . ' ' .$allDayOfWeek[$dateTime->dayOfWeek] . ' ' .Lang::get('slide_show::view.weekly') ;
                            Session::push('messageUniqueHour', $message);
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * validate unique hour start
     * @param   $attribute  
     * @param   $value      
     * @param   $parameters 
     * @return  boolean          
     */
    public function validateUniqueRepeat($attribute, $value, $parameters)
    {
        if (Session::has('messageUniqueRepeat')) {
            Session::forget('messageUniqueRepeat');
        }
        $slides = Slide::where('date', $parameters[0]);
        if ($parameters[1]) {
            $slides->whereNotIn('id', [$parameters[1]]);
        }
        $slides = $slides->get();
        $arrayHour = explode(":",$parameters[2]);
        $timeHourValue =  Carbon::createFromTime($arrayHour[0], $arrayHour[1]);
        $arrayHourTo = explode(":",$parameters[3]);
        $timeHourTo =  Carbon::createFromTime($arrayHourTo[0], $arrayHourTo[1]);

        $allHour = Slide::getHour();
        end($allHour);
        $hourLast = key($allHour);
        $arrayHourLast =  explode(":", $hourLast);
        $timeHourLast =  Carbon::createFromTime($arrayHourLast[0], $arrayHourLast[1]);
        foreach ($slides as $key => $slide) {
            foreach($value as $key => $repeat) {
                // validate repeat hourly
                if ($repeat == Repeat::TYPE_REPEAT_HOURLY) {
                    $arrayHourStart = explode(":",$slide->hour_start);
                    $arrayhourEnd = explode(":",$slide->hour_end);
                    $timeHourStart =  Carbon::createFromTime($arrayHourStart[0], $arrayHourStart[1]);
                    $timeHourEnd =  Carbon::createFromTime($arrayhourEnd[0], $arrayhourEnd[1]);
                    
                    do {
                        $hourNextStart = $timeHourValue->addHour(1);
                        $hourNextEnd = $timeHourTo->addHour(1);
                        if ($timeHourStart > $hourNextStart && $timeHourStart < $hourNextEnd ||
                            $timeHourEnd > $hourNextStart && $timeHourEnd < $hourNextEnd ||
                            $timeHourStart < $hourNextStart && $timeHourEnd > $hourNextEnd ||
                            $timeHourStart == $hourNextStart || $timeHourEnd == $hourNextEnd) {
                            
                            $message = Lang::get('slide_show::message.Do not allow repeat'). ' ' . $parameters[2]. ' - ' .$parameters[3].' '.Lang::get('slide_show::view.hourly').'. '. Lang::get('slide_show::message.There has been slide show in'). ' ' . $slide->hour_start. ' - ' . $slide->hour_end;
                            Session::push('messageUniqueRepeat', $message);
                            return false;
                            break;
                        }
                    } while ($timeHourLast >= $hourNextEnd) ;
                }
            }
        }
        if ($parameters[1]) {
            $allSildes = Slide::whereNotIn('id', [$parameters[1]]) ->get();
        } else {
            $allSildes = Slide::all();
        }
        $arrayHour = explode(":",$parameters[2]);
        $timeHourValue =  Carbon::createFromTime($arrayHour[0], $arrayHour[1]);
        $arrayHourTo = explode(":",$parameters[3]);
        $timeHourTo =  Carbon::createFromTime($arrayHourTo[0], $arrayHourTo[1]);
        $arrayDateInput = explode("-", $parameters[0]);
        $dateTimeInput = Carbon::create($arrayDateInput[0], $arrayDateInput[1], $arrayDateInput[2]);
        $allDayOfWeek = Repeat::getLabelDayOfWeek();

        foreach($value as $key => $repeat) {
            if ($repeat == Repeat::TYPE_REPEAT_DAILY) {
                foreach ($allSildes as $key => $slide) {
                // validate repeat daily
                    if ($slide->date > $parameters[0]) {
                        $repeatHourly = Repeat::where('slide_id', $slide->id)
                                    ->where('type', Repeat::TYPE_REPEAT_HOURLY)
                                    ->first();
                        if ($repeatHourly) {
                            $arrayHourStart = explode(":",$slide->hour_start);
                            $arrayhourEnd = explode(":",$slide->hour_end);
                            $timeHourStart =  Carbon::createFromTime($arrayHourStart[0], $arrayHourStart[1]);
                            $timeHourEnd =  Carbon::createFromTime($arrayhourEnd[0], $arrayhourEnd[1]);
                            do {
                                $hourNextStart = $timeHourStart->addHour(1);
                                $hourNextEnd = $timeHourEnd->addHour(1);
                                if ($timeHourValue > $hourNextStart && $timeHourValue < $hourNextEnd ||
                                    $timeHourTo > $hourNextStart && $timeHourTo < $hourNextEnd ||
                                    $timeHourValue < $hourNextStart && $timeHourTo > $hourNextEnd ||
                                    $timeHourValue == $hourNextStart || $timeHourTo == $hourNextEnd) {
                                    
                                    $message = Lang::get('slide_show::message.Do not allow repeat'). ' ' . $parameters[2]. ' - ' .$parameters[3].'  '.Lang::get('slide_show::view.daily').'. '. Lang::get('slide_show::message.There has been slide show in'). ' ' . date('Y-m-d', strtotime($slide->date)).' ('. $slide->hour_start. ' - ' . $slide->hour_end . ') '. Lang::get('slide_show::message.repeat hourly');
                                    Session::push('messageUniqueRepeat', $message);
                                    return false;
                                    break;
                                }
                            } while ($timeHourLast >= $hourNextEnd) ;
                        } else {
                            $arrayHourStart = explode(":",$slide->hour_start);
                            $arrayhourEnd = explode(":",$slide->hour_end);
                            $timeHourStart =  Carbon::createFromTime($arrayHourStart[0], $arrayHourStart[1]);
                            $timeHourEnd =  Carbon::createFromTime($arrayhourEnd[0], $arrayhourEnd[1]);
                            if ($timeHourValue > $timeHourStart && $timeHourValue < $timeHourEnd ||
                            $timeHourTo > $timeHourStart && $timeHourTo < $timeHourEnd ||
                            $timeHourValue < $timeHourStart && $timeHourTo > $timeHourEnd ||
                            $timeHourValue == $timeHourStart || $timeHourTo == $timeHourEnd) {
                                $message = Lang::get('slide_show::message.Do not allow repeat'). ' ' . $parameters[2]. ' - ' .$parameters[3].' '.Lang::get('slide_show::view.daily').'. '. Lang::get('slide_show::message.There has been slide show in'). ' ' . date('Y-m-d', strtotime($slide->date)).' ('. $slide->hour_start. ' - ' . $slide->hour_end . ')';
                                Session::push('messageUniqueRepeat', $message);
                                return false;
                            }
                        }
                    }
                }
            }

            // validate repeat weekly
            if ($repeat == Repeat::TYPE_REPEAT_WEEKLY) {
                foreach ($allSildes as $key => $slide) {
                    if ($slide->date > $parameters[0]) {
                        $arrayDay = explode("-", date_format(date_create($slide->date), 'Y-m-d'));
                        $dateTime = Carbon::create($arrayDay[0], $arrayDay[1], $arrayDay[2]);
                        if ($dateTime->dayOfWeek == $dateTimeInput->dayOfWeek) {
                            $repeatHourly = Repeat::where('slide_id', $slide->id)
                                    ->where('type', Repeat::TYPE_REPEAT_HOURLY)
                                    ->first();
                            if ($repeatHourly) {
                                $arrayHourStart = explode(":",$slide->hour_start);
                                $arrayhourEnd = explode(":",$slide->hour_end);
                                $timeHourStart =  Carbon::createFromTime($arrayHourStart[0], $arrayHourStart[1]);
                                $timeHourEnd =  Carbon::createFromTime($arrayhourEnd[0], $arrayhourEnd[1]);
                                do {
                                    $hourNextStart = $timeHourStart->addHour(1);
                                    $hourNextEnd = $timeHourEnd->addHour(1);
                                    if ($timeHourValue > $hourNextStart && $timeHourValue < $hourNextEnd ||
                                        $timeHourTo > $hourNextStart && $timeHourTo < $hourNextEnd ||
                                        $timeHourValue < $hourNextStart && $timeHourTo > $hourNextEnd ||
                                        $timeHourValue == $hourNextStart || $timeHourTo == $hourNextEnd) {
                                        
                                        $message = Lang::get('slide_show::message.Do not allow repeat'). ' ' . $parameters[2]. ' - ' .$parameters[3].' '.$allDayOfWeek[$dateTime->dayOfWeek] .' '.Lang::get('slide_show::view.weekly').'. '. Lang::get('slide_show::message.There has been slide show in'). ' ' . date('Y-m-d', strtotime($slide->date)).' ('. $slide->hour_start. ' - ' . $slide->hour_end . ') '. Lang::get('slide_show::message.repeat hourly');
                                        Session::push('messageUniqueRepeat', $message);
                                        return false;
                                        break;
                                    }
                                } while ($timeHourLast >= $hourNextEnd) ;
                            } else {
                                $arrayHourStart = explode(":",$slide->hour_start);
                                $arrayhourEnd = explode(":",$slide->hour_end);
                                $timeHourStart =  Carbon::createFromTime($arrayHourStart[0], $arrayHourStart[1]);
                                $timeHourEnd =  Carbon::createFromTime($arrayhourEnd[0], $arrayhourEnd[1]);
                                if ($timeHourValue > $timeHourStart && $timeHourValue < $timeHourEnd ||
                                $timeHourTo > $timeHourStart && $timeHourTo < $timeHourEnd ||
                                $timeHourValue < $timeHourStart && $timeHourTo > $timeHourEnd ||
                                $timeHourValue == $timeHourStart || $timeHourTo == $timeHourEnd) {
                                    $message = Lang::get('slide_show::message.Do not allow repeat'). ' ' . $parameters[2]. ' - ' .$parameters[3].' '.$allDayOfWeek[$dateTime->dayOfWeek] .' '.Lang::get('slide_show::view.weekly').'. '. Lang::get('slide_show::message.There has been slide show in'). ' ' . date('Y-m-d', strtotime($slide->date)).' ('. $slide->hour_start. ' - ' . $slide->hour_end . ')';
                                    Session::push('messageUniqueRepeat', $message);
                                    return false;
                                }
                            }
                        }
                    }
                }
            }
        }
        return true;
    }
}