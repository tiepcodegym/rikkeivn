<?php
namespace Rikkei\Resource\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\CacheHelper;

class RequestProgramming extends CoreModel
{
    
    protected $table = 'request_programming';
    
    const KEY_CACHE = 'request_programming';
    
    /**
     *  store this object
     * @var object
     */
    protected static $instance;
    
    /**
     * get list
     * 
     * @return objects
     */
    public function getList()
    {
        if ($item = CacheHelper::get(self::KEY_CACHE)) {
            return $item;
        }
        return self::orderBy('name', 'asc')->select('*')->get();
    }
    
    public static function getInstance()
    {
        if (! isset(self::$instance)) {
            self::$instance = new static;
        }
        return self::$instance;
    }
    
}