<?php
namespace Rikkei\Resource\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\CacheHelper;
use DB;
use Illuminate\Support\Facades\Validator;

class RequestChannel extends CoreModel
{
    
    protected $table = 'request_channel';
    
    const KEY_CACHE = 'request_channel';
    
    protected $fillable = ['request_id', 'channel_id', 'url', 'cost'];
    
    /**
     *  store this object
     * @var object
     */
    protected static $instance;
    
    public static function getInstance()
    {
        if (! isset(self::$instance)) {
            self::$instance = new static;
        }
        return self::$instance;
    }
    
    public function getRow($requestId, $channelId) {
        return self::where('request_id',$requestId)->where('channel_id',$channelId)
                    ->select('*')->first();
    }
    
    public function saveData($data, $requestId) {
        DB::beginTransaction();
        try {
            self::where('request_id', $requestId)->delete();
            self::insert($data);
            DB::commit();
        } catch (QueryException $ex) {
            DB::rollback();
            throw $ex;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
    
    public function checkExist($requestId, $channelId) {
        return self::where([['request_Id', $requestId], ['channel_id', $channelId]])->count();
    }
    
    public function store($input) {
        if (isset($input['old_channel_id'])) {
            $rc = self::getInstance()->getRecord($input['request_id'], $input['old_channel_id']);
        } else {
            $rc = new RequestChannel();
        }
        unset($input['old_channel_id']);
        $rc->fill($input);
        DB::beginTransaction();
        try {
            $rc->save();
            DB::commit();
            return $rc->id;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
        
    }
    
    public function getRecord($requestId, $channelId) {
        return self::where([['request_Id', $requestId], ['channel_id', $channelId]])->select('*')->first();
    }
    
    public static function deleteChannelRequest($channelId, $requestId) {
        $rc = self::getInstance()->getRecord($requestId, $channelId);
        DB::beginTransaction();
        try {
            $rc->delete();
            DB::commit();
            return true;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
   
    public function getTotalCostByChannel($channelId) {
        return self::where('channel_id', $channelId)
                ->sum('cost');
    }
}