<?php

namespace Rikkei\Core\View;

use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Image;
use Rikkei\Test\View\ViewTest;

/**
 * View ouput gender
 */
class View
{
    /**
     * get date from datetime standard
     * 
     * @param type $datetime
     * @return string
     */
    public static function getDate($datetime)
    {
        return ($datetime && !($datetime == '0000-00-00 00:00:00' || $datetime == '0000-00-00')) ? self::formatDateTime('Y-m-d H:i:s', 'Y-m-d', $datetime) : '';
    }

    /**
     * get date from date standard
     * 
     * @param string $date
     * @return string
     */
    public static function getOnlyDate($date)
    {
        return ($date !== '0000-00-00 00:00:00' && $date !== '0000-00-00' && $date) ? Carbon::parse($date) : '';
    }

    /**
     * format datetime
     * 
     * @param string $formatFrom
     * @param string $formatTo
     * @param string $datetime
     * @return string
     */
    public static function formatDateTime($formatFrom, $formatTo, $datetime)
    {
        if (! $datetime || ! $formatFrom || ! $formatTo) {
            return;
        }
        $date = DateTime::createFromFormat($formatFrom, $datetime);
        if (! $date) {
            $date = strtotime($datetime);
            return date($formatTo, $date);
        }
        return $date->format($formatTo);
    }
    
    /**
     * check email allow of intranet
     * 
     * @param string $email
     * @return boolean
     */
    public static function isEmailAllow($email)
    {
        if (preg_replace('/@.*/', '', $email) === '') {
            return false;
        }
        //add check email allow
        $domainAllow = Config::get('domain_logged');
        if ($domainAllow && count($domainAllow)) {
            foreach ($domainAllow as $value) {
                if (preg_match('/@' . $value . '$/', $email)) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * check email is root
     * 
     * @return boolean
     */
    public static function isRoot($email)
    {
        if (trim(Config('services.account_root')) == $email) {
            return true;
        }
        return false;
    }
    
    /**
     * show permission view
     */
    public static function viewErrorPermission()
    {
        Session::forget('messages');
        Session::forget('flash');
        if (request()->ajax() || request()->wantsJson() || CoreUrl::isApi()) {
            return response()->json([
                'message' => trans('core::message.You don\'t have access'),
                'status' => 0,
                'success' => 0,
                'error' => 1,
            ], 401);
        }
        echo view('errors.permission');
        exit;
    }
    
    /**
     * route to option
     * 
     * @return array
     */
    public static function routeListToOption()
    {
        $routeCollection = Route::getRoutes();
        $option = [];
        $option[] = [
            'value' => '#',
            'label' => '#',
        ];
        foreach ($routeCollection as $value) {
            $path = $value->getPath();
            if (preg_match('/\{.*\?\}/', $value->getPath())) {
                $path = preg_replace('/\{.*\?\}.*/', '', $path);
            } else if (preg_match('/[{}?]/', $value->getPath())) {
                continue;
            }
            if ($path != '/') {
                $path = trim($path, '/');
            }
            $option[] = [
               'value' => $path,
                'label' => $path,
            ];
        }
        return $option;
    }
    
    /**
     * get no. starter from grid data
     */
    public static function getNoStartGrid($collectionModel)
    {
        if (! $collectionModel->total()) {
            return 1;
        }
        $currentPage = $collectionModel->currentPage();
        $perPage = $collectionModel->perPage();
        return ($currentPage - 1) * $perPage + 1;
    }

    /**
     * upload file
     *
     * @param $file
     * @param $path
     * @param array $allowType
     * @param null $maxSize
     * @param bool $rename
     * @param array $config
     * @return string|null
     * @throws Exception
     */
    public static function uploadFile($file, $path, $allowType = [], $maxSize = null, $rename = true, array $config = [])
    {
        $widthResize = 200;
        try {
            if ($file->isValid()) {
                if ($allowType) {
                    $extension = $file->getClientMimeType();
                    if (! in_array($extension, $allowType)) {
                        throw new Exception(Lang::get('core::message.File type dont allow'), \Rikkei\Core\Model\CoreModel::ERROR_CODE_EXCEPTION);
                    }
                }

                if ($maxSize) {
                    $fileSize = $file->getClientSize();
                    if ($fileSize / 1000 > $maxSize) {
                        throw new Exception(Lang::get('core::message.File size is large'), \Rikkei\Core\Model\CoreModel::ERROR_CODE_EXCEPTION);
                    }
                }
                if ($rename) {
                    $extension = $file->getClientOriginalExtension();
                    if (is_string($rename)) {
                        $fileName = $rename . '.' . $extension;
                    } else {
                        $fileName = str_random(5) . '_' . time() . '.' . $extension;
                    }
                } else {
                    $fileName = $file->getClientOriginalName();
                }
                $fullPathOrg = $file->getRealPath();
                if ($config && isset($config['remove_exif']) && $config['remove_exif']) {
                    self::removeExifImage($fullPathOrg);
                }
                if (!Storage::exists($path)) {
                    Storage::makeDirectory($path, 0777);
                }
                @chmod(storage_path($path), 0777);

                if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                    $img = Image::make($fullPathOrg);
                    $width = $img->getWidth();

                    //Nếu ảnh lớn hơn kích thước tối đa cho phép, thì resize lại
                    if ($width > $widthResize) {
                        // Resize image
                        $img->widen($widthResize, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        })->heighten($widthResize, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        });
                    }

                    //Save image with quality 75% (only jpg)
                    $img->save(storage_path("app/$path/$fileName"), 75);
                } else {
                    Storage::put(
                        $path . '/' . $fileName,
                        file_get_contents($fullPathOrg)
                    );
                }
                return $fileName;
            }
            return null;
        } catch (Exception $e) {
            \Log::error($e);
            throw new Exception($e->getMessage());
        }
    }
    
