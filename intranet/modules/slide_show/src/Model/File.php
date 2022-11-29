<?php

namespace Rikkei\SlideShow\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Support\Facades\Config;
use Rikkei\SlideShow\View\ImageHelper;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\View\View;
use Rikkei\SlideShow\View\RunBgSlide;

class File extends CoreModel
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'file';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['slide_id', 'file_name', 'description', 'type_effect'];

    const TYPE_REPEAT_HOURLY = 1;
    const TYPE_REPEAT_DAILY = 2;
    const TYPE_REPEAT_WEEKLY = 3;

    const PATH_DEFAULT = 'slide_show/images';
    const PATH_VIDEO_DEFAULT = 'slide_show/videos';
    const PATH_DEFAULT_IMAGE_CACHE = 'slide_show/images/cache';
    const PATH_DEFAULT_IMAGE_RENDER = 'slide_show/images/render';
    /**
     * get all file for slide
     * @param array
     * @return array
     */
    public static function getAllFileForSlide($slide)
    {
        $files = self::where('slide_id', $slide->id);
        if($slide->type == Slide::TYPE_IMAGE) {
            $files = $files->get();
            $pathFolder = url('/') . '/' . Config::get('general.upload_folder') . '/' .  File::PATH_DEFAULT.'/';
            foreach ($files as $key => &$file) {
                $file['full_file_name'] = $pathFolder. $file->file_name;
            }
        } else {
            $files = $files->first();
            // $pathFolder = url('/') . '/' . Config::get('general.upload_folder') . '/' .  File::PATH_VIDEO_DEFAULT.'/';
            // $files['file_name'] = $files->file_name;
        }
        return $files;
    }

    /**
     * get file by name
     * @param string
     * @return file
     */
    public static function getFileByFileName($fileName) {
        return self::where('file_name', $fileName)->first();
    }

    /**
     * get file of slide
     * @param array
     * @return array
     */
    public static function getFileOfSlide($slide) {
        if ($slide) {
            $files = self::where('slide_id', $slide->id);
            if ($slide->option == Slide::OPTION_NOMAL) {
                if ($slide->type == Slide::TYPE_IMAGE) {
                    return $files->get();
                }
                return $files->first();
            } else {
                return $files->get();
            }
        }
        return null;
    }

    /**
     * get all file for slide resize
     * @param array
     * @param string
     * @return array
     */
    public static function getAllFileForSlideRezise($slide, $urlImage)
    {
        $imageHelper = new ImageHelper();
        $sizeImage = CoreConfigData::getSizeImagePreviewDetail();
        $files = self::where('slide_id', $slide->id);
        if ($slide->option == Slide::OPTION_NOMAL) {
            if($slide->type == Slide::TYPE_IMAGE) {
                $files = $files->get();
                foreach ($files as $key => &$file) {
                    $url = $imageHelper->setImage($urlImage. $file->file_name)->resize($sizeImage['width'], $sizeImage['height']);
                    $file['full_file_name'] = $url;
                }
            } else {
                $files = $files->first();
            }
        } else {
            $files = $files->get();
            foreach ($files as $key => &$file) {
                $url = $imageHelper->setImage($urlImage. $file->file_name)->resize($sizeImage['width'], $sizeImage['height']);
                $file['full_file_name'] = $url;
            }
        }
        return $files;
    }

    /**
     * insert file
     * @param array
     * @param array
     */
    public static function insertFile($slide, $input)
    {
        if (isset($input['number_file'])) {
            $uploadFolderImage = self::PATH_DEFAULT;
            $uploadFolderVideo = File::PATH_VIDEO_DEFAULT;
            $numberFile = $input['number_file'];
            $common = new View();
            $img = '';
            $listImageString = array();
            $listOldImage = File::where('slide_id', $slide->id)->lists('file_name')->toArray();
            for ($i = 0; $i < $numberFile; $i++) {
                // Upload file image
                if (is_string($input['image_' . $i])) {
                    array_push($listImageString, $input['image_' . $i]);
                    $file = File::getFileByFileName($input['image_' . $i]);
                    if ($file) {
                        if(isset($input['description_image'])) {
                            $file->description = $input['description_image'][$i];
                            $file->save();
                        }
                    }
                } else {
                    $file = new File();
                    $img = $common->uploadFile($input['image_' . $i],
                        Config::get('general.upload_storage_public_folder') . 
                        '/' . $uploadFolderImage,
                        Config::get('services.file.image_allow'), null, true, 
                        ['remove_exif' => true]);
                    $file->file_name = $img;
                    if(isset($input['description_image'])) {
                        $file->description = $input['description_image'][$i];
                    }
                    $file->slide_id = $slide->id;
                    $file->save();
                    array_push($listImageString, $img);
                }
            }
            //delete image in db
            File::where('slide_id', $slide->id)
                ->whereNotIn('file_name', $listImageString)
                ->delete();
            for ($t = 0; $t < count($listOldImage); $t++) {
                $checkExist = false;
                for ($k = 0; $k < count($listImageString); $k++) {
                    if ($listOldImage[$t] == $listImageString[$k]) {
                        $checkExist = true;
                    }
                }
                if (!$checkExist) {
                    $common->deleteFile(trim(Config::get('general.upload_storage_public_folder') . 
                        '/' . $uploadFolderVideo, '/').'/'.$listOldImage[$t]);
                    $common->deleteFile(trim(Config::get('general.upload_storage_public_folder') . 
                        '/' . $uploadFolderImage, '/').'/'.$listOldImage[$t]);
                }
            }
        }
        RunBgSlide::createProcessBg($slide->id);
    }
}