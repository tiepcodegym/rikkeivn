<?php

namespace Rikkei\SlideShow\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\View;
use Illuminate\Support\Facades\Config;
use Rikkei\Team\View\Config as ConfigView;


class VideoDefault extends CoreModel
{
   /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'video_default';

    const PATH_VIDEO_DEFAULT = 'slide_show/videos';

    public static function uploadVideo($input)
    {
        // dd($input);
        // $path = self::PATH_VIDEO_DEFAULT;
        if(isset($input['id'])) {
            $video = self::getVideoDefaultBygId($input['id']);
            $fileNameOld = $video->file_name;
        } else {
            $video = new VideoDefault;
        }
        // $common = new View();
        // if (isset($input['video'])) {
        //     $video_name = $common->uploadFile($input['video'],
        //                     Config::get('general.upload_storage_public_folder') . 
        //                     '/' . $path);
        //     $video->file_name = $video_name;
        // }
        $video->title = $input['title'];
        $video->file_name = $input['url'];
        if ($video->save()) {
            // if (isset($input['video']) && isset($input['id'])) {
            //     $common->deleteFile(trim(Config::get('general.upload_storage_public_folder') . 
            //                     '/' . $path, '/').'/'.$fileNameOld);
            // }
            return $video->id;
        }
    }

    public static function getVideoDefaultBygId($id) {
        return self::find($id);
    }

    public static function getGridData()
    {
        $pager = ConfigView::getPagerData();
        $collection = self::select(['id', 'file_name', 'title'])
                           ->orderBy($pager['order'], $pager['dir']);
        self::filterGrid($collection);
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    public static function getValueDefault()
    {
        return self::inRandomOrder()->first();
    }
}
