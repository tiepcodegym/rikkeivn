<?php
namespace Rikkei\Resource\Model;

use Rikkei\Core\Model\CoreModel;
use DB;
use Rikkei\Team\Model\Team;

class RequestType extends CoreModel
{

    protected $table = 'request_type';

    /**
     * store this object
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

    /**
     * Delete all type of request
     *
     * @param int $requestId
     * @return void
     */
    public static function removeOldTypeOfRequest($requestId)
    {
        self::where('request_id', $requestId)->delete();
    }

    public static function insertData($data)
    {
        self::insert($data);
    }

    /**
     * Get all candidate type of request
     *
     * @param int $requestId
     * @return RequestType collection
     */
    public static function getTypeOfRequest($requestId)
    {
        return self::where('request_id', $requestId)->get();
    }
}
