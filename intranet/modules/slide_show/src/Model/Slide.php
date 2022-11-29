<?php

namespace Rikkei\SlideShow\Model;
use Rikkei\Core\Model\CoreModel;
use Rikkei\SlideShow\Model\SlideBirthday;
use Rikkei\SlideShow\View\SlideBirthday as ViewSlideBirthday;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\View\View;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use Rikkei\Core\Model\CoreConfigData;
use Illuminate\Support\Facades\Session;
use Exception;

class Slide extends CoreModel
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'slide';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'date', 'hour_start', 'hour_end', 'type',
        'effect', 'font_size', 'language', 'name_customer', 'option'];
    
     /**
     * Get repeat for slide
     */
    public function repeatSlide() {
        return $this->hasOne('Rikkei\SlideShow\Model\Repeat', 'slide_id');
    }

     /**
     * Get file for slide
     */
    public function slideSlide() {
        return $this->hasOne('Rikkei\SlideShow\Model\File', 'slide_id');
    }

    const TYPE_IMAGE = 1;
    const TYPE_VIDEO = 2;
    
    const OPTION_NOMAL = 0;
    const OPTION_WELCOME = 1;
    const OPTION_QUOTATIONS = 2;
    const OPTION_BIRTHDAY = 3;
    
    const LANG_ENGLISH = 0;
    const LANG_JAPAN = 1;

    const BIRTHDAY_CONFIG_TITLE = 'slideshow.birthday.title';
    const BIRTHDAY_CONFIG_DATA = 'slideshow.birthday.content';
    const BIRTHDAY_SHOW_HOUR = [['07:40', '08:20'], [ '11:40', '12:20']];
    /**
     * get all types slide
     * @return array
     */
    public static function getAllTypeSlide()
    {
        return [
            self::TYPE_IMAGE => Lang::get('slide_show::view.Images'),
            self::TYPE_VIDEO => Lang::get('slide_show::view.Videos'),
        ];
    }

    /**
     * get all option slide
     * @return array
     */
    public static function getAllOptionSlide()
    {
        return [
            self::OPTION_NOMAL => Lang::get('slide_show::view.Nomal'),
            self::OPTION_WELCOME => Lang::get('slide_show::view.Welcome company'),
            self::OPTION_QUOTATIONS => Lang::get('slide_show::view.Quotations'),
            self::OPTION_BIRTHDAY => Lang::get('slide_show::view.Birthday'),
        ];
    }

    /**
     * get all language slide
     * @return array
     */
    public static function getAllLanguageSlide()
    {
        return [
            self::LANG_ENGLISH => Lang::get('slide_show::message.Welcome to rikkeisoft!(Eng)'),
            self::LANG_JAPAN => Lang::get('slide_show::message.Welcome to rikkeisoft!(Jp)'),
        ];
    }


    /**
     * get all hours create slide
     * @return array
     */
    public static function getHour()
    {
        return [
            '07:00' => '08:00',
            '08:00' => '09:00',
            '09:00' => '10:00',
            '10:00' => '11:00',
            '11:00' => '12:00',
            '12:00' => '13:00',
            '13:00' => '14:00',
            '14:00' => '15:00',
            '15:00' => '16:00',
            '16:00' => '17:00',
            '17:00' => '18:00',
            '18:00' => '19:00',
            '19:00' => '20:00',
        ];
    }

    /**
     * get all font size of description image
     * @return array
     */
    public static function getAllFontSize()
    {
        $allSize = [];
        for($i = 20; $i <= 50; $i++) {
            $allSize[$i]= $i;
        }
        return $allSize;
    }

    /**
     * get slider by date
     * @param array
     * @return array
     */
    public static function getSliderByDate($date)
    {
        $slides = self::where('date', $date)
                    ->orderBy('hour_start')
                    ->get()->toArray();
        // sort by minute after sort by hour
        usort($slides, function($a, $b) {
            return strcmp(explode(':', $a['hour_start'])[1], explode(':', $b['hour_start'])[1]);
        });
        return $slides;
    }

    /**
     * get slide by id
     * @param int
     * @return array
     */
    public static function getSlideById($id)
    {
        return self::find($id);
    }

    /**
     * insert slide
     * @param array
     * @return array
     */
    public static function insertSlide(array $input)
    {
        DB::beginTransaction();
        try {
            if(isset($input['id'])) {
                $slide = self::getSlideById($input['id']);
                $slide->repeatSlide()->delete();
            } else {
                $slide = new Slide;
            }
            $slide->fill($input);
            if (!isset($input['font_size'])) {
                $slide->font_size = null;
            }
            $slide->save();
            if(isset($input['repeat'])) {
                foreach ($input['repeat'] as $value) {
                    $repeat = new Repeat;
                    $repeat->type = $value;
                    $slide->repeatSlide()->save($repeat);
                }
            }
            $common = new View();
            $uploadFolderVideo = File::PATH_VIDEO_DEFAULT;
            $uploadFolderImage = File::PATH_DEFAULT;
            if ($input['option'] == self::OPTION_NOMAL) {
                if ($input['type'] == self::TYPE_IMAGE) {
                    File::insertFile($slide, $input);
                } else {
                    $file = File::where('slide_id', $slide->id)->delete();
                    $file = new File();
                    $file->file_name = $input['video_id'];
                    $file->slide_id = $slide->id;
                    $file->save();
                    // if (isset($input['video'])) {
                    //     $listVideoString = array();
                    //     $videoOld = File::where('slide_id', $slide->id)->lists('file_name')->toArray();
                    //     $file = new File();
                    //     $video = $common->uploadFile($input['video'],
                    //         Config::get('general.upload_storage_public_folder') . 
                    //         '/' . $uploadFolderVideo);
                    //     $file->file_name = $video;
                    //     $file->slide_id = $slide->id;
                    //     $file->save();
                    //     array_push($listVideoString, $video);

                    //     //delete image in db
                    //     File::where('slide_id', $slide->id)
                    //         ->whereNotIn('file_name', $listVideoString)
                    //         ->delete();
                    //     for ($t = 0; $t < count($videoOld); $t++) {
                    //         $checkExist = false;
                    //         for ($k = 0; $k < count($listVideoString); $k++) {
                    //             if ($videoOld[$t] == $listVideoString[$k]) {
                    //                 $checkExist = true;
                    //             }
                    //         }
                    //         if (!$checkExist) {
                    //             $common->deleteFile(trim(Config::get('general.upload_storage_public_folder') . 
                    //                 '/' . $uploadFolderVideo, '/').'/'.$videoOld[$t]);
                    //             $common->deleteFile(trim(Config::get('general.upload_storage_public_folder') . 
                    //                 '/' . $uploadFolderImage, '/').'/'.$videoOld[$t]);
                    //         }
                    //     }
                    // }
                }
            } else {
                File::insertFile($slide, $input);
            }
            if (isset($input['quotation'])) {
                SlideQuotation::insertUpdateSlideQuotation($slide, (array) $input['quotation']);
            }
            DB::commit();
            return [
                'status' => true,
                'id' => $slide->id,
            ];
        } catch (Exception $ex) {
            DB::rollback();
            return [
                'status' => false,
            ];
        }
    }

    /**
     * get slide show current
     * @return array
     */
    public static function getSlideShowNow()
    {
        $dateNow = date("Y-m-d");
        $hourNow = date("H:i");
        $arrayHourNow = explode(":", $hourNow);
        $timeHourNow =  Carbon::createFromTime($arrayHourNow[0], $arrayHourNow[1]);

        $slide = self::where('date', $dateNow)
                    ->where('hour_start', '<=', $hourNow)
                    ->where('hour_end', '>' , $hourNow)
                    ->first();
        if ($slide) {
            return $slide;
        }


        // get slide repeat hourly
        $slides = self::where('date', $dateNow)->get();
        //get hour last
        $allHour = Slide::getHour();
        end($allHour);
        $hourLast = key($allHour);
        $arrayHourLast =  explode(":", $hourLast);
        $timeHourLast =  Carbon::createFromTime($arrayHourLast[0], $arrayHourLast[1]);

        foreach($slides as $slide) {
            $repeatHourly = Repeat::where('slide_id', $slide->id)
                                    ->where('type', Repeat::TYPE_REPEAT_HOURLY)
                                    ->first();
            $arrayHourStart = explode(":", $slide->hour_start);
            $arrayhourEnd = explode(":", $slide->hour_end);
            $timeHourStart =  Carbon::createFromTime($arrayHourStart[0], $arrayHourStart[1]);
            $timeHourEnd =  Carbon::createFromTime($arrayhourEnd[0], $arrayhourEnd[1]);
            if ($repeatHourly) {
                do {
                    if ($timeHourStart <= $timeHourNow && $timeHourEnd > $timeHourNow) {
                        return $slide;
                    }
                    $timeHourStart = $timeHourStart->addHour(1);
                    $timeHourEnd = $timeHourEnd->addHour(1);
                } while($timeHourLast >= $timeHourEnd);
            }
        }

        $slides = self::all();
        $arrayDateNow = explode("-", $dateNow);
        $dateTimeNow = Carbon::create($arrayDateNow[0], $arrayDateNow[1], $arrayDateNow[2]);
        foreach ($slides as $key => $slide) {
            $repeatDaily = Repeat::where('slide_id', $slide->id)
                                    ->where('type', Repeat::TYPE_REPEAT_DAILY)
                                    ->first();
            //get slide repeat daily
            if ($repeatDaily) {
                if ($slide->date <= $dateNow) {
                    $arrayHourStart = explode(":",$slide->hour_start);
                    $arrayhourEnd = explode(":",$slide->hour_end);
                    $timeHourStart =  Carbon::createFromTime($arrayHourStart[0], $arrayHourStart[1]);
                    $timeHourEnd =  Carbon::createFromTime($arrayhourEnd[0], $arrayhourEnd[1]);
                    if ($timeHourStart <= $timeHourNow && $timeHourEnd > $timeHourNow) {
                        return $slide;
                    }
                }
            }
            //get slide repeat weekly
            $repeatWeekly = Repeat::where('slide_id', $slide->id)
                                    ->where('type', Repeat::TYPE_REPEAT_WEEKLY)
                                    ->first();
            if($repeatWeekly) {
                if ($slide->date <= $dateNow) {
                    $arrayDay = explode("-", date_format(date_create($slide->date), 'Y-m-d'));
                    $dateTime = Carbon::create($arrayDay[0], $arrayDay[1], $arrayDay[2]);
                    if ($dateTime->dayOfWeek == $dateTimeNow->dayOfWeek) {
                        $arrayHourStart = explode(":",$slide->hour_start);
                        $arrayhourEnd = explode(":",$slide->hour_end);
                        $timeHourStart =  Carbon::createFromTime($arrayHourStart[0], $arrayHourStart[1]);
                        $timeHourEnd =  Carbon::createFromTime($arrayhourEnd[0], $arrayhourEnd[1]);
                        if ($timeHourStart <= $timeHourNow && $timeHourEnd > $timeHourNow) {
                            return $slide;
                        }
                    }
                }
            }
        }
        return null;
    }

    /**
     * delete slide
     * @param array
     * @return boolean
     */
    public static function deleteSlide($slide) {
        DB::beginTransaction();
        try {
            $slide->repeatSlide()->delete();
            $slide->slideSlide()->delete();
            $files = File::where('slide_id', $slide->id)->get();
            $common = new View();
            $uploadFolderVideo = File::PATH_VIDEO_DEFAULT;
            $uploadFolderImage = File::PATH_DEFAULT;
            foreach($files as $file) {
                if ($slide->type == File::TYPE_VIDEO) {
                    $common->deleteFile(trim(Config::get('general.upload_storage_public_folder') . 
                                    '/' . $uploadFolderVideo, '/').'/'.$file->file_name);
                } else {
                    $common->deleteFile(trim(Config::get('general.upload_storage_public_folder') . 
                                    '/' . $uploadFolderImage, '/').'/'.$file->file_name);
                }
            }
            SlideQuotation::deleteSlideQuotation($slide);
            SlideBirthday::deleteSlideBirthday($slide);
            $slide->delete();
            DB::commit();
            return true;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /**
     * update password of slider
     * @param string
     * @return boolean
     */
    public static function updatePassword($data) {
        $password = CoreConfigData::where('key', 'slide_show.password')->first();
        if(!$password) {
            $password = new CoreConfigData;
            $password->key = 'slide_show.password';
        }
        $password->value = $data['password'];
        if ($password->save()) {
            if (Session::has('password-slider')) {
                Session::forget('password-slider');
            }
            return true;
        }
        return false;
    }
    
    /**
     * get effect avai
     * 
     * @return array
     */
    public static function getEffectOption()
    {
        return [
            'slide',
            'fade'
        ];
    }

    /**
     * update time birthday
     * @param string
     * @return boolean
     */
    public static function updateTimeBirthday($data) {
        $birthday = CoreConfigData::where('key', 'slide_show.birthday_company')->first();
        if(!$birthday) {
            $birthday = new CoreConfigData;
            $birthday->key = 'slide_show.birthday_company';
        }
        $date = str_replace('/', '-', $data['birthday_company']);
        $date = Carbon::parse($date)->format('Y-m-d H:i:s');
        $birthday->value = $date;
        if ($birthday->save()) {
            return true;
        }
        return false;
    }

    /**
     * update slide birthday title
     * @param string
     * @return boolean
     */
    public static function updateTitleSlideBirthday($title) {
        $slides = self::select('id')->whereRaw("Date(created_at) = CURDATE()")->get()->toArray();
        if(!$slides) {
            return;
        }
        $slideArrayId = array();
        foreach($slides as $slideItem) {
            $slideArrayId[] = $slideItem['id'];
        }  
        self::whereIn('id', $slideArrayId)->update(['title' => $title]);
    }
}