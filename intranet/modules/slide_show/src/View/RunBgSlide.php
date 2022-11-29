<?php

namespace Rikkei\SlideShow\View;

use Illuminate\Support\Facades\Storage;
use Rikkei\SlideShow\Model\Slide;
use Rikkei\SlideShow\View\ImageHelper;
use Rikkei\SlideShow\Model\File;
use Rikkei\Core\Model\CoreConfigData;
use Illuminate\Support\Facades\Log;
use Exception;

class RunBgSlide
{
    const FOLDER_UPLOAD = 'slide';
    const FOLDER_PROCESS = 'process';
    const FOLDER_APP = 'app';
    
    const RUN_WAIT = 1;
    const RUN_PROCESS = 2;
    const RUN_ERROR = 3;

    const ACCESS_FOLDER = 0777;
    const ACCESS_FILE = 'public';
    
    /**
     *  create process bg folder
     */
    public static function createProcessBg($id)
    {
        $id = (int) $id;
        if (!Storage::exists(self::FOLDER_UPLOAD)) {
            Storage::makeDirectory(self::FOLDER_UPLOAD, self::ACCESS_FOLDER);
        }
        @chmod(storage_path(self::FOLDER_APP . '/' . self::FOLDER_UPLOAD), 
                self::ACCESS_FOLDER);
        if (!Storage::exists(self::FOLDER_PROCESS)) {
            Storage::makeDirectory(self::FOLDER_PROCESS, 
                    self::ACCESS_FOLDER);
        }
        @chmod(storage_path(self::FOLDER_APP . '/' . self::FOLDER_PROCESS), self::ACCESS_FOLDER);
        Storage::put(self::FOLDER_UPLOAD.'/'.$id, $id, self::ACCESS_FILE);
        @chmod(storage_path(self::FOLDER_APP.'/'.self::FOLDER_UPLOAD . '/' . $id),
            self::ACCESS_FOLDER);
    }
    
    /**
     * 
     */
    public static function resizeSlide()
    {
        if (self::isProcess()) {
            return true;
        }
        // create process queue
        Storage::put(self::FOLDER_PROCESS . '/' . self::FOLDER_UPLOAD, 
                1, self::ACCESS_FILE);
        @chmod(storage_path(self::FOLDER_APP.'/'.self::FOLDER_PROCESS . 
            '/' . self::FOLDER_UPLOAD), self::ACCESS_FOLDER);
        try {
            // get file resize
            $files = Storage::files(self::FOLDER_UPLOAD);
            if (!$files || !count($files)) {
                self::deleteProcess();
                return;
            }
            foreach ($files as $file) {
                $idSlide = explode('/', $file);
                Storage::delete($file);
                if (!$idSlide) {
                    continue;
                }
                $idSlide = (int) end($idSlide);
                $slide = Slide::find($idSlide);
                if (!$slide) {
                    return;
                }
                $fileOfSlide = File::getFileOfSlide($slide);
                $urlImage = CoreConfigData::get('general.upload_folder') . '/' .  
                    File::PATH_DEFAULT.'/';
                $sizeImageShow = CoreConfigData::getSizeImageShow();
                if (!$fileOfSlide || !count($fileOfSlide)) {
                    continue;
                }
                foreach ($fileOfSlide as $image) {
                    $imageHelper = new ImageHelper();
                    $imageHelper->setImage($urlImage. $image->file_name)
                        ->resizeWatermark($sizeImageShow['width'], $sizeImageShow['height']);
                }
            }
            self::deleteProcess();
        } catch (Exception $ex) {
            self::deleteProcess();
            Log::info($ex);
        }
    }
    
    /**
     * check is process action
     * 
     * @return boolean
     */
    public static function isProcess()
    {
        if (Storage::exists(self::FOLDER_PROCESS . '/' . self::FOLDER_UPLOAD)) {
            return true;
        }
        return false;
    }
    
    /**
     * delete process action
     */
    public static function deleteProcess()
    {
        Storage::delete(self::FOLDER_PROCESS . '/' . self::FOLDER_UPLOAD);
    }
    
    /**
     * check is avali process action
     * 
     * @return boolean
     */
    public static function isProcessAvail()
    {
        $files = Storage::files(self::FOLDER_UPLOAD);
        if (!$files || !count($files)) {
            return false;
        }
        return true;
    }
}
