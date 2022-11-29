<?php

namespace Rikkei\Music\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\Form as FormCore;
use Rikkei\Team\View\Config as TeamConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class MusicOrder extends CoreModel
{
    protected $table = 'music_orders';

    const IS_PLAYED = 1;
    const NOT_PLAYED = 0;

    use SoftDeletes;

    /**
    * get list of music order
    */ 
    public static function getGridData()
    {
        $tableOder = self::getTableName();
        $tableOderVote = MusicOrderVote::getTableName();
        $tableOffice = MusicOffice::getTableName();

        $pager = TeamConfig::getPagerData();
        $collection = self::select($tableOder.'.id', $tableOder.'.name', $tableOder.'.link',
                $tableOder.'.sender', $tableOder.'.receiver', $tableOder.'.message',
                $tableOder.'.is_play', $tableOder.'.created_at', $tableOffice.'.name as office_name');
        if (FormCore::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy( "{$tableOder}.created_at",'DESC');
        }
        $collection->join($tableOffice, $tableOder.'.office_id', '=', $tableOffice.'.id');
        self::filterGrid($collection,[],null,'LIKE');
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
    * delete music order
    */ 
    public static function delOrder($orderId)
    {
        self::where('id', '=', $orderId)->delete();
    }
    
    /**
     * Save Order
     * 
     * @param array $options
     * @return type
     * 
     */
    public function save(array $options = [])
    {
        DB::beginTransaction();
        try {
            $this->created_by = Permission::getInstance()->getEmployee()->id;
            $result = parent::save($options);
            DB::commit();
            return $result;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
    
    
    /**
     * Get order by office id
     * 
     * @param int $office_id
     * @return array object
     */
    public static function getOrderData($officeId=null)
    {
        $tableOder = self::getTableName();
        $tableOderVote = MusicOrderVote::getTableName();

        $pager = TeamConfig::getPagerData();
        $collection = self::select($tableOder.'.id', $tableOder.'.name', $tableOder.'.link',
                $tableOder.'.sender', $tableOder.'.receiver', $tableOder.'.message',
                $tableOder.'.is_play', $tableOder.'.created_at');
        
        $collection->orderBy("{$tableOder}.created_at", 'DESC')
                ->groupBy($tableOder . '.id')
                ->where($tableOder . '.office_id', '=', $officeId);
        return $collection;
    }
    
    /**
     * Update is_play of order
     * 
     * @param int $id
     */
    public static function isPlay($id = null)
    {
        $order = self::find($id);
        $order->is_play = self::IS_PLAYED;
        $order->save();
    }

    /**
    * get order for email
    */
    public static function getOrder($officeId) 
    {
        $tableOrder = self::getTableName();
        $tableOrderVote = MusicOrderVote::getTableName();
        return self::leftJoin($tableOrderVote, $tableOrder.'.id', '=', $tableOrderVote.'.music_order_id')
            ->select($tableOrder.'.id', $tableOrder.'.name', $tableOrder.'.link')
            ->where($tableOrder.'.office_id','=',$officeId)
            ->groupBy($tableOrder.'.id')
            ->orderBy(DB::raw("DATE_FORMAT(`{$tableOrder}`.`created_at`, '%Y-%m-%d')"),'DESC')
            ->orderBy(DB::raw('COUNT(DISTINCT('.$tableOrderVote.'.employee_id))'),'DESC')
            ->limit(2)
            ->get();
    }

    /**
    * delete order follow many Id
    */
    public static function delMany($officeIds=array()){
        self::whereIn('id',$officeIds)->delete();
    }

}
