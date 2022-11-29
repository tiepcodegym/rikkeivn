<?php
namespace Rikkei\Resource\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\CacheHelper;
use Illuminate\Support\Facades\Config as SupportConfig;
use Rikkei\Core\View\View;
use Rikkei\Team\View\Config;
use Rikkei\Resource\Model\LanguageLevel;
use DB;


class Languages extends CoreModel
{
    public $timestamps = false;
    protected $table = 'languages';
    
    const KEY_CACHE = 'languages';
    const KEY_CACHE_LANG_WITH_LEVEL = 'lang_with_level';
    
    /**
     *  store this object
     * @var object
     */
    protected static $instance;
    protected $fillable = ['name','english_name'];
    /**
     * get list
     * 
     * @return objects
     */
    public function getList()
    {
        if ($langs = CacheHelper::get(self::KEY_CACHE)) {
            return $langs;
        }
        $langs = self::orderBy('name', 'asc')->select('*')->get();
        CacheHelper::put(self::KEY_CACHE, $langs);
        return $langs;
    }
    
    static function getListWithLevel() {
        if ($langs = CacheHelper::get(self::KEY_CACHE_LANG_WITH_LEVEL)) {
            return $langs;
        }
        $langLevelTable = LanguageLevel::getTableName();
        $langTable = self::getTableName();
        $groupConcat = self::GROUP_CONCAT;
        $concat = self::CONCAT;
        $langs = self::leftJoin("{$langLevelTable}", "{$langLevelTable}.language_id", "=", "{$langTable}.id")
                    ->orderBy('name', 'asc')
                    ->groupBy("{$langTable}.id")
                    ->select(
                        "{$langTable}.*",
                        DB::raw("(SELECT GROUP_CONCAT(concat( id, '{$concat}', name ) SEPARATOR '{$groupConcat}') 
                            FROM {$langLevelTable} 
                            WHERE language_id = {$langTable}.id
                        ) AS language_level")
                    )
                    ->get();
        CacheHelper::put(self::KEY_CACHE_LANG_WITH_LEVEL, $langs);
        return $langs;
    }
    
    public static function getInstance()
    {
        if (! isset(self::$instance)) {
            self::$instance = new static;
        }
        return self::$instance;
    }
    
    /**
     * Get languages name by languages id
     * @param array $ids array language_id
     * @return array
     */
    public function getNamesByIds($ids) {
        if (!is_array($ids)) $ids = array($ids);
        $langs = self::whereIn('id', $ids)->select('name')->get();
        $result = [];
        foreach ($langs as $lang) {
            $result[] = $lang->name;
        }
        
        return $result;
    }
    
    /** 
      * Get languages id by languages name
      * @param name languages
      * @return id languages
    */
    public static function getIdByName($lang) {
       
        if(!empty($lang)) {
            $arrayLangIdV = self::whereIn('name',$lang)->select('id')->get()->toArray();
            $arrayLangIdE = self::whereIn('english_name',$lang)->select('id')->get()->toArray();
            $arrayLangId = array_merge($arrayLangIdE,$arrayLangIdV);
            $arrayCheck = array();
            foreach ($arrayLangId as $value) {
              if (!in_array($value['id'],$arrayCheck)) {
                array_push($arrayCheck, $value['id']);
              }    
            }
            if($arrayLangId) {
              return $arrayCheck;
            } else {
              return false;
            }
        } else {
            return false;
        }
    }  
    public static function getGridData() {
        $pager = Config::getPagerData();
        $langTable = self::getTableName();
        $levelTable = LanguageLevel::getTableName();
        $collection = self::select(
                        "{$langTable}.*", 
                            DB::raw("(SELECT GROUP_CONCAT(concat( name ) SEPARATOR ', ') 
                                FROM {$levelTable} 
                                        where language_id = {$langTable}.id
                                ) AS levels")
                        )
                        ->leftJoin("{$levelTable}", "{$levelTable}.language_id", "=", "{$langTable}.id")
                        ->groupBy("{$langTable}.id")
                        ->orderBy($pager['order'], $pager['dir']);
        $collection = self::filterGrid($collection);
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }
    
    /**
     * Get language by id with levels name
     */
    static function getById($langId) {
        $langTable = self::getTableName();
        $levelTable = LanguageLevel::getTableName();
        return self::leftJoin("{$levelTable}", "{$levelTable}.language_id", "=", "{$langTable}.id")
                    ->where("{$langTable}.id", $langId)
                    ->select(
                        "{$langTable}.*",
                        DB::raw("(SELECT GROUP_CONCAT(concat( name ) SEPARATOR ', ') 
                                FROM {$levelTable} 
                                        where language_id = {$langTable}.id
                                ) AS levels")
                    )
                    ->first();
    }
    
    /**
     * rewrite save model
     * 
     * @param array $options
     */
    public function save(array $options = array()) {
        try {
            CacheHelper::forget(self::KEY_CACHE);
            CacheHelper::forget(self::KEY_CACHE_LANG_WITH_LEVEL);
            return parent::save($options);
        } catch (Exception $ex) {

        }
        
    }
}