    /**
     * remove exif info of image
     * 
     * @param string $pathFile
     * @return string
     */
    public static function removeExifImage($pathFile)
    {
        // check image jpg
        if (exif_imagetype($pathFile) != IMAGETYPE_JPEG) {
            return false;
        }
        $exif = @exif_read_data($pathFile, 'IFD0');
        // check image exif
        if (!$exif || !isset($exif['Orientation'])) {
            return ;
        }
        $source = imagecreatefromjpeg($pathFile);
        $image = false;
        switch($exif['Orientation']) {
            case 3: // 180 rotate left
                $image = imagerotate($source, 180, 0);
                break;
            case 6: // 90 rotate right
                $image = imagerotate($source, -90, 0);
                break;
            case 8:    // 90 rotate left
                $image = imagerotate($source, 90, 0);
                break;
        }
        if ($image) {
            imagejpeg($image, $pathFile, 100);
            imagedestroy($image);
        }
        imagedestroy($source);
        return $pathFile;
    }
    

    /**
     * delete file 
     * @param type $path
     */
    public static function deleteFile($path)
    {
        if (Storage::disk('local')->has($path)) {
            Storage::disk('local')->delete($path);
        }
    }

    /**
     * get link image file
     * 
     * @param string|null $path
     * @param boolean $useDefault
     * @return string|null
     */
    public static function getLinkImage($path = null, $useDefault = true)
    {
        if (! $path) {
            if ($useDefault) {
                return URL::asset('common/images/noimage.png');
            }
            return null;
        }
        if (preg_match('/^http(s)?:\/\//', $path)) {
            return $path;
        }
        if (file_exists(public_path($path))) {
            return URL::asset($path);
        }
        if ($useDefault) {
            return URL::asset('common/images/noimage.png');
        }
        return null;
    }
    
    /**
     * get language level
     * 
     * @return array
     */
    public static function getLanguageLevel()
    {
        return Config::get('general.language_level');
    }

    /**
     * get language level
     * 
     * @return array
     */
    public static function getLangLevelSplit()
    {
        $langs = Config::get('general.language_level');
        $result = [
            'ja' => [],
            'en' => [],
        ];
        foreach ($langs as $lang) {
            if (preg_match('/^N[0-9]$/', $lang)) {
                $result['ja'][] = $lang;
            } else {
                $result['en'][] = $lang;
            }
        }
        return $result;
    }

    /**
     * get format json language level
     * 
     * @return string json
     */
    public static function getLanguageLevelFormatJson()
    {
        return \GuzzleHttp\json_encode(self::getLanguageLevel());
    }
    
