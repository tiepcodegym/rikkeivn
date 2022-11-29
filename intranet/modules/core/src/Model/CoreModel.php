<?php

namespace Rikkei\Core\Model;

use Rikkei\Core\View\Form;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\Builder;
use Rikkei\Core\Events\DBEvent;

class CoreModel extends \Illuminate\Database\Eloquent\Model
{
    /*
     * error code exception manual
     */

    const ERROR_CODE_EXCEPTION = 9462;

    /*
     * flag boolean value
     */
    const FLAG_TRUE = 1;
    const FLAG_FALSE = 2;

    /*
     * Wildcard for concat, group_concat
     */
    const CONCAT = ",,";
    const GROUP_CONCAT = ";;";
    const REQUEST_TIMEOUT = 30000;

    /**
     * set data for a model
     * 
     * @param array $data
     * @return \Rikkei\Core\Model\CoreModel
     */
    public function setData(array $data = array(), $notSetNull = false)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            if ($notSetNull && $value === null && $this->{$key} !== null) {
                //not set value
            } else {
                $this->{$key} = $value;
            }
        }
        return $this;
    }

    /**
     * filter grid action
     * 
     * @param model $collection
     * @param array|null $except
     * @param mix $urlSubmitFilter url filter or data filter
     * @return model
     */
    public static function filterGrid(&$collection, $except = [], $urlSubmitFilter = null, $compare = 'REGEXP')
    {
        if (is_array($urlSubmitFilter)) {
            $filter = $urlSubmitFilter;
        } else {
            $filter = Form::getFilterData(null, null, $urlSubmitFilter);
        }
        if ($filter && count($filter)) {
            foreach ($filter as $key => $value) {
                if (in_array($key, $except)) {
                    continue;
                }
                if (is_array($value)) {
                    if ($key == 'number' && $value) {
                        foreach ($value as $col => $filterValue) {
                            if ($filterValue === '') {
                                continue;
                            }
                            if ($filterValue == 'NULL') {
                                $collection = $collection->whereNull($col);
                            } else {
                                $collection = $collection->where($col, $filterValue);
                            }
                        }
                    } elseif ($key == 'in' && $value) {
                        foreach ($value as $col => $filterValue) {
                            $collection = $collection->whereIn($col, $filterValue);
                        }
                    } elseif ($key == 'date' && $value) {
                        foreach ($value as $col => $filterValue) {
                            if ($filterValue == 'NULL') {
                                $collection = $collection->whereNull($col);
                            } elseif (preg_match('/^[0-9\-\:\s]+$/', $filterValue)) {
                                $collection = $collection->where($col, $filterValue);
                            }
                        }
                    } else {
                        if (isset($value['from']) && $value['from']) {
                            $collection = $collection->where($key, '>=', $value['from']);
                        }
                        if (isset($value['to']) && $value['to']) {
                            $collection = $collection->where($key, '<=', $value['to']);
                        }
                    }
                } else {
                    $value = trim($value);
                    if ($value == '') {
                        continue;
                    }
                    switch ($compare) {
                        case 'LIKE':
                            $collection = $collection->where($key, $compare, addslashes("%$value%"));
                            break;
                        default:
                            $collection = $collection->where($key, $compare, addslashes("$value"));
                    }
                }
            }
        }
        return $collection;
    }

    /**
     * Convert Query builder to Sql string
     *
     * @param $builder
     * @return string
     */
    public function builderToSql($builder)
    {
        $query = str_replace(['?'], ['\'%s\''], $builder->toSql());
        return vsprintf($query, $builder->getBindings());
    }

    /**
     * pagination collection
     * 
     * @param object $collection
     * @param int $limit
     * @param int $page
     * @return collection
     */
    public static function pagerCollection(&$collection, $limit, $page)
    {
        return $collection = $collection->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * get table name of model
     * 
     * @return string
     */
    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    /**
     * get list column fillable
     * @return array
     */
    public static function getFillableCols()
    {
        return with(new static)->getFillable();
    }

    /*
     * get all columns in table
     */

    public function getDBColumns()
    {
        return \Schema::getColumnListing($this->getTable());
    }

    /**
     * flag value to boolean
     * 
     * @param int $value
     * @return boolean
     */
    public static function flagToBoolean($value)
    {
        if ($value == self::FLAG_TRUE) {
            return true;
        }
        return false;
    }

    /**
     * boolean to flag
     * 
     * @param boolean $boolean
     * @return int
     */
    public static function booleanToFlag($boolean)
    {
        if ($boolean) {
            return self::FLAG_TRUE;
        }
        return self::FLAG_FALSE;
    }

    /**
     * rewrite __call funciton of modal
     *  - check method start string: get
     * 
     * @param type $method
     * @param type $parameters
     * @return type
     */
    public function __call($method, $parameters)
    {
        if (preg_match('/^get[A-Z]/', $method)) {
            return null;
        }
        return parent::__call($method, $parameters);
    }

    /**
     * check model use soft delete
     * 
     * @return boolean
     */
    public static function isUseSoftDelete()
    {
        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses(with(new static)))) {
            return true;
        }
        return false;
    }

    /**
     * filter grid action
     * 
     * @param model $collection
     * @param array|null $filter
     * @return model
     */
    public static function filterDataNormal(&$collection, $filter = [])
    {
        if ($filter && count($filter)) {
            foreach ($filter as $key => $value) {
                if (is_array($value)) {
                    if (isset($value['from']) && $value['from']) {
                        $collection->where($key, '>=', $value['from']);
                    }
                    if (isset($value['to']) && $value['to']) {
                        $collection->where($key, '<=', $value['to']);
                    }
                    if (isset($value['date']) && $value['date']) {
                        if (isset($value['date']['format']) && $value['date']['format']) {
                            $dateFormat = $value['date']['format'];
                            $dateValue = $value['date']['value'];
                        } else {
                            $dateFormat = 'd/m/Y';
                            $dateValue = $value['date'];
                        }
                        $dateValue = preg_split('/\-|\//', $dateValue);
                        $dateFormat = preg_split('/\-|\//', $dateFormat);
                        if (!$dateFormat || !count($dateFormat)) {
                            continue;
                        }
                        $dateValueString = '';
                        $dateFormatIndex = array_search('Y', $dateFormat);
                        if (isset($dateValue[$dateFormatIndex]) && $dateValue[$dateFormatIndex]) {
                            $dateValueString .= $dateValue[$dateFormatIndex];
                        }
                        $dateFormatIndex = array_search('m', $dateFormat);
                        if (isset($dateValue[$dateFormatIndex]) && $dateValue[$dateFormatIndex]) {
                            $dateValueString .= '-' . $dateValue[$dateFormatIndex];
                        }
                        $dateFormatIndex = array_search('d', $dateFormat);
                        if (isset($dateValue[$dateFormatIndex]) && $dateValue[$dateFormatIndex]) {
                            $dateValueString .= '-' . $dateValue[$dateFormatIndex];
                        }
                        $dateValueString = trim($dateValueString, '-');
                        $collection->where($key, 'like', "%{$dateValueString}%");
                    }
                } else {
                    $collection->where($key, 'like', "%{$value}%");
                }
            }
        }
        return $collection;
    }

    /**
     * get value of attributes
     * 
     * return array
     */
    public function getValueOfAtt(array $attr)
    {
        $result = [];
        foreach ($attr as $item) {
            $result[$item] = $this->{$item};
        }
        return $result;
    }

    /**
     * override insert method
     * @param array $data
     * @return bool
     */
    public static function insert($data)
    {
        $model = new static;
        $inserted = $model->newQuery()->insert($data);
        Event::fire(new DBEvent('inserted', static::getTableName(), $data));
        return $inserted;
    }

    /**
     * override medthod boot
     * fire event dblog
     */
    public static function boot() {
        parent::boot();

        // event created
        static::created(function ($model) {
            Event::fire(new DBEvent('created', $model->getTableName(), $model->getAttributes()));
        });

        // event udpated
        static::deleted(function ($model) {
            Event::fire(new DBEvent('deleted', $model->getTableName(), $model->getAttributes()));
        });
    }

    /**
     * save log performUpdate function
     * @param Builder $query
     * @param array $options
     * @return boolean
     */
    protected function performUpdate(Builder $query, array $options = array()) {
        $dirty = $this->getDirty();
        $oldData = array_only($this->original, array_keys($dirty));
        $performUpdate = parent::performUpdate($query, $options);
        $keyName = $this->getKeyName();
        Event::fire(new DBEvent('updated', $this->getTableName(), [
            'id' => !is_array($keyName) ? $this->original[$keyName] : json_encode(array_only($this->original, $keyName)),
            'old' => $oldData,
            'new' => $dirty
        ]));
        return $performUpdate;
    }

    /**
     * get model data
     * @param type $opts
     */
    public static function getCollections($opts = [])
    {
        $opts = array_merge([
            'select' => [],
            'where' => [],
            'orderby' => 'created_at',
            'order' => 'desc'
        ], array_filter($opts));

        $collection = self::select($opts['select']);
        if ($opts['where']) {
            foreach ($opts['where'] as $col => $value) {
                $collection->where($col, $value);
            }
        }
        if (is_array($opts['orderby'])) {
            foreach ($opts['orderby'] as $orderby) {
                $collection->orderBy($orderby['column'], $orderby['dir']);
            }
        } else {
            $collection->orderBy($opts['orderby'], $opts['order']);
        }
        return $collection->get();
    }
}
