<?php
namespace Rikkei\Contract\View;

use Illuminate\Support\Facades\Input;
use Rikkei\Core\View\Form;

class Config
{
    /**
     * get direction class css for current order
     * 
     * @param type $orderKey
     * @return type
     */
    public static function getDirClass($orderKey, $url = null)
    {
        if (! Form::getFilterPagerData('order', $url) || Form::getFilterPagerData('order', $url) != $orderKey) {
            return '';
        }
        if (Form::getFilterPagerData('dir', $url) == 'asc') {
            return 'sorting_asc';
        }
        return 'sorting_desc';
    }
    
    /**
     * get url sort order
     * 
     * @param type $orderKey
     * @return type
     */
    public static function getDirOrder($orderKey, $url = null)
    {
        if (! Form::getFilterPagerData('order', $url) || 
            Form::getFilterPagerData('order', $url) != $orderKey || 
            Form::getFilterPagerData('dir', $url) == 'asc') {
            return 'desc';
        }
        return 'asc';
    }
    
    /**
     * get url sort order
     * 
     * @param type $orderKey
     * @return type
     */
    public static function getUrlOrder($orderKey)
    {
        $request = app('request');
        if (! $orderKey) {
            return $request->fullUrl();
        }
        $dir = null;
        if (! Input::get('order') || Input::get('order') != $orderKey || Input::get('dir') == 'asc') {
            $dir = 'desc';
        } else {
            $dir = 'asc';
        }
        $orderNew = [
            'order' => $orderKey,
            'dir' => $dir,
        ];
        $paramUrl = array_merge(Input::all(),$orderNew);
        return $request->fullUrlWithQuery($paramUrl);
    }
    
    /**
     * get pager option 
     * 
     * @return type
     */
    public static function getPagerData($urlSubmitFilter = null, array $pagerOption = [])
    {
        $pager = array_merge([
            'limit' => 50,
            'order' => 'id',
            'dir' => 'asc',
            'page' => 1
        ], $pagerOption);
        $isAjax = request()->ajax() || request()->wantsJson();
        $pagerFilter = (array) Form::getFilterPagerData(null, $urlSubmitFilter);
        $pagerFilter = array_filter($pagerFilter);
        if (isset($pagerOption['page']) && $isAjax && isset($pagerFilter['page'])) {
            $pagerFilter['page'] = $pagerOption['page'];
        }
        if ($pagerFilter) {
            $pager = array_merge($pager, $pagerFilter);
        }
        if (!is_numeric($pager['page']) || $pager['page'] < 1) {
            $pager['page'] = 1;
        }
        return $pager;
    }
    
    /**
     * get pager option from url
     * 
     * @return type
     */
    public static function getPagerDataQuery($pagerOption = [])
    {
        $pager = array_merge([
            'limit' => 10,
            'order' => 'id',
            'dir' => 'asc',
            'page' => 1
        ], $pagerOption);
        $pagerFilter = (array) Input::all();
        $pagerFilter = array_filter($pagerFilter);
        if ($pagerFilter) {
            $pager = array_merge($pager, $pagerFilter);
        }
        return $pager;
    }
    
    /**
     * rebuild url with new params
     * 
     * @param array $paramsNew
     * @return string
     */
    public static function urlParams($paramsNew = array())
    {
        $request = app('request');
        if (!$paramsNew) {
            return $request->fullUrl();
        }
        $paramUrl = array_merge(Input::all(), $paramsNew);
        return $request->fullUrlWithQuery($paramUrl);
    }
    
    /**
     * limt Option
     * 
     * @return array
     */
    public static function toOptionLimit()
    {
        return [
            ['value'=>'10','label'=>'10'],
            ['value'=>'20','label'=>'20'],
            ['value'=>'50','label'=>'50']
        ];
    }
}

