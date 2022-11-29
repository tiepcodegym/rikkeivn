<?php

namespace Rikkei\Magazine\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\Form;
use Rikkei\Team\View\Config;
use Carbon\Carbon;

class Magazine extends CoreModel
{
    const MAGAZINE = 1;
    const DOCUMENT = 2;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'magazine';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'name', 'slug', 'type'];
    
    /**
     * get images
     * @return type
     */
    public function images() {
        return $this->belongsToMany('\Rikkei\Magazine\Model\ImageModel', 'magazine_images', 'magazine_id', 'image_id')
                ->withPivot('order', 'is_background');
    }
    
    public static function getGridData() {
        $pager = Config::getPagerData();
        if (Form::getFilterPagerData('order')) {
            $collection = self::orderBy($pager['order'], $pager['dir']);
        } else {
            $collection = self::orderBy('created_at', 'desc');
        }
        $collection->where('type', self::MAGAZINE);
        $collection = self::filterGrid($collection);
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }
    
    /**
     * get list in news
     * @param type $options
     * @return type
     */
    public static function getLists($options) {
        $pager = Config::getPagerDataQuery();
        $collection = self::select('id', 'name', 'slug', 'created_at')
                ->with(['images' => function ($query) {
                    $query->orderBy('is_background', 'desc')
                            ->orderBy('order', 'asc');
                }]);
        if (isset($options['search']) && $search = $options['search']) {
            $collection->where('name', 'REGEXP', addslashes($search));
        }
        return $collection->where('type', self::MAGAZINE)->orderBy('created_at', 'desc')->get();
    }
    
    /**
     * format date
     * @return type
     */
    public function getPublicDate()
    {
        if (!$this->created_at) {
            return null;
        }
        $date = Carbon::parse($this->created_at);
        return $date->format('F j, Y');
        
    }
    
}

