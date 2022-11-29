<?php
namespace Rikkei\Resource\Model;

use Rikkei\Core\Model\CoreModel;
use DB;

class LanguageLevel extends CoreModel
{
    protected $table = 'language_level';
    public $timestamps = false;
    const KEY_CACHE = 'language_level';
    
    /**
     *  store this object
     * @var object
     */
    protected static $instance;
    protected $fillable = ['name','language_id'];
    
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
    
    /**
     * check exit program language by name
    */
    public static function checkExist($name, $id = null) {
        if ($id == null) {
            return self::where('name',trim($name))->count();
        }
        return self::where('name',trim($name))->whereNotIn('id',[$id])->count();
    }
    
    /**
     * Get level of language
     * 
     * @param int $languageId
     * @return LanguageLevel collection
     */
    static function getLevelByLanguage($languageId) {
        return self::where('language_id', $languageId)
                ->select('*')
                ->get();
    }
    
    /**
     * Insert new levels for language
     * Delete old levels
     * 
     * @param array $data
     * @param int $langId
     * @throws \Rikkei\Resource\Model\Exception
     */
    static function insertLevels($data, $langId) {
        DB::beginTransaction();
        try {
            $dataInsert = [];
            self::where('language_id', $langId)->delete();
            foreach ($data as $name) {
                $dataInsert[] = [
                    'name' => $name,
                    'language_id' => $langId
                ];
            }
            if (count($dataInsert)) {
                self::insert($dataInsert);
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
