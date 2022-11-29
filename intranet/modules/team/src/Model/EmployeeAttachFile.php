<?php

namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class EmployeeAttachFile extends CoreModel
{
    public $timestamps = false;
    protected $table = 'employee_attach_files';
    protected $fillable = [
        'attach_id', 'file_id', 'file_name', 'path', 'type', 'file_size', 'created_at'
    ];

    /**
     * insert attach file
     *
     * @param int $attachId
     * @param array $data
     */
    public static function insertAttachFile($attachId, array $data)
    {
        $now = Carbon::now();
        $data['created_at'] = $now->__toString();
        $data['file_id'] = str_random(5).$now->timestamp;
        $data['attach_id'] = $attachId;
        self::create($data);
        return $data['file_id'];
    }

    /**
     * get file of 
     *
     * @param type $attachId
     * @return type
     */
    public static function getFiles($attachId)
    {
        return self::select(['file_id', 'file_name', 'path', 'file_size'])
            ->where('attach_id', $attachId)
            ->orderBy('created_at', 'ASC')
            ->get();
    }

    /**
     * delete attach file
     *
     * @param int $attachId
     * @param inyt $fileId
     * @return boolean
     */
    public static function deleteAttachFile($attachId, $fileId)
    {
        $item = self::where('attach_id', $attachId)
            ->where('file_id', $fileId)
            ->first();
        if (!$item) {
            return true;
        }
        Storage::disk('public')->delete($item->path);
        return self::where('attach_id', $attachId)
            ->where('file_id', $fileId)
            ->delete();
    }
}
