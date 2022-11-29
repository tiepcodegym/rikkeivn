<?php

namespace Rikkei\Magazine\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\TeamMember;
use DB;
use Storage;

class ImageModel extends CoreModel
{
    protected $table = 'magazine_files';
    protected $fillable = ['title', 'url', 'type', 'mimetype', 'employee_id'];
    protected $appends = ['thumb_src'];

    /**
     * get employee created image
     * @return type
     */
    public function employee(){
        return $this->belongsTo('\Rikkei\Team\Model\Employee', 'employee_id');
    }
    
    /**
     * delete not relationship image
     * @return type
     */
    public static function deleteNotAttach() {
        $relationTbl = 'magazine_images';
        $relate_ids = DB::table($relationTbl)
                ->select('image_id')
                ->groupBy('image_id')
                ->lists('image_id');
        $not_attachs = self::whereNotIn('id', $relate_ids)
                ->where('type', 'image')
                ->get();
        if ($not_attachs->isEmpty()) {
            return;
        }
        foreach ($not_attachs as $image) {
            $image->deleteImage();
        }
    }

    /**
     * get image url by size
     * @param string $size
     * @return type
     */
    public function getSrc($size = 'full', $upload_dir = null){
        $image_sizes = config('image.image_sizes');
        if(!isset($image_sizes[$size])){
            $size = 'full';
        }
        if (!$upload_dir) {
            $upload_dir = config('image.upload_dir', 'magazines/');
        }
 
        $src_file = $upload_dir.$size.'/'.$this->url;
        if (!Storage::disk('public')->exists($src_file)){
            $src_file = $upload_dir.'full/'.$this->url;
            if (Storage::disk('public')->exists($src_file)) {
                return '/storage/'.$src_file;
            }
            return null;
        }
        return '/storage/'.$src_file;
    }
    
    /**
     * get image html by size
     * @param type $size
     * @param type $class
     * @return type
     */
    public function getImage($size='full', $class=null){
        if($src = $this->getSrc($size)){
            return '<img class="img-responsive '.$class.'" src="'.$src.'" alt="No image">';
        }
        return null;
    }
    
    /**
     * get appends attribute
     * @return type
     */
    public function getThumbSrcAttribute() {
        return $this->getSrc('thumbnail');
    }
    
    /**
     * delete image
     */
    public function deleteImage($dir = null) {
        $sizes = config('image.image_sizes');
        if (!$dir) {
            $dir = config('image.magazine_dir', 'magazines/');
        }
        
        foreach (array_keys($sizes) as $key) {
            $path = $dir . $key . '/' . $this->url;
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
        $this->delete();
    }
    /**
     * get test uploaded file
     * @return collection
     */
    public static function getTestUploadedFiles()
    {
        $pager = Config::getPagerData();
        $collection = self::with('employee')
                ->where('type', 'test_image');
        $currentUser = Permission::getInstance()->getEmployee();
        if (Permission::getInstance()->isScopeCompany(null, 'test::admin.get_upload_images')) {
            //do nothing
        } elseif (Permission::getInstance()->isScopeTeam(null, 'test::admin.get_upload_images')) {
            $teamIds = $currentUser->getTeamPositons()->lists('team_id')->toArray();
            $employeeIds = TeamMember::whereIn('team_id', $teamIds)
                    ->lists('employee_id')
                    ->toArray();
            $collection->whereIn('employee_id', $employeeIds);
        } elseif (Permission::getInstance()->isScopeSelf(null, 'test::admin.get_upload_images')) {
            $collection->where('employee_id', $currentUser->id);
        } else {
            //dont have permission
            return collect();
        }
        $collection->orderBy('created_at', 'desc')
                                ->orderBy('id', 'desc');
        self::filterGrid($collection);
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }
    /*
     * check is image file
     */
    public function isImageFile()
    {
        return preg_match('/image\/(.*)/', $this->mimetype);
    }
    /*
     * check is audio file
     */
    public function isAudioFile()
    {
        return preg_match('/audio\/(.*)/', $this->mimetype);
    }
}