    /**
     * to option language level
     * 
     * @param type $nullable
     * @return type
     */
    public static function toOptionLanguageLevel($nullable = true)
    {
        $options = [];
        if ($nullable) {
            $options[] = [
                'value' => '',
                'label' => Lang::get('core::view.-- Please choose --'),
            ];
        }
        $level = self::getLanguageLevel();
        if (! $level) {
            return $options;
        }
        foreach ($level as $key => $item) {
            if (! $key) {
                continue;
            }
            $options[] = [
                'value' => $key,
                'label' => $item,
            ];
        }
        return $options;
    }
    
    /**
     * get label of level language
     * 
     * @param type $key
     * @return type
     */
    public static function getLabelLanguageLevel($key)
    {
        $level = self::getLanguageLevel();
        if (! $level || ! isset($level[$key]) || ! $level[$key]) {
            return;
        }
        return $level[$key];
    }
    
    /**
     * get normal level
     * 
     * @return array
     */
    public static function getNormalLevel()
    {
        return Config::get('general.normal_level');
    }
    
    /**
     * to option normal level
     * 
     * @param type $nullable
     * @return type
     */
    public static function toOptionNormalLevel($nullable = true)
    {
        $options = [];
        if ($nullable) {
            $options[] = [
                'value' => '',
                'label' => Lang::get('core::view.-- Please choose --'),
            ];
        }
        $level = self::getNormalLevel();
        if (! $level) {
            return $options;
        }
        foreach ($level as $key => $item) {
            if (! $key) {
                continue;
            }
            $options[] = [
                'value' => $key,
                'label' => $item,
            ];
        }
        return $options;
    }
    
    /**
     * get label of level normal
     * 
     * @param type $key
     * @return type
     */
    public static function getLabelNormalLevel($key)
    {
        $level = self::getNormalLevel();
        if (! $level || ! isset($level[$key]) || ! $level[$key]) {
            return;
        }
        return $level[$key];
    }
    
    /**
     * get format json normal level
     * 
     * @return string json
     */
    public static function getNormalLevelFormatJson()
    {
        return \GuzzleHttp\json_encode(self::getNormalLevel());
    }
    
    /**
     * Get Romanic number
     * @param int $integer
     * @param boolean $upcase
     * @return romanic
     */
    public static function romanic_number($integer, $upcase = true) 
    { 
        $table = array('M'=>1000, 'CM'=>900, 'D'=>500, 'CD'=>400, 'C'=>100, 'XC'=>90, 'L'=>50, 'XL'=>40, 'X'=>10, 'IX'=>9, 'V'=>5, 'IV'=>4, 'I'=>1); 
        $return = ''; 
        while($integer > 0) 
        { 
            foreach($table as $rom=>$arb) 
            { 
                if($integer >= $arb) 
                { 
                    $integer -= $arb; 
                    $return .= $rom; 
                    break; 
                } 
            } 
        } 

        return $return; 
    }
    
    /**
     * translate text follow module
     * 
     * @param string $text
     * @param string $file
     * @param string $module
     * @return string
     */
    public static function trans($text = '', $file = '', $module = '')
    {
        if (Lang::has($module.'::'. $file . '.' . $text)) {
            return Lang::get($module.'::'. $file . '.' . $text);
            Lang::get('project::view.Title');
        }
        return $text;
    }
    /*
     * custom lang
     * @parm string
     * @param string
     * @return string
     */
    public static function customLang($path, $text)
    {
        if (Lang::has($path)) {
            return Lang::get($path);
        }
        return $text;
    }

    /*
     * get status label
     * @param array
     * @param int
     * @return sring
     */
    public static function getStatusLabel($data, $statusId)
    {
        foreach ($data as $key => $value) {
            if ($key == $statusId) {
                return $value;
            }
        }
    }

    /*
     * format array team name
     * @param array
     * @return array
     */
    public static function formatArrayTeamName($array)
    {
        $result = [];
        foreach($array as $key =>  $value) {
            $result[$key+1] = $value;
        }
        return $result;
    }
    
    /**
     * nl2br note
     * 
     * @param string $note
     * @return string
     */
    public static function nl2br($note)
    {
        if ($note === null) {
            return null;
        }
        $note = e($note);
        return nl2br($note);
    }
    
