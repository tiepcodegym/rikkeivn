<?php

namespace Rikkei\TestOld\Models;

use Rikkei\Core\Model\CoreModel;
use Rikkei\TestOld\Models\Cat;
use Rikkei\Team\View\Config;

class Test extends CoreModel
{
    protected $table = 'md_test_tests';
    protected $fillable = ['type', 'name', 'slug', 'link', 'time', 'cat_id'];
    
    /**
     * get collection to show grid data
     * 
     * @return collection model
     */
    public static function getGridData()
    {
        $pager = Config::getPagerData();
        $pager['order'] = 'name';
        $collection = self::select('id', 'name', 'link', 'time', 'cat_id')
            ->orderBy($pager['order'], $pager['dir']);
        $collection = self::filterGrid($collection);
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }
    
    public function getAll($args=[]){
        $opts = [
            'orderby' => 'name',
            'order' => 'asc',
            'key' => '',
            'per_page' => -1,
            'cat_ids' => [],
        ];
        
        $opts = array_merge($opts, $args);
        
        $result = self::where('name', 'like', '%'.$opts['key'].'%');
        if ($opts['cat_ids']) {
            $cat_ids = $this->inCats($opts['cat_ids']);
            $result = $result->whereIn('cat_id', $cat_ids);
        }
        $result = $result->orderBy($opts['orderby'], $opts['order']);
        if ($opts['per_page'] < 0) {
            return $result->get();
        }
        return $result->paginate($opts['per_page']);
    }
    
    public function inCats($cat_ids){
        if (!is_array($cat_ids)) {
            $cat_ids = [$cat_ids];
        }
        $ids = Cat::whereIn('parent_id', $cat_ids)->lists('id')->toArray();
        $result = array_merge($cat_ids, $ids);
        if($ids){
            $result = array_unique(array_merge($result, $this->inCats($ids)));
        }
        return $result;
    }
    
    public function cat() {
        return $this->belongsTo('\Rikkei\TestOld\Models\Cat', 'cat_id', 'id');
    }
    
    public function catName() {
        if ($this->cat) {
            return $this->cat->name;
        } else if ($this->type == 2) {
            return 'GMAT';
        } else {
            return null;
        }
    }
    
}
