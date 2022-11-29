<?php

namespace Rikkei\Api\Http\Controllers\Mobiledata;

use Illuminate\Support\Facades\Log;
use Rikkei\Core\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;
use Storage;

class AdminMobileConfigController extends Controller
{
    const MANAGE_TIME_ATTACHMENT_DIR = 'storage/manage-time/';
    /**
     * List post with ID >= idgte
     * @param null $idgte
     * @return array
     */
    public function store(Request $request)
    {
//        if ($request->hasFile('avatar_url')) {
//            $image      = $request->file('avatar_url');
//            $fileName   = time() . '.' . $image->getClientOriginalExtension();
//            $img = Image::make($image->getRealPath());
//            $img->resize(120, 120, function ($constraint) {
//                $constraint->aspectRatio();
//            });
//            $img->stream();
//            Storage::disk('public_asset_path')->put($fileName, $img);
//            return [
//                'success' => 1,
//            ];
//        }

        if ($request->files && count($request->files)) {
            $data = [];
            $files = $request->file('files');
            foreach ($files as $key=>$file) {
                $fileName = $file->getClientOriginalName();
                Storage::disk('upload')->put($fileName, file_get_contents($file));
                $data['files'][$key]['uploaded'] = true;
                $data['files'][$key]['file'] = self::MANAGE_TIME_ATTACHMENT_DIR . $fileName;
            }
            return [
                'success' => 1,
                'data' => $data
            ];
        }

        if ($request->file_deletes && count($request->file_deletes)) {
            $file_deletes = $request->file_deletes;
            foreach ($file_deletes as $key=>$file) {
                Storage::disk('upload')->delete($file['name']);
            }
            return [
                'success' => 1,
                'data' => $file_deletes
            ];
        }
    }
}
