<?php

namespace Rikkei\News\Model;

use Illuminate\Support\Facades\URL;
use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Core\View\CoreImageHelper;
use Illuminate\Support\Facades\Config;

class Poster extends CoreModel
{
    use SoftDeletes;

    const STATUS_ACTIVE = 2;
    const STATUS_INACTIVE = 1;

    protected $fillable = ['status', 'title', 'order', 'start_at', 'end_at', 'image', 'slug', 'is_gif', 'link'];

    public static function getStatus()
    {
        return [
            self::STATUS_ACTIVE => 'news::view.status_active',
            self::STATUS_INACTIVE => 'news::view.status_inactive',
        ];
    }

    public static function getStatusLabel()
    {
        return [
            self::STATUS_ACTIVE => 'label label-success',
            self::STATUS_INACTIVE => 'label label-warning',
        ];
    }

    /**
     * get url asset of post
     *
     * @return string
     */
    public function getImage($noImage = null)
    {
        if ($noImage) {
            $noImage = URL::asset('common/images/noimage.png');
        }
        if (!$this->image) {
            return $noImage;
        }
        if (!file_exists(public_path($this->image))) {
            return $noImage;
        }
        return URL::asset($this->image);
    }

    public function getThumbnail($noImage = true)
    {
        if (!$this->is_gif) {
            $image = CoreImageHelper::getInstance()
                ->setImage($this->image)
                ->resize(Config::get('image.news.size_thumbnail_width'),
                    Config::get('image.news.size_thumbnail_height'));

            if (!$image && $noImage) {
                return URL::asset('common/images/noimage.png');
            }
            return $image;
        }

        return URL::asset($this->image);
    }
}
