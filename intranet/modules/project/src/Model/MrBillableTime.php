<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\Builder;

class MrBillableTime extends CoreModel
{
    protected $table = 'proj_billable_report_time';
    protected $fillable = ['report_id', 'time', 'billable', 'allocate', 'approved_cost', 'note'];
    protected $primaryKey = ['report_id', 'time'];
    public $timestamps = false;
    public $incrementing = false;

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

    /**
     * check exists row
     * @param int $reportId
     * @param string $time
     * @return object|boolean
     */
    public static function checkExists($reportId, $time)
    {
        return self::where('report_id', $reportId)
                ->where('time', $time)
                ->first();
    }

    /**
     * create or update data
     * @param array $data
     * @return object
     */
    public static function insertOrUpdate($data)
    {
        $item = self::checkExists($data['report_id'], $data['time']);
        if ($item) {
            $item->update($data);
        } else {
            $item = self::create($data);
        }
        return $item;
    }
}

