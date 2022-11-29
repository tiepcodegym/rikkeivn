<?php

namespace Rikkei\Magazine\Lib;

use Rikkei\Magazine\Model\ImageModel;
use Storage;
use Validator;
use Image;

class FileEloquent {
    
    const ACCESS_FOLDER = 0777;

    protected $model;

    public function rules() {
        return [
            'file' => 'mimes:jpeg,jpg,png,bmp,gif|max:10240'
        ];
    }

    public function validator(array $data, array $rules, array $messages = []) {
        $valid = Validator::make($data, $rules, $messages);
        if ($valid->fails()) {
            throw new \Exception($valid->errors()->first('file'));
        }
    }

    public function insert($file, $resize = false) {
        $extension = strtolower($file->getClientOriginalExtension());
        $this->validator(['file' => $file], $this->rules());

        $name = $file->getClientOriginalName();
        $mimetype = $file->getClientMimeType();
        $cut_name = $this->checkRename($name);
        $upload_dir = config('image.magazine_dir', 'magazines/');
        $type = 'image';

        if (!Storage::disk('public')->exists($upload_dir . 'full')) {
            Storage::disk('public')->makeDirectory($upload_dir . 'full', self::ACCESS_FOLDER);
            @chmod(storage_path('app/public/' . $upload_dir . 'full'), self::ACCESS_FOLDER);
        }
        $file = $this->removeExifImage($file);
        $rspath = $upload_dir . 'full/' . $cut_name.'.'.$extension;
        Storage::disk('public')->put($rspath, file_get_contents($file->getRealPath()));

        $item = new ImageModel();
        $item->title = $cut_name;
        $item->url = $cut_name.'.'.$extension;
        $item->type = $type;
        $item->mimetype = $mimetype;
        $item->employee_id = auth()->id();
        $item->save();
        
        if ($resize) {
            $this->resizeImage($item->url);
        }

        return $item;

    }
    
    /**
     * resize image
     * @param type $image_name
     */
    public function resizeImage($image_name) {
        $upload_dir = config('image.magazine_dir', 'magazines/');
        $upload_path = storage_path('app/public/' . $upload_dir);
        list($width, $height) = getimagesize($upload_path . 'full/' . $image_name);
        $ratio = $width / $height;
        $sizes = config('image.image_sizes');
        unset($sizes['full']);
        
        foreach ($sizes as $key => $value) {
            if (!Storage::disk('public')->exists($upload_dir . $key)) {
                Storage::disk('public')->makeDirectory($upload_dir . $key, self::ACCESS_FOLDER);
                @chmod(storage_path('app/public/' . $upload_dir . $key), self::ACCESS_FOLDER);
            }

            $w = $value['width'];
            $h = $value['height'];
            if ($w == null && $h == null) {
                continue;
            }
            $rspath = $upload_dir . $key . '/' . $image_name;
            $crop = $value['crop'];
            $r = ($h == null) ? 0 : $w / $h;
            if ($width > $w || $height > $h) {
                if ($width > $w && $height <= $h) {
                    $rw = $w;
                    $rh = $height;
                } else if ($width <= $w && $height > $h) {
                    $rw = $width;
                    $rh = $h;
                } else if ($ratio > $r) {
                    $rh = $h;
                    $rw = ($h == null) ? $w : $width * $h / $height;
                } else {
                    $rw = $w;
                    $rh = ($w == null) ? $h : $height * $w / $width;
                }

                $rsImage = Image::make($upload_path . 'full/' . $image_name)->resize($rw, $rh, function($constraint) {
                    $constraint->aspectRatio();
                });

                if ($crop) {
                    $sh = round(($rh - $h) / 2);
                    $sw = round(($rw - $w) / 2);
                    $rsImage->crop($w, $h, $sw, $sh);
                }
                Storage::disk('public')->put($rspath, $rsImage->stream(null, 100)->__toString());
            } else {
                if (!Storage::disk('public')->exists($rspath)) {
                    Storage::disk('public')->copy($upload_dir . 'full/' . $image_name, $rspath);
                }
            }
        }
    }

    public function checkRename($originalName) {
        $upload_dir = config('image.magazine_dir', 'magazines/'); 
        $cut_name = $this->cutName($originalName);
        $base_name = $cut_name['name'];
        $re_name = $base_name;
        $i = 1;
        while (Storage::disk('public')->exists($upload_dir.'full/'.$re_name.'.'.$cut_name['ext'])) {
            $re_name = $base_name.'-'.$i;
            $i++;
        }
        return $re_name;
    }
    
    public function cutName($originalName){
        $name_str = explode('.', $originalName);
        $extension = array_pop($name_str);
        return [
            'name' => str_slug(implode('.', $name_str)),
            'ext' => $extension
        ];
    }
    
    /**
     * remove Exif image
     * @param type $file
     * @return boolean
     */
    public static function removeExifImage($file) {
        // check image jpg
        if (exif_imagetype($file->getRealPath()) != IMAGETYPE_JPEG) {
            return $file;
        }
        $exif = @exif_read_data($file->getRealPath(), 'IFD0');
        // check image exif
        if (!$exif || !isset($exif['Orientation'])) {
            return $file;
        }
        $source = imagecreatefromjpeg($file->getRealPath());
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
            imagejpeg($image, $file->getRealPath(), 100);
            imagedestroy($image);
        }
        imagedestroy($source);
        return $file;
    }

}


