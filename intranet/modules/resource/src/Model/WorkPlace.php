<?php
namespace Rikkei\Resource\Model;

use Rikkei\Core\Model\CoreModel;

class WorkPlace extends CoreModel
{
    public $timestamps = false;
    protected $table = 'work_places';
    protected $fillable = ['name'];
    protected static $instance;

    /**
     * get list
     * @return collection
     */
    public function getList()
    {
        return self::orderBy('name', 'asc')->pluck('name', 'id')->toArray();
    }

    /**
     * get instance
     *
     * @return \self
     */
    public static function getInstance()
    {
        if (! isset(self::$instance)) {
            self::$instance = new static;
        }
        return self::$instance;
    }

}