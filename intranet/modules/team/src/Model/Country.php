<?php

namespace Rikkei\Team\Model;

use Rikkei\Core\View\CacheHelper;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Team;

class Country extends CoreModel
{
    protected $table = 'lib_country';
    const KEY_CACHE = 'lib_country';
    const KEY_CACHE_ALL = 'country_all';
    const VN = 'VN';
    const JP = 'JP';
    const US = 'US'; // united states of america
    /**
     * getLibCountry
     * return array ['country_code' => 'name']
     */
    public static function getAll()
    {
        $datas = CacheHelper::get(self::KEY_CACHE);
        if ($datas && count($datas)) {
            return $datas;
        }
        $collection = self::select(['country_code','name'])
            ->orderBy('country_code', 'ASC')
            ->get();
        $datas = [];
        foreach($collection as $item) {
            $datas[$item->country_code] = $item->name;
        }
        CacheHelper::put(self::KEY_CACHE, $datas);
        return $datas;
    }

    /**
     * getLibCountry
     * return array ['id' => 'name']
     */
    public static function getCountryList()
    {
        $datas = CacheHelper::get(self::KEY_CACHE_ALL);
        if ($datas && count($datas)) {
            return $datas;
        }
        $collection = self::select(['id','name'])
            ->orderBy('name', 'ASC')
            ->get();
        $datas = [];
        foreach($collection as $item) {
            $datas[$item->id] = $item->name;
        }
        CacheHelper::put(self::KEY_CACHE_ALL, $datas);
        return $datas;
    }

    /**
    *get list country
    *return array ['id' => 'name']
    **/
    public static function listCountry()
    {
        $datas = CacheHelper::get(self::KEY_CACHE_ALL);
        if ($datas && count($datas)) {
            return $datas;
        }
        $collection = self::select(['id','name','country_code'])
            ->orderBy('name', 'ASC')
            ->get();
        $datas = [];
        foreach($collection as $item) {
            $datas[$item->id] = [$item->name,$item->country_code];
        }
        CacheHelper::put(self::KEY_CACHE_ALL, $datas);
        return $datas;
    }

    /**
     * get id country by codes
     *
     * return array ['code' => 'id']
     */
    public static function getIdByCode($codes)
    {
        $collection = self::select(['id', 'country_code']);
        if (is_array($codes)) {
            $collection->whereIn('country_code', $codes);
        } else {
            $collection->where('country_code', '=', $codes);
        }
        $collection = $collection->get();
        $datas = [];
        foreach($collection as $item) {
            $datas[strtolower($item->country_code)] = $item->id;
        }
        return $datas;
    }

    /**
     * get country of employee by team
     * @param  [object] $employee
     * @return [collection]
     */
    public static function getCountryOfEmp($employee)
    {
        $teamCodePrefix = Team::getOnlyOneTeamCodePrefix($employee);
        $contryCode = '';
        switch ($teamCodePrefix) {
            case Team::CODE_PREFIX_JP:
                $contryCode = static::JP;
                break;
            default:
                $contryCode = static::VN;
                break;
        }
        return static::where('country_code', $contryCode)->first();
    }

    /**
     * get languages popular by country (country have company)
     * @return [array]
     */
    public static function languageByCountry()
    {
        $arrKey = [static::VN, static::JP, static::US];

        $counryList = static::listCountry();
        $countries = [];
        $languages = static::languages();
        foreach ($counryList as $key => $contry) {
            if (in_array($contry[1], $arrKey)) {
                $countries[$key] = [
                    $languages[$contry[1]],
                    $contry[1]
                ];
            }
        }

        return $countries;
    }

    /**
     * @return [type] [description]
     */
    public static function languages()
    {
        return [
            static::VN => 'Tiếng Việt - VI',
            static::JP => '日本語 - JA',
            static::US => 'English - EN',
        ];
    }
}
