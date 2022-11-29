<?php

namespace Rikkei\Me\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\Builder;

class Point extends CoreModel
{
    protected $table = 'me_points';
    protected $fillable = ['eval_id', 'attr_id', 'point'];
    protected $primaryKey = ['eval_id', 'attr_id'];
    public $timestamps = false;
    public $incrementing = false;

    private static $instance = null;

    /**
     * get instance of this class
     * @return object
     */
    public static function getInstance()
    {
        if (self::$instance !== null) {
            return self::$instance;
        }
        self::$instance = new self();
        return self::$instance;
    }

    /*
     * save attribute point
     */
    public function savePoint($evalId, $attrId, $point)
    {
        $item = self::where('eval_id', $evalId)
                ->where('attr_id', $attrId)
                ->first();
        if (!$item) {
            $item = new static();
            $item->eval_id = $evalId;
            $item->attr_id = $attrId;
        }
        $item->point = $point;
        $item->save();
        return $item;
    }

    /**
     * set custom primary key save
     */
    protected function setKeysForSaveQuery(Builder $query)
    {
        $keys = $this->primaryKey;
        foreach ($keys as $keyName) {
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }
        return $query;
    }

    /*
     * get custom primary key save
     */
    protected function getKeyForSaveQuery($keyName = null)
    {
        if (is_null($keyName)) {
            $keyName = $this->getKeyName();
        }
        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }
        return $this->getAttribute($keyName);
    }

    public function getPointByEvalIds($evalIds = [])
    {
        return self::whereIn('eval_id', $evalIds)
                ->get()
                ->groupBy('eval_id');
    }

}
