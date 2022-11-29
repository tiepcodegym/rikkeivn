<?php

namespace Rikkei\News\Model;

use Carbon\Carbon;
use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config as SupportConfig;
use Rikkei\Core\View\View;

class PostAttach extends CoreModel
{
    use SoftDeletes;

    private static $instance;

    protected $table = 'blog_post_attach';
    const UPLOAD_ATTACH = 'post';

    /**
     * upload file post
     */
    public static function uploadFiles($postId, $attach)
    {
        $store = [];
            if ($attach) {
                if (in_array($attach->getClientOriginalExtension(), ['mp3'])) {
                    $name = View::uploadFile(
                        $attach,
                        SupportConfig::get('general.upload_storage_public_folder') .
                        '/' . 'post',
                        SupportConfig::get('services.file.audio_allow'),
                        SupportConfig::get('services.file.audio_max'),
                        false
                    );
                } else {
                    $name = View::uploadFile(
                        $attach,
                        SupportConfig::get('general.upload_storage_public_folder') .
                        '/' . self::UPLOAD_ATTACH,
                        SupportConfig::get('services.file.cv_allow'),
                        SupportConfig::get('services.file.cv_max'),
                        false
                    );
                }
                $store[] = [
                    'post_id' => $postId,
                    'path' => trim(self::UPLOAD_ATTACH, '/') . '/' . $name,
                ];
            }
        self::insert($store);
    }

    /**
     * get file post
     */
    public static function getFilePost($id)
    {
        $postAttachTable = self::getTableName();
        return self::select("{$postAttachTable}.*")
            ->where("{$postAttachTable}.post_id", $id)
            ->whereNull("{$postAttachTable}.deleted_at")
            ->first();
    }

    /**
     * deleted file post
     */
    public static function deleteFileAttach($request)
    {
        if (isset($request->fileId)) {
            $post = self::where('id', $request->fileId)->first();
            $post->deleted_at = Carbon::now()->format('Y-m-d');
            $post->save();
            return $post->id;
        }
        return false;
    }
}
