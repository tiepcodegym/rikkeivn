<?php

namespace Rikkei\Music\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Permission;


class MusicOrderVote extends CoreModel
{
    public $timestamps = false;
    
    protected $fillable = ['employee_id', 'music_order_id'];
    
    protected $table = 'music_order_vote';
    
    /**
    * Vote
    */
    public static function vote($orderId)
    {
        $oldVote = self::where('employee_id', '=',
            Permission::getInstance()->getEmployee()->id)->where('music_order_id','=',$orderId);
        if($oldVote->count() >= 1 ) {
            $oldVote->delete();
        } else{
            self::create(['employee_id' =>
                Permission::getInstance()->getEmployee()->id,'music_order_id' => $orderId]);
        }
    }
    
    /**
    * get total vote
    */
    public static function getTotalVote($orderId)
    {
        return self::where('music_order_id','=',$orderId)->count();
    }
}