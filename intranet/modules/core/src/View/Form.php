<?php

namespace Rikkei\Core\View;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Rikkei\Core\View\CookieCore;
use Rikkei\Core\Model\CoreConfigData;
use Illuminate\Support\Facades\Storage;

class Form
{
    /**
     * set form data
     *
     * @param array $data
     */
    public static function setData($data = null, $key = null)
    {
        if ($key && ! $data) {
            return;
        }
        if(!$data || is_string($data)) {
            self::setDataInput($data, $key);
        } elseif (is_array($data)) {
            self::setDataForm($data, $key);
        } elseif (is_object($data)) {
            self::setDataModel($data, $key);
        }
    }

    /**
     * get form data value
     *
     * @param type $key
     * @return type
     */
    public static function getData($key = null)
    {
        if(!$key) {
            return Session::get('form_data');
        }
        if(Session::has('form_data.'.$key)) {
            return Session::get('form_data.'.$key);
        }
    }

    /**
     * pull form data value
     *
     * @param type $key
     * @return type
     */
    public static function pullData($key = null)
    {
        if(!$key) {
            return Session::pull('form_data');
        }
        if(Session::has('form_data.'.$key)) {
            return Session::pull('form_data.'.$key);
        }
        return null;
    }

    /**
     * set form data
     *  - data is request input
     *
     * @param string $name
     */
    public static function setDataInput($name = null, $key = null)
    {
        if(!$name) {
            $data = (array)app('request')->all();
        } else {
            $data = (array)app('request')->input($name);
        }
        if ($key) {
            $data = [$key => $data];
        }
        self::setDataForm($data);
    }

    /**
     * set form data
     *  - data is model
     *
     * @param type $model
     * @param string key
     */
    public static function setDataModel($model, $key = null)
    {
        if ($model instanceof \Illuminate\Contracts\Support\Arrayable) {
            $model = $model->toArray();
        } else {
            $model = [];
        }
        if($key) {
            $model = [$key => $model];
        }
        self::setDataForm($model);
    }

    /**
     * remove all form data
     */
    public static function forget($key = null)
    {
        if ($key) {
            Session::forget('form_data.' . $key);
        } else {
            Session::forget('form_data');
        }
    }

    /**
     * set form data format array
     *
     * @param array $data
     */
    protected static function setDataForm(array $data = array(), $key = null)
    {
        if (!$data || !count($data)) {
            return;
        }
        if ($key) {
            foreach ($data as $keyData => $value) {
                Session::flash('form_data.' . $key . '.' . $keyData, $value);
            }
        } else {
            foreach ($data as $keyData => $value) {
                Session::flash('form_data.' . $keyData, $value);
            }
        }
    }

    /**
     * get filter data follow current url
     *
     * @param string $key
     * @param string $key2
     * @param mix $urlFilter url or input data
     * @return string
     */
    public static function getFilterData($key = null, $key2 = null, $urlFilter = null)
    {
        if (is_array($urlFilter)) {
            $data = $urlFilter;
        } else {
            if (!$urlFilter) {
                $urlFilter = app('request')->url() . '/';
            }
            $data = CookieCore::getRaw('filter.' . $urlFilter);
        }
        if (!$key) {
            return $data;
        }
        if (!isset($data[$key])) {
            return null;
        }
        $data = $data[$key];
        if (!$key2) {
            return $data;
        }
        if (!isset($data[$key2])) {
            return null;
        }
        return $data[$key2];
    }

    /*
     * set filter data
     */
    public static function setFilterData($key, $value, $key2 = null, $urlFilter = null)
    {
        if (!$urlFilter) {
            $urlFilter = app('request')->url() . '/';
        }
        $urlFilter = 'filter.'. $urlFilter;
        $data = CookieCore::getRaw($urlFilter);
        if (!$key2) {
            $data[$key] = $value;
        } else {
            if (!isset($data[$key])) {
                $data[$key] = [];
            }
            $data[$key][$key2] = $value;
        }
        CookieCore::setRaw($urlFilter, $data);
    }

    /**
     * get filter data pager follow current url
     *
     * @param string $key
     * @return string
     */
    public static function getFilterPagerData($key = null, $urlFilter = null)
    {
        if (!$urlFilter) {
            $urlFilter = app('request')->url() . '/';
        }
        $data = CookieCore::getRaw('filter_pager.' . $urlFilter);
        if (!$key) {
            return $data;
        }
        if (!isset($data[$key])) {
            return null;
        }
        return $data[$key];
    }

    /**
     * remove filter data follow current url
     */
    public static function forgetFilter($url = null)
    {
        $url = $url ? $url : app('request')->url() . '/';
        CookieCore::forgetRaw('filter.' . $url);
    }

    /**
     * filter array, get value not null
     *
     * @param array $data
     * @param array $filterKey
     * @return array
     */
    public static function filterEmptyValue(array &$data, array $filterKey, $valueFilter = '')
    {
        foreach ($filterKey as $filter) {
            if (isset($data[$filter]) && trim($data[$filter]) === $valueFilter) {
                $data[$filter] = null;
            }
        }
        return $data;
    }

    /**
     * save system data
     */
    public static function coreSaveSettingGeneral($dataItem = [])
    {
        if (!$dataItem) {
            $dataItem = Input::get('item');
        }
        $response = [];
        if (!$dataItem) {
            $response['success'] = 1;
            $response['message'] = Lang::get('core::message.Save success');
            return response()->json($response);
        }
        foreach ($dataItem as $key => $value) {
            $item = CoreConfigData::getItem($key);
            if (!$item) {
                $item = new CoreConfigData();
                $item->key = $key;
            }
            if (is_array($value)) {
                $value = serialize($value);
            }
            $item->value = $value;
            $item->save();
        }
        $response['success'] = 1;
        $response['message'] = Lang::get('core::message.Save success');
        return response()->json($response);
    }

    /**
     * make directory and chmod
     *
     * @param string $folder
     * @param int $chmod
     */
    public static function makeDir($folder, $chmod = 0777)
    {
        if (!Storage::exists($folder)) {
            Storage::makeDirectory($folder, $chmod);
        }
        self::chmodFile($folder, $chmod);
    }

    /**
     * chmod folder, file in folder storage/app/
     *
     * @param string $pathStorage
     * @param int $chmod
     */
    public static function chmodFile($pathStorage, $chmod = 0777)
    {
        return @chmod(storage_path('app/' . $pathStorage), $chmod);
    }
}
