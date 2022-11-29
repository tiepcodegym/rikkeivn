<?php
namespace Rikkei\Api\Helper;

class Base
{
    private static $instance;
    protected $model;

    /**
     * get instance
     * @return object
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * get list
     * @param array $data
     * @return array
     */
    public function getList($data = [])
    {
        $opts = array_merge([
            'select' => [],
            's' => null,
            'page' => 1,
            'per_page' => ApiConst::PER_PAGE,
            'fields_search' => [],
            'orderby' => 'id',
            'order' => 'asc',
            'where' => []
        ], $data);

        $model = $this->model;
        $collection = $model::select($opts['select'])
                ->orderBy($opts['orderby'], $opts['order']);

        if ($opts['where']) {
            foreach ($opts['where'] as $cond) {
                $collection->where($cond['field'], $cond['compare'], $cond['value']);
            }
        }

        if ($opts['s'] && $opts['fields_search']) {
            $collection->where(function ($query) use ($opts) {
                foreach ($opts['fields_search'] as $field) {
                    $query->orWhere($field, 'like', '%'.$opts['s'].'%');
                }
            });
        }

        if ($opts['per_page'] < 0) {
            $collection = $collection->get();
            $response = [
                'data' => $collection
            ];
            $isNotFound = $collection->isEmpty();
        } else {
            $response = $collection->paginate($opts['per_page'], ['*'], 'page', $opts['page']);
            $isNotFound = $response->isEmpty();
        }
        if ($isNotFound) {
            throw new \Exception(trans('core::message.Not found entity'), 404);
        }
        return $response;
    }

    /**
     * unset special field
     * @param array $fields
     * @param array $specFields
     */
    public function unsetSpecCol($fields, $specFields, $defaultPrefix = '')
    {
        foreach ($fields as $key => $field) {
            if (in_array($field, $specFields)) {
                unset($fields[$key]);
            } elseif ($defaultPrefix) {
                $splitField = explode('.', $field);
                if (count($splitField) == 1) {
                   $fields[$key] = $defaultPrefix . '.' . $field;
                }
            } else {
                //
            }
        }
        return $fields;
    }

    /**
     * get filter error message
     * @param Exception $ex
     */
    public function errorMessage($ex, $limitLen = 100)
    {
        $message = $ex->getMessage();
        if ($ex->getCode() == 422) { //type custom
            return $ex->getMessage();
        }
        if (strlen($message) <= $limitLen) {
            return $message;
        }
        return substr($message, 0, $limitLen) . '...';
    }

}