    /**
     * cut text
     * 
     * @param type $note
     * @param type $length
     * @param type $default
     * @return type
     */
    public static function substr(
            $note, 
            $length = 50,
            $default = '...', 
            $joinDefault = true
    ) {
        if ($note === null || empty($note)) {
            return null;
        }
        $note = e($note);
        $note = Str::substr($note, 0, $length);
        if ($joinDefault) {
            $note .= $default;
        }
        return $note;
    }
    
    /**
     * get date from any format
     * 
     * @param string $datetime
     * @return object
     */
    public static function getDateFromAny($datetime)
    {
        $date = substr($datetime, 0, 10);
        if (preg_match('/^[0-9]{1,2}(\-|\/|\,|\.)[0-9]{1,2}(\-|\/|\,|\.)[0-9]{4}$/', $date)) {
            //format dd/mm/yyyy
            $flagSplit = preg_replace('/^([0-9]+)(\-|\/|\,|\.)(.+)/', '$2', $date);
            $date = Carbon::createFromFormat("d{$flagSplit}m{$flagSplit}Y", $date);
            return $date;
        }
        if (preg_match('/^[0-9]{4}(\-|\/|\,|\.)[0-9]{1,2}(\-|\/|\,|\.)[0-9]{1,2}$/', $date)) {
            //format yyyy-mm-dd
            $flagSplit = preg_replace('/^([0-9]+)(\-|\/|\,|\.)(.+)/', '$2', $date);
            $date = Carbon::createFromFormat("Y{$flagSplit}m{$flagSplit}d", $date);
            return $date;
        }
        return null;
    }
    
    /**
     * get label of options
     * 
     * @param string $key
     * @param array $options
     * @return string
     */
    public static function getLabelOfOptions($key, $options, $default = null)
    {
        if (array_key_exists($key, $options)) {
            return $options[$key];
        }
        if (!$default) {
            return reset($options);
        }
        return $options[$default];
    }
    
    /**
     * get key of options
     * 
     * @param string $key
     * @param array $options
     * @return string
     */
    public static function getKeyOfOptions($key, $options, $default = null)
    {
        if (array_key_exists($key, $options)) {
            return $key;
        }
        if (!$default) {
            reset($options);
            return key($options);
        }
        return $default;
    }

    /**
     * show not found view
     */
    public static function viewErrorNotFound()
    {
        Session::forget('messages');
        Session::forget('flash');
        echo view('errors.not-found');
        exit;
    }

    /**
     * get day of last week
     * 
     * @param datetime $date
     * @param int $day
     * 
     * @return datetime
     */
    public static function getDateLastWeek($date = null, $day = 1)
    {
        if (!$date) {
            $date = Carbon::now();
        }
        $weekCurrent = $date->format('W');
        $yearCurrent = $date->format('Y');
        $result = clone $date;
        $result->setISODate($yearCurrent, $weekCurrent-1, $day);
        return $result;
    }
    
    /**
     * get nick name from email
     * 
     * @param string|array $emails
     * @return string
     */
    public static function getNickName($emails)
    {
        if (is_array($emails)) {
            $names = [];
            foreach ($emails as $mail) {
                $names[] = ucfirst(strtolower(preg_replace('/@.*/', '', $mail)));
            }
            return implode(', ', $names);
        }
        return ucfirst(strtolower(preg_replace('/@.*/', '', $emails)));
    }
    
    /**
     * Format number 
     * @param float $number
     * @param int $decimalPoint
     * @return float
     */
    public static function formatNumber($number, $decimalPoint){
        return number_format($number, $decimalPoint, ".",",");
    }
    
