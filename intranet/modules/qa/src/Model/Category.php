<?php

namespace Rikkei\QA\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config as TeamConfig;
use Illuminate\Support\Facades\DB;

class Category extends CoreModel
{
    protected $table = 'qa_categories';
    
    /**
     * get list category
     * 
     * @return type
     */
    public static function getList($option = [])
    {
        $pager = TeamConfig::getPagerDataQuery([
            'order' => 'created_at',
            'dir' => 'desc',
        ]);
        $tableCate = self::getTableName();
        
        $collection = DB::table($tableCate. ' AS t_cate')
            ->select(['t_cate.id', 't_cate.name', 't_cate.content'])
            ->orderBy($pager['order'], $pager['dir']);
        if (isset($option['active'])) {
            $collection->where('t_cate.active', $option['active']);
        }
        return self::pagerCollection($collection, $pager['limit'], $pager['page']);
    }
}