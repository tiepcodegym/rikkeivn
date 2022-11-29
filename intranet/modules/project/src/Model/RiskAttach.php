<?php

namespace Rikkei\Project\Model;

use Carbon\Carbon;
use Rikkei\Core\Model\CoreModel;
use Illuminate\Support\Facades\Config as SupportConfig;
use Rikkei\Project\Model\Risk;
use Rikkei\Core\View\View;

class RiskAttach extends CoreModel
{

    protected $table = 'risk_attachs';

    public $timestamps = false;

    const TYPE_ISSUE = 1;
    const TYPE_RISK = 2;
    const TYPE_COMMENT = 3;
    const TYPE_OTHERS = 4;
    const TYPE_NC = 5;

    public static function uploadFiles($objectId, $attachs, $type)
    {
        $store = [];
        foreach ($attachs as $attach) {
            if ($attach) {
                if (in_array($attach->getClientOriginalExtension(), ['jpg', 'jpeg', 'png'])) {
                    $name = View::uploadFile(
                        $attach,
                        SupportConfig::get('general.upload_storage_public_folder') .
                        '/' . Risk::UPLOAD_ATTACH,
                        SupportConfig::get('services.file.image_allow'),
                        SupportConfig::get('services.file.image_max'),
                        false
                    );
                } else {
                    $name = View::uploadFile(
                        $attach,
                        SupportConfig::get('general.upload_storage_public_folder') .
                        '/' . Risk::UPLOAD_ATTACH,
                        SupportConfig::get('services.file.cv_allow'),
                        SupportConfig::get('services.file.cv_max'),
                        false
                    );
                }
                $store[] = [
                    'obj_id' => $objectId,
                    'type' => $type,
                    'path' => trim(Risk::UPLOAD_ATTACH, '/') . '/' . $name,
                ];
            }
        }
        self::insert($store);
    }

    public static function deleteFileAttach($request)
    {
        if (isset($request->fileId)) {
            $risk = self::where('id', $request->fileId)->first();
            $risk->deleted_at = Carbon::now()->format('Y-m-d');
            $risk->save();
            return $risk->id;
        }
        return false;
    }

    public static function getAttachs($objectId, $type)
    {
        return self::where('obj_id', $objectId)
                ->where('type', $type)
                ->whereNull('deleted_at')
                ->get();
    }
}