    /**
      * convert tring Vietnamese without strawberry
      * @param string
      * @return string
    */
    public static function convertString($langs)
    {
        $langs = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $langs);
        $langs = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $langs);
        $langs = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $langs);
        $langs = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $langs);
        $langs = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $langs);
        $langs = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $langs);
        $langs = preg_replace("/(đ)/", 'd', $langs);
        $langs = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'a', $langs);
        $langs = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'e', $langs);
        $langs = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'i', $langs);
        $langs = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'o', $langs);
        $langs = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'u', $langs);
        $langs = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'y', $langs);
        $langs = preg_replace("/(Đ)/", 'd', $langs);
        return $langs; 
    }
    
    /** convert standard email
     * 
     * @param string $email
     * @return string
     */
    public static function standardEmail($email)
    {
        $emailAtPosition = stripos($email, '@');
        if ($emailAtPosition) {
            $emailName = preg_replace('/\s/', '', 
                Str::ascii(substr($email, 0, $emailAtPosition)));
            $emailDomain = preg_replace('/\s/', '', 
                Str::ascii(substr($email, $emailAtPosition+1)));
            return $emailName . '@' . $emailDomain;
        }
        return preg_replace('/\s/', '', Str::ascii($email));
    }
    
    /**
     * list array days in weeks
     * @return array
     */
    public static function daysInWeek() {
        return [
            Carbon::SUNDAY => trans('core::view.Sunday'),
            Carbon::MONDAY => trans('core::view.Monday'),
            Carbon::TUESDAY => trans('core::view.Tuesday'),
            Carbon::WEDNESDAY => trans('core::view.Wednesday'),
            Carbon::THURSDAY => trans('core::view.Thursday'),
            Carbon::FRIDAY => trans('core::view.Friday'),
            Carbon::SATURDAY => trans('core::view.Saturday'),
        ];
    }

    /**
     * get value of a array with check keys
     *
     * @param array $array
     * @param array $keys
     * @param type $valueDefault
     * @return type
     */
    public static function getValueArray($array, $keys, $valueDefault = null)
    {
        $value = $array;
        foreach ($keys as $key) {
            if (!isset($value[$key])) {
                return $valueDefault;
            }
            $value = $value[$key];
        }
        return $value;
    }

    /**
     * Get a array child in array 2 dimensional from field and value of field
     *
     * @param array $arraySearch
     * @param string $field
     * @param string $value
     * @return array|boolean false
     */
    public static function getArrayInArrayTwodimensional($arraySearch, $field, $value)
    {
        foreach($arraySearch as $key => $arrayItem) {
            if ( $arrayItem[$field] === $value ) {
                return $arrayItem;
            }
        }
        return false;
    }

    /**
     * Get last word of string
     *
     * @param string $string
     * @return string
     */
    public static function getLastWord($string)
    {
        $tempArray = explode(' ', $string);
        return array_pop($tempArray);
    }

    /**
     * upload file
     *
     * @param object $file
     * @param string $path
     * @param boolean $rename
     * @return string
     */
    public static function uploadFileInfo($file, $path, array $option = [], $rename = true)
    {
        if (!$file->isValid()) {
            return null;
        }
        if (isset($option['max_size'])) { // MB
            if ($file->getClientSize() / 1000 / 1000 > $option['max_size']) {
                throw new Exception(trans('core::message.File size is large'));
            }
        }
        $extension = $file->getClientOriginalExtension();
        if (isset($option['file_mimes'])) {
            if (!preg_grep('/'.$extension.'/i', $option['file_mimes'])) {
                throw new Exception(trans('core::message.File type dont allow'));
            }
        }
        $name = substr($file->getClientOriginalName(), 0, -(strlen($extension) + 1));
        if ($rename) {
            if (is_string($rename)) {
                $new = $rename;
            } else {
                $new = str_random(10) . time();
            }
        } else {
            $new = $name;
        }
        Storage::put(
            $path . '/' . $new . '.' . $extension,
            file_get_contents($file->getRealPath())
        );
        return [
            'name'      => $name,
            'size'      => $file->getClientSize() / 1000, //KB
            'extension' => $extension,
            'new'       => $new,
            'type'      => $file->getClientMimeType(),
            'path'      => $path,
        ];
    }

    /**
     * compare two array is diff
     *
     * @param array $originData
     * @param array $newData
     * @param array $keysCompare
     * @return boolean
     */
    public static function isdiffArray($originData, $newData, $keysCompare = null)
    {
        if (!$keysCompare) {
            $keysCompare = array_keys($newData);
        }
        
        foreach ($keysCompare as $key) {
            if (!isset($originData[$key])) {
                return true;
            }
            if ($newData[$key] != $originData[$key]) {
                return true;
            }
        }
        return false;
    }

    /**
     * cut word follow limit string
     *
     * @param string $string
     * @param int $length
     * @param string $end
     * @return string
     */
    public static function cutWordLimitStr($string, $length, $end = '...')
    {
        $string = trim($string);
        if (Str::length($string) <= $length) {
            return $string;
        }
        $length++;
        $string = Str::substr($string, 0, $length);
        for ($i = 1; $i < 10; $i++) { // limit 10 times cut last char
            $space = Str::substr($string, -1);
            $string = Str::substr($string, 0, -1);
            if ($space === ' ') {
                return $string . $end;
            }
        }
        return $string . $end;
    }

    /**
     * create date from any format
     *  use Regexp
     *
     * @param string $string
     * @return Carbon Object
     */
    public static function createDateFromFormat($string)
    {
        if (!$string) {
            return null;
        }
        $formats = [
            '/^([0-9]{4})\D+([0-9]{2})\D+([0-9]{2})\D*$/'=> 'Y-m-d',
            '/^([0-9]{4})\D+([0-9]{1})\D+([0-9]{2})\D*$/'=> 'Y-m-d',
            '/^([0-9]{4})\D+([0-9]{1})\D+([0-9]{1})\D*$/'=> 'Y-m-d',
            '/^([0-9]{4})\D+([0-9]{2})\D+([0-9]{1})\D*$/'=> 'Y-m-d',
            '/^([0-9]{2})\D+([0-9]{2})\D+([0-9]{2})\D*$/'=> 'y-m-d',
            '/^([0-9]{2})\D+([0-9]{1})\D+([0-9]{2})\D*$/'=> 'y-m-d',
            '/^([0-9]{2})\D+([0-9]{1})\D+([0-9]{1})\D*$/'=> 'y-m-d',
            '/^([0-9]{2})\D+([0-9]{2})\D+([0-9]{1})\D*$/'=> 'y-m-d',
            '/^([0-9]{2})\D+([0-9]{2})\D+([0-9]{4})\D*$/'=> 'd-m-Y',
            '/^([0-9]{2})\D+([0-9]{1})\D+([0-9]{4})\D*$/'=> 'd-m-Y',
            '/^([0-9]{1})\D+([0-9]{2})\D+([0-9]{4})\D*$/'=> 'd-m-Y',
            '/^([0-9]{1})\D+([0-9]{1})\D+([0-9]{4})\D*$/'=> 'd-m-Y',
            '/^([0-9]{1})\D+([0-9]{2})\D+([0-9]{2})\D*$/'=> 'd-m-y',
            '/^([0-9]{1})\D+([0-9]{1})\D+([0-9]{2})\D*$/'=> 'd-m-y',
            '/^([0-9]{2})\D+([a-zA-Z]{3})\D+([0-9]{2})\D*$/'=> 'd-M-y',
            '/^([0-9]{1})\D+([a-zA-Z]{3})\D+([0-9]{2})\D*$/'=> 'd-M-y',
            '/^([0-9]{4})\D+([0-9]{2})\D*$/'=> 'Y-m',
            '/^([0-9]{4})\D+([0-9]{1})\D*$/'=> 'Y-m',
            '/^([0-9]{2})\D+([0-9]{2})\D*$/'=> 'y-m',
            '/^([0-9]{2})\D+([0-9]{1})\D*$/'=> 'y-m',
            '/^([a-zA-Z]{3})\D+([0-9]{1})\D+([0-9]{4})\D*$/'=> 'M-d-Y',
            '/^([a-zA-Z]{3})\D+([0-9]{2})\D+([0-9]{4})\D*$/'=> 'M-d-Y',
            '/^([a-zA-Z]{3})\D+([0-9]{1})\D+([0-9]{2})\D*$/'=> 'M-d-y',
            '/^([a-zA-Z]{3})\D+([0-9]{2})\D+([0-9]{2})\D*$/'=> 'M-d-y',
            '/^([a-zA-Z]{3})\D+([0-9]{2})\D*$/'=> 'M-y',
            '/^([a-zA-Z]{3})\D+([0-9]{4})\D*$/'=> 'M-Y',
        ];
        foreach ($formats as $reg => $format) {
            $matches = null;
            if (preg_match($reg, $string, $matches)) {
                if (!$matches || count($matches) < 2) {
                    continue;
                }
                $stringMatch = '';
                foreach ($matches as $key => $match) {
                    if ($key === 0) {
                        continue;
                    }
                    $stringMatch .= $match . '-';
                }
                $stringMatch = substr($stringMatch, 0, -1);
                try {
                    return Carbon::createFromFormat($format, $stringMatch);
                } catch (Exception $ex) {
                    Log::error($ex);
                }
            }
        }
        return null;
    }

    /*
     * get employee by ids
     */
    public static function getOldEmployees($id, $returnFirst = true)
    {
        if (!is_array($id)) {
            $id = [$id];
        }
        $list = \Rikkei\Team\Model\Employee::whereIn('id', $id);
        if ($returnFirst && count($id) == 1) {
            return $list->first();
        }
        return $list->get();
    }

    /**
     * Compare 2 numbers
     *
     * @param float $a   number 1
     * @param string $operator
     * @param float $b   number 2
     *
     * @return boolean
     *
     * @throws Exception
     */
    public static function doComparison($a, $operator, $b)
    {
        $a = (float)$a;
        $b = (float)$b;

        switch ($operator) {
            case '<':  return ($a <  $b); break;
            case '<=': return ($a <= $b); break;
            case '=':  return ($a == $b); break; // SQL way
            case '==': return ($a == $b); break;
            case '!=': return ($a != $b); break;
            case '>=': return ($a >= $b); break;
            case '>':  return ($a >  $b); break;
        }

        throw new Exception("The {$operator} operator does not exists", 1);
    }

    /**
     * Floor To Fraction
     * Example:
     * echo floorToFraction(1.82);      // 1
     * echo floorToFraction(1.82, 2);   // 1.5
     * echo floorToFraction(1.82, 3);   // 1.6666666666667
     * echo floorToFraction(1.82, 4);   // 1.75
     * echo floorToFraction(1.82, 9);   // 1.7777777777778
     * echo floorToFraction(1.82, 25);  // 1.8
     *
     * @param float $number
     * @param int $denominator
     *
     * @return float
     */
    public static function floorToFraction($number, $denominator = 1)
    {
        $x = $number * $denominator;
        $x = floor($x);
        return $x / $denominator;
    }

    /**
     * get previous url
     * @param string $route
     * @return string
     */
    public static function previousUrl($route = null)
    {
        $currentUrl = request()->url();
        $previousUrl = url()->previous();
        if ($route && $currentUrl == $previousUrl) {
            return route($route);
        }
        return $previousUrl;
    }

    /**
     * check string is serialized data or not
     * @param string $data
     * @param bool $strict
     * @return boolean
     */
    public static function isSerialized($data, $strict = true)
    {
	// if it isn't a string, it isn't serialized.
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ('N;' == $data) {
            return true;
        }
        if (strlen($data) < 4) {
            return false;
        }
        if (':' !== $data[1]) {
            return false;
        }
        if ($strict) {
            $lastc = substr($data, -1);
            if (';' !== $lastc && '}' !== $lastc) {
                return false;
            }
        } else {
            $semicolon = strpos($data, ';');
            $brace = strpos($data, '}');
            // Either ; or } must exist.
            if (false === $semicolon && false === $brace) {
                return false;
            }
            // But neither must be in the first X characters.
            if (false !== $semicolon && $semicolon < 3) {
                return false;
            }
            if (false !== $brace && $brace < 4) {
                return false;
            }
        }
        $token = $data[0];
        switch ($token) {
            case 's':
                if ($strict) {
                    if ('"' !== substr($data, -2, 1)) {
                        return false;
                    }
                } elseif (false === strpos($data, '"')) {
                    return false;
                }
            // or else fall through
            case 'a':
            case 'O':
                return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b':
            case 'i':
            case 'd':
                $end = $strict ? '$' : '';
                return (bool) preg_match("/^{$token}:[0-9.E-]+;$end/", $data);
        }
        return false;
    }

    /**
     * Đếm số từ trong 1 string với độ dài giới hạn
     * @param string $content
     * @param null|int $limit
     * @return int
     */
    public static function countWord($content, $limit = null)
    {
        return count(preg_split("/[\n\r\t ]+/", $content, $limit, PREG_SPLIT_NO_EMPTY));
    }

    /**
     * @param $content
     * @param null $litmit
     * @return array
     */
    public static function getShortContent($content, $litmit = null)
    {
        $hasMore = false;
        $shortContent = ViewTest::trimWords($content,
            ['num_line' => 2, 'num_word' => $litmit, 'num_ch' => 10000],
            '', $hasMore);
        $count = View::countWord($content) - View::countWord($shortContent, $litmit);
        $hasMore = $count > 2 ? $hasMore : false;

        return ['has_more' => $hasMore, 'short_content' => $shortContent];
    }
}
